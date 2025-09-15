<?php

namespace App\Filament\Pages;

use App\Enums\RecipienteEnum;
use App\Models\DetalleOrden;
use App\Services\ZebraLabelService;
use Filament\Actions\Action;

use Symfony\Component\HttpFoundation\StreamedResponse;

use App\Models\Orden;
use Livewire\Attributes\Url;

use Illuminate\Support\Collection;
use Log;
use Mokhosh\FilamentKanban\Pages\KanbanBoard;
use Notification;
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
    public static function shouldRegisterNavigation(): bool
{
    return false;
}

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

protected function getHeaderActions(): array
{
    return [
        Action::make('Generar ZPL')
            ->label('Generar Etiquetas ZPL')
            ->icon('heroicon-o-printer')
            ->color('warning')
            ->action(function () {
                if (!$this->ordenId) {
                    Notification::make()
                        ->title('Orden no encontrada.')
                        ->danger()
                        ->send();
                    return null;
                }

                $detalles = DetalleOrden::with('orden.cliente')
                    ->where('orden_id', $this->ordenId)
                    ->get();

                if ($detalles->isEmpty()) {
                    Notification::make()
                        ->title('No hay detalles para generar ZPL.')
                        ->warning()
                        ->send();
                    return null;
                }

                $service = new ZebraLabelService();
                $zpl = $service->generarZplMultiple($detalles);

                return new StreamedResponse(function () use ($zpl) {
                    echo $zpl;
                }, 200, [
                    'Content-Type' => 'text/plain',
                    'Content-Disposition' => 'attachment; filename="etiquetas.zpl"',
                ]);
            }),
    ];
}
    
}