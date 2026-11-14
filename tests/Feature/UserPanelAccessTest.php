<?php

use App\Enums\UserRole;
use App\Models\User;
use Filament\Facades\Filament;

it('lets each kind of user into only the panels meant for them', function (string $kind, string $panelId, bool $canAccess): void {
    $user = match ($kind) {
        'admin' => User::factory()->create(),
        'speaker' => User::factory()->speaker()->create(),
        'speaker without profile' => User::factory()->create(['role' => UserRole::Speaker]),
    };

    expect($user->canAccessPanel(Filament::getPanel($panelId)))->toBe($canAccess);
})->with([
    'admin in the admin panel' => ['admin', 'app', true],
    'admin in the speaker panel' => ['admin', 'speaker', false],
    'speaker in the admin panel' => ['speaker', 'app', false],
    'speaker in the speaker panel' => ['speaker', 'speaker', true],
    'speaker without a profile in the speaker panel' => ['speaker without profile', 'speaker', false],
]);

it('forbids speakers from opening the admin panel', function (): void {
    $this->actingAs(User::factory()->speaker()->create())
        ->get('/admin')
        ->assertForbidden();
});

it('forbids admins from opening the speaker panel', function (): void {
    $this->actingAs(User::factory()->create())
        ->get('/speaker')
        ->assertForbidden();
});

it('only offers sign-up on the speaker panel', function (): void {
    $this->get('/admin/register')->assertNotFound();
    $this->get('/speaker/register')->assertOk();
});

it('lets a speaker open the speaker panel and their talks', function (): void {
    $this->actingAs(User::factory()->speaker()->create());

    $this->get('/speaker')->assertOk()->assertSee('My talks');
    $this->get('/speaker/talks')->assertOk();
});
