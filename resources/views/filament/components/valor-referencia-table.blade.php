{{-- resources/views/filament/components/valor-referencia-table.blade.php --}}

@props(['valores'])

<div class="rounded-lg border border-gray-200 dark:border-gray-700 overflow-hidden">
    @if($valores && $valores->count() > 0)
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead>
                    <tr class="bg-gray-50 dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700">
                        <th class="px-4 py-3 font-medium text-gray-900 dark:text-white">Grupo Etario / Género</th>
                        <th class="px-4 py-3 font-medium text-gray-900 dark:text-white">Descriptivo</th>
                        <th class="px-4 py-3 font-medium text-gray-900 dark:text-white">Valor de Referencia</th>
                        <th class="px-4 py-3 font-medium text-gray-900 dark:text-white">Unidades</th>
                        <th class="px-4 py-3 font-medium text-gray-900 dark:text-white">Nota</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700 bg-white dark:bg-gray-900">
                    @foreach($valores as $valor)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors">
                            <td class="px-4 py-3 align-top text-gray-700 dark:text-gray-300">
                                <div class="font-medium">{{ $valor->grupoEtario?->nombre ?? 'N/A' }}</div>
                                <div class="text-xs text-gray-500 dark:text-gray-400">{{ $valor->genero }}</div>
                            </td>
                            <td class="px-4 py-3 align-top text-gray-700 dark:text-gray-300">
                                {{ $valor->descriptivo ?? 'N/A' }}
                            </td>
                            <td class="px-4 py-3 align-top font-mono text-gray-700 dark:text-gray-300">
                                @php
                                    $valorMin = rtrim(rtrim(number_format($valor->valor_min, 2, '.', ''), '0'), '.');
                                    $valorMax = rtrim(rtrim(number_format($valor->valor_max, 2, '.', ''), '0'), '.');
                                @endphp

                                @if($valor->operador == 'rango')
                                    {{ $valorMin }} - {{ $valorMax }}
                                @elseif($valor->operador == '<=')
                                    &le; {{ $valorMax }}
                                @elseif($valor->operador == '>=')
                                    &ge; {{ $valorMin }}
                                @elseif($valor->operador == '<')
                                    &lt; {{ $valorMax }}
                                @elseif($valor->operador == '>')
                                    &gt; {{ $valorMin }}
                                @elseif($valor->operador == '=')
                                    = {{ $valorMin }}
                                @endif
                            </td>
                            <td class="px-4 py-3 align-top text-gray-700 dark:text-gray-300">
                                {{ $valor->unidades }}
                            </td>
                            <td class="px-4 py-3 align-top text-sm text-gray-500 dark:text-gray-400 italic">
                                {{ $valor->nota }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @else
        <div class="p-4 text-center text-gray-500 dark:text-gray-400">
            No hay valores de referencia registrados para este reactivo.
        </div>
    @endif
</div>