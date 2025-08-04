<?php

namespace App\Filament\Pages;

use App\Enums\RecipienteEnum;
use App\Models\DetalleOrden;
use Filament\Actions\Action;
use Filament\Forms\Components\Builder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use App\Models\Orden;
use Livewire\Attributes\Url;

use Illuminate\Support\Collection;
use Log;
use Mokhosh\FilamentKanban\Pages\KanbanBoard;
use Str;

class DetalleOrdenKanban extends KanbanBoard
{
    // Añade esta propiedad para recibir el ID de la orden
    #[Url]
    public ?int $ordenId = null;

    protected static ?string $navigationLabel = 'Kanban Etiquetas';
    protected static ?string $title = 'Etiquetas de Exámenes';
    protected static string $model = DetalleOrden::class;
    protected static string $recordTitleAttribute = 'nombre_examen';

    public array $extraRecipientes = [];
    protected static string $statusEnum = App\Enums\RecipienteEnum::class;

protected function getBoardStyles(): string
{
    return 'w-full'; // Elimina el scroll horizontal
}

protected function getColumnWidth(): string
{
    return 'w-full'; // El ancho ahora lo controla el grid
}

// Opcional: para cambiar el número de columnas por breakpoint
protected function getGridColumns(): string
{
    return 'grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4';
}

    // Método para obtener los estados
    protected function statuses(): Collection
    {
        return RecipienteEnum::statuses();
    }

    // Modifica este método para usar el ordenId dinámico
    protected function records(): Collection
    {
        if (!$this->ordenId) {
            return collect();
        }
        
        return DetalleOrden::where('orden_id', $this->ordenId)
            ->ordered()
            ->get();
    }

    protected function getRecordTitle(): string
    {
        return $this->recordTitleAttribute;
    }

    public function onStatusChanged(int|string $recordId, string $status, array $fromOrderedIds, array $toOrderedIds): void
    {
        DetalleOrden::find($recordId)->update([
            'status' => $status,
        ]);
        DetalleOrden::setNewOrder($toOrderedIds);
    }

    public function onSortChanged(int|string $recordId, string $status, array $orderedIds): void
    {
        DetalleOrden::setNewOrder($orderedIds);
    }

    // Añade este método para establecer el título dinámico
    public function mount(): void
    {
        parent::mount();
        
        if ($this->ordenId) {
            $orden = Orden::find($this->ordenId);
            if ($orden) {
                static::$title = 'Etiquetas de Exámenes - Orden #' . $orden->id;
            }
        }
    }

    
}