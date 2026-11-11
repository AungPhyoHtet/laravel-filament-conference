<?php

use App\Enums\SpeakerQualification;
use App\Filament\Resources\Speakers\Pages\ListSpeakers;
use App\Models\Speaker;
use App\Models\User;
use Filament\Actions\CreateAction;

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
