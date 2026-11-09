<?php

use App\Enums\Region;
use App\Filament\Resources\Conferences\Pages\CreateConference;
use App\Models\Conference;
use App\Models\Speaker;
use App\Models\User;
use App\Models\Venue;
use Filament\Actions\Testing\TestAction;
use Filament\Forms\Components\Select;

use function Pest\Livewire\livewire;

beforeEach(function (): void {
    $this->actingAs(User::factory()->create());
});

it('only offers venues in the selected region', function (): void {
    $europeVenue = Venue::factory()->create(['region' => Region::Europe]);
    $asiaVenue = Venue::factory()->create(['region' => Region::Asia]);

    livewire(CreateConference::class)
        ->fillForm(['region' => Region::Europe->value])
        ->assertFormFieldExists('venue_id', function (Select $field) use ($europeVenue, $asiaVenue): bool {
            expect($field->getOptions())
                ->toHaveKey($europeVenue->getKey())
                ->not->toHaveKey($asiaVenue->getKey());

            return true;
        });
});

it('clears the venue when the region changes', function (): void {
    $venue = Venue::factory()->create(['region' => Region::Europe]);

    livewire(CreateConference::class)
        ->fillForm([
            'region' => Region::Europe->value,
            'venue_id' => $venue->getKey(),
        ])
        ->fillForm(['region' => Region::Asia->value])
        ->assertSchemaStateSet(['venue_id' => null]);
});

it('edits the selected venue from the venue select', function (): void {
    $venue = Venue::factory()->create(['region' => Region::Europe, 'name' => 'Old Hall']);

    livewire(CreateConference::class)
        ->fillForm([
            'region' => Region::Europe->value,
            'venue_id' => $venue->getKey(),
        ])
        ->callAction(TestAction::make('editOption')->schemaComponent('venue_id'), data: [
            'name' => 'New Hall',
        ])
        ->assertHasNoFormErrors();

    expect($venue->refresh()->name)->toBe('New Hall');
});

it('attaches the selected speakers to the conference', function (): void {
    $speakers = Speaker::factory(2)->create();
    Speaker::factory()->create();

    livewire(CreateConference::class)
        ->fillForm([
            'name' => 'Laracon',
            'description' => 'A Laravel conference.',
            'start_date' => now()->addMonth(),
            'end_date' => now()->addMonth()->addDay(),
            'region' => Region::Europe->value,
            'speakers' => $speakers->modelKeys(),
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    expect(Conference::query()->sole()->speakers->modelKeys())
        ->toEqualCanonicalizing($speakers->modelKeys());
});
