<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TipoExamenResource\Pages;
use App\Models\TipoExamen;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class TipoExamenResource extends Resource
{
    protected static ?string $model = TipoExamen::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationLabel = 'Tipo de Exámenes';

    protected static ?string $pluralModelLabel = 'Tipo de Exámenes';
    protected static ?string $modelLabel = 'Tipo de Examen';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Card::make()
                    ->schema([
                        Forms\Components\TextInput::make('nombre')
                            ->label('Nombre del Tipo de Examen')
                            ->placeholder('Ej: Hematología, Microbiología...')
                            ->required()
                            ->reactive()
                            ->afterStateUpdated(fn (string $state, callable $set) => $set('nombre', strtoupper($state)))
                            ->maxLength(255),

                        Forms\Components\Toggle::make('estado')
                            ->label('Activo')
                            ->required()
                            ->default(true)
                            ->inline(false),
                    ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nombre')
                    ->label('Nombre')
                    ->searchable(),

                Tables\Columns\IconColumn::make('estado')
                    ->label('Estado')
                    ->boolean(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Creado')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Actualizado')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
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
            'index' => Pages\ListTipoExamens::route('/'),
            'create' => Pages\CreateTipoExamen::route('/create'),
            'edit' => Pages\EditTipoExamen::route('/{record}/edit'),
        ];
    }
}
