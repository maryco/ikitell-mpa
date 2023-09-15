<?php

namespace Database\Factories;

use App\Models\Entities\DeviceContact;
use Illuminate\Database\Eloquent\Factories\Factory;

class DeviceContactFactory extends Factory
{
    protected $model = DeviceContact::class;

    /**
     * @return array
     */
    public function definition(): array
    {
        return [
            'device_id' => $this->faker->randomDigitNotNull,
            'contact_id' => $this->faker->randomDigitNotNull,
        ];
    }
}
