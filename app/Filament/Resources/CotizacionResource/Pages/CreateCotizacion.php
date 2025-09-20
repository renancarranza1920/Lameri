<?php

namespace App\Filament\Resources\CotizacionResource\Pages;

use App\Filament\Resources\OrdenResource;
use App\Models\Examen;
use App\Models\Perfil;
use Barryvdh\DomPDF\Facade\Pdf;
use Filament\Forms\Components\Actions\Action as FormAction;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Wizard;
use Filament\Forms\Components\Wizard\Step;
use Filament\Forms\Get;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Page as ResourcePage;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\HtmlString;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CreateCotizacion extends ResourcePage implements HasForms
{
    use InteractsWithForms;

    protected static string $resource = \App\Filament\Resources\CotizacionResource::class;
    protected static string $view = 'filament.pages.create-cotizacion-page';

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill();
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Wizard::make($this->getSteps())
                    ->submitAction(new HtmlString('<button type="submit" class="hidden"></button>')),
            ])
            ->statePath('data');
    }

    protected function getSteps(): array
    {
        return [
            Step::make('Datos del Cliente')
                ->schema([
                    TextInput::make('nombre_completo')
                        ->label('Nombre Completo del Cliente')
                        ->required(),
                    TextInput::make('whatsapp')
                        ->label('Número de WhatsApp')
                        ->tel()
                        ->prefix('+503')
                        ->helperText('Ingresar solo los 8 dígitos del número.')
                        ->required(),
                    TextInput::make('email')
                        ->label('Correo Electrónico (Opcional)')
                        ->email(),
                ]),

            Step::make('Selección de Estudios')
                ->schema(OrdenResource::getOrdenStep()),

            Step::make('Resumen')
                ->schema(fn (Get $get): array => [
                    \Filament\Forms\Components\View::make('resumen_detallado')
                        ->view('filament.forms.components.resumen-cotizacion')
                        ->viewData([
                            'nombre_cliente' => $get('nombre_completo'),
                            'perfilesSeleccionados' => $get('perfiles_seleccionados') ?? [],
                            'examenesSeleccionados' => $get('examenes_seleccionados') ?? [],
                        ]),
                ]),

            Step::make('Enviar y Descargar')
                ->schema([
                    Placeholder::make('acciones_finales')
                        ->label('Acciones')
                        ->content('Utilice los siguientes botones para descargar la cotización o compartirla.')
                        ->columnSpanFull(),

                    \Filament\Forms\Components\Actions::make([
                        FormAction::make('generarPdf')
                            ->label('Generar PDF')
                            ->icon('heroicon-o-document-arrow-down')
                            ->action(fn () => $this->generatePdfPreview(true)),

                        FormAction::make('enviarWhatsApp')
                            ->label('WhatsApp y Descargar PDF')
                            ->icon('heroicon-o-paper-airplane')
                            ->color('success')
                            ->action(function (Get $get) {
                                $numero = '503' . preg_replace('/[^0-9]/', '', $get('whatsapp'));
                                $mensaje = urlencode(
                                    "Hola {$get('nombre_completo')}, le saluda de Laboratorio Clínico Merino.\n\n" .
                                    "Le comparto el resumen de su cotización:\n\n" .
                                    $this->getTextSummary($get)
                                );
                                $whatsappUrl = "https://wa.me/{$numero}?text={$mensaje}";
                                $this->dispatch('open-url-in-new-tab', url: $whatsappUrl);
                                return $this->generatePdfPreview(true);
                            }),
                        
                        // 👇 ***** ¡AQUÍ ESTÁ LA MODIFICACIÓN! ***** 👇
                        FormAction::make('enviarEmail')
                            ->label('Gmail y Descargar PDF')
                            ->icon('heroicon-o-envelope')
                            ->color('info')
                            ->action(function (Get $get) {
                                $email = $get('email');
                                if (empty($email)) {
                                    Notification::make()
                                        ->title('Correo no especificado')
                                        ->body('Por favor, ingrese un correo en el primer paso para usar esta función.')
                                        ->warning()
                                        ->send();
                                    return; // Se usa 'return' para detener la acción
                                }
                                $subject = "Cotización de Servicios - Laboratorio Clínico Merino";
                                $body = "Hola {$get('nombre_completo')},\n\n" .
                                        "Gracias por solicitar una cotización con nosotros. Aquí tiene un resumen:\n\n" .
                                        $this->getTextSummary($get) . "\n\n" .
                                        "Quedamos a su entera disposición para cualquier consulta.\n\n" .
                                        "Atentamente,\n" .
                                        (Auth::user()?->name ?? 'Laboratorio Clínico Merino');

                                $gmailUrl = "https://mail.google.com/mail/?view=cm&fs=1&to=" . rawurlencode($email) . "&su=" . rawurlencode($subject) . "&body=" . rawurlencode($body);
                                
                                // 1. Envía el evento para abrir Gmail
                                $this->dispatch('open-url-in-new-tab', url: $gmailUrl);

                                // 2. Devuelve la descarga del PDF
                                return $this->generatePdfPreview(true);
                            }),
                    ])->columnSpanFull(),
                ]),
        ];
    }
    
    public function generatePdfPreview(bool $download = true)
    {
        $state = $this->form->getState();
        $total = 0;
        $dataPerfiles = [];
        foreach ($state['perfiles_seleccionados'] ?? [] as $item) {
            $perfil = Perfil::with('examenes')->find($item['perfil_id']);
            if ($perfil) {
                $precio = floatval($item['precio_hidden'] ?? $perfil->precio);
                $dataPerfiles[] = ['nombre' => $perfil->nombre, 'precio' => $precio, 'examenes' => $perfil->examenes];
                $total += $precio;
            }
        }
        $dataExamenes = [];
        foreach ($state['examenes_seleccionados'] ?? [] as $item) {
            $examen = Examen::find($item['examen_id']);
            if ($examen) {
                $precio = floatval($item['precio_hidden'] ?? $examen->precio);
                $dataExamenes[] = ['nombre' => $examen->nombre, 'precio' => $precio];
                $total += $precio;
            }
        }
        $data = [
            'cliente_nombre' => $state['nombre_completo'] ?? 'N/A',
            'perfiles' => $dataPerfiles,
            'examenes' => $dataExamenes,
            'total' => $total,
            'usuario_nombre' => Auth::user()?->name ?? 'N/A',
        ];
        $pdf = Pdf::loadView('pdf.cotizacion', $data)->setPaper('letter', 'portrait');
        
        if ($download) {
            return response()->streamDownload(fn() => print($pdf->stream()), 'cotizacion-' . date('Y-m-d') . '.pdf');
        } else {
            $nombreArchivo = 'cotizaciones/cotizacion-' . uniqid() . '.pdf';
            Storage::disk('public')->put($nombreArchivo, $pdf->output());
            return asset('storage/' . $nombreArchivo);
        }
    }

    protected function getTextSummary(Get $get): string
    {
        $total = 0;
        $lines = [];
        foreach ($get('perfiles_seleccionados') ?? [] as $item) {
            $perfil = Perfil::find($item['perfil_id']);
            if ($perfil) {
                $precio = floatval($item['precio_hidden'] ?? $perfil->precio);
                $lines[] = "*{$perfil->nombre}* - $" . number_format($precio, 2);
                $total += $precio;
            }
        }
        foreach ($get('examenes_seleccionados') ?? [] as $item) {
            $examen = Examen::find($item['examen_id']);
            if ($examen) {
                $precio = floatval($item['precio_hidden'] ?? $examen->precio);
                $lines[] = "*- {$examen->nombre}* - $" . number_format($precio, 2);
                $total += $precio;
            }
        }
        $lines[] = "\n*Total a Pagar:* $" . number_format($total, 2);
        return implode("\n", $lines);
    }
}

