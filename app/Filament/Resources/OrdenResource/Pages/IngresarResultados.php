<?php

namespace App\Filament\Resources\OrdenResource\Pages;

use App\Filament\Resources\OrdenResource;
use App\Models\Orden;
use Filament\Actions\Action;
use Filament\Forms\Components\View;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Page;

class IngresarResultados extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string $resource = OrdenResource::class;
    protected static string $view = 'filament.resources.orden-resource.pages.ingresar-resultados';

    public ?Orden $record;
    public array $data = [];

    public function mount(Orden $record): void
    {
        abort_unless(static::getResource()::canView($record), 404);
        $this->record = $record;
        $this->form->fill($this->prepareInitialData());
    }

    protected function prepareInitialData(): array
    {
        $detalles = $this->record->detalleOrden()->with(['examen.pruebas.reactivoEnUso.valoresReferencia.grupoEtario'])->get();
        $preparedData = [];

        foreach ($detalles as $detalle) {
            if (!$detalle->examen) continue;

            $examen = $detalle->examen;
            $pruebasData = [];

            foreach ($examen->pruebas as $prueba) {
                $resultadoExistente = $this->record->resultados()
                    ->where('prueba_id', $prueba->id)
                    ->where('detalle_orden_id', $detalle->id)
                    ->first();
                
                $referenciaStrings = [];
                $unidadesStrings = [];
                $notasStrings = [];

                if (!$examen->es_externo && $prueba->reactivoEnUso && $prueba->reactivoEnUso->valoresReferencia->isNotEmpty()) {
                    foreach ($prueba->reactivoEnUso->valoresReferencia as $valorRef) {
                        $parts = [];
                        if ($valorRef->genero) $parts[] = "($valorRef->genero)";
                        if ($valorRef->grupoEtario) $parts[] = $valorRef->grupoEtario->nombre;
                        
                        $valorMin = rtrim(rtrim(number_format($valorRef->valor_min, 2, '.', ''), '0'), '.');
                        $valorMax = rtrim(rtrim(number_format($valorRef->valor_max, 2, '.', ''), '0'), '.');

                        $rangoTexto = match ($valorRef->operador) {
                            'rango' => "$valorMin a $valorMax",
                            '<='    => "Hasta $valorMax",
                            '<'     => "Menor que $valorMax",
                            '>='    => "Desde $valorMin",
                            '>'     => "Mayor que $valorMin",
                            '='     => "Igual a $valorMin",
                            default => $valorRef->descriptivo ?? '',
                        };
                        $parts[] = $rangoTexto;
                        
                        $referenciaStrings[] = implode(' ', $parts);
                        $unidadesStrings[] = $valorRef->unidades ?? '';
                        $notasStrings[] = $valorRef->nota ?? '';
                    }
                }

                $pruebasData[] = [
                      'resultado_id' => $resultadoExistente?->id, 
                    'prueba_id' => $prueba->id,
                    'prueba_nombre' => $prueba->nombre,
                    'es_externo' => $examen->es_externo,
                    'resultado' => $resultadoExistente?->resultado,
                    'valor_referencia_display' => implode('<br>', $referenciaStrings),
                    'unidades_display' => implode('<br>', $unidadesStrings),
                    'nota_display' => implode('<br>', $notasStrings),
                    'valor_referencia_externo' => $resultadoExistente?->valor_referencia_externo,
                ];
            }
            
            $preparedData[$detalle->id] = [
                'examen_nombre' => $examen->nombre,
                'pruebas' => $pruebasData,
            ];
        }

        return ['resultados_tabla' => $preparedData];
    }

    public function form(Form $form): Form
    {
        return $form->schema([
            View::make('filament.forms.components.resultados-table')
                ->statePath('resultados_tabla'),
        ])->statePath('data');
    }
    
    protected function getFormActions(): array
    {
        return [ Action::make('save')->label('Guardar Resultados')->submit('save'), ];
    }
protected function getHeaderActions(): array
{
    return [
        Action::make('regresar')
            ->label('Regresar a Órdenes')
            ->color('gray')
            ->icon('heroicon-o-arrow-left')
            ->url(static::getResource()::getUrl('index')),
    ];
}
    public function save(): void
    {
        $formData = $this->form->getState()['resultados_tabla'];

        foreach ($formData as $detalleId => $examenData) {
            if (!isset($examenData['pruebas'])) continue;

            foreach ($examenData['pruebas'] as $resultado) {
                $this->record->resultados()->updateOrCreate(
                    // Condiciones para buscar el registro:
                    [
                        'detalle_orden_id' => $detalleId,
                        'prueba_id' => $resultado['prueba_id'],
                    ],
                    // Datos para guardar o actualizar:
                    [
                        'resultado' => $resultado['resultado'],
                        'valor_referencia_externo' => $resultado['valor_referencia_externo'] ?? null,
                    ]
                );
            }
        }

        Notification::make()->title('Resultados guardados correctamente.')->success()->send();
        
        // Refrescamos el estado del formulario con los datos recién guardados.
        $this->form->fill($this->prepareInitialData());
    }

    public function deleteResultado($resultadoId): void
{
    // Si no se pasó un ID válido, no hacemos nada.
    if (!$resultadoId) {
        return;
    }

    // Buscamos el resultado por su ID
    $resultado = \App\Models\Resultado::find($resultadoId);

    if ($resultado) {
        // Por seguridad, verificamos que el resultado pertenezca a la orden actual
        if ($resultado->detalleOrden->orden_id === $this->record->id) {
            $resultado->delete();

            // Enviamos una notificación de éxito
            Notification::make()
                ->title('Resultado eliminado correctamente')
                ->success()
                ->send();
            
            // Refrescamos el formulario para que la fila se actualice
            $this->form->fill($this->prepareInitialData());
        }
    }
}
}