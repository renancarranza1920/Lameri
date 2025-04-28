<?php

namespace App\Filament\Resources\PerfilResource\Pages;

use App\Filament\Resources\PerfilResource;
use App\Models\Perfil;
use Filament\Actions;
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
// En EditPerfil.php (o tu clase del recurso)
public function mount(int|string $record): void
{
    parent::mount($record);
    
    // Obtener el perfil
    $perfil = Perfil::find($record);
    $perfilId = $perfil ? $perfil->id : null;

    $this->viewData = ['perfilId' => $perfilId]; // Aquí asignas el perfilId de manera segura
}



    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
