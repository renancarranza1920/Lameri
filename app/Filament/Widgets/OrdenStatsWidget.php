<?php

namespace App\Filament\Widgets;

use App\Models\Cliente;
use App\Models\Orden;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Number; // Asegúrate de importar esto si usas Laravel 9+

class OrdenStatsWidget extends BaseWidget
{
    // Define el número de columnas que ocupará en el dashboard.
    // 'full' significa que ocupará todo el ancho disponible.
    // También puedes usar números como 1, 2, 3, etc.
    protected int | string | array $columnSpan = 'full';
    
    // Define el orden en el que aparecerá en el dashboard.
    // Un número más bajo significa que aparecerá antes.
    protected static ?int $sort = -2;

    protected function getStats(): array
    {
        // Calcular ingresos de hoy
        $ingresosHoy = Orden::whereDate('created_at', today())->sum('total');

        return [
            Stat::make('Órdenes de Hoy', Orden::whereDate('created_at', today())->count())
                ->description('Total de órdenes registradas hoy')
                ->color('success'),
            Stat::make('Órdenes Pendientes', Orden::where('estado', 'pendiente')->count())
                ->description('Órdenes esperando toma de muestra')
                ->color('warning'),
            Stat::make('Ingresos de Hoy', Number::currency($ingresosHoy, 'USD')) // Formato de moneda
                ->description('Suma de los totales de hoy')
                ->color('info'),
            Stat::make('Total de Clientes', Cliente::count())
                ->description('Clientes registrados en el sistema')
                ->color('gray'),
        ];
    }
}