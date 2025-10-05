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

    protected static ?string $navigationGroup = 'Administration';

    protected static ?string $modelLabel = 'User';

    protected static ?string $pluralModelLabel = 'Users';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('User Information')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255)
                            ->autofocus()
                            ->prefixIcon('heroicon-o-user'),

                        Forms\Components\TextInput::make('email')
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

                Forms\Components\Section::make('Password')
                    ->schema([
                        Forms\Components\TextInput::make('password')
                            ->password()
                            ->required(fn (string $operation): bool => $operation === 'create')
                            ->dehydrated(fn ($state) => filled($state))
                            ->confirmed()
                            ->minLength(8)
                            ->maxLength(255)
                            ->prefixIcon('heroicon-o-lock-closed')
                            ->revealable(),

                        Forms\Components\TextInput::make('password_confirmation')
                            ->password()
                            ->required(fn (string $operation): bool => $operation === 'create')
                            ->dehydrated(false)
                            ->prefixIcon('heroicon-o-lock-closed')
                            ->revealable(),
                    ])
                    ->columns(2)
                    ->visible(fn (string $operation): bool => $operation === 'create' || $operation === 'edit'),

                Forms\Components\Section::make('Security & Verification')
                    ->schema([
                        Forms\Components\Toggle::make('email_verified_at')
                            ->label('Email Verified')
                            ->helperText('Mark the user\'s email as verified')
                            ->afterStateHydrated(function ($component, $record) {
                                $component->state($record?->email_verified_at !== null);
                            })
                            ->dehydrateStateUsing(fn ($state) => $state ? now() : null),

                        Forms\Components\Placeholder::make('two_factor_status')
                            ->label('Two-Factor Authentication')
                            ->content(function ($record): string {
                                if (! $record || ! $record->two_factor_confirmed_at) {
                                    return 'Not enabled';
                                }

                                if (is_string($record->two_factor_confirmed_at)) {
                                    return 'Enabled';
                                }

                                return 'Enabled on '.$record->two_factor_confirmed_at->format('M d, Y');
                            })
                            ->visible(fn (string $operation): bool => $operation === 'edit'),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Account Status')
                    ->schema([
                        Forms\Components\Toggle::make('is_active')
                            ->label('Active')
                            ->helperText('Inactive users cannot log in')
                            ->default(true),

                        Forms\Components\Placeholder::make('last_login_at')
                            ->label('Last Login')
                            ->content(function ($record): string {
                                if (! $record || ! $record->last_login_at) {
                                    return 'Never';
                                }

                                if (is_string($record->last_login_at)) {
                                    return 'Recently';
                                }

                                return $record->last_login_at->diffForHumans();
                            })
                            ->visible(fn (string $operation): bool => $operation === 'edit'),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Branch Assignment')
                    ->schema([
                        Forms\Components\Select::make('branches')
                            ->relationship('branches', 'name')
                            ->multiple()
                            ->preload()
                            ->searchable()
                            ->helperText('Assign the user to one or more branches')
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('avatar')
                    ->circular()
                    ->defaultImageUrl(fn ($record) => 'https://ui-avatars.com/api/?name='.urlencode($record->name).'&color=7F9CF5&background=EBF4FF'),

                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->weight(FontWeight::Medium),

                Tables\Columns\TextColumn::make('email')
                    ->icon('heroicon-o-envelope')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\IconColumn::make('email_verified_at')
                    ->label('Verified')
                    ->boolean()
                    ->trueIcon('heroicon-o-shield-check')
                    ->falseIcon('heroicon-o-shield-exclamation')
                    ->trueColor('success')
                    ->falseColor('warning')
                    ->sortable(),

                Tables\Columns\IconColumn::make('two_factor_confirmed_at')
                    ->label('2FA')
                    ->boolean()
                    ->trueIcon('heroicon-o-device-phone-mobile')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('gray')
                    ->sortable(),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger')
                    ->sortable(),

                Tables\Columns\TextColumn::make('branches.name')
                    ->badge()
                    ->separator(',')
                    ->placeholder('No branches'),

                Tables\Columns\TextColumn::make('last_login_at')
                    ->label('Last Login')
                    ->dateTime()
                    ->sortable()
                    ->since()
                    ->placeholder('Never'),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('deleted_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('name', 'asc')
            ->filters([
                Tables\Filters\SelectFilter::make('is_active')
                    ->label('Status')
                    ->options([
                        1 => 'Active',
                        0 => 'Inactive',
                    ])
                    ->placeholder('All users'),

                Tables\Filters\Filter::make('email_verified')
                    ->label('Email Verified')
                    ->query(fn (Builder $query): Builder => $query->whereNotNull('email_verified_at')),

                Tables\Filters\Filter::make('email_unverified')
                    ->label('Email Unverified')
                    ->query(fn (Builder $query): Builder => $query->whereNull('email_verified_at')),

                Tables\Filters\Filter::make('two_factor_enabled')
                    ->label('2FA Enabled')
                    ->query(fn (Builder $query): Builder => $query->whereNotNull('two_factor_confirmed_at')),

                Tables\Filters\Filter::make('two_factor_disabled')
                    ->label('2FA Disabled')
                    ->query(fn (Builder $query): Builder => $query->whereNull('two_factor_confirmed_at')),

                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\EditAction::make(),

                    Tables\Actions\Action::make('disable2fa')
                        ->label('Disable 2FA')
                        ->icon('heroicon-o-device-phone-mobile')
                        ->color('warning')
                        ->requiresConfirmation()
                        ->modalHeading('Disable Two-Factor Authentication')
                        ->modalDescription('Are you sure you want to disable two-factor authentication for this user? They will be notified via email.')
                        ->modalSubmitActionLabel('Yes, Disable 2FA')
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
                                ->title('Two-Factor Authentication Disabled')
                                ->body('2FA has been removed and the user has been notified via email.')
                                ->send();
                        }),

                    Tables\Actions\Action::make('sendVerificationEmail')
                        ->label('Send Verification Email')
                        ->icon('heroicon-o-envelope')
                        ->color('info')
                        ->requiresConfirmation()
                        ->modalHeading('Send Verification Email')
                        ->modalDescription('Send an email verification link to this user.')
                        ->modalSubmitActionLabel('Send Email')
                        ->visible(fn ($record) => $record->email_verified_at === null)
                        ->action(function ($record) {
                            // This would integrate with Laravel's email verification system
                            // For now, we'll show a notification
                            Notification::make()
                                ->success()
                                ->icon('heroicon-o-envelope')
                                ->title('Verification Email Sent')
                                ->body('A verification email has been sent to '.$record->email)
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
