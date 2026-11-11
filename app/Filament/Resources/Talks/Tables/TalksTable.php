<?php

namespace App\Filament\Resources\Talks\Tables;

use App\Enums\TalkLength;
use App\Enums\TalkStatus;
use App\Filament\Exports\TalkExporter;
use App\Models\Talk;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ExportAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\TextInputColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Collection;

class TalksTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->persistFiltersInSession()
            ->filtersTriggerAction(fn (Action $action): Action => $action->button()->label('Filters'))
            ->columns([
                TextInputColumn::make('title')
                    ->rules(['required', 'max:255'])
                    ->searchable()
                    ->sortable(),
                TextColumn::make('abstract')
                    ->extraAttributes(['style' => 'min-width: 32rem'])
                    ->wrap()
                    ->lineClamp(2)
                    ->tooltip(fn (Talk $record): string => $record->abstract),
                ImageColumn::make('speaker.avatar')
                    ->label('Avatar')
                    ->disk('public')
                    ->imageHeight(40)
                    ->circular()
                    ->defaultImageUrl(fn (Talk $record): string => 'https://ui-avatars.com/api/?'.http_build_query([
                        'name' => $record->speaker->name,
                        'background' => '0D8ABC',
                        'color' => 'fff',
                    ])),
                TextColumn::make('speaker.name')
                    ->searchable(),
                TextColumn::make('status')
                    ->badge()
                    ->sortable(),
                TextColumn::make('length')
                    ->sortable(),
                ToggleColumn::make('new_talk')
                    ->label('New')
                    ->sortable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->headerActions([
                self::exportAction(),
            ])
            ->filters([
                SelectFilter::make('length')
                    ->options(TalkLength::class)
                    ->multiple(),
                SelectFilter::make('speaker')
                    ->relationship('speaker', 'name')
                    ->searchable()
                    ->preload(),
                TernaryFilter::make('new_talk')
                    ->label('New talk'),
                TrashedFilter::make(),
            ])
            ->recordActions([
                EditAction::make(),
                ActionGroup::make([
                    self::approveAction(),
                    self::rejectAction(),
                ]),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    self::approveBulkAction(),
                    self::rejectBulkAction(),
                    DeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                ]),
            ]);
    }

    protected static function approveAction(): Action
    {
        return Action::make('approve')
            ->icon(Heroicon::OutlinedCheckCircle)
            ->color('success')
            ->requiresConfirmation()
            ->modalDescription(fn (Talk $record): string => "Approve \"{$record->title}\"?")
            ->hidden(fn (Talk $record): bool => $record->isApproved())
            ->successNotification(
                Notification::make()
                    ->success()
                    ->title('Talk approved')
                    ->body('The talk has been marked as approved.')
            )
            ->action(function (Talk $record): void {
                $record->approve();
            });
    }

    protected static function rejectAction(): Action
    {
        return Action::make('reject')
            ->icon(Heroicon::OutlinedXCircle)
            ->color('danger')
            ->requiresConfirmation()
            ->modalDescription(fn (Talk $record): string => "Reject \"{$record->title}\"?")
            ->hidden(fn (Talk $record): bool => $record->isRejected())
            ->successNotification(
                Notification::make()
                    ->danger()
                    ->title('Talk rejected')
                    ->body('The talk has been marked as rejected.')
            )
            ->action(function (Talk $record): void {
                $record->reject();
            });
    }

    protected static function approveBulkAction(): BulkAction
    {
        return BulkAction::make('approve')
            ->label('Approve selected')
            ->icon(Heroicon::OutlinedCheckCircle)
            ->color('success')
            ->requiresConfirmation()
            ->successNotificationTitle('Talks approved')
            ->deselectRecordsAfterCompletion()
            ->action(function (Collection $records): void {
                $records->each(fn (Talk $talk) => $talk->approve());
            });
    }

    protected static function rejectBulkAction(): BulkAction
    {
        return BulkAction::make('reject')
            ->label('Reject selected')
            ->icon(Heroicon::OutlinedXCircle)
            ->color('danger')
            ->requiresConfirmation()
            ->successNotificationTitle('Talks rejected')
            ->deselectRecordsAfterCompletion()
            ->action(function (Collection $records): void {
                $records->each(fn (Talk $talk) => $talk->reject());
            });
    }

    /**
     * Exports the talks visible in the table, so the active status tab, filters and search all apply.
     */
    protected static function exportAction(): ExportAction
    {
        return ExportAction::make()
            ->exporter(TalkExporter::class)
            ->icon(Heroicon::OutlinedArrowDownTray)
            ->tooltip(fn (ListRecords $livewire): string => self::exportTooltip($livewire->activeTab));
    }

    protected static function exportTooltip(?string $activeTab): string
    {
        $status = TalkStatus::tryFrom((string) $activeTab);

        if ($status === null) {
            return 'Export all talks shown in the table';
        }

        return "Export {$status->getLabel()} talks shown in the table";
    }
}
