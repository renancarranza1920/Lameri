<?php

namespace App\Filament\Resources\TipoExamenResource\Pages;

use App\Filament\Resources\TipoExamenResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditTipoExamen extends EditRecord
{
    protected static string $resource = TipoExamenResource::class;

    protected function handleRecordUpdate(\Illuminate\Database\Eloquent\Model $record, array $data): \Illuminate\Database\Eloquent\Model
    {
        $updatedRecord = parent::handleRecordUpdate($record, $data);

        $this->redirect($this->getResource()::getUrl('index'));

        return $updatedRecord;
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->before(function ($record, $action) {
                    if ($record->examenes()->count() > 0) {
                        \Filament\Notifications\Notification::make()
                            ->title('No se puede eliminar')
                            ->body('No puedes eliminar este tipo de examen porque tiene exámenes asociados.')
                            ->danger()
                            ->send();
                        $action->cancel();
                    }
                }),
        ];
    }
}
