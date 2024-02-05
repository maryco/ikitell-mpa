<?php

namespace App\Http\Controllers;

use App\Http\Requests\NoticeLogSearchRequest;
use App\Models\Repositories\AlertRepositoryInterface;
use App\Models\Repositories\DeviceRepositoryInterface;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;

class HomeController extends Controller
{
    /**
     * The get notification logs limit.
     */
    private const LOG_LIST_LIMIT = 6;

    /**
     * @var DeviceRepositoryInterface
     */
    protected DeviceRepositoryInterface $deviceRepo;

    /**
     * @var AlertRepositoryInterface
     */
    protected AlertRepositoryInterface $alertRepo;

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
    public function index(): Factory|View|Application
    {
        $devices = $this->deviceRepo->getDashboard(Auth::user());

        $logs = $this->alertRepo->searchUsersAlertNoticeLog(
            (new NoticeLogSearchRequest())->getDefaultConditions(),
            self::LOG_LIST_LIMIT
        );

        return view('dashboard', compact('devices', 'logs'));
    }
}
