<?php

namespace App\Http\Controllers;

use App\Models\Entities\Alert;
use App\Models\Entities\Device;
use App\Models\Entities\DeviceDashboard;
use App\Models\Entities\User;
use App\Models\Repositories\DeviceRepositoryInterface;
use App\Models\Repositories\MessageRepositoryInterface;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use SebastianBergmann\CodeCoverage\Report\Html\Dashboard;

class LabController extends Controller
{
    /**
     * @var DeviceRepositoryInterface
     */
    private $deviceRepo;

    /**
     * @var MessageRepositoryInterface
     */
    private $messageRepo;

    /**
     * LabController constructor.
     * @param DeviceRepositoryInterface $deviceRepo
     * @param MessageRepositoryInterface $messageRepo
     */
    public function __construct(
        DeviceRepositoryInterface $deviceRepo,
        MessageRepositoryInterface $messageRepo
    ) {
        $this->deviceRepo = $deviceRepo;
        $this->messageRepo = $messageRepo;
    }

    /**
     * Show device detail view with the mock device.
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function mock()
    {
        $mockDevice = $this->deviceRepo->makeMock([
            'reported_at' => now()->subDays(1)->getTimestamp(),
            'in_alert' => false
        ]);

        $deviceDetails = [
            (new DeviceDashboard($mockDevice))->setDemo(true)
        ];

        return view('lab.device', compact('deviceDetails'));
    }

    /**
     * Device lab
     * NOTE: Using the 'basic user' of seeding for a test if not authenticated.
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function device()
    {
        $user = (Auth::guest())
            ? User::email(\SeederBase::BASIC_USER)->first()
            : Auth::user();

        $deviceDetails = $this->deviceRepo->getDashboard($user);

        return view('lab.device', compact('deviceDetails'));
    }

    /**
     * Do report the specified device.
     *
     * @param $deviceId
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function deviceReport($deviceId)
    {
        // TODO: Implements
        return redirect(route('lab.device'));
    }

    /**
     * Force issue the alerts to the specified device.
     *
     * @param $deviceId
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function issueAlert($deviceId)
    {
        $device = $this->deviceRepo->findByUser(Auth::user(), $deviceId);

        if (!$device) {
            Log::error('Not found target device [%id]', ['%id' => $deviceId]);
        }

        // Update reported_at to the time over value.
        $device->reported_at = Carbon::now()
            ->addHour(-1 * ($device->rule->time_limits + 1))->getTimestamp();
        $device->save();

        $this->deviceRepo->issueAlert($device->id);

        return redirect(route('lab.device'));
    }

    /**
     * Return the specified mail body as a markup.
     *
     * @param Request $request
     * @return mixed
     * @throws \HttpException
     */
    public function mailPreview(Request $request)
    {
        $message = $this->messageRepo->findById(
            $request->get('message_id') ?? config('alert.default_template_id'),
            null
        );

        if (!$message) {
            abort(404);
        }

        if (!$message->buildContentMock()) {
            abort(404);
        }

        return $message->renderAsMarkDown();
    }
}
