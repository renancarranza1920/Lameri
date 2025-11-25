<?php

namespace App\Filament\Resources;
use Filament\Forms\Components\Actions\Action as FormAction;
use Filament\Forms\Components\Card;
use Filament\Forms\Components\Section;
use Filament\Notifications\Notification;
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

    protected static ?string $navigationGroup = 'Gestión de Laboratorio';
    protected static ?int $navigationSort = 4;
    protected static ?string $navigationIcon = 'heroicon-o-beaker';

    

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Card::make()
                    ->schema([
                        Section::make('Información del Reactivo')
                            ->schema([
                        Select::make('prueba_id')->relationship('prueba', 'nombre')->required()->searchable()->preload(),
                        TextInput::make('nombre')->required()->maxLength(255),
                        TextInput::make('lote'),

                        Forms\Components\DatePicker::make('fecha_caducidad'),
                        Forms\Components\Toggle::make('en_uso')->default(false)->label('¿En Uso?')->helperText('Solo un reactivo por prueba puede estar en uso. Al activar este, cualquier otro reactivo para la misma prueba será desactivado.'),
                        Textarea::make('descripcion')->columnSpanFull(),
                    ])->columns(2)
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        $esAccionable = fn(Reactivo $record): bool => $record->estado === 'disponible';

        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nombre')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('prueba.nombre')->badge()->searchable()->sortable(),
                Tables\Columns\BadgeColumn::make('estado')
                    ->colors([
                        'success' => 'disponible',
                        'warning' => 'caducado',
                        'danger' => 'agotado',
                    ])
                    ->searchable(),
                Tables\Columns\TextColumn::make('lote')->searchable()->sortable(),
                Tables\Columns\IconColumn::make('en_uso')->label('En Uso')->boolean(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make()->visible($esAccionable),
                Action::make('setActive')
                    ->label('Poner en Uso')
                    ->visible(fn () => auth()->user()->can('activar_reactivos'))
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    // Usamos el atributo correcto para la visibilidad
                    ->visible(fn(Reactivo $record): bool => !$record->en_uso && $record->estado === 'disponible')
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
                    ->visible($esAccionable && auth()->user()->can('gestionar_valores_ref')) 
                    ->modalWidth('4xl')
                    ->modalSubmitActionLabel('Guardar')
                    ->fillForm(fn(Reactivo $record) => [
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
                                                        if (!$sourceReagent)
                                                            return;

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
                                                        // ... dentro del schema del Repeater 'valoresReferencia' ...
            
                                                        Forms\Components\Select::make('grupo_etario_id')
                                                            ->relationship('grupoEtario', 'nombre')
                                                            ->label('Grupo Etario')
                                                            ->preload()
                                                            ->live() // <--- 1. Hacemos que escuche cambios
                                                            ->afterStateUpdated(function (Forms\Set $set) {
                                                                // 2. Reseteamos el género si cambian el grupo para obligar a re-seleccionar
                                                                $set('genero', null);
                                                            })
                                                            ->columnSpan(2),

                                                        Forms\Components\Select::make('genero')
                                                            ->label('Género Aplicable')
                                                            // 3. Las opciones dependen del grupo seleccionado
                                                            ->options(function (Forms\Get $get) {
                                                                $grupoId = $get('grupo_etario_id');

                                                                // Si no ha seleccionado grupo, mostramos todo
                                                                if (!$grupoId) {
                                                                    return [
                                                                        'Masculino' => 'Masculino',
                                                                        'Femenino' => 'Femenino',
                                                                        'Ambos' => 'Ambos'
                                                                    ];
                                                                }

                                                                // Buscamos el grupo en la BD
                                                                $grupo = \App\Models\GrupoEtario::find($grupoId);

                                                                // Si el grupo es 'Ambos' (como Adultos), permitimos especificar
                                                                if ($grupo && $grupo->genero === 'Ambos') {
                                                                    return [
                                                                        'Masculino' => 'Masculino',
                                                                        'Femenino' => 'Femenino',
                                                                        'Ambos' => 'Ambos'
                                                                    ];
                                                                }

                                                                // Si el grupo es específico (ej: Embarazo -> Femenino), FORZAMOS esa opción
                                                                return $grupo ? [$grupo->genero => $grupo->genero] : [];
                                                            })
                                                            ->required()
                                                            ->columnSpan(2),
                                                        TextInput::make('descriptivo')->label('Descriptivo (Ej: Fumadores)')->columnSpan(4),
                                                        Select::make('operador')
                                                            ->label('Modo de Referencia')
                                                            ->options(['rango' => 'Rango (entre dos valores)', '<=' => 'Hasta un valor (<=)', '>=' => 'Desde un valor (>=)', '<' => 'Menor que (<)', '>' => 'Mayor que (>)', '=' => 'Igual a (=)'])
                                                            ->default('rango')->required()->live()->columnSpan(4),
                                                        TextInput::make('valor_min')
                                                            ->label('Valor Mínimo')
                                                            ->required(fn(Get $get) => in_array($get('operador'), ['rango', '>=', '>']))
                                                            ->visible(fn(Get $get) => in_array($get('operador'), ['rango', '>=', '>', '='])),

                                                        TextInput::make('valor_max')
                                                            ->label('Valor Máximo')
                                                            ->required(fn(Get $get) => in_array($get('operador'), ['rango', '<=', '<']))
                                                            ->visible(fn(Get $get) => in_array($get('operador'), ['rango', '<=', '<'])),
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

                Action::make('restock')
                    ->label('Reabastecer')
                    ->icon('heroicon-o-arrow-path-rounded-square')
                    ->color('primary')
                    // Solo es visible si el reactivo NO está disponible
                    ->visible(fn(Reactivo $record) => $record->estado !== 'disponible' && auth()->user()->can('reabastecer_reactivos'))
                    ->modalHeading('Reabastecer Reactivo')
                    ->modalDescription('Esto creará un nuevo registro de reactivo basado en este, con todos sus valores de referencia duplicados.')
                    ->modalSubmitActionLabel('Confirmar Reabastecimiento')
                    ->form([
                        Forms\Components\DatePicker::make('fecha_caducidad')
                            ->label('Nueva Fecha de Vencimiento')
                            ->required(),
                        Forms\Components\TextInput::make('lote')
                            ->label('Nuevo Número de Lote')
                            ->required(),
                    ])
                    ->action(function (Reactivo $record, array $data) {
                        // 1. Replicamos el reactivo original para crear una copia
                        $newReagent = $record->replicate();

                        // 2. Actualizamos con los nuevos datos del formulario
                        $newReagent->fecha_caducidad = $data['fecha_caducidad'];
                        $newReagent->lote = $data['lote'];
                        $newReagent->estado = 'disponible'; // El nuevo lote siempre está disponible
                        $newReagent->en_uso = false; // Por seguridad, el nuevo lote no entra en uso automáticamente
            
                        // 3. Guardamos el nuevo reactivo en la base de datos
                        $newReagent->save();

                        // 4. Copiamos todos los valores de referencia del original al nuevo
                        foreach ($record->valoresReferencia as $valor) {
                            $newValor = $valor->replicate();
                            $newValor->reactivo_id = $newReagent->id; // Lo asociamos al nuevo reactivo
                            $newValor->save();
                        }

                        Notification::make()->title('¡Reactivo reabastecido!')
                            ->body("Se ha creado un nuevo registro para {$newReagent->nombre} con el lote {$newReagent->lote}.")
                            ->success()->send();
                    }),
                // --- ¡NUEVO BOTÓN PARA MARCAR AGOTADO! ---
                Action::make('marcarAgotado')
                    ->label('Marcar Agotado')
                    ->visible($esAccionable)
                    ->icon('heroicon-o-archive-box-x-mark')
                    ->color('danger')
                    // El botón solo es visible si el reactivo está 'disponible'
                    ->visible(fn(Reactivo $record) => $record->estado === 'disponible' &&
                        auth()->user()->can('agotar_reactivos'))
                    ->requiresConfirmation()
                    ->modalHeading('Marcar Reactivo como Agotado')
                    ->modalDescription('Esta acción es irreversible. ¿Estás seguro?')
                    ->action(function (Reactivo $record) {
                        $record->estado = 'agotado';
                        $record->save();
                        Notification::make()->title('Reactivo marcado como agotado')->success()->send();
                    }),
            ])
            ->bulkActions([
                
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