<?php

namespace App\Http\Controllers;

use App\Models\DetalleOrden;
use App\Services\ZebraLabelService;
use Illuminate\Http\Request;

class ZplController extends Controller
{
    // Eliminamos la función sendToPrinter y $printerName, ya no sirven en la nube.

    public function single($id)
    {
        $detalle = DetalleOrden::with('orden.cliente', 'examen.tipoExamen')->findOrFail($id);

        $service = new ZebraLabelService();
        $zpl = $service->generarZpl($detalle);

        // DEVOLVEMOS JSON PARA QUE JAVASCRIPT LO LEA
        return response()->json([
            'success' => true,
            'zpl' => $zpl
        ]);
    }

    public function group($status, $ordenId)
    {
        $detalles = DetalleOrden::with('orden.cliente', 'examen.tipoExamen')
            ->where('status', $status)
            ->where('orden_id', $ordenId)
            ->get();

        if ($detalles->isEmpty()) {
            return response()->json(['success' => false, 'message' => 'No hay etiquetas']);
        }

        $service = new ZebraLabelService();
        $zpl = $service->generarZplMultiple($detalles);

        // DEVOLVEMOS JSON
        return response()->json([
            'success' => true,
            'zpl' => $zpl
        ]);
    }
    
    // Método nuevo para imprimir TODAS (botón "Generar ZPL" del header)
    public function all($ordenId)
    {
        $detalles = DetalleOrden::with('orden.cliente', 'examen.tipoExamen')
            ->where('orden_id', $ordenId)
            ->get();

        if ($detalles->isEmpty()) return response()->json(['success' => false]);

        $service = new ZebraLabelService();
        $zpl = $service->generarZplMultiple($detalles);

        return response()->json([
            'success' => true,
            'zpl' => $zpl
        ]);
    }
}