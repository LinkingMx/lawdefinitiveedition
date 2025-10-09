<?php

namespace App\Providers\Filament;

use App\Filament\Resources\DocumentResource;
use BezhanSalleh\FilamentShield\FilamentShieldPlugin;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Joaopaulolndev\FilamentGeneralSettings\FilamentGeneralSettingsPlugin;
use Tapp\FilamentMailLog\FilamentMailLogPlugin;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login()
            ->brandLogo(asset('logo_costeno_LP.svg'))
            ->brandLogoHeight('2.4rem')
            ->brandName('Costeño LP')
            ->colors([
                'primary' => [
                    '50' => '#f8f5f1',
                    '100' => '#ebe4d9',
                    '200' => '#d8cabb',
                    '300' => '#c4af9c',
                    '400' => '#b1947e',
                    '500' => '#a48166',
                    '600' => '#897053', // <-- Tu color base
                    '700' => '#725c46',
                    '800' => '#5c4a39',
                    '900' => '#483a2d',
                    '950' => '#2a221a',
                ],
                // Opcional: Para una mejor armonía, usamos una escala de grises cálida
                'gray' => Color::Stone,
                'danger' => Color::Red,
                'warning' => Color::Orange,
                'success' => Color::Green,
                'info' => Color::Blue,
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->pages([])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->widgets([])
            ->homeUrl(fn () => DocumentResource::getUrl('index'))
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ])
            ->plugins([
                FilamentShieldPlugin::make(),
                FilamentMailLogPlugin::make(),
                FilamentGeneralSettingsPlugin::make()
                    ->setSort(3)
                    ->setNavigationGroup('Administración')
                    ->setNavigationLabel('Configuración General')
                    ->setIcon('heroicon-o-cog-6-tooth')
                    ->canAccess(fn () => auth()->user()?->hasRole('super_admin') ?? false),
            ]);
    }
}
