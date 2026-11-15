<?php

use App\Enums\SpeakerQualification;
use App\Enums\UserRole;
use App\Filament\Resources\Speakers\Pages\ListSpeakers;
use App\Models\Speaker;
use App\Models\User;
use Filament\Actions\CreateAction;
use Filament\Actions\EditAction;
use Filament\Actions\Testing\TestAction;

use function Pest\Livewire\livewire;

it('stores speaker qualifications as a list', function (): void {
    $this->actingAs(User::factory()->create());

    livewire(ListSpeakers::class)
        ->callAction(CreateAction::class, data: [
            'name' => 'Ada Lovelace',
            'email' => 'ada@example.com',
            'bio' => 'First programmer.',
            'twitter_handle' => 'ada',
            'qualifications' => [SpeakerQualification::PublishedAuthor->value, SpeakerQualification::OpenSource->value],
        ])
        ->assertHasNoFormErrors();

    expect(Speaker::query()->sole()->qualifications)->toBe([SpeakerQualification::PublishedAuthor->value, SpeakerQualification::OpenSource->value]);
});

it('links a speaker to a speaker account and takes its name and email', function (): void {
    $this->actingAs(User::factory()->create());
    $speaker = Speaker::factory()->create();
    $account = User::factory()->create(['role' => UserRole::Speaker, 'name' => 'Ada Lovelace', 'email' => 'ada@example.com']);

    livewire(ListSpeakers::class)
        ->callAction(TestAction::make(EditAction::class)->table($speaker), data: ['user_id' => $account->getKey()])
        ->assertHasNoFormErrors();

    $speaker->refresh();

    expect($speaker->user->is($account))->toBeTrue()
        ->and($speaker->only(['name', 'email']))->toBe(['name' => 'Ada Lovelace', 'email' => 'ada@example.com']);
});

it('refuses to link an account that already manages another speaker', function (): void {
    $this->actingAs(User::factory()->create());
    $account = User::factory()->speaker()->create();
    $speaker = Speaker::factory()->create();

    livewire(ListSpeakers::class)
        ->callAction(TestAction::make(EditAction::class)->table($speaker), data: ['user_id' => $account->getKey()])
        ->assertHasFormErrors(['user_id' => 'This account already manages another speaker profile.']);

    expect($speaker->refresh()->user_id)->toBeNull();
});

it('keeps a linked speaker\'s name and email the same as their account', function (): void {
    $account = User::factory()->speaker()->create(['name' => 'Ada Lovelace', 'email' => 'ada@example.com']);

    $account->speaker->update(['name' => 'Someone Else', 'email' => 'someone@example.com']);

    expect($account->speaker->refresh()->only(['name', 'email']))->toBe(['name' => 'Ada Lovelace', 'email' => 'ada@example.com']);
});

it('lets an unlinked speaker have their own name and email', function (): void {
    $speaker = Speaker::factory()->create();

    $speaker->update(['name' => 'Grace Hopper', 'email' => 'grace@example.com']);

    expect($speaker->refresh()->only(['name', 'email']))->toBe(['name' => 'Grace Hopper', 'email' => 'grace@example.com']);
});

it('warns when a speaker has no account to sign in with', function (bool $isLinked): void {
    $this->actingAs(User::factory()->create());
    $speaker = $isLinked ? User::factory()->speaker()->create()->speaker : Speaker::factory()->create();

    $component = livewire(ListSpeakers::class)
        ->mountAction(TestAction::make(EditAction::class)->table($speaker));

    $isLinked
        ? $component->assertSchemaComponentHidden('no-account', 'mountedActionSchema0')
        : $component->assertSchemaComponentVisible('no-account', 'mountedActionSchema0');
})->with([
    'unlinked' => [false],
    'linked' => [true],
]);
