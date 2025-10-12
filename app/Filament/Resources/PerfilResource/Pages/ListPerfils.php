<?php

namespace App\Filament\Resources\PerfilResource\Pages;

use App\Filament\Resources\PerfilResource;
use App\Models\Examen;
use App\Models\TipoExamen;
use Filament\Actions;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;

class ListPerfils extends ListRecords
{
    protected static string $resource = PerfilResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
    public function getTabs(): array
    {
        // Obtener los 3 tipos de examen con más exámenes asociados
        $topTipoIds = Examen::select('tipo_examen_id')
            ->groupBy('tipo_examen_id')
            ->orderByRaw('COUNT(*) DESC')
            ->limit(3)
            ->pluck('tipo_examen_id');

        // Cargar los nombres de esos tipos
        $tipos = TipoExamen::whereIn('id', $topTipoIds)->get();

        $tabs = [
            'Todos' => Tab::make()->label('Todos'),
        ];

        foreach ($tipos as $tipo) {
            $tabs[$tipo->nombre] = Tab::make()
                ->label($tipo->nombre)
                ->modifyQueryUsing(function ($query) use ($tipo) {
                    // Filtra perfiles que tengan exámenes de este tipo
                    $query->whereHas('examenes', function ($q) use ($tipo) {
                        $q->where('tipo_examen_id', $tipo->id);
                    });
                });
        }

        return $tabs;
    }
}
