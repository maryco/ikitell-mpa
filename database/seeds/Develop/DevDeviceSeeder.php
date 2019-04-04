<?php
/**
 * Class TestDeviceSeeder
 * php artisan db:seed --class=DevDeviceSeeder
 */

class DevDeviceSeeder extends SeederBase
{
    /**
     * @var array
     */
    private $testDevices = [
        [
            'type' => 3,
            'name' => 'おれのアイフォン',
            'reset_word' => '生きてます',
            'in_alert' => false,
            'user_name' => '山田太郎',
            'reported_at' => null,
        ],

        [
            'type' => 3,
            'name' => 'アイフォン001',
            'reset_word' => 'リセット',
            'in_alert' => false,
            'user_name' => '101号室',
            'reported_at' => null,
        ],
        [
            'type' => 3,
            'name' => 'アンドロイド001',
            'reset_word' => '報告！',
            'in_alert' => false,
            'user_name' => '102号室',
            'assigned_user_id' => null,
            'reported_at' => 'today - 6 hour',
        ],
        [
            'type' => 3,
            'name' => 'アラート発生中端末',
            'reset_word' => 'リセット。',
            'in_alert' => true,
            'user_name' => '103号室',
            'reported_at' => 'today - 7 day',
        ],
    ];

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        /*
         * Create device for the basic user.
         */
        $basicUser = $this->getBasicUser();
        if (!$basicUser) {
            Log::warning('Missing test user(basic) run DevUserSeeder.');
            $this->call(DevUserSeeder::class);
        }

        $devices = $this->testDevices;

        $firstDevice = array_shift($devices);
        $this->create($basicUser, $firstDevice);

        /*
        * Create devices for the business user.
        */
        $businessUser = $this->getBusinessUser();
        if (!$businessUser) {
            Log::warning('Missing test user(business) run DevUserSeeder.');
            $this->call(DevUserSeeder::class);
        }

        foreach ($devices as $device) {
            $this->create($businessUser, $device);
        }
    }

    /**
     * @param $user
     * @param array $deviceData
     */
    private function create($user, $deviceData)
    {
        $deviceData['owner_id'] = $user->id;

        if (array_has($deviceData, 'assigned_user_id')) {
            $limitedUser = $this->getLimitedUser();
            $deviceData['assigned_user_id'] = $limitedUser->id;
        }

        if ((array_get($deviceData, 'reported_at', null))) {
            Log::debug(
                'Reported_at ? ',
                ['' => \Carbon\Carbon::parse($deviceData['reported_at'])->getTimestamp()]
            );
        } else {
            Log::debug('Reported at is null.');
        }

        $deviceData['reported_at'] = (array_get($deviceData, 'reported_at', null))
            ? \Carbon\Carbon::parse($deviceData['reported_at'])->getTimestamp()
            : null;

        factory(App\Models\Entities\Device::class)->create($deviceData);
    }
}
