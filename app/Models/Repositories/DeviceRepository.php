<?php

namespace App\Models\Repositories;

use App\Models\Entities\Alert;
use App\Models\Entities\Device;
use App\Models\Entities\DeviceContact;
use App\Models\Entities\DeviceDashboard;
use App\Models\Entities\DeviceLog;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use RuntimeException;
use Throwable;

class DeviceRepository implements DeviceRepositoryInterface
{
    public function makeModel($bindData = null)
    {
        $model = new Device();
        if ($bindData) {
            $model->mergeData($bindData);
        }

        return $model;
    }

    /**
     * Get count of the authenticated users device.
     *
     * @return int|mixed
     */
    public function count()
    {
        return Auth::guest() ? 0 : Device::userColumn(Auth::user())->count();
    }

    /**
     * @inheritDoc
     */
    public function findByUser($user, $deviceId)
    {
        return Device::userColumn($user)
            ->id($deviceId)
            ->first();
    }

    /**
     * @inheritDoc
     */
    public function getByUser($user, bool $withRule = false, bool $withAlert = false)
    {
        $query = Device::userColumn($user);

        if ($withRule) {
            $query->with('rule');
        }
        if ($withAlert) {
            $query->with('alert');
        }

        // FIXME:
        $query->orderByDesc('reported_at');

        return $query->get();
    }

    /**
     * @inheritDoc
     */
    public function getDashboard($user)
    {
        $devices = $this->getByUser($user, true, true);

        /**
         * NOTE: Cache the device ids for reduce DB access.
         */
        $this->cacheUsersDeviceId($user->id, $devices);

        $deviceDashboard = [];

        foreach ($devices as $device) {
            $deviceDashboard[] = new DeviceDashboard($device);
        }

        return $deviceDashboard;
    }

    /**
     * @inheritDoc
     */
    public function store($data): Device
    {
        return DB::transaction(function () use ($data) {
            if (Arr::get($data, 'id', null)) {
                $device = $this->findByUser(auth_provided_user(), $data['id']);
            } else {
                $device = $this->makeModel();
            }

            $device->mergeData($data);
            $device->setImageModel(Device::makeImageModel($data, Device::getImageType('preset')));
            $device->clearSystemSuspend(false);

            $device->save();

            /**
             * Delete/Insert device_contact
             */
            DeviceContact::deviceId($device->id)->delete();

            $contactIds = Arr::get($data, 'notification_targets', []);
            if (is_array($contactIds) && count($contactIds) > 0) {
                foreach ($contactIds as $contactId) {
                    DeviceContact::create(
                        ['device_id' => $device->id, 'contact_id' => $contactId]
                    )->save();
                }
            }

            return $device;
        });
    }

    /**
     * @inheritDoc
     */
    public function report($user, $deviceId, $reportType)
    {
        return DB::transaction(function () use ($user, $deviceId, $reportType) {
            $device = Device::userColumn($user)
                ->id($deviceId)
                ->lockForUpdate()
                ->first();

            if (!$device) {
                Log::error('Device not found.', ['deviceId' => $deviceId, 'userId' => $user->id]);
                throw new RuntimeException('Not found target device.');
            }

            $device->reported_at = Carbon::now()->getTimestamp();
            $device->in_alert = false;
            $device->save();

            /**
             * Delete alert and logging.
             */

            $device->alert()->delete();

            $this->buildDeviceLog($user->id, $deviceId, $reportType)
                ->save();

            return $device;
        });
    }

    /**
     * @inheritDoc
     */
    public function delete($deviceId, $ownerUserId)
    {
        return DB::transaction(function () use ($deviceId, $ownerUserId) {
            $device = Device::ownedByUser($deviceId, $ownerUserId)
                ->lockForUpdate()
                ->first();

            if (!$device) {
                Log::error(
                    'Not found target device [%id] [%user]',
                    ['%id' => $deviceId, '%user' => $ownerUserId]
                );
                return false;
            }

            /**
             * Delete related 'device_contact' and 'alerts'.
             */
            DeviceContact::deviceId($deviceId)->delete();
            $device->alert()->delete();

            return boolval($device->delete());
        });
    }

    /**
     * @inheritDoc
     */
    public function getForInspection($limit = 10): Collection
    {
        // FIXME: sort order is all right ?

        return Device::where('in_alert', false)
            ->select('devices.*')
            ->where('in_suspend', false)
            ->join('users', function ($join) {
                $join->on('devices.owner_id', '=', 'users.id')
                    ->whereNotNull('users.email_verified_at')
                    ->where('users.ban', 0);
            })
            ->with(['rule', 'contacts', 'ownerUser'])
            ->orderBy('reported_at')
            ->orderBy('id')
            ->limit($limit)
            ->get();
    }

    /**
     * @inheritDoc
     * @throws Throwable
     */
    public function beginSuspend($deviceId): bool
    {
        return DB::transaction(static function () use ($deviceId) {
            $locked = Device::id($deviceId)
                ->lockForUpdate()
                ->first();

            if (!$locked) {
                Log::warning('Not found target device.', ['deviceId' => $deviceId]);
                throw new RuntimeException('Not found target device.');
            }

            if (!$locked->isSuspend()) {
                Log::warning('The device do not need to begin suspend.', ['deviceId' => $locked->id]);
                $locked->clearSystemSuspend()->save();
                return false;
            }

            /**
             * NOTE:
             * If the 'suspend_end_at' not sets, assumed suspend forever.
             */

            $locked->in_suspend = true;
            $locked->report_reserved_at = $locked->suspend_end_at
                ? Carbon::parse($locked->suspend_end_at)->getTimestamp()
                : Carbon::now()
                    ->addDays(config_int('specs.reserve_report_day_forever_suspend', 90))
                    ->getTimestamp();

            return (bool)$locked->save();
        });
    }

    /**
     * @inheritDoc
     * @throws Throwable
     */
    public function endSuspend($deviceId): bool
    {
        return DB::transaction(function () use ($deviceId) {
            $locked = Device::id($deviceId)
                ->with(['ownerUser'])
                ->lockForUpdate()
                ->first();

            if (!$locked) {
                Log::warning('Not found target device', ['deviceId' => $deviceId]);
                throw new RuntimeException('Not found target device.');
            }

            if (!$locked->enableReservedReport()) {
                Log::warning('The device cannot report', ['deviceId' => $deviceId]);
                return false;
            }

            $locked->reported_at = Carbon::now()->getTimestamp();

            $this->buildDeviceLog(
                $locked->ownerUser->id,
                $locked->id,
                config('codes.report_types.system_resume')
            )->save();

            $locked->report_reserved_at = null;
            $locked->suspend_start_at = null;
            $locked->suspend_end_at = null;

            $locked->clearSystemSuspend()->save();

            return true;
        });
    }

    /**
     * @inheritDoc
     * @throws Throwable
     */
    public function issueAlert($deviceId): bool
    {
        return DB::transaction(function () use ($deviceId) {
            $locked = Device::id($deviceId)
                ->lockForUpdate()
                ->with(['rule', 'contacts', 'ownerUser', 'assignedUser'])
                ->first();

            if (!$locked || !$locked->rule) {
                Log::error('Not found target device or rule.', ['deviceId' => $deviceId]);
                return false;
            }

            if (!$locked->isTimeOver($locked->rule->time_limits)) {
                Log::error(
                    'The target device is not time over.',
                    ['deviceId' => $locked->id, 'ruleId' => $locked->rule->id]
                );
                return false;
            }

            $alertRepo = new AlertRepository($this);
            $alert = $alertRepo->buildAlert($locked);

            if (!$alert) {
                Log::error('Failed to build alert.', ['deviceId' => $locked->id, 'ruleId' => $locked->rule->id]);
                return false;
            }

            $alert->save();

            $locked->in_alert = true;

            return (bool)$locked->save();
        });
    }

    /**
     * @inheritDoc
     */
    public function getForResume(int $limit = 10): Collection
    {
        return Device::where('in_suspend', true)
            ->where('report_reserved_at', '<', Carbon::now()->getTimestamp())
            ->whereNotNull('report_reserved_at')
            ->with(['ownerUser', 'assignedUser'])
            ->orderBy('report_reserved_at')
            ->orderBy('id')
            ->limit($limit)
            ->get();
    }

    /**
     * Create a new DeviceLog instance
     *
     * @param $userId
     * @param $deviceId
     * @param $reportType
     * @return mixed
     */
    public function buildDeviceLog($userId, $deviceId, $reportType)
    {
        return DeviceLog::create([
            'user_id' => $userId,
            'device_id' => $deviceId,
            'reporting_type' => $reportType,
        ]);
    }

    /**
     * NOTE: Currently, 'Cache' use 'Session'.
     *
     * @inheritDoc
     */
    public function getCachedUsersDeviceId($user)
    {
        $key = sprintf(Device::CACHE_KEY_USER_DEVICES, $user->id);

        if (!Session::has($key)) {
            $devices = $this->getByUser($user);
            $this->cacheUsersDeviceId($user->id, $devices);
        }

        return Session::get($key, []);
    }

    /**
     * NOTE: Currently, 'Cache' use 'Session'.
     *
     * @inheritDoc
     */
    public function cacheUsersDeviceId($userId, $devices)
    {
        $ids = [];
        foreach ($devices as $device) {
            $ids[] = ($device instanceof DeviceDashboard)
                ? $device->getDevice()->id
                : $device->id;
        }

        $cacheKey = sprintf(Device::CACHE_KEY_USER_DEVICES, $userId);

        Session::put($cacheKey, $ids);
    }

    /**
     * @inheritDoc
     */
    public function makeMock($data = [])
    {
        $mock = Device::factory()->make(
            array_merge(__('docs.mock_device'), $data)
        );

        // Set default image.
        $imageModel = Device::makeImageModel([], Device::getImageType('preset'));
        $mock->forceFill(['image' => json_encode($imageModel->getArrayCopy())]);

        return $mock;
    }
}
