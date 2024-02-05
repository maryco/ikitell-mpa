<?php
namespace App\Models\Repositories;

use App\Models\Entities\Alert;
use App\Models\Entities\Device;
use App\Models\Entities\NotificationLog;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

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
     * @inheritDoc
     */
    public function getActive(int $limit = 10): Collection
    {
        return Alert::where('max_notify_count', '>=', DB::raw('notify_count'))
            ->where('next_notify_at', '<', Carbon::now()->getTimestamp())
            ->orderBy('next_notify_at')
            ->orderBy('id')
            ->limit($limit)
            ->with(['device'])
            ->get();
    }

    /**
     * @inheritDoc
     */
    public function buildAlert(Device $device): ?Alert
    {
        if (!$device->rule) {
            Log::error('The target device has no rule.', ['deviceId' => $device->id]);
            return null;
        }

        $alert = (new Alert())->fill([
            'device_id' => $device->id,
            'notify_count' => 0,
            'max_notify_count' => $device->rule?->notify_times,
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

        if ($device->contacts && count($device->contacts) > 0) {
            foreach ($device->contacts as $contact) {
                $alert->addSendTarget($contact->email, $contact->name, Alert::TARGET_TYPE_CONTACTS);
            }
        }

        /**
         * Build AlertNotification instance with the rule settings.
         */

        if (!$device->rule->message_id) {
            Log::error('The device has no message id', ['deviceId' => $device->id, 'ruleId' => $device->rule->id]);
            return null;
        }

        $msgRepo = new MessageRepository();
        $msg = $msgRepo->findById($device->rule->message_id, $device->owner_id);

        if (!$msg) {
            Log::error('Not found message.', ['messageId' => $device->rule->message_id]);
            return null;
        }

        $device->fillUserName($device->ownerUser, $device->assingedUser);

        $msg->setContent($device, $device->rule);

        $alert->notification_payload = $msg->buildNotification();

        return $alert;
    }

    /**
     * @inheritDoc
     * @throws Throwable
     */
    public function updateForNext(int $alertId): bool
    {
        return DB::transaction(static function () use ($alertId) {
            /**
             * Update to prepare to the next notification process.
             */
            $locked = Alert::id($alertId)
                ->lockForUpdate()
                ->first();

            if (!$locked) {
                Log::error('Not found target alert.', ['alertId' => $alertId]);
                return false;
            }

            if ($locked->notify_count === $locked->max_notify_count) {
                $locked->next_notify_at = null;
            } else {
                $locked->next_notify_at = Carbon::now()
                    ->addMinutes(config_int('specs.send_alert_interval', 180))
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

            return (bool)$locked->save();
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
