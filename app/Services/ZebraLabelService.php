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
^CI28
^FO5,5^GB396,193,2^FS
^FO20,20^A0N,28,28^FDLaboratorio Merino^FS
^FO20,50^A0N,18,18^FDPaciente: {$paciente}^FS
^FO20,75^A0N,18,18^FDRecipiente: {$recipiente}^FS
^FO20,100^A0N,18,18^FDFecha y Hora: {$fecha} {$hora}^FS
^FO20,120^GB366,1,1^FS
^FO30,135^ADN,8,4^FD-{$examen}^FS

^XZ\n\n";
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
        $examenTexto = strtoupper(substr($examen, 0, 16));
        $col = $index % 2; // 0 = primera columna, 1 = segunda
        $row = intdiv($index, 2); // fila dentro del bloque
        $posX = $col === 0 ? 30 : 200;
        $posY = $startY + ($lineHeight * $row);

        $examenLines .= "^FO{$posX},{$posY}^ADN,8,4^FD-{$examenTexto}^FS\n";
    }

    // Plantilla fija de la etiqueta
    $zpl .= "^XA
^PW406
^LL203
^CI28 
^FO5,5^GB396,193,2^FS
^FO20,20^A0N,28,28^FDLaboratorio Merino^FS
^FO20,50^A0N,18,18^FDPaciente: {$paciente}^FS
^FO20,75^A0N,18,18^FDRecipiente: {$recipiente}^FS
^FO20,100^A0N,18,18^FDFecha y Hora: {$fecha} {$hora}^FS
^FO20,120^GB366,1,1^FS
{$examenLines}
^XZ\n\n";
}

    foreach ($examenesSecereciones->values() as $examen) {
    $examenTexto = strtoupper(substr($examen, 0, 30));
    $posX = 30;
    $posY = 135;

    $zpl .= "^XA
^PW406
^LL203
^CI28 
^FO5,5^GB396,193,2^FS
^FO20,20^A0N,28,28^FDLaboratorio Merino^FS
^FO20,50^A0N,18,18^FDPaciente: {$paciente}^FS
^FO20,75^A0N,18,18^FDRecipiente: {$recipiente}^FS
^FO20,100^A0N,18,18^FDFecha y Hora: {$fecha} {$hora}^FS
^FO20,120^GB366,1,1^FS
^FO30,135^A0N,18,18^FD -{$examenTexto}
^XZ\n\n";
}

        return $zpl;
    }
}