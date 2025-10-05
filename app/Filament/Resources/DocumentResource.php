<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\DocumentResource\Pages;
use App\Models\Document;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class DocumentResource extends Resource
{
    protected static ?string $model = Document::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-arrow-up';

    protected static ?string $navigationGroup = 'Documents';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Document Classification')
                    ->icon('heroicon-o-folder')
                    ->description('Select the document type and associated branch')
                    ->schema([
                        Forms\Components\Select::make('document_type_id')
                            ->label('Document Type')
                            ->relationship('documentType', 'name')
                            ->required()
                            ->searchable()
                            ->preload()
                            ->prefixIcon('heroicon-o-document-text')
                            ->columnSpanFull(),

                        Forms\Components\Select::make('branch_id')
                            ->label('Branch')
                            ->relationship('branch', 'name')
                            ->required()
                            ->searchable()
                            ->preload()
                            ->prefixIcon('heroicon-o-building-office-2')
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Document Details')
                    ->icon('heroicon-o-information-circle')
                    ->description('Provide additional information about the document')
                    ->schema([
                        Forms\Components\Textarea::make('description')
                            ->label('Description')
                            ->maxLength(1000)
                            ->rows(4)
                            ->columnSpanFull(),

                        Forms\Components\DatePicker::make('expires_at')
                            ->label('Expires At')
                            ->prefixIcon('heroicon-o-calendar')
                            ->minDate(now())
                            ->columnSpanFull(),
                    ]),

                Forms\Components\Section::make('File Upload')
                    ->icon('heroicon-o-arrow-up-tray')
                    ->description('Upload the document file')
                    ->schema([
                        Forms\Components\FileUpload::make('file_path')
                            ->label('File')
                            ->required(fn (string $operation): bool => $operation === 'create')
                            ->disk('public')
                            ->directory('documents')
                            ->maxSize(10240) // 10MB in KB
                            ->preserveFilenames()
                            ->downloadable()
                            ->openable()
                            ->afterStateUpdated(function ($state, callable $set) {
                                if ($state) {
                                    $set('original_filename', $state->getClientOriginalName());
                                    $set('file_size', $state->getSize());
                                    $set('mime_type', $state->getMimeType());
                                }
                            })
                            ->dehydrated(fn ($state) => filled($state))
                            ->columnSpanFull(),

                        Forms\Components\Hidden::make('original_filename'),
                        Forms\Components\Hidden::make('file_size'),
                        Forms\Components\Hidden::make('mime_type'),
                        Forms\Components\Hidden::make('uploaded_by')
                            ->default(fn () => Auth::id()),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('documentType.name')
                    ->label('Document Type')
                    ->sortable()
                    ->searchable()
                    ->badge(),

                Tables\Columns\TextColumn::make('branch.name')
                    ->label('Branch')
                    ->sortable()
                    ->searchable()
                    ->badge()
                    ->color('info'),

                Tables\Columns\TextColumn::make('description')
                    ->limit(50)
                    ->sortable()
                    ->searchable()
                    ->placeholder('No description'),

                Tables\Columns\TextColumn::make('original_filename')
                    ->label('File')
                    ->sortable()
                    ->searchable()
                    ->icon('heroicon-o-document')
                    ->iconColor('primary'),

                Tables\Columns\TextColumn::make('file_size')
                    ->label('Size')
                    ->formatStateUsing(fn (int $state): string => static::formatBytes($state))
                    ->sortable(),

                Tables\Columns\TextColumn::make('expires_at')
                    ->label('Expires')
                    ->date()
                    ->sortable()
                    ->badge()
                    ->color(fn ($state, Document $record) => $record->isExpired() ? 'danger' : 'success')
                    ->formatStateUsing(fn ($state) => $state ? $state->format('M d, Y') : 'Never'),

                Tables\Columns\TextColumn::make('uploadedBy.name')
                    ->label('Uploaded By')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Uploaded At')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: false),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Updated At')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('document_type_id')
                    ->label('Document Type')
                    ->relationship('documentType', 'name')
                    ->searchable()
                    ->preload(),

                Tables\Filters\SelectFilter::make('branch_id')
                    ->label('Branch')
                    ->relationship('branch', 'name')
                    ->searchable()
                    ->preload(),

                Tables\Filters\TernaryFilter::make('expired')
                    ->label('Expiration Status')
                    ->placeholder('All documents')
                    ->trueLabel('Expired only')
                    ->falseLabel('Active only')
                    ->queries(
                        true: fn (Builder $query) => $query->whereNotNull('expires_at')->whereDate('expires_at', '<', now()),
                        false: fn (Builder $query) => $query->where(function ($q) {
                            $q->whereNull('expires_at')
                                ->orWhereDate('expires_at', '>=', now());
                        }),
                    ),

                Tables\Filters\SelectFilter::make('uploaded_by')
                    ->label('Uploaded By')
                    ->relationship('uploadedBy', 'name')
                    ->searchable()
                    ->preload(),

                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                Tables\Actions\Action::make('download')
                    ->label('Download')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('success')
                    ->action(function (Document $record) {
                        if (! Storage::disk('public')->exists($record->file_path)) {
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

                        return Storage::disk('public')->download($record->file_path, $record->original_filename);
                    }),

                Tables\Actions\Action::make('preview')
                    ->label('Preview')
                    ->icon('heroicon-o-eye')
                    ->color('info')
                    ->visible(fn (Document $record) => in_array($record->mime_type, [
                        'application/pdf',
                        'image/jpeg',
                        'image/png',
                        'image/gif',
                    ]))
                    ->url(fn (Document $record) => Storage::disk('public')->url($record->file_path))
                    ->openUrlInNewTab(),

                Tables\Actions\EditAction::make(),

                Tables\Actions\DeleteAction::make()
                    ->successNotification(
                        Notification::make()
                            ->success()
                            ->title('Document deleted')
                            ->body('The document has been moved to trash.')
                            ->icon('heroicon-o-trash')
                    ),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->successNotification(
                            Notification::make()
                                ->success()
                                ->title('Documents deleted')
                                ->body('Selected documents have been moved to trash.')
                                ->icon('heroicon-o-trash')
                        ),
                    Tables\Actions\ForceDeleteBulkAction::make()
                        ->successNotification(
                            Notification::make()
                                ->success()
                                ->title('Documents permanently deleted')
                                ->body('Selected documents have been permanently deleted.')
                                ->icon('heroicon-o-trash')
                        ),
                    Tables\Actions\RestoreBulkAction::make()
                        ->successNotification(
                            Notification::make()
                                ->success()
                                ->title('Documents restored')
                                ->body('Selected documents have been restored.')
                                ->icon('heroicon-o-arrow-path')
                        ),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDocuments::route('/'),
            'create' => Pages\CreateDocument::route('/create'),
            'edit' => Pages\EditDocument::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }

    /**
     * Format bytes to human-readable size.
     */
    protected static function formatBytes(int $bytes, int $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, $precision).' '.$units[$i];
    }
}
