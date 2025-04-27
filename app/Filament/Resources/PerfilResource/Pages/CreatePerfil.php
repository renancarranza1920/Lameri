<?php

namespace App\Filament\Resources\PerfilResource\Pages;

use App\Filament\Resources\PerfilResource;
use App\Models\Perfil;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreatePerfil extends CreateRecord
{
    protected static string $resource = PerfilResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        \DB::beginTransaction();
        
        try {
            $perfil = Perfil::create([
                'nombre' => $data['nombre'],
                'precio' => $data['precio'],
                'estado' => $data['estado'] ?? true,
            ]);
    
            // Manejar caso cuando no viene el campo
            $examenesData = $data['examenes_seleccionados'] ?? '[]';
            $examenesSeleccionados = json_decode($examenesData, true) ?? [];
            
            \Log::debug('Datos recibidos para exámenes:', [
                'raw' => $examenesData,
                'decoded' => $examenesSeleccionados
            ]);
            
            if (!empty($examenesSeleccionados)) {
                $perfil->examenes()->sync(
                    collect($examenesSeleccionados)->pluck('id')->toArray()
                );
            } else {
                \Log::warning('Se creó perfil sin exámenes asociados');
            }
    
            \DB::commit();
            return $perfil;
        } catch (\Exception $e) {
            \DB::rollBack();
            \Log::error('Error al crear perfil: ' . $e->getMessage());
            throw $e;
        }
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}