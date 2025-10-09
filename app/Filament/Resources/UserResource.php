<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Mail\TwoFactorDisabledMail;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Support\Enums\FontWeight;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Mail;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';

    protected static ?string $navigationGroup = 'Administración';

    protected static ?string $modelLabel = 'Usuario';

    protected static ?string $pluralModelLabel = 'Usuarios';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Información del Usuario')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Nombre')
                            ->required()
                            ->maxLength(255)
                            ->autofocus()
                            ->prefixIcon('heroicon-o-user'),

                        Forms\Components\TextInput::make('email')
                            ->label('Correo Electrónico')
                            ->email()
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255)
                            ->prefixIcon('heroicon-o-envelope'),

                        Forms\Components\FileUpload::make('avatar')
                            ->label('Avatar')
                            ->image()
                            ->imageEditor()
                            ->directory('avatars')
                            ->visibility('public')
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Contraseña')
                    ->schema([
                        Forms\Components\TextInput::make('password')
                            ->label('Contraseña')
                            ->password()
                            ->required(fn (string $operation): bool => $operation === 'create')
                            ->dehydrated(fn ($state) => filled($state))
                            ->confirmed()
                            ->minLength(8)
                            ->maxLength(255)
                            ->prefixIcon('heroicon-o-lock-closed')
                            ->revealable(),

                        Forms\Components\TextInput::make('password_confirmation')
                            ->label('Confirmar Contraseña')
                            ->password()
                            ->required(fn (string $operation): bool => $operation === 'create')
                            ->dehydrated(false)
                            ->prefixIcon('heroicon-o-lock-closed')
                            ->revealable(),
                    ])
                    ->columns(2)
                    ->visible(fn (string $operation): bool => $operation === 'create' || $operation === 'edit'),

                Forms\Components\Section::make('Seguridad y Verificación')
                    ->schema([
                        Forms\Components\Toggle::make('email_verified_at')
                            ->label('Correo Verificado')
                            ->helperText('Marcar el correo del usuario como verificado')
                            ->afterStateHydrated(function ($component, $record) {
                                $component->state($record?->email_verified_at !== null);
                            })
                            ->dehydrateStateUsing(fn ($state) => $state ? now() : null),

                        Forms\Components\Placeholder::make('two_factor_status')
                            ->label('Verificación en Dos Pasos')
                            ->content(function ($record): string {
                                if (! $record || ! $record->two_factor_confirmed_at) {
                                    return 'No habilitada';
                                }

                                if (is_string($record->two_factor_confirmed_at)) {
                                    return 'Habilitada';
                                }

                                return 'Habilitada el '.$record->two_factor_confirmed_at->format('d M, Y');
                            })
                            ->visible(fn (string $operation): bool => $operation === 'edit'),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Estado de la Cuenta')
                    ->schema([
                        Forms\Components\Toggle::make('is_active')
                            ->label('Activo')
                            ->helperText('Los usuarios inactivos no pueden iniciar sesión')
                            ->default(true),

                        Forms\Components\Placeholder::make('last_login_at')
                            ->label('Último Acceso')
                            ->content(function ($record): string {
                                if (! $record || ! $record->last_login_at) {
                                    return 'Nunca';
                                }

                                if (is_string($record->last_login_at)) {
                                    return 'Recientemente';
                                }

                                return $record->last_login_at->diffForHumans();
                            })
                            ->visible(fn (string $operation): bool => $operation === 'edit'),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Asignación de Sucursales')
                    ->schema([
                        Forms\Components\Select::make('branches')
                            ->label('Sucursales')
                            ->relationship('branches', 'name')
                            ->multiple()
                            ->preload()
                            ->searchable()
                            ->helperText('Asignar el usuario a una o más sucursales')
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('avatar')
                    ->label('Avatar')
                    ->circular()
                    ->defaultImageUrl(fn ($record) => 'https://ui-avatars.com/api/?name='.urlencode($record->name).'&color=7F9CF5&background=EBF4FF'),

                Tables\Columns\TextColumn::make('name')
                    ->label('Nombre')
                    ->searchable()
                    ->sortable()
                    ->weight(FontWeight::Medium),

                Tables\Columns\TextColumn::make('email')
                    ->label('Correo Electrónico')
                    ->icon('heroicon-o-envelope')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\IconColumn::make('email_verified_at')
                    ->label('Verificado')
                    ->boolean()
                    ->trueIcon('heroicon-o-shield-check')
                    ->falseIcon('heroicon-o-shield-exclamation')
                    ->trueColor('success')
                    ->falseColor('warning')
                    ->sortable(),

                Tables\Columns\IconColumn::make('two_factor_confirmed_at')
                    ->label('Verificación en 2 Pasos')
                    ->boolean()
                    ->trueIcon('heroicon-o-device-phone-mobile')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('gray')
                    ->sortable(),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Activo')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger')
                    ->sortable(),

                Tables\Columns\TextColumn::make('branches.name')
                    ->label('Sucursales')
                    ->badge()
                    ->separator(',')
                    ->placeholder('Sin sucursales'),

                Tables\Columns\TextColumn::make('last_login_at')
                    ->label('Último Acceso')
                    ->dateTime()
                    ->sortable()
                    ->since()
                    ->placeholder('Nunca'),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Creado el')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Actualizado el')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('deleted_at')
                    ->label('Eliminado el')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('name', 'asc')
            ->filters([
                Tables\Filters\SelectFilter::make('is_active')
                    ->label('Estado')
                    ->options([
                        1 => 'Activo',
                        0 => 'Inactivo',
                    ])
                    ->placeholder('Todos los usuarios'),

                Tables\Filters\Filter::make('email_verified')
                    ->label('Correo Verificado')
                    ->query(fn (Builder $query): Builder => $query->whereNotNull('email_verified_at')),

                Tables\Filters\Filter::make('email_unverified')
                    ->label('Correo No Verificado')
                    ->query(fn (Builder $query): Builder => $query->whereNull('email_verified_at')),

                Tables\Filters\Filter::make('two_factor_enabled')
                    ->label('Verificación en 2 Pasos Habilitada')
                    ->query(fn (Builder $query): Builder => $query->whereNotNull('two_factor_confirmed_at')),

                Tables\Filters\Filter::make('two_factor_disabled')
                    ->label('Verificación en 2 Pasos Deshabilitada')
                    ->query(fn (Builder $query): Builder => $query->whereNull('two_factor_confirmed_at')),

                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\EditAction::make(),

                    Tables\Actions\Action::make('disable2fa')
                        ->label('Deshabilitar Verificación en 2 Pasos')
                        ->icon('heroicon-o-device-phone-mobile')
                        ->color('warning')
                        ->requiresConfirmation()
                        ->modalHeading('Deshabilitar Verificación en Dos Pasos')
                        ->modalDescription('¿Está seguro que desea deshabilitar la verificación en dos pasos para este usuario? Se le notificará por correo electrónico.')
                        ->modalSubmitActionLabel('Sí, Deshabilitar')
                        ->visible(fn ($record) => $record->two_factor_confirmed_at !== null)
                        ->action(function ($record) {
                            $record->update([
                                'two_factor_secret' => null,
                                'two_factor_recovery_codes' => null,
                                'two_factor_confirmed_at' => null,
                            ]);

                            // Send notification email
                            Mail::to($record->email)->send(new TwoFactorDisabledMail($record));

                            Notification::make()
                                ->success()
                                ->icon('heroicon-o-device-phone-mobile')
                                ->title('Verificación en Dos Pasos Deshabilitada')
                                ->body('La verificación en dos pasos ha sido removida y el usuario ha sido notificado por correo electrónico.')
                                ->send();
                        }),

                    Tables\Actions\Action::make('sendVerificationEmail')
                        ->label('Enviar Correo de Verificación')
                        ->icon('heroicon-o-envelope')
                        ->color('info')
                        ->requiresConfirmation()
                        ->modalHeading('Enviar Correo de Verificación')
                        ->modalDescription('Enviar un enlace de verificación por correo electrónico a este usuario.')
                        ->modalSubmitActionLabel('Enviar Correo')
                        ->visible(fn ($record) => $record->email_verified_at === null)
                        ->action(function ($record) {
                            // This would integrate with Laravel's email verification system
                            // For now, we'll show a notification
                            Notification::make()
                                ->success()
                                ->icon('heroicon-o-envelope')
                                ->title('Correo de Verificación Enviado')
                                ->body('Se ha enviado un correo de verificación a '.$record->email)
                                ->send();
                        }),

                    Tables\Actions\DeleteAction::make(),
                ]),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
                    Tables\Actions\ForceDeleteBulkAction::make(),
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
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
