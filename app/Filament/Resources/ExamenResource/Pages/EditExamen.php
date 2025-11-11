<?php
namespace App\Filament\Resources\ExamenResource\Pages;
use App\Filament\Resources\ExamenResource;
use Filament\Resources\Pages\EditRecord;
use Filament\Actions;

class EditExamen extends EditRecord
{
    protected static string $resource = ExamenResource::class;

 protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    
}