<?php

namespace App\Providers\Filament;

use App\Filament\Pages\DetalleOrdenKanban;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;

use Filament\Widgets;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\Facades\Route;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Filament\Http\RouteRegistrar; // ¡¡¡Esta importación es CRÍTICA!!!

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        // Configura cada aspecto del panel de forma explícita, sin encadenar una línea tras otra.
        // Esto elimina las posibilidades de errores sutiles de encadenamiento.
        $panel->default();
        $panel->id('admin');
        $panel->path('admin');
        $panel->login();
        $panel->favicon('storage/iconlab.png');
        $panel->colors([
            'primary' => '#1E73BE',
            'secondary' => '#F6A623',
            'success' => '#8BC34A',
            'dark' => '#333333',
            'accent' => '#FFFFFF'
        ]);
        $panel->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources');
        $panel->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages');
        $panel->pages([
            Pages\Dashboard::class,
            'detalle-orden-kanban' => DetalleOrdenKanban::class,
        ]);
        $panel->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets');
        $panel->widgets([
            Widgets\AccountWidget::class,
        ]);
        $panel->middleware([
            EncryptCookies::class,
            AddQueuedCookiesToResponse::class,
            StartSession::class,
            AuthenticateSession::class,
            ShareErrorsFromSession::class,
            VerifyCsrfToken::class,
            SubstituteBindings::class,
            DisableBladeIconComponents::class,
            DispatchServingFilamentEvent::class,
        ]);



        $panel->authMiddleware([
            Authenticate::class,
        ]);
        $panel->brandLogo(fn () => view('components.logo'));

        // Finalmente, devuelve el objeto $panel configurado
        return $panel;
    }
}
