<?php

namespace App\Filament\Resources\ReactivoResource\Pages;

use App\Filament\Resources\ReactivoResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditReactivo extends EditRecord
{
    protected static string $resource = ReactivoResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
    protected function afterSave(): void
    {
        // Lo mismo para cuando editas
        if ($this->record->en_uso) {
            $this->record->resolverConflictosDeUso();
        }
    }
}
