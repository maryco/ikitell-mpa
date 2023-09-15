<?php

namespace Database\Factories;

use App\Models\Entities\Contact;
use Illuminate\Database\Eloquent\Factories\Factory;

class ContactFactory extends Factory
{
    protected $model = Contact::class;

    /**
     * @return array
     */
    public function definition(): array
    {
        return [
            'user_id' => null,
            'email' => $this->faker->email,
            'name' => $this->faker->text(),
            'description' => $this->faker->word,
            'email_verified_at' => $this->faker->randomElement([null, now(), $this->faker->dateTime]),
            'send_verify_at' => $this->faker->randomElement([null, now(), $this->faker->dateTime])
        ];
    }
}
