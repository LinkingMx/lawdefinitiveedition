<?php

declare(strict_types=1);

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use App\Mail\TwoFactorDisabledMail;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Mail;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('disable2fa')
                ->label('Disable 2FA')
                ->icon('heroicon-o-device-phone-mobile')
                ->color('warning')
                ->requiresConfirmation()
                ->modalHeading('Disable Two-Factor Authentication')
                ->modalDescription('Are you sure you want to disable two-factor authentication for this user? They will be notified via email.')
                ->modalSubmitActionLabel('Yes, Disable 2FA')
                ->visible(fn () => $this->record->two_factor_confirmed_at !== null)
                ->action(function () {
                    $this->record->update([
                        'two_factor_secret' => null,
                        'two_factor_recovery_codes' => null,
                        'two_factor_confirmed_at' => null,
                    ]);

                    // Send notification email
                    Mail::to($this->record->email)->send(new TwoFactorDisabledMail($this->record));

                    Notification::make()
                        ->success()
                        ->icon('heroicon-o-device-phone-mobile')
                        ->title('Two-Factor Authentication Disabled')
                        ->body('2FA has been removed and the user has been notified via email.')
                        ->send();

                    // Refresh the form to update the 2FA status display
                    $this->fillForm();
                }),

            Actions\DeleteAction::make(),
            Actions\ForceDeleteAction::make(),
            Actions\RestoreAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getSavedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->icon('heroicon-o-users')
            ->title('User Updated')
            ->body('The user has been updated successfully.');
    }
}
