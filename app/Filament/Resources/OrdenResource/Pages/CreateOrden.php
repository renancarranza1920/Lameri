<?php

namespace App\Filament\Resources\OrdenResource\Pages;

use App\Filament\Resources\OrdenResource;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Filament\Support\Exceptions\Halt;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

class CreateOrden extends CreateRecord
{
    protected static string $resource = OrdenResource::class;

      protected function beforeCreate(): void
    {
        $state = $this->form->getState();

        Log::info('🟡 [beforeCreate] Estado completo del formulario:', $state);

        $perfiles = $state['perfiles_seleccionados'] ?? [];
        $examenes = $state['examenes_seleccionados'] ?? [];

        Log::info('🧪 [beforeCreate] Perfiles seleccionados:', $perfiles);
        Log::info('🧪 [beforeCreate] Exámenes seleccionados:', $examenes);

        if (empty($perfiles) && empty($examenes)) {
            Notification::make()
                ->title('Debe seleccionar al menos un perfil o un examen.')
                ->danger()
                ->persistent()
                ->send();

            Log::warning('🚫 [beforeCreate] Se bloqueó la creación: no hay perfiles ni exámenes.');

            throw new Halt(); // Detiene sin mostrar modal de error
        }
    }
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        Log::info('🔵 [mutateFormDataBeforeCreate] Datos base:', $data);

        $state = $this->form->getState();

        $perfiles = $state['perfiles_seleccionados'] ?? [];
        $examenes = $state['examenes_seleccionados'] ?? [];

        Log::info('🟢 [mutateFormDataBeforeCreate] Perfiles seleccionados:', $perfiles);
        Log::info('🟢 [mutateFormDataBeforeCreate] Exámenes seleccionados:', $examenes);

        $total = 0;

        foreach ($perfiles as $item) {
            $precio = floatval($item['precio_hidden'] ?? 0);
            $total += $precio;
            Log::info("➕ Perfil: {$item['perfil_id']} - Precio: $precio");
        }

        foreach ($examenes as $item) {
            $precio = floatval($item['precio_hidden'] ?? 0);
            $total += $precio;
            Log::info("➕ Examen: {$item['examen_id']} - Precio: $precio");
        }

        $data['total'] = $total;
        $data['fecha'] = Carbon::now();
        $data['estado'] = 'pendiente';

        Log::info("✅ [mutateFormDataBeforeCreate] Total calculado: $total");
        Log::info('📦 [mutateFormDataBeforeCreate] Data final con total:', $data);

        return $data;
    }

    protected function afterCreate(): void
{
    $state = $this->form->getState();
    $orden = $this->record;

    $perfiles = $state['perfiles_seleccionados'] ?? [];
    $examenes = $state['examenes_seleccionados'] ?? [];

   
    foreach ($perfiles as $perfil) {
        $orden->detalleOrdenPerfils()->create([
            'perfil_id' => $perfil['perfil_id'],
        ]);
        Log::info("✅ Perfil guardado: {$perfil['perfil_id']}");
    }

    foreach ($examenes as $examen) {
        $orden->detalleOrdenExamens()->create([
            'examen_id' => $examen['examen_id'],
        ]);
        Log::info("✅ Examen guardado: {$examen['examen_id']}");
    }
}

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
