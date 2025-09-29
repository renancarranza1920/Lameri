<?php

namespace App\Filament\Resources\ReactivoResource\Pages;

use App\Filament\Resources\ReactivoResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Components\Tab; // <-- ¡Importante!
use Illuminate\Database\Eloquent\Builder; // <-- ¡Importante!

class ListReactivos extends ListRecords
{
    protected static string $resource = ReactivoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }



public function getTabs(): array
{
    // Función para crear la consulta base de los reactivos que necesitan atención
    $crearConsultaReabastecer = function (Builder $query) {
        $query
            // 1. Empezamos con los caducados o agotados
            ->whereIn('estado', ['caducado', 'agotado'])
            // 2. Nos aseguramos de que NO EXISTA un reemplazo disponible
            ->whereNotExists(function ($subQuery) {
                $subQuery->selectRaw(1)
                         ->from('reactivos as r2')
                         ->whereColumn('r2.nombre', 'reactivos.nombre')
                         ->whereColumn('r2.prueba_id', 'reactivos.prueba_id')
                         ->where('r2.estado', 'disponible');
            })
            // 3. ¡LA NUEVA LÓGICA! Seleccionamos solo el registro MÁS RECIENTE de su grupo
            ->whereIn('id', function ($subQuery) {
                $subQuery->selectRaw('max(id)')
                         ->from('reactivos')
                         ->groupBy('nombre', 'prueba_id');
            });
    };

    // Función para los históricos (esta no cambia)
    $crearConsultaHistoricos = function (Builder $query) {
        $query
            ->whereIn('estado', ['caducado', 'agotado'])
            ->whereExists(function ($subQuery) {
                $subQuery->selectRaw(1)
                         ->from('reactivos as r2')
                         ->whereColumn('r2.nombre', 'reactivos.nombre')
                         ->whereColumn('r2.prueba_id', 'reactivos.prueba_id')
                         ->where('r2.estado', 'disponible');
            });
    };

    return [
        'todos' => Tab::make('Todos')
            ->badge(static::getModel()::count()),

        'disponibles' => Tab::make('Disponibles')
            ->modifyQueryUsing(fn (Builder $query) => $query->where('estado', 'disponible'))
            ->badge(static::getModel()::where('estado', 'disponible')->count())
            ->badgeColor('success'),
        
        'por_reabastecer' => Tab::make('Por Reabastecer')
            ->modifyQueryUsing($crearConsultaReabastecer)
            ->badge(static::getModel()::query()->where(fn (Builder $q) => $crearConsultaReabastecer($q))->count())
            ->badgeColor('danger'),

        'historicos' => Tab::make('Históricos')
            ->modifyQueryUsing($crearConsultaHistoricos)
            ->badge(static::getModel()::query()->where(fn (Builder $q) => $crearConsultaHistoricos($q))->count())
            ->badgeColor('gray'),
    ];
}
}