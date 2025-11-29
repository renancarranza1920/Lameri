<?php

namespace App\Filament\Pages;

use App\Enums\RecipienteEnum;
use App\Models\DetalleOrden;
use App\Models\Orden;
use App\Services\ZebraLabelService;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Support\Collection;
use Mokhosh\FilamentKanban\Pages\KanbanBoard;
use Illuminate\Support\Facades\Auth; // <-- 1. IMPORTAR AUTH Y ACTIVITY

class DetalleOrdenKanban extends KanbanBoard
{
    #[\Livewire\Attributes\Url]
    public ?int $ordenId = null;

    protected static ?string $navigationLabel = 'Kanban Etiquetas';
    protected static ?string $title = 'Etiquetas de Exámenes';
    protected static string $model = DetalleOrden::class;
    protected static string $recordTitleAttribute = 'nombre_examen';

    protected static string $statusEnum = App\Enums\RecipienteEnum::class;

    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }


    public array $extraRecipientes = [];

    protected function getBoardStyles(): string
    {
        return 'w-full';
    }

    protected function getColumnWidth(): string
    {
        return 'w-full';
    }

    protected function getGridColumns(): string
    {
        return 'grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4';
    }

    protected function statuses(): Collection
    {
        return RecipienteEnum::statuses();
    }

    protected function records(): Collection
    {
        if (!$this->ordenId) return collect();

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
        // 🔒 VALIDACIÓN
        if (!auth()->user()->can('mover_etiquetas_kanban')) {
            Notification::make()->title('No tienes permiso para mover etiquetas.')->danger()->send();
            return; // Se detiene y no guarda el cambio
        }
        $detalle = DetalleOrden::find($recordId);
        $detalle->update(['status' => $status]);
        DetalleOrden::setNewOrder($toOrderedIds);

        // --- 2. REGISTRAR BITÁCORA MANUAL ---
        // Registra la acción sobre la Orden principal
        if ($detalle->orden) {
            activity()
                ->causedBy(Auth::user())
                ->performedOn($detalle->orden) 
                ->log("Movió la etiqueta '{$detalle->nombre_examen}' al estado '{$status}' en la Orden #{$this->ordenId}");
        }
    }

    public function onSortChanged(int|string $recordId, string $status, array $orderedIds): void
    {
        DetalleOrden::setNewOrder($orderedIds);
    }

    public function mount(): void
    {
        parent::mount();

        if ($this->ordenId) {
            $orden = Orden::find($this->ordenId);
            if ($orden) {
                static::$title = 'Etiquetas de Exámenes - Orden #' . $orden->id;
           
                if (session()->get('from_create_orden')) {
                    Notification::make()
                        ->title('Orden creada con éxito')
                        ->body('ID de la orden: ' . $orden->id)
                        ->success()
                        ->persistent()
                        ->send();
                    session()->forget('from_create_orden');
                }
            }
        }
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('Generar ZPL')
                ->label('Generar Etiquetas ZPL')
                ->icon('heroicon-o-printer')
                ->visible(fn() => auth()->user()->can('imprimir_etiquetas_kanban')) // 🔒 VALIDACIÓN VISUAL
                ->color('warning')
                ->action(fn() => $this->printAll()),
                Action::make('Volver a Ordenes')
                ->label('Volver a Ordenes')
                ->url(route('filament.admin.resources.ordenes.index'))
                ->openUrlInNewTab(false),
        ];

    }

    public function printGroup(string $status): void
    {
        if (!auth()->user()->can('imprimir_etiquetas_kanban')) {
            Notification::make()->title('Acceso denegado')->body('No tienes permiso para imprimir etiquetas.')->danger()->send();
            return;
        }
        if (!$this->ordenId) {
            Notification::make()->title('Orden no encontrada.')->danger()->send();
            return;
        }

        $detalles = DetalleOrden::with('orden.cliente')
            ->where('orden_id', $this->ordenId)
            ->where('status', $status)
            ->get();

        if ($detalles->isEmpty()) {
            Notification::make()->title('No hay detalles para generar ZPL.')->warning()->send();
            return;
        }

        try {
            $service = new ZebraLabelService();
            $zpl = $service->generarZplMultiple($detalles);
            $this->sendToPrinter($zpl);

            Notification::make()->title('Etiquetas enviadas a la impresora.')->success()->send();

            // --- 2. REGISTRAR BITÁCORA MANUAL ---
            activity()
                ->causedBy(Auth::user())
                ->performedOn(Orden::find($this->ordenId))
                ->log("Imprimió el grupo de etiquetas '{$status}' para la Orden #{$this->ordenId}");

        } catch (\Exception $e) {
            Notification::make()->title('Error al enviar las etiquetas: ' . $e->getMessage())->danger()->send();
        }
    }

    public function printAll(): void
    {
        if (!auth()->user()->can('imprimir_etiquetas_kanban')) {
            Notification::make()->title('Acceso denegado')->body('No tienes permiso para imprimir etiquetas.')->danger()->send();
            return;
        }
        if (!$this->ordenId) return;

        $detalles = DetalleOrden::with('orden.cliente')
            ->where('orden_id', $this->ordenId)
            ->get();

        if ($detalles->isEmpty()) {
            Notification::make()->title('No hay detalles para generar ZPL.')->warning()->send();
            return;
        }

        try {
            $service = new ZebraLabelService();
            $zpl = $service->generarZplMultiple($detalles);
            $this->sendToPrinter($zpl);

            Notification::make()->title('Etiquetas enviadas a la impresora.')->success()->send();
            
            // --- 2. REGISTRAR BITÁCORA MANUAL ---
            activity()
                ->causedBy(Auth::user())
                ->performedOn(Orden::find($this->ordenId))
                ->log("Imprimió TODAS las etiquetas para la Orden #{$this->ordenId}");

        } catch (\Exception $e) {
            Notification::make()->title('Error al enviar las etiquetas: ' . $e->getMessage())->danger()->send();
        }
    }

    private function sendToPrinter(string $zpl): void
    {
        $tempFile = tempnam(sys_get_temp_dir(), 'zpl');
        file_put_contents($tempFile, $zpl);
        exec("print /D:\\\\localhost\\ZebraZD230 " . escapeshellarg($tempFile));
        @unlink($tempFile);
    }

    public function printSingle(int $recordId): void
    {
        if (!auth()->user()->can('imprimir_etiquetas_kanban')) {
            Notification::make()->title('Acceso denegado')->body('No tienes permiso para imprimir etiquetas.')->danger()->send();
            return;
        }
        $detalle = DetalleOrden::with('orden.cliente')->find($recordId);

        if (!$detalle) {
            Notification::make()->title('Detalle no encontrado.')->danger()->send();
            return;
        }

        try {
            $service = new ZebraLabelService();
            $zpl = $service->generarZpl($detalle);
            $this->sendToPrinter($zpl);

            Notification::make()->title('Etiqueta enviada a la impresora.')->success()->send();

            // --- 2. REGISTRAR BITÁCORA MANUAL ---
            if ($detalle->orden) {
                activity()
                    ->causedBy(Auth::user())
                    ->performedOn($detalle->orden)
                    ->log("Imprimió una etiqueta individual ('{$detalle->nombre_examen}') para la Orden #{$this->ordenId}");
            }

        } catch (\Exception $e) {
            Notification::make()->title('Error al enviar la etiqueta: ' . $e->getMessage())->danger()->send();
        }
    }
}