<?php

namespace App\Filament\Widgets;

use App\Models\Orden;
use Filament\Widgets\ChartWidget;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class OrdenesChart extends ChartWidget
{
    protected static ?string $heading = 'Órdenes en los Últimos 7 Días';
    protected static ?int $sort = -1; // Para que aparezca después de las estadísticas pero antes de la tabla

    protected function getData(): array
    {
        // Recupera el total de órdenes por día en los últimos 7 días.
        $data = Orden::query()
            ->select(DB::raw('DATE(created_at) as fecha'), DB::raw('count(*) as total'))
            ->whereBetween('created_at', [Carbon::now()->subDays(6)->startOfDay(), Carbon::now()->endOfDay()])
            
            // --- ¡CAMBIO CLAVE AQUÍ! ---
            // Agrupamos por la expresión completa para compatibilidad con ONLY_FULL_GROUP_BY
            ->groupBy(DB::raw('DATE(created_at)'), 'fecha') 
            
            ->orderBy('fecha', 'asc')
            ->get()
            ->pluck('total', 'fecha'); // Crea un array asociativo [fecha => total]

        // Prepara las etiquetas para los últimos 7 días (ej. 25/10, 26/10, etc.)
        $labels = [];
        for ($i = 6; $i >= 0; $i--) {
            $labels[] = Carbon::now()->subDays($i)->format('d/m');
        }

        // Prepara el conjunto de datos para la gráfica.
        $datasetData = [];
        for ($i = 6; $i >= 0; $i--) {
            $dateKey = Carbon::now()->subDays($i)->format('Y-m-d'); // Formato de llave para el array $data
            $datasetData[] = $data->get($dateKey, 0); // Obtiene el total o 0 si no hay órdenes ese día.
        }

        return [
            'datasets' => [
                [
                    'label' => 'Órdenes Creadas', // Leyenda del dataset
                    'data' => $datasetData, // Los datos de la gráfica
                    'backgroundColor' => 'rgba(54, 162, 235, 0.2)', // Color de fondo
                    'borderColor' => 'rgba(54, 162, 235, 1)', // Color de la línea
                    'tension' => 0.3, // Curvatura de la línea
                    'fill' => 'origin', // Rellena el área bajo la línea
                ],
            ],
            'labels' => $labels, // Las etiquetas del eje X
        ];
    }

    protected function getType(): string
    {
        // Como indicaste que era un "line chart", lo configuramos así.
        return 'line';
    }
}