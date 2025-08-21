<?php

namespace App\Http\Controllers;

use App\Models\DetalleOrden;
use App\Services\ZebraLabelService;

class ZplController extends Controller
{
    public function single($id)
    {
        $detalle = DetalleOrden::with('orden.cliente')->findOrFail($id);

        $service = new ZebraLabelService();
        $zpl = $service->generarZpl($detalle);

        return response()->streamDownload(function () use ($zpl) {
            echo $zpl;
        }, "etiqueta_{$detalle->id}.zpl", [
            'Content-Type' => 'text/plain',
        ]);
    }

    
public function group($status, $ordenId)
{
    // Filtrar por status y ordenId
    $detalles = DetalleOrden::with('orden.cliente')
        ->where('status', $status)
        ->where('orden_id', $ordenId)
        ->get();

    if ($detalles->isEmpty()) {
        return response()->json(['error' => 'No hay etiquetas para este grupo en esta orden.'], 404);
    }

    $service = new ZebraLabelService();
    $zpl = $service->generarZplMultiple($detalles);

    return response()->streamDownload(function () use ($zpl) {
        echo $zpl;
    }, "etiquetas_grupo_{$status}_orden_{$ordenId}.zpl", [
        'Content-Type' => 'text/plain',
    ]);
}
}