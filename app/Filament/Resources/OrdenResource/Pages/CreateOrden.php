<?php

namespace App\Filament\Resources\OrdenResource\Pages;

use App\Filament\Pages\DetalleOrdenKanban;
use App\Filament\Resources\OrdenResource;
use App\Models\cliente;
use App\Models\Examen;
use App\Models\Codigo;
use App\Models\Perfil;
use Auth;
use Barryvdh\DomPDF\Facade\Pdf;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\Pages\CreateRecord\Concerns\HasWizard;
use Filament\Forms\Components\Wizard\Step;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Throwable;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Filament\Support\Exceptions\Halt;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Number;
use Illuminate\Support\HtmlString;

class CreateOrden extends CreateRecord
{
    use HasWizard;

    protected static string $resource = OrdenResource::class;

    // Propiedades para manejar el estado del descuento en vivo
    public float $subtotal = 0;
    public float $descuento = 0;
    public ?Codigo $codigoAplicado = null;

    protected function getSteps(): array
    {
        return [
            Step::make('Cliente')
                ->schema(OrdenResource::getClienteStep()),

            Step::make('Orden')
                ->schema(OrdenResource::getOrdenStep()),

            Step::make('Resumen')
                ->schema(fn(Get $get): array => [
                    // 1. SECCIÓN DE CUPÓN MOVIDA AQUÍ
                    OrdenResource::getCuponSection(),

                    // 2. SECCIÓN DE RESUMEN FINANCIERO
                    Placeholder::make('resumen_costos')
                        ->label('Resumen Financiero')
                        ->content(function (Get $get) {
                            $this->calcularSubtotal($get);
                            $total = $this->subtotal - $this->descuento;

                            $html = "<div class='text-base space-y-1'>";
                            $html .= "<div><strong>Subtotal:</strong> <span class='float-right'>" . Number::currency($this->subtotal, 'USD') . "</span></div>";

                            if ($this->descuento > 0 && $this->codigoAplicado) {
                                $html .= "<div class='text-success-600'><strong>Descuento ({$this->codigoAplicado->codigo}):</strong> <span class='float-right'>- " . Number::currency($this->descuento, 'USD') . "</span></div>";
                            }

                            $html .= "<div class='text-lg font-bold border-t border-gray-300 pt-1 mt-1'><strong>Total a Pagar:</strong> <span class='float-right text-primary-600'>" . Number::currency($total, 'USD') . "</span></div>";
                            $html .= "</div>";

                            return new HtmlString($html);
                        })->columnSpanFull(),

                    // 3. SECCIÓN DE DETALLES (LISTA DE EXÁMENES)
                  // ... dentro de getSteps() -> Step 'Resumen'

\Filament\Forms\Components\View::make('filament.forms.components.resumen-orden')
    ->label('Resumen de la Orden')
    // 1. EL TRUCO: key() fuerza a repintar el componente si cambia el subtotal o el descuento
    ->key('view-resumen-' . $this->subtotal . '-' . $this->descuento)
    ->viewData([
        'cliente' => \App\Models\Cliente::find($get('cliente_id')),
        'perfilesSeleccionados' => $get('perfiles_seleccionados') ?? [],
        'examenesSeleccionados' => $get('examenes_seleccionados') ?? [],
        
        // 2. LA CORRECCIÓN: Usa $this-> propiedad, NO $get()
        'codigoAplicado' => $this->codigoAplicado, 
        'descuento'      => $this->descuento,      
        'subtotal'       => $this->subtotal,       
    

           // dd( Cliente::find($get('cliente_id')))
    ])


                ]),
        ];
    }

    // --- LÓGICA DE NEGOCIO (CALCULAR, LIMPIAR, APLICAR) ---

    protected function calcularSubtotal(callable $dataSource): void
    {
        $perfiles = $dataSource('perfiles_seleccionados') ?? [];
        $examenes = $dataSource('examenes_seleccionados') ?? [];

        $total = 0;
        foreach ($perfiles as $item)
            $total += floatval($item['precio_hidden'] ?? 0);
        foreach ($examenes as $item)
            $total += floatval($item['precio_hidden'] ?? 0);

        $this->subtotal = $total;
    }

    public function limpiarDescuento(): void
    {
        $this->descuento = 0;
        $this->codigoAplicado = null;
        // No limpiamos el input ($set) para no borrar lo que el usuario está escribiendo.
    }

    public function aplicarCodigo(?Get $get = null, ?Set $set = null): void
    {
        // Si no nos pasaron $get (llamada directa desde acción sin parámetros),
        // obtenemos el estado desde $this->form->getState()
        $state = $get ? null : $this->form->getState();

        // Reiniciamos y recalculamos
        $this->limpiarDescuento();

        if ($get) {
            // Modo normal (desde callback que pasa Get/Set)
            $this->calcularSubtotal($get);
            $codigoStr = $get('codigo_input');
        } else {
            // Modo defensivo (sin Get): usamos el estado obtenido previamente
            $this->calcularSubtotal(fn($key) => $state[$key] ?? null);
            $codigoStr = $state['codigo_input'] ?? null;
        }

        if (empty($codigoStr)) {
            Notification::make()->title('Por favor, ingresa un código.')->warning()->send();
            return;
        }

        $codigo = Codigo::where('codigo', $codigoStr)->first();

        if (!$codigo) {
            Notification::make()->title('Código no encontrado')->danger()->send();
            return;
        }

        if ($codigo->codigo !== $codigoStr) {
         Notification::make()
            ->title('Código inválido')
            ->body('Verifica las mayúsculas y minúsculas exactamente.')
            ->danger()
            ->send();
        return;
    }

        if (!$codigo->esValido()) {
            Notification::make()->title('Código no válido')->body('Expirado o límite alcanzado.')->danger()->send();
            return;
        }

        $nuevoTotal = $codigo->aplicarDescuento($this->subtotal);
        $this->descuento = $this->subtotal - $nuevoTotal;
        $this->codigoAplicado = $codigo;

        Notification::make()->title('¡Cupón aplicado!')->body('Descuento: ' . Number::currency($this->descuento, 'USD'))->success()->send();

        // Si nos pasaron $set y quieres actualizar el campo visualmente, puedes hacerlo:
        if ($set) {
            // opcional: dejar el input con el código aplicado (o vaciarlo)
            $set('codigo_input', $codigo->codigo);
        }
    }


    // --- MANEJO DE REGISTRO Y GUARDADO ---

    protected function handleRecordCreation(array $data): \Illuminate\Database\Eloquent\Model
    {
        return DB::transaction(function () use ($data) {
            $orden = static::getModel()::create($data);
            $this->record = $orden;

            if ($this->codigoAplicado) {
                $this->codigoAplicado->registrarUso();
            }

            session()->flash('from_create_orden', true);
            $state = $this->form->getState();
            $perfiles = $state['perfiles_seleccionados'] ?? [];
            $examenes = $state['examenes_seleccionados'] ?? [];

            foreach ($perfiles as $perfil) {
                $examenesPerfil = [];
                $precioPerfil = $perfil['precio_hidden'];
                $nombrePerfil = Perfil::find($perfil['perfil_id'])?->nombre ?? 'Perfil desconocido';

                Perfil::find($perfil['perfil_id'])?->examenes->each(function ($examen) use (&$examenesPerfil, $precioPerfil, $nombrePerfil) {
                    $examenesPerfil[] = [
                        'examen_id' => $examen->id,
                        'nombre_examen' => $examen->nombre,
                        'precio_examen' => $examen->precio,
                        'perfil_id' => $examen->pivot->perfil_id,
                        'recipiente' => $examen->recipiente,
                        'nombre_perfil' => $nombrePerfil,
                        'precio_perfil' => $precioPerfil,
                    ];
                });

                foreach ($examenesPerfil as $examenp) {
                    $orden->detalleOrden()->create([
                        'examen_id' => $examenp['examen_id'],
                        'perfil_id' => $examenp['perfil_id'],
                        'nombre_perfil' => $examenp['nombre_perfil'] ?? null,
                        'precio_perfil' => $examenp['precio_perfil'] ?? null,
                        'nombre_examen' => $examenp['nombre_examen'],
                        'precio_examen' => $examenp['precio_examen'],
                        'status' => $examenp['recipiente'] ?? null,
                    ]);
                }
            }

            foreach ($examenes as $examen) {
                $orden->detalleOrden()->create([
                    'examen_id' => $examen['examen_id'],
                    'nombre_examen' => $examen['nombre_examen'],
                    'precio_examen' => $examen['precio_hidden'],
                    'status' => $examen['recipiente'] ?? null
                ]);
            }

            return $orden;
        });
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $state = $this->form->getState();
        $this->calcularSubtotal(fn($path) => $state[$path] ?? null);

        $data['total'] = $this->subtotal - $this->descuento;
        $data['descuento'] = $this->descuento;
        $data['codigo_id'] = $this->codigoAplicado?->id;

        $data['fecha'] = Carbon::now();
        $data['estado'] = 'pendiente';

        return $data;
    }

    protected function beforeCreate(): void
    {
        $state = $this->form->getState();
        $perfiles = $state['perfiles_seleccionados'] ?? [];
        $examenes = $state['examenes_seleccionados'] ?? [];

        if (empty($perfiles) && empty($examenes)) {
            Notification::make()
                ->title('Debe seleccionar al menos un perfil o un examen.')
                ->danger()
                ->persistent()
                ->send();
            throw new Halt();
        }
    }

    protected function getRedirectUrl(): string
    {
        return DetalleOrdenKanban::getUrl(['ordenId' => $this->record->id]);
    }

    public function generatePdfPreview(): StreamedResponse
    {
        // 1. Recolectar el estado actual del formulario
        $state = $this->form->getState();

        $cliente = Cliente::find($state['cliente_id'] ?? null);
        $perfilesSeleccionados = $state['perfiles_seleccionados'] ?? [];
        $examenesSeleccionados = $state['examenes_seleccionados'] ?? [];


        // --- ¡LÓGICA DE TOTAL ACTUALIZADA! ---
        $this->calcularSubtotal(fn($path) => $state[$path] ?? null);
        $total = $this->subtotal - $this->descuento;
        // --- FIN ---

        // ---> ¡NUEVO! Obtenemos el usuario autenticado
        $usuarioNombre = Auth::user() ? Auth::user()->name : 'N/A';
        $dataPerfiles = [];
        foreach ($perfilesSeleccionados as $item) {
            $perfil = Perfil::with('examenes')->find($item['perfil_id']);
            if ($perfil) {
                $precio = floatval($item['precio_hidden'] ?? $perfil->precio);
                $dataPerfiles[] = ['nombre' => $perfil->nombre, 'precio' => $precio, 'examenes' => $perfil->examenes];
                // El total ya se calcula arriba
            }
        }

        $dataExamenes = [];
        foreach ($examenesSeleccionados as $item) {
            $examen = Examen::find($item['examen_id']);
            if ($examen) {
                $precio = floatval($item['precio_hidden'] ?? $examen->precio);
                $dataExamenes[] = ['nombre' => $examen->nombre, 'precio' => $precio];
                // El total ya se calcula arriba
            }
        }

        // 2. Preparar datos y generar el PDF
        $data = [
            'cliente' => $cliente,
            'perfiles' => $dataPerfiles,
            'examenes' => $dataExamenes,
            'total' => $total, // <-- Total con descuento
            'subtotal' => $this->subtotal, // <-- Subtotal
            'descuento' => $this->descuento, // <-- Descuento
            'codigo' => $this->codigoAplicado?->codigo, // <-- Código
            'usuario_nombre' => $usuarioNombre,
        ];

    $pdf = Pdf::loadView('pdf.comprobante', $data)->setPaper('letter', 'portrait');

    // 3. Enviar el PDF al navegador para descarga
    return new StreamedResponse(function () use ($pdf) {
        echo $pdf->output();
    }, 200, [
        'Content-Type' => 'application/pdf',
        'Content-Disposition' => 'attachment; filename="comprobante-preliminar.pdf"',
    ]);
    }


}