<?php

namespace App\Livewire;

use App\Models\Attendee;
use App\Models\Conference;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Schemas\Schema;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\Rules\Unique;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;

/**
 * Public conference sign-up page.
 *
 * @property-read Schema $form
 * @property-read Collection<int, Conference> $listedConferences
 */
#[Layout('components.layouts.app')]
#[Title('Sign up')]
class SignUp extends Component implements HasSchemas
{
    use InteractsWithSchemas;

    /**
     * Sign-ups allowed per visitor IP address each minute.
     */
    public const int MAX_ATTEMPTS_PER_MINUTE = 5;

    /**
     * @var array<string, mixed>|null
     */
    public ?array $data = [];

    /**
     * Conference to preselect, from the `?conference=` query string.
     */
    #[Url(as: 'conference', history: false)]
    public ?int $preselectedConference = null;

    /**
     * Locked so visitors cannot swap it client-side to view someone else's sign-up.
     */
    #[Locked]
    public ?int $attendeeId = null;

    public function mount(): void
    {
        $this->form->fill([
            'conference_id' => $this->listedConferences->has($this->preselectedConference ?? 0)
                ? $this->preselectedConference
                : null,
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('conference_id')
                    ->label('Conference')
                    ->options(fn (): array => $this->listedConferences
                        ->map(fn (Conference $conference): string => "{$conference->name} · {$conference->dateRange()}")
                        ->all())
                    ->in(fn (): array => $this->listedConferences->keys()->all())
                    ->native(false)
                    ->required(),
                TextInput::make('name')
                    ->label('Full name')
                    ->autocomplete('name')
                    ->maxLength(255)
                    ->required(),
                TextInput::make('email')
                    ->label('Email address')
                    ->email()
                    ->autocomplete('email')
                    ->maxLength(255)
                    ->required()
                    ->unique(
                        table: Attendee::class,
                        modifyRuleUsing: fn (Unique $rule, Get $get): Unique => $rule->where('conference_id', $get('conference_id')),
                    )
                    ->validationMessages([
                        'unique' => 'This email address is already signed up for this conference.',
                    ]),
            ])
            ->statePath('data');
    }

    /**
     * Validate the form and register the visitor as an attendee.
     *
     * @throws ValidationException
     */
    public function signUp(): void
    {
        $throttleKey = 'sign-up:'.request()->ip();

        if (RateLimiter::tooManyAttempts($throttleKey, self::MAX_ATTEMPTS_PER_MINUTE)) {
            throw ValidationException::withMessages([
                'data.email' => 'Too many sign-up attempts. Please try again in '.RateLimiter::availableIn($throttleKey).' seconds.',
            ]);
        }

        RateLimiter::hit($throttleKey);

        $attendee = Attendee::query()->create($this->form->getState());

        $this->attendeeId = $attendee->getKey();
    }

    /**
     * Clear the confirmation so the visitor can sign someone else up.
     */
    public function signUpAnother(): void
    {
        $this->attendeeId = null;
        $this->form->fill();
    }

    public function render(): View
    {
        return view('livewire.sign-up', [
            'attendee' => $this->attendeeId
                ? Attendee::query()->with('conference.venue')->find($this->attendeeId)
                : null,
        ]);
    }

    /**
     * Publicly listed conferences that are open for sign-up, keyed by ID.
     *
     * @return Collection<int, Conference>
     */
    #[Computed]
    public function listedConferences(): Collection
    {
        return Conference::query()->listed()->get()->keyBy('id')->toBase();
    }
}
