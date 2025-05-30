<?php

namespace App\Filament\Resources\OrdenResource\Pages;

use App\Filament\Resources\OrdenResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Carbon;

use App\Models\DetalleOrdenPerfil;
use App\Models\DetalleOrdenExamen;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Log;
use Exception;

class CreateOrden extends CreateRecord
{
    protected static string $resource = OrdenResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        logger($data); // Para ver en los logs

         $perfiles = $data['perfiles_seleccionados'] ?? [];
    $examenes = $data['examenes_seleccionados'] ?? [];

    $hayPerfilValido = collect($perfiles)->filter(fn ($p) => !empty($p['perfil_seleccionado']))->isNotEmpty();
    $hayExamenValido = collect($examenes)->filter(fn ($e) => !empty($e['examen_seleccionado']))->isNotEmpty();

    if (!($hayPerfilValido || $hayExamenValido)) {
        Notification::make()
            ->title('Debe seleccionar al menos un Perfil o un Examen.')
            ->danger()
            ->persistent()
            ->send();

        $this->halt();
    }

 
        // Guardamos los datos originales en propiedades de clase
        $this->perfiles = $data['perfiles_seleccionados'] ?? [];
        $this->examenes = $data['examenes_seleccionados'] ?? [];

        

        $productos = [];

        foreach ($this->perfiles as $perfil) {
            if (!empty($perfil['id'])) {
                $productos[] = [
                    'tipo' => 'perfil',
                    'id' => $perfil['id'],
                    'precio' => $perfil['preciot'] ?? 0,
                ];
            }
        }

        foreach ($this->examenes as $examen) {
            if (!empty($examen['id'])) {
                $productos[] = [
                    'tipo' => 'examen',
                    'id' => $examen['id'],
                    'precio' => $examen['preciot'] ?? 0,
                ];
            }
        }

    foreach ($data['perfiles_seleccionados'] ?? [] as $perfil) {
        if (!empty($perfil['id'])) {
            $productos[] = [
                'tipo' => 'perfil',
                'id' => $perfil['id'],
                'precio' => $perfil['preciot'] ?? 0,
            ];
        }
    }

    foreach ($data['examenes_seleccionados'] ?? [] as $examen) {
        if (!empty($examen['id'])) {
            $productos[] = [
                'tipo' => 'examen',
                'id' => $examen['id'],
                'precio' => $examen['preciot'] ?? 0,
            ];
        }
    }

        $data['productos'] = $productos;
        $data['total'] = collect($productos)->sum('precio');
        $data['fecha'] = Carbon::now(); // fecha actual
        $data['estado'] = 'pendiente';

        return $data;
    }

    protected function afterCreate(): void
    {

        logger('Record completo:', $this->record->toArray());

        logger('Orden ID: ' . $this->record->id);

        //foreach para un logger de losid de los perfiles y examenes
        foreach ($this->perfiles as $perfil) {
            logger('Perfil ID: ' . $perfil['id']);
        }
        foreach ($this->examenes as $examen) {
            logger('Examen ID: ' . $examen['id']);
        }

        // Crear los detalles usando los datos guardados previamente
        foreach ($this->perfiles as $perfil) {
            if (!empty($perfil['id'])) {
                DetalleOrdenPerfil::create([
                    'orden_id' => $this->record->id,
                    'perfil_id' => $perfil['id'],
                ]);
            }
        }

        foreach ($this->examenes as $examen) {
            if (!empty($examen['id'])) {
                DetalleOrdenExamen::create([
                    'orden_id' => $this->record->id,
                    'examen_id' => $examen['id'],
                ]);
            }
        }
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
