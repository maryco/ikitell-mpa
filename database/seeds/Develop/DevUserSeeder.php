<?php
/**
 * Class TestUserSeeder
 * php artisan db:seed --class=TestUserSeeder
 */

class DevUserSeeder extends SeederBase
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        foreach (self::TEST_USERS as $testUser) {

            $user = \App\Models\Entities\User::email($testUser['email'])->first();

            if ($user) {
                Log::warning(
                    'Already registered test user. [%email]',
                    ['email' => $testUser['email']]
                );
                continue;
            }

            factory(App\Models\Entities\User::class)->create($testUser);
        }
    }
}
