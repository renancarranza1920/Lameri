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
   public ?string $activeTab = 'disponibles';

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }


public function getTableQueryStringIdentifier(): ?string
{
    return $this->activeTab;
}
public function getActiveTab(): ?string
{
    return $this->activeTab;
}






public function getTabs(): array
{
    return [
        'disponibles' => Tab::make('Disponibles')
            ->modifyQueryUsing(fn (Builder $query) => $query->where('estado', 'disponible'))
            ->badge(static::getModel()::where('estado', 'disponible')->count())
            ->badgeColor('success'),
        
        'por_reabastecer' => Tab::make('Por Reabastecer')
            ->modifyQueryUsing(fn (Builder $query) => 
                $query->whereIn('estado', ['agotado', 'caducado'])
                      ->where('es_historico', false) // Solo los que faltan atender
            )
            ->badge(static::getModel()::whereIn('estado', ['agotado', 'caducado'])->where('es_historico', false)->count())
            ->badgeColor('danger'),

        'historicos' => Tab::make('Históricos')
            ->modifyQueryUsing(fn (Builder $query) => $query->where('es_historico', true))
            ->badge(static::getModel()::where('es_historico', true)->count())
            ->badgeColor('gray'),
    ];
}

}