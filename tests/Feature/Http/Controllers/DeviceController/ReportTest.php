<?php

namespace Tests\Feature\Http\Controllers\DeviceController;

use App\Enums\DeviceLog\ReportingType;
use App\Enums\User\PlanType;
use App\Models\Entities\Alert;
use App\Models\Entities\Device;
use App\Models\Entities\DeviceLog;
use App\Models\Entities\Rule;
use App\Models\Entities\User;
use Carbon\Carbon;
use Closure;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Testing\TestResponse;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;
use Throwable;

class ReportTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * @var User
     */
    private User $actingUser;

    /**
     * @var Device
     */
    private Device $device;

    /**
     * @var int
     */
    private int $reportInterval;

    protected function setUp(): void
    {
        parent::setUp();
        $this->actingUser = User::where('plan', PlanType::PERSONAL->value)->first();
        $this->reportInterval = config_int('specs.device_report_interval', 0);
    }

    /**
     * @param Closure $setUp
     * @param Closure $assertion
     * @return void
     *
     * @dataProvider data_report_success
     */
    public function test_report_success(Closure $setUp, Closure $assertion): void
    {
        $this->device = $setUp($this);
        $this->actingAs($this->actingUser);

        $response = $this->postJson(route('device.report', ['id' => $this->device->id]));
        $response->assertOk();
        $assertion($response, $this);
        Carbon::setTestNow();
    }

    /**
     * @return array<string, mixed>
     */
    public static function data_report_success(): array
    {
        return [
            'アラート中, 通知ルールなし, アラートなし' => [
                'setUp' => function (ReportTest $case) {
                    Carbon::setTestNow('2024-01-29 11:50');
                    $reportedAt = now()->subMinutes($case->reportInterval)->timestamp;
                    $device = Device::factory()->for($case->actingUser, 'ownerUser')
                        ->create([
                            'in_alert' => 1,
                            'reported_at' => $reportedAt,
                            'updated_at' => $reportedAt,
                        ]);
                    self::assertFalse($device->alert()->exists());
                    return $device;
                },
                'assertion' => function (TestResponse $response, ReportTest $case) {
                    $case->assertSuccessResponse($response, $case->device, '2024-01-29 11:50');
                    $case->assertDeviceLog($case->device, $case->actingUser);
                    // 更新前の端末の最終リセット日時は1時間前
                    self::assertSame(
                        Carbon::parse('2024-01-29 10:50')->timestamp,
                        $case->device->reported_at
                    );
                    // 更新日時が更新されていること
                    self::assertSame(
                        Carbon::parse('2024-01-29 11:50')->timestamp,
                        Device::find($case->device->id)->updated_at->timestamp
                    );
                },
            ],
            '通知ルールあり' => [
                'setUp' => function (ReportTest $case) {
                    Carbon::setTestNow('2024-01-29 11:51');
                    return Device::factory()->for($case->actingUser, 'ownerUser')
                        ->for(Rule::factory()->for($case->actingUser)->state(fn($attr) => ['time_limits' => 3]))
                        ->create(['reported_at' => null]);
                },
                'assertion' => function (TestResponse $response, ReportTest $case) {
                    $case->assertSuccessResponse($response, $case->device, '2024-01-29 11:51');
                    $case->assertDeviceLog($case->device, $case->actingUser);

                    $actual = $response->decodeResponseJson()['deviceInfo'];
                    self::assertSame(3, $actual['remainingTime']);
                    self::assertSame(3, $actual['limitTime']);
                    self::assertSame(3, $case->device->rule->time_limits);
                    self::assertFalse(Alert::where('device_id', $case->device->id)->exists());
                },
            ],
            'アラートあり' => [
                'setUp' => function (ReportTest $case) {
                    Carbon::setTestNow('2024-01-29 11:52');
                    $device = Device::factory()->for($case->actingUser, 'ownerUser')
                        ->has(Alert::factory())
                        ->create(['reported_at' => null]);
                    self::assertTrue($device->alert()->exists());
                    return $device;
                },
                'assertion' => function (TestResponse $response, ReportTest $case) {
                    $case->assertSuccessResponse($response, $case->device, '2024-01-29 11:52');
                    $case->assertDeviceLog($case->device, $case->actingUser);

                    // 更新前の最終リセット履歴なし
                    self::assertNull($case->device->reported_at);
                    self::assertFalse(Alert::where('device_id', $case->device->id)->exists());
                },
            ],
        ];
    }

    /**
     * @param Closure $setUp
     * @param Closure $assertion
     * @return void
     *
     * @dataProvider data_report_fail
     */
    public function test_report_fail(Closure $setUp, Closure $assertion): void
    {
        $this->device = $setUp($this);
        $this->actingAs($this->actingUser);

        $response = $this->postJson(route('device.report', ['id' => $this->device->id]));
        $assertion($response, $this);
        Carbon::setTestNow();
    }

    /**
     * @return array<string, mixed>
     */
    public static function data_report_fail(): array
    {
        return [
            '最終レポートから実行可能なインターバル未経過' => [
                'setUp' => function (ReportTest $case) {
                    Carbon::setTestNow('2024-01-29 12:59');
                    return Device::factory()->for($case->actingUser, 'ownerUser')
                        ->create(['reported_at' => now()->subMinutes($case->reportInterval - 1)->timestamp]);
                },
                'assertion' => function (TestResponse $response, ReportTest $case) {
                    $response->assertStatus(Response::HTTP_INTERNAL_SERVER_ERROR);
                    self::assertSame(
                        sprintf('リセットは「%d分に1回」までです', $case->reportInterval),
                        $response->decodeResponseJson()['message']
                    );
                    self::assertFalse(DeviceLog::where('device_id', $case->device->id)->exists());

                    // 更新前の最終リセット日時は1時間+1分前
                    self::assertSame(
                        Carbon::parse('2024-01-29 12:00')->timestamp,
                        $case->device->reported_at
                    );
                },
            ],
            '削除端末を指定' => [
                'setUp' => function (ReportTest $case) {
                    $device = Device::factory()
                        ->for($case->actingUser, 'ownerUser')
                        ->create(['reported_at' => null]);
                    $device->delete();
                    return $device;
                },
                'assertion' => function (TestResponse $response, ReportTest $case) {
                    $response->assertNotFound();
                    self::assertFalse(DeviceLog::where('device_id', $case->device->id)->exists());
                },
            ],
            '他ユーザーの端末を指定' => [
                'setUp' => function (ReportTest $case) {
                    return Device::factory()
                        ->for(User::factory(), 'ownerUser')
                        ->create(['reported_at' => null]);
                },
                'assertion' => function (TestResponse $response, ReportTest $case) {
                    $response->assertNotFound();
                },
            ],
        ];
    }

    /**
     * @return void
     */
    public function test_report_not_json_fail(): void
    {
        $device = Device::factory()->for($this->actingUser, 'ownerUser')->create();
        $this->actingAs($this->actingUser);
        $response = $this->post(route('device.report', ['id' => $device->id]));
        $response->assertStatus(Response::HTTP_BAD_REQUEST);
    }

    /**
     * @param TestResponse $response
     * @param Device $device
     * @param string $reportDateTime
     * @return void
     * @throws Throwable
     */
    private function assertSuccessResponse(TestResponse $response, Device $device, string $reportDateTime): void
    {
        $remainingHour = $device->rule?->time_limits ?? config_int('specs.time_limit_min', 0);
        $response->decodeResponseJson()->assertExact([
            'message' => '',
            'deviceInfo' => [
                'id' => $device->id,
                'image' => $device->getImage(),
                'name' => $device->name,
                'resetWord' => $device->reset_word,
                'lastResetAt' => $reportDateTime,
                'isAlert' => false,
                'isSuspend' => false,
                'enableReset' => false,
                'isDemo' => false,
                'baseDate' => $reportDateTime,
                'remainingTime' => $remainingHour,
                'limitTime' => $remainingHour,
                'resetLimitAt' =>
                    Carbon::parse($reportDateTime)->addHours($remainingHour)->format('Y-m-d H:i'),
            ]
        ]);
    }

    /**
     * @param Device $device
     * @param User $user
     * @return void
     */
    private function assertDeviceLog(Device $device, User $user): void
    {
        $actual = DeviceLog::where('user_id', $user->id)->where('device_id', $device->id)->first();
        $this->assertSame(ReportingType::USER_WEB->value, $actual->reporting_type);
        $this->assertNotNull($actual->created_at);
    }
}
