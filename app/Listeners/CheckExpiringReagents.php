<?php

namespace App\Listeners;

use App\Models\Reactivo;
use Carbon\Carbon;
use Filament\Notifications\Notification;
use Illuminate\Auth\Events\Login;

class CheckExpiringReagents
{
    public function __construct()
    {
        //
    }

    public function handle(Login $event): void
    {   
        // --- CORRECCIÓN AQUÍ ---
        // Al usar update() masivo, los eventos del modelo no se disparan.
        // Debemos forzar 'en_uso' => false manualmente aquí.
        Reactivo::where('estado', 'disponible')
            ->where('fecha_caducidad', '<', Carbon::now())
            ->update([
                'estado' => 'caducado',
                'en_uso' => false // <--- AGREGADO: Apagado forzoso
            ]);
        // -----------------------

        // Notificaciones (Esto queda igual)
        $thresholdDate = Carbon::now()->addDays(15);
        $expiringReagents = Reactivo::query()
            ->where('estado', 'disponible') // Sugerencia: Solo avisar de los disponibles
            ->whereNotNull('fecha_caducidad')
            ->where('fecha_caducidad', '<=', $thresholdDate)
            ->where('fecha_caducidad', '>=', Carbon::now())
            ->get();

        if ($expiringReagents->isNotEmpty()) {
            $reagentNames = $expiringReagents->pluck('nombre')->implode(', ');
            
            Notification::make()
                ->title('Reactivos Próximos a Caducar')
                ->warning()
                ->body("Los siguientes reactivos caducarán pronto: {$reagentNames}")
                ->persistent()
                ->send();
        }
    }
}