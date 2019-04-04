<?php

namespace App\Http\Controllers;

use App\Http\Requests\NoticeLogSearchRequest;
use App\Models\Repositories\AlertRepositoryInterface;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class NoticeLogController extends Controller
{
    /**
     * The get notification logs limit.
     */
    const LIST_PER_PAGE = 20;

    /**
     * @var AlertRepositoryInterface
     */
    protected $alertRepo;

    /**
     * NoticeLogController constructor.
     *
     * @param AlertRepositoryInterface $alertRepo
     */
    public function __construct(AlertRepositoryInterface $alertRepo)
    {
        $this->alertRepo = $alertRepo;
    }

    /**
     * Search notification log of alert.
     *
     * @param NoticeLogSearchRequest $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function searchAlertLog(NoticeLogSearchRequest $request)
    {
        $logs = $this->alertRepo->searchUsersAlertNoticeLog(
            $request->getConditions(),
            self::LIST_PER_PAGE
        );

        return view('notice.log.list', compact('logs'));
    }

    /**
     * Show specified notification log of alert.
     *
     * @param $id
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function showDetail($id)
    {
        $log = $this->alertRepo->findAlertLog($id, Auth::user());

        if (!$log) {
            abort(404);
        }

        return view('notice.log.detail', compact('log'));
    }
}
