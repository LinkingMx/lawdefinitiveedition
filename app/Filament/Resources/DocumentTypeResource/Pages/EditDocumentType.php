<?php

declare(strict_types=1);

namespace App\Filament\Resources\DocumentTypeResource\Pages;

use App\Filament\Resources\DocumentTypeResource;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditDocumentType extends EditRecord
{
    protected static string $resource = DocumentTypeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->successNotification(
                    fn () => Notification::make()
                        ->success()
                        ->title('Document Type Deleted')
                        ->body('The document type has been deleted successfully.')
                        ->icon('heroicon-o-trash')
                ),
            Actions\ForceDeleteAction::make()
                ->successNotification(
                    fn () => Notification::make()
                        ->danger()
                        ->title('Document Type Permanently Deleted')
                        ->body('The document type has been permanently deleted.')
                        ->icon('heroicon-o-trash')
                ),
            Actions\RestoreAction::make()
                ->successNotification(
                    fn () => Notification::make()
                        ->success()
                        ->title('Document Type Restored')
                        ->body('The document type has been restored successfully.')
                        ->icon('heroicon-o-arrow-path')
                ),
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
            ->title('Document Type Updated')
            ->body('The document type has been updated successfully.')
            ->icon('heroicon-o-check-circle');
    }
}
