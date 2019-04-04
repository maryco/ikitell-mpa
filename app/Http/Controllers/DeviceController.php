<?php

namespace App\Http\Controllers;

use App\Http\Requests\DeviceStoreRequest;
use App\Models\Entities\DeviceDashboard;
use App\Models\Repositories\ContactRepositoryInterface;
use App\Models\Repositories\DeviceRepositoryInterface;
use App\Models\Repositories\RuleRepositoryInterface;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Route;

class DeviceController extends Controller
{
    /**
     * @var DeviceRepositoryInterface
     */
    private $deviceRepo;

    /**
     * @var RuleRepositoryInterface
     */
    private $ruleRepo;

    /**
     * @var ContactRepositoryInterface
     */
    private $contactRepo;

    /**
     * DeviceController constructor.
     *
     * @param DeviceRepositoryInterface $deviceRepo
     * @param RuleRepositoryInterface $ruleRepo
     * @param ContactRepositoryInterface $contactRepo
     */
    public function __construct(
        DeviceRepositoryInterface $deviceRepo,
        RuleRepositoryInterface $ruleRepo,
        ContactRepositoryInterface $contactRepo
    ) {
        $this->middleware('throttle:6,1')->only('device.report');

        $this->deviceRepo = $deviceRepo;
        $this->ruleRepo = $ruleRepo;
        $this->contactRepo = $contactRepo;
    }

    /**
     * Get device list
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function getList()
    {
        $devices = $this->deviceRepo->getByUser(Auth::user(), false, false);

        return view('device.list', compact('devices'));
    }

    /**
     * Show create/edit form.
     *
     * @param null $id
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function showForm($id = null)
    {
        $device = $this->deviceRepo->makeModel(
            ['owner_id' => Auth::id()]
        );

        if (Route::getCurrentRoute()->named('device.edit')) {
            $device = $this->deviceRepo->findByUser(Auth::user(), $id);
            if (!$device) {
                abort('404');
            }
        }

        $rules = DeviceStoreRequest::rulesToArray(
            $this->ruleRepo->getByUserId(Auth::id(), false)
        );

        $contacts = DeviceStoreRequest::contactsToArray(
            $this->contactRepo->getByUserId(Auth::id(), true),
            old('device_notification_targets', Arr::pluck($device->contact->toArray(), 'id'))
        );

        $deviceForm = new DeviceStoreRequest();

        return view('device.form', compact('device', 'rules', 'contacts', 'deviceForm'));
    }

    /**
     * Store the device.
     *
     * @param DeviceStoreRequest $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function store(DeviceStoreRequest $request)
    {
        //Log::debug('REQUEST ALL = []', ['' => $request->all()]);

        $appInfoKey = 'saved';
        $targetId = Route::getCurrentRoute()->parameter('id', null);

        if (Route::getCurrentRoute()->named('device.edit')) {
            $device = $this->deviceRepo->findByUser(Auth::user(), $targetId);

            if (!$device) {
                abort('404');
            }

            $appInfoKey = 'edited';
        }

        $device = $this->deviceRepo->store(
            array_merge($request->onlyForStore(), ['id' => $targetId])
        );

        return redirect(route('device.edit', ['id' => $device->id]))
            ->with($appInfoKey, true);
    }

    /**
     * Delete specific device.
     *
     * @param $deviceId
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function delete($deviceId)
    {
        // TODO: Create policy
        if (Auth::user()->isLimited()) {
            abort('403');
        }

        $device = $this->deviceRepo->findByUser(Auth::user(), $deviceId);

        if (!$device) {
            abort('404');
        }

        $this->deviceRepo->delete($deviceId, Auth::id());

        return redirect(route('device.list'))
            ->with('deleted', true);
    }

    /**
     * Report by the specific device.
     * NOTE: The request accept ajax only.
     *
     * @param $deviceId
     * @return false|string(json)
     */
    public function report($deviceId)
    {
        if (!Request::isJson()) {
            abort(400);
        }

        $device = $this->deviceRepo->findByUser(Auth::user(), $deviceId);

        if (!$device) {
            //Log::debug('Error! Device Not Found! [%id]', ['%id' => $deviceId]);
            return abort(404);
        }

        if (!$device->enableReport()) {
            //Log::debug('Error! Reporting interval [%id]', ['%id' => $deviceId]);
            return abort(
                500,
                __('message.error.report_interval', ['minutes' => config('specs.device_report_interval')])
            );
        }

        $resultDevice = $this->deviceRepo->report(
            Auth::user(),
            $deviceId,
            Config::get('codes.report_types.user_web')
        );

        return json_encode([
            'message' => '',
            'deviceInfo' => (new DeviceDashboard($resultDevice))->toArray()
        ]);
    }

    /**
     * Mock report for the demo of the report action.
     * NOTE: The request accept ajax only.
     *
     * @return false|string(json)
     */
    public function mockReport()
    {
        if (!Request::isJson()) {
            abort('400');
        }

        $mockDevice = $this->deviceRepo->makeMock([
            'reported_at' => now()->getTimestamp(),
            'in_alert' => false,
            'in_suspend' => false
        ]);

        return json_encode([
            'message' => '',
            'deviceInfo' => (new DeviceDashboard($mockDevice))->setDemo(true)->toArray()
        ]);
    }
}
