@php
    // Agrupamos la lógica al inicio para mantener el HTML limpio
    $agrupadoPorPerfil = $record->detalleOrden->groupBy('perfil_id');

    $orden = $record; // Renombramos para claridad
    $cliente = $orden->cliente;

    // Mapeo de colores para los estados
    $statusColor = match($record->estado) {
        'pendiente' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/50 dark:text-yellow-400',
        'en proceso' => 'bg-blue-100 text-blue-800 dark:bg-blue-900/50 dark:text-blue-400',
        'pausada' => 'bg-red-100 text-red-800 dark:bg-red-900/50 dark:text-red-400',
        'finalizado' => 'bg-green-100 text-green-800 dark:bg-green-900/50 dark:text-green-400',
        'cancelado' => 'bg-gray-100 text-gray-800 dark:bg-gray-900/50 dark:text-gray-400',
        'default' => 'bg-gray-100 text-gray-800 dark:bg-gray-900/50 dark:text-gray-400',
    };
@endphp

<div class="space-y-6 text-sm">

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
        <div>
            <h3 class="text-base font-semibold text-gray-800 dark:text-gray-200 mb-2">Cliente</h3>
            <p><strong>Expediente:</strong> {{ $record->cliente->NumeroExp }}</p>
            <p><strong>Nombre:</strong> {{ $record->cliente->nombre }} {{ $record->cliente->apellido }}</p>
        </div>
        <div>
            <h3 class="text-base font-semibold text-gray-800 dark:text-gray-200 mb-2">Orden</h3>
            <p><strong>Fecha:</strong> {{ \Carbon\Carbon::parse($record->fecha)->format('d/m/Y') }}</p>
            
            <div class="flex items-center space-x-2">
                <strong>Estado:</strong>
                <span class="px-2 py-1 text-xs font-medium rounded-full {{ $statusColor }}">
                    {{ ucfirst(str_replace('_', ' ', $record->estado)) }}
                </span>
            </div>
            
            @if (!empty($orden->observaciones))
                <div class="col-span-2 pt-4 border-t dark:border-gray-600">
                    <dt class="text-gray-500 dark:text-gray-400">Observaciones de la Orden:</dt>
                    <dd class="font-medium text-gray-900 dark:text-white whitespace-pre-wrap">{{ $orden->observaciones }}</dd>
                </div>
            @endif

            @if ($record->estado === 'pausada' && $record->motivo_pausa)
                <div class="mt-2 p-2 bg-yellow-50 dark:bg-yellow-900/20 border-l-4 border-yellow-400 dark:border-yellow-500 rounded-r-lg">
                    <p class="font-semibold text-yellow-800 dark:text-yellow-300">Motivo de la Pausa:</p>
                    <p class="text-yellow-700 dark:text-yellow-400">{{ $record->motivo_pausa }}</p>
                </div>
            @endif
        </div>
    </div>

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
                            <tr class="font-bold bg-gray-50 dark:bg-gray-800/50">
                                <td class="p-3">{{ $primerItem->nombre_perfil ?? 'Perfil sin nombre' }}</td>
                                <td class="p-3 text-right font-mono">${{ number_format($primerItem->precio_perfil, 2) }}</td>
                            </tr>
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
      <section>
        <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-200 mb-2">Gestión de Muestras</h3>
        <div class="p-4 border rounded-lg dark:border-gray-700 text-sm">
            @if($orden->tomaMuestraUser)
                <div class="mb-2">
                    <dt class="text-gray-500 dark:text-gray-400">Muestras recibidas por:</dt>
                    <dd class="font-medium text-gray-900 dark:text-white">{{ $orden->tomaMuestraUser->name }}</dd>
                </div>
                <div>
                    <dt class="text-gray-500 dark:text-gray-400">Fecha de recepción:</dt>
                    <dd class="font-medium text-gray-900 dark:text-white">{{ $orden->fecha_toma_muestra->format('d/m/Y h:i A') }}</dd>
                </div>
            @else
                <p class="text-gray-500 dark:text-gray-400">Las muestras de esta orden aún no han sido recibidas.</p>
            @endif
        </div>
    </section>
    
    <hr class="my-4 dark:border-gray-700">
    <div>
        <h3 class="text-base font-semibold text-gray-800 dark:text-gray-200 mb-2">Estado de las Pruebas</h3>
        <div class="space-y-4">
            @foreach($record->detalleOrden->whereNotNull('examen_id') as $detalle)
                @if($detalle->examen && $detalle->examen->pruebas->isNotEmpty() && !$detalle->examen->es_externo)
                    <div class="p-3 border rounded-lg dark:border-gray-700">
                        <p class="font-semibold text-gray-800 dark:text-gray-200">{{ $detalle->examen->nombre }}</p>
                        <ul class="mt-2 space-y-1 pl-4">
                            @foreach($detalle->examen->pruebas as $prueba)
                                <li class="flex items-center text-sm text-gray-600 dark:text-gray-400">
                                    @php
                                        $resultadoExiste = $record->resultados
                                            ->where('detalle_orden_id', $detalle->id)
                                            ->where('prueba_id', $prueba->id)
                                            ->first();
                                    @endphp

                                    @if($resultadoExiste && $resultadoExiste->resultado)
                                        <x-heroicon-s-check-circle class="h-5 w-5 text-green-500 mr-2 flex-shrink-0"/>
                                        <span>{{ $prueba->nombre }}</span>
                                    @else
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

    <div class="mt-6 pt-4 border-t-2 border-gray-300 dark:border-gray-600">
        <div class="flex flex-col items-end space-y-2">
            
            {{-- Si hay descuento, mostramos el desglose --}}
            @if(isset($record->descuento) && $record->descuento > 0)
                <div class="flex justify-between w-full sm:w-1/2 text-sm text-gray-600 dark:text-gray-400">
                    <span>Subtotal:</span>
                    {{-- Calculamos el subtotal sumando el total final + el descuento --}}
                    <span class="font-mono">${{ number_format($record->total + $record->descuento, 2) }}</span>
                </div>

                <div class="flex justify-between w-full sm:w-1/2 text-sm text-red-600 dark:text-red-400 font-medium">
                    <span>
                        Descuento 
                        @if($record->codigo) 
                            <span class="text-xs text-gray-500">({{ $record->codigo->codigo }})</span> 
                        @endif
                        :
                    </span>
                    <span class="font-mono">- ${{ number_format($record->descuento, 2) }}</span>
                </div>
                
                {{-- Línea separadora pequeña --}}
                <div class="w-full sm:w-1/2 border-t border-gray-200 dark:border-gray-700 my-1"></div>
            @endif

            {{-- Total Final --}}
            <div class="flex justify-between w-full sm:w-1/2 text-xl font-bold">
                <span class="text-gray-900 dark:text-white">Total a Pagar:</span>
                <span class="font-mono text-success-600 dark:text-success-500">${{ number_format($record->total, 2) }}</span>
            </div>

        </div>
    </div>
</div>