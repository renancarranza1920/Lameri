<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ReactivoResource\Pages;
use App\Models\Reactivo;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ReactivoResource extends Resource
{
    protected static ?string $model = Reactivo::class;
    protected static ?string $navigationIcon = 'heroicon-o-beaker';
    protected static ?string $navigationGroup = 'Catálogos de Laboratorio';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Card::make()->schema([
                Forms\Components\Select::make('prueba_id')->relationship('prueba', 'nombre')->required()->searchable()->preload(),
                Forms\Components\TextInput::make('nombre')->required()->maxLength(255),
                Forms\Components\TextInput::make('lote'),
                Forms\Components\DatePicker::make('fecha_caducidad'),
                Forms\Components\Toggle::make('En uso')->default(true),
                Forms\Components\Textarea::make('descripcion')->columnSpanFull(),
            ])
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nombre')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('prueba.nombre')->badge()->searchable()->sortable(),
                Tables\Columns\TextColumn::make('lote')->searchable()->sortable(),
                Tables\Columns\IconColumn::make('is_active')->label('Activo')->boolean(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('gestionarReferencias')
                    ->label('Gestionar Referencias')
                    ->icon('heroicon-o-cog-6-tooth')
                    ->color('gray')
                    ->url(fn (Reactivo $record): string => static::getUrl('gestionar', ['record' => $record])),
            ]);
    }
    
    public static function getPages(): array
    {
        return [
            'index' => Pages\ListReactivos::route('/'),
            'create' => Pages\CreateReactivo::route('/create'),
            'edit' => Pages\EditReactivo::route('/{record}/edit'),
            'gestionar' => Pages\GestionarValoresReferencia::route('/{record}/gestionar'),
        ];
    }    
}