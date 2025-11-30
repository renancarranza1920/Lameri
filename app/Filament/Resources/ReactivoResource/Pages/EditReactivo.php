<?php

namespace App\Filament\Resources\ReactivoResource\Pages;

use App\Filament\Resources\ReactivoResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditReactivo extends EditRecord
{
    protected static string $resource = ReactivoResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
   protected function afterSave(): void
    {
        // Misma lógica: Solo alertar si hubo víctimas
        if ($this->record->en_uso) {
            $afectados = $this->record->resolverConflictosDeUso();

            if ($afectados > 0) {
                \Filament\Notifications\Notification::make()
                    ->title('Conflictos resueltos')
                    ->body("Se han desactivado {$afectados} reactivo(s) por conflicto de pruebas.")
                    ->warning()
                    ->send();
            }
        }
    }
}
