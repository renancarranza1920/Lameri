<x-filament-panels::page>
    <div class="space-y-4">
        {{ $this->form }}

        <x-filament::button wire:click="createValorReferencia" color="primary">
            Guardar valor de referencia
        </x-filament::button>
    </div>
</x-filament-panels::page>
