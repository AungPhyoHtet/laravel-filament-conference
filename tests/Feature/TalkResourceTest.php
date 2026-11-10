<?php

use App\Enums\TalkLength;
use App\Enums\TalkStatus;
use App\Filament\Resources\Talks\Pages\CreateTalk;
use App\Filament\Resources\Talks\Pages\ListTalks;
use App\Models\Speaker;
use App\Models\Talk;
use App\Models\User;
use Filament\Tables\Columns\ImageColumn;

use function Pest\Livewire\livewire;

beforeEach(function (): void {
    $this->actingAs(User::factory()->create());
});

it('defaults the create form to a new, submitted, normal length talk', function (): void {
    livewire(CreateTalk::class)
        ->assertSchemaStateSet([
            'status' => TalkStatus::Submitted,
            'length' => TalkLength::Normal,
            'new_talk' => true,
        ]);
});

it('saves the status, length and new talk flag', function (): void {
    $speaker = Speaker::factory()->create();

    livewire(CreateTalk::class)
        ->fillForm([
            'title' => 'Scaling Laravel',
            'abstract' => 'Lessons from production.',
            'status' => TalkStatus::Approved,
            'length' => TalkLength::Keynote,
            'new_talk' => false,
            'speaker_id' => $speaker->getKey(),
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $talk = Talk::query()->sole();

    expect($talk->status)->toBe(TalkStatus::Approved)
        ->and($talk->length)->toBe(TalkLength::Keynote)
        ->and($talk->new_talk)->toBeFalse();
});

it('shows status, length and new talk columns', function (): void {
    $talk = Talk::factory()->create([
        'status' => TalkStatus::Rejected,
        'length' => TalkLength::Lightning,
    ]);

    livewire(ListTalks::class)
        ->assertCanSeeTableRecords([$talk])
        ->assertTableColumnExists('new_talk')
        ->assertTableColumnStateSet('status', TalkStatus::Rejected, $talk)
        ->assertTableColumnStateSet('length', TalkLength::Lightning, $talk);
});

it('shows the speaker avatar in the talks table', function (): void {
    $talk = Talk::factory()
        ->for(Speaker::factory()->state(['avatar' => 'avatars/ada.png']))
        ->create();

    livewire(ListTalks::class)
        ->assertTableColumnStateSet('speaker.avatar', 'avatars/ada.png', $talk);
});

it('falls back to an initials avatar when the speaker has none', function (): void {
    $talk = Talk::factory()
        ->for(Speaker::factory()->state(['name' => 'Ada Lovelace', 'avatar' => null]))
        ->create();

    livewire(ListTalks::class)
        ->assertTableColumnExists('speaker.avatar', function (ImageColumn $column): bool {
            expect($column->getDefaultImageUrl())->toBe('https://ui-avatars.com/api/?name=Ada+Lovelace&background=0D8ABC&color=fff');

            return true;
        }, $talk);
});

it('toggles the new talk flag from the table', function (): void {
    $talk = Talk::factory()->create(['new_talk' => true]);

    livewire(ListTalks::class)
        ->call('updateTableColumnState', 'new_talk', (string) $talk->getKey(), false);

    expect($talk->refresh()->new_talk)->toBeFalse();
});

it('edits the title from the table', function (): void {
    $talk = Talk::factory()->create(['title' => 'Old title']);

    livewire(ListTalks::class)
        ->call('updateTableColumnState', 'title', (string) $talk->getKey(), 'New title');

    expect($talk->refresh()->title)->toBe('New title');
});

it('rejects a blank title from the table', function (): void {
    $talk = Talk::factory()->create(['title' => 'Old title']);

    livewire(ListTalks::class)
        ->call('updateTableColumnState', 'title', (string) $talk->getKey(), '');

    expect($talk->refresh()->title)->toBe('Old title');
});
