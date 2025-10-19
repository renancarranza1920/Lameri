<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CodigoResource\Pages;
use App\Models\Codigo;
use Filament\Forms;
use Filament\Forms\Components\Hidden;
use Filament\Tables;
use Filament\Resources\Resource;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\ToggleButtons;

class CodigoResource extends Resource
{
    protected static ?string $model = Codigo::class;

    protected static ?string $navigationIcon = 'heroicon-o-ticket';
    protected static ?string $navigationLabel = 'Códigos';
    protected static ?string $pluralLabel = 'Códigos';

    public static function form(Forms\Form $form): Forms\Form
    {
        return $form->schema([
            TextInput::make('codigo')
                ->required()
                ->unique(ignoreRecord: true)
                ->maxLength(50)
                ->label('Código de descuento'),

            Select::make('tipo_descuento')
                ->label('Tipo de descuento')
                ->options([
                    'porcentaje' => 'Porcentaje (%)',
                    'monto' => 'Monto fijo ($)',
                ])
                ->required()
                ->default('porcentaje'),

            TextInput::make('valor_descuento')
                ->numeric()
                ->label('Valor del descuento')
                ->required(),

            TextInput::make('limite_usos')
            ->numeric()
            ->minValue(0)
            ->default(0)
            ->label('Límite de usos')
            ->helperText('Usa 0 para indicar que es ilimitado.')
            ->required(),

            TextInput::make('usos_actuales')
                ->numeric()
                ->disabled()
                ->default(0)
                ->label('Usos actuales')
                ->hidden(),

            DatePicker::make('fecha_vencimiento')
                ->label('Fecha de vencimiento')
                ->minDate(now())
                ->helperText('Dejar vacío para no establecer vencimiento.'),
        ]);
    }

    public static function table(Tables\Table $table): Tables\Table
    {
        return $table->columns([
            TextColumn::make('codigo')->searchable()->label('Código'),
            TextColumn::make('tipo_descuento')->label('Tipo'),
            TextColumn::make('valor_descuento')
            ->label('Valor')
            ->formatStateUsing(function ($state, $record) {
                if ($record->tipo_descuento === 'porcentaje') {
                    return $state . '%';
                } elseif ($record->tipo_descuento === 'monto') {
                    return '$' . number_format($state, 2);
                }
                return $state;
            }),
            TextColumn::make('usos_actuales')->label('Usos'),

           // COLUMNA LÍMITE (USANDO PLACEHOLDER)
        TextColumn::make('limite_usos')
                ->label('Límite')
                ->formatStateUsing(fn ($state): string => 
                    // Si el valor del campo es 0 (que se guarda en la DB), muestra Ilimitado.
                    $state === 0 || $state === '0'
                        ? 'Ilimitado' 
                        : (string) $state
                )
                ->placeholder('Ilimitado'), // Muestra "Ilimitado" cuando el valor es NULL

        // COLUMNA FECHA VENCIMIENTO (USANDO PLACEHOLDER Y DATE)
        TextColumn::make('fecha_vencimiento')
            ->label('Fecha Vencimiento')
            ->date('d/m/Y') // Formato si el valor NO es NULL
            ->placeholder('Sin vencimiento'), // Muestra "Sin vencimiento" cuando el valor es NULL


            TextColumn::make('estado')
                ->badge()
                ->color(fn($state) => match ($state) {
                    'Activo' => 'success',
                    'Inactivo' => 'gray',
                    default => 'gray',
                }),
        ])->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCodigos::route('/'),
            'create' => Pages\CreateCodigo::route('/create'),
            'edit' => Pages\EditCodigo::route('/{record}/edit'),
        ];
    }
}
