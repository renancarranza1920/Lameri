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
                                ->getOptionLabelFromRecordUsing(fn ($record) => "{$record->NumeroExp} - {$record->nombre} {$record->apellido}")
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
                        ]),

                    // Paso 2: Detalles de Orden
                    Step::make("Orden")
                        ->schema([
                        Tabs::make('Detalles de Orden')
                         ->tabs([
                             Tabs\Tab::make('Perfiles')
                                ->schema([
                                    Select::make('perfil_seleccionado')
                                     ->label('Buscar Perfil')
                                     ->options(\App\Models\Perfil::pluck('nombre', 'id')->toArray())
                                     ->searchable()
                                     ->preload(),

                        Repeater::make('perfiles_seleccionados')
                            ->label('Perfiles añadidos')
                            ->schema([
                                Select::make('perfil_id')
                                    ->label('Perfil')
                                    ->options(\App\Models\Perfil::pluck('nombre', 'id'))
                                    ->disabled()
                            ])
                            ->createItemButtonLabel('Añadir Perfil')
                            ->default([])
                            ->disabled(),
            ]),

        // TAB: EXÁMENES
        Tabs\Tab::make('Exámenes')
            ->schema([
                Select::make('examen_seleccionado')
                    ->label('Buscar Examen')
                    ->options(\App\Models\Examen::pluck('nombre', 'id')->toArray())
                    ->searchable()
                    ->preload(),

                Repeater::make('examenes_seleccionados')
                    ->label('Exámenes añadidos')
                    ->schema([
                        Select::make('examen_id')
                            ->label('Examen')
                            ->options(\App\Models\Examen::pluck('nombre', 'id'))
                            ->disabled()
                    ])
                    ->createItemButtonLabel('Añadir Examen')
                    ->default([])
                    ->disabled(),
            ])
                                ])->columnSpanFull(),
                        ])   
                ])
                ->columnSpanFull(),
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
                    ->getStateUsing(fn ($record) => $record->cliente->nombre . ' ' . $record->cliente->apellido)
                    ->searchable(),

                Tables\Columns\TextColumn::make('fecha')
                    ->label('Fecha')
                    ->date()
                    ->sortable(),

                Tables\Columns\TextColumn::make('descripcion')
                    ->label('Descripción')
                    ->limit(50),
            ])
            ->filters([])
            ->actions([
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
