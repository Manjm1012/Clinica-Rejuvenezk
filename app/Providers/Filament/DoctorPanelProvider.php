<?php

namespace App\Providers\Filament;

use App\Filament\Doctor\Pages\Auth\Login as DoctorLogin;
use App\Http\Middleware\SetAdminClinic;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

/**
 * Panel médico (/medico): pacientes, consultas, fórmulas y solicitudes de renovación.
 * Solo entran usuarios con un perfil de médico activo (User::canAccessPanel).
 */
class DoctorPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('doctor')
            ->path('medico')
            ->login(DoctorLogin::class)
            ->colors([
                'primary' => Color::hex('#7E9C78'),
                'gray' => Color::Stone,
                'warning' => Color::hex('#C9A96E'),
            ])
            ->theme(asset('css/filament/admin/theme.css'))
            ->brandName('Dr. Kevin Rejuvenezk · Panel médico')
            ->favicon(asset('favicon.ico'))
            ->discoverResources(in: app_path('Filament/Doctor/Resources'), for: 'App\Filament\Doctor\Resources')
            ->discoverPages(in: app_path('Filament/Doctor/Pages'), for: 'App\Filament\Doctor\Pages')
            ->pages([
                Pages\Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Doctor/Widgets'), for: 'App\Filament\Doctor\Widgets')
            ->sidebarCollapsibleOnDesktop()
            ->navigationGroups([
                'Atención',
                'Agenda',
            ])
            ->databaseNotifications(false)
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
            ->authMiddleware([
                SetAdminClinic::class,
            ], isPersistent: true);
    }
}
