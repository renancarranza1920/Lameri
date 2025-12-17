<?php

namespace App\Filament\Resources\OrdenResource\Pages;

use App\Filament\Resources\OrdenResource;
use App\Models\Examen;
use App\Models\GrupoEtario;
use App\Models\Orden;
use App\Models\Prueba; // Asegúrate de importar el modelo
use Filament\Actions\Action;
use Filament\Forms\Components\View;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Page;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

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
        $this->form->fill($this->prepareInitialData());
    }

    public function form(Form $form): Form
    {
        return $form->schema([
            View::make('filament.resources.orden-resource.pages.ingresar-resultados-view')
                ->statePath('resultados_examenes'),
        ])->statePath('data');
    }

    // En app/Filament/Resources/OrdenResource/Pages/IngresarResultados.php

public function isOrderComplete(): bool
{
    // 1. Traemos los detalles para iterar uno por uno
    $detalles = $this->record->detalleOrden()
        ->whereNotNull('examen_id')
        // Cargamos la relación para no hacer mil consultas
        ->with(['examen', 'orden.resultados']) 
        ->get();

    $totalRequeridas = 0;
    $totalCompletadas = 0;

    foreach ($detalles as $detalle) {
        // Saltamos los externos porque esos se manejan diferente
        if ($detalle->examen && $detalle->examen->es_externo) continue;

        // A. DETERMINAR QUÉ PRUEBAS PIDE EL SNAPSHOT ACTUAL
        $idsRequeridos = [];
        
        if (!empty($detalle->pruebas_snapshot)) {
            // Si hay foto, sacamos los IDs de la foto
            $idsRequeridos = collect($detalle->pruebas_snapshot)->pluck('id')->toArray();
        } elseif ($detalle->examen) {
            // Si es vieja, sacamos los IDs activos de la BD
            $idsRequeridos = $detalle->examen->pruebas
                ->where('estado', 'activo') // Tu filtro personalizado
                ->pluck('id')
                ->toArray();
        }

        if (empty($idsRequeridos)) continue;

        // Sumamos al contador global de lo que se necesita
        $totalRequeridas += count($idsRequeridos);

        // B. CONTAR RESULTADOS VÁLIDOS (LA CORRECCIÓN MÁGICA)
        // Usamos la colección de resultados en memoria ($this->record->resultados)
        // y filtramos para contar SOLO los que coinciden con los IDs requeridos.
        $completadasEnEsteDetalle = $this->record->resultados
            ->where('detalle_orden_id', $detalle->id)
            ->whereIn('prueba_id', $idsRequeridos) // <--- ¡AQUÍ ESTÁ EL TRUCO! Ignoramos los huérfanos
            ->whereNotNull('resultado')
            ->where('resultado', '!=', '')
            ->count();

        $totalCompletadas += $completadasEnEsteDetalle;
    }

    // Si por alguna razón no se requieren pruebas, asumimos completo
    if ($totalRequeridas === 0) return true;

    // Ahora sí: 1 requerido === 1 completado válido (aunque sobren huérfanos)
    return $totalRequeridas === $totalCompletadas;
}

    protected function prepareInitialData(): array
    {
        // Cargamos la relación correcta 'reactivosActivos' para evitar errores SQL
        $detalles = $this->record->detalleOrden()
            ->with(['examen.pruebas.reactivosActivos.valoresReferencia.grupoEtario']) 
            ->get();
            
        $preparedData = [];

        foreach ($detalles as $detalle) {

            if (!$detalle->examen) continue;

            // --- LÓGICA HÍBRIDA (SNAPSHOT VS BD) ---
            if (!empty($detalle->pruebas_snapshot)) {
                $todasLasPruebas = collect($detalle->pruebas_snapshot)->map(function ($item) {
                    return json_decode(json_encode($item)); 
                });
            } else {
                // Si no hay snapshot, no precargamos nada
                $todasLasPruebas = collect();
            }

            // Clasificación
            $pruebasUnitarias = $todasLasPruebas->whereNull('tipo_conjunto');
            $pruebasConjuntas = $todasLasPruebas->whereNotNull('tipo_conjunto')->groupBy('tipo_conjunto');

            // Mapeo (Nota: quitamos el tipo 'Prueba $prueba' para que soporte stdClass del snapshot)
            $dataUnitarias = $pruebasUnitarias->map(fn($prueba) => $this->getPruebaData($prueba, $detalle->id))->values()->all();
            
            $dataMatrices = $pruebasConjuntas->map(function ($pruebasDelConjunto) use ($detalle) {
                $filas = [];
                $columnas = [];
                $dataMatrix = [];
                foreach ($pruebasDelConjunto as $prueba) {
                    $partes = explode(', ', $prueba->nombre);
                    if (count($partes) >= 2) {
                        [$nombreFila, $nombreColumna] = $partes;
                        $filas[] = $nombreFila;
                        $columnas[] = $nombreColumna;
                        $dataMatrix[$nombreFila][$nombreColumna] = $this->getPruebaData($prueba, $detalle->id);
                    }
                }
                return [
                    'filas' => array_values(array_unique($filas)),
                    'columnas' => array_values(array_unique($columnas)),
                    'data' => $dataMatrix
                ];
            })->all();

            // Externos
            $resultadosExternosDB = $this->record->resultados()
                ->where('detalle_orden_id', $detalle->id)
                ->where('es_externo', true)
                ->get();

            $dataExternos = [];
            foreach ($resultadosExternosDB as $resExt) {
                $dataExternos[] = [
                    'id' => $resExt->id,
                    'temp_id' => Str::uuid()->toString(),
                    'prueba_nombre' => $resExt->prueba_nombre_snapshot,
                    'resultado' => $resExt->resultado,
                    'valor_referencia' => $resExt->valor_referencia_snapshot,
                    'unidades' => $resExt->unidades_snapshot,
                ];
            }

            $preparedData[$detalle->id] = [
                'examen_nombre' => $detalle->nombre_examen,
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
    // 1. Buscamos si ya hay un resultado guardado
    $resultadoExistente = $this->record->resultados()
        ->where('prueba_id', $prueba->id)
        ->where('detalle_orden_id', $detalleId)
        ->first();

    $referencia = ''; 
    $unidades = '';
    $esAlerta = false; 
    $mensajeAlerta = '';

    // 2. Obtener la lista de todos los valores de referencia posibles
    $todosLosValores = collect([]);

    if (isset($prueba->reactivo) && isset($prueba->reactivo->valores_referencia)) {
        // Viene del Snapshot (JSON)
        $todosLosValores = collect($prueba->reactivo->valores_referencia);
        $unidades = $todosLosValores->first()->unidades ?? ''; 
    } elseif ($prueba instanceof \App\Models\Prueba) {
        // Viene de la BD (Orden vieja o sin snapshot)
        $reactivo = $prueba->reactivosActivos->first();
        if ($reactivo) {
            $todosLosValores = $reactivo->valoresReferencia;
        }
    }

    // 3. Lógica de Selección Prioritaria (El corazón del cambio)
    if ($todosLosValores->isNotEmpty()) {
        $cliente = $this->record->cliente;
        $generoCliente = $cliente->genero; // 'Masculino' o 'Femenino'
        $valorRef = null;

        // --- PRE-FILTRO: Caso Especial Embarazo (Prioridad Absoluta) ---
        if (isset($this->record->semanas_gestacion) && $this->record->semanas_gestacion) {
             $grupoEmbarazo = \App\Models\GrupoEtario::where('unidad_tiempo', 'semanas')
                ->where('edad_min', '<=', $this->record->semanas_gestacion)
                ->where('edad_max', '>=', $this->record->semanas_gestacion)
                ->first();

            if ($grupoEmbarazo) {
                $valorRef = $todosLosValores->first(function($val) use ($grupoEmbarazo) {
                    $gid = is_array($val) ? ($val['grupo_etario_id'] ?? null) : $val->grupo_etario_id;
                    return $gid == $grupoEmbarazo->id;
                });
            }
        }

        // Si no es embarazo, seguimos la lógica estándar 1-2-3-4
        if (!$valorRef) {
            
            // Paso A: Obtener el Grupo Etario Específico (Ej: Adultos)
            $grupoEtarioCliente = $cliente->getGrupoEtario(); 

            // Paso B: Obtener el Grupo "Todas las edades" (Ej: 0-120 años)
            $grupoTodasEdades = \App\Models\GrupoEtario::where('nombre', 'Todas las edades')
                ->orWhere(function($query) {
                    $query->where('edad_min', 0)->where('edad_max', '>=', 120);
                })->first();

            // --- INTENTO 1: Grupo Específico + Género Exacto ---
            if ($grupoEtarioCliente) {
                $valorRef = $todosLosValores->first(function($val) use ($grupoEtarioCliente, $generoCliente) {
                    $gid = is_array($val) ? ($val['grupo_etario_id'] ?? null) : $val->grupo_etario_id;
                    $gen = is_array($val) ? ($val['genero'] ?? null) : $val->genero;
                    return $gid == $grupoEtarioCliente->id && $gen == $generoCliente;
                });
            }

            // --- INTENTO 2: Grupo Específico + "Ambos" ---
            if (!$valorRef && $grupoEtarioCliente) {
                $valorRef = $todosLosValores->first(function($val) use ($grupoEtarioCliente) {
                    $gid = is_array($val) ? ($val['grupo_etario_id'] ?? null) : $val->grupo_etario_id;
                    $gen = is_array($val) ? ($val['genero'] ?? null) : $val->genero;
                    return $gid == $grupoEtarioCliente->id && $gen == 'Ambos';
                });
            }

            // --- INTENTO 3: Grupo "Todas las edades" + Género Exacto ---
            if (!$valorRef && $grupoTodasEdades) {
                $valorRef = $todosLosValores->first(function($val) use ($grupoTodasEdades, $generoCliente) {
                    $gid = is_array($val) ? ($val['grupo_etario_id'] ?? null) : $val->grupo_etario_id;
                    $gen = is_array($val) ? ($val['genero'] ?? null) : $val->genero;
                    return $gid == $grupoTodasEdades->id && $gen == $generoCliente;
                });
            }

            // --- INTENTO 4: Grupo "Todas las edades" + "Ambos" ---
            if (!$valorRef && $grupoTodasEdades) {
                $valorRef = $todosLosValores->first(function($val) use ($grupoTodasEdades) {
                    $gid = is_array($val) ? ($val['grupo_etario_id'] ?? null) : $val->grupo_etario_id;
                    $gen = is_array($val) ? ($val['genero'] ?? null) : $val->genero;
                    return $gid == $grupoTodasEdades->id && $gen == 'Ambos';
                });
            }
        }

        // 4. Resultado del Proceso
        if ($valorRef) {
            // --- ÉXITO: Formateamos el valor encontrado ---
            $vMin = is_array($valorRef) ? $valorRef['valor_min'] : $valorRef->valor_min;
            $vMax = is_array($valorRef) ? $valorRef['valor_max'] : $valorRef->valor_max;
            $vOp = is_array($valorRef) ? $valorRef['operador'] : $valorRef->operador;
            $vDesc = is_array($valorRef) ? ($valorRef['descriptivo'] ?? null) : ($valorRef->descriptivo ?? null);
            $vUni = is_array($valorRef) ? ($valorRef['unidades'] ?? '') : ($valorRef->unidades ?? '');

            $valorMin = rtrim(rtrim(number_format((float)$vMin, 2, '.', ''), '0'), '.');
            $valorMax = rtrim(rtrim(number_format((float)$vMax, 2, '.', ''), '0'), '.');

            $referencia = match ($vOp) {
                'rango' => "$valorMin - $valorMax",
                '<=' => "≤ $valorMax",
                '<' => "< $valorMax",
                '>=' => "≥ $valorMin",
                '>' => "> $valorMin",
                '=' => "= $valorMin",
                default => $vDesc ?? "$valorMin - $valorMax",
            };

            if (empty($unidades)) $unidades = $vUni;
            
        } else {
            // --- FALLO: Activamos la Alerta ---
            $esAlerta = true;
            $referencia = "N/A"; 
            
            $grupoNombre = $cliente->getGrupoEtario()?->nombre ?? 'Edad sin clasificar';
            $mensajeAlerta = "Falta conf. para: {$grupoNombre} ({$generoCliente})";
        }

    } else {
         // No hay valores configurados en el reactivo siquiera
         $esAlerta = false; 
         $referencia = '';
    }

    return [
        'prueba_id' => $prueba->id,
        'prueba_nombre' => $prueba->nombre,
        'resultado_id' => $resultadoExistente?->id,
        'resultado' => $resultadoExistente?->resultado,
        'valor_referencia' => $referencia,
        'unidades' => $unidades,
        'es_alerta' => $esAlerta,
        'mensaje_alerta' => $mensajeAlerta
    ];
}
    public function addExternalRow($detalleId)
    {
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
        if ($resultadoId) {
            $this->deleteResultado($resultadoId);
        }
        unset($this->data['resultados_examenes'][$detalleId]['externos'][$index]);
        $this->data['resultados_examenes'][$detalleId]['externos'] = array_values($this->data['resultados_examenes'][$detalleId]['externos']);
    }

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
                ->visible(fn() => !in_array($this->record->estado, ['finalizado', 'cancelado']))
                ->requiresConfirmation()
                ->modalHeading('¿Actualizar definición de pruebas?')
                ->modalDescription('Esto actualizará la orden con la configuración actual del catálogo (nombres, reactivos y valores de referencia).')
                ->action(function () {
                    foreach ($this->record->detalleOrden as $detalle) {
                        if ($detalle->examen_id) {
                            // 1. Cargamos la data FRESCA con la relación correcta
                            $examenFresco = Examen::with([
                                'pruebas.reactivosActivos.valoresReferencia' // <--- CORRECTO
                            ])->find($detalle->examen_id);

                            if ($examenFresco) {
                                // 2. Generamos el NUEVO SNAPSHOT
                                $nuevoSnapshot = $examenFresco->pruebas->where('estado', 'activo')->map(function ($prueba) {
                                    $data = [
                                        'id' => $prueba->id,
                                        'nombre' => $prueba->nombre,
                                        'tipo_conjunto' => $prueba->tipo_conjunto,
                                        'tipo_prueba_id' => $prueba->tipo_prueba_id,
                                        'reactivo' => null,
                                    ];

                                    // CORRECCIÓN: Usamos la colección y sacamos el primero
                                    $reactivoEnUso = $prueba->reactivosActivos->first();

                                    if ($reactivoEnUso) {
                                        $valoresRefFiltrados = $reactivoEnUso->valoresReferencia
                                            ->filter(function ($val) use ($prueba) {
                                                return $val->prueba_id === $prueba->id || is_null($val->prueba_id);
                                            })
                                            ->map(function ($val) {
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
                                            'nombre' => $reactivoEnUso->nombre,
                                            'lote' => $reactivoEnUso->lote,
                                            'valores_referencia' => $valoresRefFiltrados
                                        ];
                                    }
                                    return $data;
                                })->toArray();

                                // 3. Guardamos
                                $detalle->update(['pruebas_snapshot' => $nuevoSnapshot]);
                            }
                        }
                    }
                    Notification::make()->title('Orden sincronizada')->success()->send();
                    return redirect(request()->header('Referer'));
                }),

            Action::make('completar')->label('Completar Orden')->color('success')->icon('heroicon-o-check-circle')
                ->visible(fn(): bool => $this->isOrderComplete())
                ->requiresConfirmation()
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
            foreach (($examenData['pruebas_unitarias'] ?? []) as $r) {
                $this->guardarResultado($detalleId, $r);
            }
            foreach (($examenData['matrices'] ?? []) as $m) {
                foreach ($m['data'] as $f) {
                    foreach ($f as $c) {
                        $this->guardarResultado($detalleId, $c);
                    }
                }
            }
            foreach (($examenData['externos'] ?? []) as $ext) {
                if (!empty($ext['prueba_nombre']) && !empty($ext['resultado'])) {
                    $this->guardarResultadoExterno($detalleId, $ext);
                }
            }
        }
        Notification::make()->title('Resultados guardados')->success()->send();
        $this->form->fill($this->prepareInitialData());
    }

    protected function guardarResultado(int $detalleId, array $r): void
    {
        if (isset($r['resultado']) && $r['resultado'] !== '' && !is_null($r['resultado'])) {
            $this->record->resultados()->updateOrCreate(
                ['detalle_orden_id' => $detalleId, 'prueba_id' => $r['prueba_id']],
                [
                    'resultado' => $r['resultado'],
                    'user_id' => auth()->id(),
                    'prueba_nombre_snapshot' => $r['prueba_nombre'],
                    'valor_referencia_snapshot' => $r['valor_referencia'],
                    'unidades_snapshot' => $r['unidades'],
                    'es_externo' => 0
                ]
            );
        }
    }

    protected function guardarResultadoExterno(int $detalleId, array $ext): void
    {
        $dataToSave = [
            'detalle_orden_id' => $detalleId,
            'prueba_id' => null,
            'prueba_nombre_snapshot' => $ext['prueba_nombre'],
            'resultado' => $ext['resultado'],
            'user_id' => auth()->id(),
            'valor_referencia_snapshot' => $ext['valor_referencia'],
            'unidades_snapshot' => $ext['unidades'],
            'es_externo' => 1,
        ];

        if (!empty($ext['id'])) {
            $this->record->resultados()->where('id', $ext['id'])->update($dataToSave);
        } else {
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