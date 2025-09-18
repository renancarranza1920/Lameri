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
        // Crear archivo temporal con el contenido ZPL
        $tempFile = tempnam(sys_get_temp_dir(), 'zpl');
        file_put_contents($tempFile, $zpl);

        // Ejecutar el comando de impresión
        exec("print /D:{$this->printerName} " . escapeshellarg($tempFile), $output, $resultCode);

        // Eliminar el archivo temporal
        @unlink($tempFile);

        if ($resultCode !== 0) {
            throw new \Exception("No se pudo imprimir el archivo en la impresora {$this->printerName}");
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
            return null;
        } catch (\Exception $e) {
            Notification::make()
                ->title('Error al enviar la etiqueta: ' . $e->getMessage())
                ->danger()
                ->send();
            return null;
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
            return null;
        }

        $service = new ZebraLabelService();
        $zpl = $service->generarZplMultiple($detalles);

        try {
            $this->sendToPrinter($zpl);
            Notification::make()
                ->title('Etiquetas enviadas a la impresora')
                ->success()
                ->send();
            return null;
        } catch (\Exception $e) {
            Notification::make()
                ->title('Error al enviar las etiquetas: ' . $e->getMessage())
                ->danger()
                ->send();
            return null;
        }
    }
}
