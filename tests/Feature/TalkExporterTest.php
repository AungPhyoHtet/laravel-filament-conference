<?php

use App\Enums\TalkLength;
use App\Enums\TalkStatus;
use App\Filament\Exports\TalkExporter;
use App\Filament\Resources\Talks\Pages\ListTalks;
use App\Models\Speaker;
use App\Models\Talk;
use App\Models\User;
use Filament\Actions\ExportAction;
use Filament\Actions\Exports\Models\Export;
use Filament\Actions\Testing\TestAction;
use Illuminate\Database\Eloquent\Builder;

use function Pest\Livewire\livewire;

beforeEach(function (): void {
    $this->actingAs(User::factory()->create());
});

it('starts a talk export from the table header', function (): void {
    $talks = Talk::factory(2)->create();
    $exports = ExportAction::fake();

    livewire(ListTalks::class)
        ->callAction(TestAction::make('export')->table())
        ->assertHasNoFormErrors();

    $exports->assertDispatched(TalkExporter::class, function (Export $export, Builder $query) use ($talks): bool {
        expect($query->pluck('id')->sort()->values()->all())->toBe($talks->modelKeys());

        return true;
    });
});

it('exports only the talks in the active status tab', function (): void {
    $approved = Talk::factory()->create(['status' => TalkStatus::Approved]);
    Talk::factory()->create(['status' => TalkStatus::Rejected]);
    $exports = ExportAction::fake();

    livewire(ListTalks::class)
        ->set('activeTab', TalkStatus::Approved->value)
        ->callAction(TestAction::make('export')->table());

    $exports->assertDispatched(TalkExporter::class, function (Export $export, Builder $query) use ($approved): bool {
        expect($query->pluck('id')->all())->toBe([$approved->getKey()]);

        return true;
    });
});

it('exports readable labels and guards against formula injection', function (): void {
    $talk = Talk::factory()
        ->for(Speaker::factory()->state(['name' => 'Ada Lovelace']))
        ->create([
            'title' => '=HYPERLINK("evil")',
            'status' => TalkStatus::Approved,
            'length' => TalkLength::Keynote,
            'new_talk' => false,
        ]);

    expect(TalkExporter::test()->export($talk))->toBe([
        (string) $talk->getKey(),
        '\'=HYPERLINK("evil")',
        $talk->plainAbstract(),
        'Ada Lovelace',
        'Approved',
        'Keynote',
        'No',
    ]);
});

it('exports the abstract as plain text with formula injection guarded', function (): void {
    $talk = Talk::factory()->create(['abstract' => '<p>=SUM(A1) &amp; more</p><ul><li>One</li></ul>']);

    expect(TalkExporter::test()->export($talk)[2])->toBe('\'=SUM(A1) & more One');
});

it('describes the active status tab in the export tooltip', function (?string $activeTab, string $tooltip): void {
    livewire(ListTalks::class)
        ->set('activeTab', $activeTab)
        ->assertActionExists(TestAction::make('export')->table(), function (ExportAction $action) use ($tooltip): bool {
            expect($action->getTooltip())->toBe($tooltip);

            return true;
        });
})->with([
    'all tab' => ['all', 'Export all talks shown in the table'],
    'approved tab' => [TalkStatus::Approved->value, 'Export Approved talks shown in the table'],
    'rejected tab' => [TalkStatus::Rejected->value, 'Export Rejected talks shown in the table'],
]);
