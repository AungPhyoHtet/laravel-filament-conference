<?php

namespace Database\Factories;

use App\Enums\TalkLength;
use App\Enums\TalkStatus;
use App\Models\Speaker;
use Illuminate\Database\Eloquent\Factories\Factory;

class TalkFactory extends Factory
{
    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'title' => fake()->sentence(4),
            'abstract' => '<p>'.fake()->text().'</p>',
            'new_talk' => fake()->boolean(),
            'status' => fake()->randomElement(TalkStatus::cases()),
            'length' => fake()->randomElement(TalkLength::cases()),
            'speaker_id' => Speaker::factory(),
        ];
    }
}
