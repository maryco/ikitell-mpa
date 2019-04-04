<?php

namespace App\Http\Controllers;

use App\Http\Requests\NoticeLogSearchRequest;
use App\Models\Entities\NotificationLog;
use App\Models\Repositories\AlertRepositoryInterface;
use App\Models\Repositories\DeviceRepositoryInterface;
use Illuminate\Support\Facades\Auth;

class HomeController extends Controller
{
    /**
     * The get notification logs limit.
     */
    const LOG_LIST_LIMIT = 6;

    /**
     * @var DeviceRepositoryInterface
     */
    protected $deviceRepo;

    /**
     * @var AlertRepositoryInterface
     */
    protected $alertRepo;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(
        DeviceRepositoryInterface $deviceRepo,
        AlertRepositoryInterface $alertRepo
    ) {
        $this->middleware('auth');

        $this->deviceRepo = $deviceRepo;
        $this->alertRepo = $alertRepo;
    }

    /**
     * Show home.
     */
    public function index()
    {
        $devices = $this->deviceRepo->getDashboard(Auth::user());

        $searchRequest = new NoticeLogSearchRequest();

        $logs = $this->alertRepo->searchUsersAlertNoticeLog(
            $searchRequest->getDefaultConditions(),
            self::LOG_LIST_LIMIT
        );

        return view('dashboard', compact('devices', 'logs'));
    }
}
