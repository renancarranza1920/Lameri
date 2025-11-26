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
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ViewField;
use Filament\Forms\Components\Wizard;
use Barryvdh\DomPDF\Facade\Pdf;
use Filament\Tables\Filters\Filter;
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
use Filament\Forms\Components\Actions\Action as FormAction; // <-- Importante alias
use Filament\Pages\Page;
use Number;




class OrdenResource extends Resource
{
    protected static ?string $model = Orden::class;

    protected static ?string $navigationGroup = 'Atención al Paciente';
    protected static ?int $navigationSort = 2;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-check';
    protected static ?string $navigationLabel = 'Órdenes';
    protected static ?string $slug = 'ordenes';
    protected static ?string $modelLabel = 'Orden';
    protected static ?string $pluralModelLabel = 'Órdenes';

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
                
                // --- INICIO LÓGICA EMBARAZO ---
                ->live() // 1. Escuchar cambios en tiempo real
                ->afterStateUpdated(function ($state, Set $set) {
                    // 2. Buscar el género del cliente seleccionado
                    if ($state) {
                        $cliente = Cliente::find($state);
                        $set('genero_temp', $cliente?->genero);
                    } else {
                        $set('genero_temp', null);
                    }
                })
                // ------------------------------

                ->createOptionForm([
                    Forms\Components\TextInput::make('nombre')->required()->maxLength(255),
                    Forms\Components\TextInput::make('apellido')->required()->maxLength(255),
                    Forms\Components\DatePicker::make('fecha_nacimiento')->label('Fecha de Nacimiento')->required(),
                    Forms\Components\TextInput::make('telefono')->maxLength(9),
                    Forms\Components\TextInput::make('correo')->email()->maxLength(255),
                    Forms\Components\TextInput::make('direccion')->maxLength(255),
                ])
                ->createOptionAction(function (Forms\Components\Actions\Action $action) {
                    return $action
                        ->modalHeading('Nuevo Cliente')
                        ->modalSubmitActionLabel('Guardar')
                        ->icon('heroicon-m-plus');
                })
                ->required(),

            // --- CAMPOS PARA EMBARAZO ---
            
            // Variable temporal para controlar la visibilidad (no se guarda en BD)
            Forms\Components\Hidden::make('genero_temp')
                ->dehydrated(false), 

            Forms\Components\TextInput::make('semanas_gestacion')
                ->label('Semanas de Gestación')
                ->numeric()
                ->minValue(1)
                ->maxValue(42)
                ->placeholder('Ej: 12')
                ->helperText('Ingresa las semanas solo si aplica (embarazo).')
                // Solo visible si el cliente es mujer
                ->visible(fn (Get $get) => $get('genero_temp') === 'Femenino')
                ->columnSpanFull(),
            
            // -----------------------------

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
                                                ->options(\App\Models\Examen::where('estado', 1)
                                                ->pluck('nombre', 'id')->toArray())
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







                ]),


Forms\Components\Hidden::make('subtotal')->default(0)->reactive(),
Forms\Components\Hidden::make('descuento')->default(0)->reactive(),
Forms\Components\Hidden::make('codigo_aplicado'),




        ];
    }


    public static function getCuponSection(): Section
    {
        return Section::make('Código de Descuento')
            ->schema([
                TextInput::make('codigo_input')
                    ->label('Cupón')
                    ->placeholder('INGRESA UN CUPÓN')
                    ->reactive()
                    // Llama al método en CreateOrden
                    ->afterStateUpdated(fn(Page $livewire) => $livewire->limpiarDescuento())
                    ->suffixAction(
                        FormAction::make('aplicarCodigo')
                            ->icon('heroicon-o-check-circle')
                            ->color('success')
                            ->label('Aplicar')
                          ->action(function ($livewire) {
                                    $livewire->aplicarCodigo();
                                })
                                // Usamos $livewire para acceder a la propiedad pública
                                ->visible(fn ($livewire) => is_null($livewire->codigoAplicado)), 

                    ),
                // Placeholder para mostrar si el cupón está aplicado o el descuento
                Forms\Components\Placeholder::make('descuento_display')
                    ->content(function (Page $livewire) {
                        if ($livewire->codigoAplicado) {
                            $total = $livewire->subtotal - $livewire->descuento;
                            return new \Illuminate\Support\HtmlString(
                                "<div class='text-sm text-green-600 font-bold'>Cupón {$livewire->codigoAplicado->codigo} aplicado. Descuento: " . Number::currency($livewire->descuento, 'USD') . "</div>"
                            );
                        }
                        return 'Aún no se aplica ningún cupón.';
                    })
                    ->visible(fn(Page $livewire) => $livewire->subtotal > 0), // Solo mostrar si hay algo que comprar
            ])
            ->collapsible()
            // El subtotal se calcula en el paso anterior y lo usamos aquí para decidir la visibilidad.
            ->visible(
                fn(Get $get) =>
                count($get('perfiles_seleccionados') ?? []) > 0 ||
                count($get('examenes_seleccionados') ?? []) > 0
            );
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
                        TextColumn::make('id')
                            ->label('Orden #')
                            ->formatStateUsing(fn ($state) => "Orden #{$state}") // Formato visual "Orden #123"
                            ->weight('bold')
                            ->color('primary')
                            ->searchable() // <--- ¡ESTO PERMITE BUSCAR POR ID!
                            ->sortable(),
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
                                'pausada' => 'warning',
                                'finalizado' => 'success',
                                'cancelado' => 'danger',
                                default => 'gray',
                            }),
                    ]),
                ])
            ])
            ->filters([
                //filtro por fecha
                Tables\Filters\Filter::make('fecha_rango')
                    ->form([
                        Forms\Components\DatePicker::make('fecha_desde')
                            ->label('Fecha Desde'),
                        Forms\Components\DatePicker::make('fecha_hasta')
                            ->label('Fecha Hasta'),
                    ])->columns(2)
                    ->query(function (Builder $query, array $data) {
                        return $query
                            ->when($data['fecha_desde'], fn(Builder $query, $date) => $query->whereDate('fecha', '>=', $date))
                            ->when($data['fecha_hasta'], fn(Builder $query, $date) => $query->whereDate('fecha', '<=', $date));
                    }),
                
                Filter::make('fecha_unica')
                    ->label('Filtrar por Fecha')
                    ->form([
                        DatePicker::make('fecha_unica')
                            ->label('Seleccionar Fecha')

                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['fecha_unica'], // Si el usuario llenó la fecha
                                // Aplica un filtro exacto para ESE día
                                fn(Builder $query, $date): Builder => $query->whereDate('fecha', $date),
                            );
                    })
            ])
           ->filtersLayout(\Filament\Tables\Enums\FiltersLayout::AboveContentCollapsible)
        
        ->filtersFormColumns(2)
            ->actions([
                Tables\Actions\Action::make('gestionarMuestras')
                    ->label('Gestionar Muestras')
                    ->tooltip('Registrar muestras recibidas')
                    ->icon('heroicon-o-beaker')
                    ->iconButton()
                    ->color('info')
                    ->visible(fn(Orden $record): bool => in_array($record->estado, ['pendiente'])
                    && auth()->user()->can('procesar_muestras_orden'))
                    ->modalHeading('Registrar Muestras Recibidas')
                    ->modalSubmitActionLabel('Guardar Estado')
                    ->form(function (Orden $record) {
                        $detalles = $record->detalleOrden()->with('examen.muestras')->get();

                        $opcionesMuestras = [];
                        $valoresPorDefecto = [];

                        foreach ($detalles as $detalle) {
                            if (!$detalle->examen || $detalle->examen->muestras->isEmpty())
                                continue;

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
                                if ($detalle->examen->muestras->isEmpty())
                                    continue;

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
                    ->icon('heroicon-o-document-plus')
                    ->iconButton()
                    ->color('primary')
                    ->visible(fn(Orden $record): bool => $record->estado === 'en proceso'
                    && auth()->user()->can('ingresar_resultados_orden'))
                    ->url(fn(Orden $record): string => static::getUrl('ingresar-resultados', ['record' => $record])),

                Tables\Actions\Action::make('imprimirEtiquetas')
                    ->tooltip('Imprimir Etiquetas')
                    ->icon('heroicon-o-tag')
                    ->iconButton()
                    ->color('gray')
                    // Visible si la orden no está finalizada o cancelada
                    ->visible(fn(Orden $record): bool => in_array($record->estado, ['pendiente'])
                    && auth()->user()->can('imprimir_etiquetas_orden'))
                    ->url(fn(Orden $record): string => DetalleOrdenKanban::getUrl(['ordenId' => $record->id])),

                Tables\Actions\Action::make('ver')
                    ->tooltip('Ver Detalles')
                    ->icon('heroicon-o-eye')
                    ->iconButton()
                    ->color('gray')
                    ->modalContent(function (Orden $record) {
                        // Corregido para evitar error de memoria
                        $record->load([
                            'cliente',
                            'detalleOrden.examen.muestras',
                            'detalleOrden.perfil',
                            'resultados.prueba',
                            'tomaMuestraUser'
                        ]);
                        return view('filament.modals.ver-orden', ['record' => $record]);
                    })
                    ->modalSubmitAction(false)
                    ->modalCancelAction(false),

                Tables\Actions\Action::make('verPruebas')
                    ->tooltip('Ver Pruebas Realizadas')
                    ->icon('heroicon-o-clipboard-document-check')
                    ->iconButton()
                    ->color('gray')
                    ->visible(fn(Orden $record): bool => in_array($record->estado, ['en proceso', 'pausada', 'finalizado'])
                    && auth()->user()->can('ver_pruebas_orden'))
                    ->modalHeading('Pruebas a Realizar')
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Cerrar')
                    ->form(function (Orden $record) {
                        // 1. Cargamos resultados para no hacer consultas en el loop
                        $record->load('resultados'); 

                        $detalles = $record->detalleOrden()->with('examen.pruebas')->get();
                        $examenes = $detalles->map(fn($detalle) => $detalle->examen)->filter()->unique('id');

                        return [
                            Forms\Components\View::make('filament.modals.ver-orden-pruebas')
                                ->viewData([
                                    'examenes' => $examenes,
                                    'orden' => $record, // <--- ¡ESTO ES LO NUEVO! Pasamos la orden completa
                                ])
                        ];
                    }),
                Tables\Actions\Action::make('pausarOrden')
                    ->tooltip('Pausar Orden')
                    ->icon('heroicon-o-pause-circle')
                    ->iconButton()
                    ->color('warning')
                    ->visible(fn(Orden $record): bool => $record->estado === 'en proceso' &&
                        auth()->user()->can('pausar_orden'))
                    ->requiresConfirmation()
                    ->form([Textarea::make('motivo_pausa')->label('Motivo de la Pausa')->required()])
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
                    ->visible(fn(Orden $record): bool => $record->estado === 'pausada' &&
                        auth()->user()->can('reanudar_orden'))
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
                    ->visible(fn(Orden $record): bool => $record->estado === 'en proceso' && auth()->user()->can('finalizar_orden'))
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
    ->visible(fn(Orden $record): bool => $record->estado === 'finalizado' &&
                        auth()->user()->can('generar_reporte_orden'))
    ->action(function (Orden $record) {
        // 1. Cargar relaciones necesarias
        $orden = $record->load([
            'cliente',
            'detalleOrden.examen.tipoExamen',
            'detalleOrden.examen.pruebas.tipoPrueba',
            'detalleOrden.examen.pruebas.reactivoEnUso.valoresReferencia.grupoEtario',
            'resultados.prueba'
        ]);

        // 2. Agrupar por tipo de examen (Hematología, Química, etc.)
        $detallesAgrupados = $orden->detalleOrden
            ->whereNotNull('examen_id')
            ->groupBy('examen.tipoExamen.nombre');

        $datos_agrupados = [];

        foreach ($detallesAgrupados as $tipoExamenNombre => $detalles) {
            $examenes_data = [];

            foreach ($detalles as $detalle) {
                
                // --- LÓGICA DIFERENCIADA: EXTERNO vs INTERNO ---
                
                if ($detalle->examen->es_externo) {
                    // CASO A: EXAMEN EXTERNO (REFERIDO)
                    // Buscamos directamente en la tabla 'resultados' los datos guardados manualmente (snapshots)
                    $resultadosExternos = $orden->resultados
                        ->where('detalle_orden_id', $detalle->id)
                        ->where('es_externo', true);

                    $dataUnitarias = $resultadosExternos->map(function ($res) {
                        return [
                            'nombre' => $res->prueba_nombre_snapshot ?? 'Prueba Externa',
                            'resultado' => $res->resultado,
                            'referencia' => $res->valor_referencia_snapshot ?? 'N/A',
                            'unidades' => $res->unidades_snapshot ?? '',
                            'fecha_resultado' => $res->updated_at->format('d/m/Y'),
                            'es_fuera_de_rango' => false, // No calculamos rangos en externos
                        ];
                    })->all();

                    // Agregamos al reporte como un examen simple (sin matrices)
                    $examenes_data[] = [
                        'nombre' => $detalle->examen->nombre ,
                        'codigo' => $detalle->examen->id,
                        'pruebas_unitarias' => $dataUnitarias,
                        'matrices' => [], // Los externos no suelen usar matrices complejas
                    ];

                } else {
                    // CASO B: EXAMEN INTERNO (CATÁLOGO)
                    // Usamos la definición de 'pruebas' y calculamos rangos con la función auxiliar
                    $todasLasPruebas = $detalle->examen->pruebas->where('es_externo', false);

                    $pruebasUnitarias = $todasLasPruebas->whereNull('tipo_conjunto');
                    $pruebasConjuntas = $todasLasPruebas->whereNotNull('tipo_conjunto')->groupBy('tipo_conjunto');

                    // Procesar unitarias internas
                    $dataUnitarias = $pruebasUnitarias->map(function ($prueba) use ($orden, $detalle) {
                        return self::getDatosPruebaParaPdf($prueba, $orden, $detalle->id);
                    })->all();

                    // Procesar matrices internas
                    $dataMatrices = $pruebasConjuntas->map(function (Collection $pruebasDelConjunto) use ($orden, $detalle) {
                        $filas = [];
                        $columnas = [];
                        $dataMatrix = [];
                        foreach ($pruebasDelConjunto as $prueba) {
                            $partes = explode(', ', $prueba->nombre);
                            if (count($partes) >= 2) {
                                [$nombreFila, $nombreColumna] = $partes;
                                $filas[] = $nombreFila;
                                $columnas[] = $nombreColumna;
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
            }
            $datos_agrupados[$tipoExamenNombre ?: 'Exámenes Generales'] = $examenes_data;
        }

        // 3. Datos de Firma y Sello
        $usuarioQueFirma = auth()->user();
        $rutaFirma = $usuarioQueFirma?->firma_path ?? null;
        $rutaSello = $usuarioQueFirma?->sello_path ?? null;

        // 4. Preparar PDF
        $pdf_data = [
            'orden' => $orden,
            'datos_agrupados' => $datos_agrupados,
            'ruta_firma_digital' => $rutaFirma,
            'ruta_sello_digital' => $rutaSello,
            'nombre_licenciado' => $usuarioQueFirma?->name ?? 'Licenciado Desconocido',
            'ruta_sello_registro' => public_path('storage/sello.png'),
        ];

        $pdf = Pdf::loadView('pdf.reporte_resultados', $pdf_data);

        return response()->streamDownload(
            fn() => print ($pdf->output()),
            "Resultados-{$orden->cliente->nombre}-{$orden->id}.pdf"
        );
    }),
                Tables\Actions\Action::make('cancelarOrden')
                    ->tooltip('Cancelar Orden')
                    ->icon('heroicon-o-x-circle')
                    ->iconButton()
                    ->color('danger')
                    ->visible(fn(Orden $record): bool => in_array($record->estado, ['pendiente', 'en proceso', 'pausada'])
                    &&
                        auth()->user()->can('cancelar_orden'))
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

        $nombre_prueba = $prueba->nombre; // Nombre por defecto
        $referencia_formateada = 'N/A';
        $unidades = '';
        $es_fuera_de_rango = false;
        $valor_resultado_num = null;

        if ($resultado && is_numeric($resultado->resultado)) {
            $valor_resultado_num = (float) $resultado->resultado;
        }

        // --- INICIO DE LA LÓGICA DE REFERENCIA CORREGIDA ---
        if ($prueba->reactivoEnUso && $prueba->reactivoEnUso->valoresReferencia->isNotEmpty()) {

            // 1. OBTENER DATOS DEL PACIENTE
            $cliente = $orden->cliente;
            $generoCliente = $cliente->genero; // "Masculino" o "Femenino"
            $grupoEtarioCliente = $cliente->getGrupoEtario(); // Objeto GrupoEtario o null

            $valorRef = null;
            $todosLosValores = $prueba->reactivoEnUso->valoresReferencia;

            if ($grupoEtarioCliente) {
                // 2. INTENTO DE BÚSQUEDA 1: Grupo Etario + Género Específico
                // Ej: "Adultos" (ID: 8) + "Masculino"

                // AGREGAR ESTO TEMPORALMENTE PARA PROBAR
               
                $valorRef = $todosLosValores
                    ->where('grupo_etario_id', $grupoEtarioCliente->id)
                    ->where('genero', $generoCliente)
                    ->first();

                // 3. INTENTO DE BÚSQUEDA 2 (FALLBACK): Grupo Etario + "Ambos"
                // Ej: "Adultos" (ID: 8) + "Ambos"
                if (!$valorRef) {
                    $valorRef = $todosLosValores
                        ->where('grupo_etario_id', $grupoEtarioCliente->id)
                        ->where('genero', 'Ambos')
                        ->first();
                }
            }

            // 4. INTENTO DE BÚSQUEDA 3 (FALLBACK): Sin Grupo Etario + Género Específico
            // (Para valores que no dependen de la edad, solo del género)
            if (!$valorRef) {
                $valorRef = $todosLosValores
                    ->whereNull('grupo_etario_id')
                    ->where('genero', $generoCliente)
                    ->first();
            }

            // 5. INTENTO DE BÚSQUEDA 4 (FALLBACK): Sin Grupo Etario + "Ambos"
            // (El valor más genérico, ej: 0-100 U/L para todos)
            if (!$valorRef) {
                $valorRef = $todosLosValores
                    ->whereNull('grupo_etario_id')
                    ->where('genero', 'Ambos')
                    ->first();
            }

            // 6. ÚLTIMO RECURSO: Si todo falla, toma el primero (evita crasheo)
            if (!$valorRef) {
                $valorRef = $todosLosValores->first();
            }

            // --- FIN DE LA LÓGICA DE BÚSQUEDA ---

            // Ahora $valorRef es el correcto (o el mejor disponible)
            if ($resultado && !empty($resultado->prueba_nombre_snapshot)) {

                $nombre_prueba = $resultado->prueba_nombre_snapshot;
                $referencia_formateada = $resultado->valor_referencia_snapshot ?? 'N/A';
                $unidades = $resultado->unidades_snapshot ?? '';

                // Intentar extraer valores numéricos del snapshot para la comparación
                // Esto asume un formato simple como "1.0 - 5.0"
                if (preg_match('/([\d\.]+)\s*-\s*([\d\.]+)/', $referencia_formateada, $matches)) {
                    $valorMin = (float) $matches[1];
                    $valorMax = (float) $matches[2];
                    if (!is_null($valor_resultado_num)) {
                        if ($valor_resultado_num < $valorMin || $valor_resultado_num > $valorMax) {
                            $es_fuera_de_rango = true;
                        }
                    }
                }
                // (Puedes añadir más 'preg_match' para operadores como '<', '≥', etc.)

            }
            // CASO 2: Es una orden antigua sin "foto", usamos los datos en vivo
            elseif ($prueba->reactivoEnUso && $prueba->reactivoEnUso->valoresReferencia->isNotEmpty()) {

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
                $referencia_formateada = $rangoTexto;

                // --- NUEVA LÓGICA DE COMPARACIÓN ---
                if (!is_null($valor_resultado_num)) {
                    switch ($valorRef->operador) {
                        case 'rango':
                            if ($valor_resultado_num < $valorMin || $valor_resultado_num > $valorMax)
                                $es_fuera_de_rango = true;
                            break;
                        case '<=':
                            if ($valor_resultado_num > $valorMax)
                                $es_fuera_de_rango = true;
                            break;
                        case '<':
                            if ($valor_resultado_num >= $valorMax)
                                $es_fuera_de_rango = true;
                            break;
                        case '>=':
                            if ($valor_resultado_num < $valorMin)
                                $es_fuera_de_rango = true;
                            break;
                        case '>':
                            if ($valor_resultado_num <= $valorMin)
                                $es_fuera_de_rango = true;
                            break;
                        case '=':
                            if ($valor_resultado_num != $valorMin)
                                $es_fuera_de_rango = true;
                            break;
                    }
                }
            }
        }

        return [
            'nombre' => $nombre_prueba, // <-- Usa el nombre de la "foto" o el nombre en vivo
            'resultado' => $resultado->resultado ?? 'PENDIENTE',
            'referencia' => $referencia_formateada, // <-- Usa la referencia de la "foto" o la de en vivo
            'unidades' => $unidades, // <-- Usa las unidades de la "foto" o las de en vivo
            'fecha_resultado' => $resultado ? $resultado->updated_at->format('d/m/Y') : '',
            'es_fuera_de_rango' => $es_fuera_de_rango, // <-- Devuelve la bandera
            'tipo_prueba' => $prueba->tipoPrueba->nombre ?? '', 
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
