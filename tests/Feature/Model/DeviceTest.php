<?php

namespace Tests\Feature\Model;

use App\Models\Entities\Device;
use Carbon\Carbon;
use Tests\TestCase;

class DeviceTest extends TestCase
{
    /**
     * Test for isSuspend() return false case.
     */
    public function testIsNotSuspend()
    {
        $device = Device::factory()->make([
            'in_suspend' => true,
            'suspend_start_at' => null,
            'suspend_end_at' => null,
        ]);

        $this->assertFalse($device->isSuspend());

        $device = Device::factory()->make([
            'in_suspend' => true,
            'suspend_start_at' => Carbon::now()->addDay(1),
            'suspend_end_at' => null,
        ]);

        $this->assertFalse($device->isSuspend());

        $device = Device::factory()->make([
            'in_suspend' => true,
            'suspend_start_at' => Carbon::now()->addDay(-3),
            'suspend_end_at' => Carbon::now()->addDay(-1),
        ]);

        $this->assertFalse($device->isSuspend());
    }

    /**
     * Test for isSuspend() return true case.
     */
    public function testIsSuspend()
    {
        $device = Device::factory()->create([
            'in_suspend' => false,
            'suspend_start_at' => Carbon::now()->addDay(-3),
            'suspend_end_at' => Carbon::now(),
        ]);

        $this->assertTrue($device->isSuspend(), 'Device is in suspend term.');
        $device->forceDelete();

        $device = Device::factory()->create([
            'in_suspend' => false,
            'suspend_start_at' => Carbon::now(),
            'suspend_end_at' => Carbon::now(),
        ]);

        \Log::debug(
            'Suspend [%from] - [%to]',
            ['%from', $device->suspend_start_at, '%to' => $device->suspend_end_at]
        );

        $this->assertTrue($device->isSuspend(), 'Device is one day suspend.');
        $device->forceDelete();

        $device = Device::factory()->make([
            'in_suspend' => false,
            'suspend_start_at' => Carbon::now()->addDay(-7),
            'suspend_end_at' => null,
        ]);

        $this->assertTrue($device->isSuspend(), 'Device is suspended since before 7 days.');

        $device = Device::factory()->make([
            'in_suspend' => false,
            'suspend_start_at' => null,
            'suspend_end_at' => Carbon::now()->addDay(1),
        ]);

        $this->assertTrue($device->isSuspend(), 'Device is suspend until tomorrow.');
    }

    /**
     * Test for isTimeOver()
     */
    public function testIsTimeOver()
    {
        $device = Device::factory()->make([
            'reported_at' => null,
        ]);

        $this->assertFalse($device->isTimeOver(1), 'Device never reporting.');

        $device = Device::factory()->make([
            'reported_at' => Carbon::now()
                ->addHours(-1)
                ->addSecond(-1)
                ->getTimestamp(),
        ]);

        $this->assertTrue($device->isTimeOver(1), 'Device will just time over.');

        $device = Device::factory()->make([
            'reported_at' => Carbon::now()
                ->addHours(-1)
                ->getTimestamp(),
        ]);

        $this->assertFalse($device->isTimeOver(1), 'Device report just limit time before.');
    }

    /**
     * Test for enableReport()
     */
    public function testEnableReport()
    {
        $device = Device::factory()->make([
            'reported_at' => null,
        ]);

        $this->assertTrue($device->enableReport(), 'Device never reporting.');

        $device->reported_at = now()->getTimestamp();
        $this->assertFalse($device->enableReport(), 'Too soon to report.');

        $device->reported_at = now()
            ->subMinutes(config('specs.device_report_interval') + 1)
            ->getTimestamp();
        $this->assertTrue($device->enableReport(), 'Almost passed from last report.');
    }
}
