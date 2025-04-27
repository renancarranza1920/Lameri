<div class="grid grid-cols-2 gap-6 p-4 transition-all duration-300">
    {{-- Lista de disponibles --}}
    <div class="rounded-2xl border shadow-sm p-4 bg-white dark:bg-gray-800 transition">
        <div class="flex justify-between items-center mb-2">
            <h3 class="text-lg font-semibold">Exámenes Disponibles</h3>
            <button type="button" wire:click="toggleDisponibles"
                class="text-sm text-blue-600 hover:underline">-</button>
        </div>

        <input type="text" wire:model="busquedaDisponible" wire:keyup="$refresh" placeholder="Buscar..."
            class="w-full p-2 mb-4 border rounded-lg dark:bg-gray-700 dark:text-white" />

        <div class="space-y-4 transition-all duration-500 overflow-hidden"
            wire:key="lista-disponibles-{{ $busquedaDisponible }}" @class([
                'max-h-0 opacity-0 pointer-events-none' => $colapsadoDisponible,
                'max-h-[1000px] opacity-100' => !$colapsadoDisponible,
            ])>
            @forelse($agrupadosDisponibles as $tipo => $examenes)
                <div class="p-3 rounded-xl shadow-md bg-gray-100 dark:bg-gray-700">
                    <h4 class="font-bold mb-2">{{ $tipo }}</h4>
                    <div class="flex flex-wrap gap-2">
                        @foreach($examenes as $examen)
                        <style>
    .examen-tag {
        cursor: pointer;
        background-color: white;
        padding: 0.25rem 0.75rem;
        font-size: 0.875rem;
        border-radius: 9999px;
        box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
        transition: all 0.2s ease;
    }
    
    .dark .examen-tag {
        background-color: #4b5563; /* bg-gray-600 */
    }
    
    .examen-tag:hover {
        background-color: #22c55e !important; /* bg-success-500 */
        color: white !important;
    }
</style>

<div wire:click="addExamen({{ $examen->id }})" class="examen-tag">
    {{ $examen->nombre }}
</div>
                        @endforeach
                    </div>
                </div>
            @empty
                <p class="text-sm text-gray-500 dark:text-gray-300">No hay exámenes.</p>
            @endforelse
        </div>
    </div>

    {{-- Lista de seleccionados --}}
    <div class="rounded-2xl border shadow-sm p-4 bg-white dark:bg-gray-800 transition">
        <div class="flex justify-between items-center mb-2">
            <h3 class="text-lg font-semibold">Exámenes Seleccionados</h3>
            <button type="button" wire:click="toggleSeleccionados"
                class="text-sm text-blue-600 hover:underline">-</button>
        </div>

        <input type="text" wire:model="busquedaSeleccionado" wire:keyup="$refresh" placeholder="Buscar..."
            class="w-full p-2 mb-4 border rounded-lg dark:bg-gray-700 dark:text-white" />

        <div class="space-y-4 transition-all duration-500 overflow-hidden"
            style="{{ $colapsadoSeleccionado ? 'max-height: 0; opacity: 0;' : 'max-height: 1000px; opacity: 1;' }}">
            @forelse($agrupadosSeleccionados as $tipo => $examenes)
                <div class="p-3 rounded-xl shadow-md bg-gray-100 dark:bg-gray-700">
                    <h4 class="font-bold mb-2">{{ $tipo }}</h4>
                    <div class="flex flex-wrap gap-2">
                        @foreach($examenes as $examen)
                            <div wire:click="removeExamen({{ $examen['id'] }})"
                                style="cursor: pointer; background-color:#22c55e; color: white; padding: 0.25rem 0.75rem; font-size: 0.875rem; border-radius: 9999px; box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05); transition: all 0.2s ease;"
                                onmouseover="this.style.backgroundColor='#ba2b39'"
                                onmouseout="this.style.backgroundColor='#22c55e'">
                                {{ $examen['nombre'] }}
                            </div>
                        @endforeach
                    </div>
                </div>
            @empty
                <p class="text-sm text-gray-500 dark:text-gray-300">No hay exámenes seleccionados.</p>
            @endforelse
        </div>
    </div>

    @push('scripts')
        <script>
            document.addEventListener('livewire:initialized', () => {
                // Emitir inicialmente los exámenes seleccionados
                Livewire.emit('examenesSeleccionadosUpdated', @json($examenesSeleccionados));

                // Escuchar eventos desde Filament
                window.addEventListener('filament:submit', () => {
                    Livewire.emit('prepareForSave');
                });
            });
        </script>
    @endpush
</div>