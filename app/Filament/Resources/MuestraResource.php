<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MuestraResource\Pages;
use App\Models\Muestra;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class MuestraResource extends Resource
{
    protected static ?string $model = Muestra::class;
    
    // Esta línea oculta el resource del menú de navegación.
    protected static bool $shouldRegisterNavigation = false;

    protected static ?string $navigationIcon = 'heroicon-o-beaker';
    protected static ?string $navigationLabel = 'Muestras Biológicas';
    protected static ?string $pluralModelLabel = 'Muestras Biológicas';
    protected static ?string $modelLabel = 'Muestra Biológica';
    protected static ?string $navigationGroup = 'Catálogos';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('nombre')
                    ->label('Nombre de la Muestra Biológica')
                    ->placeholder('Ej: Sangre, Orina, Suero, Plasma, Hisopo...')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->maxLength(255),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nombre')->searchable()->sortable(),
            ]);
    }
    
    public static function getPages(): array
    {
        return [
            'index' => Pages\ListMuestras::route('/'),
        ];
    }
}