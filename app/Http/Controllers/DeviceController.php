<?php

namespace App\Http\Controllers;

use App\Enums\DeviceLog\ReportingType;
use App\Http\Requests\DeviceStoreRequest;
use App\Models\Entities\DeviceDashboard;
use App\Models\Repositories\ContactRepositoryInterface;
use App\Models\Repositories\DeviceRepositoryInterface;
use App\Models\Repositories\RuleRepositoryInterface;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Redirector;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

class DeviceController extends Controller
{
    /**
     * @var DeviceRepositoryInterface
     */
    private DeviceRepositoryInterface $deviceRepo;

    /**
     * @var RuleRepositoryInterface
     */
    private RuleRepositoryInterface $ruleRepo;

    /**
     * @var ContactRepositoryInterface
     */
    private ContactRepositoryInterface $contactRepo;

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
        $this->middleware('throttle:6,1')->only('report');

        $this->deviceRepo = $deviceRepo;
        $this->ruleRepo = $ruleRepo;
        $this->contactRepo = $contactRepo;
    }

    /**
     * Get device list
     *
     * @return Factory|View
     */
    public function getList(): Factory|View
    {
        $devices = $this->deviceRepo->getByUser(Auth::user(), false, false);

        return view('device.list', compact('devices'));
    }

    /**
     * Show create/edit form.
     *
     * @param null $id
     * @return Factory|View
     */
    public function showForm($id = null): Factory|View
    {
        $device = $this->deviceRepo->makeModel(
            ['owner_id' => Auth::id()]
        );

        if (Route::getCurrentRoute()?->named('device.edit')) {
            $device = $this->deviceRepo->findByUser(
                auth_provided_user(),
                $id ?? abort(Response::HTTP_NOT_FOUND)
            );
            abort_if(!$device, Response::HTTP_NOT_FOUND);
        }

        $rules = DeviceStoreRequest::rulesToArray(
            $this->ruleRepo->getByUserId(Auth::id(), false)
        );

        $contacts = DeviceStoreRequest::contactsToArray(
            $this->contactRepo->getByUserId(Auth::id(), true),
            old('device_notification_targets', Arr::pluck($device->contacts->toArray(), 'id'))
        );

        $deviceForm = new DeviceStoreRequest();

        return view('device.form', compact('device', 'rules', 'contacts', 'deviceForm'));
    }

    /**
     * Create or update device.
     *
     * @param DeviceStoreRequest $request
     * @return RedirectResponse|Redirector
     */
    public function store(DeviceStoreRequest $request): Redirector|RedirectResponse
    {
        $appInfoKey = self::ACTION_RESULT_KEY_SAVE;
        $targetId = Route::getCurrentRoute()?->parameter('id');

        if (Route::getCurrentRoute()?->named('device.edit')) {
            $device = $this->deviceRepo->findByUser(
                auth_provided_user(),
                $targetId ?? abort(Response::HTTP_NOT_FOUND)
            );
            abort_if(!$device, Response::HTTP_NOT_FOUND);
            $appInfoKey = self::ACTION_RESULT_KEY_EDIT;
        }

        $device = $this->deviceRepo->store(
            array_merge($request->onlyForStore(), ['id' => $targetId])
        );

        return redirect(route('device.edit', ['id' => $device->id]))->with($appInfoKey, true);
    }

    /**
     * Delete specific device.
     *
     * @param $deviceId
     * @return RedirectResponse|Redirector
     */
    public function delete($deviceId): Redirector|RedirectResponse
    {
        // TODO: Create policy
        if (Auth::user()?->isLimited()) {
            abort('403');
        }

        $device = $this->deviceRepo->findByUser(auth_provided_user(), $deviceId);

        if (!$device) {
            abort('404');
        }

        $this->deviceRepo->delete($deviceId, Auth::id());

        return redirect(route('device.list'))
            ->with('deleted', true);
    }

    /**
     * Report specific device.
     * NOTE: The request accept only ajax.
     *
     * @param $deviceId
     * @return JsonResponse
     */
    public function report($deviceId): JsonResponse
    {
        abort_if(!Request::isJson(), Response::HTTP_BAD_REQUEST);

        $device = $this->deviceRepo->findByUser(auth_provided_user(), $deviceId);
        abort_if(!$device, Response::HTTP_NOT_FOUND);

        abort_if(
            !$device->enableReport(),
            Response::HTTP_INTERNAL_SERVER_ERROR,
            __('message.error.report_interval', ['minutes' => config('specs.device_report_interval')])
        );

        $resultDevice = $this->deviceRepo->report(
            Auth::user(),
            $deviceId,
            ReportingType::USER_WEB->value
        );

        return response()->json([
            'message' => '',
            'deviceInfo' => (new DeviceDashboard($resultDevice))->toArray()
        ]);
    }

    /**
     * Mock report for demonstration of report actions.
     * NOTE: The request accept only ajax.
     *
     * @return JsonResponse
     */
    public function mockReport(): JsonResponse
    {
        abort_if(!Request::isJson(), Response::HTTP_BAD_REQUEST);

        $mockDevice = $this->deviceRepo->makeMock([
            'reported_at' => now()->getTimestamp(),
            'in_alert' => false,
            'in_suspend' => false
        ]);

        return response()->json([
            'message' => '',
            'deviceInfo' => (new DeviceDashboard($mockDevice))->setDemo(true)->toArray()
        ]);
    }
}
