<?php

namespace Tests\Feature\Console\Commands;

use App\Models\Entities\Alert;
use App\Models\Entities\Contact;
use App\Models\Entities\Device;
use App\Models\Repositories\DeviceRepository;
use App\Notifications\AlertNotification;
use Carbon\Carbon;
use Closure;
use Illuminate\Database\Eloquent\Factories\Sequence;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Log\Events\MessageLogged;
use Illuminate\Support\Facades\Event;
use Notification;
use Tests\TestCase;

class SendAlertEmailsTest extends TestCase
{
    use DatabaseTransactions;

    private DeviceRepository $deviceRepo;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->deviceRepo = new DeviceRepository();

        Event::fake(MessageLogged::class);
        $this->createUserWithDevices(10);
    }

    /**
     * @param Closure $setUp
     * @param Closure $assertion
     * @return void
     *
     * @dataProvider data_success
     */
    public function test_success(Closure $setUp, Closure $assertion): void
    {
        Notification::fake();
        $data = $setUp($this);

        Carbon::setTestNow(now()->addMinutes(3));
        $this->artisan('alert:send', $data['arguments'])->assertSuccessful();

        $assertion($data['expectedDevices'], $this);
        Carbon::setTestNow();
    }

    /**
     * @return array<string, mixed>
     */
    public static function data_success(): array
    {
        return [
            'アラート対象端末1件, 1回目' => [
                'setUp' => static function (SendAlertEmailsTest $case) {
                    $device = Device::inRandomOrder()->first();
                    $device->update([
                        'name' => '時間切れ端末',
                        'reported_at' => now()->subHour()->subMinute()->timestamp
                    ]);
                    $device->rule()->update([
                        'time_limits' => 1,
                        'notify_times' => 3,
                    ]);
                    Contact::factory()->for($device->ownerUser)->hasAttached($device)->emailVerified()->create();

                    // アラート生成
                    $case->deviceRepo->issueAlert($device->id);

                    return [
                        'expectedDevices' => [$device],
                        'arguments' => []
                    ];
                },
                'assertion' => static function (array $expectedDevices, SendAlertEmailsTest $case) {
                    $device = $expectedDevices[0];
                    // アラート解除なし
                    self::assertTrue((bool)Device::find($device->id)->in_alert);

                    $actualAlerts = Alert::where('device_id', $device->id)->get();
                    self::assertCount(1, $actualAlerts);

                    $alert = $actualAlerts[0];
                    self::assertSame(1, $alert->notify_count);
                    self::assertSame(3, $alert->max_notify_count);
                    self::assertSame(
                        now()->addMinutes(config('specs.send_alert_interval'))->timestamp,
                        $alert->next_notify_at
                    );

                    // 端末所持ユーザーにのみ送信
                    Notification::assertSentTo($device->ownerUser, AlertNotification::class);
                    Notification::assertNotSentTo($device->contacts()->first(), AlertNotification::class);
                },
            ],
            'アラート対象端末1件, 2回目' => [
                'setUp' => static function (SendAlertEmailsTest $case) {
                    $device = Device::inRandomOrder()->first();
                    $device->update([
                        'name' => '時間切れ端末',
                        'reported_at' => now()->subHour()->subMinute()->timestamp
                    ]);
                    $device->rule()->update([
                        'time_limits' => 1,
                        'notify_times' => 3,
                    ]);
                    Contact::factory()->for($device->ownerUser)->hasAttached($device)->emailVerified()->create();

                    // アラート生成、通知回数アップ
                    $case->deviceRepo->issueAlert($device->id);
                    Alert::where('device_id', $device->id)->first()->update(['notify_count' => 1,]);

                    return [
                        'expectedDevices' => [$device],
                        'arguments' => []
                    ];
                },
                'assertion' => static function (array $expectedDevices, SendAlertEmailsTest $case) {
                    $device = $expectedDevices[0];
                    // アラート解除なし
                    self::assertTrue((bool)Device::find($device->id)->in_alert);

                    $actualAlerts = Alert::where('device_id', $device->id)->get();
                    self::assertCount(1, $actualAlerts);

                    $alert = $actualAlerts[0];
                    self::assertSame(2, $alert->notify_count);
                    self::assertSame(3, $alert->max_notify_count);
                    self::assertSame(
                        now()->addMinutes(config('specs.send_alert_interval'))->timestamp,
                        $alert->next_notify_at
                    );

                    // 端末所持ユーザーと通知先に送信
                    Notification::assertSentTo($device->ownerUser, AlertNotification::class);
                    Notification::assertSentTo($device->contacts()->first(), AlertNotification::class);
                },
            ],
            'アラート対象端末1件, 最終回' => [
                'setUp' => static function (SendAlertEmailsTest $case) {
                    $device = Device::inRandomOrder()->first();
                    $device->update([
                        'name' => '時間切れ端末',
                        'reported_at' => now()->subHour()->subMinute()->timestamp
                    ]);
                    $device->rule()->update([
                        'time_limits' => 1,
                        'notify_times' => 3,
                    ]);
                    Contact::factory()->for($device->ownerUser)->hasAttached($device)->emailVerified()->create();

                    // アラート生成、通知回数アップ
                    $case->deviceRepo->issueAlert($device->id);
                    Alert::where('device_id', $device->id)->first()->update(['notify_count' => 3,]);

                    return [
                        'expectedDevices' => [$device],
                        'arguments' => []
                    ];
                },
                'assertion' => static function (array $expectedDevices, SendAlertEmailsTest $case) {
                    $device = $expectedDevices[0];
                    // アラート解除なし
                    self::assertTrue((bool)Device::find($device->id)->in_alert);

                    $actualAlerts = Alert::where('device_id', $device->id)->get();
                    self::assertCount(1, $actualAlerts);

                    $alert = $actualAlerts[0];
                    // NOTE: 0回目は端末所持ユーザーにのみ通知するため、最終は通知回数+1となる
                    self::assertSame(4, $alert->notify_count);
                    self::assertSame(3, $alert->max_notify_count);
                    self::assertNull($alert->next_notify_at);

                    // 端末所持ユーザーと通知先に送信
                    Notification::assertSentTo($device->ownerUser, AlertNotification::class);
                    Notification::assertSentTo($device->contacts()->first(), AlertNotification::class);
                },
            ],
            'アラート対象端末複数件' => [
                'setUp' => static function (SendAlertEmailsTest $case) {
                    $devices = Device::inRandomOrder()->take(2)->get();
                    $devices->each(function ($device) use ($case) {
                        $device->update([
                            'name' => '時間切れ端末',
                            'reported_at' => now()->subHour()->subMinute()->timestamp
                        ]);
                        $device->rule()->update([
                            'time_limits' => 1,
                            'notify_times' => random_int(3, 10),
                        ]);
                        Contact::factory()->for($device->ownerUser)->hasAttached($device)->emailVerified()->create();

                        // アラート生成
                        $case->deviceRepo->issueAlert($device->id);
                    });

                    // 片方のみ通知2回目
                    Alert::where('device_id', $devices->first()->id)->first()->update([
                        'notify_count' => 2,
                    ]);

                    return [
                        'expectedDevices' => $devices->all(),
                        'arguments' => []
                    ];
                },
                'assertion' => static function (array $expectedDevices, SendAlertEmailsTest $case) {
                    $devices = collect($expectedDevices);
                    $actualAlerts = Alert::whereIn('device_id', $devices->pluck('id')->all())->get();
                    self::assertCount(2, $actualAlerts);

                    foreach ($devices as $index => $device) {
                        $alert = $actualAlerts->where('device_id', $device->id)->first();
                        self::assertSame($index === 0 ? 3 : 1, $alert->notify_count);
                        self::assertSame($device->rule->notify_times, $alert->max_notify_count);
                        self::assertSame(
                            now()->addMinutes(config('specs.send_alert_interval'))->timestamp,
                            $alert->next_notify_at
                        );
                        if ($index === 0) {
                            Notification::assertSentTo($device->ownerUser, AlertNotification::class);
                            Notification::assertSentTo($device->contacts()->first(), AlertNotification::class);
                        } else {
                            Notification::assertSentTo($device->ownerUser, AlertNotification::class);
                            Notification::assertNotSentTo($device->contacts()->first(), AlertNotification::class);
                        }
                    }
                },
            ],
            '最大通知回数通知済み' => [
                'setUp' => static function (SendAlertEmailsTest $case) {
                    $device = Device::inRandomOrder()->first();
                    $device->update([
                        'name' => '時間切れ端末',
                        'reported_at' => now()->subHour()->subMinute()->timestamp
                    ]);
                    $device->rule()->update([
                        'time_limits' => 1,
                        'notify_times' => 3,
                    ]);
                    Contact::factory()->for($device->ownerUser)->hasAttached($device)->emailVerified()->create();

                    // アラート生成、通知回数アップ
                    $case->deviceRepo->issueAlert($device->id);
                    Alert::where('device_id', $device->id)->first()->update(['notify_count' => 4]);

                    return [
                        'expectedDevices' => [$device],
                        'arguments' => []
                    ];
                },
                'assertion' => static function (array $expectedDevices, SendAlertEmailsTest $case) {
                    $device = $expectedDevices[0];
                    $actualAlerts = Alert::where('device_id', $device->id)->get();
                    self::assertCount(1, $actualAlerts);

                    $alert = $actualAlerts[0];
                    // 変更なし
                    self::assertSame(4, $alert->notify_count);
                    self::assertSame(3, $alert->max_notify_count);
                    self::assertLessThanOrEqual(now()->timestamp, $alert->next_notify_at);
                    self::assertLessThanOrEqual(now()->timestamp, $alert->updated_at->timestamp);

                    Notification::assertNothingSent();
                },
            ],
            '次回通知日時が未来' => [
                'setUp' => static function (SendAlertEmailsTest $case) {
                    $device = Device::inRandomOrder()->first();
                    $device->update([
                        'name' => '時間切れ端末',
                        'reported_at' => now()->subHour()->subMinute()->timestamp
                    ]);
                    $device->rule()->update([
                        'time_limits' => 1,
                        'notify_times' => 3,
                    ]);
                    Contact::factory()->for($device->ownerUser)->hasAttached($device)->emailVerified()->create();

                    // アラート生成、次回通知日時は10分後
                    $case->deviceRepo->issueAlert($device->id);
                    Alert::where('device_id', $device->id)->first()->update([
                        'next_notify_at' => now()->addMinutes(10)->timestamp,
                    ]);

                    return [
                        'expectedDevices' => [$device],
                        'arguments' => []
                    ];
                },
                'assertion' => static function (array $expectedDevices, SendAlertEmailsTest $case) {
                    $device = $expectedDevices[0];
                    $actualAlerts = Alert::where('device_id', $device->id)->get();
                    self::assertCount(1, $actualAlerts);

                    // 更新なし
                    $alert = $actualAlerts[0];
                    self::assertSame(0, $alert->notify_count);
                    self::assertLessThan(
                        now()->addMinutes(config('specs.send_alert_interval'))->timestamp,
                        $alert->next_notify_at
                    );

                    Notification::assertNothingSent();

                    Event::assertDispatched(MessageLogged::class, static function ($log) {
                        return $log->level === 'info'
                            && $log->message === 'No notifiable alerts.';
                    });
                },
            ],
            '通知先複数' => [
                'setUp' => static function (SendAlertEmailsTest $case) {
                    $device = Device::inRandomOrder()->first();
                    $device->update([
                        'name' => '時間切れ端末',
                        'reported_at' => now()->subHour()->subMinute()->timestamp
                    ]);
                    $device->rule()->update([
                        'time_limits' => 1,
                        'notify_times' => 3,
                    ]);
                    Contact::factory()->count(2)
                        ->for($device->ownerUser)
                        ->hasAttached($device)
                        ->emailVerified()
                        ->create();

                    // アラート生成、通知回数アップ
                    $case->deviceRepo->issueAlert($device->id);
                    Alert::where('device_id', $device->id)->first()->update(['notify_count' => 1,]);

                    return [
                        'expectedDevices' => [$device],
                        'arguments' => []
                    ];
                },
                'assertion' => static function (array $expectedDevices, SendAlertEmailsTest $case) {
                    $device = $expectedDevices[0];
                    $actualAlerts = Alert::where('device_id', $device->id)->get();
                    self::assertCount(1, $actualAlerts);

                    $alert = $actualAlerts[0];
                    self::assertSame(2, $alert->notify_count);
                    self::assertSame(
                        now()->addMinutes(config('specs.send_alert_interval'))->timestamp,
                        $alert->next_notify_at
                    );

                    // 端末所持ユーザーとすべての通知先に送信
                    Notification::assertSentTo($device->ownerUser, AlertNotification::class);
                    foreach ($device->contacts as $contact) {
                        Notification::assertSentTo($contact, AlertNotification::class);
                    }
                },
            ],
            '通知先未承認' => [
                'setUp' => static function (SendAlertEmailsTest $case) {
                    $device = Device::inRandomOrder()->first();
                    $device->update([
                        'name' => '時間切れ端末',
                        'reported_at' => now()->subHour()->subMinute()->timestamp
                    ]);
                    $device->rule()->update([
                        'time_limits' => 1,
                        'notify_times' => 3,
                    ]);
                    // 片方のみ未承認
                    Contact::factory()->count(2)
                        ->for($device->ownerUser)
                        ->hasAttached($device)
                        ->state(
                            new Sequence(
                                ['email_verified_at' => null],
                                ['email_verified_at' => now()->timestamp]
                            )
                        )
                        ->create();

                    // アラート生成、通知回数アップ
                    $case->deviceRepo->issueAlert($device->id);
                    Alert::where('device_id', $device->id)->first()->update(['notify_count' => 1,]);

                    return [
                        'expectedDevices' => [$device],
                        'arguments' => []
                    ];
                },
                'assertion' => static function (array $expectedDevices, SendAlertEmailsTest $case) {
                    $device = $expectedDevices[0];
                    $actualAlerts = Alert::where('device_id', $device->id)->get();
                    self::assertCount(1, $actualAlerts);

                    $alert = $actualAlerts[0];
                    self::assertSame(2, $alert->notify_count);
                    self::assertSame(
                        now()->addMinutes(config('specs.send_alert_interval'))->timestamp,
                        $alert->next_notify_at
                    );

                    // 端末所持ユーザーと承認済みの通知先に送信
                    Notification::assertSentTo($device->ownerUser, AlertNotification::class);
                    foreach ($device->contacts as $index => $contact) {
                        if ($index === 0) {
                            Notification::assertNotSentTo($contact, AlertNotification::class);
                        } else {
                            Notification::assertSentTo($contact, AlertNotification::class);
                        }
                    }

                    Event::assertDispatched(MessageLogged::class, static function ($log) {
                        return $log->level === 'warning'
                            && $log->message === 'The alert has deleted (or not verified) contacts.';
                    });
                },
            ],
            '端末所持ユーザー無効' => [
                'setUp' => static function (SendAlertEmailsTest $case) {
                    $device = Device::inRandomOrder()->first();
                    $device->update([
                        'name' => '時間切れ端末',
                        'reported_at' => now()->subHour()->subMinute()->timestamp
                    ]);
                    $device->rule()->update([
                        'time_limits' => 1,
                        'notify_times' => 3,
                    ]);
                    Contact::factory()->for($device->ownerUser)->hasAttached($device)->emailVerified()->create();

                    // アラート生成、ユーザー削除
                    $case->deviceRepo->issueAlert($device->id);
                    $device->ownerUser->delete();

                    return [
                        'expectedDevices' => [$device],
                        'arguments' => []
                    ];
                },
                'assertion' => static function (array $expectedDevices, SendAlertEmailsTest $case) {
                    $device = $expectedDevices[0];
                    $actualAlerts = Alert::where('device_id', $device->id)->get();
                    self::assertCount(1, $actualAlerts);

                    // 次回通知に向けて更新 (**仕様変更の可能性あり**)
                    $alert = $actualAlerts[0];
                    self::assertSame(1, $alert->notify_count);
                    self::assertSame(
                        now()->addMinutes(config('specs.send_alert_interval'))->timestamp,
                        $alert->next_notify_at
                    );

                    Notification::assertNothingSent();

                    Event::assertDispatched(MessageLogged::class, static function ($log) {
                        return $log->level === 'warning'
                            && $log->message === 'The alert includes invalid user.';
                    });
                },
            ],
            'データなし' => [
                'setUp' => static function (SendAlertEmailsTest $case) {
                    return [
                        'expectedDevices' => [],
                        'arguments' => []
                    ];
                },
                'assertion' => static function (array $expectedDevices, SendAlertEmailsTest $case) {
                    self::assertCount(0, Alert::all());
                    Notification::assertNothingSent();
                    Event::assertDispatched(MessageLogged::class, static function ($log) {
                        return $log->level === 'info'
                            && $log->message === 'No notifiable alerts.';
                    });
                },
            ],
            '件数指定実行' => [
                'setUp' => static function (SendAlertEmailsTest $case) {
                    $devices = Device::inRandomOrder()->take(3)->get()->sortBy('id');
                    foreach ($devices as $index => $device) {
                        $device->update([
                            'name' => '時間切れ端末',
                            'reported_at' => now()->subHour()->subMinute()->timestamp
                        ]);
                        $device->rule()->update([
                            'time_limits' => 1,
                            'notify_times' => 10,
                        ]);
                        Contact::factory()->for($device->ownerUser)->hasAttached($device)->emailVerified()->create();

                        // アラート生成、次回通知時間をずらす
                        $case->deviceRepo->issueAlert($device->id);
                        Alert::where('device_id', $device->id)->first()->update([
                            'next_notify_at' => now()->subMinutes(10)->addMinutes($index)->timestamp,
                            'notify_count' => 1
                        ]);
                    }

                    return [
                        'expectedDevices' => $devices->all(),
                        'arguments' => ['limit' => 1]
                    ];
                },
                'assertion' => static function (array $expectedDevices, SendAlertEmailsTest $case) {
                    $devices = collect($expectedDevices);
                    $actualAlerts = Alert::whereIn('device_id', $devices->pluck('id')->all())->get();
                    self::assertCount(3, $actualAlerts);

                    // 1件目のみ送信され次回通知に向けて更新
                    foreach ($devices as $index => $device) {
                        $alert = $actualAlerts->where('device_id', $device->id)->first();
                        self::assertSame($device->rule->notify_times, $alert->max_notify_count);

                        if ($index === 0) {
                            self::assertSame(2, $alert->notify_count);
                            self::assertSame(
                                now()->addMinutes(config('specs.send_alert_interval'))->timestamp,
                                $alert->next_notify_at
                            );
                            Notification::assertSentTo($device->ownerUser, AlertNotification::class);
                            Notification::assertSentTo($device->contacts()->first(), AlertNotification::class);
                        } else {
                            self::assertSame(1, $alert->notify_count);
                            self::assertLessThanOrEqual(
                                now()->timestamp,
                                $alert->next_notify_at
                            );
                            Notification::assertNotSentTo($device->ownerUser, AlertNotification::class);
                            Notification::assertNotSentTo($device->contacts()->first(), AlertNotification::class);
                        }
                    }
                },
            ],
        ];
    }
}
