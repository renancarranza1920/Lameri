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
    'primary'   => '#1E73BE', // Azul base
    'primary-light' => '#3A8FD1', // Azul más claro
    'primary-dark'  => '#155A92', // Azul más oscuro

    'secondary' => '#F6A623', // Naranja base
    'secondary-light' => '#F9BC49', // Naranja más claro
    'secondary-dark'  => '#C6861A', // Naranja más oscuro

    'success'   => '#8BC34A', // Verde base
    'success-light' => '#A6D36A', // Verde más claro
    'success-dark'  => '#6B9B35', // Verde más oscuro

    'dark'      => '#333333',
    'accent'    => '#FFFFFF',
    'danger'    => '#FF3B30',
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
