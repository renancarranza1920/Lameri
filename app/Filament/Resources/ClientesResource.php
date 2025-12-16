<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ClientesResource\Pages;
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
                Forms\Components\Card::make()
                    ->schema([
                        Forms\Components\Section::make('Datos Personales')
                            ->schema([
                                Forms\Components\Grid::make(2)
                                    ->schema([
                                        Forms\Components\TextInput::make('nombre')
                                            ->label('Nombre')
                                            ->required()
                                            ->validationMessages([
                                                'required' => 'El nombre es obligatorio.',
                                            ])
                                            ->maxLength(255),

                                        Forms\Components\TextInput::make('apellido')
                                            ->label('Apellido')
                                            ->required()
                                            ->validationMessages([
                                                'required' => 'El apellido es obligatorio.',
                                            ])
                                            ->maxLength(255),

                                        Forms\Components\DatePicker::make('fecha_nacimiento')
                                            ->label('Fecha de Nacimiento')
                                            ->required()
                                            ->validationMessages([
                                                'required' => 'La fecha de nacimiento es obligatoria.',
                                            ])
                                            ->placeholder('dd/mm/aaaa')
                                            ->maxDate(now()->subYears(5)),

                                        Forms\Components\Select::make('genero')
                                            ->label('Género')
                                            ->options([
                                                'Masculino' => 'Masculino',
                                                'Femenino' => 'Femenino',
                                            ])
                                            ->required()
                                            ->validationMessages([
                                                'required' => 'Por favor, selecciona un género.',
                                            ]),
                                    ]),
                            ]),

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

                        /* AQUÍ VA EL TOGGLE CON ESTADO TEXTO */
                        Forms\Components\Toggle::make('estado')
                            ->label('Estado')
                            ->inline(false)
                            ->onColor('success')
                            ->offColor('danger')
                            ->onIcon('heroicon-o-check')
                            ->offIcon('heroicon-o-x-mark')
                            ->default('Activo')
                            ->formatStateUsing(fn ($state) => $state ? 'Activo' : 'Inactivo')
                            ->dehydrateStateUsing(fn ($state) => $state ? 'Activo' : 'Inactivo')
                            ->afterStateHydrated(function ($state, $set) {
                                $set('estado', $state === 'Activo');
                            }),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('nombre')
            ->columns([
                Tables\Columns\TextColumn::make('NumeroExp')
                    ->label('No. Expediente')
                    ->weight('bold')
                    ->copyable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('nombre_completo')
                    ->label('Paciente')
                    ->getStateUsing(fn (Cliente $record) => $record->nombre . ' ' . $record->apellido)
                    ->sortable(['nombre']),

                Tables\Columns\TextColumn::make('fecha_nacimiento')
                    ->label('Edad')
                    ->date('d/m/Y')
                    ->description(fn (Cliente $record) => \Carbon\Carbon::parse($record->fecha_nacimiento)->age . ' años'),

                Tables\Columns\TextColumn::make('telefono')
                    ->label('Teléfono')
                    ->icon('heroicon-m-phone'),

                Tables\Columns\TextColumn::make('correo')
                    ->label('Correo')
                    ->toggleable(isToggledHiddenByDefault: true),

                /* ESTADO COMO TEXTO */
                Tables\Columns\TextColumn::make('estado')
                    ->badge()
                    ->color(fn ($state) => $state === 'Activo' ? 'success' : 'danger'),
            ])
            ->filters([
                /* FILTRO USANDO TEXTO */
                SelectFilter::make('estado')
                    ->label('Estado')
                    ->options([
                        'Activo' => 'Activo',
                        'Inactivo' => 'Inactivo',
                    ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make()->visible(fn () => auth()->user()->can('update_clientes')),

                Action::make('ver-modal')
                    ->label('Ver')
                    ->icon('heroicon-s-eye')
                    ->visible(fn () => auth()->user()->can('ver_detalle_clientes'))
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

                /* CAMBIO DE ESTADO TEXTO */
                Action::make('cambiar_estado')
                    ->label(fn($record) => $record->estado === 'Activo' ? 'Dar de baja' : 'Dar de alta')
                    ->icon(fn($record) => $record->estado === 'Activo' ? 'heroicon-o-x-circle' : 'heroicon-o-check-circle')
                    ->color(fn($record) => $record->estado === 'Activo' ? 'danger' : 'success')
                    ->visible(fn () => auth()->user()->can('cambiar_estado_clientes'))
                    ->tooltip(fn($record) => $record->estado === 'Activo' ? 'Dar de baja' : 'Dar de alta')
                    ->requiresConfirmation()
                    ->action(function ($record) {
                        $record->estado = $record->estado === 'Activo' ? 'Inactivo' : 'Activo';
                        $record->save();

                        Notification::make()
                            ->title($record->estado === 'Activo' ? 'Cliente activado' : 'Cliente desactivado')
                            ->success()
                            ->send();
                    })
                    ->iconButton(),

                Action::make('expediente')
                    ->label('Expediente')
                    ->icon('heroicon-o-folder-open')
                    ->color('info')
                    ->visible(fn () => auth()->user()->can('ver_expediente_clientes'))
                    ->url(fn (Cliente $record): string => ClientesResource::getUrl('expediente', ['record' => $record->id])),
            ])
            ->bulkActions([
                
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
