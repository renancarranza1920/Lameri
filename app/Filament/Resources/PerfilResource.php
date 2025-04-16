<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PerfilResource\Pages;
use App\Filament\Resources\PerfilResource\RelationManagers;
use App\Models\Perfil;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class PerfilResource extends Resource
{
    protected static ?string $model = Perfil::class;
    //definiendo el nombre de la opción en el menu
    protected static ?string $navigationLabel = 'Perfiles';
    //nombre plural
    protected static ?string $pluralModelLabel = 'Perfiles';
    //nombre singular
    protected static ?string $modelLabel = 'Perfil';
    protected static ?string $navigationIcon = 'heroicon-o-beaker';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('nombre')
                    ->required()
                    ->maxLength(255),
    
                Forms\Components\TextInput::make('precio')
                    ->numeric()
                    ->prefix('$')
                    ->required(),
    
                Forms\Components\Toggle::make('estado')
                    ->label('Activo')
                    ->default(true),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nombre')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('precio')->money('USD'),
                Tables\Columns\IconColumn::make('estado')
                    ->boolean()
                    ->label('Activo'),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
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
            'index' => Pages\ListPerfils::route('/'),
            'create' => Pages\CreatePerfil::route('/create'),
            'edit' => Pages\EditPerfil::route('/{record}/edit'),
        ];
    }
}
