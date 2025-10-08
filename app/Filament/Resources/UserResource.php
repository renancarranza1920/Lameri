<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Components\Card;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Spatie\Permission\Models\Role as SpatieRole;
use Spatie\Permission\Models\Role;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';
    protected static ?string $navigationGroup = 'Administración';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Card::make()
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Nombre')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\TextInput::make('email')
                            ->label('Correo Electrónico')
                            ->required()
                            ->email()
                            ->maxLength(255),

                        Forms\Components\TextInput::make('password')
                            ->label('Contraseña')
                            ->password()
                            ->required(fn($livewire) => $livewire instanceof Pages\CreateUser)
                            ->maxLength(255)
                            ->dehydrated(fn($state) => filled($state))
                            ->hiddenOn('edit'),

                        Forms\Components\Select::make('roles')
                            ->label('Rol')
                            ->relationship('roles', 'name') // Filament maneja la relación y muestra el valor actual
                            ->options(\Spatie\Permission\Models\Role::pluck('name', 'id'))
                            ->required()
                            ->preload()
                            ->multiple(false)
                            ->searchable(),
                    ])
                    ->columns(1)
            ]);
    }


    public static function table(Table $table): Table
    {
        return $table
            ->recordUrl(null)
            ->columns([
                Tables\Columns\TextColumn::make('id')->sortable(),
                Tables\Columns\TextColumn::make('name')
                    ->label('Nombre')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('email')
                    ->label('Correo Electrónico')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\BadgeColumn::make('roles.name')
                    ->label('Roles')
                    ->getStateUsing(fn($record) => $record->getRoleNames()->implode(', '))
                    ->colors([
                        'primary',
                        'success' => fn($state) => str_contains($state, 'Admin'),
                        'warning' => fn($state) => str_contains($state, 'User'),
                        'danger' => fn($state) => str_contains($state, 'Guest'),
                    ]),
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
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
