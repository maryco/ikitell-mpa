<?php
namespace App\Models\Repositories;

use App\Models\Entities\Alert;
use App\Models\Entities\Device;
use App\Notifications\AlertNotification;
use Illuminate\Database\Eloquent\Collection;

interface AlertRepositoryInterface extends BaseRepositoryInterface
{
    /**
     * Get notifiable records.
     *
     * @param int $limit
     * @return Collection<Alert>
     */
    public function getActive(int $limit = 10): Collection;

    /**
     * Return the new Notification instance.
     *
     * @param Device $device (NOTE: need with contact, rule, ownerUser and assignedUser)
     * @return Alert|null
     */
    public function buildAlert(Device $device): ?Alert;

    /**
     * Update specific alert for prepare to a next notification.
     *
     * @param int $alertId
     * @return bool
     */
    public function updateForNext(int $alertId): bool;

    /**
     * Search notification log of alert by specified conditions.
     *
     * @param array $searchCond
     * @param int $limit
     * @return mixed
     */
    public function searchUsersAlertNoticeLog($searchCond = [], $limit = 10);

    /**
     * Get the specified notification log of alert.
     *
     * @param $id
     * @param $user
     * @return mixed
     */
    public function findAlertLog($id, $user);
}
