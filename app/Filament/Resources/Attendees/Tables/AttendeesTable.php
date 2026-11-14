<?php

namespace App\Filament\Resources\Attendees\Tables;

use App\Enums\ConferenceStatus;
use App\Enums\Region;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\Indicator;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class AttendeesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('email')
                    ->label('Email address')
                    ->icon('heroicon-m-envelope')
                    ->copyable()
                    ->searchable()
                    ->sortable(),
                TextColumn::make('conference.name')
                    ->badge()
                    ->searchable()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label('Signed up')
                    ->since()
                    ->dateTimeTooltip()
                    ->sortable(),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('conference')
                    ->relationship('conference', 'name')
                    ->multiple()
                    ->searchable()
                    ->preload(),
                self::conferenceAttributeFilter('conference_status', 'Conference status', 'status', ConferenceStatus::class),
                self::conferenceAttributeFilter('region', 'Region', 'region', Region::class),
                self::conferenceTimingFilter(),
                self::signedUpBetweenFilter(),
            ])
            ->filtersFormColumns(2)
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    /**
     * Filters attendees by one or more values of an enum column on their conference.
     *
     * @param  class-string<\UnitEnum>  $enum
     */
    protected static function conferenceAttributeFilter(string $name, string $label, string $column, string $enum): SelectFilter
    {
        return SelectFilter::make($name)
            ->label($label)
            ->options($enum)
            ->multiple()
            ->query(fn (Builder $query, array $data): Builder => $query->when(
                $data['values'] ?? [],
                fn (Builder $query, array $values): Builder => $query->whereHas(
                    'conference',
                    fn (Builder $query): Builder => $query->whereIn($column, $values),
                ),
            ));
    }

    /**
     * Filters attendees by whether their conference has ended yet.
     */
    protected static function conferenceTimingFilter(): TernaryFilter
    {
        return TernaryFilter::make('conference_timing')
            ->label('Conference dates')
            ->placeholder('All conferences')
            ->trueLabel('Upcoming or ongoing')
            ->falseLabel('Ended')
            ->queries(
                true: fn (Builder $query): Builder => $query->whereHas('conference', fn (Builder $query): Builder => $query->where('end_date', '>=', now())),
                false: fn (Builder $query): Builder => $query->whereHas('conference', fn (Builder $query): Builder => $query->where('end_date', '<', now())),
                blank: fn (Builder $query): Builder => $query,
            );
    }

    /**
     * Filters attendees by the date range they signed up in.
     */
    protected static function signedUpBetweenFilter(): Filter
    {
        return Filter::make('signed_up')
            ->form([
                DatePicker::make('from')
                    ->label('Signed up from'),
                DatePicker::make('until')
                    ->label('Signed up until'),
            ])
            ->columns(2)
            ->columnSpanFull()
            ->query(fn (Builder $query, array $data): Builder => $query
                ->when($data['from'] ?? null, fn (Builder $query, string $date): Builder => $query->whereDate('created_at', '>=', $date))
                ->when($data['until'] ?? null, fn (Builder $query, string $date): Builder => $query->whereDate('created_at', '<=', $date)))
            ->indicateUsing(function (array $data): array {
                $indicators = [];

                if ($data['from'] ?? null) {
                    $indicators[] = Indicator::make('Signed up from '.$data['from'])->removeField('from');
                }

                if ($data['until'] ?? null) {
                    $indicators[] = Indicator::make('Signed up until '.$data['until'])->removeField('until');
                }

                return $indicators;
            });
    }
}
