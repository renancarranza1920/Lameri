<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PruebaResource\Pages;
use App\Filament\Resources\PruebaResource\Pages\ListPruebasConjuntas;
use App\Models\Prueba;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Components\Tabs; 
use Filament\Forms\Components\Placeholder; 
use Filament\Forms\Get;
use Illuminate\Support\HtmlString;
use Filament\Forms\Components\Fieldset;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\TextInput;
use Illuminate\Database\Eloquent\Builder;
use Filament\Notifications\Notification; // <--- IMPORTANTE: Agregamos esto

class PruebaResource extends Resource
{
    protected static ?string $model = Prueba::class;
    protected static ?string $navigationGroup = 'Gestión de Laboratorio';
    protected static ?int $navigationSort = 3;
    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $pluralModelLabel = 'Pruebas';

    public static function form(Form $form): Form
      {
        return $form
            ->schema([
                Tabs::make('Tipo de Creación')
                    ->tabs([
                        Tabs\Tab::make('Prueba Unitaria')
                            ->schema([
                                Forms\Components\Card::make()
                                    ->schema([
                                        Forms\Components\TextInput::make('nombre')
                                            ->label('Nombre de la Prueba')
                                            ->maxLength(255),
                                        
                                        Forms\Components\Select::make('examen_id')
                                            ->label('Examen al que Pertenece')
                                            ->relationship('examen', 'nombre')
                                            ->requiredWith('nombre') 
                                            ->searchable()->preload(),

                                        Forms\Components\Select::make('tipo_prueba_id')
                                            ->label('Tipo de Prueba')
                                            ->relationship('tipoPrueba', 'nombre')
                                            ->createOptionAction(fn($action) => $action->visible(true))
                                            ->createOptionForm([
                                                Forms\Components\TextInput::make('nombre')
                                                    ->label('Nombre del Tipo de Prueba')
                                                    ->required()
                                                    ->maxLength(255),
                                            ])
                                            ->searchable()->preload()->helperText('Opcional.'),
                                    ])
                            ]),
                        
                        Tabs\Tab::make('Pruebas Conjuntas (Matriz)')
                            ->schema([
                                Forms\Components\Card::make()
                                    ->schema(function (Get $get): array {
                                        $staticComponents = [
                                            Forms\Components\Select::make('examen_id_conjunto')
                                                ->label('Examen para la Matriz')
                                                ->relationship('examen', 'nombre')
                                                ->requiredWith('filas,columnas')
                                                ->searchable()->preload(),
                                            
                                            Grid::make(4)
                                                ->schema([
                                                    TextInput::make('filas')
                                                        ->label('Número de Filas')
                                                        ->numeric()->minValue(1)
                                                        ->nullable()
                                                        ->requiredWith('examen_id_conjunto,columnas')
                                                        ->live(onBlur: true),
                                                    
                                                    TextInput::make('columnas')
                                                        ->label('Número de Columnas')
                                                        ->numeric()->minValue(1)
                                                        ->nullable()
                                                        ->requiredWith('examen_id_conjunto,filas')
                                                        ->live(onBlur: true),
                                                ]),
                                        ];

                                        $filas = (int) $get('filas', 0);
                                        $columnas = (int) $get('columnas', 0);
                                        $dynamicComponents = [];

                                        if ($filas > 0 && $columnas > 0) {
                                            $headerComponents = [];
                                            $headerComponents[] = Placeholder::make('top_left_corner')->label('')->content(new HtmlString('&nbsp;'));
                                            for ($c = 1; $c <= $columnas; $c++) {
                                                $headerComponents[] = TextInput::make("nombres_columnas.{$c}")->label("Columna {$c}")->placeholder("Nombre Columna {$c}");
                                            }
                                            $matrixComponents = [];
                                            $matrixComponents[] = Grid::make($columnas + 1)->schema($headerComponents);

                                            for ($f = 1; $f <= $filas; $f++) {
                                                $matrixComponents[] = Grid::make($columnas + 1)
                                                    ->schema([
                                                        TextInput::make("nombres_filas.{$f}")->label("Fila {$f}")->placeholder("Nombre Fila {$f}")->columnSpan(1),
                                                    ]);
                                            }
                                            $dynamicComponents[] = Forms\Components\Card::make()
                                                ->schema($matrixComponents)
                                                ->columnSpanFull();
                                        }
                                        return array_merge($staticComponents, $dynamicComponents);
                                    })
                            ])
                    ])->columnSpanFull()
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nombre')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('tipoPrueba.nombre')
                    ->label('Tipo de Prueba')
                    ->badge()->searchable()->sortable(),
                Tables\Columns\TextColumn::make('tipo_conjunto')
                    ->label('Grupo Conjunto')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->searchable(),
                
                // Mantenemos tu configuración de columna de estado
                Tables\Columns\TextColumn::make('estado')
                    ->label('Estado')
                    ->badge()
                    ->colors([
                        'success' => 'activo',
                        'danger' => 'inactivo',
                    ])
                    ->formatStateUsing(fn (string $state): string => ucfirst($state)) // Capitalizar primera letra
                    ->sortable(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                
                // 👇 LÓGICA COMPLETA DE ALTA/BAJA
                Tables\Actions\Action::make('cambiar_estado')
                    ->label(fn (Prueba $record) => $record->estado === 'activo' ? 'Dar de baja' : 'Dar de alta')
                    ->icon(fn (Prueba $record) => $record->estado === 'activo' ? 'heroicon-o-x-circle' : 'heroicon-o-check-circle')
                    ->color(fn (Prueba $record) => $record->estado === 'activo' ? 'danger' : 'success')
                    ->tooltip(fn (Prueba $record) => $record->estado === 'activo' ? 'Desactivar Prueba' : 'Activar Prueba')
                    
                    ->requiresConfirmation()
                    ->modalHeading(fn (Prueba $record) => $record->estado === 'activo' ? '¿Desactivar Prueba?' : '¿Activar Prueba?')
                    ->modalDescription('¿Estás seguro de que deseas cambiar el estado de este registro?')
                    
                    ->action(function (Prueba $record) {
                        // Cambiamos el estado (asumiendo que en DB es string 'activo'/'inactivo')
                        $record->estado = $record->estado === 'activo' ? 'inactivo' : 'activo';
                        $record->save();
                        
                        Notification::make()
                            ->title($record->estado === 'activo' ? 'Prueba activada' : 'Prueba desactivada')
                            ->success()
                            ->send();
                    })
                    ->iconButton(),
            ])
            ->headerActions([
                Tables\Actions\Action::make('pruebas_conjuntas')
                    ->label('Ver Pruebas en Matriz')
                    ->visible(fn () => auth()->user()->can('view_any_prueba')) 
                    ->icon('heroicon-o-table-cells')
                    ->color('gray')
                    ->url(ListPruebasConjuntas::getUrl()),

                Tables\Actions\Action::make('gestionar_tipos')
                    ->label('Tipos de Prueba')
                    ->url(TipoPruebaResource::getUrl('index'))
                    ->color('gray'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
    
    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPruebas::route('/'),
            'create' => Pages\CreatePrueba::route('/create'),
            'edit' => Pages\EditPrueba::route('/{record}/edit'),
            'matrices' => ListPruebasConjuntas::route('/matrices'),
            'edit-conjunta' => Pages\EditPruebaConjunta::route('/{record}/edit-conjunta'),
        ];
    }
      public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->whereNull('tipo_conjunto');
    }
}