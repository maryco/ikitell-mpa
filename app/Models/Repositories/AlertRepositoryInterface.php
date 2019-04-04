<?php
namespace App\Models\Repositories;

use App\Models\Entities\Device;
use App\Notifications\AlertNotification;

interface AlertRepositoryInterface extends BaseRepositoryInterface
{
    /**
     * Get notifiable records.
     *
     * @param int $limit
     * @return mixed
     */
    public function getActive($limit = 10);

    /**
     * Return the new Notification instance.
     *
     * @param Device $device (NOTE: need with contact, rule, ownerUser and assignedUser)
     * @return AlertNotification|null
     */
    public function buildAlert($device);

    /**
     * Update specific alert for prepare to a next notification.
     *
     * @param $alertId
     * @return bool
     */
    public function updateForNext($alertId);

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
