<?php



namespace App\Filament\Resources;





use Filament\Forms\Components\Toggle;



use Illuminate\Support\Str;



use App\Models\User;

use App\Models\TipoPrueba;





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

use Filament\Notifications\Actions\Action as NotificationAction; // Importante para no confundir con la Action de la tabla



use Closure;

use Filament\Forms\Components\Actions\Action as FormAction; // <-- Importante alias

use Filament\Pages\Page;

use Number;

use Illuminate\Support\Facades\Storage;









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

    Forms\Components\Section::make('Datos Personales')

        ->schema([

            Forms\Components\Grid::make(2)

                ->schema([

                    Forms\Components\TextInput::make('nombre')

                        ->label('Nombre')

                        ->required()

                        ->maxLength(255),



                    Forms\Components\TextInput::make('apellido')

                        ->label('Apellido')

                        ->required()

                        ->maxLength(255),



                    // Campo Edad

                    Forms\Components\TextInput::make('edad')

                        ->label('Edad (Años)')

                        ->numeric()

                        ->integer()

                        ->minValue(1)

                        ->maxValue(120)

                        ->placeholder('Ej: 25')

                        ->helperText('Ingresar solo si desconoce la Fecha de Nacimiento.')

                        ->columnSpan(1),



                    // Campo Fecha de Nacimiento con lógica reactiva

                    Forms\Components\DatePicker::make('fecha_nacimiento')

                        ->label('Fecha de Nacimiento')

                        ->nullable()

                        ->placeholder('dd/mm/aaaa')

                        ->maxDate(now())

                        ->reactive()

                        ->helperText('Si seleccionas fecha, se limpia el grupo etario.')

                        ->afterStateUpdated(function ($state, Set $set) {

                            if ($state) {

                                $set('grupo_etario', null);

                            }

                        }),



                    // Campo Grupo Etario CORREGIDO (Muestra todos)

                    Forms\Components\Select::make('grupo_etario')

                                        ->label('Grupo Etario')

                                        ->options(function () {

                                            // Filtramos los grupos etarios que queremos mostrar (los que nos mencionaste)

                                            return \App\Models\GrupoEtario::whereIn('nombre', [

                                                    'Neonatos',

                                                    'Lactantes',

                                                    'Niños',

                                                    'Adolescentes',

                                                    'Adultos',

                                                    'Adultos mayores'

                                                ])

                                                ->pluck('nombre', 'id')  // Pluck el nombre para mostrar y el id para guardar

                                                ->toArray();

                                        })

                                        ->nullable()

                                        ->reactive() // Permite que los cambios actualicen el estado

                                        ->helperText('Si seleccionas un grupo etario, se borrará la fecha de nacimiento.')

                                        ->afterStateUpdated(function ($state, $set) {

                                            // Si se selecciona un grupo etario, borra la fecha de nacimiento

                                            if ($state) {

                                                $set('fecha_nacimiento', null); // Borra la selección de fecha de nacimiento

                                            }

                                        }),



                    // Campo Género (Faltaba y es vital para tus validaciones de embarazo)

                    Forms\Components\Select::make('genero')

                        ->label('Género')

                        ->options([

                            'Masculino' => 'Masculino',

                            'Femenino' => 'Femenino',

                        ])

                        ->required(),

                ]),

        ]),



    Forms\Components\Section::make('Contacto')

        ->schema([

            Forms\Components\Grid::make(2)

                ->schema([

                    Forms\Components\TextInput::make('telefono')

                        ->label('Teléfono')

                        ->numeric()

                        ->maxLength(9),



                    Forms\Components\TextInput::make('correo')

                        ->label('Correo Electrónico')

                        ->email()

                        ->maxLength(255),



                    Forms\Components\TextInput::make('direccion')

                        ->label('Dirección')

                        ->columnSpanFull()

                        ->maxLength(255),

                ]),

        ]),



    // Campo oculto para asegurar que el cliente se cree como Activo

    Forms\Components\Hidden::make('estado')->default('Activo'),

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

                ->visible(fn(Get $get) => $get('genero_temp') === 'Femenino')

                ->columnSpanFull(),



            // -----------------------------

            Forms\Components\Select::make('medico_id')

                ->label('Seleccionar o agregar médico')

                ->relationship(

                    name: 'medico',

                    titleAttribute: 'nombre'

                )

                ->searchable(['nombre'])

                ->preload()

                ->placeholder('Buscar médico...')

                

                // --- CREACIÓN RÁPIDA DE MÉDICO ---

                ->createOptionForm([

                    Forms\Components\TextInput::make('nombre')

                        ->label('Nombre del Médico')

                        ->required()

                        ->maxLength(255)

                        ->placeholder('Ej: Dr. Juan Pérez'),

                ])

                ->createOptionAction(function (Forms\Components\Actions\Action $action) {

                    return $action

                        ->modalHeading('Registrar Nuevo Médico')

                        ->modalSubmitActionLabel('Guardar Médico')

                        ->icon('heroicon-m-plus');

                }),

            // --------------------------------------



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

                /*
                |--------------------------------------------------------------------------
                | TAB PERFILES
                |--------------------------------------------------------------------------
                */

                Tabs\Tab::make('Perfiles')
                    ->schema([

                        Forms\Components\Select::make('buscar_perfil')
                            ->label('Buscar Perfil')
                           ->searchable()
    ->reactive()
   ->options(
    \App\Models\Perfil::where('estado',1)
        ->pluck('nombre','id')
)
    ->afterStateUpdated(function ($state, $set, $get) {

        if(!$state) return;

        $perfil = \App\Models\Perfil::find($state);

        $lista = $get('perfiles_seleccionados') ?? [];

        $lista[] = [
            'perfil_id' => $perfil->id,
            'nombre' => $perfil->nombre,
            'precio' => $perfil->precio,
            'precio_hidden' => $perfil->precio,
            'estado' => $perfil->estado,
            'tipo' => 'perfi',
        ];

        $set('perfiles_seleccionados',$lista);

        $set('buscar_perfil',null);

    }),

                      

                        Forms\Components\Repeater::make('perfiles_seleccionados')
                            ->label('Perfiles agregados')
                            ->addable(false)
                               ->default([])
                            ->reorderable(false)
                            ->deletable(true)
                            ->schema([

                                Forms\Components\Grid::make(3)
                                    ->schema([

                                        Forms\Components\TextInput::make('nombre')
                                            ->label('Perfil')
                                            ->disabled(),

                                        Forms\Components\TextInput::make('precio')
                                            ->label('Precio')
                                            ->disabled(),

                                        Hidden::make('perfil_id'),

                                        Hidden::make('tipo')
                                            ->default('perfil'),

                                        Hidden::make('precio_hidden'),

                                    ])

                            ])
                    ]),

                /*
                |--------------------------------------------------------------------------
                | TAB EXÁMENES
                |--------------------------------------------------------------------------
                */

                Tabs\Tab::make('Exámenes')
                    ->schema([

                       Select::make('buscar_examen')
    ->label('Buscar Examen')
    ->searchable()
    ->reactive()
    
    ->options(
    \App\Models\Examen::where('estado',1)
        ->pluck('nombre','id')
)
    ->afterStateUpdated(function ($state, $set, $get) {

        if(!$state) return;

        $examen = \App\Models\Examen::find($state);

        $lista = $get('examenes_seleccionados') ?? [];

        $lista[] = [
            'examen_id' => $examen->id,
            'nombre_examen' => $examen->nombre,
            'precio' => $examen->precio,
            'precio_hidden' => $examen->precio,
            'recipiente' => $examen->recipiente,
            'tipo' => 'examen',
        ];

        $set('examenes_seleccionados',$lista);

        $set('buscar_examen',null);
        //dd($lista);

    }),

                        

                        Forms\Components\Repeater::make('examenes_seleccionados')
                            ->label('Exámenes agregados')
                            ->addable(false)
                            ->default([])
                            ->reorderable(false)
                            ->deletable(true)
                            ->schema([

                                Forms\Components\Grid::make(3)
                                    ->schema([

                                        Forms\Components\TextInput::make('nombre_examen')
                                            ->label('Examen')
                                            ->disabled()
                                            ->dehydrated(true),

                                        Forms\Components\TextInput::make('precio')
                                            ->label('Precio')
                                            ->disabled(),

                                           Hidden::make('nombre_examen')

                                                ->dehydrated(true),
                                        Hidden::make('examen_id'),

                                        Hidden::make('tipo')
                                            ->default('examen'),

                                        Hidden::make('precio_hidden'),

                                        Hidden::make('recipiente')->dehydrated(true),

                                    ])

                            ])
                    ]),

            ]),

        Hidden::make('subtotal')->default(0),
        Hidden::make('descuento')->default(0),
        Hidden::make('codigo_aplicado'),
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

                            ->visible(fn($livewire) => is_null($livewire->codigoAplicado)),



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

        ->defaultSort('id', 'desc')

        ->defaultPaginationPageOption(50)

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

                            ->formatStateUsing(fn($state) => "Orden #{$state}") // Formato visual "Orden #123"

                            ->weight('bold')

                            ->color('primary')

                            ->searchable() // <--- ¡ESTO PERMITE BUSCAR POR ID!

                            ->sortable(),
                            
                        TextColumn::make('cliente.telefono')
    ->label('Teléfono')
    ->icon('heroicon-o-phone')
    ->color('success')
    ->searchable()
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

        // 1. Cargar relaciones base

        $record->load([

            'cliente',

            'tomaMuestraUser',

            'resultados', // Cargamos resultados para verificar el estado

            // Cargamos relaciones de respaldo por si es una orden vieja sin snapshot

            'detalleOrden.examen.pruebas.reactivosActivos', 

            'detalleOrden.perfil'

        ]);



        // 2. PROCESAR LA DATA (Lógica idéntica a IngresarResultados)

        // Generamos una estructura limpia para la vista que ya tenga las pruebas correctas

        $listaDePruebasVisual = $record->detalleOrden->map(function ($detalle) use ($record) {

            

            // A. Determinar la fuente de las pruebas (Snapshot vs BD)

            $pruebas = collect([]);

            

            if (!empty($detalle->pruebas_snapshot)) {

                // CASO 1: Usar Snapshot (JSON)

                $pruebas = collect($detalle->pruebas_snapshot)->map(fn($item) => (object) $item);

            } elseif ($detalle->examen) {

                // CASO 2: Fallback a BD viva (Ordenes viejas)

                $pruebas = $detalle->examen->pruebas;

            }



            // Si no hay pruebas (ej: examen externo sin configurar), retornamos null para no mostrarlo en la lista de estado

            if ($pruebas->isEmpty()) return null;



            // B. Mapear cada prueba con su estado de resultado

            $pruebasProcesadas = $pruebas->map(function ($prueba) use ($record, $detalle) {

                // Buscamos si ya tiene resultado

                $tieneResultado = $record->resultados

                    ->where('detalle_orden_id', $detalle->id)

                    ->where('prueba_id', $prueba->id)

                    ->whereNotNull('resultado')

                    ->where('resultado', '!=', '')

                    ->isNotEmpty();



                return [

                    'id' => $prueba->id,

                    'nombre' => $prueba->nombre,

                    'completado' => $tieneResultado,

                ];

            });



            return [

                'nombre_examen' => $detalle->nombre_examen ?? ($detalle->examen->nombre ?? 'Examen'),

                'es_externo' => $detalle->examen->es_externo ?? false,

                'pruebas' => $pruebasProcesadas,

            ];

        })->filter(); // Eliminar nulos



        // 3. Retornar la vista pasando la nueva variable procesada

        return view('filament.modals.ver-orden', [

            'record' => $record,

            'lista_pruebas_visual' => $listaDePruebasVisual // <--- VARIABLE NUEVA

        ]);

    })

    ->modalSubmitAction(false)

    ->modalCancelAction(false),



                

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

    // ---------------------------------------------------------
    // 1. FORMULARIO MODAL (TEXTAREA + TOGGLE)
    // ---------------------------------------------------------
    ->modalHeading('Opciones de Impresión')
    ->modalDescription('Puede editar las observaciones antes de generar el PDF.')
    ->modalSubmitActionLabel('Generar PDF')
   ->form(function (Orden $record) {

    // Obtener tipos de examen dinámicamente
    $tipos = $record->detalleOrden
        ->pluck('examen.tipoExamen.nombre')
        ->filter()
        ->unique()
        ->values();

    // Crear campos dinámicos
    $camposAreas = $tipos->map(function ($tipo) use ($record) {

        return Forms\Components\Textarea::make("observaciones_area.$tipo")
            ->label($tipo)
            ->rows(2)
            ->default($record->observaciones_por_area[$tipo] ?? null);

    })->toArray();

    return [

        Forms\Components\Textarea::make('observaciones_edicion')
            ->label('Observaciones Generales')
            ->rows(3)
            ->default($record->observaciones),

        Toggle::make('incluir_firmas')
            ->label('Incluir sellos y firmas')
            ->default(true),

        Forms\Components\Section::make('Observaciones por Área')
            ->collapsible()
            ->collapsed()
            ->schema($camposAreas),

    ];
})

    // ---------------------------------------------------------
    // 2. ACCIÓN PRINCIPAL
    // ---------------------------------------------------------
    ->action(function (Orden $record, array $data) {
   //guardar las observaciones     
$record->update([
    'observaciones' => $data['observaciones_edicion'] ?? null,
    'observaciones_por_area' => $data['observaciones_area'] ?? null,
]);

        // --- B. RESTO DE TU LÓGICA (INTACTA) ---
        $incluirFirmas = $data['incluir_firmas'] ?? false;

        // 1. Cargar relaciones
        $orden = $record->load([
            'cliente',
            'detalleOrden.examen.tipoExamen',
            'detalleOrden.examen.pruebas.tipoPrueba',
            'detalleOrden.examen.pruebas.reactivosActivos.valoresReferencia.grupoEtario',
            'resultados.prueba'
        ]);

        // ---------------------------------------------------------
        //  CONSTRUIR OBSERVACIONES DESDE NOTAS DEL SNAPSHOT
        // ---------------------------------------------------------
        $observacionesDesdeNotas = collect($orden->detalleOrden)
            ->flatMap(function ($detalle) {
                return collect($detalle->pruebas_snapshot ?? [])
                    ->flatMap(function ($prueba) {
                        return collect($prueba['reactivo']['valores_referencia'] ?? [])
                            ->map(fn($v) => $v['nota'] ?? null);
                    });
            })
            ->filter()
            ->unique()
            ->values()
            ->implode("\n");

        // ---------------------------------------------------------
        //  UNIR CON OBSERVACIONES EXISTENTES (SOLO PARA VISUALIZAR)
        // ---------------------------------------------------------
        // Aquí unimos lo que acabas de guardar ($orden->observaciones)
        // con las notas automáticas del snapshot para el PDF.
        $orden->observaciones = collect([
                $orden->observaciones,
                $observacionesDesdeNotas,
            ])
            ->filter()
            ->implode("\n");
// ---------------------------------------------------------

//  UNIR CON OBSERVACIONES EXISTENTES (SIN GUARDAR EN BD)

// ---------------------------------------------------------

$orden->observaciones = collect([

        $orden->observaciones,

        $observacionesDesdeNotas,

    ])

    ->filter()

    ->implode("\n");

    

 



        // 2. Helper para convertir imágenes a Base64

        $imgToBase64 = function ($path) {

            if ($path && file_exists($path)) {

                $type = pathinfo($path, PATHINFO_EXTENSION);

                $data = file_get_contents($path);

                return 'data:image/' . $type . ';base64,' . base64_encode($data);

            }

            return null;

        };



        // 3. Helper interno para procesar cada prueba

        $procesarPrueba = function ($prueba, $orden, $detalleId) {

            $resultado = $orden->resultados->first(function ($res) use ($prueba, $detalleId) {

                return $res->detalle_orden_id == $detalleId && $res->prueba_id == $prueba->id;

            });

            

            $tipoPrueba = $prueba->tipo_prueba_id ? TipoPrueba::find($prueba->tipo_prueba_id)->nombre : ''; 



            if ($resultado) {

                return [

                    'nombre' => $resultado->prueba_nombre_snapshot ?? $prueba->nombre,

                    'resultado' => $resultado->resultado,

                    'referencia' => $resultado->valor_referencia_snapshot ?? 'N/A',

                    'unidades' => $resultado->unidades_snapshot ?? '',

                    'fecha_resultado' => $resultado->updated_at->format('d/m/Y'),
                    
                     'alertar' => (bool) $resultado->alertar,

                    'es_fuera_de_rango' => (bool) $resultado->fuera_de_rango,

                    'user_id' => $resultado->user_id,

                    'tipo_prueba' => $tipoPrueba,

                ];

            }



            // Lógica de Fallback

            $referencia = 'N/A';

            $unidades = '';

            $reactivo = ($prueba instanceof \App\Models\Prueba)

                ? $prueba->reactivosActivos->first()

                : (isset($prueba->reactivo) ? (object) $prueba->reactivo : null);



            if ($reactivo && isset($reactivo->valores_referencia)) {

    $valores = collect($reactivo->valores_referencia ?? []);
    $primero = $valores->first();

    $unidades = is_array($primero)
        ? ($primero['unidades'] ?? '')
        : ($primero->unidades ?? '');

}



            return [

                'nombre' => $prueba->nombre,

                'resultado' => 'Pendiente',

                'referencia' => $referencia,

                'unidades' => $unidades,

                'fecha_resultado' => $orden->fecha->format('d/m/Y'),
                
                'alertar' => false,

                'es_fuera_de_rango' => false,

                'user_id' => null,

            ];

        };



        // 4. Procesamiento Inicial (Contenido Maestro)

        $detallesAgrupados = $orden->detalleOrden->whereNotNull('examen_id')->groupBy('examen.tipoExamen.nombre');

        $contenido_maestro = [];



        foreach ($detallesAgrupados as $tipoExamenNombre => $detalles) {

            $examenes_data = [];

            foreach ($detalles as $detalle) {

                if ($detalle->examen->es_externo) {

                    $resultadosExternos = $orden->resultados

                        ->where('detalle_orden_id', $detalle->id)

                        ->where('es_externo', true);



                    $dataUnitarias = $resultadosExternos->map(fn($res) => [

                        'nombre' => $res->prueba_nombre_snapshot ?? 'Prueba Externa',

                        'resultado' => $res->resultado,

                        'referencia' => $res->valor_referencia_snapshot ?? 'N/A',

                        'unidades' => $res->unidades_snapshot ?? '',

                        'fecha_resultado' => $res->updated_at->format('d/m/Y'),
                        
                        'alertar' => (bool) $res->alertar,

                        'es_fuera_de_rango' => false,

                        'user_id' => $res->user_id,

                    ])->all();



                    $examenes_data[] = [

                        'nombre' => $detalle->examen->nombre,

                        'pruebas_unitarias' => $dataUnitarias,

                        'matrices' => [],

                    ];

                } else {

                    $todasLasPruebas = !empty($detalle->pruebas_snapshot)

                        ? collect($detalle->pruebas_snapshot)->map(fn($item) => json_decode(json_encode($item)))

                        : $detalle->examen->pruebas->where('es_externo', false);



                    $pruebasUnitarias = $todasLasPruebas->whereNull('tipo_conjunto');

                    $pruebasConjuntas = $todasLasPruebas->whereNotNull('tipo_conjunto')->groupBy('tipo_conjunto');



                    $dataUnitarias = $pruebasUnitarias->map(fn($p) => $procesarPrueba($p, $orden, $detalle->id))->all();



                    $dataMatrices = $pruebasConjuntas->map(function ($pruebasDelConjunto) use ($procesarPrueba, $orden, $detalle) {

                        $f = []; $c = []; $m = [];

                        foreach ($pruebasDelConjunto as $p) {

                            $partes = explode(', ', $p->nombre);

                            if (count($partes) >= 2) {

                                [$nf, $nc] = $partes;

                                $f[] = $nf; $c[] = $nc;

                                $m[$nf][$nc] = $procesarPrueba($p, $orden, $detalle->id);

                            }

                        }

                        return ['filas' => array_values(array_unique($f)), 'columnas' => array_values(array_unique($c)), 'data' => $m];

                    })->all();



                    $examenes_data[] = [

                        'nombre' => $detalle->nombre_examen,

                        'pruebas_unitarias' => $dataUnitarias,

                        'matrices' => $dataMatrices,

                    ];

                }

            }

            $contenido_maestro[$tipoExamenNombre ?: 'Exámenes Generales'] = $examenes_data;

        }



        // 5. RE-AGRUPACIÓN POR USUARIO

        $userIdsParticipantes = $orden->resultados->pluck('user_id')->filter()->unique();

        $usuariosCargados = User::whereIn('id', $userIdsParticipantes)->get();



        if ($usuariosCargados->isEmpty() && $orden->resultados->count() > 0) {

            $usuariosCargados = collect([auth()->user()]);

        }



        $grupos_finales = [];



        foreach ($usuariosCargados as $usuario) {

            $uId = $usuario->id;

            $datos_usuario = []; 



            foreach ($contenido_maestro as $tipo => $listaExamenes) {

                $examenesFiltrados = [];

                

                foreach ($listaExamenes as $ex) {

                    $unitariasUser = array_filter($ex['pruebas_unitarias'], fn($p) => ($p['user_id'] ?? null) == $uId);

                    

                    $matricesUser = [];

                    foreach ($ex['matrices'] as $matriz) {

                        $tieneData = false;

                        foreach($matriz['data'] as $row) {

                            foreach($row as $cell) {

                                if (($cell['user_id'] ?? null) == $uId) {

                                    $tieneData = true; break 2;

                                }

                            }

                        }

                        if ($tieneData) {

                            $matricesUser[] = $matriz;

                        }

                    }



                    if (!empty($unitariasUser) || !empty($matricesUser)) {

                        $examenesFiltrados[] = [

                            'nombre' => $ex['nombre'],

                            'pruebas_unitarias' => $unitariasUser,

                            'matrices' => $matricesUser

                        ];

                    }

                }



                if (!empty($examenesFiltrados)) {

                    $datos_usuario[$tipo] = $examenesFiltrados;

                }

            }



            if (!empty($datos_usuario)) {

                // *** AQUÍ APLICAMOS LA LÓGICA DEL TOGGLE ***

                if ($incluirFirmas) {

                    $pathFirma = $usuario->firma_path ? storage_path('app/public/' . $usuario->firma_path) : null;

                    $pathSello = $usuario->sello_path ? storage_path('app/public/' . $usuario->sello_path) : null;

                } else {

                    // Si dijo que NO, enviamos null

                    $pathFirma = null;

                    $pathSello = null;

                }



                $grupos_finales[] = [

                    'laboratorista' => $usuario->name,

                    'firma_b64' => $imgToBase64($pathFirma),

                    'sello_b64' => $imgToBase64($pathSello),

                    'datos' => $datos_usuario

                ];

            }

        }

        

        // 6. Preparar PDF

        $pathLogo = storage_path('app/public/logo.png');

        

        // *** LÓGICA DEL TOGGLE PARA EL SELLO DE REGISTRO ***

        if ($incluirFirmas) {

             $pathSelloRegistro = storage_path('app/public/sello.png');

        } else {

             $pathSelloRegistro = null;

        }



        $pdf_data = [

            'orden' => $orden,

            'grupos_por_usuario' => $grupos_finales,

            'logo_b64' => $imgToBase64($pathLogo),

            'sello_registro_b64' => $imgToBase64($pathSelloRegistro),

        ];



        $pdf = Pdf::setOptions([

            'isRemoteEnabled' => false,

            'isHtml5ParserEnabled' => true,

            'dpi' => 96,

            'defaultFont' => 'sans-serif',

            'chroot' => base_path(),

        ])->loadView('pdf.reporte_resultados', $pdf_data);



        // Nombre y Descarga

        $expediente = $record->cliente->NumeroExp ?? 'SinExp';

        $nombreCliente = Str::slug($record->cliente->nombre . ' ' . $record->cliente->apellido);

        $fileName = strtoupper("{$nombreCliente} - {$record->id}.pdf");



        Storage::disk('public')->put("reportes/{$fileName}", $pdf->output());

        Notification::make()->title('Reporte generado')->success()->send();



        return response()->streamDownload(fn() => print($pdf->output()), $fileName);

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



               Tables\Actions\Action::make('descargarReporte')

                ->tooltip('Descargar Reporte Guardado')

                ->icon('heroicon-o-arrow-down-tray')

                ->iconButton()

                ->color('success')

                ->visible(function (Orden $record) {

                    // 1. Reconstruimos el nombre EXACTO para verificar si existe

                    $expediente = $record->cliente->NumeroExp ?? 'SinExp';

                    $nombreCliente = \Illuminate\Support\Str::slug($record->cliente->nombre . ' ' . $record->cliente->apellido);

                    $ordenId = $record->id;

            

                    $fileName = strtoupper("{$nombreCliente} - {$record->id}.pdf");
                    $filePath = "reportes/{$fileName}";

            

                    // Verificamos existencia y estado

                    return Storage::disk('public')->exists($filePath) && $record->estado === 'finalizado';

                })

                ->action(function (Orden $record) {

                    // 1. Reconstruimos el nombre nuevamente para proceder a la descarga

                    $expediente = $record->cliente->NumeroExp ?? 'SinExp';

                    $nombreCliente = \Illuminate\Support\Str::slug($record->cliente->nombre . ' ' . $record->cliente->apellido);

                    $ordenId = $record->id;

            

                    $fileName = strtoupper("{$nombreCliente} - {$record->id}.pdf");


                    $filePath = "reportes/{$fileName}";

                    $fullPath = storage_path("app/public/{$filePath}");

            

                    // 2. Descargar

                    return response()->download($fullPath);

                }),



                Tables\Actions\Action::make('restaurarOrden')

                    ->label('Restaurar')

                    ->tooltip('Regresar a estado "En Proceso"')

                    ->icon('heroicon-o-arrow-uturn-left') // Icono de "Deshacer"

                    ->color('warning') // Naranja para indicar precaución



                    // --- VISIBILIDAD BLINDADA ---

                    ->visible(

                        fn(Orden $record): bool =>

                        ($record->estado === 'finalizado' || $record->estado === 'cancelado') && // 1. Solo si está finalizada o cancelada

                        auth()->user()->can('restaurar_orden') // 2. Solo si tiene el permiso especial

                    )



                    // --- CONFIRMACIÓN ---

                    ->requiresConfirmation()

                    ->modalHeading('¿Restaurar Orden?')

                    ->modalDescription('La orden cambiará de estado "Finalizado" a "En Proceso". Esto permitirá modificar resultados nuevamente.')

                    ->modalSubmitActionLabel('Sí, Restaurar')



                    // --- LÓGICA ---

                    ->action(function (Orden $record) {

                        $record->update([

                            'estado' => 'en proceso' // Regresamos el estado

                        ]);



                        \Filament\Notifications\Notification::make()

                            ->title('Orden Restaurada')

                            ->body("La orden #{$record->id} está abierta nuevamente.")

                            ->success()

                            ->send();

                    }),

                    

Tables\Actions\Action::make('enviarPorCorreoOWhatsApp')

    ->tooltip('Enviar Resultados por Correo o WhatsApp')

    ->icon('heroicon-o-paper-airplane')

    ->iconButton()

    ->color('blue')

    ->visible(function (Orden $record) {

        $expediente = $record->cliente->NumeroExp ?? 'SinExp';

        $nombreCliente = \Illuminate\Support\Str::slug($record->cliente->nombre . ' ' . $record->cliente->apellido);

        $ordenId = $record->id;

        $fileName = strtoupper("{$nombreCliente} - {$record->id}.pdf");


        $filePath = "reportes/{$fileName}";

        

        return Storage::disk('public')->exists($filePath) && $record->estado === 'finalizado';

    })

    ->action(function (Orden $record) {

        // --- 1. CONFIGURACIÓN DE DATOS DEL LABORATORIO ---

        $labNombre = "Laboratorio Clínico Merino";

        $labTelefonos = "2606-6596 /  7595-4210"; 

        // CAMBIO: Aquí ponemos la web en lugar de redes

        $labWeb = "www.laboratorioclinicomerino.com"; 

        

        // --- 2. PREPARACIÓN DE ARCHIVOS ---

        $expediente = $record->cliente->NumeroExp ?? 'SinExp';

        $nombreCliente = \Illuminate\Support\Str::slug($record->cliente->nombre . ' ' . $record->cliente->apellido);

        $ordenId = $record->id;

        $fileName = strtoupper("{$nombreCliente} - {$ordenId}.pdf");


        $filePath = "reportes/{$fileName}";

        $fullPath = storage_path("app/public/{$filePath}");



        // --- 3. CONSTRUCCIÓN DEL MENSAJE AMABLE ---

        $nombrePaciente = $record->cliente->nombre . ' ' . $record->cliente->apellido;

        $asuntoCorreo = "Resultados de Laboratorio - Orden #{$ordenId} - {$labNombre}";



        // Construcción del mensaje con la Web

        $mensajeBase = "Estimado(a) *{$nombrePaciente}*,\n\n";

        $mensajeBase .= " *Por favor, revise el documento PDF adjunto.*\n\n";

        $mensajeBase .= "Para cualquier consulta sobre sus resultados, estamos a su disposición en:\n";

        $mensajeBase .= " Teléfonos: {$labTelefonos}\n";

        $mensajeBase .= " Web Informativa: {$labWeb}\n\n"; // <--- CAMBIO AQUÍ

        $mensajeBase .= "¡Gracias por confiar en nosotros! Que tenga un excelente día.";



        // --- 4. GENERACIÓN DE LINKS ---

        $telefonoCliente = $record->cliente->telefono; 

        $correoCliente = $record->cliente->correo; 



        // rawurlencode asegura que los espacios y saltos de línea funcionen en todos los dispositivos

        $linkWhatsapp = 'https://wa.me/' . $telefonoCliente . '?text=' . rawurlencode($mensajeBase);

        $linkCorreo = 'mailto:' . $correoCliente . '?subject=' . rawurlencode($asuntoCorreo) . '&body=' . rawurlencode($mensajeBase);



        // --- 5. NOTIFICACIÓN AL USUARIO DEL SISTEMA ---

        Notification::make()

            ->title('PDF Descargado')

            ->body("El archivo se ha guardado en tu equipo.\nSelecciona cómo enviar el mensaje y **recuerda adjuntar el PDF manualmente**.")

            ->success()

            ->persistent() // Obliga a cerrar manual

            ->actions([

                \Filament\Notifications\Actions\Action::make('whatsapp')

                    ->label('WhatsApp')

                    ->url($linkWhatsapp, shouldOpenInNewTab: true)

                    ->button()

                    ->color('success'),

                

                \Filament\Notifications\Actions\Action::make('email')

                    ->label('Correo')

                    ->url($linkCorreo)

                    ->button()

                    ->color('gray'),

            ])

            ->send();



        // --- 6. DESCARGA FINAL ---

        //return response()->download($fullPath);

    }),

    

    

            ]);

    }

    public static function getRelations(): array

    {

        return [];

    }



    // En app/Filament/Resources/OrdenResource.php



public static function getDatosPruebaParaPdf($prueba, $orden, $detalleId): array

{

    $resultado = $orden->resultados->where('prueba_id', $prueba->id)->where('detalle_orden_id', $detalleId)->first();



    $nombre_prueba = $prueba->nombre;

    $referencia_formateada = 'N/A'; // Valor por defecto

    $unidades = '';                 // Unidades vacías por defecto

    $es_fuera_de_rango = false;

    $valor_resultado_num = null;



    if ($resultado && is_numeric($resultado->resultado)) {

        $valor_resultado_num = (float) $resultado->resultado;

    }



    // --- 1. INTENTAR CALCULAR EN VIVO (BD VIVA) ---

    $todosLosValores = collect([]);

    if ($prueba->reactivoEnUso && $prueba->reactivoEnUso->valoresReferencia->isNotEmpty()) {

        $todosLosValores = $prueba->reactivoEnUso->valoresReferencia;

    }



    if ($todosLosValores->isNotEmpty()) {

        $cliente = $orden->cliente;

        $generoCliente = $cliente->genero; 

        $valorRef = null;



        // A. Embarazo

        if (isset($orden->semanas_gestacion) && $orden->semanas_gestacion) {

                $grupoEmbarazo = \App\Models\GrupoEtario::where('unidad_tiempo', 'semanas')

                ->where('edad_min', '<=', $orden->semanas_gestacion)

                ->where('edad_max', '>=', $orden->semanas_gestacion)

                ->first();



            if ($grupoEmbarazo) {

                $valorRef = $todosLosValores->first(function($val) use ($grupoEmbarazo) {

                    $gid = is_array($val) ? ($val['grupo_etario_id'] ?? null) : $val->grupo_etario_id;

                    return $gid == $grupoEmbarazo->id;

                });

            }

        }



        // B. Cascada de Grupos (Específico -> Universal)

        if (!$valorRef) {

            $grupoEtarioCliente = $cliente->getGrupoEtario(); 

            $grupoTodasEdades = \App\Models\GrupoEtario::where('nombre', 'Todas las edades')

                ->orWhere(function($query) {

                    $query->where('edad_min', 0)->where('edad_max', '>=', 120);

                })->first();



            // 1. Específico + Género

            if ($grupoEtarioCliente) {

                $valorRef = $todosLosValores->first(fn($val) => 

                    ($val->grupo_etario_id == $grupoEtarioCliente->id) && ($val->genero == $generoCliente)

                );

            }

            // 2. Específico + Ambos

            if (!$valorRef && $grupoEtarioCliente) {

                $valorRef = $todosLosValores->first(fn($val) => 

                    ($val->grupo_etario_id == $grupoEtarioCliente->id) && ($val->genero == 'Ambos')

                );

            }

            // 3. Universal + Género

            if (!$valorRef && $grupoTodasEdades) {

                $valorRef = $todosLosValores->first(fn($val) => 

                    ($val->grupo_etario_id == $grupoTodasEdades->id) && ($val->genero == $generoCliente)

                );

            }

            // 4. Universal + Ambos

            if (!$valorRef && $grupoTodasEdades) {

                $valorRef = $todosLosValores->first(fn($val) => 

                    ($val->grupo_etario_id == $grupoTodasEdades->id) && ($val->genero == 'Ambos')

                );

            }

        }



        // Si encontramos referencia en vivo, seteamos valores

        if ($valorRef) {

            $valorMin = (float) $valorRef->valor_min;

            $valorMax = (float) $valorRef->valor_max;

            $unidades = $valorRef->unidades ?? '';



            $referencia_formateada = match ($valorRef->operador) {

                'rango' => "$valorMin - $valorMax",

                '<=' => "≤ $valorMax",

                '<' => "< $valorMax",

                '>=' => "≥ $valorMin",

                '>' => "> $valorMin",

                '=' => "= $valorMin",

                default => $valorRef->descriptivo ?? '',

            };



            // Cálculo Fuera de Rango

            if (!is_null($valor_resultado_num)) {

                switch ($valorRef->operador) {

                    case 'rango': if ($valor_resultado_num < $valorMin || $valor_resultado_num > $valorMax) $es_fuera_de_rango = true; break;

                    case '<=': if ($valor_resultado_num > $valorMax) $es_fuera_de_rango = true; break;

                    case '<': if ($valor_resultado_num >= $valorMax) $es_fuera_de_rango = true; break;

                    case '>=': if ($valor_resultado_num < $valorMin) $es_fuera_de_rango = true; break;

                    case '>': if ($valor_resultado_num <= $valorMin) $es_fuera_de_rango = true; break;

                    case '=': if ($valor_resultado_num != $valorMin) $es_fuera_de_rango = true; break;

                }

            }

        }

    }



    // --- 2. PRIORIDAD AL SNAPSHOT (Datos Guardados) ---

    if ($resultado && !empty($resultado->prueba_nombre_snapshot)) {

        $nombre_prueba = $resultado->prueba_nombre_snapshot;

        // Tomamos lo que dice la BD, pero abajo lo validamos

        $referencia_formateada = $resultado->valor_referencia_snapshot ?? 'N/A';

        $unidades = $resultado->unidades_snapshot ?? '';

    }



    // --- 3. LIMPIEZA FINAL (El arreglo que pediste) ---

    // Si dice "SIN RANGO" (porque se guardó así antes) o es nulo/vacío -> Forzamos N/A y sin unidades

    if ($referencia_formateada === 'SIN RANGO' || empty($referencia_formateada)) {

        $referencia_formateada = 'N/A';

        $unidades = ''; // <--- Aquí borramos las unidades

    }



    return [

        'nombre' => $nombre_prueba,

        'resultado' => $resultado->resultado ?? 'PENDIENTE',

        'referencia' => $referencia_formateada,

        'unidades' => $unidades,

        'fecha_resultado' => $resultado ? $resultado->updated_at->format('d/m/Y') : '',

        'es_fuera_de_rango' => $es_fuera_de_rango,

        'tipo_prueba' => $prueba->tipoPrueba->nombre ?? '',

    ];

}





    public static function getRecordUrlUsing(): Closure

    {

        return fn($record) => null; // esto desactiva el enlace de clic en la tarjeta

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