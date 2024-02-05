<?php

namespace Database\Factories;

use App\Models\Entities\Rule;
use Illuminate\Database\Eloquent\Factories\Factory;

class RuleFactory extends Factory
{
    protected $model = Rule::class;

    /**
     * @return array
     */
    public function definition(): array
    {
        return [
            'user_id' => $this->faker->randomDigitNotNull,
            'name' => $this->faker->text(200),
            'description' => $this->faker->word,
            'time_limits' => $this->faker->randomElement(range(24, (24 * 7), 24)),
            'notify_times' => $this->faker->numberBetween(1, 5),
            'message_id' => null,
            'embedded_message' => $this->faker->text(100),
        ];
    }
}
