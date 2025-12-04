<?php

namespace App\Filament\Resources;
use Filament\Forms\Components\Actions\Action as FormAction;
use Filament\Forms\Components\Card;
use Filament\Forms\Components\Section;
use Filament\Notifications\Notification;
use Livewire\Component as Livewire;
// ... otras declaraciones 'use'
use Filament\Forms\Components\Tabs;
use Filament\Tables\Actions\Action;
use Filament\Forms\Get;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use App\Filament\Resources\ReactivoResource\Pages;
use App\Models\Reactivo;


class ReactivoResource extends Resource
{
    protected static ?string $model = Reactivo::class;

    protected static ?string $navigationGroup = 'Gestión de Laboratorio';
    protected static ?int $navigationSort = 4;
    protected static ?string $navigationIcon = 'heroicon-o-beaker';



    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Card::make()
                    ->schema([
                        Section::make('Información del Reactivo')
                            ->schema([
                                // --- CAMBIO 1: Relación Múltiple ---
                                Select::make('pruebas')
                                    ->relationship(
                                        'pruebas',
                                        'nombre',
                                        // Aquí aplicamos el filtro: Solo pruebas donde tipo_conjunto es NULL
                                        fn($query) => $query->whereNull('tipo_conjunto')
                                         ->where('estado', 'activo')
                                    )
                                    ->multiple()
                                    ->preload()
                                    ->searchable()
                                    ->required()
                                    ->label('Pruebas Asignadas'),
                                // ----------------------------------

                                TextInput::make('nombre')->required()->maxLength(255),
                                TextInput::make('lote'),

                                Forms\Components\DatePicker::make('fecha_caducidad')->minDate(now()->toDateString()),

                                Forms\Components\Toggle::make('en_uso')
                                    ->default(false)
                                    ->label('¿En Uso?')
                                    ->helperText('Indica si este bote/lote es el que se está utilizando actualmente.'),

                                Textarea::make('descripcion')->columnSpanFull(),
                            ])->columns(2)
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        $esAccionable = fn(Reactivo $record): bool => $record->estado === 'disponible';
$activeTab = request()->query('table');




        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nombre')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('pruebas.nombre')
                    ->badge()
                    ->searchable(),
                Tables\Columns\BadgeColumn::make('estado')
                    ->colors([
                        'success' => 'disponible',
                        'warning' => 'caducado',
                        'danger' => 'agotado',
                    ]),
                Tables\Columns\TextColumn::make('lote')->searchable()->sortable(),
                Tables\Columns\IconColumn::make('en_uso')->label('En Uso')->boolean(),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->visible(fn(Reactivo $record): bool => $record->estado === 'disponible'), // Solo visible si está disponible

              Action::make('setActive')
                    ->label('Poner en Uso')
                    ->visible(fn() => auth()->user()->can('activar_reactivos'))
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                   ->visible(fn(Reactivo $record): bool => 
                        auth()->user()->can('activar_reactivos') && 
                        !$record->en_uso && 
                        $record->estado === 'disponible' // <--- Candado extra
                    ) ->requiresConfirmation()
                    ->modalHeading('Activar Reactivo y Resolver Conflictos')

                    // ... dentro de Action::make('setActive') ...

                    ->modalDescription(function (Reactivo $record) {
                        // 1. Lógica de cálculo (Igual que antes)
                        $nuevasPruebasIds = $record->pruebas()->pluck('pruebas.id')->toArray();
                        $conflictos = Reactivo::with('pruebas')
                            ->where('en_uso', true)
                            ->where('id', '!=', $record->id)
                            ->whereHas('pruebas', function ($q) use ($nuevasPruebasIds) {
                                $q->whereIn('pruebas.id', $nuevasPruebasIds);
                            })
                            ->get();

                        if ($conflictos->isEmpty()) {
                            return 'No hay conflictos. Este reactivo se activará correctamente.';
                        }

                        $pruebasHuerfanas = [];
                        $nombresConflictos = [];

                        foreach ($conflictos as $viejo) {
                            $nombresConflictos[] = $viejo->nombre;
                            foreach ($viejo->pruebas as $pruebaVieja) {
                                if (!in_array($pruebaVieja->id, $nuevasPruebasIds)) {
                                    $pruebasHuerfanas[] = "{$pruebaVieja->nombre} <span class='text-gray-500 text-xs'>(del {$viejo->nombre})</span>";
                                }
                            }
                        }

                        // 2. CONSTRUCCIÓN DEL HTML BONITO
                        $html = '<div class="space-y-4 text-sm">';

                        // A) Aviso de desactivación (Amarillo/Naranja)
                        $html .= '<div class="p-3 bg-orange-50 border-l-4 border-orange-500 rounded-r-md">';
                        $html .= '<p class="text-orange-900 font-medium">Se desactivarán los siguientes reactivos:</p>';
                        $html .= '<ul class="mt-1 list-disc list-inside text-orange-800">';
                        foreach (array_unique($nombresConflictos) as $nombre) {
                            $html .= "<li>{$nombre}</li>";
                        }
                        $html .= '</ul></div>';

                        // B) Advertencia Crítica (Rojo) - Solo si hay huérfanos
                        if (!empty($pruebasHuerfanas)) {
                            $html .= '<div class="p-3 bg-red-50 border-l-4 border-red-500 rounded-r-md">';
                            $html .= '<div class="flex items-center gap-2 text-red-800 font-bold mb-1">';
                            $html .= '<svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" /></svg>';
                            $html .= 'ADVERTENCIA CRÍTICA';
                            $html .= '</div>';

                            $html .= '<p class="text-red-700 mb-2">El nuevo reactivo <u>NO cubre</u> todas las pruebas. Las siguientes quedarán <strong>SIN REACTIVO ASIGNADO</strong>:</p>';

                            $html .= '<ul class="list-disc list-inside text-red-900 font-medium bg-red-100/50 p-2 rounded">';
                            foreach (array_unique($pruebasHuerfanas) as $huerfana) {
                                $html .= "<li>{$huerfana}</li>";
                            }
                            $html .= '</ul>';
                            $html .= '</div>';
                        } else {
                            // Mensaje verde si todo está seguro
                            $html .= '<div class="text-green-600 flex items-center gap-2 font-medium">';
                            $html .= '<svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" /></svg>';
                            $html .= 'Cobertura completa: Todas las pruebas necesarias están cubiertas.';
                            $html .= '</div>';
                        }

                        $html .= '</div>';

                        return new \Illuminate\Support\HtmlString($html);
                    })

                    ->action(function (Reactivo $record): void {
                        \Illuminate\Support\Facades\DB::transaction(function () use ($record) {
                            $misPruebasIds = $record->pruebas()->pluck('pruebas.id')->toArray();

                            // Desactivar competencia
                            Reactivo::where('en_uso', true)
                                ->where('id', '!=', $record->id)
                                ->whereHas('pruebas', function ($q) use ($misPruebasIds) {
                                $q->whereIn('pruebas.id', $misPruebasIds);
                            })
                                ->update(['en_uso' => false]);

                            // Activar nuevo
                            $record->update(['en_uso' => true]);
                        });

                        Notification::make()
                            ->title('Reactivo activado')
                            ->body('Configuración de uso actualizada.')
                            ->success()
                            ->send();
                    }),

                Action::make('gestionarValores')
                    ->label('Valores de Referencia')
                    ->icon('heroicon-o-clipboard-document-list')
                    ->color('gray')
                    ->modalWidth('4xl')
                    ->modalSubmitActionLabel('Guardar')
                    ->fillForm(fn(Reactivo $record) => [
                        'valoresReferencia' => $record->valoresReferencia->toArray(),
                    ])
                    ->form(function (Reactivo $record) {
                        return [
                            Tabs::make('Opciones')->tabs([
                                Tabs\Tab::make('Consultar Valores')
                                    ->schema([
                                        Forms\Components\View::make('filament.components.valor-referencia-table')
                                            ->viewData([
                                                'valores' => $record->valoresReferencia()->with('grupoEtario')->get(),
                                            ]),
                                    ]),

                                Tabs\Tab::make('Gestionar Valores')
                                    ->schema([
                                        Forms\Components\Section::make('Registros de Referencia')
                                            ->description('Administre los rangos de referencia para este reactivo.')
                                            ->headerActions([
                                                FormAction::make('copyFrom')
                                                    ->label('Copiar valores')
                                                    ->icon('heroicon-o-document-duplicate')
                                                    ->color('gray')
                                                    ->form([
                                                        Select::make('source_reactivo_id')
                                                            ->label('Seleccionar Reactivo de Origen')
                                                            ->helperText('Se copiarán todos los rangos de referencia del reactivo seleccionado.')
                                                            ->required()
                                                            ->searchable()
                                                            ->preload()
                                                            ->options(function () use ($record) {
                                                                // Buscamos reactivos que compartan pruebas
                                                                $misPruebasIds = $record->pruebas->pluck('id')->toArray();

                                                                if (empty($misPruebasIds)) {
                                                                    return Reactivo::limit(50)->pluck('nombre', 'id');
                                                                }

                                                                return Reactivo::whereHas('pruebas', function ($q) use ($misPruebasIds) {
                                                                    $q->whereIn('pruebas.id', $misPruebasIds);
                                                                })
                                                                    ->where('id', '!=', $record->id)
                                                                    ->get()
                                                                    ->mapWithKeys(fn($r) => [$r->id => "{$r->nombre} (Lote: {$r->lote})"]);
                                                            }),
                                                    ])
                                                    ->action(function (array $data, callable $set) {
                                                        $sourceReagent = Reactivo::with('valoresReferencia')->find($data['source_reactivo_id']);
                                                        if (!$sourceReagent)
                                                            return;

                                                        $valuesToCopy = $sourceReagent->valoresReferencia->map(function ($valor) {
                                                            $data = $valor->toArray();
                                                            unset($data['id'], $data['reactivo_id'], $data['created_at'], $data['updated_at']);
                                                            return $data;
                                                        })->toArray();

                                                        $set('valoresReferencia', $valuesToCopy);
                                                        Notification::make()->title('Valores importados correctamente')->success()->send();
                                                    }),
                                            ])
                                            ->schema([
                                                Repeater::make('valoresReferencia')
                                                    ->relationship()
                                                    ->label('')
                                                    ->itemLabel(
                                                        fn(array $state): ?string =>
                                                        ($state['prueba_id'] ? \App\Models\Prueba::find($state['prueba_id'])?->nombre : 'General') . ' | ' .
                                                        ($state['valor_min'] ?? '?') . ' - ' . ($state['valor_max'] ?? '?') . ' ' . ($state['unidades'] ?? '')
                                                    )
                                                    ->schema([
                                                        Forms\Components\Group::make()
                                                            ->schema([
                                                                Forms\Components\Grid::make(3)
                                                                    ->schema([

                                                                        // --- CORRECCIÓN DEL SELECTOR DE PRUEBA ---
                                                                        Forms\Components\Select::make('prueba_id')
                                                                            ->label('Aplica a la Prueba')
                                                                            ->placeholder('General (Aplica a todas)')
                                                                            // Usamos $record (Reactivo) capturado del scope superior
                                                                            ->options(function () use ($record) {
                                                                                if ($record && $record->exists) {
                                                                                    return $record->pruebas->pluck('nombre', 'id');
                                                                                }

                                                                                // Si aún no existe (ej: creando un reactivo), mostrar TODAS las pruebas
                                                                                return \App\Models\Prueba::pluck('nombre', 'id');
                                                                            })

                                                                            ->searchable()
                                                                            ->preload()
                                                                            ->columnSpan(1),
                                                                        // -----------------------------------------
            
                                                                        Forms\Components\Select::make('grupo_etario_id')
                                                                            ->relationship('grupoEtario', 'nombre')
                                                                            ->label('Grupo Etario')
                                                                            ->searchable()
                                                                            ->preload()
                                                                            ->live()
                                                                            ->afterStateUpdated(fn(Forms\Set $set) => $set('genero', 'Ambos'))
                                                                            ->columnSpan(1),

                                                                        Forms\Components\Select::make('genero')
                                                                            ->label('Género')
                                                                            ->options(function (Get $get) {
                                                                                $grupoId = $get('grupo_etario_id');
                                                                                $base = ['Masculino' => 'Masculino', 'Femenino' => 'Femenino', 'Ambos' => 'Ambos'];

                                                                                if ($grupoId) {
                                                                                    $grupo = \App\Models\GrupoEtario::find($grupoId);
                                                                                    if ($grupo && $grupo->genero !== 'Ambos') {
                                                                                        return [$grupo->genero => $grupo->genero];
                                                                                    }
                                                                                }
                                                                                return $base;
                                                                            })
                                                                            ->default('Ambos')
                                                                            ->required()
                                                                            ->columnSpan(1),
                                                                    ]),
                                                            ]),

                                                        Forms\Components\Section::make()
                                                            ->compact()
                                                            ->schema([
                                                                Forms\Components\Grid::make(4)
                                                                    ->schema([
                                                                        Select::make('operador')
                                                                            ->options([
                                                                                'rango' => 'Rango (Min - Max)',
                                                                                '<=' => 'Menor o igual (<=)',
                                                                                '>=' => 'Mayor o igual (>=)',
                                                                                '<' => 'Menor que (<)',
                                                                                '>' => 'Mayor que (>)',
                                                                                '=' => 'Igual a (=)',
                                                                            ])
                                                                            ->default('rango')
                                                                            ->required()
                                                                            ->live()
                                                                            ->columnSpan(1),

                                                                        TextInput::make('valor_min')
                                                                            ->numeric()
                                                                            ->label('Mínimo')
                                                                            ->visible(fn(Get $get) => in_array($get('operador'), ['rango', '>=', '>', '=']))
                                                                            ->required(fn(Get $get) => in_array($get('operador'), ['rango', '>=', '>']))
                                                                            ->columnSpan(1),

                                                                        TextInput::make('valor_max')
                                                                            ->numeric()
                                                                            ->label('Máximo')
                                                                            ->visible(fn(Get $get) => in_array($get('operador'), ['rango', '<=', '<']))
                                                                            ->required(fn(Get $get) => in_array($get('operador'), ['rango', '<=', '<']))
                                                                            ->columnSpan(1),

                                                                        TextInput::make('unidades')
                                                                            ->label('Unidades')
                                                                            ->placeholder('mg/dL')
                                                                            ->datalist(['mg/dL', 'g/dL', '%', 'U/L'])
                                                                            ->columnSpan(1),
                                                                    ]),
                                                            ]),

                                                        Forms\Components\Grid::make(2)
                                                            ->schema([
                                                                TextInput::make('descriptivo')
                                                                    ->label('Texto Descriptivo (Opcional)')
                                                                    ->placeholder('Ej: Negativo')
                                                                    ->columnSpan(1),

                                                                TextInput::make('nota')
                                                                    ->label('Nota interna')
                                                                    ->placeholder('Comentario para el bacteriólogo')
                                                                    ->columnSpan(1),
                                                            ]),
                                                    ])
                                                    ->columns(1)
                                                    ->collapsible()
                                                    ->collapsed(false)
                                                    ->cloneable()
                                                    ->defaultItems(0)
                                                    ->deleteAction(fn($action) => $action->requiresConfirmation()),
                                            ]),
                                    ])->visible( !$record->es_historico),
                            ])
                        ];
                    })
                    ->action(function (Reactivo $record, array $data): void {
                        // Guardado automático
                    }),

               Action::make('restock')
                    ->label('Reabastecer')
                    ->icon('heroicon-o-arrow-path-rounded-square')
                    ->color('primary')
                    ->visible(fn(Reactivo $record) => 
        in_array($record->estado, ['agotado', 'caducado']) && 
        !$record->es_historico && // <--- NUEVA CONDICIÓN
        auth()->user()->can('reabastecer_reactivos')
    )
                    ->modalHeading('Reabastecer Reactivo')
                    ->modalDescription('Esto creará un nuevo lote basado en este, copiando sus pruebas asignadas y valores de referencia.')
                    ->form([
                        Forms\Components\DatePicker::make('fecha_caducidad')
                            ->label('Nueva Fecha de Vencimiento')
                            ->required(),
                        Forms\Components\TextInput::make('lote')
                            ->label('Nuevo Número de Lote')
                            ->required(),
                    ])
                    ->action(function (Reactivo $record, array $data) {
                        // 1. Replicamos atributos básicos
                        $newReagent = $record->replicate();

                        // 2. Asignamos datos nuevos
                        $newReagent->fecha_caducidad = $data['fecha_caducidad'];
                        $newReagent->lote = $data['lote'];
                        $newReagent->estado = 'disponible';
                        $newReagent->en_uso = false;

                        $newReagent->save(); // Guardamos para tener ID
            
                        // --- CORRECCIÓN AQUÍ: COPIAR LAS PRUEBAS (PIVOTE) ---
                        // Obtenemos los IDs de las pruebas del reactivo viejo
                        $pruebasIds = $record->pruebas()->pluck('pruebas.id')->toArray();
                        // Se los pegamos al reactivo nuevo en la tabla pivote
                        $newReagent->pruebas()->sync($pruebasIds);
                        // ----------------------------------------------------
            
                        // 4. Copiamos los valores de referencia (HasMany)
                        foreach ($record->valoresReferencia as $valor) {
                            $newValor = $valor->replicate();
                            $newValor->reactivo_id = $newReagent->id;
                            $newValor->save();
                        }
                        $record->update(['es_historico' => true]);
                      Notification::make()->title('Reabastecido y archivado')->success()->send();
                    }),
                // --- ¡NUEVO BOTÓN PARA MARCAR AGOTADO! ---
                Action::make('marcarAgotado')
                    ->label('Marcar Agotado')
                    ->icon('heroicon-o-archive-box-x-mark')
                    ->color('danger')
                    ->visible(
                        fn(Reactivo $record): bool =>
                        $record->estado === 'disponible' &&
                        auth()->user()->can('agotar_reactivos')
                    )
                    ->requiresConfirmation()
                    ->modalHeading('Marcar Reactivo como Agotado')
                    ->modalDescription('Esta acción es irreversible y desactivará el uso de este reactivo. ¿Estás seguro?')
                    ->action(function (Reactivo $record) {
                        $record->update([
                            'estado' => 'agotado',
                            'en_uso' => false, // <--- FORZAMOS EL APAGADO AQUÍ TAMBIÉN
                        ]);

                        Notification::make()->title('Reactivo marcado como agotado y desactivado')->success()->send();
                    }),
            ])
            ->bulkActions([

            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListReactivos::route('/'),
            'create' => Pages\CreateReactivo::route('/create'),
            'edit' => Pages\EditReactivo::route('/{record}/edit'),
        ];
    }
}