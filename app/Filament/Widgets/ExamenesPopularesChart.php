<?php

namespace App\Filament\Widgets;

use App\Models\DetalleOrden;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class ExamenesPopularesChart extends ChartWidget
{
    protected static ?string $heading = 'Top 5 Exámenes Más Solicitados';
    protected static ?int $sort = 0; // Orden en el dashboard

    protected function getData(): array
    {
        $data = DetalleOrden::query()
            ->join('examens', 'detalle_orden.examen_id', '=', 'examens.id')
            ->select('examens.nombre', DB::raw('count(detalle_orden.id) as total'))
            ->groupBy('examens.nombre')
            ->orderBy('total', 'desc')
            ->limit(5)
            ->get();

        return [
            'datasets' => [
                [
                    'label' => 'Cantidad Solicitada',
                    'data' => $data->pluck('total')->toArray(),
                    'backgroundColor' => 'rgba(54, 162, 235, 0.5)',
                    'borderColor' => 'rgba(54, 162, 235, 1)',
                ],
            ],
            'labels' => $data->pluck('nombre')->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'bar'; // Gráfica de barras
    }
}