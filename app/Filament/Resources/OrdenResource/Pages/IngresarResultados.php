<?php

namespace App\Filament\Resources\OrdenResource\Pages;

use App\Filament\Resources\OrdenResource;
use App\Models\Orden;
use App\Models\Prueba;
use Filament\Actions\Action;
use Filament\Forms\Components\View;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Page;
use Illuminate\Support\Collection;

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

    // --- CORRECCIÓN 2: El formulario ahora renderiza la vista COMPONENTE ---
    public function form(Form $form): Form
    {
        return $form->schema([
            // Aquí apuntamos a la vista que tiene toda la lógica de tablas y bucles
            View::make('filament.resources.orden-resource.pages.ingresar-resultados-view') 
                ->statePath('resultados_examenes'),
        ])->statePath('data');
    }

    public function isOrderComplete(): bool
    {
        $totalPruebas = $this->record->detalleOrden
            ->whereNotNull('examen_id')
            ->flatMap(fn ($detalle) => $detalle->examen->pruebas)
            ->count();
        $resultadosIngresados = $this->record->resultados()
            ->where('resultado', '!=', '')
            ->whereNotNull('resultado')
            ->count();
        return ($totalPruebas > 0) && ($totalPruebas === $resultadosIngresados);
    }

    protected function prepareInitialData(): array
    {
        $detalles = $this->record->detalleOrden()->with(['examen.pruebas.reactivoEnUso.valoresReferencia.grupoEtario'])->get();
        $preparedData = [];

        foreach ($detalles as $detalle) {
            if (!$detalle->examen) continue;

            $examen = $detalle->examen;
            $todasLasPruebas = $examen->pruebas;

            $pruebasUnitarias = $todasLasPruebas->whereNull('tipo_conjunto');
            $pruebasConjuntas = $todasLasPruebas->whereNotNull('tipo_conjunto')->groupBy('tipo_conjunto');

            $dataUnitarias = $pruebasUnitarias->map(fn(Prueba $prueba) => $this->getPruebaData($prueba, $detalle->id))->values()->all();
            
            $dataMatrices = $pruebasConjuntas->map(function (Collection $pruebasDelConjunto) use ($detalle) {
                $filas = []; $columnas = []; $dataMatrix = [];
                foreach ($pruebasDelConjunto as $prueba) {
                    $partes = explode(', ', $prueba->nombre);
                    if (count($partes) >= 2) {
                        [$nombreFila, $nombreColumna] = $partes;
                        $filas[] = $nombreFila; $columnas[] = $nombreColumna;
                        $dataMatrix[$nombreFila][$nombreColumna] = $this->getPruebaData($prueba, $detalle->id);
                    }
                }
                return ['filas' => array_values(array_unique($filas)), 'columnas' => array_values(array_unique($columnas)), 'data' => $dataMatrix];
            })->all();

            $preparedData[$detalle->id] = [
                'examen_nombre' => $examen->nombre,
                'pruebas_unitarias' => $dataUnitarias,
                'matrices' => $dataMatrices,
            ];
        }

        return ['resultados_examenes' => $preparedData];
    }

   protected function getPruebaData(Prueba $prueba, int $detalleId): array
    {
        $resultadoExistente = $this->record->resultados()->where('prueba_id', $prueba->id)->where('detalle_orden_id', $detalleId)->first();
        $referencia = 'N/A'; $unidades = '';
        if ($prueba->reactivoEnUso && $prueba->reactivoEnUso->valoresReferencia->isNotEmpty()) {
            $valorRef = $prueba->reactivoEnUso->valoresReferencia->first();
            $valorMin = rtrim(rtrim(number_format($valorRef->valor_min, 2, '.', ''), '0'), '.');
            $valorMax = rtrim(rtrim(number_format($valorRef->valor_max, 2, '.', ''), '0'), '.');
            $referencia = "$valorMin - $valorMax";
            $unidades = $valorRef->unidades ?? '';
        }
        return [
            'prueba_id' => $prueba->id, 
            'prueba_nombre' => $prueba->nombre, // <-- Dato que ya teníamos
            'resultado_id' => $resultadoExistente?->id,
            'resultado' => $resultadoExistente?->resultado, 
            'valor_referencia' => $referencia, // <-- Dato que ya teníamos
            'unidades' => $unidades, // <-- Dato que ya teníamos
        ];
    }
    
    protected function getFormActions(): array
    { return [Action::make('save')->label('Guardar Resultados')->submit('save')]; }
    
    protected function getHeaderActions(): array
    {
        return [
            Action::make('completar')->label('Completar Orden')->color('success')->icon('heroicon-o-check-circle')
                ->visible(fn (): bool => $this->isOrderComplete())->requiresConfirmation()->modalHeading('Finalizar Orden')
                ->modalDescription('Una vez completada, ya no podrás ingresar más resultados. ¿Estás seguro?')
                ->action(function () {
                    $this->record->estado = 'finalizado'; $this->record->save();
                    Notification::make()->title('Orden Completada')->success()->send();
                    return redirect(static::getResource()::getUrl('index'));
                }),
            Action::make('regresar')->label('Regresar a Órdenes')->color('gray')->icon('heroicon-o-arrow-left')
                ->url(static::getResource()::getUrl('index')),
        ];
    }

    public function save(): void
    {
        $formData = $this->form->getState()['resultados_examenes'];
        foreach ($formData as $detalleId => $examenData) {
            foreach (($examenData['pruebas_unitarias'] ?? []) as $r) { $this->guardarResultado($detalleId, $r); }
            foreach (($examenData['matrices'] ?? []) as $m) {
                foreach ($m['data'] as $f) { foreach ($f as $c) { $this->guardarResultado($detalleId, $c); } }
            }
        }
        Notification::make()->title('Resultados guardados')->success()->send();
        $this->form->fill($this->prepareInitialData());
    }

      protected function guardarResultado(int $detalleId, array $r): void
    {
        if (isset($r['resultado']) && $r['resultado'] !== '' && !is_null($r['resultado'])) {
            $this->record->resultados()->updateOrCreate(
                [
                    // Búsqueda
                    'detalle_orden_id' => $detalleId, 
                    'prueba_id' => $r['prueba_id']
                ],
                [
                    // Datos a guardar/actualizar
                    'resultado' => $r['resultado'],
                    'prueba_nombre_snapshot' => $r['prueba_nombre'],
                    'valor_referencia_snapshot' => $r['valor_referencia'],
                    'unidades_snapshot' => $r['unidades'],
                ]
            );
        }
    }
    
    public function deleteResultado($resultadoId): void
    {
        $resultado = \App\Models\Resultado::find($resultadoId);
        if ($resultado && $resultado->detalleOrden->orden_id === $this->record->id) {
            $resultado->delete();
            Notification::make()->title('Resultado eliminado')->success()->send();
            $this->form->fill($this->prepareInitialData());
        }
    }
}

