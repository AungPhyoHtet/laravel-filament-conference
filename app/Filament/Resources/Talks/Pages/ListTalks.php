<?php

namespace App\Filament\Resources\Talks\Pages;

use App\Enums\TalkStatus;
use App\Filament\Resources\Talks\TalkResource;
use App\Models\Talk;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListTalks extends ListRecords
{
    protected static string $resource = TalkResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }

    /**
     * An "All" tab followed by one tab per talk status, each showing its talk count.
     *
     * @return array<string, Tab>
     */
    public function getTabs(): array
    {
        $talkCountsByStatus = Talk::query()
            ->toBase()
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $statusTabs = collect(TalkStatus::cases())
            ->mapWithKeys(fn (TalkStatus $status): array => [
                $status->value => Tab::make($status->getLabel())
                    ->badge($talkCountsByStatus->get($status->value, 0))
                    ->badgeColor($status->getColor())
                    ->modifyQueryUsing(fn (Builder $query): Builder => $query->where('status', $status)),
            ])
            ->all();

        return [
            'all' => Tab::make('All')->badge($talkCountsByStatus->sum()),
            ...$statusTabs,
        ];
    }
}
