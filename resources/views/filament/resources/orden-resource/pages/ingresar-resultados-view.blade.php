@php
    $examenes = $getState();
@endphp

<div class="space-y-8">
    @forelse ($examenes as $detalleId => $examenData)
        @php
            // Detectamos si es referido desde el array de datos
            $esReferido = $examenData['es_referido'] ?? false;
            
            // Definimos la pestaña activa por defecto según el tipo
            $tabInicial = $esReferido ? 'externo' : 'interno';
        @endphp

        <x-filament::card>
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-xl font-bold tracking-tight text-gray-900 dark:text-white">
                    {{ $examenData['examen_nombre'] }}
                </h2>
                {{-- Badge opcional para indicar visualmente el tipo --}}
                @if($esReferido)
                    <span class="bg-blue-100 text-blue-800 text-xs font-medium px-2.5 py-0.5 rounded dark:bg-blue-900 dark:text-blue-300">Examen Referido</span>
                @endif
            </div>

            {{-- INICIO ALPINE --}}
            <div x-data="{ activeTab: '{{ $tabInicial }}' }">
                
                {{-- 
                    BARRA DE PESTAÑAS (Solo mostramos la correspondiente)
                    Aunque solo hay una opción, dejar el estilo de pestaña ayuda a la consistencia visual.
                --}}
                <div class="flex border-b border-gray-200 dark:border-gray-700 mb-6">
                    @if(!$esReferido)
                        <button 
                            type="button"
                            class="py-2 px-4 text-sm font-medium text-center border-b-2 border-primary-500 text-primary-600 dark:text-primary-400 dark:border-primary-400 focus:outline-none cursor-default"
                        >
                            Resultados Laboratorio
                        </button>
                    @else
                        <button 
                            type="button"
                            class="py-2 px-4 text-sm font-medium text-center border-b-2 border-primary-500 text-primary-600 dark:text-primary-400 dark:border-primary-400 focus:outline-none cursor-default"
                        >
                            Resultados Externos / Manuales
                        </button>
                    @endif
                </div>

                {{-- CONTENIDO INTERNO (Solo visible si NO es referido) --}}
                @if(!$esReferido)
                    <div x-show="activeTab === 'interno'" class="space-y-6">
                        {{-- Pruebas Unitarias --}}
                        @if (!empty($examenData['pruebas_unitarias']))
                            <div class="overflow-x-auto border rounded-lg dark:border-gray-700">
                                <table class="w-full text-sm text-left rtl:text-right text-gray-500 dark:text-gray-400">
                                    <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                                        <tr>
                                            <th scope="col" class="px-3 py-2">Prueba</th>
                                            <th scope="col" class="px-3 py-2">Resultado</th>
                                            <th scope="col" class="px-3 py-2">Valor de Referencia</th>
                                            <th scope="col" class="px-3 py-2">Unid.</th>
                                            <th scope="col" class="px-3 py-2"></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($examenData['pruebas_unitarias'] as $index => $prueba)
                                            <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700">
                                                <th scope="row" class="px-3 py-2 font-medium text-gray-900 whitespace-nowrap dark:text-white">
                                                    {{ $prueba['prueba_nombre'] }}
                                                </th>
                                                <td class="px-3 py-2">
                                                    <x-filament::input.wrapper>
                                                        <x-filament::input type="text" wire:model.defer="data.resultados_examenes.{{ $detalleId }}.pruebas_unitarias.{{ $index }}.resultado"/>
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

                        {{-- Matrices --}}
                        @if (!empty($examenData['matrices']))
                            <div class="space-y-6 mt-6">
                                @foreach ($examenData['matrices'] as $tipoConjunto => $matriz)
                                    <div>
                                        <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Matriz: {{ ucfirst($tipoConjunto) }}</h3>
                                        <div class="overflow-x-auto border rounded-lg dark:border-gray-700">
                                            <table class="w-full text-sm text-left rtl:text-right text-gray-500 dark:text-gray-400">
                                                <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                                                    <tr>
                                                        <th class="px-4 py-2"></th> 
                                                        @foreach ($matriz['columnas'] as $columna)
                                                            <th class="px-4 py-2 text-center">{{ $columna }}</th>
                                                        @endforeach
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach ($matriz['filas'] as $fila)
                                                        <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700">
                                                            <th class="px-4 py-2 font-medium text-gray-900 whitespace-nowrap dark:text-white">{{ $fila }}</th>
                                                            @foreach ($matriz['columnas'] as $columna)
                                                                <td class="px-4 py-2 text-center border-l dark:border-gray-700">
                                                                    @if ($celda = $matriz['data'][$fila][$columna] ?? null)
                                                                        <x-filament::input.wrapper class="mb-1">
                                                                            <x-filament::input type="text" wire:model.defer="data.resultados_examenes.{{ $detalleId }}.matrices.{{ $tipoConjunto }}.data.{{ $fila }}.{{ $columna }}.resultado"/>
                                                                        </x-filament::input.wrapper>
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

                        @if(empty($examenData['pruebas_unitarias']) && empty($examenData['matrices']))
                            <div class="p-4 text-center text-gray-500 bg-gray-50 rounded-lg">No hay pruebas configuradas para este examen.</div>
                        @endif
                    </div>
                @endif

                {{-- CONTENIDO EXTERNO (Solo visible si ES referido) --}}
                @if($esReferido)
                    <div x-show="activeTab === 'externo'" class="space-y-6">
                        <div class="p-4 mb-4 text-sm text-blue-800 rounded-lg bg-blue-50 dark:bg-gray-800 dark:text-blue-400">
                            <span class="font-medium">Examen Referido:</span> Ingresa los resultados manualmente tal como aparecen en el reporte externo.
                        </div>
                        <div class="overflow-x-auto border rounded-lg dark:border-gray-700">
                            <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
                                <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                                    <tr>
                                        <th class="px-3 py-2 w-1/3">Prueba</th>
                                        <th class="px-3 py-2">Resultado</th>
                                        <th class="px-3 py-2">Valor de Referencia</th>
                                        <th class="px-3 py-2">Unid.</th>
                                        <th class="px-3 py-2 text-center"></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($examenData['externos'] ?? [] as $index => $ext)
                                        <tr wire:key="ext-{{ $detalleId }}-{{ $ext['temp_id'] }}" class="bg-white border-b dark:bg-gray-800 dark:border-gray-700">
                                            <td class="px-3 py-2">
                                                <x-filament::input.wrapper>
                                                    <x-filament::input type="text" placeholder="Nombre..." wire:model.defer="data.resultados_examenes.{{ $detalleId }}.externos.{{ $index }}.prueba_nombre"/>
                                                </x-filament::input.wrapper>
                                            </td>
                                            <td class="px-3 py-2">
                                                <x-filament::input.wrapper>
                                                    <x-filament::input type="text" wire:model.defer="data.resultados_examenes.{{ $detalleId }}.externos.{{ $index }}.resultado"/>
                                                </x-filament::input.wrapper>
                                            </td>
                                            <td class="px-3 py-2">
                                                <x-filament::input.wrapper>
                                                    <x-filament::input type="text" placeholder="Ref" wire:model.defer="data.resultados_examenes.{{ $detalleId }}.externos.{{ $index }}.valor_referencia"/>
                                                </x-filament::input.wrapper>
                                            </td>
                                            <td class="px-3 py-2">
                                                <x-filament::input.wrapper>
                                                    <x-filament::input type="text" placeholder="Unid" wire:model.defer="data.resultados_examenes.{{ $detalleId }}.externos.{{ $index }}.unidades"/>
                                                </x-filament::input.wrapper>
                                            </td>
                                            <td class="px-3 py-2 text-center">
                                                <button type="button" wire:click="removeExternalRow({{ $detalleId }}, {{ $index }}, {{ $ext['id'] ?? 'null' }})" class="text-red-500 hover:text-red-700">
                                                    <x-heroicon-o-trash class="w-5 h-5"/>
                                                </button>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="5" class="px-6 py-4 text-center text-gray-500">Sin resultados agregados.</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        <div class="mt-2">
                            <x-filament::button type="button" size="sm" color="gray" icon="heroicon-m-plus" wire:click="addExternalRow({{ $detalleId }})">
                                Agregar Resultado
                            </x-filament::button>
                        </div>
                    </div>
                @endif

            </div> {{-- Fin Alpine --}}
        </x-filament::card>
    @empty
        <x-filament::card>
            <div class="flex items-center justify-center h-24 text-gray-500">
                No hay exámenes para mostrar.
            </div>
        </x-filament::card>
    @endforelse
</div>