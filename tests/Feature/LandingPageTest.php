<?php

use App\Enums\ConferenceStatus;
use App\Enums\TalkStatus;
use App\Models\Conference;
use App\Models\Speaker;
use App\Models\Talk;
use Illuminate\Support\Carbon;

beforeEach(function (): void {
    $this->withoutVite();
});

/**
 * A conference that should appear on the landing page.
 *
 * @param  array<string, mixed>  $attributes
 */
function listedConference(array $attributes = []): Conference
{
    return Conference::factory()->listed()->create($attributes);
}

it('is public and links organizers to the admin sign in', function (): void {
    $this->get(route('home'))
        ->assertOk()
        ->assertSee('Conferences worth')
        ->assertSee(route('filament.app.auth.login'));
});

it('shows published upcoming and ongoing conferences, soonest first', function (): void {
    listedConference(['name' => 'Later Conf', 'start_date' => now()->addMonths(3), 'end_date' => now()->addMonths(3)->addDay()]);
    listedConference(['name' => 'Sooner Conf', 'start_date' => now()->addMonth(), 'end_date' => now()->addMonth()->addDay()]);
    listedConference(['name' => 'Happening Conf', 'status' => ConferenceStatus::Ongoing, 'start_date' => now()->subDay(), 'end_date' => now()->addDay()]);

    $this->get(route('home'))
        ->assertOk()
        ->assertSeeInOrder(['Happening Conf', 'Sooner Conf', 'Later Conf']);
});

it('hides unpublished, draft, cancelled and finished conferences', function (array $attributes): void {
    listedConference(['name' => 'Hidden Conf', ...$attributes]);

    $this->get(route('home'))
        ->assertOk()
        ->assertDontSee('Hidden Conf')
        ->assertSee('No conferences are scheduled yet.');
})->with([
    'unpublished' => [['is_published' => false]],
    'draft' => [['status' => ConferenceStatus::Draft]],
    'cancelled' => [['status' => ConferenceStatus::Cancelled]],
    'already ended' => [['start_date' => now()->subWeek(), 'end_date' => now()->subDays(5)]],
]);

it('features speakers and approved talks from listed conferences only', function (): void {
    $conference = listedConference();
    $speaker = Speaker::factory()->create(['name' => 'Ada Lovelace']);
    $approved = Talk::factory()->for($speaker)->create(['title' => 'Notes on the Engine', 'status' => TalkStatus::Approved]);
    $submitted = Talk::factory()->for($speaker)->create(['title' => 'Unreviewed Idea', 'status' => TalkStatus::Submitted]);
    $conference->speakers()->attach($speaker);
    $conference->talks()->attach([$approved->getKey(), $submitted->getKey()]);

    $hiddenSpeaker = Speaker::factory()->create(['name' => 'Grace Hopper']);
    listedConference(['is_published' => false])->speakers()->attach($hiddenSpeaker);

    $this->get(route('home'))
        ->assertOk()
        ->assertSee('Ada Lovelace')
        ->assertSee('Notes on the Engine')
        ->assertDontSee('Unreviewed Idea')
        ->assertDontSee('Grace Hopper');
});

it('lists conferences made with the listed factory state', function (): void {
    $conference = Conference::factory()->listed()->create();

    expect(Conference::query()->listed()->pluck('id')->all())->toBe([$conference->getKey()]);
});

it('formats conference date ranges', function (string $start, string $end, string $expected): void {
    $conference = Conference::factory()->make([
        'start_date' => Carbon::parse($start),
        'end_date' => Carbon::parse($end),
    ]);

    expect($conference->dateRange())->toBe($expected);
})->with([
    'single day' => ['2027-03-12 09:00', '2027-03-12 17:00', 'Mar 12, 2027'],
    'same month' => ['2027-03-12', '2027-03-14', 'Mar 12 – 14, 2027'],
    'across months' => ['2027-03-30', '2027-04-02', 'Mar 30 – Apr 2, 2027'],
    'across years' => ['2026-12-30', '2027-01-02', 'Dec 30, 2026 – Jan 2, 2027'],
]);
