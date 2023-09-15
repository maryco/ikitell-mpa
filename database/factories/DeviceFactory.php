<?php

namespace Database\Factories;

use App\Models\Entities\Device;
use Illuminate\Database\Eloquent\Factories\Factory;

class DeviceFactory extends Factory
{
    protected $model = Device::class;

    /**
     * @return array
     */
    public function definition(): array
    {
        // TODO: Fix problem  $this->faker->text() in php7.4
        //  "ErrorException: implode(): Passing glue string after array is deprecated. Swap the parameters"
        //  @see https://laracasts.com/discuss/channels/laravel/laravel-6-and-php-74-seeding-problem?page=1
        return [
            'owner_id' => $this->faker->randomDigitNotNull,
            'assigned_user_id' => null,
            //'passport_client_id' => '',
            //'mac_address' => '',
            'type' => $this->faker->numberBetween(0, 4),
            'rule_id' => null,
            'name' => '',//$this->faker->text(200),
            'description' => $this->faker->word,
            'reset_word' => '',//$this->faker->text(50),
            'in_alert' => $this->faker->boolean,
            'in_suspend' => $this->faker->boolean,
            'user_name' => $this->faker->name,
            'image' => $this->faker->randomElement([
                //json_encode(['type' => 1, 'value' => $this->faker->numberBetween(1, 8)]),
                json_encode(Device::makeImageModel(
                    ['image_preset' => $this->faker->numberBetween(1, 8)]
                )),
                null
            ]),
            'reported_at' => $this->faker->randomElement([null, now()->getTimestamp(), $this->faker->unixTime]),
            'report_reserved_at' => null,
            'suspend_start_at' => null,
            'suspend_end_at' => null,
        ];
    }
}
