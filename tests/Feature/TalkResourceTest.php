<?php

use App\Enums\TalkLength;
use App\Enums\TalkStatus;
use App\Filament\Resources\Talks\Pages\CreateTalk;
use App\Filament\Resources\Talks\Pages\ListTalks;
use App\Models\Speaker;
use App\Models\Talk;
use App\Models\User;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;

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

it('filters talks by length', function (): void {
    $keynote = Talk::factory()->create(['length' => TalkLength::Keynote]);
    $lightning = Talk::factory()->create(['length' => TalkLength::Lightning]);

    livewire(ListTalks::class)
        ->filterTable('length', [TalkLength::Keynote->value])
        ->assertCanSeeTableRecords([$keynote])
        ->assertCanNotSeeTableRecords([$lightning]);
});

it('filters talks by speaker', function (): void {
    $speaker = Speaker::factory()->create();
    $speakersTalk = Talk::factory()->for($speaker)->create();
    $otherTalk = Talk::factory()->create();

    livewire(ListTalks::class)
        ->filterTable('speaker', $speaker->getKey())
        ->assertCanSeeTableRecords([$speakersTalk])
        ->assertCanNotSeeTableRecords([$otherTalk]);
});

it('filters talks by whether they are new', function (): void {
    $newTalk = Talk::factory()->create(['new_talk' => true]);
    $repeatTalk = Talk::factory()->create(['new_talk' => false]);

    livewire(ListTalks::class)
        ->filterTable('new_talk', false)
        ->assertCanSeeTableRecords([$repeatTalk])
        ->assertCanNotSeeTableRecords([$newTalk]);
});

it('shows only talks with the selected status tab', function (): void {
    $approved = Talk::factory()->create(['status' => TalkStatus::Approved]);
    $submitted = Talk::factory()->create(['status' => TalkStatus::Submitted]);

    livewire(ListTalks::class)
        ->assertCanSeeTableRecords([$approved, $submitted])
        ->set('activeTab', TalkStatus::Approved->value)
        ->assertCanSeeTableRecords([$approved])
        ->assertCanNotSeeTableRecords([$submitted]);
});

it('counts talks on each status tab', function (): void {
    Talk::factory(2)->create(['status' => TalkStatus::Approved]);
    Talk::factory()->create(['status' => TalkStatus::Rejected]);

    $tabs = livewire(ListTalks::class)->instance()->getTabs();

    expect($tabs['all']->getBadge())->toEqual(3)
        ->and($tabs[TalkStatus::Approved->value]->getBadge())->toEqual(2)
        ->and($tabs[TalkStatus::Rejected->value]->getBadge())->toEqual(1)
        ->and($tabs[TalkStatus::Submitted->value]->getBadge())->toEqual(0);
});

it('shows the abstract clamped to two lines with a minimum width', function (): void {
    $talk = Talk::factory()->create();

    livewire(ListTalks::class)
        ->assertTableColumnExists('abstract', function (TextColumn $column): bool {
            expect($column->getLineClamp())->toBe(2)
                ->and($column->getExtraAttributes())->toBe(['style' => 'min-width: 32rem']);

            return true;
        }, $talk);
});
