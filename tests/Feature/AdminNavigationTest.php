<?php

use App\Models\User;

it('renders the admin sidebar with the navigation groups', function (): void {
    $this->actingAs(User::factory()->create())
        ->get('/admin')
        ->assertOk()
        ->assertSeeInOrder(['Events', 'Conferences', 'Venues', 'Program', 'Speakers', 'Talks', 'People', 'Attendees', 'Users']);
});
