@php
    $total = 0;
@endphp
<div>
    {{-- La parte visual del resumen se queda igual --}}
    <div class="p-6 bg-white dark:bg-gray-800 rounded-lg border dark:border-gray-700">
        {{-- ... (todo el HTML de tu resumen visual: encabezado, cliente, tabla, total, nota preliminar) ... --}}
        {{-- Para abreviar, no pego todo de nuevo, solo asegúrate que esté aquí --}}
        
        <div class="flex justify-between items-center border-b pb-4 mb-6 dark:border-gray-700">
            <div class="text-left">
                <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Resumen de Orden</h2>
                <p class="text-sm text-gray-500 dark:text-gray-400">Fecha de Emisión: {{ now()->translatedFormat('d \d\e F \d\e Y') }}</p>
            </div>
        </div>
        @if ($cliente)
            <div class="mb-6">
                <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-200 mb-2">Cliente</h3>
                <div class="text-sm text-gray-700 dark:text-gray-300">
                    <p><strong>Expediente:</strong> {{ $cliente->NumeroExp }}</p>
                    <p><strong>Nombre:</strong> {{ $cliente->nombre }} {{ $cliente->apellido }}</p>
                </div>
            </div>
        @endif
        <table class="w-full text-left">
            <thead><tr><th class="pb-2 border-b text-sm font-semibold text-gray-600 dark:text-gray-400">Descripción</th><th class="pb-2 border-b text-sm font-semibold text-gray-600 dark:text-gray-400 text-right">Precio</th></tr></thead>
            <tbody class="divide-y dark:divide-gray-700">
                @if (!empty($perfilesSeleccionados))
                    @foreach ($perfilesSeleccionados as $item)
                        @php
                            $perfil = \App\Models\Perfil::with('examenes')->find($item['perfil_id']);
                            if (!$perfil) continue;
                            $precioPerfil = floatval($item['precio_hidden'] ?? $perfil->precio);
                            $total += $precioPerfil;
                        @endphp
                        <tr class="font-bold"><td class="py-3 pr-4">{{ $perfil->nombre }}</td><td class="py-3 text-right font-mono">${{ number_format($precioPerfil, 2) }}</td></tr>
                        @if ($perfil->examenes->isNotEmpty())
                            @foreach ($perfil->examenes as $examen)
                                <tr class="text-sm text-gray-600 dark:text-gray-400"><td class="pt-0 pb-2 pl-6 pr-4">- {{ $examen->nombre }}</td><td></td></tr>
                            @endforeach
                        @endif
                    @endforeach
                @endif
                @if (!empty($examenesSeleccionados))
                    @foreach ($examenesSeleccionados as $item)
                        @php
                            $examen = \App\Models\Examen::find($item['examen_id']);
                            if (!$examen) continue;
                            $precioExamen = floatval($item['precio_hidden'] ?? $examen->precio);
                            $total += $precioExamen;
                        @endphp
                        <tr><td class="py-3 pr-4">{{ $examen->nombre }}</td><td class="py-3 text-right font-mono">${{ number_format($precioExamen, 2) }}</td></tr>
                    @endforeach
                @endif
            </tbody>
        </table>
        @if($total > 0)
        <div class="mt-6 pt-4 border-t-2 border-gray-300 dark:border-gray-600">
            <div class="flex justify-between items-center text-xl font-bold">
                <span class="text-gray-900 dark:text-white">Total a Pagar:</span>
                <span class="font-mono text-success-600 dark:text-success-500">${{ number_format($total, 2) }}</span>
            </div>
        </div>
        @endif
        <div class="mt-8 p-3 text-center bg-gray-50 dark:bg-gray-900/50 rounded-lg">
            <p class="text-xs text-gray-500 dark:text-gray-400">
                <svg xmlns="http://www.w3.org/2000/svg" class="inline-block w-4 h-4 mr-1" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" /></svg>
                Este es un comprobante preliminar. La orden aún no ha sido confirmada.
            </p>
        </div>
    </div>

    <!-- 👇 ***** ESTE ES EL NUEVO BOTÓN CONECTADO A LIVEWIRE ***** 👇 -->
    <div class="mt-6 flex justify-end">
        <button
            type="button"
            wire:click="generatePdfPreview"
            wire:loading.attr="disabled"
            class="fi-btn fi-btn-size-md fi-btn-color-primary fi-btn-icon-position-before fi-btn-style-filled rounded-lg shadow-sm"
        >
            <!-- Icono de Carga (aparece mientras se genera el PDF) -->
            <svg wire:loading wire:target="generatePdfPreview" class="animate-spin w-5 h-5 mr-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            <!-- Icono Normal (se oculta durante la carga) -->
            <svg wire:loading.remove wire:target="generatePdfPreview" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-5 h-5 mr-2">
                <path d="M10.75 2.75a.75.75 0 0 0-1.5 0v8.614L6.295 8.235a.75.75 0 1 0-1.09 1.03l4.25 4.5a.75.75 0 0 0 1.09 0l4.25-4.5a.75.75 0 0 0-1.09-1.03l-2.955 3.129V2.75Z" />
                <path d="M3.5 12.75a.75.75 0 0 0-1.5 0v2.5A2.75 2.75 0 0 0 4.75 18h10.5A2.75 2.75 0 0 0 18 15.25v-2.5a.75.75 0 0 0-1.5 0v2.5c0 .69-.56 1.25-1.25 1.25H4.75c-.69 0-1.25-.56-1.25-1.25v-2.5Z" />
            </svg>
            Generar Comprobante
        </button>
    </div>
</div>
