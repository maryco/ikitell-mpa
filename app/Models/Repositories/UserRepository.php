<?php

namespace App\Models\Repositories;

use App\Exceptions\IkitellRuntimeException;
use App\Models\Entities\Device;
use App\Models\Entities\Rule;
use App\Models\Entities\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class UserRepository implements UserRepositoryInterface
{
    public function makeModel($bindData = null)
    {
        return new User();
    }

    public function count()
    {
        // TODO: Implement count() method.
    }

    /**
     * @see UserRepositoryInterface::createUserDataSet()
     * @throws \Throwable
     */
    public function createUserDataSet($data)
    {
        return DB::transaction(function () use ($data) {

            $user = User::create([
                'name' => '',
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
                'plan' => config('codes.subscription_types.basic'),
            ]);
            $user->save();

            $rule = Rule::create([
                'user_id' => $user->id,
                'name' => __('label.default.rule.name'),
                'message_id' => config('alert.default_template_id'),
                'embedded_message' => '',
            ]);
            $rule->fillDefault();
            $rule->save();

            $device = Device::create([
                'owner_id' => $user->id,
                'type' => config('codes.device_types.pc'),
                'rule_id' => $rule->id,
                'name' => __('label.default.device.name'),
                'user_name' => __('label.default.device.user_name'),
            ]);
            $device->setImageModel($device->makeImageModel([]));
            $device->save();

            return $user;
        });
    }

    /**
     * @see \App\Models\Repositories\UserRepositoryInterface::findById
     */
    public function findById($userId)
    {
        return User::find($userId);
    }

    /**
     * @see \App\Models\Repositories\UserRepositoryInterface::findByEmail
     */
    public function findByEmail($email)
    {
        return User::email($email)->first();
    }

    /**
     * @see \App\Models\Repositories\UserRepositoryInterface::updateProfile
     * @throws \Throwable
     */
    public function updateProfile($data, $userId)
    {
        return DB::transaction(function () use ($data, $userId) {
            $profile = User::where('id', $userId)
                ->lockForUpdate()
                ->first();

            if (!$profile) {
                Log::error('Not found target [%user]', ['%user' => $userId]);
                return false;
            }

            $profile->name = $data['name'];

            return $profile->update();
        });
    }

    /**
     * @see UserRepositoryInterface::delete()
     * @throws \Throwable
     */
    public function delete($userId)
    {
        return DB::transaction(function () use ($userId) {
            $user = User::where('id', $userId)
                ->lockForUpdate()
                ->first();

            if (!$user) {
                Log::error('Not found target. [%user]', ['%user' => $userId]);
                return false;
            }

            $deviceRepo = new DeviceRepository();

            // Delete 'device', 'device_contact' and 'alert'
            foreach ($user->devices as $device) {

                if (!$deviceRepo->delete($device->id, $device->owner_id)) {
                    Log::error(
                        'Failed to delete device. [%deviceId]',
                        ['%deviceId' => $device->id]
                    );
                    throw new IkitellRuntimeException('Failed to delete device.');
                    break;
                }
            }

            $user->contacts()->delete();

            $user->rules()->delete();

            return boolval($user->delete());
        });
    }
}
