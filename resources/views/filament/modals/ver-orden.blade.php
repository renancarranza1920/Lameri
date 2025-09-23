@php
    // Agrupamos la lógica al inicio para mantener el HTML limpio
    $agrupadoPorPerfil = $record->detalleOrden->groupBy('perfil_id');
@endphp

<div class="space-y-6 text-sm">

    <!-- Sección de Cliente y Orden -->
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
        <div>
            <h3 class="text-base font-semibold text-gray-800 dark:text-gray-200 mb-2">Cliente</h3>
            <p><strong>Expediente:</strong> {{ $record->cliente->NumeroExp }}</p>
            <p><strong>Nombre:</strong> {{ $record->cliente->nombre }} {{ $record->cliente->apellido }}</p>
        </div>
        <div>
            <h3 class="text-base font-semibold text-gray-800 dark:text-gray-200 mb-2">Orden</h3>
            <p><strong>Fecha:</strong> {{ \Carbon\Carbon::parse($record->fecha)->format('d/m/Y') }}</p>
            <p><strong>Estado:</strong> {{ ucfirst($record->estado) }}</p>
        </div>
    </div>

    <!-- Sección de Detalles de la Orden -->
    <div>
        <h3 class="text-base font-semibold text-gray-800 dark:text-gray-200 mb-2">Detalles de la Orden</h3>
        <div class="border rounded-lg overflow-hidden dark:border-gray-700">
            <table class="w-full text-left">
                <thead class="bg-gray-50 dark:bg-gray-700/50">
                    <tr>
                        <th class="p-3 font-semibold">Descripción</th>
                        <th class="p-3 font-semibold text-right">Precio</th>
                    </tr>
                </thead>
                <tbody class="divide-y dark:divide-gray-700">
                    {{-- Perfiles --}}
                    @foreach ($agrupadoPorPerfil as $perfilId => $items)
                        @if ($perfilId)
                            @php $primerItem = $items->first(); @endphp
                            <!-- Fila principal del Perfil -->
                            <tr class="font-bold bg-gray-50 dark:bg-gray-800/50">
                                <td class="p-3">{{ $primerItem->nombre_perfil ?? 'Perfil sin nombre' }}</td>
                                <td class="p-3 text-right font-mono">${{ number_format($primerItem->precio_perfil, 2) }}</td>
                            </tr>
                            <!-- Sub-filas para los exámenes del perfil -->
                            @foreach ($items as $detalle)
                                <tr class="text-gray-600 dark:text-gray-400">
                                    <td class="py-2 pl-6 pr-3">
                                        - {{ $detalle->nombre_examen }}
                                        {{-- ✅ ¡CORRECCIÓN! Solo muestra el recipiente si existe --}}
                                        @if($detalle->status)
                                            <span class="text-xs text-gray-500">({{ $detalle->status }})</span>
                                        @endif
                                    </td>
                                    <td></td> {{-- Columna de precio vacía para los sub-items --}}
                                </tr>
                            @endforeach
                        @endif
                    @endforeach

                    {{-- Exámenes Individuales --}}
                    @if ($agrupadoPorPerfil->has(null) || $agrupadoPorPerfil->has(''))
                        @foreach ($agrupadoPorPerfil[null] ?? [] as $detalle)
                             <tr>
                                <td class="p-3 font-bold">
                                    {{ $detalle->nombre_examen }}
                                    {{-- ✅ ¡CORRECCIÓN! Solo muestra el recipiente si existe --}}
                                    @if($detalle->status)
                                        <span class="text-xs text-gray-500">({{ $detalle->status }})</span>
                                    @endif
                                </td>
                                <td class="p-3 text-right font-mono">${{ number_format($detalle->precio_examen, 2) }}</td>
                            </tr>
                        @endforeach
                    @endif
                </tbody>
            </table>
        </div>
    </div>

    <!-- Sección del Total -->
    <div class="mt-6 pt-4 border-t-2 border-gray-300 dark:border-gray-600">
        <div class="flex justify-between items-center text-xl font-bold">
            <span class="text-gray-900 dark:text-white">Total de la Orden:</span>
            <span class="font-mono text-success-600 dark:text-success-500">${{ number_format($record->total, 2) }}</span>
        </div>
    </div>
</div>
