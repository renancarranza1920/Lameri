@php
    // Ahora $getState() funcionará porque el form ya no tiene dependencias circulares
    $examenes = $getState();
@endphp

<div class="space-y-8">
    @forelse ($examenes as $detalleId => $examenData)
        <x-filament::card>
            <h2 class="text-xl font-bold tracking-tight text-gray-900 dark:text-white mb-4">
                {{ $examenData['examen_nombre'] }}
            </h2>

            {{-- SECCIÓN PARA PRUEBAS UNITARIAS --}}
            @if (!empty($examenData['pruebas_unitarias']))
                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left rtl:text-right text-gray-500 dark:text-gray-400">
                        <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                            <tr>
                                <th scope="col" class="px-3 py-2">Prueba</th>
                                <th scope="col" class="px-3 py-2">Resultado</th>
                                <th scope="col" class="px-3 py-2">Valor de Referencia</th>
                                <th scope="col" class="px-3 py-2">Unidades</th>
                                <th scope="col" class="px-3 py-2"><span class="sr-only">Acciones</span></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($examenData['pruebas_unitarias'] as $index => $prueba)
                                <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700">
                                    <th scope="row" class="px-3 py-2 font-medium text-gray-900 whitespace-nowrap dark:text-white">
                                        {{ $prueba['prueba_nombre'] ?? 'Nombre no encontrado' }}
                                    </th>
                                    <td class="px-3 py-2">
                                        <x-filament::input.wrapper>
                                            <x-filament::input
                                                type="text"
                                                wire:model.defer="data.resultados_examenes.{{ $detalleId }}.pruebas_unitarias.{{ $index }}.resultado"
                                            />
                                        </x-filament::input.wrapper>
                                    </td>
                                    <td class="px-3 py-2">{{ $prueba['valor_referencia'] }}</td>
                                    <td class="px-3 py-2">{{ $prueba['unidades'] }}</td>
                                    <td class="px-3 py-2 text-right">
                                        @if($prueba['resultado_id'])
                                            <button type="button" wire:click="deleteResultado({{ $prueba['resultado_id'] }})" class="text-danger-500 hover:text-danger-700">
                                                <x-heroicon-o-trash class="w-5 h-5"/>
                                            </button>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif

            {{-- SECCIÓN PARA MATRICES --}}
            @if (!empty($examenData['matrices']))
                <div class="space-y-6 mt-6">
                    @foreach ($examenData['matrices'] as $tipoConjunto => $matriz)
                        <div>
                            <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-200 mb-2">
                                Matriz de Resultados
                            </h3>
                            <div class="overflow-x-auto border rounded-lg dark:border-gray-700">
                                <table class="w-full text-sm text-left rtl:text-right text-gray-500 dark:text-gray-400">
                                    <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                                        <tr>
                                            <th scope="col" class="px-4 py-2"></th> <!-- Esquina vacía -->
                                            @foreach ($matriz['columnas'] as $columna)
                                                <th scope="col" class="px-4 py-2 text-center">{{ $columna }}</th>
                                            @endforeach
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($matriz['filas'] as $fila)
                                            <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700">
                                                <th scope="row" class="px-4 py-2 font-medium text-gray-900 whitespace-nowrap dark:text-white">
                                                    {{ $fila }}
                                                </th>
                                                @foreach ($matriz['columnas'] as $columna)
                                                    <td class="px-4 py-2 text-center border-l dark:border-gray-700">
                                                        @php
                                                            $celda = $matriz['data'][$fila][$columna] ?? null;
                                                        @endphp
                                                        @if ($celda)
                                                            <x-filament::input.wrapper class="mb-1">
                                                                <x-filament::input
                                                                    type="text"
                                                                    wire:model.defer="data.resultados_examenes.{{ $detalleId }}.matrices.{{ $tipoConjunto }}.data.{{ $fila }}.{{ $columna }}.resultado"
                                                                />
                                                            </x-filament::input.wrapper>
                                                            <div class="text-xs text-gray-500 dark:text-gray-400">
                                                                <span>{{ $celda['valor_referencia'] }}</span>
                                                                <span>{{ $celda['unidades'] }}</span>
                                                            </div>
                                                        @endif
                                                    </td>
                                                @endforeach
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif

        </x-filament::card>
    @empty
        <x-filament::card>
            <div class="flex items-center justify-center h-48">
                <p class="text-lg text-gray-500">No hay exámenes en esta orden para ingresar resultados.</p>
            </div>
        </x-filament::card>
    @endforelse
</div>

