<?php

use App\Enums\UserRole;
use App\Filament\Speaker\Pages\Auth\Register;
use App\Models\User;
use Filament\Facades\Filament;

use function Pest\Livewire\livewire;

beforeEach(function (): void {
    Filament::setCurrentPanel('speaker');
});

it('signs up a speaker with a linked speaker profile', function (): void {
    livewire(Register::class)
        ->fillForm([
            'name' => 'Ada Lovelace',
            'email' => 'ada@example.com',
            'password' => 'correct-horse-battery',
            'passwordConfirmation' => 'correct-horse-battery',
        ])
        ->call('register')
        ->assertHasNoFormErrors();

    $user = User::query()->where('email', 'ada@example.com')->sole();

    expect($user->role)->toBe(UserRole::Speaker)
        ->and($user->speaker->only(['name', 'email']))->toBe(['name' => 'Ada Lovelace', 'email' => 'ada@example.com']);

    $this->assertAuthenticatedAs($user);
});
