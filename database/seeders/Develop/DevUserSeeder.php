<?php

namespace Database\Seeders\Develop;

use App\Models\Entities\User;
use Database\Seeders\SeederBase;
use Illuminate\Support\Facades\Log;

/**
 * Class TestUserSeeder
 * php artisan db:seed --class=TestUserSeeder
 */

class DevUserSeeder extends SeederBase
{
    /**
     * Run the database seeders.
     *
     * @return void
     */
    public function run(): void
    {
        foreach (self::TEST_USERS as $testUser) {

            $user = User::email($testUser['email'])->first();

            if ($user) {
                Log::warning(
                    'Already registered test user. [%email]',
                    ['email' => $testUser['email']]
                );
                continue;
            }

            User::factory()->create($testUser);
        }
    }
}
