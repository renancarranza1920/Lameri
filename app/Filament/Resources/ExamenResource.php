<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ExamenResource\Pages;
use App\Filament\Resources\ExamenResource\RelationManagers\PruebasRelationManager;
use App\Models\Examen;
use App\Models\TipoExamen;
use DB;
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

    protected static ?string $navigationGroup = 'Gestión de Laboratorio';
    protected static ?int $navigationSort = 1;
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
                             Forms\Components\Select::make('muestras')
                        ->relationship('muestras', 'nombre')->multiple()->preload()->searchable()->createOptionForm([
                                Forms\Components\TextInput::make('nombre')->required()->unique('muestras', 'nombre'),
                            ]),
                                Forms\Components\Toggle::make('es_externo')
                        ->label('Es un examen externo/referido')
                        ->helperText('Activa esto si el examen se procesa en otro laboratorio.'),

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

                    Tables\Columns\BooleanColumn::make('es_externo')
                        ->label('Origen')
                        ->trueIcon('heroicon-o-paper-airplane')
                        ->falseIcon('heroicon-o-home')
                        ->color(fn($state) => $state ? 'danger' : 'success')
                        ->sortable(),

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
                    ->visible(fn () => auth()->user()->can('ver_detalle_examenes'))
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
                      Action::make('addPruebas')
                    ->label('Añadir Pruebas')
                    ->icon('heroicon-o-plus-circle')
                    ->visible(fn (Examen $record) => $record->es_externo === false && auth()->user()->can('agregar_pruebas_examenes'))
                    ->color('gray')
                    ->modalHeading(fn (Examen $record) => 'Añadir pruebas a: ' . $record->nombre)
                    ->form([
                        Forms\Components\TagsInput::make('nombres_pruebas')
                            ->label('Nombres de las Pruebas')
                            ->helperText('Escribe un nombre y presiona Enter para añadirlo a la lista.')
                            ->placeholder('Nueva prueba...')
                            ->required(),
                    ])
                    ->action(function (Examen $record, array $data) {
                        $nombres = $data['nombres_pruebas'];
                        try {
                            DB::transaction(function () use ($record, $nombres) {
                                foreach ($nombres as $nombre) {
                                    $record->pruebas()->create([
                                        'nombre' => $nombre,
                                        // examen_id se asigna automáticamente por la relación
                                    ]);
                                }
                            });
                            Notification::make()
                                ->title(count($nombres) . ' pruebas creadas')
                                ->body('Se han añadido las pruebas al examen exitosamente.')
                                ->success()
                                ->send();
                        } catch (\Throwable $e) {
                            Notification::make()
                                ->title('Error al guardar')
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),

                
                Action::make('cambiar_estado')
                    ->label(fn($record) => $record->estado ? 'Dar de baja' : 'Dar de alta')
                    ->icon(fn($record) => $record->estado ? 'heroicon-o-x-circle' : 'heroicon-o-check-circle')
                    ->color(fn($record) => $record->estado ? 'danger' : 'success')
                    ->tooltip(fn($record) => $record->estado ? 'Dar de baja' : 'Dar de alta')
                    ->requiresConfirmation()
                    ->visible(fn () => auth()->user()->can('cambiar_estado_examenes'))
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
        return [
            PruebasRelationManager::class,
        ];
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