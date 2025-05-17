<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TipoExamenResource\Pages;
use App\Models\TipoExamen;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Notifications\Notification;
use Filament\Tables\Actions\Action;
use Filament\Tables\Filters\SelectFilter;

class TipoExamenResource extends Resource
{
    protected static ?string $model = TipoExamen::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';

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
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('estado')
                    ->label('Estado')
                    ->formatStateUsing(function ($state) {
                        return $state
                            ? '✅ Activo'
                            : '❌ Inactivo';
                    })
                    ->badge() // opcional para que se vea como etiqueta
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
                Tables\Actions\ViewAction::make(),

                Action::make('toggleEstado')
                    ->label(fn($record) => $record->estado ? 'Dar de baja' : 'Dar de alta')
                    ->icon(fn($record) => $record->estado ? 'heroicon-o-x-circle' : 'heroicon-o-check-circle')
                    ->color(fn($record) => $record->estado ? 'danger' : 'success')
                    ->tooltip(fn($record) => $record->estado ? 'Dar de baja' : 'Dar de alta')
                    ->action(function ($record) {
                        $record->estado = $record->estado ? 0 : 1;
                        $record->save();

                        Notification::make()
                            ->title('Estado actualizado')
                            ->body('El examen fue ' . ($record->estado ? 'activado' : 'dado de baja') . ' correctamente.')
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
