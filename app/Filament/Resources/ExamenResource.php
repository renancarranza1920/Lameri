<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ExamenResource\Pages;
use App\Filament\Resources\ExamenResource\RelationManagers\PruebasRelationManager;
use App\Models\Examen;
use App\Models\TipoExamen;
use Illuminate\Support\Facades\DB;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Notifications\Notification;
use Filament\Tables\Actions\Action;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Support\HtmlString;

class ExamenResource extends Resource
{
    protected static ?string $model = Examen::class;

    protected static ?string $navigationGroup = 'Gestión de Laboratorio';
    protected static ?int $navigationSort = 1;
    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document';
    protected static ?string $navigationLabel = 'Exámenes';
    protected static ?string $pluralModelLabel = 'Exámenes';
    protected static ?string $modelLabel = 'Examen';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Card::make()
                    ->schema([
                        Forms\Components\Section::make('Información ')
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
                                    ->relationship('muestras', 'nombre')
                                    ->multiple()
                                    ->preload()
                                    ->searchable()
                                    ->createOptionForm([
                                        Forms\Components\TextInput::make('nombre')
                                            ->required()
                                            ->unique('muestras', 'nombre'),
                                    ]),

                                Forms\Components\Toggle::make('es_externo')
                                    ->label('Es un examen externo/referido')
                                    ->helperText('Activa esto si el examen se procesa en otro laboratorio.'),

                                Forms\Components\TextInput::make('precio')
                                    ->label('Precio')
                                    ->prefix('$')
                                    ->numeric()
                                    ->required(),

                                Forms\Components\Toggle::make('estado')
                                    ->label('Activo')
                                    ->required()
                                    ->default(true)
                                    ->inline(false),
                            ])->columns(2),
                    ])
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
                    ->searchable()
                    ->description(function (Examen $record) {
            // Si es interno y no tiene pruebas, mostramos alerta
            if (!$record->es_externo && $record->pruebas()->count() === 0) {
                return '⚠️ Sin pruebas asignadas';
            }
            return null;
        })
        // Cambiamos el color a naranja/rojo si falta configuración
        ->color(function (Examen $record) {
            if (!$record->es_externo && $record->pruebas()->count() === 0) {
                return 'warning'; // O 'danger' si prefieres rojo
            }
            return null;
        }),

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
            ->headerActions([
                Tables\Actions\Action::make('gestionar_muestras')
                    ->label('Catálogo de Muestras')
                    ->url(fn() => MuestraResource::getUrl('index'))
                    ->color('gray'),
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

                Action::make('ver-modal')
                    ->label('Ver')
                    ->icon('heroicon-s-eye')
                    ->visible(fn() => auth()->user()->can('ver_detalle_examenes'))
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
                    ->visible(fn(Examen $record) => $record->es_externo === false && auth()->user()->can('agregar_pruebas_examenes'))
                    ->color('gray')
                    ->modalHeading(fn(Examen $record) => 'Añadir pruebas a: ' . $record->nombre)
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
                                    ]);
                                }
                            });
                            Notification::make()->title(count($nombres) . ' pruebas creadas')->success()->send();
                        } catch (\Throwable $e) {
                            Notification::make()->title('Error')->body($e->getMessage())->danger()->send();
                        }
                    }),

                // --- ACCIÓN CAMBIAR ESTADO ---
                Action::make('cambiar_estado')
                    ->label(fn($record) => $record->estado ? 'Dar de baja' : 'Dar de alta')
                    ->icon(fn($record) => $record->estado ? 'heroicon-o-x-circle' : 'heroicon-o-check-circle')
                    ->color(fn($record) => $record->estado ? 'danger' : 'success')
                    ->tooltip(fn($record) => $record->estado ? 'Dar de baja' : 'Dar de alta')
                    ->requiresConfirmation()

                    // 1. Encabezado del modal
                    ->modalHeading(fn($record) => $record->estado ? '¿Desactivar Examen?' : '¿Activar Examen?')

                    // 2. Descripción con Viñetas y Alerta Detallada
                    ->modalDescription(function (Examen $record) {
                        if (!$record->estado) {
                            return '¿Activar este examen nuevamente?';
                        }

                        $perfiles = $record->perfiles;

                        if ($perfiles->isNotEmpty()) {

                            // Construimos la lista con viñetas (bullet points)
                            $listaHtml = '<ul class="list-disc list-inside text-sm text-gray-500 dark:text-gray-400 text-left ml-4">';
                            $perfilesEnRiesgo = [];

                            foreach ($perfiles as $perfil) {
                                $listaHtml .= '<li>' . e($perfil->nombre) . '</li>';

                                // --- CAMBIO AQUÍ ---
                                // Solo verificamos riesgo si el perfil está ACTIVO
                                if ($perfil->estado == 1) {
                                    // Cálculo de riesgo (con getTable() para evitar error)
                                    $activosRestantes = $perfil->examenes()
                                        ->where('estado', 1)
                                        ->where($record->getTable() . '.id', '!=', $record->id)
                                        ->count();

                                    if ($activosRestantes < 2) {
                                        $perfilesEnRiesgo[] = $perfil->nombre;
                                    }
                                }
                            }
                            $listaHtml .= '</ul>';

                            // Alerta compacta si hay riesgo CON EL MOTIVO EN 3 LÍNEAS
                            $alertaRiesgo = '';
                            if (!empty($perfilesEnRiesgo)) {
                                $afectados = implode(', ', $perfilesEnRiesgo);
                                $alertaRiesgo = 
                                    "<div class='mt-3 text-sm text-red-600 dark:text-red-400 font-semibold text-center'>" .
                                    "⚠️ Nota: Se desactivarán automáticamente los perfiles:<br>" .
                                    "<span class='text-base'>{$afectados}</span><br>" .
                                    "debido a que quedarán con menos de 2 exámenes activos." .
                                    "</div>";
                            }

                            return new HtmlString(
                                "<div class='space-y-3 text-center'>" .
                                "<p>Este examen pertenece a estos perfiles:</p>" .
                                $listaHtml .
                                "<p>¿Deseas darlo de baja?</p>" .
                                $alertaRiesgo .
                                "</div>"
                            );
                        }

                        return '¿Deseas dar de baja este examen?';
                    })

                    // 3. Ejecución de la lógica
                    ->action(function ($record) {
                        $nuevoEstado = !$record->estado;
                        $mensajePerfiles = '';
                        $tipoNotificacion = 'success';

                        DB::transaction(function () use ($record, $nuevoEstado, &$mensajePerfiles, &$tipoNotificacion) {
                            $record->estado = $nuevoEstado;
                            $record->save();

                            if (!$nuevoEstado) {
                                $perfiles = $record->perfiles;
                                $perfilesDesactivados = [];

                                foreach ($perfiles as $perfil) {
                                    // --- VALIDACIÓN ADICIONAL ---
                                    // Solo procesamos perfiles que están activos actualmente
                                    if ($perfil->estado == 1) {
                                        $activosRestantes = $perfil->examenes()
                                            ->where('estado', 1)
                                            ->count();

                                        if ($activosRestantes < 2) {
                                            $perfil->estado = 0;
                                            $perfil->save();
                                            $perfilesDesactivados[] = $perfil->nombre;
                                        }
                                    }
                                }

                                // Mensaje de éxito/advertencia CON EL MOTIVO
                                if (!empty($perfilesDesactivados)) {
                                    $mensajePerfiles = ' (Se desactivaron automáticamente los perfiles: ' . implode(', ', $perfilesDesactivados) . ' por tener menos de 2 exámenes activos)';
                                    $tipoNotificacion = 'warning';
                                }
                            }
                        });

                        Notification::make()
                            ->title($record->estado ? 'Activado' : 'Desactivado')
                            ->body(($record->estado ? 'Examen activado.' : 'Examen dado de baja.') . $mensajePerfiles)
                            ->status($tipoNotificacion)
                            ->send();
                    })
                    ->iconButton()
            ])
            ->bulkActions([
                
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