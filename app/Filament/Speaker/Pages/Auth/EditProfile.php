<?php

namespace App\Filament\Speaker\Pages\Auth;

use App\Filament\Concerns\BuildsFormSections;
use App\Filament\Resources\Speakers\Schemas\SpeakerForm;
use Filament\Auth\Pages\EditProfile as BaseEditProfile;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Eloquent\Model;
use SensitiveParameter;

class EditProfile extends BaseEditProfile
{
    use BuildsFormSections;

    /**
     * The account details sign the speaker in; the speaker details are what attendees see on the conference site.
     */
    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                self::section('Account', 'Your name and email are also shown on your speaker profile.', Heroicon::OutlinedUser)
                    ->schema([
                        $this->getNameFormComponent(),
                        $this->getEmailFormComponent(),
                        $this->getPasswordFormComponent(),
                        $this->getPasswordConfirmationFormComponent(),
                        $this->getCurrentPasswordFormComponent(),
                    ]),
                self::section('Speaker profile', 'How you appear to attendees.', Heroicon::OutlinedMicrophone)
                    ->relationship('speaker')
                    ->schema([
                        SpeakerForm::avatarField(),
                        SpeakerForm::twitterHandleField(),
                        SpeakerForm::bioField(),
                    ]),
                SpeakerForm::qualificationsSection()
                    ->relationship('speaker'),
            ]);
    }

    /**
     * The base page only updates the user, so the speaker profile sections are saved here too.
     *
     * @param  array<string, mixed>  $data
     */
    protected function handleRecordUpdate(Model $record, #[SensitiveParameter] array $data): Model
    {
        $record = parent::handleRecordUpdate($record, $data);

        $this->form->model($record)->saveRelationships();

        return $record;
    }
}
