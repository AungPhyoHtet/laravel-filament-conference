<?php

use App\Filament\Resources\Users\Pages\CreateUser;
use App\Filament\Resources\Users\Pages\EditUser;
use App\Filament\Resources\Users\Pages\ListUsers;
use App\Models\User;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\Testing\TestAction;
use Illuminate\Support\Facades\Hash;

use function Pest\Livewire\livewire;

beforeEach(function (): void {
    $this->admin = User::factory()->create();
    $this->actingAs($this->admin);
});

it('lists and searches users', function (): void {
    $ada = User::factory()->create(['name' => 'Ada Lovelace']);
    $grace = User::factory()->create(['name' => 'Grace Hopper']);

    livewire(ListUsers::class)
        ->assertCanSeeTableRecords([$this->admin, $ada, $grace])
        ->searchTable('Ada')
        ->assertCanSeeTableRecords([$ada])
        ->assertCanNotSeeTableRecords([$grace]);
});

it('filters users by whether their email is verified', function (): void {
    $verified = User::factory()->create();
    $unverified = User::factory()->unverified()->create();

    livewire(ListUsers::class)
        ->filterTable('email_verified_at', false)
        ->assertCanSeeTableRecords([$unverified])
        ->assertCanNotSeeTableRecords([$verified]);
});

it('creates a user with a hashed password', function (): void {
    livewire(CreateUser::class)
        ->fillForm([
            'name' => 'Ada Lovelace',
            'email' => 'ada@example.com',
            'password' => 'correct-horse-battery',
            'password_confirmation' => 'correct-horse-battery',
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $user = User::query()->where('email', 'ada@example.com')->sole();

    expect(Hash::check('correct-horse-battery', $user->password))->toBeTrue();
});

it('requires a confirmed password when creating a user', function (): void {
    livewire(CreateUser::class)
        ->fillForm([
            'name' => 'Ada Lovelace',
            'email' => 'ada@example.com',
            'password' => 'correct-horse-battery',
            'password_confirmation' => 'something-else',
        ])
        ->call('create')
        ->assertHasFormErrors(['password' => 'confirmed']);
});

it('rejects an email that is already taken', function (): void {
    User::factory()->create(['email' => 'taken@example.com']);

    livewire(CreateUser::class)
        ->fillForm([
            'name' => 'Copycat',
            'email' => 'taken@example.com',
            'password' => 'correct-horse-battery',
            'password_confirmation' => 'correct-horse-battery',
        ])
        ->call('create')
        ->assertHasFormErrors(['email' => 'unique']);
});

it('keeps the current password when it is left blank on edit', function (): void {
    $user = User::factory()->create(['password' => 'original-password']);

    livewire(EditUser::class, ['record' => $user->getRouteKey()])
        ->fillForm(['name' => 'Renamed'])
        ->call('save')
        ->assertHasNoFormErrors();

    $user->refresh();

    expect($user->name)->toBe('Renamed')
        ->and(Hash::check('original-password', $user->password))->toBeTrue();
});

it('changes the password on edit when a new one is entered', function (): void {
    $user = User::factory()->create(['password' => 'original-password']);

    livewire(EditUser::class, ['record' => $user->getRouteKey()])
        ->fillForm([
            'password' => 'brand-new-password',
            'password_confirmation' => 'brand-new-password',
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    expect(Hash::check('brand-new-password', $user->refresh()->password))->toBeTrue();
});

it('lets users delete other accounts but not their own', function (): void {
    $other = User::factory()->create();

    livewire(EditUser::class, ['record' => $this->admin->getRouteKey()])
        ->assertActionHidden(DeleteAction::class);

    livewire(EditUser::class, ['record' => $other->getRouteKey()])
        ->callAction(DeleteAction::class);

    expect(User::query()->find($other->getKey()))->toBeNull();
});

it('does not let users bulk delete their own account', function (): void {
    $other = User::factory()->create();

    livewire(ListUsers::class)
        ->selectTableRecords([$this->admin->getKey(), $other->getKey()])
        ->callAction(TestAction::make(DeleteBulkAction::class)->table()->bulk());

    expect(User::query()->find($this->admin->getKey()))->not->toBeNull()
        ->and(User::query()->find($other->getKey()))->toBeNull();
});
