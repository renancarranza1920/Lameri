<x-filament-panels::page>
    <form wire:submit="save">
        {{ $this->form }}
        <div class="mt-6">
             <x-filament-panels::form.actions 
                :actions="$this->getFormActions()"
            /> 
        </div>
    </form>
</x-filament-panels::page>