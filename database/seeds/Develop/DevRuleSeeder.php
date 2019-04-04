<?php
/**
 * Class TestRuleSeeder
 * php artisan db:seed --class=DevRuleSeeder
 */

class DevRuleSeeder extends SeederBase
{
    private $testRules = [
        [
            'name' => '通知設定01',
        ],
        [
            'name' => '通知設定02',
        ],
        [
            'name' => '通知設定03',
        ],
    ];

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        /**
         * Create rules for the basic user.
         */
        $basicUser = $this->getBasicUser();
        if (!$basicUser) {
            Log::warning('Missing test user(basic) run TestUserSeeder.');
            $this->call(DevUserSeeder::class);
        }

        foreach ($this->testRules as $testRule) {
            $testRule['user_id'] = $basicUser->id;

            factory(App\Models\Entities\Rule::class)->create($testRule);
        }
    }
}
