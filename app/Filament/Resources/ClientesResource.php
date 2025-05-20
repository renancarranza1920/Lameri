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

class ClientesResource extends Resource
{
    protected static ?string $model = Cliente::class;

    protected static ?string $navigationIcon = 'heroicon-o-identification';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([

                Forms\Components\TextInput::make('nombre')
                    ->required()
                    ->maxLength(255),

                Forms\Components\TextInput::make('apellido')
                    ->required()
                    ->maxLength(255),

                Forms\Components\DatePicker::make('fecha_nacimiento')
                    ->label('Fecha de Nacimiento')
                    ->required()
                    ->placeholder('dd/mm/aaaa')
                    ->maxDate(now()->subYears(5)),
                    

                Forms\Components\TextInput::make('telefono')
                    ->maxLength(9),

                Forms\Components\TextInput::make('correo')
                    ->email()
                    ->maxLength(255)
                    ->live(debounce: 500)
                    ->afterStateUpdated(function ($state, callable $set) {
                        $set('correo', strtolower($state));
                    }),

                Forms\Components\TextInput::make('direccion')
                    ->maxLength(255),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('NumeroExp')
                    ->label('Expediente')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('nombre_completo')
                    ->label('Nombre')
                    ->getStateUsing(fn ($record) => $record->nombre . ' ' . $record->apellido)
                    ->sortable(['nombre', 'apellido'])
                    ->searchable(['nombre', 'apellido']),

                Tables\Columns\TextColumn::make('fecha_nacimiento')
                    ->label('Edad')
                    ->getStateUsing(fn ($record) => $record->fecha_nacimiento ? \Carbon\Carbon::parse($record->fecha_nacimiento)->age : '-')
                    ->sortable()
                    ->searchable()
                    ->formatStateUsing(fn ($state) => $state . ' años'),

                Tables\Columns\TextColumn::make('telefono')
                    ->label('Teléfono')
                    ->getStateUsing(fn ($record) => $record->telefono ?? '-')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('correo')
                    ->label('Correo')
                    ->getStateUsing(fn ($record) => $record->correo ?? '-')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('direccion')
                    ->label('Dirección')
                    ->getStateUsing(fn ($record) => $record->direccion ?? '-')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('estado')
                    ->label('Estado')
                    ->formatStateUsing(function ($state) {
                        return $state
                            ? '✅'
                            : '❌';
                    })
                    ->badge() // opcional para que se vea como etiqueta
                    ->color(fn($state) => $state ? 'success' : 'danger'),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make() 
                    ->icon('heroicon-o-pencil')
                    ->color('primary'),
                    
                //accion para activar y desactivar al cliente
                 Tables\Actions\Action::make('toggleEstado')
                ->label(fn ($record) => $record->estado ? 'Dar de baja' : 'Dar de alta')
                ->icon(fn ($record) => $record->estado ? 'heroicon-o-x-circle' : 'heroicon-o-check-circle')
                ->color(fn ($record) => $record->estado ? 'danger' : 'success')
                ->tooltip(fn ($record) => $record->estado ? 'Dar de baja' : 'Dar de alta')
                ->action(function ($record) {
                    $record->estado = $record->estado ? 0 : 1;
                    $record->save();
            
                    Notification::make()
                        ->title('Estado actualizado')
                        ->body('El cliente fue ' . ($record->estado ? 'activado' : 'dado de baja') . ' correctamente.')
                        ->success()
                        ->send();
                })
                ->requiresConfirmation()
                ->iconButton(),
            ])
            ->bulkActions([
                // Puedes agregar acciones masivas si quieres
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListClientes::route('/'),
            'create' => Pages\CreateClientes::route('/create'),
            'edit' => Pages\EditClientes::route('/{record}/edit'),
        ];
    }
}