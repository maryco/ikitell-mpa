<?php

namespace Tests\Feature\Console\Commands;

use App\Models\Entities\Alert;
use App\Models\Entities\Device;
use Carbon\Carbon;
use Closure;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Log\Events\MessageLogged;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class WatchDeviceTest extends TestCase
{
    use DatabaseTransactions;

    private const LOG_INFO_MESSAGE = 'WatchDevice inspection report.';

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        parent::setUp();

        Event::fake(MessageLogged::class);
        $this->createUserWithDevices(10);
    }

    /**
     * @param Closure $setUp
     * @param Closure $expectedLog
     * @param Closure $assertion
     * @return void
     *
     * @dataProvider data_inspect_target
     */
    public function test_get_inspect_target(Closure $setUp, Closure $expectedLog, Closure $assertion): void
    {
        $parameters = $setUp($this);
        $this->artisan('device:watch', $parameters)->assertSuccessful();
        $assertion($this);

        $log = $expectedLog();
        Event::assertDispatched(static function (MessageLogged $logged) use ($log) {
            return $logged->message === $log['message']
                && $logged->context === $log['context']
                && $logged->level === $log['level'];
        });
    }

    /**
     * @return array
     */
    public static function data_inspect_target(): array
    {
        return [
            '有効端末10件' => [
                'setUp' => static function (WatchDeviceTest $case) {
                    return [];
                },
                'expectedLog' => function () {
                    $ids = Device::select('id')->oldest('id')->get()->pluck('id')->all();
                    return [
                        'message' => self::LOG_INFO_MESSAGE,
                        'context' => [
                            'details' => [
                                'begin_suspend' => [],
                                'issue_alert' => [],
                                'ok' => $ids,
                            ]],
                        'level' => 'info',
                    ];
                },
                'assertion' => static function (WatchDeviceTest $case) {
                    self::assertCount(10, Device::all());
                }
            ],
            '有効端末11件' => [
                'setUp' => static function (WatchDeviceTest $case) {
                    $case->createUserWithDevices(1);
                    return [];
                },
                'expectedLog' => function () {
                    // 取得対象は10件まで
                    $ids = Device::select('id')->oldest('id')->take(10)->pluck('id')->all();
                    return [
                        'message' => self::LOG_INFO_MESSAGE,
                        'context' => ['details' => [
                            'begin_suspend' => [],
                            'issue_alert' => [],
                            'ok' => $ids,
                        ]],
                        'level' => 'info',
                    ];
                },
                'assertion' => static function (WatchDeviceTest $case) {
                    self::assertCount(11, Device::all());
                }
            ],
            '件数指定実行' => [
                'setUp' => static function (WatchDeviceTest $case) {
                    return ['limit' => 3];
                },
                'expectedLog' => function () {
                    $ids = Device::select('id')->oldest('id')->take(3)->pluck('id')->all();
                    return [
                        'message' => self::LOG_INFO_MESSAGE,
                        'context' => [
                            'details' => [
                                'begin_suspend' => [],
                                'issue_alert' => [],
                                'ok' => $ids,
                            ]],
                        'level' => 'info',
                    ];
                },
                'assertion' => static function (WatchDeviceTest $case) {
                    self::assertCount(10, Device::all());
                }
            ],
            '無効端末(所有ユーザー未承認)' => [
                'setUp' => static function (WatchDeviceTest $case) {
                    Device::inRandomOrder()->first()->ownerUser()->update(['email_verified_at' => null]);
                    return [];
                },
                'expectedLog' => function () {
                    // 所有ユーザー未承認の端末が含まれていないこと
                    $ids = Device::whereHas('ownerUser', static fn($q) => $q->whereNotNull('email_verified_at'))
                        ->select('id')->oldest('id')->get()
                        ->pluck('id')->all();
                    return [
                        'message' => self::LOG_INFO_MESSAGE,
                        'context' => [
                            'details' => [
                                'begin_suspend' => [],
                                'issue_alert' => [],
                                'ok' => $ids,
                            ]],
                        'level' => 'info',
                    ];
                },
                'assertion' => static function (WatchDeviceTest $case) {
                    self::assertCount(10, Device::all());
                }
            ],
            '無効端末(所有ユーザーBAN)' => [
                'setUp' => static function (WatchDeviceTest $case) {
                    Device::inRandomOrder()->first()->ownerUser()->update(['ban' => 1]);
                    return [];
                },
                'expectedLog' => function () {
                    // 所有ユーザーBANの端末が含まれていないこと
                    $ids = Device::whereHas('ownerUser', static fn($q) => $q->where('ban', 0))
                        ->select('id')->oldest('id')->get()
                        ->pluck('id')->all();
                    return [
                        'message' => self::LOG_INFO_MESSAGE,
                        'context' => [
                            'details' => [
                                'begin_suspend' => [],
                                'issue_alert' => [],
                                'ok' => $ids,
                            ]],
                        'level' => 'info',
                    ];
                },
                'assertion' => static function (WatchDeviceTest $case) {
                    self::assertCount(10, Device::all());
                }
            ],
            '休止中端末' => [
                'setUp' => static function (WatchDeviceTest $case) {
                    Device::inRandomOrder()->first()->update(['in_suspend' => 1]);
                    return [];
                },
                'expectedLog' => function () {
                    // 休止中端末が含まれていないこと
                    $ids = Device::select('id')->where('in_suspend', 0)->oldest('id')->get()->pluck('id')->all();
                    return [
                        'message' => self::LOG_INFO_MESSAGE,
                        'context' => [
                            'details' => [
                                'begin_suspend' => [],
                                'issue_alert' => [],
                                'ok' => $ids,
                            ]],
                        'level' => 'info',
                    ];
                },
                'assertion' => static function (WatchDeviceTest $case) {
                    $actual = Device::all();
                    self::assertCount(10, $actual);
                    self::assertSame(1, $actual->where('in_suspend', 1)->count());
                }
            ],
            '警報中端末' => [
                'setUp' => static function (WatchDeviceTest $case) {
                    Device::inRandomOrder()->first()->update(['in_alert' => 1]);
                    return [];
                },
                'expectedLog' => function () {
                    // 警報中端末が含まれていないこと
                    $ids = Device::select('id')->where('in_alert', 0)->oldest('id')->get()->pluck('id')->all();
                    return [
                        'message' => self::LOG_INFO_MESSAGE,
                        'context' => [
                            'details' => [
                                'begin_suspend' => [],
                                'issue_alert' => [],
                                'ok' => $ids,
                            ]],
                        'level' => 'info',
                    ];
                },
                'assertion' => static function (WatchDeviceTest $case) {
                    $actual = Device::all();
                    self::assertCount(10, $actual);
                    self::assertSame(1, $actual->where('in_alert', 1)->count());
                }
            ],
            '端末なし' => [
                'setUp' => static function (WatchDeviceTest $case) {
                    Device::whereNull('deleted_at')->delete();
                    return [];
                },
                'expectedLog' => function () {
                    return [
                        'message' => self::LOG_INFO_MESSAGE,
                        'context' => [
                            'details' => [
                                'begin_suspend' => [],
                                'issue_alert' => [],
                                'ok' => [],
                            ]],
                        'level' => 'info',
                    ];
                },
                'assertion' => static function (WatchDeviceTest $case) {
                    self::assertCount(0, Device::all());
                }
            ],
            '異常, ルールなし端末' => [
                'setUp' => static function (WatchDeviceTest $case) {
                    Device::inRandomOrder()->first()->update(['rule_id' => null]);
                    return [];
                },
                'expectedLog' => function () {
                    $device = Device::whereNull('rule_id')->first();
                    return [
                        'message' => 'The device has no rule.',
                        'context' => ['deviceId' => $device->id],
                        'level' => 'warning',
                    ];
                },
                'assertion' => static function (WatchDeviceTest $case) {
                    self::assertCount(10, Device::all());
                }
            ],
        ];
    }

    /**
     * @param Closure $setUp
     * @param Closure $assertion
     * @param Closure $expectedLog
     * @return void
     *
     * @dataProvider data_issue_alert
     */
    public function test_issue_alert(Closure $setUp, Closure $assertion, Closure $expectedLog): void
    {
        $setUp();
        $this->artisan('device:watch')->assertSuccessful();
        $assertion();

        $log = $expectedLog();
        Event::assertDispatched(static function (MessageLogged $logged) use ($log) {
            return $logged->message === $log['message']
                && $logged->context === $log['context']
                && $logged->level === $log['level'];
        });
    }

    /**
     * @return array
     */
    public static function data_issue_alert(): array
    {
        return [
            '時間切れ閾値' => [
                'setUp' => static function () {
                    $device = Device::inRandomOrder()->first();
                    $device->update([
                        'name' => '時間切れ閾値端末',
                        'reported_at' => now()->subHour()->timestamp
                    ]);
                    $device->rule()->update(['time_limits' => 1]);
                },
                'assertion' => static function () {
                    $devices = Device::all();
                    self::assertCount(10, $devices);

                    $alerts = Alert::all();
                    self::assertCount(0, $alerts);
                    self::assertFalse((bool)$devices->where('name', '時間切れ閾値端末')->first()->in_alert);
                },
                'expectedLog' => function () {
                    $ids = Device::select('id')->oldest('reported_at')->oldest('id')->get()->pluck('id')->all();
                    return [
                        'message' => self::LOG_INFO_MESSAGE,
                        'context' => [
                            'details' => [
                                'begin_suspend' => [],
                                'issue_alert' => [],
                                'ok' => $ids,
                            ]],
                        'level' => 'info',
                    ];
                },
            ],
            '時間切れ端末1件' => [
                'setUp' => static function () {
                    $device = Device::inRandomOrder()->first();
                    $device->update([
                        'name' => '時間切れ端末',
                        'reported_at' => now()->subHour()->subMinute()->timestamp
                    ]);
                    $device->rule()->update(['time_limits' => 1]);
                },
                'assertion' => static function () {
                    $devices = Device::all();
                    self::assertCount(10, $devices);

                    $alerts = Alert::all();
                    $device = $devices->where('id', $alerts[0]->device_id)->first();
                    self::assertCount(1, $alerts);
                    self::assertSame('時間切れ端末', $device->name);
                    self::assertTrue((bool)$device->in_alert);
                },
                'expectedLog' => function () {
                    $devices = Device::oldest('id')->get();
                    $alertDevice = $devices->where('name', '時間切れ端末')->first();
                    $alertLog = ['id' => $alertDevice->id, 'result' => true];
                    return [
                        'message' => self::LOG_INFO_MESSAGE,
                        'context' => [
                            'details' => [
                                'begin_suspend' => [],
                                'issue_alert' => [$alertLog],
                                'ok' => $devices->except($alertDevice->id)->pluck('id')->all(),
                            ]
                        ],
                        'level' => 'info',
                    ];
                },
            ],
            '時間切れ端末複数件' => [
                'setUp' => static function () {
                    $devices = Device::inRandomOrder()->take(2)->get();
                    $devices->each(function ($device) {
                        $device->update([
                            'name' => '時間切れ端末',
                            'reported_at' => now()->subHour()->subMinute()->timestamp
                        ]);
                        $device->rule()->update(['time_limits' => 1]);
                    });
                },
                'assertion' => static function () {
                    $devices = Device::all();
                    self::assertCount(10, $devices);

                    $alerts = Alert::all();
                    self::assertCount(2, $alerts);
                    self::assertTrue($alerts->every(fn($a) => $a->device->name === '時間切れ端末'));
                    self::assertTrue($alerts->every(fn($a) => (bool)$a->device->in_alert === true));
                },
                'expectedLog' => function () {
                    $devices = Device::oldest('reported_at')->oldest('id')->get();
                    $alertDevices = $devices->filter(fn($d) => $d->name === '時間切れ端末');
                    $alertLogs = $alertDevices->map(fn($d) => ['id' => $d->id, 'result' => true])->values();
                    return [
                        'message' => self::LOG_INFO_MESSAGE,
                        'context' => [
                            'details' => [
                                'begin_suspend' => [],
                                'issue_alert' => $alertLogs->all(),
                                'ok' => $devices->except($alertDevices->pluck('id')->all())->pluck('id')->all(),
                            ]
                        ],
                        'level' => 'info',
                    ];
                },
            ],
            '時間切れ端末, ルール設定のメッセージID不正' => [
                'setUp' => static function () {
                    $device = Device::inRandomOrder()->first();
                    $device->update([
                        'name' => '時間切れ端末',
                        'reported_at' => now()->subHour()->subMinute()->timestamp
                    ]);
                    $device->rule()->update([
                        'time_limits' => 1,
                        'message_id' => 123456,
                    ]);
                },
                'assertion' => static function () {
                    $devices = Device::all();
                    self::assertCount(10, $devices);

                    $alerts = Alert::all();
                    self::assertCount(0, $alerts);
                },
                'expectedLog' => function () {
                    $devices = Device::oldest('reported_at')->oldest('id')->get();
                    $alertDevice = $devices->where('name', '時間切れ端末')->first();
                    $alertLog = ['id' => $alertDevice->id, 'result' => false];
                    return [
                        'message' => self::LOG_INFO_MESSAGE,
                        'context' => [
                            'details' => [
                                'begin_suspend' => [],
                                'issue_alert' => [$alertLog],
                                'ok' => $devices->except($alertDevice->id)->pluck('id')->all(),
                            ]
                        ],
                        'level' => 'info',
                    ];
                },
            ],
        ];
    }

    /**
     * @param Closure $setUp
     * @param Closure $assertion
     * @param Closure $expectedLog
     * @return void
     *
     * @dataProvider data_begin_suspend
     */
    public function test_begin_suspend(Closure $setUp, Closure $assertion, Closure $expectedLog): void
    {
        $expectedDevices = $setUp();
        $this->artisan('device:watch')->assertSuccessful();
        $assertion($expectedDevices);

        $log = $expectedLog();
        Event::assertDispatched(static function (MessageLogged $logged) use ($log) {
            return $logged->message === $log['message']
                && $logged->context === $log['context']
                && $logged->level === $log['level'];
        });
        Carbon::setTestNow();
    }

    /**
     * @return array<string, mixed>
     */
    public static function data_begin_suspend(): array
    {
        return [
            '対象1件, 休止開始・終了指定あり' => [
                'setUp' => static function () {
                    Carbon::setTestNow('2024-02-04 10:36');
                    $device = Device::inRandomOrder()->first();
                    $device->update([
                        'name' => '休止端末',
                        'suspend_start_at' => now()->subDay(),
                        'suspend_end_at' => now()->addDays(3)
                    ]);
                    return [$device];
                },
                'assertion' => static function (array $expectedDevices) {
                    $devices = Device::all();
                    self::assertCount(10, $devices);

                    $expected = $expectedDevices[0];
                    $actual = $devices->where('id', $expected->id)->first();
                    self::assertTrue((bool)$actual->in_suspend);
                    self::assertSame(
                        now()->addDays(3)->setTime(23, 59, 59)->timestamp,
                        $actual->report_reserved_at
                    );
                },
                'expectedLog' => function () {
                    $devices = Device::oldest('reported_at')->oldest('id')->get();
                    $suspendDevices = $devices->where('in_suspend', 1);
                    $suspendLog = [
                        'id' => $suspendDevices->first()->id,
                        'result' => true,
                    ];
                    return [
                        'message' => self::LOG_INFO_MESSAGE,
                        'context' => [
                            'details' => [
                                'begin_suspend' => [$suspendLog],
                                'issue_alert' => [],
                                'ok' => $devices->except($suspendDevices->pluck('id')->all())->pluck('id')->all(),
                            ]],
                        'level' => 'info',
                    ];
                },
            ],
            '対象1件, 休止開始のみ' => [
                'setUp' => static function () {
                    Config::set('specs.reserve_report_day_forever_suspend', 60);
                    Carbon::setTestNow('2024-02-04 10:36');
                    $device = Device::inRandomOrder()->first();
                    $device->update([
                        'name' => '休止端末',
                        'suspend_start_at' => now()->subDay(),
                        'suspend_end_at' => null
                    ]);
                    return [$device];
                },
                'assertion' => static function (array $expectedDevices) {
                    $devices = Device::all();
                    self::assertCount(10, $devices);

                    $expected = $expectedDevices[0];
                    $actual = $devices->where('id', $expected->id)->first();
                    self::assertTrue((bool)$actual->in_suspend);
                    // FIXME: 時間の23:59指定が効いていない?
                    self::assertSame(now()->addDays(60)->timestamp, $actual->report_reserved_at);
                },
                'expectedLog' => function () {
                    $devices = Device::oldest('reported_at')->oldest('id')->get();
                    $suspendDevices = $devices->where('in_suspend', 1);
                    $suspendLog = [
                        'id' => $suspendDevices->first()->id,
                        'result' => true,
                    ];
                    return [
                        'message' => self::LOG_INFO_MESSAGE,
                        'context' => [
                            'details' => [
                                'begin_suspend' => [$suspendLog],
                                'issue_alert' => [],
                                'ok' => $devices->except($suspendDevices->pluck('id')->all())->pluck('id')->all(),
                            ]],
                        'level' => 'info',
                    ];
                },
            ],
            '対象1件, 休止終了のみ' => [
                'setUp' => static function () {
                    Config::set('specs.reserve_report_day_forever_suspend', 60);
                    Carbon::setTestNow('2024-02-04 10:36');
                    $device = Device::inRandomOrder()->first();
                    $device->update([
                        'name' => '休止端末',
                        'suspend_start_at' => null,
                        'suspend_end_at' => now()->addDays(7),
                    ]);
                    return [$device];
                },
                'assertion' => static function (array $expectedDevices) {
                    $devices = Device::all();
                    self::assertCount(10, $devices);

                    $expected = $expectedDevices[0];
                    $actual = $devices->where('id', $expected->id)->first();
                    self::assertTrue((bool)$actual->in_suspend);
                    self::assertSame(
                        now()->addDays(7)->setTime(23, 59, 59)->timestamp,
                        $actual->report_reserved_at
                    );
                },
                'expectedLog' => function () {
                    $devices = Device::oldest('reported_at')->oldest('id')->get();
                    $suspendDevices = $devices->where('in_suspend', 1);
                    $suspendLog = [
                        'id' => $suspendDevices->first()->id,
                        'result' => true,
                    ];
                    return [
                        'message' => self::LOG_INFO_MESSAGE,
                        'context' => [
                            'details' => [
                                'begin_suspend' => [$suspendLog],
                                'issue_alert' => [],
                                'ok' => $devices->except($suspendDevices->pluck('id')->all())->pluck('id')->all(),
                            ]],
                        'level' => 'info',
                    ];
                },
            ],
            '対象複数件' => [
                'setUp' => static function () {
                    Carbon::setTestNow('2024-02-04 11:37');
                    $devices = Device::inRandomOrder()->take(2)->get();
                    foreach ($devices as $device) {
                        $device->update([
                            'name' => '休止端末',
                            'suspend_start_at' => now()->subDay(),
                            'suspend_end_at' => now()->addDays($device->id),
                        ]);
                    }
                    return $devices->all();
                },
                'assertion' => static function (array $expectedDevices) {
                    $devices = Device::all();
                    self::assertCount(10, $devices);

                    $actualDevices = $devices->whereIn('id', Arr::pluck($expectedDevices, 'id'));
                    foreach ($actualDevices as $actual) {
                        self::assertTrue((bool)$actual->in_suspend);
                        self::assertSame(
                            now()->addDays($actual->id)->setTime(23, 59, 59)->timestamp,
                            $actual->report_reserved_at
                        );
                    }
                },
                'expectedLog' => function () {
                    $devices = Device::oldest('reported_at')->oldest('id')->get();
                    $suspendDevices = $devices->where('in_suspend', 1);
                    $suspendLogs = $suspendDevices
                        ->map(fn($d) => ['id' => $d->id, 'result' => true])
                        ->values()->all();
                    return [
                        'message' => self::LOG_INFO_MESSAGE,
                        'context' => [
                            'details' => [
                                'begin_suspend' => $suspendLogs,
                                'issue_alert' => [],
                                'ok' => $devices->except($suspendDevices->pluck('id')->all())->pluck('id')->all(),
                            ]],
                        'level' => 'info',
                    ];
                },
            ],
        ];
    }

    public function test_fail(): void
    {
        $this->markTestSkipped('WIP');
    }
}
