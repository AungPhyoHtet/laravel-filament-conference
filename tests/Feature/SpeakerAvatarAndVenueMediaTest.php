<?php

use App\Enums\Region;
use App\Filament\Resources\Speakers\Pages\CreateSpeaker;
use App\Filament\Resources\Venues\Pages\CreateVenue;
use App\Models\Speaker;
use App\Models\User;
use App\Models\Venue;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

use function Pest\Livewire\livewire;

it('stores an optional speaker avatar path', function (): void {
    $speakerWithoutAvatar = Speaker::factory()->create();
    $speakerWithAvatar = Speaker::factory()->create(['avatar' => 'avatars/ada.png']);

    expect($speakerWithoutAvatar->fresh()->avatar)->toBeNull()
        ->and($speakerWithAvatar->fresh()->avatar)->toBe('avatars/ada.png');
});

it('attaches media to a venue', function (): void {
    Storage::fake('public');

    $venue = Venue::factory()->create();

    $venue->addMedia(UploadedFile::fake()->image('hall.jpg'))->toMediaCollection();

    $media = $venue->fresh()->getFirstMedia();

    expect($media)->not->toBeNull()
        ->and($media->file_name)->toBe('hall.jpg');

    Storage::disk('public')->assertExists($media->getPathRelativeToRoot());
});

it('uploads a speaker avatar from the create form', function (): void {
    Storage::fake('public');
    $this->actingAs(User::factory()->create());

    livewire(CreateSpeaker::class)
        ->fillForm([
            'avatar' => UploadedFile::fake()->image('ada.png'),
            'name' => 'Ada Lovelace',
            'email' => 'ada@example.com',
            'bio' => 'First programmer.',
            'twitter_handle' => 'ada',
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $avatar = Speaker::query()->sole()->avatar;

    expect($avatar)->toStartWith('avatars/');
    Storage::disk('public')->assertExists($avatar);
});

it('uploads venue images from the create form', function (): void {
    Storage::fake('public');
    $this->actingAs(User::factory()->create());

    livewire(CreateVenue::class)
        ->fillForm([
            'name' => 'Grand Hall',
            'city' => 'Yangon',
            'country' => 'Myanmar',
            'region' => Region::cases()[0],
            'postal_code' => '11181',
            'images' => [
                UploadedFile::fake()->image('front.jpg'),
                UploadedFile::fake()->image('stage.jpg'),
            ],
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    expect(Venue::query()->sole()->getMedia('venue-images'))->toHaveCount(2);
});
