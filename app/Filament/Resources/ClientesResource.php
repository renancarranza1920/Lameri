<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ClientesResource\Pages;
use App\Filament\Resources\ClientesResource\RelationManagers;
use App\Models\Cliente;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ClientesResource extends Resource
{
    protected static ?string $model = Cliente::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

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
                    ->placeholder('dd/mm/aaaa'),

                Forms\Components\TextInput::make('telefono')
                    ->maxLength(9),

                Forms\Components\TextInput::make('correo')
                    ->email()
                    ->maxLength(255),

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
                //edad y calcula la edad
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
                    ->sortable()
                    ->searchable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make() 
                    ->label('Editar')
                    ->icon('heroicon-o-pencil')
                    ->color('primary'),
                Tables\Actions\DeleteAction::make()
                    ->label('Eliminar')
                    ->icon('heroicon-o-trash')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->action(function (Cliente $record) {
                        $record->delete();
                        
                    }),
            ])
            ->bulkActions([
                //Tables\Actions\BulkActionGroup::make([
                  //  Tables\Actions\DeleteBulkAction::make(),
                //]),
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
