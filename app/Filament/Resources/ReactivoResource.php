<?php

namespace App\Filament\Resources;
use Filament\Forms\Components\Actions\Action as FormAction;
use Filament\Forms\Components\Card;
use Livewire\Component as Livewire;
// ... otras declaraciones 'use'
use Filament\Forms\Components\Tabs;
use Filament\Tables\Actions\Action;
use Filament\Forms\Get;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use App\Filament\Resources\ReactivoResource\Pages;
use App\Models\Reactivo;

class ReactivoResource extends Resource
{
    protected static ?string $model = Reactivo::class;
    protected static ?string $navigationIcon = 'heroicon-o-beaker';
    protected static ?string $navigationGroup = 'Catálogos de Laboratorio';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Card::make()
                ->schema([
                Select::make('prueba_id')->relationship('prueba', 'nombre')->required()->searchable()->preload(),
                TextInput::make('nombre')->required()->maxLength(255),
                TextInput::make('lote'),
                Forms\Components\DatePicker::make('fecha_caducidad'),
                Forms\Components\Toggle::make('en_uso')->default(false)->label('¿En Uso?')->helperText('Solo un reactivo por prueba puede estar en uso. Al activar este, cualquier otro reactivo para la misma prueba será desactivado.'),
                Textarea::make('descripcion')->columnSpanFull(),
                ])->columns(2    )
            ]);
    }

public static function table(Table $table): Table
{
    return $table
        ->columns([
            Tables\Columns\TextColumn::make('nombre')->searchable()->sortable(),
            Tables\Columns\TextColumn::make('prueba.nombre')->badge()->searchable()->sortable(),
            Tables\Columns\TextColumn::make('lote')->searchable()->sortable(),
            Tables\Columns\IconColumn::make('en_uso')->label('En Uso')->boolean(),
        ])
        ->filters([
            //
        ])
        ->actions([
            Tables\Actions\EditAction::make(),
               Action::make('setActive')
                ->label('Poner en Uso')
                ->icon('heroicon-o-check-circle')
                ->color('success')
                // Usamos el atributo correcto para la visibilidad
                ->visible(fn (Reactivo $record): bool => !$record->en_uso)
                ->requiresConfirmation()
                ->modalHeading('Activar Reactivo')
                ->modalDescription('¿Estás seguro de que quieres establecer este reactivo como el principal? Cualquier otro reactivo para la misma prueba será desactivado.')
                ->action(function (Reactivo $record): void {
                    // Usamos el atributo correcto para guardar
                    $record->en_uso = true;
                    $record->save();
                }),
Action::make('gestionarValores')
    ->label('Valores de Referencia')
    ->icon('heroicon-o-clipboard-document-list')
    ->color('gray')
    ->modalWidth('4xl')
    ->modalSubmitActionLabel('Guardar')
    ->fillForm(fn (Reactivo $record) => [
        'valoresReferencia' => $record->valoresReferencia->toArray(),
    ])
    ->form(function (Reactivo $record) {
        return [
            Tabs::make('Opciones')->tabs([
                Tabs\Tab::make('Consultar Valores')
                    ->schema([
                        Forms\Components\View::make('filament.components.valor-referencia-table')
                            ->viewData([
                                'valores' => $record->valoresReferencia()->with('grupoEtario')->get(),
                            ]),
                    ]),
                
                Tabs\Tab::make('Gestionar Valores')
                    ->schema([
                     
                        Forms\Components\Section::make('Registros de Referencia')
                            ->description('Añada, modifique o copie valores de referencia.')
                         
                            ->headerActions([
                                FormAction::make('copyFrom')
                                    ->label('Copiar de otro reactivo')
                                    ->icon('heroicon-o-document-duplicate')
                                    ->color('gray')
                                    ->form([
                                        Select::make('source_reactivo_id')
                                            ->label('Reactivo de Origen')
                                            ->required()
                                            ->options(function () use ($record) {
                                                return Reactivo::where('prueba_id', $record->prueba_id)
                                                    ->where('id', '!=', $record->id)
                                                    ->pluck('nombre', 'id');
                                            })
                                            ->searchable(),
                                    ])
                                    ->action(function (array $data, callable $set) {
                                        $sourceReagent = Reactivo::find($data['source_reactivo_id']);
                                        if (!$sourceReagent) return;

                                        $valuesToCopy = $sourceReagent->valoresReferencia->map(function ($valor) {
                                            unset($valor['id'], $valor['reactivo_id']);
                                            return $valor;
                                        })->toArray();
                                        
                                        $set('valoresReferencia', $valuesToCopy);
                                    })
                                    ->modalCancelAction(false)
                                    ->modalSubmitActionLabel('Copiar Valores'),
                            ])
                            ->schema([
                                Repeater::make('valoresReferencia')
                                    ->relationship()
                                    ->label('')
                                    ->schema([
                                        Select::make('genero')->options(['Masculino'=>'Masculino', 'Femenino'=>'Femenino', 'Ambos'=>'Ambos'])->columnSpan(2),
                                        Select::make('grupo_etario_id')->relationship('grupoEtario', 'nombre')->label('Grupo Etario')->preload()->columnSpan(2),
                                        TextInput::make('descriptivo')->label('Descriptivo (Ej: Fumadores)')->columnSpan(4),
                                        Select::make('operador')
                                            ->label('Modo de Referencia')
                                            ->options(['rango'=>'Rango (entre dos valores)', '<='=>'Hasta un valor (<=)', '>='=>'Desde un valor (>=)', '<'=>'Menor que (<)', '>'=>'Mayor que (>)', '='=>'Igual a (=)'])
                                            ->default('rango')->required()->live()->columnSpan(4),
                                        TextInput::make('valor_min')->label('Valor Mínimo')->numeric()
                                            ->required(fn (Get $get) => in_array($get('operador'), ['rango', '>=', '>']))
                                            ->visible(fn (Get $get) => in_array($get('operador'), ['rango', '>=', '>', '='])),
                                        TextInput::make('valor_max')->label('Valor Máximo')->numeric()
                                            ->required(fn (Get $get) => in_array($get('operador'), ['rango', '<=', '<']))
                                            ->visible(fn (Get $get) => in_array($get('operador'), ['rango', '<=', '<'])),
                                        TextInput::make('unidades')->label('Unidades')->columnSpan(2),
                                        Textarea::make('nota')->label('Nota Adicional')->columnSpanFull(),
                                    ])
                                    ->columns(4)
                                    ->collapsible()
                                    ->defaultItems(0),
                            ]),
                    ]),
            ])
        ];
    })
    ->action(function (Reactivo $record, array $data): void {
        // Guardado automático
    }), 
        ])
        ->bulkActions([
            Tables\Actions\BulkActionGroup::make([
                Tables\Actions\DeleteBulkAction::make(),
            ]),
        ]);
}

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListReactivos::route('/'),
            'create' => Pages\CreateReactivo::route('/create'),
            'edit' => Pages\EditReactivo::route('/{record}/edit'),
        ];
    }    
}