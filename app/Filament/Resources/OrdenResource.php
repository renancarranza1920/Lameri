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




class OrdenResource extends Resource
{
    protected static ?string $model = Orden::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-check';
    protected static ?string $navigationLabel = 'Órdenes';
    protected static ?string $modelLabel = 'Orden';
    protected static ?string $pluralModelLabel = 'Órdenes';

    public static function form(Form $form): Form
    {
        $updateProductos = function (Get $get, Set $set) {
            $productos = [];

            foreach ($get('perfiles_seleccionados') ?? [] as $item) {
                $perfil = \App\Models\Perfil::find($item['perfil_seleccionado'] ?? null);
                if ($perfil) {
                    $productos[] = [
                        'tipo' => 'perfil',
                        'id' => $perfil->id,
                        'nombre' => $perfil->nombre,
                        'precio' => $perfil->precio,
                    ];
                }
            }

            foreach ($get('examenes_seleccionados') ?? [] as $item) {
                $examen = \App\Models\Examen::find($item['examen_seleccionado'] ?? null);
                if ($examen) {
                    $productos[] = [
                        'tipo' => 'examen',
                        'id' => $examen->id,
                        'nombre' => $examen->nombre,
                        'precio' => $examen->precio,
                    ];
                }
            }

            $set('productos', $productos);

            $total = array_sum(array_column($productos, 'precio'));
            $set('total', $total);
            Log::info('Actualizando productos:', $productos);


        };

        
        return $form->schema([

            Hidden::make('productos')
                ->default([])
                ->dehydrated(true),

            Hidden::make('cliente_id')
            ->default(fn () => request()->get('cliente_id')) // o puedes poner un valor fijo si es necesario
            ->required()
            ->dehydrated(true),

            Hidden::make('total')
            ->default(0)
            ->dehydrated(true), // Importante para que se mande al backend


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
                                ->maxLength(500)
                                ->columnSpanFull()
                                ->placeholder('Escribe aquí las observaciones del cliente'),
                        ]),

                    // Paso 2: Detalles de Orden
                    Step::make("Orden")
                        ->schema([
                            Tabs::make('Detalles de Orden')
                                ->tabs([
                                    Tabs\Tab::make('Perfiles')
                                        ->schema([

                                            Repeater::make('perfiles_seleccionados')
                                                ->schema([
                                                    Forms\Components\Grid::make(2)
                                                        ->columnSpanFull()
                                                        ->schema([
                                                            Select::make('perfil_seleccionado')
                                                                ->label('Buscar Perfil')
                                                                ->options(\App\Models\Perfil::pluck('nombre', 'id')->toArray())
                                                                ->searchable()
                                                                ->preload()
                                                                ->reactive()
                                                                ->afterStateUpdated(function ($state, Set $set, Get $get) use ($updateProductos) {
                                                                    if ($state === null) {
                                                                        // Se eliminó la selección
                                                                        $set('precio', null);
                                                                        $set('preciot', null);
                                                                        $set('id', null);
                                                                    } else {
                                                                        // Se seleccionó un elemento
                                                                        $perfil = \App\Models\Perfil::find($state);
                                                                        $set('precio', $perfil?->precio ?? 0);
                                                                        $set('preciot', $perfil?->precio ?? 0);
                                                                        $set('id', $perfil->id);
                                                                    }
                                                                }),

                                                            Forms\Components\TextInput::make('precio')
                                                                ->label('Precio')
                                                                ->disabled(),

                                                            Hidden::make('tipo')->default('perfil'),
                                                            Hidden::make('id')->default(null),
                                                            Hidden::make('preciot')->default(null),
                                                            
                                                            
                                                        ]),
                                                ])
                                                ->reorderable(false)
                                                ->addActionLabel('Añadir Perfil')
                                                ->reorderableWithButtons(false)
                                                ->minItems(1)
                                                ->required()
                                                ->reactive()
                                                ->afterStateUpdated(function (Get $get, Set $set) use ($updateProductos) {
                                                    $updateProductos($get, $set);
                                                })


                                        ]),

                                    // TAB: EXÁMENES
                                    Tabs\Tab::make('Exámenes')
                                        ->schema([
                                            Repeater::make('examenes_seleccionados')
                                                ->schema([
                                                    Forms\Components\Grid::make(2)
                                                        ->columnSpanFull()
                                                        ->schema([
                                                            Select::make('examen_seleccionado')
                                                                ->label('Buscar Examen')
                                                                ->options(\App\Models\Examen::pluck('nombre', 'id')->toArray())
                                                                ->searchable()
                                                                ->preload()
                                                                ->reactive()
                                                                ->afterStateUpdated(function ($state, Set $set, Get $get) use ($updateProductos) {
                                                                    if ($state === null) {
                                                                        // Se eliminó la selección
                                                                        $set('precio', null);
                                                                        $set('preciot', null);
                                                                        $set('id', null);
                                                                    } else {
                                                                        // Se seleccionó un elemento
                                                                        $examen = \App\Models\Examen::find($state);
                                                                        $set('precio', $examen?->precio ?? 0);
                                                                        $set('preciot', $examen?->precio ?? 0);
                                                                        $set('id', $examen->id);
                                                                    }
                                                                }),

                                                            Forms\Components\TextInput::make('precio')
                                                                ->label('Precio')
                                                                ->disabled(),

                                                            Hidden::make('tipo')->default('examen'),
                                                            Hidden::make('id')->default(null),
                                                            Hidden::make('preciot')->default(null),
                                                        ]),
                                                ])
                                                ->reorderable(false)
                                                ->addActionLabel('Añadir Examen')
                                                ->reorderableWithButtons(false)
                                                ->minItems(1)
                                                ->required()
                                                ->reactive()
                                                ->afterStateUpdated(function (Get $get, Set $set) use ($updateProductos) {
                                                    $updateProductos($get, $set);
                                                }),

                                        ])
                                ])
                                            ]),
                     
                    // Paso 3: Confirmación

                    Step::make('Resumen')
                        ->schema([

                            // Resumen del cliente
                            Forms\Components\Placeholder::make('cliente_resumen')
                                ->label('Cliente seleccionado')
                                ->content(function (Get $get) {
                                    $clienteId = $get('cliente_id');
                                    $cliente = $clienteId ? Cliente::find($clienteId) : null;

                                    return $cliente
                                        ? "{$cliente->NumeroExp} - {$cliente->nombre} {$cliente->apellido}"
                                        : 'No se ha seleccionado un cliente.';
                                }),

                            // Resumen de productos
                            Forms\Components\Repeater::make('productos')
                                ->label('Resumen de productos seleccionados')
                                ->schema([

                                    Forms\Components\TextInput::make('nombre')
                                        ->label('Nombre')
                                        ->disabled()
                                        ->columnSpan(1),

                                    Forms\Components\TextInput::make('precio')
                                        ->label('Precio')
                                        ->disabled()
                                        ->columnSpan(1),
                                ])
                                ->columns(2) // Establece el número de columnas del repeater
                                ->disabled() // Modo solo lectura
                                ->columnSpanFull()
                            ,

                            // Total
                            Forms\Components\Placeholder::make('total')
                                ->label('Total a pagar')
                                ->content(function (Get $get) {
                                    $productos = $get('productos') ?? [];
                                    $total = array_sum(array_column($productos, 'precio'));
                                    
                                    return '$' . number_format($total, 2);
                                }),

                        ])
                        
                ])
                


        ]);


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
                    ->formatStateUsing(fn (string $state) => match ($state) {
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
                    ->modalSubheading(fn ($record) => 'Orden N.º ' . $record->id)
                    ->modalButton('Completar orden') // Botón personalizado
                    ->modalWidth('md')
                    ->modalContent(fn ($record) => view('filament.modals.ver-orden', ['record' => $record]))
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
