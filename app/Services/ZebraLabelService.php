<?php

namespace App\Services;

use App\Models\DetalleOrden;

class ZebraLabelService
{
public function generarZpl(DetalleOrden $detalle): string
{
    $logoGrf = file_get_contents(storage_path('app/public/logo.grf')); // asegúrate de que exista
    $examen = strtoupper(substr($detalle->nombre_examen, 0, 30));
    $paciente = strtoupper(substr($detalle->orden->cliente->nombre ?? 'PACIENTE', 0, 30));
    $recipiente = strtoupper(substr($detalle->status, 0, 30));
    $ordenId = $detalle->orden->id;

    return "$logoGrf

^XA
^CF0,30
^FO30,20^GB720,380,2^FS

^FO580,30^XGE:LOGO.GRF,1,1^FS

^FO50,40^ADN,30,20^FDLABORATORIO CLÍNICO^FS
^FO50,80^ADN,28,14^FDExamen:^FS
^FO180,80^ADN,28,14^FD$examen^FS

^FO50,120^ADN,28,14^FDPaciente:^FS
^FO180,120^ADN,28,14^FD$paciente^FS

^FO50,160^ADN,28,14^FDRecipiente:^FS
^FO180,160^ADN,28,14^FD$recipiente^FS

^FO50,210^ADN,28,14^FDOrden ID:^FS
^FO180,210^ADN,28,14^FD$ordenId^FS

^BY2,2,60
^FO180,260^BCN,60,Y,N,N
^FD$ordenId^FS

^XZ";
}




    public function generarZplMultiple($detalles): string
    {
        return $detalles->map(fn($d) => $this->generarZpl($d))->implode("\n\n");
    }
}
