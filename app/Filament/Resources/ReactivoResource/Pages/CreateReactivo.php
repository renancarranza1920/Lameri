<?php

namespace App\Filament\Resources\ReactivoResource\Pages;

use App\Filament\Resources\ReactivoResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateReactivo extends CreateRecord
{
    protected static string $resource = ReactivoResource::class;

    protected function afterCreate(): void
    {
        // Si el usuario marcó "En Uso"...
        if ($this->record->en_uso) {
            
            // Ejecutamos la limpieza y guardamos CUÁNTOS se desactivaron
            $afectados = $this->record->resolverConflictosDeUso();
            
            // CORRECCIÓN CLAVE: Solo mostramos la alerta si hubo afectados reales (> 0)
            if ($afectados > 0) {
                \Filament\Notifications\Notification::make()
                    ->title('Conflictos resueltos')
                    ->body("Se han desactivado {$afectados} reactivo(s) antiguo(s) automáticamente.")
                    ->warning() // Color naranja para diferenciarlo del verde "Creado"
                    ->send();
            }
        }
    }
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
