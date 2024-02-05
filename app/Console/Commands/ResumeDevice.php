<?php

namespace App\Console\Commands;

use App\Console\Commands\Traits\GetArgument;
use App\Models\Repositories\DeviceRepositoryInterface;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class ResumeDevice extends Command
{
    use GetArgument;

    /**
     * The limit of select devices per process.
     */
    public const SELECT_LIMIT = 10;

    protected $signature = 'device:resume {limit?}';

    protected $description = 'Pick report reserved devices, and resume from suspend mode.';

    /**
     * @var DeviceRepositoryInterface
     */
    protected DeviceRepositoryInterface $deviceRepo;

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
     * @return void
     */
    public function handle(): void
    {
        $devices = $this->deviceRepo->getForResume($this->getArgumentInt('limit', self::SELECT_LIMIT));

        if (count($devices) === 0) {
            Log::info('No devices that can be resumed.');
        }

        foreach ($devices as $device) {
            if (!$this->deviceRepo->endSuspend($device->id)) {
                Log::error('Failed to resume', ['deviceId' => $device->id]);
                return;
            }

            if ($device->ownerUser) {
                $device->ownerUser->sendDeviceResumedNotification($device);
            }
        }
    }
}
