<?php

namespace Database\Factories;

use App\Enums\SpeakerQualification;
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
            'bio' => fake()->text(),
            'twitter_handle' => fake()->word(),
            'qualifications' => array_column(fake()->randomElements(SpeakerQualification::cases(), fake()->numberBetween(0, 3)), 'value'),
        ];
    }
}
