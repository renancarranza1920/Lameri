<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PruebaResource\Pages;
use App\Models\Prueba;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class PruebaResource extends Resource
{
    protected static ?string $model = Prueba::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $modelLabel = 'Prueba';
    protected static ?string $pluralModelLabel = 'Pruebas';
    protected static ?string $navigationGroup = 'Catálogos de Laboratorio';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                // 👇 1. DISEÑO UNIFICADO CON CARD
                Forms\Components\Card::make()
                    ->schema([
                        Forms\Components\TextInput::make('nombre')
                            ->label('Nombre de la Prueba')
                            ->placeholder('Ej: Glóbulos Rojos, Creatinina Sérica')
                            ->required()
                            ->maxLength(255),
                          Forms\Components\Select::make('examen_id')
                ->label('Examen al que Pertenece')
                ->relationship('examen', 'nombre')
                ->searchable()
                ->preload()
                ->required(),
                        // 👇 2. CAMPO SELECT CON CREACIÓN INTEGRADA
                        Forms\Components\Select::make('tipo_prueba_id')
                            ->label('Tipo de Prueba')
                            ->relationship('tipoPrueba', 'nombre')
                            ->searchable()
                            ->preload()
                            ->helperText('Opcional: puedes dejarlo en blanco o crear uno nuevo.')
                            // Permite crear un nuevo Tipo de Prueba desde un modal
                            ->createOptionForm([
                                Forms\Components\TextInput::make('nombre')
                                    ->label('Nombre del Nuevo Tipo de Prueba')
                                    ->placeholder('Ej: Inmunología')
                                    ->required()
                                    ->unique('tipos_pruebas', 'nombre'),
                            ])
                            ->createOptionAction(function (Forms\Components\Actions\Action $action) {
                                return $action
                                    ->modalHeading('Añadir Nuevo Tipo de Prueba')
                                    ->modalSubmitActionLabel('Crear Tipo de Prueba');
                            }),
                    ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nombre')->searchable()->sortable(),
                // 👇 3. TIPO DE PRUEBA MOSTRADO COMO INSIGNIA (BADGE) EN LA TABLA
                Tables\Columns\TextColumn::make('tipoPrueba.nombre')
                    ->label('Tipo de Prueba')
                    ->badge() // <-- ¡Esta es la mejora visual que querías!
                    ->searchable()
                    ->sortable(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
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
        ];
    }    
}