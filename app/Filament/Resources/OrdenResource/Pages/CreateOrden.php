<?php

namespace App\Filament\Resources\OrdenResource\Pages;

use App\Filament\Pages\DetalleOrdenKanban;
use App\Filament\Resources\OrdenResource;
use Filament\Resources\Pages\CreateRecord\Concerns\HasWizard; 
use Filament\Forms\Components\Wizard\Step;
use Illuminate\Support\Facades\DB;
use Throwable;
use App\Models\Perfil;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Filament\Support\Exceptions\Halt;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

class CreateOrden extends CreateRecord
{
  use HasWizard;

   
    protected static string $resource = OrdenResource::class;
 protected function getSteps(): array
    {
        return [
            // 👇 Los nombres deben ser simples y consistentes 👇
            Step::make('Cliente') 
                ->schema(OrdenResource::getClienteStep()),

            Step::make('Orden')
                ->schema(OrdenResource::getOrdenStep()),

            Step::make('Resumen')
                ->schema(OrdenResource::getResumenStep()),
        ];
    }
protected function handleRecordCreation(array $data): \Illuminate\Database\Eloquent\Model
{
    return DB::transaction(function () use ($data) {
        $orden = static::getModel()::create($data);
        $this->record = $orden;

         session()->flash('from_create_orden', true);

        $state = $this->form->getState();
        $perfiles = $state['perfiles_seleccionados'] ?? [];
        $examenes = $state['examenes_seleccionados'] ?? [];

        foreach ($perfiles as $perfil) {
            $examenesPerfil = [];
            $precioPerfil = $perfil['precio_hidden'];
            $nombrePerfil = Perfil::find($perfil['perfil_id'])?->nombre ?? 'Perfil desconocido';

            Perfil::find($perfil['perfil_id'])?->examenes->each(function ($examen) use (&$examenesPerfil, $precioPerfil, $nombrePerfil) {
                $examenesPerfil[] = [
                    'examen_id' => $examen->id,
                    'nombre_examen' => $examen->nombre,
                    'precio_examen' => $examen->precio,
                    'perfil_id' => $examen->pivot->perfil_id,
                    'recipiente' => $examen->recipiente,
                    'nombre_perfil' => $nombrePerfil,
                    'precio_perfil' => $precioPerfil,
                ];
            });

            foreach ($examenesPerfil as $examenp) {
                $orden->detalleOrden()->create([
                    'examen_id' => $examenp['examen_id'],
                    'perfil_id' => $examenp['perfil_id'],
                    'nombre_perfil' => $examenp['nombre_perfil'] ?? null,
                    'precio_perfil' => $examenp['precio_perfil'] ?? null,
                    'nombre_examen' => $examenp['nombre_examen'],
                    'precio_examen' => $examenp['precio_examen'],
                    'status' => $examenp['recipiente'] ?? null,
                ]);
                Log::info("✅ Examen del perfil guardado: {$examenp['examen_id']} para perfil {$perfil['perfil_id']}");
            }
        }
       
        foreach ($examenes as $examen) {
            $orden->detalleOrden()->create([
                'examen_id' => $examen['examen_id'],
                'nombre_examen' => $examen['nombre_examen'],
                'precio_examen' => $examen['precio_hidden'] ,
                'status' => $examen['recipiente'] ?? null
            ]);
            Log::info("✅ Examen guardado: {$examen['examen_id']}");
        }
        
        return $orden;
    });
}


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


protected function getRedirectUrl(): string
{
    // Redirige a la vista de etiquetas después de guardar
    return DetalleOrdenKanban::getUrl(['ordenId' => $this->record->id]);
}

}
