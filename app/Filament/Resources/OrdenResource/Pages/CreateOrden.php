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
        $total = 0;
        

        foreach ($data['perfiles_seleccionados'] ?? [] as $item) {
            $total += floatval($item['precio'] ?? 0);
        }

        foreach ($data['examenes_seleccionados'] ?? [] as $item) {
            $total += floatval($item['precio'] ?? 0);
        }

        $data['total'] = $total;
        $data['fecha'] = Carbon::now();
        $data['estado'] = 'pendiente';

        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
