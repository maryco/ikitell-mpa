<?php

namespace Database\Seeders\Develop;

use App\Models\Entities\Contact;
use Carbon\Carbon;
use Database\Seeders\SeederBase;
use Illuminate\Support\Facades\Log;

/**
 * Class TestContactSeeder
 * php artisan db:seed --class=DevContactSeeder
 */

class DevContactSeeder extends SeederBase
{
    private array $testContacts = [
        [
            'name' => '通知先01(未承認)',
            'email' => 'contact001@dev.ikitell.me',
            'email_verified_at' => null,
            'send_verify_at' => 'today - 1 hour',
        ],
        [
            'name' => '通知先02(承認済)',
            'email' => 'contact002@dev.ikitell.me',
            'email_verified_at' => 'today',
        ],
        [
            'name' => '通知先03(承認済)',
            'email' => 'contact003@dev.ikitell.me',
            'email_verified_at' => 'today - 1 day',
        ],
        [
            'name' => '通知先04(未承認+削除)',
            'email' => 'contact004@dev.ikitell.me',
            'email_verified_at' => null,
            'deleted_at' => 'today - 3 hour'
        ],
        [
            'name' => '通知先05(承認+削除)',
            'email' => 'contact005@dev.ikitell.me',
            'email_verified_at' => 'today - 3 day',
            'deleted_at' => 'today - 3 hour'
        ],
        [
            'name' => '通知先06(承認済)',
            'email' => 'contact006@dev.ikitell.me',
            'email_verified_at' => 'today - 1 hour',
        ],
        [
            'name' => '通知先07(承認期限切れ)',
            'email' => 'contact007@dev.ikitell.me',
            'email_verified_at' => null,
            'send_verify_at' => 'today - 1 day',
        ],
        [
            'name' => '通知先08(承認作業未実施)',
            'email' => 'contact008@dev.ikitell.me',
            'email_verified_at' => null,
            'send_verify_at' => null,
        ],

    ];

    /**
     * Run the database seeders.
     *
     * @return void
     */
    public function run(): void
    {
        /**
         * Create contacts for the basic user.
         */
        $basicUser = $this->getBasicUser();
        if (!$basicUser) {
            Log::warning('Missing test user(basic) run DevUserSeeder.');
            $this->call(DevUserSeeder::class);
        }

        foreach ($this->testContacts as $contact) {
            $contact['user_id'] = $basicUser->id;

            $contact['email_verified_at'] = (array_get($contact, 'email_verified_at', null))
                ? Carbon::parse($contact['email_verified_at'])
                : null;

            $contact['send_verify_at'] = (array_get($contact, 'send_verify_at', null))
                ? Carbon::parse($contact['send_verify_at'])
                : null;

            $contact['deleted_at'] = (array_get($contact, 'deleted_at', null))
                ? Carbon::parse($contact['deleted_at'])
                : null;

            Contact::factory()->create($contact);
        }
    }
}
