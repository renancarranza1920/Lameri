<?php

namespace App\Filament\Resources\OrdenResource\Pages;

use App\Filament\Resources\OrdenResource;
use App\Models\Examen;
use App\Models\GrupoEtario;
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
use Illuminate\Support\Str; // Importante para generar IDs temporales en la vista

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
        abort_unless(auth()->user()->can('ingresar_resultados_orden'), 403);
        $this->record = $record;
        // Cargamos los datos iniciales (internos y externos) al formulario
        $this->form->fill($this->prepareInitialData());
    }

    public function form(Form $form): Form
    {
        return $form->schema([
            // Apuntamos a la vista Blade personalizada que contiene las pestañas y tablas
            View::make('filament.resources.orden-resource.pages.ingresar-resultados-view')
                ->statePath('resultados_examenes'),
        ])->statePath('data');
    }
public function isOrderComplete(): bool
{
    // 1. Calcular total de pruebas INTERNAS requeridas
    // Filtramos los detalles cuyo EXAMEN no sea externo
    $totalPruebasInternas = $this->record->detalleOrden
        ->whereNotNull('examen_id')
        ->filter(function ($detalle) {
            // Verificamos que exista el examen y que NO sea externo
            return $detalle->examen && ! $detalle->examen->es_externo;
        })
        ->flatMap(fn ($detalle) => $detalle->examen->pruebas)
        ->count();

    // 2. Contar resultados INTERNOS válidos ya ingresados
    $resultadosInternosIngresados = $this->record->resultados()
        ->where('es_externo', false)
        ->whereNotNull('resultado')
        ->where('resultado', '!=', '')
        ->count();

    // ESCENARIO A: La orden es 100% Externa (No requiere pruebas internas)
    if ($totalPruebasInternas === 0) {
        // Opción 1: Permitir completar siempre (el botón sale siempre)
        return true; 
        
        // Opción 2 (Más estricta): Solo permitir si ya ingresó al menos 1 resultado externo
        // return $this->record->resultados()->where('es_externo', true)->exists();
    }

    // ESCENARIO B: La orden tiene pruebas internas
    // El botón solo sale si se han llenado todos los resultados internos
    return $totalPruebasInternas === $resultadosInternosIngresados;
}

    protected function prepareInitialData(): array
    {
        $detalles = $this->record->detalleOrden()->with(['examen.pruebas.reactivoEnUso.valoresReferencia.grupoEtario'])->get();
        $preparedData = [];

        foreach ($detalles as $detalle) {

            if (!$detalle->examen) continue;

            // --- LÓGICA HÍBRIDA (SNAPSHOT VS BD) ---
            if (!empty($detalle->pruebas_snapshot)) {
                $todasLasPruebas = collect($detalle->pruebas_snapshot)->map(function($item) {
                    return json_decode(json_encode($item)); // Truco rápido para convertir array profuso a objetos stdClass
                });
                 } elseif ($detalle->examen) {
               // Fallback a BD antigua
                $detalle->load('examen.pruebas.reactivoEnUso.valoresReferencia');
                $todasLasPruebas = $detalle->examen->pruebas;
            } else {
                continue;
            }
            
            
            // --- LÓGICA 1: RESULTADOS INTERNOS (CATÁLOGO) ---
            $pruebasUnitarias = $todasLasPruebas->whereNull('tipo_conjunto');
            $pruebasConjuntas = $todasLasPruebas->whereNotNull('tipo_conjunto')->groupBy('tipo_conjunto');

            // Mapeamos pruebas unitarias
          $dataUnitarias = $pruebasUnitarias->map(fn($prueba) => $this->getPruebaData($prueba, $detalle->id))->values()->all();  
            // Mapeamos matrices
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
                return [
                    'filas' => array_values(array_unique($filas)), 
                    'columnas' => array_values(array_unique($columnas)), 
                    'data' => $dataMatrix
                ];
            })->all();

            // --- LÓGICA 2: RESULTADOS EXTERNOS (MANUALES) ---
            // Buscamos en la BD si ya existen resultados guardados como externos para este detalle
            $resultadosExternosDB = $this->record->resultados()
                ->where('detalle_orden_id', $detalle->id)
                ->where('es_externo', true)
                ->get();

            $dataExternos = [];
            foreach ($resultadosExternosDB as $resExt) {
                $dataExternos[] = [
                    'id' => $resExt->id, // ID real de la BD
                    'temp_id' => Str::uuid()->toString(), // ID temporal para manejo visual
                    'prueba_nombre' => $resExt->prueba_nombre_snapshot,
                    'resultado' => $resExt->resultado,
                    'valor_referencia' => $resExt->valor_referencia_snapshot,
                    'unidades' => $resExt->unidades_snapshot,
                ];
            }

            // Armamos la estructura final para este detalle (examen)
            $preparedData[$detalle->id] = [
                'examen_nombre' => $detalle->nombre_examen, // Usar nombre congelado del detalle
                'es_referido' => (bool) ($detalle->examen->es_externo ?? false),
                'pruebas_unitarias' => $dataUnitarias,
                'matrices' => $dataMatrices,
                'externos' => $dataExternos,
            ];
        }

        return ['resultados_examenes' => $preparedData];
    }

protected function getPruebaData($prueba, int $detalleId): array
    {
        // 1. Buscamos resultado ya ingresado (siempre de BD)
        $resultadoExistente = $this->record->resultados()
            ->where('prueba_id', $prueba->id)
            ->where('detalle_orden_id', $detalleId)
            ->first();

        $referencia = 'N/A'; 
        $unidades = '';

        // 2. OBTENER LISTA DE VALORES (Desde JSON o Modelo)
        $todosLosValores = collect([]);

        // CASO A: Es un SNAPSHOT (Objeto stdClass del JSON)
        if (isset($prueba->reactivo) && isset($prueba->reactivo->valores_referencia)) {
            // Convertimos el array del json a colección
            $todosLosValores = collect($prueba->reactivo->valores_referencia);
            // Tomamos unidades del primer valor (asumiendo homogeneidad) o lógica especifica
            $unidades = $todosLosValores->first()->unidades ?? '';
        } 
        // CASO B: Es un MODELO (BD Viva)
        elseif ($prueba instanceof \App\Models\Prueba && $prueba->reactivoEnUso) {
            $todosLosValores = $prueba->reactivoEnUso->valoresReferencia;
        }

        // 3. CALCULAR REFERENCIA (Lógica agnóstica: funciona con arrays o modelos)
        if ($todosLosValores->isNotEmpty()) {
            $cliente = $this->record->cliente;
            $generoCliente = $cliente->genero;
            $valorRef = null;

            // Logica Embarazo
            if (isset($this->record->semanas_gestacion) && $this->record->semanas_gestacion) {
                $grupoEmbarazo = GrupoEtario::where('unidad_tiempo', 'semanas')
                    ->where('edad_min', '<=', $this->record->semanas_gestacion)
                    ->where('edad_max', '>=', $this->record->semanas_gestacion)
                    ->first();
                
                if ($grupoEmbarazo) {
                    // Nota: Accedemos como objeto ($val->grupo_etario_id)
                    // Funciona tanto para Eloquent como para json_decode objects
                    $valorRef = $todosLosValores->first(fn($val) => $val->grupo_etario_id == $grupoEmbarazo->id);
                }
            }

            // Logica Edad Cronológica
            if (!$valorRef) {
                $grupoEtarioCliente = $cliente->getGrupoEtario();
                if ($grupoEtarioCliente) {
                    $valorRef = $todosLosValores->first(fn($val) => 
                        $val->grupo_etario_id == $grupoEtarioCliente->id && $val->genero == $generoCliente
                    );
                    
                    if (!$valorRef) {
                        $valorRef = $todosLosValores->first(fn($val) => 
                            $val->grupo_etario_id == $grupoEtarioCliente->id && $val->genero == 'Ambos'
                        );
                    }
                }
            }

            // Fallbacks
            if (!$valorRef) $valorRef = $todosLosValores->first(fn($val) => is_null($val->grupo_etario_id) && $val->genero == $generoCliente);
            if (!$valorRef) $valorRef = $todosLosValores->first(fn($val) => is_null($val->grupo_etario_id) && $val->genero == 'Ambos');
            if (!$valorRef) $valorRef = $todosLosValores->first();

            // Formatear Texto
            if ($valorRef) {
                // Asegurar acceso seguro a propiedades sea array u objeto
                $vMin = is_array($valorRef) ? $valorRef['valor_min'] : $valorRef->valor_min;
                $vMax = is_array($valorRef) ? $valorRef['valor_max'] : $valorRef->valor_max;
                $vOp  = is_array($valorRef) ? $valorRef['operador'] : $valorRef->operador;
                $vDesc = is_array($valorRef) ? ($valorRef['descriptivo'] ?? null) : ($valorRef->descriptivo ?? null);
                $vUni = is_array($valorRef) ? ($valorRef['unidades'] ?? '') : ($valorRef->unidades ?? '');

                $valorMin = rtrim(rtrim(number_format($vMin, 2, '.', ''), '0'), '.');
                $valorMax = rtrim(rtrim(number_format($vMax, 2, '.', ''), '0'), '.');
                
                $referencia = match ($vOp) {
                    'rango' => "$valorMin - $valorMax",
                    '<=' => "≤ $valorMax",
                    '<' => "< $valorMax",
                    '>=' => "≥ $valorMin",
                    '>' => "> $valorMin",
                    '=' => "= $valorMin",
                    default => $vDesc ?? "$valorMin - $valorMax",
                };
                
                // Si viene del modelo, $unidades estaba vacía al inicio, la llenamos aquí
                if(empty($unidades)) $unidades = $vUni;
            }
        }

        return [
            'prueba_id' => $prueba->id, 
            'prueba_nombre' => $prueba->nombre,
            'resultado_id' => $resultadoExistente?->id,
            'resultado' => $resultadoExistente?->resultado, 
            'valor_referencia' => $referencia,
            'unidades' => $unidades,
        ];
    }
    
    // --- MÉTODOS PARA MANIPULAR LA TABLA DE EXTERNOS DESDE LA VISTA ---

    public function addExternalRow($detalleId)
    {
        // Añade una fila vacía al array de externos en memoria
        $this->data['resultados_examenes'][$detalleId]['externos'][] = [
            'id' => null,
            'temp_id' => Str::uuid()->toString(),
            'prueba_nombre' => '',
            'resultado' => '',
            'valor_referencia' => '',
            'unidades' => '',
        ];
    }

    public function removeExternalRow($detalleId, $index, $resultadoId = null)
    {
        // Si tiene ID real, lo borramos de la BD
        if ($resultadoId) {
            $this->deleteResultado($resultadoId);
        }
        // Lo quitamos del array visual
        unset($this->data['resultados_examenes'][$detalleId]['externos'][$index]);
        // Reindexamos para evitar huecos en el array que confundan a JavaScript
        $this->data['resultados_examenes'][$detalleId]['externos'] = array_values($this->data['resultados_examenes'][$detalleId]['externos']);
    }

    // --- ACCIONES ---

    protected function getFormActions(): array
    { 
        return [Action::make('save')->label('Guardar Resultados')->submit('save')->visible(fn() => auth()->user()->can('ingresar_resultados_orden'))]; 
    }
    
    protected function getHeaderActions(): array
    {
        return [
            Action::make('reconstruir')
                ->label('Sincronizar con Catálogo')
                ->color('warning')
                ->icon('heroicon-o-arrow-path')
                // Solo visible si la orden no está finalizada/cancelada
                ->visible(fn() => !in_array($this->record->estado, ['finalizado', 'cancelado']))
                ->requiresConfirmation()
                ->modalHeading('¿Actualizar definición de pruebas?')
                ->modalDescription('Esto volverá a cargar los nombres, reactivos activos y valores de referencia desde el catálogo maestro. Úsalo si corregiste un error tipográfico o cambiaste un rango de referencia y necesitas que esta orden lo refleje.')
                ->action(function () {
                    // Recorremos cada detalle (examen) de la orden
                    foreach ($this->record->detalleOrden as $detalle) {
                        
                        // Solo procesamos si está vinculado a un examen del catálogo
                        if ($detalle->examen_id) {
                            
                            // 1. Cargamos la data FRESCA y PROFUNDA de la BD
                            $examenFresco = Examen::with([
                                'pruebas.reactivoEnUso.valoresReferencia'
                            ])->find($detalle->examen_id);
                            
                            if ($examenFresco) {
                                // 2. Generamos el NUEVO SNAPSHOT (Misma lógica que en CreateOrden)
                                $nuevoSnapshot = $examenFresco->pruebas->map(function($prueba) {
                                    
                                    $data = [
                                        'id' => $prueba->id,
                                        'nombre' => $prueba->nombre,
                                        'tipo_conjunto' => $prueba->tipo_conjunto,
                                        'tipo_prueba_id' => $prueba->tipo_prueba_id,
                                        'reactivo' => null,
                                    ];

                                    // Si hay reactivo activo, guardamos sus datos y sus valores filtrados
                                    if ($prueba->reactivoEnUso) {
                                        
                                        // AQUI ESTA TU NUEVA LOGICA DE FILTRADO POR PRUEBA_ID
                                        $valoresRefFiltrados = $prueba->reactivoEnUso->valoresReferencia
                                            ->filter(function($val) use ($prueba) {
                                                // Aceptamos si el valor apunta a esta prueba específica
                                                // O si es null (valor genérico para el reactivo)
                                                return $val->prueba_id === $prueba->id || is_null($val->prueba_id);
                                            })
                                            ->map(function($val) {
                                                return [
                                                    'grupo_etario_id' => $val->grupo_etario_id,
                                                    'genero' => $val->genero,
                                                    'valor_min' => $val->valor_min,
                                                    'valor_max' => $val->valor_max,
                                                    'operador' => $val->operador,
                                                    'unidades' => $val->unidades,
                                                    'descriptivo' => $val->descriptivo,
                                                ];
                                            })->values()->toArray();

                                        $data['reactivo'] = [
                                            'nombre' => $prueba->reactivoEnUso->nombre,
                                            'lote' => $prueba->reactivoEnUso->lote,
                                            'valores_referencia' => $valoresRefFiltrados
                                        ];
                                    }

                                    return $data;
                                })->toArray();

                                // 3. Guardamos el JSON actualizado en el detalle
                                $detalle->update(['pruebas_snapshot' => $nuevoSnapshot]);
                            }
                        }
                    }

                    Notification::make()->title('Orden sincronizada correctamente')->success()->send();
                    
                    // Recargamos la página para que la vista lea el nuevo JSON
                    return redirect(request()->header('Referer'));
                }),
            ////
            Action::make('completar')->label('Completar Orden')->color('success')->icon('heroicon-o-check-circle')
                ->visible(fn (): bool => $this->isOrderComplete())->requiresConfirmation()->modalHeading('Finalizar Orden')
                ->modalDescription('Una vez completada, ya no podrás ingresar más resultados. ¿Estás seguro?')
                ->action(function () {
                    $this->record->estado = 'finalizado'; 
                    $this->record->save();
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
            // 1. Guardar Internos (Pruebas Unitarias)
            foreach (($examenData['pruebas_unitarias'] ?? []) as $r) { 
                $this->guardarResultado($detalleId, $r); 
            }
            // 2. Guardar Internos (Matrices)
            foreach (($examenData['matrices'] ?? []) as $m) {
                foreach ($m['data'] as $f) { 
                    foreach ($f as $c) { 
                        $this->guardarResultado($detalleId, $c); 
                    } 
                }
            }
            // 3. Guardar Externos (NUEVO)
            foreach (($examenData['externos'] ?? []) as $ext) {
                // Solo guardamos si el usuario escribió al menos nombre y resultado
                if (!empty($ext['prueba_nombre']) && !empty($ext['resultado'])) {
                    $this->guardarResultadoExterno($detalleId, $ext);
                }
            }
        }
        
        Notification::make()->title('Resultados guardados')->success()->send();
        // Recargamos los datos para que los nuevos IDs se reflejen en el formulario
        $this->form->fill($this->prepareInitialData());
    }

    protected function guardarResultado(int $detalleId, array $r): void
    {
        if (isset($r['resultado']) && $r['resultado'] !== '' && !is_null($r['resultado'])) {
            $this->record->resultados()->updateOrCreate(
                [
                    'detalle_orden_id' => $detalleId, 
                    'prueba_id' => $r['prueba_id']
                ],
                [
                    'resultado' => $r['resultado'],
                    'user_id' => auth()->id(),
                    'prueba_nombre_snapshot' => $r['prueba_nombre'],
                    'valor_referencia_snapshot' => $r['valor_referencia'],
                    'unidades_snapshot' => $r['unidades'],
                    'es_externo' => 0 // Forzamos 0 para internos
                ]
            );
        }
    }

    protected function guardarResultadoExterno(int $detalleId, array $ext): void
    {
        $dataToSave = [
            'detalle_orden_id' => $detalleId,
            'prueba_id' => null, // No tiene ID de catálogo
            'prueba_nombre_snapshot' => $ext['prueba_nombre'],
            'resultado' => $ext['resultado'],
            'user_id' => auth()->id(),
            'valor_referencia_snapshot' => $ext['valor_referencia'],
            'unidades_snapshot' => $ext['unidades'],
            'es_externo' => 1, // Forzamos 1 para externos
        ];

        if (!empty($ext['id'])) {
            // Si ya tiene ID, actualizamos
            $this->record->resultados()->where('id', $ext['id'])->update($dataToSave);
        } else {
            // Si no, creamos uno nuevo
            $this->record->resultados()->create($dataToSave);
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