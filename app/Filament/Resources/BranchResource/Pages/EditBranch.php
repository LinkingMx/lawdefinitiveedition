<?php

declare(strict_types=1);

namespace App\Filament\Resources\BranchResource\Pages;

use App\Filament\Resources\BranchResource;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditBranch extends EditRecord
{
    protected static string $resource = BranchResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->successNotification(
                    Notification::make()
                        ->success()
                        ->icon('heroicon-o-trash')
                        ->title('Branch Deleted')
                        ->body('The branch has been deleted successfully.')
                ),
            Actions\ForceDeleteAction::make()
                ->successNotification(
                    Notification::make()
                        ->success()
                        ->icon('heroicon-o-trash')
                        ->title('Branch Permanently Deleted')
                        ->body('The branch has been permanently deleted.')
                ),
            Actions\RestoreAction::make()
                ->successNotification(
                    Notification::make()
                        ->success()
                        ->icon('heroicon-o-arrow-path')
                        ->title('Branch Restored')
                        ->body('The branch has been restored successfully.')
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
            ->icon('heroicon-o-building-office-2')
            ->title('Branch Updated')
            ->body('The branch has been updated successfully.');
    }
}
