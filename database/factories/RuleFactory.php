<?php

use Faker\Generator as Faker;

$factory->define(\App\Models\Entities\Rule::class, function (Faker $faker) {

    return [
        'user_id' => $faker->randomDigitNotNull,
        'name' => $faker->text(200),
        'description' => $faker->word,
        'time_limits' => $faker->numberBetween(0, (24 * 7)),
        'notify_times' => $faker->numberBetween(1, 5),
        'message_id' => null,
        'embedded_message' => null,
    ];
});
