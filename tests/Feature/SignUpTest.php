<?php

use App\Livewire\SignUp;
use App\Models\Attendee;
use App\Models\Conference;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Features\SupportLockedProperties\CannotUpdateLockedPropertyException;

use function Pest\Livewire\livewire;

beforeEach(function (): void {
    $this->withoutVite();
    RateLimiter::clear('sign-up:127.0.0.1');
});

it('renders the public sign up page', function (): void {
    $conference = Conference::factory()->listed()->create(['name' => 'Laracon EU']);

    $this->get(route('sign-up'))
        ->assertOk()
        ->assertSee('Save your seat')
        ->assertSee('Laracon EU');
});

it('signs a visitor up for a conference', function (): void {
    $conference = Conference::factory()->listed()->create(['name' => 'Laracon EU']);

    livewire(SignUp::class)
        ->fillForm([
            'conference_id' => $conference->getKey(),
            'name' => 'Ada Lovelace',
            'email' => 'ada@example.com',
        ])
        ->call('signUp')
        ->assertHasNoFormErrors()
        ->assertSee('signed up, Ada Lovelace!');

    expect(Attendee::query()->sole())
        ->conference_id->toBe($conference->getKey())
        ->name->toBe('Ada Lovelace')
        ->email->toBe('ada@example.com');
});

it('preselects a listed conference from the query string', function (): void {
    $conference = Conference::factory()->listed()->create();

    livewire(SignUp::class, ['preselectedConference' => $conference->getKey()])
        ->assertSchemaStateSet(['conference_id' => $conference->getKey()]);
});

it('does not preselect or accept conferences that are not listed', function (): void {
    $unlisted = Conference::factory()->listed()->create(['is_published' => false]);

    livewire(SignUp::class, ['preselectedConference' => $unlisted->getKey()])
        ->assertSchemaStateSet(['conference_id' => null])
        ->fillForm([
            'conference_id' => $unlisted->getKey(),
            'name' => 'Ada Lovelace',
            'email' => 'ada@example.com',
        ])
        ->call('signUp')
        ->assertHasFormErrors(['conference_id']);

    expect(Attendee::query()->count())->toBe(0);
});

it('validates the attendee details', function (array $data, array $errors): void {
    $conference = Conference::factory()->listed()->create();

    livewire(SignUp::class)
        ->fillForm(['conference_id' => $conference->getKey(), 'name' => 'Ada', 'email' => 'ada@example.com', ...$data])
        ->call('signUp')
        ->assertHasFormErrors($errors);
})->with([
    'missing name' => [['name' => ''], ['name' => 'required']],
    'missing email' => [['email' => ''], ['email' => 'required']],
    'invalid email' => [['email' => 'not-an-email'], ['email' => 'email']],
    'missing conference' => [['conference_id' => null], ['conference_id' => 'required']],
]);

it('allows one sign up per email for each conference', function (): void {
    $conference = Conference::factory()->listed()->create();
    $otherConference = Conference::factory()->listed()->create();
    Attendee::factory()->for($conference)->create(['email' => 'ada@example.com']);

    livewire(SignUp::class)
        ->fillForm(['conference_id' => $conference->getKey(), 'name' => 'Ada', 'email' => 'ada@example.com'])
        ->call('signUp')
        ->assertHasFormErrors(['email' => 'unique']);

    livewire(SignUp::class)
        ->fillForm(['conference_id' => $otherConference->getKey(), 'name' => 'Ada', 'email' => 'ada@example.com'])
        ->call('signUp')
        ->assertHasNoFormErrors();

    expect(Attendee::query()->where('email', 'ada@example.com')->count())->toBe(2);
});

it('rate limits sign ups from the same visitor', function (): void {
    $conference = Conference::factory()->listed()->create();
    $component = livewire(SignUp::class);

    foreach (range(1, SignUp::MAX_ATTEMPTS_PER_MINUTE) as $attempt) {
        $component
            ->fillForm(['conference_id' => $conference->getKey(), 'name' => 'Ada', 'email' => "ada{$attempt}@example.com"])
            ->call('signUp')
            ->assertHasNoFormErrors()
            ->call('signUpAnother');
    }

    $component
        ->fillForm(['conference_id' => $conference->getKey(), 'name' => 'Ada', 'email' => 'one-too-many@example.com'])
        ->call('signUp')
        ->assertHasErrors(['data.email']);

    expect(Attendee::query()->count())->toBe(SignUp::MAX_ATTEMPTS_PER_MINUTE);
});

it('does not let visitors change the confirmed attendee', function (): void {
    $someoneElse = Attendee::factory()->create();

    livewire(SignUp::class)
        ->set('attendeeId', $someoneElse->getKey());
})->throws(CannotUpdateLockedPropertyException::class);

it('links to the sign up page from the landing page', function (): void {
    $conference = Conference::factory()->listed()->create();

    $this->get(route('home'))
        ->assertOk()
        ->assertSee(route('sign-up', ['conference' => $conference->getKey()]), escape: false);
});
