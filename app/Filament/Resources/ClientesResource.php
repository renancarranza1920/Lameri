<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ClientesResource\Pages;
use App\Filament\Resources\ClientesResource\Pages\ViewExpediente;
use App\Models\Cliente;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Notifications\Notification;
use Filament\Tables\Actions\Action;
use Filament\Tables\Filters\SelectFilter;

class ClientesResource extends Resource
{
    protected static ?string $model = Cliente::class;

    protected static ?string $navigationGroup = 'Atención al Paciente';
    protected static ?int $navigationSort = 3;
    protected static ?string $navigationIcon = 'heroicon-o-identification';
    protected static ?string $navigationLabel = 'Clientes';
    protected static ?string $pluralModelLabel = 'Clientes';
    protected static ?string $modelLabel = 'Cliente';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                // Card principal con todas las secciones
                Forms\Components\Card::make()
                    ->schema([
                        // Sección Datos Personales
                        Forms\Components\Section::make('Datos Personales')
                            ->schema([
                                Forms\Components\Grid::make(2)
                                    ->schema([
                                        Forms\Components\TextInput::make('nombre')
                                            ->label('Nombre')
                                            ->required()
                                            ->maxLength(255),

                                        Forms\Components\TextInput::make('apellido')
                                            ->label('Apellido')
                                            ->required()
                                            ->maxLength(255),

                                        Forms\Components\DatePicker::make('fecha_nacimiento')
                                            ->label('Fecha de Nacimiento')
                                            ->required()
                                            ->placeholder('dd/mm/aaaa')
                                            ->maxDate(now()->subYears(5)),

                                            Forms\Components\Select::make('genero')
                                        ->label('Género')
                                        ->options([
                                            'Masculino' => 'Masculino',
                                            'Femenino' => 'Femenino',
                                        ])
                                        ->required(),
                                    ]),
                                    
                            ]),

                        // Sección Contacto
                        Forms\Components\Section::make('Contacto')
                            ->schema([
                                Forms\Components\Grid::make(2)
                                    ->schema([
                                        Forms\Components\TextInput::make('telefono')
                                            ->label('Teléfono')
                                            ->numeric()
                                            ->maxLength(9),

                                        Forms\Components\TextInput::make('correo')
                                            ->label('Correo Electrónico')
                                            ->email()
                                            ->maxLength(255)
                                            ->live(debounce: 500)
                                            ->afterStateUpdated(fn($state, callable $set) => $set('correo', strtolower($state))),

                                        Forms\Components\TextInput::make('direccion')
                                            ->label('Dirección')
                                            ->columnSpanFull()
                                            ->maxLength(255),
                                    ]),
                            ]),

                        Forms\Components\Toggle::make('estado')
                            ->label('Activo')
                            ->required()
                            ->default(true)
                            ->inline(false),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->recordUrl(null)
            ->columns([
                Tables\Columns\TextColumn::make('NumeroExp')
                    ->label('Expediente')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('nombre_completo')
                    ->label('Nombre')
                    ->getStateUsing(fn($record) => $record->nombre . ' ' . $record->apellido)
                    ->sortable(['nombre', 'apellido'])
                    ->searchable(['nombre', 'apellido']),

                Tables\Columns\TextColumn::make('fecha_nacimiento')
                    ->label('Edad')
                    ->getStateUsing(fn($record) => $record->fecha_nacimiento ? \Carbon\Carbon::parse($record->fecha_nacimiento)->age : '-')
                    ->sortable()
                    ->formatStateUsing(fn($state) => $state . ' años'),

                Tables\Columns\TextColumn::make('telefono')
                    ->label('Teléfono')
                    ->getStateUsing(fn($record) => $record->telefono ?? '-')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('correo')
                    ->label('Correo')
                    ->getStateUsing(fn($record) => $record->correo ?? '-')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('direccion')
                    ->label('Dirección')
                    ->getStateUsing(fn($record) => $record->direccion ?? '-')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('estado')
                    ->label('Estado')
                    ->formatStateUsing(fn($state) => $state ? '✅ Activo' : '❌ Inactivo')
                    ->badge()
                    ->color(fn($state) => $state ? 'success' : 'danger'),
            ])
            ->filters([
                SelectFilter::make('estado')
                    ->label('Estado')
                    ->options([
                        '1' => 'Activos',
                        '0' => 'Inactivos',
                    ])
                    ->attribute('estado')
                    ->default(null),
            ])
            ->actions([
                Tables\Actions\EditAction::make()->visible(fn () => auth()->user()->can('update_clientes')),

                Action::make('ver-modal')
                    ->label('Ver')
                    ->icon('heroicon-s-eye')
                    ->modalHeading('Detalle del Cliente')
                    ->color('gray')
                    ->modalWidth('lg')
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Cerrar')
                    ->form([
                        Forms\Components\TextInput::make('nombre')
                            ->label('Nombre')
                            ->disabled()
                            ->default(fn($record) => $record->nombre),

                        Forms\Components\TextInput::make('apellido')
                            ->label('Apellido')
                            ->disabled()
                            ->default(fn($record) => $record->apellido),

                        Forms\Components\TextInput::make('telefono')
                            ->label('Teléfono')
                            ->disabled()
                            ->default(fn($record) => $record->telefono),

                        Forms\Components\TextInput::make('correo')
                            ->label('Correo Electrónico')
                            ->disabled()
                            ->default(fn($record) => $record->correo),

                        Forms\Components\TextInput::make('direccion')
                            ->label('Dirección')
                            ->disabled()
                            ->default(fn($record) => $record->direccion),

                        Forms\Components\TextInput::make('fecha_nacimiento')
                            ->label('Fecha de Nacimiento')
                            ->disabled()
                            ->default(fn($record) => $record->fecha_nacimiento),

                    ]),

                Action::make('cambiar_estado')
                    ->label(fn($record) => $record->estado ? 'Dar de baja' : 'Dar de alta')
                    ->icon(fn($record) => $record->estado ? 'heroicon-o-x-circle' : 'heroicon-o-check-circle')
                    ->color(fn($record) => $record->estado ? 'danger' : 'success')
                    ->tooltip(fn($record) => $record->estado ? 'Dar de baja' : 'Dar de alta')
                    ->requiresConfirmation()
                    ->action(function ($record) {
                        $record->estado = !$record->estado;
                        $record->save();
                        Notification::make()
                            ->title($record->estado ? 'Cliente activado' : 'Cliente desactivado')
                            ->success()
                            ->send();
                    })
                    ->iconButton(),

                    Action::make('expediente')
                    ->label('Expediente')
                    ->icon('heroicon-o-folder-open') // Un ícono adecuado
                    ->color('info') // Color del botón
                    ->url(fn (Cliente $record): string => ClientesResource::getUrl('expediente', ['record' => $record->id]))

            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    
                ]),
            ]);
            
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListClientes::route('/'),
            'create' => Pages\CreateClientes::route('/create'),
            'edit' => Pages\EditClientes::route('/{record}/edit'),
            'expediente' => Pages\Expediente::route('/{record}/expediente'),
        ];
    }

}
