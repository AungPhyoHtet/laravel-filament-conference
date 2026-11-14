<?php

use App\Enums\TalkLength;
use App\Enums\TalkStatus;
use App\Filament\Speaker\Resources\Talks\Pages\CreateTalk;
use App\Filament\Speaker\Resources\Talks\Pages\EditTalk;
use App\Filament\Speaker\Resources\Talks\Pages\ListTalks;
use App\Filament\Speaker\Resources\Talks\TalkResource;
use App\Models\Speaker;
use App\Models\Talk;
use App\Models\User;
use Filament\Facades\Filament;

use function Pest\Livewire\livewire;

beforeEach(function (): void {
    Filament::setCurrentPanel('speaker');
});

it('lists only the signed-in speaker\'s talks', function (): void {
    $user = User::factory()->speaker()->create();
    $ownTalk = Talk::factory()->for($user->speaker)->create();
    $otherTalk = Talk::factory()->for(Speaker::factory())->create();

    $this->actingAs($user);

    livewire(ListTalks::class)
        ->assertCanSeeTableRecords([$ownTalk])
        ->assertCanNotSeeTableRecords([$otherTalk]);
});

it('submits a new talk for review under the signed-in speaker', function (): void {
    $user = User::factory()->speaker()->create();

    $this->actingAs($user);

    livewire(CreateTalk::class)
        ->fillForm([
            'title' => 'Shipping Filament panels',
            'abstract' => '<p>How we built two panels.</p>',
            'length' => TalkLength::Normal,
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $talk = Talk::query()->sole();

    expect($talk->speaker->is($user->speaker))->toBeTrue()
        ->and($talk->status)->toBe(TalkStatus::Submitted)
        ->and($talk->title)->toBe('Shipping Filament panels');
});

it('requires a title when submitting a talk', function (): void {
    $this->actingAs(User::factory()->speaker()->create());

    livewire(CreateTalk::class)
        ->fillForm(['title' => '', 'abstract' => '<p>An abstract.</p>'])
        ->call('create')
        ->assertHasFormErrors(['title' => 'required']);

    expect(Talk::query()->count())->toBe(0);
});

it('lets a speaker edit their own talk while it is waiting for review', function (): void {
    $user = User::factory()->speaker()->create();
    $talk = Talk::factory()->for($user->speaker)->create(['status' => TalkStatus::Submitted]);

    $this->actingAs($user);

    livewire(EditTalk::class, ['record' => $talk->getRouteKey()])
        ->fillForm(['title' => 'A better title'])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($talk->refresh()->title)->toBe('A better title');
});

it('hides other speakers\' talks instead of revealing they exist', function (): void {
    $otherTalk = Talk::factory()->for(Speaker::factory())->create(['status' => TalkStatus::Submitted]);

    $this->actingAs(User::factory()->speaker()->create())
        ->get(TalkResource::getUrl('edit', ['record' => $otherTalk], panel: 'speaker'))
        ->assertNotFound();
});

it('forbids editing a talk once it has been reviewed', function (TalkStatus $status): void {
    $user = User::factory()->speaker()->create();
    $talk = Talk::factory()->for($user->speaker)->create(['status' => $status]);

    $this->actingAs($user)
        ->get(TalkResource::getUrl('edit', ['record' => $talk], panel: 'speaker'))
        ->assertForbidden();
})->with([TalkStatus::Approved, TalkStatus::Rejected]);
