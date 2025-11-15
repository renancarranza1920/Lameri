<?php

namespace App\Filament\Widgets;

use App\Models\Orden;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class EstadosOrdenesChart extends ChartWidget
{
    protected static ?string $heading = 'Distribución de Estados de Órdenes';
    protected static ?int $sort = -1; // Orden en el dashboard

    protected function getData(): array
    {
        $data = Orden::query()
            ->select('estado', DB::raw('count(*) as total'))
            ->groupBy('estado')
            ->get();

        return [
            'datasets' => [
                [
                    'label' => 'Órdenes',
                    'data' => $data->pluck('total')->toArray(),
                    'backgroundColor' => [
                        '#FFB020', // Pendiente (warning)
                        '#3B82F6', // En Proceso (info)
                        '#10B981', // Finalizado (success)
                        '#6B7280', // Pausada (danger)
                        '#9CA3AF', // Cancelado (gray)
                    ],
                ],
            ],
            'labels' => $data->pluck('estado')->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'doughnut'; // Gráfica de dona
    }
}