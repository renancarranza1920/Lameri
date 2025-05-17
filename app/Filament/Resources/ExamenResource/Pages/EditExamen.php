<?php

namespace App\Filament\Resources\ExamenResource\Pages;

use App\Filament\Resources\ExamenResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditExamen extends EditRecord
{
    protected static string $resource = ExamenResource::class;

    protected function handleRecordUpdate(\Illuminate\Database\Eloquent\Model $record, array $data): \Illuminate\Database\Eloquent\Model
    {
        $updatedRecord = parent::handleRecordUpdate($record, $data);

        $this->redirect($this->getResource()::getUrl('index'));

        return $updatedRecord;
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
