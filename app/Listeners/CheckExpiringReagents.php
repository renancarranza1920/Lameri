<?php

namespace App\Listeners;

use App\Models\Reactivo;
use Carbon\Carbon;
use Filament\Notifications\Notification;
use Illuminate\Auth\Events\Login; // Se usa el evento Login

class CheckExpiringReagents
{
    public function __construct()
    {
        //
    }

    public function handle(Login $event): void
    {
        // Ya no se necesita la comprobación de sesión
        $thresholdDate = Carbon::now()->addDays(15);
        $expiringReagents = Reactivo::query()
            ->whereNotNull('fecha_caducidad')
            ->where('fecha_caducidad', '<=', $thresholdDate)
            ->where('fecha_caducidad', '>=', Carbon::now())
            ->get();

        if ($expiringReagents->isNotEmpty()) {
            $reagentNames = $expiringReagents->pluck('nombre')->implode(', ');
            
            // Se usa ->send() para mostrar la notificación en pantalla
            Notification::make()
                ->title('Reactivos Próximos a Caducar')
                ->warning()
                ->body("Los siguientes reactivos caducarán pronto: {$reagentNames}")
                ->persistent()
                ->send();
        }
    }
}