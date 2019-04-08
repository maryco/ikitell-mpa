<?php

namespace App\Console\Commands;

use App\Models\Repositories\DeviceRepositoryInterface;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class WatchDevices extends Command
{
    /**
     * The limit of select devices per process.
     */
    const SELECT_LIMIT = 10;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'device:watch {limit?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Watching devices, and issue the alerts or be a suspend mode.';

    /**
     * @var DeviceRepositoryInterface
     */
    protected $deviceRepo;
    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(DeviceRepositoryInterface $deviceRepo)
    {
        parent::__construct();

        $this->deviceRepo = $deviceRepo;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        /**
         * NOTE:
         * - Select active devices.
         *
         * - Update device to the suspending or alerting,
         * by the result of checking device status.
         *
         * - Issue alerts if found device of need alerting.
         */

        $devices = $this->deviceRepo->getForInspection($this->argument('limit') ?? self::SELECT_LIMIT);

        if (count($devices) === 0) {
            Log::info('No devices for need inspection.');
        }

        $report = [];

        foreach ($devices as $device) {
            if (!$device->rule) {
                Log::warning('The device has no rule settings. [%device]', ['%device' => $device->id]);
                continue;
            }

            if (!$device->ownerUser) {
                Log::warning('The device has no owner user. [%device]', ['%device' => $device->id]);
                continue;
            }

            if ($device->isSuspend()) {
                $res = $this->deviceRepo->beginSuspend($device->id);
                $report['Begin suspend'][] = ['id' => $device->id, 'result' => $res];

                continue;
            }

            if ($device->isTimeOver($device->rule->time_limits)) {
                $res = $this->deviceRepo->issueAlert($device->id);
                $report['Issue alert'][] = ['id' => $device->id, 'result' => $res];
            }
        }

        Log::info('WatchDevice result report []', ['' => $report]);

        return;
    }
}
