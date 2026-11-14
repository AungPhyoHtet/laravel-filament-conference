<?php

namespace App\Filament\Speaker\Resources\Talks;

use App\Enums\TalkStatus;
use App\Filament\Speaker\Resources\Talks\Pages\CreateTalk;
use App\Filament\Speaker\Resources\Talks\Pages\EditTalk;
use App\Filament\Speaker\Resources\Talks\Pages\ListTalks;
use App\Filament\Speaker\Resources\Talks\Schemas\TalkForm;
use App\Filament\Speaker\Resources\Talks\Tables\TalksTable;
use App\Models\Talk;
use App\Models\User;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Auth\Access\Response;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class TalkResource extends Resource
{
    protected static ?string $model = Talk::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedMicrophone;

    protected static ?string $navigationLabel = 'My talks';

    protected static ?string $recordTitleAttribute = 'title';

    public static function form(Schema $schema): Schema
    {
        return TalkForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return TalksTable::configure($table);
    }

    /**
     * Speakers only ever see their own talks, which also stops them opening anyone else's talk by URL.
     */
    public static function getEloquentQuery(): Builder
    {
        /** @var User $user */
        $user = auth()->user();

        return parent::getEloquentQuery()->whereBelongsTo($user->speaker);
    }

    /**
     * Talks can only be changed while they are waiting for review.
     */
    public static function getEditAuthorizationResponse(Model $record): Response
    {
        if (! ($record instanceof Talk && $record->status === TalkStatus::Submitted)) {
            return Response::deny('This talk has already been reviewed, so it can no longer be changed.');
        }

        return parent::getEditAuthorizationResponse($record);
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
