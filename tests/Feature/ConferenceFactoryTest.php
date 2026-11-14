<?php

use App\Enums\ConferenceStatus;
use App\Models\Conference;

use function Pest\Laravel\travelTo;

it('gives conferences a status that matches their dates', function (string $startDate, string $endDate, ConferenceStatus $expectedStatus): void {
    travelTo('2026-11-12 12:00:00');

    $conference = Conference::factory()->create(['start_date' => $startDate, 'end_date' => $endDate]);

    expect($conference->status)->toBe($expectedStatus);
})->with([
    'future conference' => ['2027-01-10 09:00:00', '2027-01-12 17:00:00', ConferenceStatus::Upcoming],
    'running conference' => ['2026-11-11 09:00:00', '2026-11-13 17:00:00', ConferenceStatus::Ongoing],
    'finished conference' => ['2026-06-01 09:00:00', '2026-06-03 17:00:00', ConferenceStatus::Completed],
]);

it('keeps an explicitly given status', function (): void {
    $conference = Conference::factory()->create([
        'start_date' => now()->subMonth(),
        'end_date' => now()->subMonth()->addDay(),
        'status' => ConferenceStatus::Draft,
    ]);

    expect($conference->status)->toBe(ConferenceStatus::Draft);
});
