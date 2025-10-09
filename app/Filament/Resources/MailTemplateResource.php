<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\MailTemplateResource\Pages;
use App\Models\MailTemplate;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\HtmlString;

class MailTemplateResource extends Resource
{
    protected static ?string $model = MailTemplate::class;

    protected static ?string $navigationIcon = 'heroicon-o-envelope';

    protected static ?string $navigationGroup = 'Configuración';

    protected static ?string $modelLabel = 'Plantilla de Correo';

    protected static ?string $pluralModelLabel = 'Plantillas de Correo';

    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Tabs::make('Configuración')
                    ->tabs([
                        Forms\Components\Tabs\Tab::make('Información General')
                            ->schema([
                                Forms\Components\TextInput::make('name')
                                    ->label('Nombre de la Plantilla')
                                    ->required()
                                    ->maxLength(255)
                                    ->helperText('Nombre descriptivo para identificar la plantilla'),

                                Forms\Components\Select::make('category')
                                    ->label('Categoría')
                                    ->options([
                                        'incidents' => 'Incidentes',
                                        'documents' => 'Documentos',
                                        'users' => 'Usuarios',
                                        'system' => 'Sistema',
                                    ])
                                    ->required(),

                                Forms\Components\Select::make('mailable')
                                    ->label('Tipo de Correo (Mailable)')
                                    ->options(self::getAvailableMailables())
                                    ->required()
                                    ->live()
                                    ->afterStateUpdated(fn ($state, callable $set) => $set('available_variables', self::getMailableVariables($state))
                                    )
                                    ->helperText('Selecciona el tipo de correo electrónico'),

                                Forms\Components\Placeholder::make('variables_info')
                                    ->label('Variables Disponibles')
                                    ->content(function (callable $get) {
                                        $mailable = $get('mailable');
                                        if (! $mailable) {
                                            return 'Selecciona un tipo de correo para ver las variables disponibles';
                                        }

                                        $variables = self::getMailableVariables($mailable);
                                        if (empty($variables)) {
                                            return 'No hay variables disponibles';
                                        }

                                        $list = collect($variables)
                                            ->map(fn ($desc, $var) => "• {{ {$var} }} - {$desc}")
                                            ->join("\n");

                                        return new HtmlString("<pre class='text-sm'>{$list}</pre>");
                                    }),
                            ]),

                        Forms\Components\Tabs\Tab::make('Contenido del Correo')
                            ->schema([
                                Forms\Components\TextInput::make('subject')
                                    ->label('Asunto')
                                    ->required()
                                    ->maxLength(255)
                                    ->helperText('Puedes usar variables Mustache: {{ variable }}'),

                                Forms\Components\RichEditor::make('html_template')
                                    ->label('Contenido HTML')
                                    ->required()
                                    ->columnSpanFull()
                                    ->toolbarButtons([
                                        'bold',
                                        'italic',
                                        'link',
                                        'bulletList',
                                        'orderedList',
                                        'h2',
                                        'h3',
                                    ])
                                    ->helperText('Contenido del correo en formato HTML. Usa {{ variable }} para insertar variables'),

                                Forms\Components\Textarea::make('text_template')
                                    ->label('Contenido Texto Plano (Opcional)')
                                    ->rows(10)
                                    ->columnSpanFull()
                                    ->helperText('Versión en texto plano del correo (fallback)'),
                            ]),
                    ])
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nombre')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('category')
                    ->label('Categoría')
                    ->badge()
                    ->colors([
                        'primary' => 'incidents',
                        'success' => 'documents',
                        'warning' => 'users',
                        'danger' => 'system',
                    ])
                    ->sortable(),

                Tables\Columns\TextColumn::make('subject')
                    ->label('Asunto')
                    ->limit(50)
                    ->searchable(),

                Tables\Columns\TextColumn::make('mailable')
                    ->label('Tipo')
                    ->formatStateUsing(fn (string $state) => class_basename($state))
                    ->sortable(),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Última Actualización')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('category')
                    ->label('Categoría')
                    ->options([
                        'incidents' => 'Incidentes',
                        'documents' => 'Documentos',
                        'users' => 'Usuarios',
                        'system' => 'Sistema',
                    ]),

                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
                Tables\Actions\Action::make('preview')
                    ->label('Vista Previa')
                    ->icon('heroicon-o-eye')
                    ->modalHeading('Vista Previa de Plantilla')
                    ->modalContent(fn (MailTemplate $record) => view('filament.mail-template-preview', ['record' => $record]))
                    ->modalSubmitAction(false),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\ForceDeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
                ]),
            ])
            ->defaultSort('updated_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListMailTemplates::route('/'),
            'create' => Pages\CreateMailTemplate::route('/create'),
            'edit' => Pages\EditMailTemplate::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }

    protected static function getAvailableMailables(): array
    {
        return [
            'App\\Mail\\IncidentCreatedMail' => 'Incidente Creado',
            'App\\Mail\\IncidentCommentMail' => 'Comentario en Incidente',
            'App\\Mail\\DocumentExpiringMail' => 'Documento Próximo a Vencer',
            'App\\Mail\\DocumentUploadedMail' => 'Documento Cargado',
        ];
    }

    protected static function getMailableVariables(string $mailable): array
    {
        $variables = [
            'App\\Mail\\IncidentCreatedMail' => [
                'incident.title' => 'Título del incidente',
                'incident.priority' => 'Prioridad',
                'incident.description' => 'Descripción',
                'reporter.name' => 'Nombre del reportador',
                'branch.name' => 'Sucursal',
            ],
            'App\\Mail\\IncidentCommentMail' => [
                'incident.title' => 'Título del incidente',
                'comment.comment' => 'Contenido del comentario',
                'user.name' => 'Nombre del comentarista',
                'comment.created_at' => 'Fecha del comentario',
            ],
            'App\\Mail\\DocumentExpiringMail' => [
                'document.original_filename' => 'Nombre del documento',
                'document.expires_at' => 'Fecha de expiración',
                'days_until_expiry' => 'Días hasta vencer',
                'branch.name' => 'Sucursal',
            ],
            'App\\Mail\\DocumentUploadedMail' => [
                'document.original_filename' => 'Nombre del documento',
                'uploader.name' => 'Usuario que subió',
                'branch.name' => 'Sucursal',
                'upload_date' => 'Fecha de carga',
            ],
        ];

        return $variables[$mailable] ?? [];
    }
}
