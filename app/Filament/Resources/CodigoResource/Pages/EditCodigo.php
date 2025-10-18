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
}
