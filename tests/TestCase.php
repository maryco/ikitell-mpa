<?php

namespace Tests;

use App\Models\Entities\User;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Arr;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    /**
     * @see config/alert.php
     */
    public const MESSAGE_TEMPLATE_IDS = [101, 102];

    /**
     * @param int $count
     * @return mixed
     */
    protected function createUserWithDevices(int $count): mixed
    {
        return User::factory()
            ->count($count)
            ->hasDevices(1, ['reported_at' => null])
            ->hasRules(1, ['message_id' => Arr::random(self::MESSAGE_TEMPLATE_IDS)])
            ->afterCreating(
            // attach rule and device
                fn(User $user) => $user->devices->first()
                    ->update(['rule_id' => $user->rules->first()->id])
            )
            ->create();
    }
}
