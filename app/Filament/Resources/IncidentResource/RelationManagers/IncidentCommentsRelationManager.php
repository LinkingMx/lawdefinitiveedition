<?php

declare(strict_types=1);

namespace App\Filament\Resources\IncidentResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class IncidentCommentsRelationManager extends RelationManager
{
    protected static string $relationship = 'comments';

    protected static ?string $title = 'Comentarios';

    protected static ?string $modelLabel = 'Comentario';

    protected static ?string $pluralModelLabel = 'Comentarios';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Textarea::make('comment')
                    ->label('Comentario')
                    ->required()
                    ->rows(4),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('comment')
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Usuario')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('comment')
                    ->label('Comentario')
                    ->limit(100)
                    ->searchable()
                    ->wrap(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Fecha')
                    ->dateTime()
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->mutateFormDataUsing(function (array $data): array {
                        $data['user_id'] = auth()->id();

                        return $data;
                    })
                    ->successNotification(
                        fn () => Notification::make()
                            ->success()
                            ->icon('heroicon-o-chat-bubble-left-right')
                            ->title('Comentario agregado')
                            ->body('El comentario ha sido agregado correctamente.')
                    ),
            ])
            ->actions([
                // Disable edit and delete to preserve history
            ])
            ->bulkActions([
                // Disable bulk actions to preserve history
            ]);
    }
}
