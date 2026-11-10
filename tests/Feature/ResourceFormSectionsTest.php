<?php

use App\Filament\Resources\Conferences\Pages\CreateConference;
use App\Filament\Resources\Speakers\Pages\CreateSpeaker;
use App\Filament\Resources\Talks\Pages\CreateTalk;
use App\Filament\Resources\Venues\Pages\CreateVenue;
use App\Models\User;

use function Pest\Livewire\livewire;

it('renders the create form sections', function (string $page, array $sections): void {
    $this->actingAs(User::factory()->create());

    livewire($page)
        ->assertSuccessful()
        ->assertSeeInOrder($sections);
})->with([
    'conference' => [CreateConference::class, ['Conference Details', 'Status', 'Speakers', 'Location']],
    'venue' => [CreateVenue::class, ['Venue Details', 'Address', 'Images']],
    'speaker' => [CreateSpeaker::class, ['Profile', 'Qualifications']],
    'talk' => [CreateTalk::class, ['Talk Details', 'Submission', 'Speaker']],
]);
