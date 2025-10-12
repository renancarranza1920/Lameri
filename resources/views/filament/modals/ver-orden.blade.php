@php
    // Agrupamos la lógica al inicio para mantener el HTML limpio
    $agrupadoPorPerfil = $record->detalleOrden->groupBy('perfil_id');

    // Mapeo de colores para los estados, igual que en la tabla de órdenes
    $statusColor = match($record->estado) {
        'pendiente' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/50 dark:text-yellow-400',
        'en_proceso' => 'bg-blue-100 text-blue-800 dark:bg-blue-900/50 dark:text-blue-400',
        'pausada' => 'bg-red-100 text-red-800 dark:bg-red-900/50 dark:text-red-400',
        'finalizado' => 'bg-green-100 text-green-800 dark:bg-green-900/50 dark:text-green-400',
        'cancelado' => 'bg-gray-100 text-gray-800 dark:bg-gray-900/50 dark:text-gray-400',
        default => 'bg-gray-100 text-gray-800 dark:bg-gray-900/50 dark:text-gray-400',
    };
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
            
            {{-- 👇 ***** ¡AQUÍ ESTÁ LA MODIFICACIÓN! ***** 👇 --}}
            <div class="flex items-center space-x-2">
                <strong>Estado:</strong>
                <span class="px-2 py-1 text-xs font-medium rounded-full {{ $statusColor }}">
                    {{ ucfirst(str_replace('_', ' ', $record->estado)) }}
                </span>
            </div>

            {{-- El bloque para el motivo de la pausa se mantiene igual --}}
            @if ($record->estado === 'pausada' && $record->motivo_pausa)
                <div class="mt-2 p-2 bg-yellow-50 dark:bg-yellow-900/20 border-l-4 border-yellow-400 dark:border-yellow-500 rounded-r-lg">
                    <p class="font-semibold text-yellow-800 dark:text-yellow-300">Motivo de la Pausa:</p>
                    <p class="text-yellow-700 dark:text-yellow-400">{{ $record->motivo_pausa }}</p>
                </div>
            @endif
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
                                        @if($detalle->status)
                                            <span class="text-xs text-gray-500">({{ $detalle->status }})</span>
                                        @endif
                                    </td>
                                    <td></td>
                                </tr>
                            @endforeach
                        @endif
                    @endforeach

                    {{-- Exámenes Individuales --}}
                    @if ($agrupadoPorPerfil->has(null) || $agrupadoPorPerfil->has(''))
                        @foreach ($agrupadoPorPerfil[null] ?? [] as $detalle)
                             <tr>
                                <td class="p-3">
                                    {{ $detalle->nombre_examen }}
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
    
    {{-- --- ¡NUEVA SECCIÓN DE ESTADO DE PRUEBAS! --- --}}
    <hr class="my-4 dark:border-gray-700">
    <div>
        <h3 class="text-base font-semibold text-gray-800 dark:text-gray-200 mb-2">Estado de las Pruebas</h3>
        <div class="space-y-4">
            {{-- Iteramos sobre los detalles (exámenes) de la orden --}}
            @foreach($record->detalleOrden->whereNotNull('examen_id') as $detalle)
                @if($detalle->examen && $detalle->examen->pruebas->isNotEmpty())
                    <div class="p-3 border rounded-lg dark:border-gray-700">
                        <p class="font-semibold text-gray-800 dark:text-gray-200">{{ $detalle->examen->nombre }}</p>
                        <ul class="mt-2 space-y-1 pl-4">
                            {{-- Iteramos sobre las pruebas de cada examen --}}
                            @foreach($detalle->examen->pruebas as $prueba)
                                <li class="flex items-center text-sm text-gray-600 dark:text-gray-400">
                                    @php
                                        // Verificamos si existe un resultado para esta prueba en esta línea de detalle
                                        $resultadoExiste = $record->resultados
                                            ->where('detalle_orden_id', $detalle->id)
                                            ->where('prueba_id', $prueba->id)
                                            ->first();
                                    @endphp

                                    @if($resultadoExiste && $resultadoExiste->resultado)
                                        {{-- Check verde si el resultado ya fue ingresado y no está vacío --}}
                                        <x-heroicon-s-check-circle class="h-5 w-5 text-green-500 mr-2 flex-shrink-0"/>
                                        <span>{{ $prueba->nombre }}</span>
                                    @else
                                        {{-- Reloj gris si está pendiente --}}
                                        <x-heroicon-o-clock class="h-5 w-5 text-gray-400 mr-2 flex-shrink-0"/>
                                        <span>{{ $prueba->nombre }}</span>
                                    @endif
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endif
            @endforeach
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

