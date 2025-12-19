<?php

namespace App\Services;

use App\Models\DetalleOrden;
use Log;

class ZebraLabelService
{
    public function generarZplMultiple($detalles): string
    {
        Log::info('Generando etiquetas ZPL agrupadas por color.');

        // Agrupar por color (recipiente)
        $agrupados = $detalles->groupBy(function ($detalle) {
            $recipiente = $detalle->status ?? 'Sin color';
            if (enum_exists(\App\Enums\RecipienteEnum::class) && \App\Enums\RecipienteEnum::tryFrom($recipiente)) {
                $recipiente = \App\Enums\RecipienteEnum::tryFrom($recipiente)?->getTitle();
            }
            return $recipiente;
        });

        Log::info('Agrupados por color:', $agrupados->toArray());

        // Generar etiquetas por cada color y sus detalles
        return $agrupados->map(function ($items, $color) {
            return $this->generarZplPorColor($color, $items);
        })->implode("\n\n");
    }

    public function generarZpl(DetalleOrden $detalle): string
    {
        date_default_timezone_set('America/El_Salvador');
        
        $examen = strtoupper(substr($detalle->nombre_examen, 0, 30));
        $paciente = strtoupper(substr($detalle->orden->cliente->nombre ?? 'PACIENTE', 0, 30));
        // Fecha actual en formato dd/mm/yyyy
        $fecha = date('d/m/Y');
        $hora = date('h:i A');
        // Usar el título del enum si es válido
        $recipiente = $detalle->status;
        if (enum_exists(\App\Enums\RecipienteEnum::class) && \App\Enums\RecipienteEnum::tryFrom($detalle->status)) {
            $recipiente = \App\Enums\RecipienteEnum::tryFrom($detalle->status)?->getTitle();
        }
        $ordenId = $detalle->orden->id;

        return "
^XA
^PW406
^LL203
^ci27

// --- SECCION 1: ENCABEZADO (FONDO NEGRO OPCIONAL O TEXTO CENTRADO) ---
// Marco Exterior


// Nombre del Laboratorio Centrado (^FB = Field Block para centrar)
^FO0,15^FB406,1,0,C,0^A0N,26,26^FDLABORATORIO MERINO^FS

// Línea separadora 1
^FO10,45^GB386,1,1^FS


// --- SECCION 2: INFORMACION DEL PACIENTE ---
// Paciente
^FO15,55^A0N,20,20^FDPaciente: {$paciente}^FS

// Recipiente y Fecha (En la misma línea para ahorrar espacio)
^FO15,80^A0N,20,20^FDRecip.: {$recipiente}^FS
^FO270,80^A0N,20,20^FD{$fecha}^FS

// Línea separadora 2
^FO10,105^GB386,1,1^FS


// --- SECCION 3: DETALLE DEL EXAMEN (DESTACADO) ---
// Nombre del examen centrado y un poco más grande
^FO0,120^FB406,2,0,C,0^A0N,24,24^FD{$examen}^FS


// --- SECCION 4: PIE DE PAGINA (Pequeño) ---
^FO15,175^A0N,18,18^FDHora: {$hora}^FS
^FO280,175^A0N,18,18^FDOrd: #{$ordenId}^FS

^XZ
\n\n";
    }

    private function generarZplPorColor($color, $items): string
    {
        date_default_timezone_set('America/El_Salvador');
        // === Paciente (primer nombre + primer apellido) ===
        $nombre = $items->first()->orden->cliente->nombre ?? 'PACIENTE';
        $apellido = $items->first()->orden->cliente->apellido ?? '';

        $tokensNombre = preg_split('/\s+/', trim($nombre));
        $primerNombre = $tokensNombre[0] ?? '';

        $tokensApellido = preg_split('/\s+/', trim($apellido));
        $primerApellido = $tokensApellido[0] ?? '';

        $paciente = strtoupper(trim($primerNombre . ' ' . $primerApellido));

        // Recipiente (color)
        $recipiente = $color;
        if (enum_exists(\App\Enums\RecipienteEnum::class) && \App\Enums\RecipienteEnum::tryFrom($color)) {
            $recipiente = \App\Enums\RecipienteEnum::tryFrom($color)?->getTitle();
        }

        Log::info("Generando etiquetas ZPL para recipiente: {$recipiente}, con " . count($items) . " exámenes.");
        // Fecha actual en formato dd/mm/yyyy
        $fecha = date('d/m/Y');
        $hora = date('h:i A');

        $examenesUnicos = collect();
        $examenesSecereciones = collect();
        foreach ($items as $detalle) {
           
            if($detalle->status == "cultivo_secreciones"){
                $examenesSecereciones->push($detalle->examen->nombre);
            }else{
                if ($detalle->examen && $detalle->examen->tipoExamen) {
                $examenesUnicos->push($detalle->examen->tipoExamen->nombre);
            }
            }
            
        }
        $examenesUnicos = $examenesUnicos->unique()->values();

        // === Agrupar exámenes en bloques de 4 ===
        $bloques = $examenesUnicos->chunk(4);

        $zpl = '';

        foreach ($bloques as $bloque) {
    $examenLines = '';
    $startY = 135;
    $lineHeight = 25;

    foreach ($bloque->values() as $index => $examen) {
        $examenTexto = strtoupper(substr($examen, 0, 20));
        $col = $index % 2; // 0 = primera columna, 1 = segunda
        $row = intdiv($index, 2); // fila dentro del bloque
        $posX = $col === 0 ? 30 : 200;
        $posY = $startY + ($lineHeight * $row);

        $examenLines .= "^FO{$posX},{$posY}^ADN,8,4^FD-{$examenTexto}^FS\n";
    }
$ordenId = $items->first()->orden->id;
    // Plantilla fija de la etiqueta
// Asegúrate de tener el ID disponible antes: $ordenId = $items->first()->orden->id;

$zpl .= "^XA
^PW406
^LL203
^ci27

// 1. ENCABEZADO

^FO0,15^FB406,1,0,C,0^A0N,24,24^FDLABORATORIO MERINO^FS
^FO10,42^GB386,1,1^FS

// 2. DATOS PACIENTE Y RECIPIENTE
^FO15,50^A0N,20,20^FDPaciente: {$paciente}^FS
^FO15,75^A0N,20,20^FDRecip.: {$recipiente}^FS
^FO270,75^A0N,20,20^FD{$fecha}^FS

// 3. SEPARADOR Y CUERPO (LISTA)
^FO10,105^GB386,1,1^FS
{$examenLines}

// 4. PIE DE PAGINA (Hora y Orden)
^FO15,180^A0N,15,15^FDHora: {$hora}^FS
// Si tienes la variable $ordenId disponible, descomenta la siguiente linea:
// ^FO280,180^A0N,18,18^FDOrd: #{$ordenId}^FS
^XZ\n\n";
}

    foreach ($examenesSecereciones->values() as $examen) {
    $examenTexto = strtoupper(substr($examen, 0, 30));
    $posX = 30;
    $posY = 135;

  // Dentro del foreach ($examenesSecereciones...)

$zpl .= "^XA
^PW406
^LL203
^ci27

// 1. ENCABEZADO

^FO0,15^FB406,1,0,C,0^A0N,24,24^FDLABORATORIO MERINO^FS
^FO10,42^GB386,1,1^FS

// 2. DATOS PACIENTE
^FO15,50^A0N,20,20^FDPaciente: {$paciente}^FS
^FO15,75^A0N,20,20^FDRecip.: {$recipiente}^FS
^FO270,75^A0N,20,20^FD{$fecha}^FS

// 3. SEPARADOR Y EXAMEN DESTACADO
^FO10,105^GB386,1,1^FS

// Nombre del examen centrado automáticamente en el área inferior
^FO0,125^FB406,2,0,C,0^A0N,24,24^FD{$examenTexto}^FS

// 4. PIE DE PAGINA
^FO15,180^A0N,15,15^FDHora: {$hora}^FS
// Si tienes $ordenId:
// ^FO280,180^A0N,18,18^FDOrd: #{$ordenId}^FS
^XZ\n\n";
}

        return $zpl;
    }
}
