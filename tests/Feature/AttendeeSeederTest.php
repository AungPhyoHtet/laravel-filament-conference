<?php

use App\Models\Conference;
use Database\Seeders\AttendeeSeeder;

use function Pest\Laravel\seed;

it('seeds between 10 and 40 attendees for every conference', function (): void {
    $conferences = Conference::factory()->count(2)->create();

    seed(AttendeeSeeder::class);

    $conferences->each(fn (Conference $conference) => expect($conference->attendees()->count())->toBeBetween(10, 40));
});
