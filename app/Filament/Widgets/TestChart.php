<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\Attendees\AttendeeResource;
use Filament\Actions\Action;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Notifications\Notification;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Widgets\Widget;

class TestChart extends Widget implements HasActions, HasSchemas
{
    use InteractsWithActions;
    use InteractsWithSchemas;

    protected string $view = 'filament.widgets.test-chart';

    protected int|string|array $columnSpan = 'full';

    public function callNotificationAction(): Action
    {
        return Action::make('callNotification')
            ->button()
            ->color('warning')
            ->label('Send a notification')
            ->action(function (): void {
                Notification::make()
                    ->warning()
                    ->title('You sent a notification')
                    ->body('This is a test')
                    ->persistent()
                    ->actions([
                        Action::make('goToAttendees')
                            ->button()
                            ->color('primary')
                            ->url(AttendeeResource::getUrl('index')),
                        Action::make('undo')
                            ->link()
                            ->color('gray')
                            ->close(),
                    ])
                    ->send();
            });
    }
}
