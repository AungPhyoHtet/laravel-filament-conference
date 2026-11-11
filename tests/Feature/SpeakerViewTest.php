<?php

use App\Enums\SpeakerQualification;
use App\Enums\TalkStatus;
use App\Filament\Resources\Speakers\Pages\ListSpeakers;
use App\Filament\Resources\Speakers\Pages\ViewSpeaker;
use App\Filament\Resources\Speakers\SpeakerResource;
use App\Models\Conference;
use App\Models\Speaker;
use App\Models\Talk;
use App\Models\User;
use Filament\Actions\CreateAction;
use Filament\Actions\EditAction;
use Filament\Actions\Testing\TestAction;
use Filament\Actions\ViewAction;

use function Pest\Livewire\livewire;

beforeEach(function (): void {
    $this->actingAs(User::factory()->create());
});

it('shows the speaker profile, qualifications, talks and conferences', function (): void {
    $speaker = Speaker::factory()->create([
        'name' => 'Ada Lovelace',
        'email' => 'ada@example.com',
        'qualifications' => [SpeakerQualification::PublishedAuthor->value],
    ]);
    Talk::factory()->for($speaker)->create(['title' => 'Notes on the Engine', 'status' => TalkStatus::Approved]);
    $speaker->conferences()->attach(Conference::factory()->create(['name' => 'Laracon EU']));

    livewire(ViewSpeaker::class, ['record' => $speaker->getRouteKey()])
        ->assertOk()
        ->assertSchemaStateSet([
            'name' => 'Ada Lovelace',
            'email' => 'ada@example.com',
        ])
        ->assertSee('Published Author')
        ->assertSee('Notes on the Engine')
        ->assertSee('Approved')
        ->assertSee('Laracon EU');
});

it('shows placeholders for a speaker with no talks or conferences', function (): void {
    $speaker = Speaker::factory()->create(['qualifications' => []]);

    livewire(ViewSpeaker::class, ['record' => $speaker->getRouteKey()])
        ->assertOk()
        ->assertSee('No qualifications listed.')
        ->assertSee('No talks yet.')
        ->assertSee('Not booked for any conferences.');
});

it('renders the rich text bio as sanitised html', function (): void {
    $speaker = Speaker::factory()->create([
        'bio' => '<p><strong>First</strong> programmer.</p><script>alert("xss")</script>',
    ]);

    livewire(ViewSpeaker::class, ['record' => $speaker->getRouteKey()])
        ->assertSeeHtml('<strong>First</strong> programmer.')
        ->assertDontSeeHtml('<script>alert("xss")</script>');
});

it('loads factory bios into the rich editor as paragraphs', function (): void {
    $speaker = Speaker::factory()->create();

    $editor = livewire(ListSpeakers::class)
        ->mountAction(TestAction::make(EditAction::class)->table($speaker))
        ->instance()
        ->mountedActions[0]['data']['bio'];

    expect($editor['content'][0]['type'])->toBe('paragraph');
});

it('links from the list to the view page', function (): void {
    $speaker = Speaker::factory()->create();

    livewire(ListSpeakers::class)
        ->assertActionHasUrl(TestAction::make(ViewAction::class)->table($speaker), SpeakerResource::getUrl('view', ['record' => $speaker]));
});

it('creates speakers in a slide-over', function (): void {
    livewire(ListSpeakers::class)
        ->mountAction(CreateAction::class)
        ->assertSeeInOrder(['Profile', 'Qualifications'])
        ->fillForm([
            'name' => 'Ada Lovelace',
            'email' => 'ada@example.com',
            'bio' => 'First programmer.',
            'twitter_handle' => 'ada',
        ])
        ->callMountedAction()
        ->assertHasNoFormErrors();

    livewire(ListSpeakers::class)
        ->assertActionExists(CreateAction::class, fn (CreateAction $action): bool => $action->isModalSlideOver());

    expect(Speaker::query()->sole()->name)->toBe('Ada Lovelace');
});

it('edits speakers in a slide-over from the table and the view page', function (): void {
    $speaker = Speaker::factory()->create(['name' => 'Ada Lovelace']);
    $isSlideOver = fn (EditAction $action): bool => $action->isModalSlideOver();

    livewire(ListSpeakers::class)
        ->assertActionExists(TestAction::make(EditAction::class)->table($speaker), $isSlideOver)
        ->callAction(TestAction::make(EditAction::class)->table($speaker), data: ['name' => 'Ada King'])
        ->assertHasNoFormErrors();

    expect($speaker->refresh()->name)->toBe('Ada King');

    livewire(ViewSpeaker::class, ['record' => $speaker->getRouteKey()])
        ->assertActionExists(EditAction::class, $isSlideOver)
        ->mountAction(EditAction::class)
        ->assertSchemaStateSet(['name' => 'Ada King'])
        ->fillForm(['name' => 'Countess of Lovelace'])
        ->callMountedAction()
        ->assertHasNoFormErrors();

    expect($speaker->refresh()->name)->toBe('Countess of Lovelace');
});

it('builds a twitter profile link from the handle', function (?string $handle, ?string $url): void {
    expect(Speaker::factory()->make(['twitter_handle' => $handle])->twitterUrl())->toBe($url);
})->with([
    'plain handle' => ['adalovelace', 'https://x.com/adalovelace'],
    'handle with @' => ['@adalovelace', 'https://x.com/adalovelace'],
    'unsafe characters are encoded' => ['ada"><script>', 'https://x.com/ada%22%3E%3Cscript%3E'],
    'blank handle' => ['  ', null],
]);

it('links the twitter handle on the view page and in the table', function (): void {
    $speaker = Speaker::factory()->create(['twitter_handle' => 'adalovelace']);

    livewire(ViewSpeaker::class, ['record' => $speaker->getRouteKey()])
        ->assertSeeHtml('href="https://x.com/adalovelace"');

    livewire(ListSpeakers::class)
        ->assertSeeHtml('href="https://x.com/adalovelace"');
});
