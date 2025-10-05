<?php

declare(strict_types=1);

namespace App\Filament\Resources\DocumentResource\Pages;

use App\Filament\Resources\DocumentResource;
use App\Models\Document;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Storage;

class EditDocument extends EditRecord
{
    protected static string $resource = DocumentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('download')
                ->label('Download')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('success')
                ->action(function (Document $record) {
                    if (! Storage::disk('private')->exists($record->file_path)) {
                        Notification::make()
                            ->title('File not found')
                            ->body('The requested file could not be found in storage.')
                            ->danger()
                            ->icon('heroicon-o-exclamation-triangle')
                            ->send();

                        return;
                    }

                    Notification::make()
                        ->title('Download started')
                        ->body("Downloading {$record->original_filename}")
                        ->success()
                        ->icon('heroicon-o-arrow-down-tray')
                        ->send();

                    return Storage::disk('private')->download($record->file_path, $record->original_filename);
                }),

            Actions\DeleteAction::make()
                ->successNotification(
                    Notification::make()
                        ->success()
                        ->title('Document deleted')
                        ->body('The document has been moved to trash.')
                        ->icon('heroicon-o-trash')
                ),

            Actions\ForceDeleteAction::make()
                ->successNotification(
                    Notification::make()
                        ->success()
                        ->title('Document permanently deleted')
                        ->body('The document has been permanently deleted.')
                        ->icon('heroicon-o-trash')
                ),

            Actions\RestoreAction::make()
                ->successNotification(
                    Notification::make()
                        ->success()
                        ->title('Document restored')
                        ->body('The document has been restored.')
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
            ->title('Document updated')
            ->body('The document has been successfully updated.')
            ->icon('heroicon-o-check-circle');
    }
}
