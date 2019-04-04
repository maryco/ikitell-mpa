<?php

use Faker\Generator as Faker;

$factory->define(\App\Models\Entities\Contact::class, function (Faker $faker) {

    return [
        'user_id' => null,
        'email' => $faker->email,
        'name' => $faker->text(200),
        'description' => $faker->word,
        'email_verified_at' => $faker->randomElement([null, now(), $faker->dateTime]),
        'send_verify_at' => $faker->randomElement([null, now(), $faker->dateTime])
    ];
});
