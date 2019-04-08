<?php

namespace App\Listeners;

use App\Models\Repositories\DeviceRepositoryInterface;
use Illuminate\Auth\Events\Verified;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;

class MarkDeviceReported
{
    /**
     * @var DeviceRepositoryInterface
     */
    protected $deviceRepo;

    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct(DeviceRepositoryInterface $deviceRepo)
    {
        $this->deviceRepo = $deviceRepo;
    }

    /**
     * Handle the event.
     *
     * @param Verified $event
     * @return void
     */
    public function handle(Verified $event)
    {
        if ($event->user->hasVerifiedEmail() && ! $event->user->isLimited()) {

            $devices = $this->deviceRepo->getByUser($event->user);

            foreach ($devices as $device) {
                $this->deviceRepo->report(
                    $event->user,
                    $device->id,
                    config('codes.report_types.system')
                );
            }
        }
    }
}
