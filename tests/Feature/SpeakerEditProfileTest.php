<?php

use App\Enums\SpeakerQualification;
use App\Filament\Speaker\Pages\Auth\EditProfile;
use App\Models\User;
use Filament\Facades\Filament;

use function Pest\Livewire\livewire;

beforeEach(function (): void {
    Filament::setCurrentPanel('speaker');
});

it('opens the profile page from the speaker panel', function (): void {
    $this->actingAs(User::factory()->speaker()->create())
        ->get('/speaker/profile')
        ->assertOk()
        ->assertSee('Speaker profile');
});

it('loads the signed-in speaker\'s account and profile details', function (): void {
    $user = User::factory()->speaker()->create(['name' => 'Ada Lovelace']);
    $user->speaker->update(['twitter_handle' => 'ada']);

    $this->actingAs($user);

    livewire(EditProfile::class)
        ->assertSchemaStateSet([
            'name' => 'Ada Lovelace',
            'speaker.twitter_handle' => 'ada',
        ]);
});

it('saves the account and speaker profile together', function (): void {
    $user = User::factory()->speaker()->create();

    $this->actingAs($user);

    livewire(EditProfile::class)
        ->fillForm([
            'name' => 'Ada Lovelace',
            'email' => 'ada@example.com',
            'currentPassword' => 'password',
            'speaker.twitter_handle' => 'ada',
            'speaker.bio' => '<p>First programmer.</p>',
            'speaker.qualifications' => [SpeakerQualification::PublishedAuthor->value],
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    $speaker = $user->speaker->refresh();

    expect($user->refresh()->only(['name', 'email']))->toBe(['name' => 'Ada Lovelace', 'email' => 'ada@example.com'])
        ->and($speaker->only(['name', 'email', 'twitter_handle', 'bio', 'qualifications']))->toBe([
            'name' => 'Ada Lovelace',
            'email' => 'ada@example.com',
            'twitter_handle' => 'ada',
            'bio' => '<p>First programmer.</p>',
            'qualifications' => [SpeakerQualification::PublishedAuthor->value],
        ]);
});

it('requires a bio on the speaker profile', function (): void {
    $user = User::factory()->speaker()->create();
    $originalBio = $user->speaker->bio;

    $this->actingAs($user);

    livewire(EditProfile::class)
        ->fillForm(['speaker.bio' => ''])
        ->call('save')
        ->assertHasFormErrors(['speaker.bio' => 'The bio field is required.']);

    expect($user->speaker->refresh()->bio)->toBe($originalBio);
});
