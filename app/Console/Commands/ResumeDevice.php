<?php

namespace App\Console\Commands;

use App\Models\Entities\Device;
use App\Models\Repositories\DeviceRepositoryInterface;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class ResumeDevice extends Command
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
    protected $signature = 'device:resume {limit?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Pick the report reserved devices, and resume from suspend mode.';

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
        $devices = $this->deviceRepo->getForResume($this->argument('limit') ?? self::SELECT_LIMIT);

        if (count($devices) === 0) {
            Log::info('No devices for be resume.');
        }

        foreach ($devices as $device) {

            if (!$this->deviceRepo->endSuspend($device->id)) {
                Log::error('Failed to resume [%device]', ['%device' => $device->id]);
                return;
            }

            if ($device->ownerUser) {
                $device->ownerUser->sendDeviceResumedNotification($device);
            }
        }

        return;
    }
}
