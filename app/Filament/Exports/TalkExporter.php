<?php

namespace App\Filament\Exports;

use App\Enums\TalkLength;
use App\Enums\TalkStatus;
use App\Models\Talk;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;
use Illuminate\Support\Str;

class TalkExporter extends Exporter
{
    protected static ?string $model = Talk::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('id')
                ->label('ID'),
            ExportColumn::make('title')
                ->preventFormulaInjection(),
            ExportColumn::make('abstract')
                ->preventFormulaInjection(),
            ExportColumn::make('speaker.name')
                ->label('Speaker')
                ->preventFormulaInjection(),
            ExportColumn::make('status')
                ->formatStateUsing(fn (TalkStatus $state): string => $state->getLabel()),
            ExportColumn::make('length')
                ->formatStateUsing(fn (TalkLength $state): string => $state->getLabel()),
            ExportColumn::make('new_talk')
                ->label('New talk')
                ->formatStateUsing(fn (bool $state): string => $state ? 'Yes' : 'No'),
            ExportColumn::make('created_at')
                ->enabledByDefault(false),
            ExportColumn::make('updated_at')
                ->enabledByDefault(false),
            ExportColumn::make('deleted_at')
                ->enabledByDefault(false),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'Your talk export has completed and '.Str::of('row')->counted($export->successful_rows).' exported.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' '.Str::of('row')->counted($failedRowsCount).' failed to export.';
        }

        return $body;
    }
}
