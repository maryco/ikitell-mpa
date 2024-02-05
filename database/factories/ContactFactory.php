<?php

namespace Database\Factories;

use App\Models\Entities\Contact;
use Carbon\Carbon;
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
            'email' => $this->faker->safeEmail,
            'name' => $this->faker->name,
            'description' => $this->faker->text(300),
            'email_verified_at' => $this->faker->randomElement([null, now(), $this->faker->dateTime]),
            'send_verify_at' => fn($attr) => $attr['email_verified_at']
                ? Carbon::parse($attr['email_verified_at'])->subHour()->timestamp : null,
        ];
    }

    /**
     * @return static
     */
    public function emailNotVerified(): static
    {
        return $this->state(fn($attr) => [
            'email_verified_at' => null,
            'send_verify_at' => null,
        ]);
    }

    /**
     * @param ?Carbon $verifiedAt
     * @return static
     */
    public function emailVerified(?Carbon $verifiedAt = null): static
    {
        $when = $verifiedAt ?? now();
        return $this->state(fn($attr) => [
            'send_verify_at' => $when->subHour(),
            'email_verified_at' => $when,
        ]);
    }

    /**
     * @param ?Carbon $requestedAt
     * @return static
     */
    public function emailVerifyRequested(?Carbon $requestedAt = null): static
    {
        return $this->state(fn($attr) => [
            'send_verify_at' => $requestedAt ?? now(),
            'email_verified_at' => null,
        ]);
    }
}
