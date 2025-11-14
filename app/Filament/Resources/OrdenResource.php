<?php

namespace App\Filament\Resources;

use App\Filament\Pages\DetalleOrdenKanban;
use App\Filament\Resources\OrdenResource\Pages;
use App\Models\Codigo;
use App\Models\Orden;
use App\Models\Cliente;
use Carbon\Carbon;
use DB;
use Filament\Forms;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\ViewField;
use Filament\Forms\Components\Wizard;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Response;
use Filament\Forms\Form;
use Filament\Forms\Components\Wizard\Step;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\Tabs\Tab;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Button;
use Filament\Forms\Components\Actions\Action;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\Layout\Split;
use Filament\Tables\Columns\Layout\Stack;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Illuminate\Support\Facades\Log;
use Filament\Notifications\Notification;
use Closure;





class OrdenResource extends Resource
{
    protected static ?string $model = Orden::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-check';
    protected static ?string $navigationLabel = 'Órdenes';
    protected static ?string $slug = 'ordenes';
    protected static ?string $modelLabel = 'Orden';
    protected static ?string $pluralModelLabel = 'Órdenes';
        public float $subtotal = 0;
    public float $descuento = 0;
    public ?Codigo $codigoAplicado = null;
    public static function form(Form $form): Form
    {
        return $form
            ->schema([

                Wizard::make()
                    ->schema([


                    ])
                    ->columnSpanFull(),
            ]);
    }

    public static function getClienteStep(): array
    {
        return [
            Forms\Components\Select::make('cliente_id')
                                ->label('Seleccionar o agregar cliente')
                                ->options(function () {
                    return Cliente::where('estado', 'Activo')
                        ->get()
                        ->mapWithKeys(fn($cliente) => [
                            $cliente->id => $cliente->NumeroExp . ' - ' . $cliente->nombre . ' ' . $cliente->apellido
                        ]);
                })
                                ->relationship(
                                    name: 'cliente',
                                    titleAttribute: 'nombre'
                                )
                                ->preload()
                                ->searchable(['NumeroExp', 'nombre', 'apellido'])
                                ->getOptionLabelFromRecordUsing(fn($record) => "{$record->NumeroExp} - {$record->nombre} {$record->apellido}")
                                ->createOptionForm([
                                    Forms\Components\TextInput::make('nombre')
                                        ->required()
                                        ->maxLength(255),

                                    Forms\Components\TextInput::make('apellido')
                                        ->required()
                                        ->maxLength(255),

                                    Forms\Components\DatePicker::make('fecha_nacimiento')
                                        ->label('Fecha de Nacimiento')
                                        ->required(),

                                    Forms\Components\TextInput::make('telefono')
                                        ->maxLength(9),

                                    Forms\Components\TextInput::make('correo')
                                        ->email()
                                        ->maxLength(255),

                                    Forms\Components\TextInput::make('direccion')
                                        ->maxLength(255),
                                ])
                                ->createOptionAction(function (Forms\Components\Actions\Action $action) {
                                    return $action
                                        ->modalHeading('Nuevo Cliente')
                                        ->modalSubmitActionLabel('Guardar')
                                        ->icon('heroicon-m-plus');
                                })
                                ->required(),
                            // crear text area para observaciones
                            Forms\Components\Textarea::make('observaciones')
                                ->label('Observaciones')
                                ->placeholder('Escribe cualquier comentario adicional...')
                                ->rows(4)
                                ->columnSpanFull()
                                ->extraInputAttributes(['class' => 'resize-none']),
                        
        ];
    }

    public static function getOrdenStep(): array
    {
        return [
            Tabs::make('Detalles de Orden')
                ->tabs([

                    // TAB: PERFILES
                    Tabs\Tab::make('Perfiles')
                        ->schema([
                            Forms\Components\Repeater::make('perfiles_seleccionados')

                                                ->schema([
                                                    Forms\Components\Grid::make(2)
                                                        ->columnSpanFull()
                                                        ->schema([
                                                            Select::make('perfil_id')
                                                                ->label('Buscar Perfil')
                                                                 ->options(\App\Models\Perfil::where('estado', 1)->pluck('nombre', 'id')->toArray())
                                                                ->searchable()
                                                                ->preload()
                                                                ->reactive()
                                                                ->required()
                                                                ->validationMessages([
                                                                    'required' => 'Debe seleccionar un perfil.',
                                                                ])
                                                                ->placeholder('Selecciona un perfil')
                                                                ->afterStateHydrated(function ($state, Set $set) {
                                                                    if (is_numeric($state)) {
                                                                        $perfil = \App\Models\Perfil::find($state);
                                                                        if ($perfil instanceof \App\Models\Perfil) {
                                                                            $set('precio', $perfil->precio);
                                                                            $set('precio_hidden', $perfil?->precio ?? 0);
                                                                        }
                                                                    }
                                                                })
                                                                ->suffixAction(
                                                                    Action::make('crearPerfil')
                                                                        ->icon('heroicon-m-plus')
                                                                        ->tooltip('Agregar nuevo perfil')
                                                                        ->url(route('filament.admin.resources.perfils.create')) // ajusta el nombre de la resource si es necesario
                                                                        ->openUrlInNewTab() // o elimínalo si prefieres abrir en la misma pestaña
                                                                )
                                                                ->afterStateUpdated(function ($state, Set $set) {
                                                                    if ($state === null) {
                                                                        $set('precio', null);
                                                                    } else {
                                                                        $perfil = \App\Models\Perfil::find($state);
                                                                        $set('precio', $perfil?->precio ?? 0);
                                                                        $set('precio_hidden', $perfil?->precio ?? 0);
                                                                    }
                                                                }),

                                            Forms\Components\TextInput::make('precio')
                                                ->label('Precio')
                                                ->dehydrated(true)
                                                ->disabled(),

                                            Hidden::make('tipo')->default('perfil'),
                                            //Hidden para precio
                                            Hidden::make('precio_hidden')
                                                ->dehydrated(true),


                                        ])
                                    ,
                                ])
                                ->reorderable(false)
                                ->addActionLabel('Añadir Perfil a su Orden')
                                ->reorderableWithButtons(false)
                                ->default([])
                                ->reactive()
                                ->label('Resumen de Perfiles Seleccionados')
                            ,
                        ]),

                    // TAB: EXÁMENES
                    Tabs\Tab::make('Exámenes')
                        ->schema([
                            Forms\Components\Repeater::make('examenes_seleccionados')

                                                ->schema([
                                                    Forms\Components\Grid::make(2)
                                                        ->columnSpanFull()
                                                        ->schema([
                                                            Select::make('examen_id')
                                                                ->label('Buscar Examen')
                                                                ->options(\App\Models\Examen::pluck('nombre', 'id')->toArray())
                                                                ->searchable()
                                                                ->preload()
                                                                ->reactive()
                                                                ->required()
                                                                ->validationMessages([
                                                                    'required' => 'Debe seleccionar un examen.',
                                                                ])
                                                                ->placeholder('Selecciona un examen')
                                                                ->afterStateHydrated(function ($state, Set $set) {
                                                                    Log::info('Estado del examen después de hidratar:', ['state' => $state]);
                                                                    if (is_numeric($state)) {
                                                                        $examen = \App\Models\Examen::find($state);
                                                                        if ($examen instanceof \App\Models\Examen) {
                                                                            $set('precio', $examen->precio);
                                                                            $set('precio_hidden', $examen->precio ?? 0);
                                                                            $set('nombre_examen', $examen->nombre ?? '');
                                                                            $set('recipiente', $examen->recipiente ?? '');

                                                        }
                                                    }
                                                })
                                                ->afterStateUpdated(function ($state, Set $set) {
                                                    if ($state === null) {
                                                        $set('precio', null);
                                                    } else {
                                                        $examen = \App\Models\Examen::find($state);
                                                        $set('precio', $examen?->precio ?? 0);
                                                        $set('precio_hidden', $examen?->precio ?? 0);
                                                        $set('nombre_examen', $examen?->nombre ?? '');
                                                        $set('recipiente', $examen?->recipiente ?? '');
                                                    }

                                                })
                                            ,

                                            Forms\Components\TextInput::make('precio')
                                                ->label('Precio')
                                                ->dehydrated(true)
                                                ->disabled(),

                                            Hidden::make('tipo')->default('examen'),
                                            Hidden::make('precio_hidden'),
                                            Hidden::make('nombre_examen')
                                                ->dehydrated(true),
                                            Hidden::make('recipiente')
                                                ->dehydrated(true),

                                        ]),
                                ])
                                ->reorderable(false)
                                ->addActionLabel('Añadir Examen a su Orden')
                                ->reorderableWithButtons(false)
                                ->default([])
                                ->label('Resumen de Examenes Seleccionados')
                                ->reactive()
                            ,
                                    ]),
                    
                

                    


                ])
        ];
    }

    public static function getResumenStep(): array
    {
        return [
            Forms\Components\Placeholder::make('cliente_resumen')
                ->label('Cliente seleccionado')
                ->content(function (Get $get) {
                    $clienteId = $get('cliente_id');
                    $cliente = $clienteId ? Cliente::find($clienteId) : null;
                    return $cliente
                        ? "{$cliente->NumeroExp} - {$cliente->nombre} {$cliente->apellido}"
                        : 'No se ha seleccionado un cliente.';
                }),

            // Perfiles
            Forms\Components\Placeholder::make('perfiles_resumen')
                ->label('Perfiles seleccionados')
                ->content(function (Get $get) {
                    $perfiles = $get('perfiles_seleccionados') ?? [];
                    if (empty($perfiles))
                        return 'No se ha agregado ningún perfil.';

                    $resumen = [];
                    foreach ($perfiles as $item) {
                        $perfilId = $item['perfil_id'] ?? null;
                        $perfil = \App\Models\Perfil::find($perfilId);
                        if ($perfil) {
                            $resumen[] = "{$perfil->nombre} ($" . number_format($perfil->precio, 2) . ")";
                        }
                    }
                    return implode(', ', $resumen);
                }),

            // Exámenes
            Forms\Components\Placeholder::make('examenes_resumen')
                ->label('Exámenes seleccionados')
                ->content(function (Get $get) {
                    $examenes = $get('examenes_seleccionados') ?? [];
                    if (empty($examenes))
                        return 'No se ha agregado ningún examen.';

                    $resumen = [];
                    foreach ($examenes as $item) {
                        $examenId = $item['examen_id'] ?? null;
                        $examen = \App\Models\Examen::find($examenId);
                        if ($examen) {
                            $resumen[] = "{$examen->nombre} ($" . number_format($examen->precio, 2) . ")";
                        }
                    }
                    return implode(', ', $resumen);
                }),

            // Total

            Forms\Components\Placeholder::make('totalPagar')
                ->label('Total a pagar')
                ->content(function (Get $get) {
                    $total = 0;

                    foreach ($get('perfiles_seleccionados') ?? [] as $item) {
                        $total += floatval($item['precio'] ?? 0);
                    }

                    foreach ($get('examenes_seleccionados') ?? [] as $item) {
                        $total += floatval($item['precio'] ?? 0);
                    }

                    return '$' . number_format($total, 2);
                }),

        ];
    }



    public static function table(Table $table): Table
    {
        return $table
            ->contentGrid([
                'md' => 2,
                'lg' => 3,
                'xl' => 4,
            ])
            ->columns([
                Split::make([
                    Stack::make([
                        TextColumn::make('cliente.nombre')
                            ->label('Cliente')
                            ->getStateUsing(fn($record) => $record->cliente->nombre . ' ' . $record->cliente->apellido)
                            ->size('lg')
                            ->searchable()
                            ->weight('bold'),

                        TextColumn::make('total')
                            ->label('Total')
                            ->money('USD', true)
                            ->color('primary')
                            ->size('xl')
                            ->weight('bold'),

                        TextColumn::make('fecha')
                            ->label('Fecha')
                            ->date()
                            ->color('gray'),

                        TextColumn::make('cliente.NumeroExp')
                            ->label('Expediente')
                            ->searchable(),

                        TextColumn::make('estado')
                            ->label('Estado')
                            ->badge()
                            ->searchable()
                            ->color(fn($state) => match ($state) {
                                'pendiente' => 'warning',
                                'en proceso' => 'info',
                                'pausada' => 'danger',
                                'finalizado' => 'success',
                                'cancelado' => 'gray',
                                default => 'gray',
                            }),
                    ]),
                ])
            ])
            ->filters([
                // ... (tus filtros se quedan igual)
            ])
           ->actions([
Tables\Actions\Action::make('gestionarMuestras')
                    ->label('Gestionar Muestras')
                    ->tooltip('Registrar muestras recibidas')
                    ->icon('heroicon-o-beaker')
                    ->iconButton()
                    ->color('info')
                    ->visible(fn(Orden $record): bool => in_array($record->estado, ['pendiente']))
                    ->modalHeading('Registrar Muestras Recibidas')
                    ->modalSubmitActionLabel('Guardar Estado')
                    ->form(function (Orden $record) {
                        $detalles = $record->detalleOrden()->with('examen.muestras')->get();
                        
                        $opcionesMuestras = [];
                        $valoresPorDefecto = [];

                        foreach ($detalles as $detalle) {
                            if (!$detalle->examen || $detalle->examen->muestras->isEmpty()) continue;
                            
                            // Obtenemos las muestras ya recibidas para ESTE detalle
                            $recibidas = $detalle->muestras_recibidas ?? [];

                            foreach ($detalle->examen->muestras as $muestra) {
                                // Creamos un ID único (detalle_id + muestra_id)
                                $key = "d{$detalle->id}_m{$muestra->id}";
                                $label = "{$detalle->examen->nombre}: {$muestra->nombre}";
                                $opcionesMuestras[$key] = $label;

                                // Si la muestra está en el array, la marcamos
                                if (in_array($muestra->id, $recibidas)) {
                                    $valoresPorDefecto[] = $key;
                                }
                            }
                        }

                        if (empty($opcionesMuestras)) {
                            return [Forms\Components\Placeholder::make('no_muestras')->content('Este examen no tiene muestras asociadas.')];
                        }

                        return [
                            CheckboxList::make('muestras_recibidas_list')
                                ->label('Marcar muestras como recibidas')
                                ->options($opcionesMuestras)
                                ->default($valoresPorDefecto) // Carga el estado guardado
                                ->columns(1)
                                ->bulkToggleable(),
                        ];
                    })
                     ->action(function (Orden $record, array $data) {
                        $selectedKeys = $data['muestras_recibidas_list'] ?? [];
                        $detalles = $record->detalleOrden()->with('examen.muestras')->get();
                        
                        $totalMuestrasRequeridas = 0;
                        $totalMuestrasRecibidas = 0;

                        DB::transaction(function () use ($detalles, $selectedKeys, &$totalMuestrasRequeridas, &$totalMuestrasRecibidas) {
                            foreach ($detalles as $detalle) {
                                $muestrasDeEsteDetalle = [];
                                if ($detalle->examen->muestras->isEmpty()) continue;

                                foreach ($detalle->examen->muestras as $muestra) {
                                    $totalMuestrasRequeridas++;
                                    $key = "d{$detalle->id}_m{$muestra->id}";
                                    if (in_array($key, $selectedKeys)) {
                                        $muestrasDeEsteDetalle[] = $muestra->id;
                                        $totalMuestrasRecibidas++;
                                    }
                                }
                                $detalle->muestras_recibidas = $muestrasDeEsteDetalle;
                                $detalle->save();
                            }
                        });

                        // --- LÓGICA DE AUDITORÍA Y ESTADO ---
                        if ($totalMuestrasRequeridas > 0 && $totalMuestrasRequeridas === $totalMuestrasRecibidas) {
                            $record->estado = 'en proceso';
                            $record->fecha_toma_muestra = Carbon::now(); // <-- GUARDAR FECHA
                            $record->toma_muestra_user_id = auth()->id(); // <-- GUARDAR USUARIO
                            Notification::make()->title('¡Todas las muestras recibidas!')->body('La orden está lista para procesar.')->success()->send();
                        } else {
                            $record->estado = 'pendiente'; 
                            $record->fecha_toma_muestra = null; // <-- LIMPIAR FECHA
                            $record->toma_muestra_user_id = null; // <-- LIMPIAR USUARIO
                            $notificacion = ($totalMuestrasRecibidas > 0)
                                ? Notification::make()->title('Muestras guardadas')->body('Aún faltan muestras por recibir. La orden sigue pendiente.')->info()
                                : Notification::make()->title('Muestras guardadas')->body('No se ha recibido ninguna muestra.')->warning();
                            $notificacion->send();
                        }
                        $record->save();
                    }),

               

   Tables\Actions\Action::make('ingresarResultados')
                    ->tooltip('Ingresar Resultados')
                    ->icon('heroicon-o-pencil-square')
                    ->iconButton()
                    ->color('primary')
                    ->visible(fn(Orden $record): bool => $record->estado === 'en proceso') // <-- ¡LÓGICA CLAVE!
                    ->url(fn(Orden $record): string => static::getUrl('ingresar-resultados', ['record' => $record])),
                
    Tables\Actions\Action::make('imprimirEtiquetas')
                    ->tooltip('Imprimir Etiquetas')
                    ->icon('heroicon-o-tag')
                    ->iconButton()
                    ->color('gray')
                    // Visible si la orden no está finalizada o cancelada
                    ->visible(fn(Orden $record): bool => !in_array($record->estado, ['finalizado', 'cancelado'])) 
                    ->url(fn(Orden $record): string => DetalleOrdenKanban::getUrl(['ordenId' => $record->id])),

    Tables\Actions\Action::make('ver')
        ->tooltip('Ver Detalles')
        ->icon('heroicon-o-eye')
        ->iconButton()
        ->color('gray')
        ->modalContent(function (Orden $record) {
            // Corregido para evitar error de memoria
            $record->load(['detalleOrden.examen.pruebas', 'resultados']);
            return view('filament.modals.ver-orden', ['record' => $record]);
        })
        ->modalSubmitAction(false)
        ->modalCancelAction(false),

    Tables\Actions\Action::make('verPruebas')
        ->tooltip('Ver Pruebas Realizadas')
        ->icon('heroicon-o-clipboard-document-check')
        ->iconButton()
        ->color('gray')
        ->visible(fn(Orden $record): bool => in_array($record->estado, ['en proceso', 'pausada', 'finalizado']))
        ->modalHeading('Pruebas a Realizar')
        ->modalSubmitAction(false)
        ->modalCancelActionLabel('Cerrar')
        ->form(function (Orden $record) {
            $detalles = $record->detalleOrden()->with('examen.pruebas')->get();
            $examenes = $detalles->map(fn ($detalle) => $detalle->examen)->filter()->unique('id');
            return [Forms\Components\View::make('filament.modals.ver-orden-pruebas')->viewData(['examenes' => $examenes])];
        }),

    Tables\Actions\Action::make('pausarOrden')
        ->tooltip('Pausar Orden')
        ->icon('heroicon-o-pause-circle')
        ->iconButton()
        ->color('warning')
        ->visible(fn(Orden $record): bool => $record->estado === 'en proceso')
        ->requiresConfirmation()
        ->form([ Textarea::make('motivo_pausa')->label('Motivo de la Pausa')->required() ])
        ->action(function (Orden $record, array $data) {
            $record->estado = 'pausada';
            $record->motivo_pausa = $data['motivo_pausa'];
            $record->save();
            Notification::make()->title('Orden Pausada')->warning()->send();
        }),

    Tables\Actions\Action::make('reanudarOrden')
        ->tooltip('Reanudar Orden')
        ->icon('heroicon-o-play-circle')
        ->iconButton()
        ->color('success')
        ->visible(fn(Orden $record): bool => $record->estado === 'pausada')
        ->requiresConfirmation()
        ->action(function (Orden $record) {
            $record->estado = 'en proceso';
            $record->motivo_pausa = null;
            $record->save();
            Notification::make()->title('Orden Reanudada')->success()->send();
        }),
    
    Tables\Actions\Action::make('finalizarOrden')
        ->tooltip('Finalizar Orden')
        ->icon('heroicon-o-check-circle')
        ->iconButton()
        ->color('success')
        ->visible(fn(Orden $record): bool => $record->estado === 'en proceso')
        ->requiresConfirmation()
        ->action(function (Orden $record) {
            $record->estado = 'finalizado';
            $record->save();
            Notification::make()->title('Orden Finalizada con Éxito')->success()->send();
        }),

      Tables\Actions\Action::make('generarReporte')
                    ->tooltip('Generar Reporte PDF')
                    ->icon('heroicon-o-printer')
                    ->iconButton()
                    ->color('gray')
                    ->visible(fn(Orden $record): bool => $record->estado === 'finalizado')
                    // --- ¡LÓGICA DE PREPARACIÓN DE DATOS REESCRITA! ---
                    ->action(function (Orden $record) {
                        $orden = $record->load([
                            'cliente', 
                            'detalleOrden.examen.tipoExamen',
                            'detalleOrden.examen.pruebas.reactivoEnUso.valoresReferencia.grupoEtario', 
                            'resultados.prueba'
                        ]);

                        $detallesAgrupados = $orden->detalleOrden
                            ->whereNotNull('examen_id')
                            ->groupBy('examen.tipoExamen.nombre');

                        $datos_agrupados = [];
                        foreach ($detallesAgrupados as $tipoExamenNombre => $detalles) {
                            
                            $examenes_data = [];
                            foreach ($detalles as $detalle) {
                                $todasLasPruebas = $detalle->examen->pruebas;

                                // 1. Separar pruebas unitarias de las conjuntas
                                $pruebasUnitarias = $todasLasPruebas->whereNull('tipo_conjunto');
                                $pruebasConjuntas = $todasLasPruebas->whereNotNull('tipo_conjunto')->groupBy('tipo_conjunto');

                                // 2. Procesar pruebas unitarias
                                $dataUnitarias = $pruebasUnitarias->map(function ($prueba) use ($orden, $detalle) {
                                    return self::getDatosPruebaParaPdf($prueba, $orden, $detalle->id);
                                })->all();

                                // 3. Procesar matrices
                                $dataMatrices = $pruebasConjuntas->map(function (Collection $pruebasDelConjunto) use ($orden, $detalle) {
                                    $filas = []; $columnas = []; $dataMatrix = [];
                                    foreach ($pruebasDelConjunto as $prueba) {
                                        $partes = explode(', ', $prueba->nombre);
                                        if (count($partes) >= 2) {
                                            [$nombreFila, $nombreColumna] = $partes;
                                            $filas[] = $nombreFila; $columnas[] = $nombreColumna;
                                            $dataMatrix[$nombreFila][$nombreColumna] = self::getDatosPruebaParaPdf($prueba, $orden, $detalle->id);
                                        }
                                    }
                                    return [
                                        'filas' => array_values(array_unique($filas)),
                                        'columnas' => array_values(array_unique($columnas)),
                                        'data' => $dataMatrix,
                                    ];
                                })->all();

                                $examenes_data[] = [
                                    'nombre' => $detalle->examen->nombre,
                                    'codigo' => $detalle->examen->id,
                                    'pruebas_unitarias' => $dataUnitarias,
                                    'matrices' => $dataMatrices,
                                ];
                            }
                            $datos_agrupados[$tipoExamenNombre ?: 'Exámenes Generales'] = $examenes_data;
                        }

                        $pdf = Pdf::loadView('pdf.reporte_resultados', [
                            'orden' => $orden,
                            'datos_agrupados' => $datos_agrupados,
                        ]);

                        return response()->streamDownload(
                            fn () => print($pdf->output()),
                            "Resultados-{$orden->cliente->nombre}-{$orden->id}.pdf"
                        );
                    }),
    Tables\Actions\Action::make('cancelarOrden')
        ->tooltip('Cancelar Orden')
        ->icon('heroicon-o-x-circle')
        ->iconButton()
        ->color('danger')
        ->visible(fn(Orden $record): bool => in_array($record->estado, ['pendiente', 'en proceso', 'pausada']))
        ->requiresConfirmation()
        ->action(function (Orden $record) {
            $record->estado = 'cancelado';
            $record->save();
            Notification::make()->title('Orden Cancelada')->danger()->send();
        }),
    ]);
    }
    public static function getRelations(): array
    {
        return [];
    }
     public static function getDatosPruebaParaPdf($prueba, $orden, $detalleId): array
    {
        $resultado = $orden->resultados->where('prueba_id', $prueba->id)->where('detalle_orden_id', $detalleId)->first();
        
        $referencia_formateada = 'N/A';
        $unidades = '';
        $es_fuera_de_rango = false; // <-- NUEVA BANDERA
        $valor_resultado_num = null;

        // Intentar convertir el resultado a número para comparar
        if ($resultado && is_numeric($resultado->resultado)) {
            $valor_resultado_num = (float) $resultado->resultado;
        }

        if ($prueba->reactivoEnUso && $prueba->reactivoEnUso->valoresReferencia->isNotEmpty()) {
            // Se mantiene la lógica de tomar el primero
            $valorRef = $prueba->reactivoEnUso->valoresReferencia->first(); 
            
            $valorMin = (float) $valorRef->valor_min;
            $valorMax = (float) $valorRef->valor_max;
            $unidades = $valorRef->unidades ?? '';

            // Formatear el texto de referencia
            $rangoTexto = match ($valorRef->operador) {
                'rango' => "{$valorMin} - {$valorMax}",
                '<=' => "≤ {$valorMax}",
                '<' => "< {$valorMax}",
                '>=' => "≥ {$valorMin}",
                '>' => "> {$valorMin}",
                '=' => "= {$valorMin}",
                default => $valorRef->descriptivo ?? '',
            };
            $referencia_formateada = $rangoTexto; // (Simplificado)

            // --- NUEVA LÓGICA DE COMPARACIÓN ---
            if (!is_null($valor_resultado_num)) {
                switch ($valorRef->operador) {
                    case 'rango':
                        if ($valor_resultado_num < $valorMin || $valor_resultado_num > $valorMax) $es_fuera_de_rango = true;
                        break;
                    case '<=':
                        if ($valor_resultado_num > $valorMax) $es_fuera_de_rango = true;
                        break;
                    case '<':
                        if ($valor_resultado_num >= $valorMax) $es_fuera_de_rango = true;
                        break;
                    case '>=':
                        if ($valor_resultado_num < $valorMin) $es_fuera_de_rango = true;
                        break;
                    case '>':
                        if ($valor_resultado_num <= $valorMin) $es_fuera_de_rango = true;
                        break;
                    case '=':
                         if ($valor_resultado_num != $valorMin) $es_fuera_de_rango = true;
                        break;
                }
            }
        }

        return [
            'nombre' => $prueba->nombre,
            'resultado' => $resultado->resultado ?? 'PENDIENTE',
            'referencia' => $referencia_formateada,
            'unidades' => $unidades,
            'fecha_resultado' => $resultado ? $resultado->updated_at->format('d/m/Y') : '',
            'es_fuera_de_rango' => $es_fuera_de_rango, // <-- DEVOLVEMOS LA BANDERA
        ];
    }
    public static function getRecordUrlUsing(): Closure
    {
        return fn($record) => null; // 👈 esto desactiva el enlace de clic en la tarjeta
    }


    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOrdens::route('/'),
            'create' => Pages\CreateOrden::route('/create'),
            //  'edit' => Pages\EditOrden::route('/{record}/edit'),
            'ingresar-resultados' => Pages\IngresarResultados::route('/{record}/ingresar-resultados'), // <-- AÑADE ESTA LÍNEA


        ];
    }


}
