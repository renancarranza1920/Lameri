<?php

namespace App\Filament\Resources\ExamenResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class PruebasRelationManager extends RelationManager
{
    protected static string $relationship = 'pruebas';

    protected static ?string $recordTitleAttribute = 'nombre';
    protected static ?string $modelLabel = 'Prueba';
    protected static ?string $pluralModelLabel = 'Pruebas';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('nombre')
                    ->required()
                    ->maxLength(255),
                // El examen_id se asigna automáticamente
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nombre')->searchable(),
                Tables\Columns\TextColumn::make('tipo_conjunto')
                    ->label('Tipo')
                    ->formatStateUsing(fn ($state) => is_null($state) ? 'Unitaria' : 'Matriz')
                    ->badge()
                    ->color(fn ($state) => is_null($state) ? 'success' : 'info'),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}