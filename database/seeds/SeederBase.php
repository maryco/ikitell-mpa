<?php
/**
 * Variables for TEST data
 */

use Illuminate\Database\Seeder;

abstract class SeederBase extends Seeder
{
    const BASIC_USER = 'basic001@dev.ikitell.me';

    const BUSINESS_USER = 'business001@dev.ikitell.me';

    const LIMITED_USER = 'limited001@dev.ikitell.me';

    const TEST_USERS = [
        [
            'email' => self::BASIC_USER,
            'plan' => 0,
        ],
        [
            'email' => self::BUSINESS_USER,
            'plan' => 1,
        ],
        [
            'email' => self::LIMITED_USER,
            'plan' => 2,
        ],
    ];

    protected $testUsers = [
        'basic' => null,
        'business' => null,
        'limited' => null,
    ];

    /**
     * @return mixed
     */
    protected function getBasicUser()
    {
        return $this->getTestUser('basic', self::BASIC_USER);
    }

    /**
     * @return mixed
     */
    protected function getBusinessUser()
    {
        return $this->getTestUser('business', self::BUSINESS_USER);
    }

    /**
     * @return mixed
     */
    protected function getLimitedUser()
    {
        return $this->getTestUser('limited', self::LIMITED_USER);
    }

    /**
     * @param $planName
     * @param $email
     * @return mixed
     */
    protected function getTestUser($planName, $email)
    {
        if (!$this->testUsers[$planName]) {
            $this->testUsers[$planName] = \App\Models\Entities\User::email($email)->first();
        }
        return $this->testUsers[$planName];
    }

}
