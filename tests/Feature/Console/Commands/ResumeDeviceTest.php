<?php

namespace Tests\Feature\Console\Commands;

use App\Models\Entities\Device;
use App\Models\Entities\DeviceLog;
use App\Models\Repositories\DeviceRepository;
use App\Notifications\DeviceResumedNotification;
use Carbon\Carbon;
use Closure;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Log\Events\MessageLogged;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class ResumeDeviceTest extends TestCase
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
    public function test_sucess(Closure $setUp, Closure $assertion): void
    {
        Notification::fake();
        $data = $setUp($this);

        // 休止中確認
        Device::whereIn('id', Arr::pluck($data['expectedDevices'], 'id'))
            ->get()
            ->each(function ($device) {
                self::assertTrue((bool)$device->in_suspend);
                self::assertNotNull($device->report_reserved_at);
            });

        Carbon::setTestNow(now()->addDays(3));
        $this->artisan('device:resume', $data['arguments'])->assertSuccessful();

        $assertion($data['expectedDevices'], $this);
        Carbon::setTestNow();
    }

    /**
     * @return array<string, mixed>
     */
    public static function data_success(): array
    {
        return [
            '1件' => [
                'setUp' => static function (ResumeDeviceTest $case) {
                    $device = Device::inRandomOrder()->first();
                    $device->update([
                        'name' => '休止中端末',
                        'suspend_end_at' => now()->addMinute()->timestamp,
                    ]);

                    // 休止開始
                    $case->deviceRepo->beginSuspend($device->id);

                    return [
                        'expectedDevices' => [$device],
                        'arguments' => []
                    ];
                },
                'assertion' => static function (array $expectedDevices, ResumeDeviceTest $case) {
                    $actual = Device::find($expectedDevices[0]->id);
                    // 休止解除、レポート強制実行
                    self::assertFalse((bool)$actual->in_suspend);
                    self::assertNull($actual->report_reserved_at);
                    self::assertSame(now()->timestamp, $actual->reported_at);

                    // 端末ログ
                    $logs = DeviceLog::where('device_id', $actual->id)->get();
                    self::assertCount(1, $logs);
                    self::assertSame($actual->owner_id, $logs[0]->user_id);
                    self::assertSame($actual->id, $logs[0]->device_id);
                    self::assertSame(config('codes.report_types.system_resume'), $logs[0]->reporting_type);

                    Notification::assertSentTo($actual->ownerUser, DeviceResumedNotification::class);
                },
            ],
            '複数件' => [
                'setUp' => static function (ResumeDeviceTest $case) {
                    $devices = Device::inRandomOrder()->take(2)->get();
                    foreach ($devices as $index => $device) {
                        $device->update([
                            'name' => '休止中端末',
                            'suspend_end_at' => now()->addDays($index)->timestamp,
                        ]);

                        // 休止開始
                        $case->deviceRepo->beginSuspend($device->id);
                    }

                    return [
                        'expectedDevices' => $devices->all(),
                        'arguments' => []
                    ];
                },
                'assertion' => static function (array $expectedDevices, ResumeDeviceTest $case) {
                    $actualDevices = Device::whereIn('id', Arr::pluck($expectedDevices, 'id'))->get();
                    foreach ($actualDevices as $actual) {
                        // 休止解除、レポート強制実行
                        self::assertFalse((bool)$actual->in_suspend);
                        self::assertNull($actual->report_reserved_at);
                        self::assertSame(now()->timestamp, $actual->reported_at);

                        // 端末ログ
                        $logs = DeviceLog::where('device_id', $actual->id)->get();
                        self::assertCount(1, $logs);
                        self::assertSame($actual->owner_id, $logs[0]->user_id);
                        self::assertSame($actual->id, $logs[0]->device_id);
                        self::assertSame(config('codes.report_types.system_resume'), $logs[0]->reporting_type);

                        Notification::assertSentTo($actual->ownerUser, DeviceResumedNotification::class);
                    }
                },
            ],
            '件数指定実行' => [
                'setUp' => static function (ResumeDeviceTest $case) {
                    $devices = Device::inRandomOrder()->take(2)->get();
                    foreach ($devices as $index => $device) {
                        $device->update([
                            'name' => '休止中端末',
                            'suspend_end_at' => now()->addDays($index)->timestamp,
                        ]);

                        // 休止開始
                        $case->deviceRepo->beginSuspend($device->id);
                    }

                    return [
                        'expectedDevices' => $devices->all(),
                        'arguments' => ['limit' => 1]
                    ];
                },
                'assertion' => static function (array $expectedDevices, ResumeDeviceTest $case) {
                    $actualDevices = Device::whereIn('id', Arr::pluck($expectedDevices, 'id'))->get()
                        ->sortBy('report_reserved_at');
                    self::assertCount(2, $actualDevices);
                    foreach ($actualDevices as $index => $actual) {
                        if ($index === 0) {
                            // 休止解除、レポート強制実行
                            self::assertFalse((bool)$actual->in_suspend);
                            self::assertNull($actual->report_reserved_at);
                            self::assertSame(now()->timestamp, $actual->reported_at);

                            // 端末ログ
                            $logs = DeviceLog::where('device_id', $actual->id)->get();
                            self::assertCount(1, $logs);
                            self::assertSame($actual->owner_id, $logs[0]->user_id);
                            self::assertSame($actual->id, $logs[0]->device_id);
                            self::assertSame(config('codes.report_types.system_resume'), $logs[0]->reporting_type);

                            Notification::assertSentTo($actual->ownerUser, DeviceResumedNotification::class);
                        } else {
                            // 休止未解除
                            self::assertTrue((bool)$actual->in_suspend);
                            self::assertFalse(DeviceLog::where('device_id', $actual->id)->exists());
                            Notification::assertNotSentTo($actual->ownerUser, DeviceResumedNotification::class);
                        }
                    }
                },
            ],
        ];
    }
}
