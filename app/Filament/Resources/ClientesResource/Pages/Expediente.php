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
use App\Filament\Pages\BuscarExpediente;
use Filament\Tables\Actions\Action; 
use Filament\Tables\Concerns\InteractsWithTable; 
use Filament\Tables\Contracts\HasTable;         
use Filament\Tables\Table;                      
use Filament\Tables\Columns\TextColumn;          
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
        abort_unless(auth()->user()->can('ver_expediente_clientes'), 403);
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
            ->emptyStateHeading('No se ha realizado ninguna orden')
            ->emptyStateDescription('Este paciente aún no tiene historial de órdenes registradas.')
            ->emptyStateIcon('heroicon-o-clipboard-document')
            ->actions([
                Action::make('ver_detalle_modal')
                    ->label('Ver Detalles')
                    ->icon('heroicon-o-eye')
                    ->iconButton()
                    ->visible(fn () => auth()->user()->can('ver_detalle_orden'))
                    ->color('gray')
                    ->modalHeading(fn(Orden $record) => 'Detalles de Orden #' . $record->id)
                    ->modalWidth('4xl')
                    ->modalContent(function (Orden $record) {
                        $record->load(['detalleOrden.examen.pruebas', 'resultados']);
                        return view('filament.modals.ver-orden', ['record' => $record]);
                    })
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Cerrar'),

                Action::make('generarReporte')
                    ->tooltip('Ver Resultados')
                    ->label('Ver Resultados')
                    ->icon('heroicon-o-printer')
                    ->iconButton()
                    ->color('gray')
                    ->visible(fn(Orden $record): bool => $record->estado === 'finalizado' &&
                        auth()->user()->can('generar_reporte_orden'))
                    ->modalWidth('7xl')
                    ->modalHeading(fn(Orden $record) => 'Reporte de Resultados: #' . $record->id)
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Cerrar')
                    ->modalContent(function (Orden $record) {

     // 1. Cargar relaciones necesarias
        $orden = $record->load([
            'cliente',
            'detalleOrden.examen.tipoExamen',
            'detalleOrden.examen.pruebas.tipoPrueba',
            'detalleOrden.examen.pruebas.reactivoEnUso.valoresReferencia.grupoEtario',
            'resultados.prueba'
        ]);

        // 2. Agrupar por tipo de examen (Hematología, Química, etc.)
        $detallesAgrupados = $orden->detalleOrden
            ->whereNotNull('examen_id')
            ->groupBy('examen.tipoExamen.nombre');

        $datos_agrupados = [];

        foreach ($detallesAgrupados as $tipoExamenNombre => $detalles) {
            $examenes_data = [];

            foreach ($detalles as $detalle) {
                
                // --- LÓGICA DIFERENCIADA: EXTERNO vs INTERNO ---
                
                if ($detalle->examen->es_externo) {
                    // CASO A: EXAMEN EXTERNO (REFERIDO)
                    // Buscamos directamente en la tabla 'resultados' los datos guardados manualmente (snapshots)
                    $resultadosExternos = $orden->resultados
                        ->where('detalle_orden_id', $detalle->id)
                        ->where('es_externo', true);

                    $dataUnitarias = $resultadosExternos->map(function ($res) {
                        return [
                            'nombre' => $res->prueba_nombre_snapshot ?? 'Prueba Externa',
                            'resultado' => $res->resultado,
                            'referencia' => $res->valor_referencia_snapshot ?? 'N/A',
                            'unidades' => $res->unidades_snapshot ?? '',
                            'fecha_resultado' => $res->updated_at->format('d/m/Y'),
                            'es_fuera_de_rango' => false, // No calculamos rangos en externos
                        ];
                    })->all();

                    // Agregamos al reporte como un examen simple (sin matrices)
                    $examenes_data[] = [
                        'nombre' => $detalle->examen->nombre ,
                        'codigo' => $detalle->examen->id,
                        'pruebas_unitarias' => $dataUnitarias,
                        'matrices' => [], // Los externos no suelen usar matrices complejas
                    ];

                } else {
                    // CASO B: EXAMEN INTERNO (CATÁLOGO)
                    // Usamos la definición de 'pruebas' y calculamos rangos con la función auxiliar
                    $todasLasPruebas = $detalle->examen->pruebas->where('es_externo', false);

                    $pruebasUnitarias = $todasLasPruebas->whereNull('tipo_conjunto');
                    $pruebasConjuntas = $todasLasPruebas->whereNotNull('tipo_conjunto')->groupBy('tipo_conjunto');

                    // Procesar unitarias internas
                    $dataUnitarias = $pruebasUnitarias->map(function ($prueba) use ($orden, $detalle) {
                        return self::getDatosPruebaParaPdf($prueba, $orden, $detalle->id);
                    })->all();

                    // Procesar matrices internas
                    $dataMatrices = $pruebasConjuntas->map(function (Collection $pruebasDelConjunto) use ($orden, $detalle) {
                        $filas = [];
                        $columnas = [];
                        $dataMatrix = [];
                        foreach ($pruebasDelConjunto as $prueba) {
                            $partes = explode(', ', $prueba->nombre);
                            if (count($partes) >= 2) {
                                [$nombreFila, $nombreColumna] = $partes;
                                $filas[] = $nombreFila;
                                $columnas[] = $nombreColumna;
                                $dataMatrix[$nombreFila][$nombreColumna] = self::getDatosPruebaParaPdf($prueba, $orden, $detalle->id);
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
            }
            $datos_agrupados[$tipoExamenNombre ?: 'Exámenes Generales'] = $examenes_data;
        }

        // 3. Datos de Firma y Sello
        $usuarioQueFirma = auth()->user();
        $rutaFirma = $usuarioQueFirma?->firma_path ?? null;
        $rutaSello = $usuarioQueFirma?->sello_path ?? null;

        // 4. Preparar PDF
        $pdf_data = [
            'orden' => $orden,
            'datos_agrupados' => $datos_agrupados,
            'ruta_firma_digital' => $rutaFirma,
            'ruta_sello_digital' => $rutaSello,
            'nombre_licenciado' => $usuarioQueFirma?->name ?? 'Licenciado Desconocido',
            'ruta_sello_registro' => public_path('storage/sello.png'),
        ];

        $pdf = Pdf::loadView('pdf.reporte_resultados', $pdf_data);

                        $pdfContent = base64_encode($pdf->output());

                        return view('filament.modals.pdf-viewer', [
                            'pdfContent' => $pdfContent,
                        ]);
                    })
            ]);
    }

  public static function getDatosPruebaParaPdf($prueba, $orden, $detalleId): array
    {
        $resultado = $orden->resultados->where('prueba_id', $prueba->id)->where('detalle_orden_id', $detalleId)->first();

        $nombre_prueba = $prueba->nombre; // Nombre por defecto
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

                // AGREGAR ESTO TEMPORALMENTE PARA PROBAR
               
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
            if ($resultado && !empty($resultado->prueba_nombre_snapshot)) {

                $nombre_prueba = $resultado->prueba_nombre_snapshot;
                $referencia_formateada = $resultado->valor_referencia_snapshot ?? 'N/A';
                $unidades = $resultado->unidades_snapshot ?? '';

                // Intentar extraer valores numéricos del snapshot para la comparación
                // Esto asume un formato simple como "1.0 - 5.0"
                if (preg_match('/([\d\.]+)\s*-\s*([\d\.]+)/', $referencia_formateada, $matches)) {
                    $valorMin = (float) $matches[1];
                    $valorMax = (float) $matches[2];
                    if (!is_null($valor_resultado_num)) {
                        if ($valor_resultado_num < $valorMin || $valor_resultado_num > $valorMax) {
                            $es_fuera_de_rango = true;
                        }
                    }
                }
                // (Puedes añadir más 'preg_match' para operadores como '<', '≥', etc.)

            }
            // CASO 2: Es una orden antigua sin "foto", usamos los datos en vivo
            elseif ($prueba->reactivoEnUso && $prueba->reactivoEnUso->valoresReferencia->isNotEmpty()) {

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

                // --- NUEVA LÓGICA DE COMPARACIÓN ---
                if (!is_null($valor_resultado_num)) {
                    switch ($valorRef->operador) {
                        case 'rango':
                            if ($valor_resultado_num < $valorMin || $valor_resultado_num > $valorMax)
                                $es_fuera_de_rango = true;
                            break;
                        case '<=':
                            if ($valor_resultado_num > $valorMax)
                                $es_fuera_de_rango = true;
                            break;
                        case '<':
                            if ($valor_resultado_num >= $valorMax)
                                $es_fuera_de_rango = true;
                            break;
                        case '>=':
                            if ($valor_resultado_num < $valorMin)
                                $es_fuera_de_rango = true;
                            break;
                        case '>':
                            if ($valor_resultado_num <= $valorMin)
                                $es_fuera_de_rango = true;
                            break;
                        case '=':
                            if ($valor_resultado_num != $valorMin)
                                $es_fuera_de_rango = true;
                            break;
                    }
                }
            }
        }

        return [
            'nombre' => $nombre_prueba, // <-- Usa el nombre de la "foto" o el nombre en vivo
            'resultado' => $resultado->resultado ?? 'PENDIENTE',
            'referencia' => $referencia_formateada, // <-- Usa la referencia de la "foto" o la de en vivo
            'unidades' => $unidades, // <-- Usa las unidades de la "foto" o las de en vivo
            'fecha_resultado' => $resultado ? $resultado->updated_at->format('d/m/Y') : '',
            'es_fuera_de_rango' => $es_fuera_de_rango, // <-- Devuelve la bandera
            'tipo_prueba' => $prueba->tipoPrueba->nombre ?? '',
        ];
    }
protected function getHeaderActions(): array
    {
        return [
            // Usamos la barra invertida \ para decirle a PHP que busque la clase exacta
            // Esta es la acción de PÁGINA (Header)
            \Filament\Actions\Action::make('regresar_buscar') 
                ->label('Buscar Otro Paciente')
                ->visible(fn() => auth()->user()->can('acceder_buscador_expedientes'))
                ->url(\App\Filament\Pages\BuscarExpediente::getUrl())
                ->icon('heroicon-o-magnifying-glass')
                ->color('gray'),

            \Filament\Actions\Action::make('regresar a clientes') 
                ->label('Lista de Clientes')
                ->visible(fn() => auth()->user()->can('view_any_clientes'))
                ->url($this->getResource()::getUrl('index'))
                ->icon('heroicon-o-users')
                ->color('gray'),
        ];
    }
   
}
