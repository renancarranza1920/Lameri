{{-- resources/views/filament/modals/ver-orden-pruebas.blade.php --}}

@props(['examenes'])

<div class="space-y-6">
    @if($examenes && $examenes->count() > 0)
        @foreach ($examenes as $examen)
            <div class="space-y-2">
                {{-- Encabezado: Azul corporativo en claro, azul claro legible en oscuro --}}
                <h3 class="text-lg font-bold text-[#1E73BE] dark:text-blue-400">
                    Examen: {{ $examen->nombre }}
                </h3>
                
                @if($examen->pruebas && $examen->pruebas->count() > 0)
                    {{-- Contenedor de la tabla con bordes redondeados --}}
                    {{-- dark:border-gray-700: Borde gris en modo oscuro --}}
                    <div class="rounded-lg border border-gray-200 dark:border-gray-700 overflow-hidden">
                        <table class="w-full text-sm text-left border-collapse">
                            <thead>
                                {{-- Fondo gris muy claro en modo luz, gris oscuro en modo noche --}}
                                <tr class="bg-gray-50 dark:bg-gray-800">
                                    {{-- TH: Borde gris claro en luz, gris oscuro en noche. Texto oscuro en luz, blanco en noche --}}
                                    <th class="p-3 font-medium border border-gray-200 dark:border-gray-700 text-gray-900 dark:text-white">
                                        Prueba a Realizar
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($examen->pruebas as $prueba)
                                    {{-- Fondo blanco en luz, transparente/oscuro en noche --}}
                                    <tr class="bg-white dark:bg-transparent">
                                        {{-- TD: Borde gris claro en luz, gris oscuro en noche. Texto gris en luz, gris claro en noche --}}
                                        <td class="p-3 border border-gray-200 dark:border-gray-700 text-gray-700 dark:text-white">
                                            {{ $prueba->nombre }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="p-4 text-center text-gray-500 border border-gray-200 rounded-lg dark:border-gray-700 dark:text-gray-400">
                        Este examen no tiene pruebas definidas.
                    </div>
                @endif
            </div>
        @endforeach
    @else
        <div class="p-4 text-center text-gray-500 dark:text-gray-400">
            No hay exámenes con pruebas en esta orden.
        </div>
    @endif
</div>