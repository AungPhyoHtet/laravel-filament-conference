<?php

use App\Enums\TalkLength;
use App\Enums\TalkStatus;
use App\Filament\Resources\Speakers\Pages\ViewSpeaker;
use App\Filament\Resources\Speakers\RelationManagers\TalksRelationManager;
use App\Models\Speaker;
use App\Models\Talk;
use App\Models\User;
use Filament\Actions\CreateAction;
use Filament\Actions\EditAction;
use Filament\Actions\Testing\TestAction;

use function Pest\Livewire\livewire;

beforeEach(function (): void {
    $this->actingAs(User::factory()->create());
    $this->speaker = Speaker::factory()->create();
});

/**
 * Mount the talks relation manager as it appears on the speaker view page.
 */
function speakerTalks(Speaker $speaker): mixed
{
    return livewire(TalksRelationManager::class, [
        'ownerRecord' => $speaker,
        'pageClass' => ViewSpeaker::class,
    ]);
}

it('lists only the speaker\'s own talks, with trashed ones behind the filter', function (): void {
    $own = Talk::factory()->for($this->speaker)->create();
    $trashed = Talk::factory()->for($this->speaker)->create();
    $trashed->delete();
    $other = Talk::factory()->create();

    speakerTalks($this->speaker)
        ->assertCanSeeTableRecords([$own])
        ->assertCanNotSeeTableRecords([$trashed, $other])
        ->filterTable('trashed', true)
        ->assertCanSeeTableRecords([$own, $trashed])
        ->assertCanNotSeeTableRecords([$other]);
});

it('creates a talk for the speaker from the view page', function (): void {
    speakerTalks($this->speaker)
        ->assertActionVisible(TestAction::make(CreateAction::class)->table())
        ->callAction(TestAction::make(CreateAction::class)->table(), data: [
            'title' => 'Notes on the Engine',
            'abstract' => 'Thoughts on the Analytical Engine.',
            'status' => TalkStatus::Submitted,
            'length' => TalkLength::Keynote,
            'new_talk' => true,
        ])
        ->assertHasNoFormErrors();

    expect($this->speaker->talks()->sole())
        ->title->toBe('Notes on the Engine')
        ->length->toBe(TalkLength::Keynote);
});

it('edits a talk from the view page', function (): void {
    $talk = Talk::factory()->for($this->speaker)->create(['title' => 'Old title']);

    speakerTalks($this->speaker)
        ->callAction(TestAction::make(EditAction::class)->table($talk), data: ['title' => 'New title'])
        ->assertHasNoFormErrors();

    expect($talk->refresh()->title)->toBe('New title');
});

it('approves and rejects talks from the view page', function (): void {
    $talk = Talk::factory()->for($this->speaker)->create(['status' => TalkStatus::Submitted]);

    speakerTalks($this->speaker)
        ->callAction(TestAction::make('approve')->table($talk));

    expect($talk->refresh()->status)->toBe(TalkStatus::Approved);

    speakerTalks($this->speaker)
        ->callAction(TestAction::make('reject')->table($talk));

    expect($talk->refresh()->status)->toBe(TalkStatus::Rejected);
});
