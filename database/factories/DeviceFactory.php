<?php

namespace Database\Factories;

use App\Enums\Device\DeviceType;
use App\Models\Entities\Device;
use Illuminate\Database\Eloquent\Factories\Factory;
use Throwable;

class DeviceFactory extends Factory
{
    protected $model = Device::class;

    /**
     * @return array
     * @throws Throwable
     */
    public function definition(): array
    {
        // NOTE: Error $this->faker->text() in php7.4
        //  "ErrorException: implode(): Passing glue string after array is deprecated. Swap the parameters"
        //  @see https://laracasts.com/discuss/channels/laravel/laravel-6-and-php-74-seeding-problem?page=1
        return [
            'owner_id' => $this->faker->randomDigitNotNull,
            'assigned_user_id' => null,
            //'passport_client_id' => '', // deprecated
            //'mac_address' => '', // deprecated
            'type' => DeviceType::GENERAL->value,
            'rule_id' => null,
            'name' => $this->faker->text(200),
            'description' => $this->faker->word,
            'reset_word' => $this->faker->text(20),
            'in_alert' => false,
            'in_suspend' => false,
            'user_name' => $this->faker->name,
            'image' => json_encode(
                Device::makeImageModel(['image_preset' => $this->faker->numberBetween(1, 8)]),
                JSON_THROW_ON_ERROR
            ),
            'reported_at' => $this->faker->randomElement([null, now()->getTimestamp(), $this->faker->unixTime]),
            'report_reserved_at' => null,
            'suspend_start_at' => null,
            'suspend_end_at' => null,
        ];
    }

    /**
     * @param int $number
     * @return static
     */
    public function imagePreset(int $number): static
    {
        return $this->state(fn(array $attr) => [
            'image' => json_encode(
                Device::makeImageModel(['image_preset' => $number]),
                JSON_THROW_ON_ERROR
            ),
        ]);
    }

    /**
     * @return static
     */
    public function inAlert(): static
    {
        return $this->state(fn(array $attr) => [
            'in_alert' => true,
        ]);
    }

    /**
     * @return static
     */
    public function inSuspend(): static
    {
        return $this->state(fn(array $attr) => [
            'in_suspend' => true,
        ]);
    }

    /**
     * @return static
     */
    public function suspendNow(): static
    {
        $now = now();
        return $this->state(fn(array $attr) => [
            'suspend_start_at' => $now,
            'suspend_end_at' => $now,
        ]);
    }
}
