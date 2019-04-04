<?php

use Faker\Generator as Faker;

$factory->define(\App\Models\Entities\Alert::class, function (Faker $faker) {
    return [
        'device_id' => $faker->randomDigitNotNull,
        'notify_count' => $faker->numberBetween(0, 4),
        'max_notify_count' => $faker->numberBetween(1, 10),
        'next_notify_at' => $faker->randomElement([null, now()->getTimestamp(), $faker->unixTime]),
        'notification_payload' => null,
        'send_targets' => [],
    ];
});
