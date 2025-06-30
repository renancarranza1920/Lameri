<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OrdenResource\Pages;
use App\Models\Orden;
use App\Models\Cliente;
use Filament\Forms;
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
    protected static ?string $modelLabel = 'Orden';
    protected static ?string $pluralModelLabel = 'Órdenes';

    public static function form(Form $form): Form
    {

        return $form->schema([
            Forms\Components\Wizard::make()
                ->schema([
                    // Paso 1: Cliente
                    Step::make('Cliente')
                        ->schema([
                            Forms\Components\Select::make('cliente_id')
                                ->label('Seleccionar o agregar cliente')
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
                        ]),

                    // Paso 2: Detalles de Orden
                    Step::make("Orden")
                        ->schema([

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
                                                                ->options(\App\Models\Perfil::pluck('nombre', 'id')->toArray())
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
                                                                    }

                                                                })
                                                            ,

                                                            Forms\Components\TextInput::make('precio')
                                                                ->label('Precio')
                                                                 ->dehydrated(true)
                                                                ->disabled(),

                                                            Hidden::make('tipo')->default('examen'),
                                                             Hidden::make('precio_hidden')
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
                                        ])




                                ])
                        ]),

                    // Paso 3: Confirmación

                    Step::make('Resumen')
                        ->schema([
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
                        ])





                ])->columnSpanFull(),



        ])
        ;


    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('ID')
                    ->sortable(),

                Tables\Columns\TextColumn::make('cliente.NumeroExp')
                    ->label('Expediente')
                    ->searchable(),

                Tables\Columns\TextColumn::make('cliente.nombre')
                    ->label('Nombre')
                    ->getStateUsing(fn($record) => $record->cliente->nombre . ' ' . $record->cliente->apellido)
                    ->searchable(),

                Tables\Columns\TextColumn::make('fecha')
                    ->label('Fecha')
                    ->date()
                    ->sortable(),

                Tables\Columns\TextColumn::make('total')
                    ->label('Total')
                    ->money('USD', true)
                    ->sortable(),

                Tables\Columns\TextColumn::make('estado')
                    ->formatStateUsing(fn(string $state) => match ($state) {
                        'pendiente' => 'Pendiente',
                        'finalizado' => 'Finalizado',
                        'cancelado' => 'Cancelado',
                        'en_proceso' => 'En Proceso',
                        default => ucfirst($state),
                    })

                    ->sortable(),

            ])
            ->filters([])
            ->actions([
                Tables\Actions\Action::make('ver')
                    ->label('Ver')
                    ->icon('heroicon-o-eye')
                    ->modalHeading('Detalles de la Orden')
                    ->modalSubheading(fn($record) => 'Orden N.º ' . $record->id)
                    ->modalButton('Completar orden') // Botón personalizado
                    ->modalWidth('md')
                    ->modalContent(fn($record) => view('filament.modals.ver-orden', ['record' => $record]))
                    ->action(function ($record) {
                        $record->estado = 'finalizado';
                        $record->save();

                        Notification::make()
                            ->title('Orden completada con éxito')
                            ->success()
                            ->send();
                    }),

                Tables\Actions\EditAction::make()
                    ->label('Editar')
                    ->icon('heroicon-o-pencil'),


            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOrdens::route('/'),
            'create' => Pages\CreateOrden::route('/create'),
            'edit' => Pages\EditOrden::route('/{record}/edit'),
        ];
    }
}
