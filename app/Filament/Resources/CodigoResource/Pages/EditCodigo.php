<?php

namespace App\Filament\Resources\CodigoResource\Pages;

use App\Filament\Resources\CodigoResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCodigo extends EditRecord
{
    protected static string $resource = CodigoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    //al editar debe realizar lo mismo
    protected function mutateFormDataBeforeUpdate(array $data): array
    {
     

        // Si deja la fecha vacía, se guarda como null (sin vencimiento)
        if (empty($data['fecha_vencimiento'])) {
            $data['fecha_vencimiento'] = null;
        }

        return $data;
    }

    //regresar al listado despues de editar
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
