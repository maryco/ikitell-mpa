<?php

use Faker\Generator as Faker;
use \App\Models\Entities\Device as Device;

$factory->define(\App\Models\Entities\Device::class, function (Faker $faker) {

    // TODO: Fix problem  $faker->text() in php7.4
    //  "ErrorException: implode(): Passing glue string after array is deprecated. Swap the parameters"
    //  @see https://laracasts.com/discuss/channels/laravel/laravel-6-and-php-74-seeding-problem?page=1
    return [
        'owner_id' => $faker->randomDigitNotNull,
        'assigned_user_id' => null,
        //'passport_client_id' => '',
        //'mac_address' => '',
        'type' => $faker->numberBetween(0, 4),
        'rule_id' => null,
        'name' => '',//$faker->text(200),
        'description' => $faker->word,
        'reset_word' => '',//$faker->text(50),
        'in_alert' => $faker->boolean,
        'in_suspend' => $faker->boolean,
        'user_name' => $faker->name,
        'image' => $faker->randomElement([
            //json_encode(['type' => 1, 'value' => $faker->numberBetween(1, 8)]),
            json_encode(Device::makeImageModel(
                ['image_preset' => $faker->numberBetween(1, 8)]
            )),
            null
        ]),
        'reported_at' => $faker->randomElement([null, now()->getTimestamp(), $faker->unixTime]),
        'report_reserved_at' => null,
        'suspend_start_at' => null,
        'suspend_end_at' => null,
    ];
});
