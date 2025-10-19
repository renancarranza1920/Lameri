<?php

namespace App\Filament\Resources\CodigoResource\Pages;

use App\Filament\Resources\CodigoResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateCodigo extends CreateRecord
{
    protected static string $resource = CodigoResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
{
    // Si deja la fecha vacía, se guarda como null (sin vencimiento)
    if (empty($data['fecha_vencimiento'])) {
        $data['fecha_vencimiento'] = null;
    }

    // Siempre activo
    $data['estado'] = 'Activo';

    return $data;

}

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

}
