<?php
namespace App\Models\Repositories;

use App\Models\Entities\Alert;
use App\Models\Entities\Device;
use App\Models\Entities\DeviceDashboard;
use App\Models\Entities\NotificationLog;
use Carbon\Carbon;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AlertRepository implements AlertRepositoryInterface
{
    /**
     * @var DeviceRepositoryInterface
     */
    private $deviceRepo;

    public function __construct(DeviceRepositoryInterface $deviceRepo)
    {
        $this->deviceRepo = $deviceRepo;
    }

    public function makeModel($bindData = null)
    {
        return new Alert();
    }

    public function count()
    {
        // TODO: Implement count() method.
        return 0;
    }

    /**
     * @see \App\Models\Repositories\AlertRepositoryInterface::getActive
     */
    public function getActive($limit = 10)
    {
        return Alert::where('max_notify_count', '>=', 'notify_count')
            ->where('next_notify_at', '<', Carbon::now()->getTimestamp())
            ->orderBy('next_notify_at')
            ->orderBy('id')
            ->limit($limit)
            ->with(['device'])
            ->get();
    }

    /**
     * @see \App\Models\Repositories\AlertRepositoryInterface::buildAlert
     */
    public function buildAlert($device)
    {
        if (!$device->rule) {
            Log::error('The target device has no rule. [%device]', ['%id' => $device->id]);
            return null;
        }

        $alert = factory(Alert::class)->make([
            'device_id' => $device->id,
            'notify_count' => 0,
            'max_notify_count' => $device->rule->notify_times,
            'next_notify_at' => now()->getTimestamp(),
        ]);

        /**
         * Set send target notifiable (User and Contacts).
         */

        if ($device->ownerUser) {
            $alert->addSendTarget(
                $device->ownerUser->email,
                $device->ownerUser->name ?: __('label.default.user.name'),
                Alert::TARGET_TYPE_OWNER
            );
        }

        if ($device->assingedUser) {
            $alert->addSendTarget(
                $device->assingedUser->email,
                $device->assingedUser->name ?: __('label.default.user.name'),
                Alert::TARGET_TYPE_USER
            );
        }

        if ($device->contact && count($device->contact) > 0) {
            foreach ($device->contact as $contact) {
                $alert->addSendTarget(
                    $contact->email,
                    $contact->name,
                    Alert::TARGET_TYPE_CONTACTS
                );
            }
        }

        /**
         * Build AlertNotification instance with the rule settings.
         */

        if (!$device->rule->message_id) {
            Log::error(
                'The device has no message id [%device] [%rule]',
                ['%device' => $device->id, '%rule' => $device->rule->id]
            );
            return null;
        }

        $msgRepo = new MessageRepository();
        $msg = $msgRepo->findById($device->rule->message_id, $device->owner_id);

        if (!$msg) {
            Log::error(
                'Not found message. [%message]',
                ['%message' => $device->rule->message_id]
            );
            return null;
        }

        $device->fillUserName($device->ownerUser, $device->assingedUser);

        $msg->setContent($device, $device->rule);

        $alert->notification_payload = $msg->buildNotification();

        return $alert;
    }

    /**
     * @see \App\Models\Repositories\AlertRepositoryInterface::updateForNext
     * @throws \Throwable
     */
    public function updateForNext($alertId)
    {
        DB::transaction(function () use ($alertId) {

            /**
             * Update to prepare to the next notification process.
             */
            $locked = Alert::id($alertId)
                ->lockForUpdate()
                ->first();

            if (!$locked) {
                Log::error('Not found target alert. [%alert]', ['%id' => $alertId]);
                return false;
            }

            if ($locked->notify_count == $locked->max_notify_count) {
                $locked->next_notify_at = null;
            } else {
                $locked->next_notify_at = Carbon::now()
                    ->addMinute(config('specs.send_alert_interval'))
                    ->getTimestamp();
            }

            /**
             * NOTE: The last condition notify_count = max_notify_count + 1
             */
            $locked->notify_count++;

            /**
             * TODO:
             * I think if need to change mail content,
             * It's ok to implements here.
             */

            return boolval($locked->save());
        });
    }

    /**
     * @see \App\Models\Repositories\AlertRepositoryInterface::searchUsersAlertNoticeLog
     */
    public function searchUsersAlertNoticeLog($searchCond = [], $limit = 10)
    {
        $deviceIds = $this->deviceRepo->getCachedUsersDeviceId(Auth::user());

        if (count($deviceIds) === 0) {
            return [];
        }

        $query = NotificationLog::whereIn('device_id', $deviceIds)->with(['device']);

        if (Arr::has($searchCond, 'job_status')) {
            $query->jobStatus($searchCond['job_status']);
        }

        if (Arr::has($searchCond, 'past')) {
            $query->where(
                'created_at',
                '>=',
                Carbon::now()->startOfDay()->subDays($searchCond['past'])
            );
        }

        if (Arr::has($searchCond, 'sort')) {
            $query->orderBy(
                $searchCond['sort'],
                Arr::get($searchCond, 'sort_direction', 'desc')
            );
        }

        return $query->select(NotificationLog::$outline)->paginate($limit);
    }

    /**
     * @see \App\Models\Repositories\AlertRepositoryInterface::findAlertLog
     */
    public function findAlertLog($id, $user)
    {
        // TODO: 他人のログが見れないか再テスト！
        return NotificationLog::id($id)
            ->with(['device' => function ($query) use ($user) {
                $query->userColumn($user);
            }])->first();
    }
}
