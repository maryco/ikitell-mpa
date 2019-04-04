<?php
/**
 * Class TestDeviceContactSeeder
 * php artisan db:seed --class=TestDeviceContactSeeder
 */

use \App\Models\Entities\Device as Device;
use \App\Models\Entities\Contact as Contact;

class DevDeviceContactSeeder extends SeederBase
{
    const SEED_TOTAL = 2;

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        /**
         * Create device_contact for the basic user.
         */
        $basicUser = $this->getBasicUser();
        if (!$basicUser) {
            Log::warning('Missing test user(basic) run TestUserSeeder.');
            $this->call(DevUserSeeder::class);
        }

        $device = Device::owner($basicUser->id)->first();
        if (!$device) {
            Log::warning('Missing test device for user(basic) run TestDeviceSeeder.');
            $this->call(DevDeviceSeeder::class);
        }

        $contacts = Contact::userId($basicUser->id)->verified()->get();
        Log::debug('Test Contacts = []', ['' => $contacts]);

        $seedCnt = 0;

        foreach ($contacts as $contact) {
            factory(\App\Models\Entities\DeviceContact::class)->create([
                'device_id' => $device->id,
                'contact_id' => $contact->id,
            ]);

            $seedCnt++;
            if ($seedCnt >= self::SEED_TOTAL) {
                break;
            }
        }
    }
}
