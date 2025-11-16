<?php

namespace App\Filament\Resources\ClientesResource\Pages;

use App\Filament\Resources\ClientesResource;
use Filament\Resources\Pages\Page;
use App\Models\Cliente;
use Illuminate\Support\Facades\Log;
use Filament\Infolists\Infolist;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\Grid;

use Filament\Tables\Concerns\InteractsWithTable; 
use Filament\Tables\Contracts\HasTable;         
use Filament\Tables\Table;                      
use Filament\Tables\Columns\TextColumn;         
use Filament\Tables\Actions\Action;             
use App\Models\Orden;
use Filament\Forms\Components\ViewField; 
use Barryvdh\DomPDF\Facade\Pdf;       
use Illuminate\Support\Collection;
use Filament\Forms\Get;


class Expediente extends Page implements HasTable
{
    use InteractsWithTable; // 👈 USAR EL TRAIT
    protected static string $resource = ClientesResource::class;

    protected static string $view = 'filament.resources.clientes-resource.pages.expediente';

    public Cliente $record;

    public function mount($record): void
    {
        //dd($this->record);
        //$this->record = Cliente::findOrFail($record);

    }

    public function getTitle(): string
    {

        return ' ';
    }

    public function clienteInfolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->record($this->record)
            ->schema([
                Section::make('Información del Paciente')
                    ->schema([
                        Grid::make(3)->schema([
                            TextEntry::make('NumeroExp')->label('No. Expediente'),
                            TextEntry::make('nombre')->label('Nombre Completo')
                                ->getStateUsing(fn($record) => $record->nombre . ' ' . $record->apellido),
                            TextEntry::make('genero')->label('Género'),
                        ]),

                        Grid::make(3)->schema([
                            TextEntry::make('telefono'),
                            TextEntry::make('correo'),
                            TextEntry::make('fecha_nacimiento')
                                ->label('Edad')
                                ->getStateUsing(fn($record) => \Carbon\Carbon::parse($record->fecha_nacimiento)->age . ' años'),
                        ]),

                        Grid::make(3)->schema([
                            TextEntry::make('direccion')->columnSpanFull(),
                        ]),
                    ])
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            // 💡 Filtra las órdenes usando $this->record (el cliente ya cargado)
            ->query(Orden::query()->where('cliente_id', $this->record->id))
            ->columns([
                TextColumn::make('id')
                    ->label('# Orden')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('fecha')
                    ->date('d/m/Y')
                    ->label('Fecha')
                    ->sortable(),


                TextColumn::make('estado')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'finalizado' => 'success',
                        'en proceso' => 'warning',
                        'pendiente' => 'gray',
                        'pausada' => 'danger',
                        default => 'gray',
                    }),
            ])
            ->defaultSort('fecha', 'desc')
            // Mensaje si no hay órdenes
            ->emptyStateHeading('No se ha realizado ninguna orden')
            ->emptyStateDescription('Este paciente aún no tiene historial de órdenes registradas.')
            ->emptyStateIcon('heroicon-o-clipboard-document')
            ->actions([
                // Puedes añadir una acción aquí para ver la orden en detalle
                Action::make('ver_detalle_modal')
                    ->label('Ver Detalles')
                    ->icon('heroicon-o-eye')
                    ->iconButton()
                    ->color('gray')
                    ->modalHeading(fn(Orden $record) => 'Detalles de Orden #' . $record->id)
                    ->modalWidth('4xl') // Puedes ajustar el ancho si el modal es complejo
                    ->modalContent(function (Orden $record) {
                        // Carga las relaciones necesarias para tu vista Blade
                        $record->load(['detalleOrden.examen.pruebas', 'resultados']);
                        // Retorna la vista Blade que ya tienes en OrdenResource
                        return view('filament.modals.ver-orden', ['record' => $record]);
                    })
                    // Oculta los botones de enviar/cancelar por defecto del modal de acción
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Cerrar'),

                // 2. ACCIÓN GENERAR REPORTE PDF (MODAL CON VISOR IFRAME)
                Action::make('generarReporte')
                    ->tooltip('Ver Resultados')
                    ->label('Ver Resultados')
                    ->icon('heroicon-o-printer')
                    ->iconButton()
                    ->color('gray')
                    ->visible(fn(Orden $record): bool => $record->estado === 'finalizado')
                    // --- Configuración del Modal de Previsualización ---
                    ->modalWidth('7xl')
                    ->modalHeading(fn(Orden $record) => 'Reporte de Resultados: #' . $record->id)
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Cerrar')
                    ->modalContent(function (Orden $record) {

                        // Cargar relaciones
                        $orden = $record->load([
                            'cliente',
                            'detalleOrden.examen.tipoExamen',
                            'detalleOrden.examen.pruebas.reactivoEnUso.valoresReferencia.grupoEtario',
                            'resultados.prueba'
                        ]);

                        // Agrupamiento…
                        $detallesAgrupados = $orden->detalleOrden
                            ->whereNotNull('examen_id')
                            ->groupBy('examen.tipoExamen.nombre');

                        $datos_agrupados = [];

                        foreach ($detallesAgrupados as $tipoExamenNombre => $detalles) {
                            $examenes_data = [];

                            foreach ($detalles as $detalle) {
                                $todasLasPruebas = $detalle->examen->pruebas;

                                $pruebasUnitarias = $todasLasPruebas->whereNull('tipo_conjunto');
                                $pruebasConjuntas = $todasLasPruebas->whereNotNull('tipo_conjunto')->groupBy('tipo_conjunto');

                                $dataUnitarias = $pruebasUnitarias->map(function ($prueba) use ($orden, $detalle) {
                                    return self::getDatosPruebaParaPdf($prueba, $orden, $detalle->id);
                                })->all();

                                $dataMatrices = $pruebasConjuntas->map(function ($pruebasDelConjunto) use ($orden, $detalle) {
                                    $filas = [];
                                    $columnas = [];
                                    $dataMatrix = [];

                                    foreach ($pruebasDelConjunto as $prueba) {
                                        $partes = explode(', ', $prueba->nombre);

                                        if (count($partes) >= 2) {
                                            [$f, $c] = $partes;
                                            $filas[] = $f;
                                            $columnas[] = $c;

                                            $dataMatrix[$f][$c] = self::getDatosPruebaParaPdf($prueba, $orden, $detalle->id);
                                        }
                                    }

                                    return [
                                        'filas' => array_values(array_unique($filas)),
                                        'columnas' => array_values(array_unique($columnas)),
                                        'data' => $dataMatrix,
                                    ];
                                })->all();

                                $examenes_data[] = [
                                    'nombre' => $detalle->examen->nombre,
                                    'codigo' => $detalle->examen->id,
                                    'pruebas_unitarias' => $dataUnitarias,
                                    'matrices' => $dataMatrices,
                                ];
                            }

                            $datos_agrupados[$tipoExamenNombre ?: 'Exámenes Generales'] = $examenes_data;
                        }

                        // GENERAR PDF
                        $pdf = Pdf::loadView('pdf.reporte_resultados', [
                            'orden' => $orden,
                            'datos_agrupados' => $datos_agrupados,
                        ]);

                        $pdfContent = base64_encode($pdf->output());

                        // Devuelve la vista lista
                        return view('filament.modals.pdf-viewer', [
                            'pdfContent' => $pdfContent,
                        ]);
                    })

            ]);
    }

    public static function getDatosPruebaParaPdf($prueba, $orden, $detalleId): array
    {
        $resultado = $orden->resultados->where('prueba_id', $prueba->id)->where('detalle_orden_id', $detalleId)->first();
        
        $referencia_formateada = 'N/A';
        $unidades = '';
        $es_fuera_de_rango = false;
        $valor_resultado_num = null;

        if ($resultado && is_numeric($resultado->resultado)) {
            $valor_resultado_num = (float) $resultado->resultado;
        }

        // --- INICIO DE LA LÓGICA DE REFERENCIA CORREGIDA ---
        if ($prueba->reactivoEnUso && $prueba->reactivoEnUso->valoresReferencia->isNotEmpty()) {
            
            // 1. OBTENER DATOS DEL PACIENTE
            $cliente = $orden->cliente;
            $generoCliente = $cliente->genero; // "Masculino" o "Femenino"
            $grupoEtarioCliente = $cliente->getGrupoEtario(); // Objeto GrupoEtario o null

            $valorRef = null;
            $todosLosValores = $prueba->reactivoEnUso->valoresReferencia;

            if ($grupoEtarioCliente) {
                // 2. INTENTO DE BÚSQUEDA 1: Grupo Etario + Género Específico
                // Ej: "Adultos" (ID: 8) + "Masculino"
                $valorRef = $todosLosValores
                    ->where('grupo_etario_id', $grupoEtarioCliente->id)
                    ->where('genero', $generoCliente)
                    ->first();

                // 3. INTENTO DE BÚSQUEDA 2 (FALLBACK): Grupo Etario + "Ambos"
                // Ej: "Adultos" (ID: 8) + "Ambos"
                if (!$valorRef) {
                    $valorRef = $todosLosValores
                        ->where('grupo_etario_id', $grupoEtarioCliente->id)
                        ->where('genero', 'Ambos')
                        ->first();
                }
            }

            // 4. INTENTO DE BÚSQUEDA 3 (FALLBACK): Sin Grupo Etario + Género Específico
            // (Para valores que no dependen de la edad, solo del género)
            if (!$valorRef) {
                $valorRef = $todosLosValores
                    ->whereNull('grupo_etario_id')
                    ->where('genero', $generoCliente)
                    ->first();
            }

            // 5. INTENTO DE BÚSQUEDA 4 (FALLBACK): Sin Grupo Etario + "Ambos"
            // (El valor más genérico, ej: 0-100 U/L para todos)
            if (!$valorRef) {
                $valorRef = $todosLosValores
                    ->whereNull('grupo_etario_id')
                    ->where('genero', 'Ambos')
                    ->first();
            }
            
            // 6. ÚLTIMO RECURSO: Si todo falla, toma el primero (evita crasheo)
            if (!$valorRef) {
                $valorRef = $todosLosValores->first();
            }

            // --- FIN DE LA LÓGICA DE BÚSQUEDA ---

            // Ahora $valorRef es el correcto (o el mejor disponible)
            $valorMin = (float) $valorRef->valor_min;
            $valorMax = (float) $valorRef->valor_max;
            $unidades = $valorRef->unidades ?? '';

            // Formatear el texto de referencia
            $rangoTexto = match ($valorRef->operador) {
                'rango' => "{$valorMin} - {$valorMax}",
                '<=' => "≤ {$valorMax}",
                '<' => "< {$valorMax}",
                '>=' => "≥ {$valorMin}",
                '>' => "> {$valorMin}",
                '=' => "= {$valorMin}",
                default => $valorRef->descriptivo ?? '',
            };
            $referencia_formateada = $rangoTexto;

            // --- LÓGICA DE COMPARACIÓN (FUERA DE RANGO) ---
            if (!is_null($valor_resultado_num)) {
                switch ($valorRef->operador) {
                    case 'rango':
                        if ($valor_resultado_num < $valorMin || $valor_resultado_num > $valorMax) $es_fuera_de_rango = true;
                        break;
                    case '<=':
                        if ($valor_resultado_num > $valorMax) $es_fuera_de_rango = true;
                        break;
                    case '<':
                        if ($valor_resultado_num >= $valorMax) $es_fuera_de_rango = true;
                        break;
                    case '>=':
                        if ($valor_resultado_num < $valorMin) $es_fuera_de_rango = true;
                        break;
                    case '>':
                        if ($valor_resultado_num <= $valorMin) $es_fuera_de_rango = true;
                        break;
                    case '=':
                         if ($valor_resultado_num != $valorMin) $es_fuera_de_rango = true;
                        break;
                }
            }
        }

        return [
            'nombre' => $prueba->nombre,
            'resultado' => $resultado->resultado ?? 'PENDIENTE',
            'referencia' => $referencia_formateada,
            'unidades' => $unidades,
            'fecha_resultado' => $resultado ? $resultado->updated_at->format('d/m/Y') : '',
            'es_fuera_de_rango' => $es_fuera_de_rango,
        ];
    }

}
