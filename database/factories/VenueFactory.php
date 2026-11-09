<?php

namespace Database\Factories;

use App\Enums\Region;
use Illuminate\Database\Eloquent\Factories\Factory;

class VenueFactory extends Factory
{
    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'city' => fake()->city(),
            'country' => fake()->country(),
            'region' => fake()->randomElement(Region::cases()),
            'postal_code' => fake()->postcode(),
        ];
    }
}
