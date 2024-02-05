<?php
namespace App\Models\Repositories;

use App\Models\Entities\Device;
use App\Models\Entities\User;
use Illuminate\Database\Eloquent\Collection;

interface DeviceRepositoryInterface extends BaseRepositoryInterface
{
    /**
     * Return the specified device.
     *
     * NOTE: Depends on user's subscription type.
     * (refer owner_id or assigned_user_id)
     *
     * @param User $user
     * @param int $deviceId
     * @return mixed
     */
    public function findByUser(User $user, int $deviceId);

    /**
     * Get user's devices.
     * @param $user
     * @param bool $withRule
     * @param bool $withAlert
     * @return mixed
     *@see app/Models/Repositories/DeviceRepositoryInterface.php:9
     *
     */
    public function getByUser($user, bool $withRule = false, bool $withAlert = false);

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
     * @return Device
     */
    public function store($data): Device;

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
     * @param int $limit
     * @return Collection<Device>
     */
    public function getForInspection(int $limit = 10): Collection;

    /**
     * Update specific device to suspend.
     * And set report_reserved_at into date of the suspend_end_at.
     *
     * @param $deviceId
     * @return bool
     */
    public function beginSuspend($deviceId): bool;

    /**
     * Update specific device to resume.
     *
     * @param int $deviceId
     * @return bool
     */
    public function endSuspend(int $deviceId): bool;

    /**
     * Create a new alert from the device.
     * And update the device to alert mode.
     *
     * @param int $deviceId
     * @return bool
     */
    public function issueAlert(int $deviceId): bool;

    /**
     * Get the devices which in the suspending and can be resuming.
     *
     * @param int $limit
     * @return Collection<Device>
     */
    public function getForResume(int $limit = 10): Collection;

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
