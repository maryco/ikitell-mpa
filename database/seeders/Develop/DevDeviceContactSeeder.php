<?php

namespace Database\Seeders\Develop;

/**
 * Class TestDeviceContactSeeder
 * php artisan db:seed --class=TestDeviceContactSeeder
 */

use \App\Models\Entities\Device as Device;
use \App\Models\Entities\Contact as Contact;
use App\Models\Entities\DeviceContact;
use Database\Seeders\SeederBase;
use Illuminate\Support\Facades\Log;

class DevDeviceContactSeeder extends SeederBase
{
    const SEED_TOTAL = 2;

    /**
     * Run the database seeders.
     *
     * @return void
     */
    public function run(): void
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
            DeviceContact::factory()->create([
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
