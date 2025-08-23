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
            return $detalle->status ?? 'Sin color';
        });

        Log::info('Agrupados por color:', $agrupados->toArray());

        // Generar etiquetas por cada color y sus detalles
        return $agrupados->map(function ($items, $color) {
            return $this->generarZplPorColor($color, $items);
        })->implode("\n\n");
    }
public function generarZpl(DetalleOrden $detalle): string
{
    $examen = strtoupper(substr($detalle->nombre_examen, 0, 30));
    $paciente = strtoupper(substr($detalle->orden->cliente->nombre ?? 'PACIENTE', 0, 30));
        // Fecha actual en formato dd/mm/yyyy
        $fecha = date('d/m/Y');
    $recipiente = strtoupper(substr($detalle->status, 0, 30));
    $ordenId = $detalle->orden->id;

    return "
^XA
^PW400
^LL200

^FO5,5^GB390,190,2^FS

^FO20,20^ADN,18,10^FDLABORATORIO CLINICO MERINO^FS

^FO20,50^ADN,14,7^FDPaciente:^FS
^FO130,50^ADN,14,7^FD{$paciente}^FS

^FO20,70^ADN,14,7^FDRecipiente:^FS
^FO155,70^ADN,14,7^FD{$recipiente}^FS

^FO360,40^ADR,8,4^FD{$fecha}^FS

^FO030,100^ADN,8,4^FD-{$examen}^FS

^XZ\n\n";


}







    private function generarZplPorColor($color, $items): string
    {
        // === Paciente (primer nombre + primer apellido) ===
        $nombre = $items->first()->orden->cliente->nombre ?? 'PACIENTE';
        $apellido = $items->first()->orden->cliente->apellido ?? '';

        $tokensNombre = preg_split('/\s+/', trim($nombre));
        $primerNombre = $tokensNombre[0] ?? '';

        $tokensApellido = preg_split('/\s+/', trim($apellido));
        $primerApellido = $tokensApellido[0] ?? '';

        $paciente = strtoupper(trim($primerNombre . ' ' . $primerApellido));

        // Recipiente (color)
        $recipiente = strtoupper(substr($color, 0, 12));

        // Fecha actual en formato dd/mm/yyyy
        $fecha = date('d/m/Y');

        // === Agrupar exámenes en bloques de 4 ===
        $bloques = $items->chunk(4);

        $zpl = '';

        foreach ($bloques as $bloque) {
            $examenLines = '';
            $startY = 100;   // inicio fijo debajo del encabezado y fecha
            $lineHeight = 25;
            $posX = 30;

            foreach ($bloque->values() as $index => $detalle) {
                $examen = strtoupper(substr($detalle->nombre_examen, 0, 25));
                $posY = $startY + ($lineHeight * $index);
                // 📌 fuente más pequeña
                $examenLines .= "^FO{$posX},{$posY}^ADN,8,4^FD-{$examen}^FS\n";
            }

            // Plantilla fija de la etiqueta
            $zpl .= "^XA
^PW400
^LL200

^FO5,5^GB390,190,2^FS

^FO20,20^ADN,18,10^FDLABORATORIO CLINICO MERINO^FS

^FO20,50^ADN,14,7^FDPaciente:^FS
^FO130,50^ADN,14,7^FD{$paciente}^FS

^FO20,70^ADN,14,7^FDRecipiente:^FS
^FO155,70^ADN,14,7^FD{$recipiente}^FS

^FO360,40^ADR,8,4^FD{$fecha}^FS

{$examenLines}

^XZ\n\n";
        }

        return $zpl;
    }
}