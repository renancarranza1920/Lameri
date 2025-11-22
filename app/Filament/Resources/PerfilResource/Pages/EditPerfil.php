<?php

namespace App\Filament\Resources\PerfilResource\Pages;

use App\Filament\Resources\PerfilResource;
use App\Models\Perfil;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditPerfil extends EditRecord
{

    protected static string $resource = PerfilResource::class;
    
    protected function mutateFormDataBeforeFill(array $data): array
    {
        
        // Cargar los exámenes asociados y meterlos al textarea como JSON
        $examenes = $this->record->examenes()
            ->with('tipoExamen') // Carga la relación
            ->select('examens.id', 'examens.nombre', 'examens.tipo_examen_id')
            ->get()
            ->map(function ($examen) {
                return [
                    'id' => $examen->id,
                    'nombre' => $examen->nombre,
                    //
                    'tipo' => $examen->tipoExamen->nombre ?? '', // Nombre real del tipo
                ];
            })
            ->toArray();
      
        //log para verificar el ID del perfil que se está editando
       
        $data['examenes_seleccionados'] = json_encode($examenes, JSON_UNESCAPED_UNICODE);
       
        return $data;
    }

    public function save(bool $shouldRedirect = true, bool $shouldSendSavedNotification = true): void
    {
        \DB::beginTransaction();

        try {
            // Guardar los cambios en el perfil
               // Guardar campos del formulario (nombre, precio, estado, etc.)
        parent::save(false, false); // Esto guarda los datos del formulario automáticamente


            // Obtener los exámenes seleccionados desde el campo correspondiente
            $examenesJSON = $this->data['examenes_seleccionados'] ?? '[]';

            \Log::debug('Contenido del textarea en EditPerfil:', ['json' => $examenesJSON]);

            // Decodificar el JSON
            $examenes = json_decode($examenesJSON, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new \Exception('Formato JSON inválido en examenes_seleccionados');
            }

            // Extraer los IDs de los exámenes
            $ids = array_column($examenes, 'id');

            \Log::debug('IDs extraídos en EditPerfil:', $ids);

            // Sincronizar los exámenes con el perfil
            $this->record->examenes()->sync($ids);

            \DB::commit();

            // Redirigir si se solicita
            if ($shouldRedirect) {
                $this->redirectRoute('filament.admin.resources.perfils.index');
            }
             // Enviar notificación si se solicita
             if ($shouldSendSavedNotification) {
                Notification::make()
                    ->title('Perfil actualizado correctamente.')
                    ->success()
                    ->send();
            }
            
         
        } catch (\Exception $e) {
            \DB::rollBack();
            \Log::error('Error al guardar perfil en EditPerfil:', [
                'error' => $e->getMessage(),
                'data' => $this->data,
            ]);
            throw $e;
        }
    }

  public function mount(int|string $record): void
{
    parent::mount($record);
    
    // Obtener el perfil
    $perfil = Perfil::find($record);
    $perfilId = $perfil ? $perfil->id : null;

    $this->viewData = ['perfilId' => $perfilId]; // Aquí asignas el perfilId de manera segura
}


}
