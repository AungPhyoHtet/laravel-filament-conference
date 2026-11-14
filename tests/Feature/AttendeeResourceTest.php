<?php

use App\Enums\ConferenceStatus;
use App\Enums\Region;
use App\Filament\Resources\Attendees\Pages\CreateAttendee;
use App\Filament\Resources\Attendees\Pages\EditAttendee;
use App\Filament\Resources\Attendees\Pages\ListAttendees;
use App\Filament\Resources\Attendees\Widgets\AttendeeSignUpsChart;
use App\Filament\Resources\Attendees\Widgets\AttendeesStatsOverview;
use App\Models\Attendee;
use App\Models\Conference;
use App\Models\User;

use function Livewire\invade;
use function Pest\Laravel\travelTo;
use function Pest\Livewire\livewire;

beforeEach(function (): void {
    $this->actingAs(User::factory()->create());
    travelTo('2026-11-12 12:00:00');
});

it('lists, searches and filters attendees by conference', function (): void {
    $laracon = Conference::factory()->create(['name' => 'Laracon EU']);
    $phpConf = Conference::factory()->create(['name' => 'PHP Conf']);
    $ada = Attendee::factory()->for($laracon)->create(['name' => 'Ada Lovelace']);
    $grace = Attendee::factory()->for($phpConf)->create(['name' => 'Grace Hopper']);

    livewire(ListAttendees::class)
        ->assertCanSeeTableRecords([$ada, $grace])
        ->searchTable('Ada')
        ->assertCanSeeTableRecords([$ada])
        ->assertCanNotSeeTableRecords([$grace])
        ->searchTable('')
        ->filterTable('conference', [$phpConf->getKey()])
        ->assertCanSeeTableRecords([$grace])
        ->assertCanNotSeeTableRecords([$ada]);
});

it('filters attendees by sign-up date', function (): void {
    $recent = Attendee::factory()->create(['created_at' => now()->subDays(2)]);
    $old = Attendee::factory()->create(['created_at' => now()->subDays(20)]);

    livewire(ListAttendees::class)
        ->filterTable('signed_up', ['from' => now()->subWeek()->toDateString()])
        ->assertCanSeeTableRecords([$recent])
        ->assertCanNotSeeTableRecords([$old]);
});

it('filters attendees by their conference\'s status', function (): void {
    $upcoming = Attendee::factory()->for(Conference::factory()->state(['status' => ConferenceStatus::Upcoming]))->create();
    $completed = Attendee::factory()->for(Conference::factory()->state(['status' => ConferenceStatus::Completed]))->create();
    $cancelled = Attendee::factory()->for(Conference::factory()->state(['status' => ConferenceStatus::Cancelled]))->create();

    livewire(ListAttendees::class)
        ->filterTable('conference_status', [ConferenceStatus::Upcoming->value, ConferenceStatus::Completed->value])
        ->assertCanSeeTableRecords([$upcoming, $completed])
        ->assertCanNotSeeTableRecords([$cancelled]);
});

it('filters attendees by their conference\'s region', function (): void {
    $europe = Attendee::factory()->for(Conference::factory()->state(['region' => Region::Europe]))->create();
    $asia = Attendee::factory()->for(Conference::factory()->state(['region' => Region::Asia]))->create();

    livewire(ListAttendees::class)
        ->filterTable('region', [Region::Asia->value])
        ->assertCanSeeTableRecords([$asia])
        ->assertCanNotSeeTableRecords([$europe]);
});

it('filters attendees by whether their conference has ended', function (): void {
    $ongoing = Attendee::factory()->for(Conference::factory()->state(['start_date' => now()->subDay(), 'end_date' => now()->addHour()]))->create();
    $ended = Attendee::factory()->for(Conference::factory()->state(['start_date' => now()->subDays(3), 'end_date' => now()->subHour()]))->create();

    livewire(ListAttendees::class)
        ->filterTable('conference_timing', true)
        ->assertCanSeeTableRecords([$ongoing])
        ->assertCanNotSeeTableRecords([$ended])
        ->filterTable('conference_timing', false)
        ->assertCanSeeTableRecords([$ended])
        ->assertCanNotSeeTableRecords([$ongoing]);
});

it('creates an attendee', function (): void {
    $conference = Conference::factory()->create();

    livewire(CreateAttendee::class)
        ->fillForm([
            'name' => 'Ada Lovelace',
            'email' => 'ada@example.com',
            'conference_id' => $conference->getKey(),
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    expect(Attendee::query()->sole())
        ->name->toBe('Ada Lovelace')
        ->conference_id->toBe($conference->getKey());
});

it('allows one attendee per email for each conference', function (): void {
    $conference = Conference::factory()->create();
    $existing = Attendee::factory()->for($conference)->create(['email' => 'ada@example.com']);

    livewire(CreateAttendee::class)
        ->fillForm(['name' => 'Ada', 'email' => 'ada@example.com', 'conference_id' => $conference->getKey()])
        ->call('create')
        ->assertHasFormErrors(['email' => 'unique']);

    livewire(EditAttendee::class, ['record' => $existing->getRouteKey()])
        ->fillForm(['name' => 'Ada King'])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($existing->refresh()->name)->toBe('Ada King');
});

it('shows attendee stats with a week-over-week change', function (): void {
    $conferences = Conference::factory()->count(2)->create();
    Attendee::factory()->count(3)->for($conferences[0])->create(['created_at' => now()->subDays(1)]);
    Attendee::factory()->for($conferences[1])->create(['created_at' => now()->subDays(10)]);

    livewire(AttendeesStatsOverview::class)
        ->assertSeeText('Total attendees')
        ->assertSeeText('Across 2 conferences')
        ->assertSeeText('Sign-ups in the last 7 days')
        ->assertSeeText('200% increase on the previous 7 days')
        ->assertSeeText('2.0');
});

it('limits the stats to the table\'s current filters and search', function (): void {
    $laracon = Conference::factory()->create();
    Attendee::factory()->count(2)->for($laracon)->create(['name' => 'Ada']);
    Attendee::factory()->count(5)->create(['name' => 'Grace']);

    livewire(AttendeesStatsOverview::class, [
        'tableFilters' => ['conference' => ['values' => [$laracon->getKey()]]],
    ])
        ->assertSeeText('Across 1 conference');

    livewire(AttendeesStatsOverview::class, ['tableSearch' => 'Grace'])
        ->assertSeeText('Across 5 conferences');
});

it('charts daily sign-ups for the selected range', function (): void {
    Attendee::factory()->count(2)->create(['created_at' => now()->subDays(1)]);
    Attendee::factory()->create(['created_at' => now()]);
    Attendee::factory()->create(['created_at' => now()->subDays(40)]);

    $chart = livewire(AttendeeSignUpsChart::class)->set('filter', '7');
    $data = invade($chart->instance())->getData();

    expect($data['labels'])->toHaveCount(7)
        ->and($data['labels'][6])->toBe('Nov 12')
        ->and($data['datasets'][0]['data'])->toBe([0, 0, 0, 0, 0, 2, 1]);

    $chart->set('filter', '90');

    expect(array_sum(invade($chart->instance())->getData()['datasets'][0]['data']))->toBe(4);
});

it('falls back to 30 days for an unknown chart range', function (): void {
    $chart = livewire(AttendeeSignUpsChart::class)->set('filter', '9999');

    expect(invade($chart->instance())->getData()['labels'])->toHaveCount(30);
});

it('shows the stats and chart widgets on the attendees list', function (): void {
    livewire(ListAttendees::class)
        ->assertSeeLivewire(AttendeesStatsOverview::class)
        ->assertSeeLivewire(AttendeeSignUpsChart::class);
});
