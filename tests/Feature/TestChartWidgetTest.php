<?php

use App\Filament\Widgets\TestChart;
use App\Models\User;

use function Pest\Livewire\livewire;

beforeEach(function (): void {
    $this->actingAs(User::factory()->create());
});

it('sends a notification when the action is called', function (): void {
    livewire(TestChart::class)
        ->assertSee('Send a notification')
        ->callAction('callNotification')
        ->assertNotified('You sent a notification');
});
