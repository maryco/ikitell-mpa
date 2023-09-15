<?php

namespace Database\Factories;

use App\Models\Entities\Alert;
use Illuminate\Database\Eloquent\Factories\Factory;

class AlertFactory extends Factory
{
    protected $model = Alert::class;

    /**
     * @return array
     */
    public function definition(): array
    {
        return [
            'device_id' => $this->faker->randomDigitNotNull,
            'notify_count' => $this->faker->numberBetween(0, 4),
            'max_notify_count' => $this->faker->numberBetween(1, 10),
            'next_notify_at' => $this->faker->randomElement([null, now()->getTimestamp(), $this->faker->unixTime]),
            'notification_payload' => null,
            'send_targets' => [],
        ];
    }
}
