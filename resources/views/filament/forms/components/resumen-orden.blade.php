@php
    $subtotal = 0;
    $total = 0;
@endphp

<div>

    <!-- ENCABEZADO -->
    <div class="p-6 bg-white dark:bg-gray-800 rounded-lg border dark:border-gray-700">

        <div class="flex justify-between items-center border-b pb-4 mb-6 dark:border-gray-700">
            <div class="text-left">
                <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Resumen de Orden</h2>
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    Fecha de Emisión: {{ now()->translatedFormat('d \d\e F \d\e Y') }}
                </p>
            </div>
        </div>

        <!-- CLIENTE -->
        @if ($cliente)
       
            <div class="mb-6">
                <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-400 mb-2">Cliente</h3>
                <div class="text-sm text-gray-600 dark:text-gray-400">
                    <p><strong>Expediente:</strong> {{ $cliente->NumeroExp }}</p>
                    <p><strong>Nombre:</strong> {{ $cliente->nombre }} {{ $cliente->apellido }}</p>
                </div>
            </div>
        @endif

        <!-- TABLA DE EXÁMENES -->
        <table class="w-full text-left">
            <thead>
                <tr>
                    <th class="pb-2 border-b text-sm font-semibold text-gray-600 dark:text-gray-400">
                        Descripción
                    </th>
                    <th class="pb-2 border-b text-sm font-semibold text-gray-600 dark:text-gray-400 text-right">
                        Precio
                    </th>
                </tr>
            </thead>

            <tbody class="divide-y dark:divide-gray-700">

                {{-- PERFILES --}}
                @if (!empty($perfilesSeleccionados))
                    @foreach ($perfilesSeleccionados as $item)
                        @php
                            // --- AQUÍ ESTÁ EL CAMBIO PARA LO VISUAL ---
                            // Filtramos la relación 'examenes' para traer solo los activos (estado = 1)
                            $perfil = \App\Models\Perfil::with(['examenes' => function ($query) {
                                $query->where('estado', 1);
                            }])->find($item['perfil_id']);

                            if (!$perfil) continue;

                            $precioPerfil = floatval($item['precio_hidden'] ?? $perfil->precio);
                            $subtotal += $precioPerfil;
                        @endphp

                        <tr class="font-bold">
                            <td class="py-3 pr-4">{{ $perfil->nombre }}</td>
                            <td class="py-3 text-right font-mono">
                                ${{ number_format($precioPerfil, 2) }}
                            </td>
                        </tr>

                        {{-- Ahora este loop solo mostrará los exámenes activos --}}
                        @foreach ($perfil->examenes as $examen)
                            <tr class="text-sm text-gray-600 dark:text-gray-600">
                                <td class="pt-0 pb-2 pl-6 pr-4">- {{ $examen->nombre }}</td>
                                <td></td>
                            </tr>
                        @endforeach

                    @endforeach
                @endif

                {{-- EXÁMENES SUELTOS --}}
                @if (!empty($examenesSeleccionados))
                    @foreach ($examenesSeleccionados as $item)
                        @php
                            $examen = \App\Models\Examen::find($item['examen_id']);
                            if (!$examen) continue;

                            $precioExamen = floatval($item['precio_hidden'] ?? $examen->precio);
                            $subtotal += $precioExamen;
                        @endphp

                        <tr>
                            <td class="py-3 pr-4">{{ $examen->nombre }}</td>
                            <td class="py-3 text-right font-mono">
                                ${{ number_format($precioExamen, 2) }}
                            </td>
                        </tr>

                    @endforeach
                @endif

            </tbody>
        </table>

        @php
            // VARIABLES CON SEGURIDAD
            $codigoAplicado = $codigoAplicado ?? null;
            $descuento = $descuento ?? 0;

            $totalFinal = $subtotal - $descuento;
            if ($totalFinal < 0) $totalFinal = 0;
        @endphp


        <!-- TOTALES -->
        @if ($subtotal > 0)
            <div class="mt-6 pt-4 border-t-2 border-gray-300 dark:border-gray-400">

                <!-- SUBTOTAL -->
                <div class="flex justify-between text-base mb-2">
                    <span class="text-gray-700 dark:text-gray-400">Subtotal:</span>
                    <span class="font-mono">${{ number_format($subtotal, 2) }}</span>
                </div>

                <!-- DESCUENTO -->
                @if ($codigoAplicado)
                    <div class="flex justify-between text-base mb-2">
                        <span class="text-gray-700 dark:text-gray-400">
                            Descuento ({{ $codigoAplicado['codigo'] }}):
                        </span>
                        <span class="font-mono text-red-600">
                            - ${{ number_format($descuento, 2) }}
                        </span>
                    </div>
                @endif

                <!-- TOTAL FINAL -->
                <div class="flex justify-between items-center text-xl font-bold mt-4">
                    <span class="text-gray-900 dark:text-white">Total a Pagar:</span>
                    <span class="font-mono text-success-600 dark:text-success-500">
                        ${{ number_format($totalFinal, 2) }}
                    </span>
                </div>
            </div>
        @endif


        <!-- NOTA -->
        <div class="mt-8 p-3 text-center bg-gray-50 dark:bg-gray-900/50 rounded-lg">
            <p class="text-xs text-gray-500 dark:text-gray-600">
                <svg xmlns="http://www.w3.org/2000/svg" class="inline-block w-4 h-4 mr-1"
                     viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd"
                        d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z"
                        clip-rule="evenodd" />
                </svg>
                Este es un comprobante preliminar. La orden aún no ha sido confirmada.
            </p>
        </div>

    </div>


    <!-- BOTÓN DE PDF -->
    <div class="mt-6 flex justify-end">
        <button
            type="button"
            wire:click="generatePdfPreview"
            wire:loading.attr="disabled"
            class="fi-btn fi-btn-size-md fi-btn-color-primary fi-btn-icon-position-before fi-btn-style-filled rounded-lg shadow-sm"
        >
            <!-- Loading icon -->
            <svg wire:loading wire:target="generatePdfPreview"
                 class="animate-spin w-5 h-5 mr-2"
                 xmlns="http://www.w3.org/2000/svg" fill="none"
                 viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10"
                        stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor"
                      d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>

            <span wire:loading.remove wire:target="generatePdfPreview">
                Generar Comprobante
            </span>
        </button>
    </div>

</div>
