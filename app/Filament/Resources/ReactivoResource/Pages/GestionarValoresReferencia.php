<?php

namespace App\Filament\Resources\ReactivoResource\Pages;

use App\Filament\Resources\ReactivoResource;
use App\Models\GrupoEtario;
use App\Models\PlantillaReferencia;
use App\Models\Reactivo;
use App\Models\ValorReferencia;
use Filament\Forms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Resources\Pages\Page;
use Filament\Notifications\Notification;

class GestionarValoresReferencia extends Page implements HasForms
{
    protected static string $resource = ReactivoResource::class;

    protected static string $view = 'filament.resources.reactivo-resource.pages.gestionar-valores-referencia';

    public ?array $formData = [];
public ?Reactivo $record = null;

    public function mount(Reactivo $record): void
    {
        $this->record = $record;
    }
    public function form(Form $form): Form
    {
        return $form
            ->schema($this->getPlantillaFormSchema())
            ->statePath('formData');
    }

    /**
     * Genera el schema dinámico según la plantilla seleccionada
     */
    protected function getPlantillaFormSchema(): array
    {
        $plantillas = PlantillaReferencia::pluck('nombre', 'id')->toArray();
        $grupos = GrupoEtario::pluck('nombre', 'id')->toArray();

        return [
            Forms\Components\Select::make('plantilla_referencia_id')
                ->label('Plantilla de referencia')
                ->options($plantillas)
                ->reactive()
                ->required(),

            Forms\Components\Select::make('grupo_etario_id')
                ->label('Grupo etario')
                ->options($grupos)
                ->required(),

            Forms\Components\Repeater::make('datos_referencia')
                ->label('Datos de referencia')
                ->schema(function (callable $get) {
                    $plantillaId = $get('plantilla_referencia_id');
                    if (!$plantillaId) {
                        return [];
                    }

                    $plantilla = PlantillaReferencia::find($plantillaId);
                    if (!$plantilla) {
                        return [];
                    }

                    $estructura = $plantilla->estructura_formulario ?? [];

                    // Creamos inputs según estructura
                    return collect($estructura)->map(function ($campo) {
                        return Forms\Components\TextInput::make($campo['nombre'])
                            ->label($campo['label'] ?? ucfirst($campo['nombre']))
                            ->required($campo['required'] ?? false);
                    })->toArray();
                })
                ->columns(2)
                ->required(),
        ];
    }

    /**
     * Guardar el valor de referencia
     */
    public function createValorReferencia(): void
    {
        $data = $this->form->getState();

        ValorReferencia::create([
            'reactivo_id' => $this->record->id,
            'grupo_etario_id' => $data['grupo_etario_id'],
            'plantilla_referencia_id' => $data['plantilla_referencia_id'],
            'datos_referencia' => $data['datos_referencia'],
        ]);

        Notification::make()
            ->title('Valor de referencia guardado correctamente')
            ->success()
            ->send();

        $this->form->fill(); // limpia el formulario
    }
}
