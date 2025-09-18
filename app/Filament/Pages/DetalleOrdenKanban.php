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
        DetalleOrden::find($recordId)->update(['status' => $status]);
        DetalleOrden::setNewOrder($toOrderedIds);
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
                ->action(fn() => $this->printAll()),
        ];
    }

    // === NUEVO MÉTODO PARA IMPRIMIR TODAS LAS ETIQUETAS ===
    public function printGroup(string $status): void
    {
        if (!$this->ordenId) {
            Notification::make()
                ->title('Orden no encontrada.')
                ->danger()
                ->send();
            return;
        }

        $detalles = DetalleOrden::with('orden.cliente')
            ->where('orden_id', $this->ordenId)
            ->where('status', $status)
            ->get();

        if ($detalles->isEmpty()) {
            Notification::make()
                ->title('No hay detalles para generar ZPL.')
                ->warning()
                ->send();
            return;
        }

        try {
            $service = new ZebraLabelService();
            $zpl = $service->generarZplMultiple($detalles);

            $this->sendToPrinter($zpl);

            Notification::make()
                ->title('Etiquetas enviadas a la impresora Zebra.')
                ->success()
                ->send();
        } catch (\Exception $e) {
            Notification::make()
                ->title('Error al enviar las etiquetas: ' . $e->getMessage())
                ->danger()
                ->send();
        }
    }

    // Método de prueba para el botón "Generar Etiquetas ZPL"
    public function printAll(): void
    {
        if (!$this->ordenId) return;

        $detalles = DetalleOrden::with('orden.cliente')
            ->where('orden_id', $this->ordenId)
            ->get();

        if ($detalles->isEmpty()) {
            Notification::make()
                ->title('No hay detalles para generar ZPL.')
                ->warning()
                ->send();
            return;
        }

        try {
            $service = new ZebraLabelService();
            $zpl = $service->generarZplMultiple($detalles);

            $this->sendToPrinter($zpl);

            Notification::make()
                ->title('Etiquetas enviadas a la impresora Zebra.')
                ->success()
                ->send();
        } catch (\Exception $e) {
            Notification::make()
                ->title('Error al enviar las etiquetas: ' . $e->getMessage())
                ->danger()
                ->send();
        }
    }

    // Método privado que ya existía
    private function sendToPrinter(string $zpl): void
    {
        $tempFile = tempnam(sys_get_temp_dir(), 'zpl');
        file_put_contents($tempFile, $zpl);

        exec("print /D:\\\\localhost\\ZebraZD230 " . escapeshellarg($tempFile));

        @unlink($tempFile);
    }

    // === NUEVO MÉTODO PARA IMPRIMIR UN REGISTRO INDIVIDUAL ===
public function printSingle(int $recordId): void
{
    $detalle = DetalleOrden::with('orden.cliente')->find($recordId);

    if (!$detalle) {
        Notification::make()
            ->title('Detalle no encontrado.')
            ->danger()
            ->send();
        return;
    }

    try {
        $service = new ZebraLabelService();
        $zpl = $service->generarZpl($detalle);

        $this->sendToPrinter($zpl);

        Notification::make()
            ->title('Etiqueta enviada a la impresora Zebra.')
            ->success()
            ->send();
    } catch (\Exception $e) {
        Notification::make()
            ->title('Error al enviar la etiqueta: ' . $e->getMessage())
            ->danger()
            ->send();
    }
}

}

