<?php

use App\Models\User;

test('redirects guests from the panel home to the login page', function (): void {
    $this->get('/admin')->assertRedirect(route('filament.app.auth.login'));
});

test('shows the panel home to signed-in users', function (): void {
    $this->actingAs(User::factory()->create())
        ->get('/admin')
        ->assertOk();
});
