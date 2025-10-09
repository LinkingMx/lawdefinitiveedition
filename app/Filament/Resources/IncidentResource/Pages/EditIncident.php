<?php

declare(strict_types=1);

namespace App\Filament\Resources\IncidentResource\Pages;

use App\Filament\Resources\IncidentResource;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditIncident extends EditRecord
{
    protected static string $resource = IncidentResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getSavedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->icon('heroicon-o-check-circle')
            ->title('Incidencia actualizada')
            ->body('La incidencia ha sido actualizada correctamente.');
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->successNotification(
                    fn () => Notification::make()
                        ->success()
                        ->icon('heroicon-o-trash')
                        ->title('Incidencia eliminada')
                        ->body('La incidencia ha sido eliminada correctamente.')
                ),
            Actions\RestoreAction::make()
                ->successNotification(
                    fn () => Notification::make()
                        ->success()
                        ->icon('heroicon-o-arrow-path')
                        ->title('Incidencia restaurada')
                        ->body('La incidencia ha sido restaurada correctamente.')
                ),
            Actions\ForceDeleteAction::make()
                ->successNotification(
                    fn () => Notification::make()
                        ->success()
                        ->icon('heroicon-o-trash')
                        ->title('Incidencia eliminada permanentemente')
                        ->body('La incidencia ha sido eliminada permanentemente.')
                ),
        ];
    }
}
