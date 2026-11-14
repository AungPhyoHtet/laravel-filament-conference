<?php

namespace Database\Seeders;

use App\Models\Attendee;
use App\Models\Conference;
use Illuminate\Database\Seeder;

class AttendeeSeeder extends Seeder
{
    /**
     * Seed a handful of attendees for every conference.
     */
    public function run(): void
    {
        Conference::query()->each(
            fn (Conference $conference) => Attendee::factory(fake()->numberBetween(10, 40))->for($conference)->create(),
        );
    }
}
