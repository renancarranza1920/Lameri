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
    '#34D399' , // succes (Más suave)
    '#60A5FA', // En Proceso (Más suave)
    '#ffcb47ff', // pendiente (Más suave)
    '#F87171', // Pausada (Más suave)
    '#9CA3AF', // Cancelado (Gris medio)
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