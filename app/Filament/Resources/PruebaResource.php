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
use Filament\Forms\Components\Fieldset; // <-- AÑADIR ESTE IMPORT
use Filament\Forms\Components\Grid;     // <-- AÑADIR ESTE IMPORT
use Filament\Forms\Components\TextInput;
use Illuminate\Database\Eloquent\Builder;

class PruebaResource extends Resource
{
    protected static ?string $model = Prueba::class;
    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $pluralModelLabel = 'Pruebas';
    protected static ?string $navigationGroup = 'Catálogos de Laboratorio';

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
                                            ->requiredWith('nombre') // Esto está perfecto
                                            ->searchable()->preload(),

                                        Forms\Components\Select::make('tipo_prueba_id')
                                            ->label('Tipo de Prueba')
                                            ->relationship('tipoPrueba', 'nombre')
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
                                                // --- ¡CAMBIO CLAVE DE VALIDACIÓN! ---
                                                // Requerido solo si se llenan las filas O las columnas
                                                ->requiredWith('filas,columnas')
                                                ->searchable()->preload(),
                                            
                                            Grid::make(4)
                                                ->schema([
                                                    TextInput::make('filas')
                                                        ->label('Número de Filas')
                                                        ->numeric()->minValue(1)
                                                        // --- ¡CAMBIOS CLAVE! ---
                                                        ->nullable() // Permitir que esté vacío
                                                        ->requiredWith('examen_id_conjunto,columnas') // Requerido si llenas los otros
                                                        ->live(onBlur: true),
                                                    
                                                    TextInput::make('columnas')
                                                        ->label('Número de Columnas')
                                                        ->numeric()->minValue(1)
                                                        // --- ¡CAMBIOS CLAVE! ---
                                                        ->nullable() // Permitir que esté vacío
                                                        ->requiredWith('examen_id_conjunto,filas') // Requerido si llenas los otros
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
                // Agregamos la columna para ver el grupo, oculta por defecto
                Tables\Columns\TextColumn::make('tipo_conjunto')
                    ->label('Grupo Conjunto')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->searchable(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->headerActions([ // <-- AÑADIMOS UN BOTÓN EN LA CABECERA
                Tables\Actions\Action::make('pruebas_conjuntas')
                    ->label('Ver Pruebas en Matriz')
                    ->icon('heroicon-o-table-cells')
                    ->color('gray')
                    // Esto nos llevará a la nueva página que creamos
                    ->url(ListPruebasConjuntas::getUrl()),
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