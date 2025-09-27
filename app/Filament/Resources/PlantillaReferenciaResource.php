<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PlantillaReferenciaResource\Pages;
use App\Models\PlantillaReferencia;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class PlantillaReferenciaResource extends Resource
{
    protected static ?string $model = PlantillaReferencia::class;
    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';
    protected static ?string $modelLabel = 'Plantilla de Referencia';
    protected static ?string $pluralModelLabel = 'Constructor de Plantillas';
    protected static ?string $navigationGroup = 'Administración';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Información de la Plantilla')->schema([
                Forms\Components\TextInput::make('nombre')->required()->maxLength(255),
                Forms\Components\Textarea::make('descripcion')->columnSpanFull(),
            ])->columns(2),
            Forms\Components\Section::make('Constructor de Campos')->schema([
                Forms\Components\Repeater::make('estructura_formulario')->label('')->schema([
                    Forms\Components\TextInput::make('nombre_campo')->label('ID del Campo')->helperText('Ej: valor_min')->required()->alphaDash(),
                    Forms\Components\TextInput::make('etiqueta')->label('Etiqueta para Mostrar')->helperText('Ej: Valor Mínimo')->required(),
                    Forms\Components\Select::make('tipo')->label('Tipo de Campo')->options(['text' => 'Texto Corto', 'number' => 'Número', 'textarea' => 'Texto Largo', 'select' => 'Lista Desplegable'])->required()->live(),
                    Forms\Components\TagsInput::make('opciones')->label('Opciones (para listas)')->helperText('Escribe una opción y presiona Enter.')->visible(fn ($get) => $get('tipo') === 'select'),
                ])->columns(2)->collapsible()->itemLabel(fn (array $state): ?string => $state['etiqueta'] ?? 'Nuevo Campo'),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nombre')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('descripcion')->limit(50),
            ])
            ->actions([Tables\Actions\EditAction::make(),]);
    }
    
    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPlantillaReferencias::route('/'),
            'create' => Pages\CreatePlantillaReferencia::route('/create'),
            'edit' => Pages\EditPlantillaReferencia::route('/{record}/edit'),
        ];
    }    
}