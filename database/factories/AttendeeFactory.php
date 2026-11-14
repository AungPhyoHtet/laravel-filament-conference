<?php

namespace Database\Factories;

use App\Models\Attendee;
use App\Models\Conference;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Attendee>
 */
class AttendeeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'conference_id' => Conference::factory(),
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'created_at' => $signedUpAt = fake()->dateTimeBetween('-60 days'),
            'updated_at' => $signedUpAt,
        ];
    }
}
