<?php

namespace App\Filament\Resources\OrdenResource\Pages;

use App\Filament\Resources\OrdenResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

class CreateOrden extends CreateRecord
{
    protected static string $resource = OrdenResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        Log::info('📝 Mutando datos antes de crear la orden:', $data);
        $total = 0;

        $logPerfiles = [];
        foreach ($data['detalleOrdenPerfils'] ?? [] as $detalle) {
            $perfilId = $detalle['perfil_id'] ?? null;
            if ($perfilId) {
                $perfil = \App\Models\Perfil::find($perfilId);
                $precioPerfil = $perfil?->precio ?? 0;
                $total += $precioPerfil;

                $logPerfiles[] = [
                    'id' => $perfilId,
                    'nombre' => $perfil->nombre ?? 'Desconocido',
                    'precio' => $precioPerfil,
                ];
            }
        }

        $logExamenes = [];
        foreach ($data['detalleOrdenExamens'] ?? [] as $detalle) {
            $examenId = $detalle['examen_id'] ?? null;
            if ($examenId) {
                $examen = \App\Models\Examen::find($examenId);
                $precioExamen = $examen?->precio ?? 0;
                $total += $precioExamen;

                $logExamenes[] = [
                    'id' => $examenId,
                    'nombre' => $examen->nombre ?? 'Desconocido',
                    'precio' => $precioExamen,
                ];
            }
        }

        // Loguear todos los detalles antes de guardar
        Log::info('🧪 Perfiles seleccionados:', $logPerfiles);
        Log::info('🔬 Exámenes seleccionados:', $logExamenes);
        Log::info('💲 Total calculado:', ['total' => $total]);

        // Si deseas permitir el guardado, comenta o elimina la línea de abort()
        $data['total'] = $total;
        $data['fecha'] = Carbon::now();
        $data['estado'] = 'pendiente';

        return $data;
    }

    protected function afterCreate(): void
    {
        logger('✅ Orden creada con ID: ' . $this->record->id);
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
