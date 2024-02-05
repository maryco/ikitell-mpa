<?php

namespace Tests\Feature\Http\Controllers;

use App\Enums\NotificationLog\JobStatus;
use App\Models\Entities\Device;
use App\Models\Entities\DeviceDashboard;
use App\Models\Entities\NotificationLog;
use App\Models\Entities\User;
use Carbon\Carbon;
use Closure;
use Illuminate\Database\Eloquent\Factories\Sequence;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

class HomeControllerTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * @var User
     */
    private User $actingUser;

    /**
     * @param Closure $actingUser
     * @param Closure $assertion
     * @return void
     *
     * @dataProvider data_public_root
     */
    public function test_public_root(Closure $actingUser, Closure $assertion): void
    {
        if ($user = $actingUser()) {
            $this->actingAs($user);
        }
        $response = $this->get('/');
        $assertion($response);
    }

    /**
     * @return array<string, mixed>
     */
    public static function data_public_root(): array
    {
        return [
            '未ログイン' => [
                'actingUser' => fn(): ?User => null,
                'assertion' => function (TestResponse $response) {
                    $response->assertOk();
                    $response->assertSeeText('Laravelは「おひとりさま」の不測の事態を誰かにお知らせするための自己防衛サービスです。');
                }
            ],
            'ログイン済' => [
                'actingUser' => fn(): ?User => User::where('plan', config('codes.subscription_types.basic'))->first(),
                'assertion' => function (TestResponse $response) {
                    $response->assertRedirect('home');
                }
            ],
        ];
    }

    /**
     * @param Closure $setUp
     * @param Closure $assertion
     * @return void
     *
     * @dataProvider data_home
     */
    public function test_home(Closure $setUp, Closure $assertion): void
    {
        $this->actingUser = User::where('plan', config('codes.subscription_types.basic'))->first();
        $setUp($this->actingUser);
        $this->actingAs($this->actingUser);

        $response = $this->get('home');
        $response->assertOk();
        $assertion($response, $this);

        Carbon::setTestNow();
    }

    /**
     * @return array<string, mixed>
     */
    public static function data_home(): array
    {
        return [
            '端末, アラート履歴なし' => [
                'setUp' => function (User $user) {
                },
                'assertion' => function (TestResponse $response, HomeControllerTest $case) {
                    $response->assertViewHasAll([
                        'devices' => [],
                        'logs' => [],
                    ]);
                    $response->assertSee('通知ログ');
                    $response->assertDontSee('通知メールが送信されました。');
                },
            ],
            '端末1つ, アラート履歴なし' => [
                'setUp' => function (User $user) {
                    Carbon::setTestNow('2024-01-27 17:30');
                    Device::factory()->for($user, 'ownerUser')
                        ->create([
                            'name' => 'スマホ01',
                            'image' => null,
                            'reset_word' => 'タイマーリセット',
                            'reported_at' => null,
                        ]);
                },
                'assertion' => function (TestResponse $response, HomeControllerTest $case) {
                    $response->assertViewHas('devices', function ($actualDevices) use ($case) {
                        self::assertCount(1, $actualDevices);

                        $device = Device::where('owner_id', $case->actingUser->id)->first();
                        $expected = (new DeviceDashboard($device))->toArray();
                        $actual = $actualDevices[0]->toArray();
                        self::assertInstanceOf(DeviceDashboard::class, $actualDevices[0]);
                        self::assertSame($expected['id'], $actual['id']);
                        self::assertSame($expected['name'], $actual['name']);
                        self::assertSame($expected['resetWord'], $actual['resetWord']);
                        self::assertSame('2024-01-27 17:30', $actual['baseDate']);
                        self::assertSame(24, $actual['remainingTime']);
                        self::assertSame(24, $actual['limitTime']);
                        self::assertSame('2024-01-28 17:30', $actual['resetLimitAt']);
                        return true;
                    });

                    $response->assertViewHas('logs', function ($logs) {
                        self::assertInstanceOf(LengthAwarePaginator::class, $logs);
                        self::assertCount(0, $logs);
                        return true;
                    });
                },
            ],
            '端末2つ' => [
                'setUp' => function (User $user) {
                    Carbon::setTestNow('2024-01-27 18:00');
                    Device::factory()
                        ->count(2)
                        ->for($user, 'ownerUser')
                        ->state(new Sequence(fn($seq) => ['reported_at' => now()->addHours($seq->index)->timestamp]))
                        ->create();
                },
                'assertion' => function (TestResponse $response, HomeControllerTest $case) {
                    $response->assertViewHas('devices', function ($actualDevices) use ($case) {
                        self::assertCount(2, $actualDevices);

                        // リセット日時の降順である
                        $expectedDevices = Device::where('owner_id', $case->actingUser->id)->orderByDesc('reported_at')
                            ->get()
                            ->transform(fn($device) => (new DeviceDashboard($device))->toArray());
                        foreach ($expectedDevices as $index => $expected) {
                            $actual = $actualDevices[$index]->toArray();
                            self::assertSame($expected['id'], $actual['id']);
                            self::assertSame($expected['name'], $actual['name']);
                            self::assertSame($expected['resetWord'], $actual['resetWord']);
                            self::assertSame($expected['baseDate'], $actual['baseDate']);
                        }
                        return true;
                    });
                },
            ],
            'アラート履歴あり(表示対象ログ1件)' => [
                'setUp' => function (User $user) {
                    $device = Device::factory()->for($user, 'ownerUser')->create();
                    NotificationLog::factory()
                        ->count(4)
                        ->for($device)
                        ->state(new Sequence(
                            ['job_status' => JobStatus::RESERVED->value],
                            ['job_status' => JobStatus::EXECUTED->value],
                            ['job_status' => JobStatus::FAILED->value],
                            ['job_status' => JobStatus::UNKNOWN->value],
                        ))
                        ->create();
                },
                'assertion' => function (TestResponse $response, HomeControllerTest $case) {
                    $response->assertViewHas('logs', function ($logs) use ($case) {
                        self::assertCount(1, $logs);
                        $expected = NotificationLog::jobStatus(JobStatus::EXECUTED->value)->first();
                        $actual = $logs[0];
                        self::assertSame(
                            $expected->name . 'さんに通知メールが送信されました。',
                            $actual->name . 'さんに通知メールが送信されました。',
                        );
                        return true;
                    });
                },
            ],
            '複数端末, 複数アラート履歴(表示対象ログ8件)' => [
                'setUp' => function (User $user) {
                    NotificationLog::factory()
                        ->count(4)
                        ->for(Device::factory()->for($user, 'ownerUser')->create())
                        ->jobExecuted()
                        ->create();
                    NotificationLog::factory()
                        ->count(4)
                        ->for(Device::factory()->for($user, 'ownerUser')->create())
                        ->jobExecuted()
                        ->create();
                },
                'assertion' => function (TestResponse $response, HomeControllerTest $case) {
                    $response->assertViewHas('logs', function ($logs) use ($case) {
                        // 所持する端末すべてのログが対象となっていること
                        self::assertCount(2, $logs->pluck('device_id')->unique());
                        // 表示は最大6件まで
                        self::assertCount(6, $logs);
                        return true;
                    });
                    $response->assertSee(
                        sprintf('href="%s/notice/history/alert/search"', config('app.url')),
                        false
                    );
                },
            ],
        ];
    }
}
