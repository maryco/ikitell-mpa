<?php

use Faker\Generator as Faker;

$factory->define(\App\Models\Entities\DeviceContact::class, function (Faker $faker) {
    return [
        'device_id' => $faker->randomDigitNotNull,
        'contact_id' => $faker->randomDigitNotNull,
    ];
});
