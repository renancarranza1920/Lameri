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
        // Si el usuario marcó el switch "En Uso" en el formulario...
        if ($this->record->en_uso) {
            // ...limpiamos los conflictos automáticamente.
            $this->record->resolverConflictosDeUso();
            
            // Opcional: Avisar al usuario
            \Filament\Notifications\Notification::make()
                ->title('Conflictos resueltos')
                ->body('Se han desactivado otros reactivos automáticamente.')
                ->success()
                ->send();
        }
    }
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
