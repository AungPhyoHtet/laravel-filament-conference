<?php

namespace Database\Factories;

use App\Enums\SpeakerQualification;
use App\Models\Talk;
use Illuminate\Database\Eloquent\Factories\Factory;

class SpeakerFactory extends Factory
{
    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->safeEmail(),
            'bio' => '<p>'.fake()->text().'</p>',
            'twitter_handle' => fake()->word(),
            'qualifications' => collect(SpeakerQualification::cases())->shuffle()->take(fake()->numberBetween(0, 3))->pluck('value')->all(),
        ];
    }

    /**
     * Give the speaker a number of talks.
     */
    public function withTalks(int $count = 3): static
    {
        return $this->has(Talk::factory()->count($count));
    }
}
