<?php

namespace Database\Factories;

use App\Enums\NotificationLog\JobStatus;
use App\Models\Entities\NotificationLog;
use Illuminate\Database\Eloquent\Factories\Factory;

class NotificationLogFactory extends Factory
{
    protected $model = NotificationLog::class;

    /**
     * @return array
     */
    public function definition(): array
    {
        return [
            'alert_id' => $this->faker->randomDigitNotNull,
            'device_id' => $this->faker->randomDigitNotNull,
            'contact_id' => $this->faker->randomDigit(),
            'notify_count' => $this->faker->numberBetween(0, 10),
            'email' => $this->faker->safeEmail,
            'name' => $this->faker->name,
            'content' => $this->faker->text(),
            'job_status' => $this->faker->randomElement(JobStatus::values()),
        ];
    }

    /**
     * @return static
     */
    public function jobExecuted(): static
    {
        return $this->state(fn($ttr) => ['job_status' => JobStatus::EXECUTED->value]);
    }
}
