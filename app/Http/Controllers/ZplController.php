<?php

namespace App\Http\Controllers;


use App\Models\DetalleOrden;
use App\Services\ZebraLabelService;
use Filament\Notifications\Notification;


class ZplController extends Controller
{
    private $printerName = '\\\\localhost\\ZebraZD230'; // Nombre del recurso compartido

    private function sendToPrinter($zpl)
    {
        // Abre el recurso de impresión
        $handle = @popen("print /D:{$this->printerName}", "w");

        if ($handle) {
            fwrite($handle, $zpl);
            pclose($handle);
        } else {
            throw new \Exception("No se pudo conectar a la impresora {$this->printerName}");
        }
    }

    public function single($id)
    {
        $detalle = DetalleOrden::with('orden.cliente')->findOrFail($id);

        $service = new ZebraLabelService();
        $zpl = $service->generarZpl($detalle);

        try {
            $this->sendToPrinter($zpl);
            Notification::make()
                ->title('Etiqueta enviada a la impresora')
                ->success()
                ->send();
            return response()->json(['success' => true, 'msg' => 'Etiqueta enviada a la impresora']);
        } catch (\Exception $e) {
            Notification::make()
                ->title('Error al enviar la etiqueta: ' . $e->getMessage())
                ->danger()
                ->send();
            return response()->json(['success' => false, 'msg' => $e->getMessage()], 500);
        }
    }

    public function group($status, $ordenId)
    {
        $detalles = DetalleOrden::with('orden.cliente')
            ->where('status', $status)
            ->where('orden_id', $ordenId)
            ->get();

        if ($detalles->isEmpty()) {
            Notification::make()
                ->title('No hay etiquetas para este grupo en esta orden.')
                ->danger()
                ->send();
            return response()->json(['error' => 'No hay etiquetas para este grupo en esta orden.'], 404);
        }

        $service = new ZebraLabelService();
        $zpl = $service->generarZplMultiple($detalles);

        try {
            $this->sendToPrinter($zpl);
            Notification::make()
                ->title('Etiquetas enviadas a la impresora')
                ->success()
                ->send();
            return response()->json(['success' => true, 'msg' => 'Etiquetas enviadas a la impresora']);
        } catch (\Exception $e) {
            Notification::make()
                ->title('Error al enviar las etiquetas: ' . $e->getMessage())
                ->danger()
                ->send();
            return response()->json(['success' => false, 'msg' => $e->getMessage()], 500);
        }
    }
}
