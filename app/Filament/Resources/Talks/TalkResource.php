<?php

namespace App\Filament\Resources\Talks;

use App\Filament\Resources\Talks\Pages\CreateTalk;
use App\Filament\Resources\Talks\Pages\EditTalk;
use App\Filament\Resources\Talks\Pages\ListTalks;
use App\Filament\Resources\Talks\Schemas\TalkForm;
use App\Filament\Resources\Talks\Tables\TalksTable;
use App\Models\Talk;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use UnitEnum;

class TalkResource extends Resource
{
    protected static ?string $model = Talk::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedMicrophone;

    protected static string|UnitEnum|null $navigationGroup = 'Program';

    protected static ?string $recordTitleAttribute = 'title';

    public static function form(Schema $schema): Schema
    {
        return TalkForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return TalksTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    /**
     * Let the edit page open soft-deleted talks so they can be restored or force deleted.
     *
     * @return Builder<Talk>
     */
    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListTalks::route('/'),
            'create' => CreateTalk::route('/create'),
            'edit' => EditTalk::route('/{record}/edit'),
        ];
    }
}
