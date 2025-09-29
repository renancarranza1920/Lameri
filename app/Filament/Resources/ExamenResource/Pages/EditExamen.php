<?php
namespace App\Filament\Resources\ExamenResource\Pages;
use App\Filament\Resources\ExamenResource;
use Filament\Resources\Pages\EditRecord;

class EditExamen extends EditRecord
{
    protected static string $resource = ExamenResource::class;

    // Rellenamos el campo TagsInput con las pruebas existentes
    protected function mutateFormDataBeforeFill(array $data): array
    {
        $data['pruebas_nombres'] = $this->record->pruebas->pluck('nombre')->all();
        return $data;
    }

    protected function afterSave(): void
    {
        $pruebasNombres = $this->data['pruebas_nombres'] ?? [];

        // Sincronizamos las pruebas:
        // 1. Borramos las pruebas que ya no están en la lista
        $this->record->pruebas()->whereNotIn('nombre', $pruebasNombres)->delete();

        // 2. Creamos las pruebas nuevas que no existían
        foreach ($pruebasNombres as $nombrePrueba) {
            $this->record->pruebas()->updateOrCreate(
                ['nombre' => $nombrePrueba], // Condición para buscar
                ['nombre' => $nombrePrueba]  // Datos para crear/actualizar
            );
        }
    }
}