<?php

namespace Database\Factories;

use App\Enums\ConferenceStatus;
use App\Enums\Region;
use App\Models\Venue;
use Illuminate\Database\Eloquent\Factories\Factory;

class ConferenceFactory extends Factory
{
    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        $startDate = fake()->dateTimeBetween('-6 months', '+1 year');

        return [
            'name' => fake()->words(2, true).' Conf '.$startDate->format('Y'),
            'description' => fake()->paragraphs(3, true),
            'start_date' => $startDate,
            'end_date' => (clone $startDate)->modify('+'.fake()->numberBetween(1, 3).' days'),
            'status' => fake()->randomElement(ConferenceStatus::cases()),
            'venue_id' => Venue::factory(),
            'region' => fn (array $attributes): Region => Venue::query()->find($attributes['venue_id'])?->region
                ?? fake()->randomElement(Region::cases()),
            'is_published' => fake()->boolean(),
        ];
    }

    /**
     * A published, upcoming conference that appears on the public landing page.
     */
    public function listed(): static
    {
        return $this->state(function (): array {
            $startDate = fake()->dateTimeBetween('+2 weeks', '+10 months');

            return [
                'name' => fake()->words(2, true).' Conf '.$startDate->format('Y'),
                'start_date' => $startDate,
                'end_date' => (clone $startDate)->modify('+'.fake()->numberBetween(1, 3).' days'),
                'status' => ConferenceStatus::Upcoming,
                'is_published' => true,
            ];
        });
    }
}
