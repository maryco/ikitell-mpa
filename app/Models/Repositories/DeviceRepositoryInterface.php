<?php
namespace App\Models\Repositories;

use App\Models\Entities\Device;

interface DeviceRepositoryInterface extends BaseRepositoryInterface
{
    /**
     * Return the specified device.
     *
     * NOTE: Depends on user's subscription type.
     * (refer owner_id or assigned_user_id)
     *
     * @param $user
     * @param $deviceId
     * @return mixed
     */
    public function findByUser($user, $deviceId);

    /**
     * Get user's devices.
     * @see app/Models/Repositories/DeviceRepositoryInterface.php:9
     *
     * @param $user
     * @param bool $withRule
     * @param bool $withAlert
     * @return mixed
     */
    public function getByUser($user, $withRule = false, $withAlert = false);

    /**
     * Get user's device detail information for shown at the dashboard.
     * @see app/Models/Repositories/DeviceRepositoryInterface.php:9
     *
     * @param $user
     * @return mixed
     */
    public function getDashboard($user);

    /**
     * Update 'reported_at' and clear alert.
     * @see app/Models/Repositories/DeviceRepositoryInterface.php:9
     *
     * @param $user
     * @param $deviceId
     * @param $reportType
     * @return mixed
     */
    public function report($user, $deviceId, $reportType);

    /**
     * Store by given data.
     *
     * @param $data
     * @return mixed
     */
    public function store($data);

    /**
     * Delete the specified device
     * and related data. (alert, device_contact)
     *
     * @param $deviceId
     * @param $ownerUserId
     * @return bool
     */
    public function delete($deviceId, $ownerUserId);

    /**
     * Get all active devices except in_alert and in_suspend.
     *
     * @param $limit
     * @return mixed
     */
    public function getForInspection($limit = 10);

    /**
     * Update specific device to the suspend.
     * And set report_reserved_at into date of the suspend_end_at.
     *
     * @param $deviceId
     * @return mixed
     */
    public function beginSuspend($deviceId);

    /**
     * Update specific device to resume.
     *
     * @param $devieId
     * @return bool
     */
    public function endSuspend($deviceId);

    /**
     * Create a new alert from the device.
     * And update the device to alert mode.
     *
     * @param $deviceId
     * @return bool|mixed
     */
    public function issueAlert($deviceId);

    /**
     * Get the devices which in the suspending and can be resume.
     *
     * @param int $limit
     * @return mixed
     */
    public function getForResume($limit = 10);

    /**
     * Get the specified users device ids from cache.
     *
     * @param $user
     * @return array
     */
    public function getCachedUsersDeviceId($user);

    /**
     * Set device ids to the specified users cache.
     *
     * @param $userId
     * @param $devices (Collection of Device or DeviceDashboard)
     * @return void
     */
    public function cacheUsersDeviceId($userId, $devices);

    /**
     * Make mock model.
     *
     * @param array $data
     * @return Device
     */
    public function makeMock($data = []);
}
