<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ExamenResource\Pages;
use App\Models\Examen;
use App\Models\TipoExamen;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Notifications\Notification;
use Filament\Tables\Actions\Action;
use Filament\Tables\Filters\SelectFilter;

class ExamenResource extends Resource
{
    protected static ?string $model = Examen::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document';
    protected static ?string $navigationLabel = 'Exámenes';
    protected static ?string $pluralModelLabel = 'Exámenes';
    protected static ?string $modelLabel = 'Examen';

    // En App\Filament\Resources\ExamenResource.php
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Card::make()
                    ->schema([
                        Forms\Components\Select::make('tipo_examen_id')
                            ->label('Tipo de Examen')
                            ->relationship('tipoExamen', 'nombre')
                            ->options(function () {
                                return TipoExamen::where('estado', 1)->pluck('nombre', 'id');
                            })
                            ->required()
                            ->searchable()
                            ->preload(),

                        Forms\Components\TextInput::make('nombre')
                            ->label('Nombre del Examen')
                            ->placeholder('Ej: Glucosa, Creatinina...')
                            ->required()
                            ->reactive()
                            ->maxLength(255),

                        Forms\Components\Select::make('recipiente')
                            ->label('Recipiente')
                            ->options([
                                'quimica_sanguinea' => 'Quimica Sanginea',
                                'cuagulacion' => 'Cuagulacion',
                                'hematologia' => 'Hematologia',
                                'coprologia' => 'Coprologia',
                                'uroanalisis' => 'Uroanalisis',
                                'cultivo_secreciones' => 'Cultivo Secreciones',
                            ])
                            ->required()
                            ->searchable(),

                    Forms\Components\TextInput::make('precio')
                        ->label('Precio')->prefix('$')->numeric()->required(),

                    Forms\Components\Toggle::make('estado')
                        ->label('Activo')->required()->default(true)->inline(false),
                ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
        ->recordUrl(null)
            ->columns([
                Tables\Columns\TextColumn::make('tipoExamen.nombre')
                    ->label('Tipo de Examen')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('nombre')
                    ->label('Nombre')
                    ->sortable()
                    ->searchable(),

                // 👇 ***** AÑADIMOS ESTA COLUMNA PARA VER LAS MUESTRAS ***** 👇
                Tables\Columns\TextColumn::make('muestras.nombre')
                    ->label('Muestras')
                    ->badge()
                    ->searchable(),

                Tables\Columns\TextColumn::make('precio')
                    ->label('Precio')
                    ->money('USD'),

                Tables\Columns\TextColumn::make('estado')
                    ->label('Estado')
                    ->formatStateUsing(fn($state) => $state ? '✅ Activo' : '❌ Inactivo')
                    ->badge()
                    ->color(fn($state) => $state ? 'success' : 'danger'),

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
                Tables\Actions\EditAction::make(),
                // Acción personalizada para mostrar el modal
                Action::make('ver-modal')
                    ->label('Ver')
                    ->icon('heroicon-s-eye')
                    ->modalHeading('Detalle del Examen')
                    ->color('gray')
                    ->modalWidth('lg')
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Cerrar')
                    ->form([
                        Forms\Components\TextInput::make('nombre')
                            ->label('Nombre del Examen')
                            ->disabled()
                            ->default(fn($record) => $record->nombre),
                        Forms\Components\Select::make('tipo_examen_id')
                            ->label('Tipo de Examen')
                            ->options(TipoExamen::pluck('nombre', 'id'))
                            ->disabled()
                            ->default(fn($record) => $record->tipo_examen_id),
                        Forms\Components\TextInput::make('recipiente')
                            ->label('Recipiente')
                            ->disabled()
                            ->default(fn($record) => $record->recipiente),
                        Forms\Components\TextInput::make('precio')
                            ->label('Precio')
                            ->prefix('$')
                            ->disabled()
                            ->default(fn($record) => $record->precio),
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
                            ->title($record->estado ? 'Examen activado' : 'Examen desactivado')
                            ->success()
                            ->send();
                    })
                    ->requiresConfirmation()
                    ->iconButton()
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
            'index' => Pages\ListExamens::route('/'),
            'create' => Pages\CreateExamen::route('/create'),
            'edit' => Pages\EditExamen::route('/{record}/edit'),
            'view' => Pages\ViewExamen::route('/{record}'),
        ];
    }
}