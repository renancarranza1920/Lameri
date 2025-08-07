<x-filament-panels::page>

    <div 
        x-data 
        wire:ignore.self 
        class="w-full pb-4"
    >
        {{-- Grid de columnas --}}
        <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-3 gap-4  px-3">
         
             @foreach($statuses as $status)
                @include(static::$statusView, [
                    'columnWidth' => $this->getColumnWidth() ?? 'w-full' // Ancho controlado por el grid
                ])
            @endforeach
        </div>

        <div wire:ignore>
            @include(static::$scriptsView)
        </div>
    </div>

    @unless($disableEditModal)
        <x-filament-kanban::edit-record-modal/>
    @endunless
</x-filament-panels::page>