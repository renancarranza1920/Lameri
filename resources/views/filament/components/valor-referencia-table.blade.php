{{-- resources/views/filament/components/valor-referencia-table.blade.php --}}

@props(['valores'])

<style>
    .custom-table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 1rem;
    }
    .custom-table th, .custom-table td {
        border: 1px solid #e5e7eb; /* Color de borde de Filament */
        padding: 0.75rem;
        text-align: left;
        font-size: 0.875rem;
    }
    .custom-table th {
        background-color: #f9fafb; /* Color de fondo del header */
    }
    .custom-table tr:nth-child(even) {
        background-color: #f9fafb;
    }
    .custom-table td {
        vertical-align: top;
    }
    .no-records {
        padding: 1rem;
        text-align: center;
        color: #6b7280;
    }
</style>

<div class="rounded-lg border border-gray-200">
    @if($valores && $valores->count() > 0)
        <table class="custom-table">
            <thead>
                <tr>
                    <th>Grupo Etario / Género</th>
                    <th>Descriptivo</th>
                    <th>Valor de Referencia</th>
                    <th>Unidades</th>
                    <th>Nota</th>
                </tr>
            </thead>
            <tbody>
                @foreach($valores as $valor)
                    <tr>
                        <td>
                            <div>{{ $valor->grupoEtario?->nombre ?? 'N/A' }}</div>
                            <div class="text-xs text-gray-500">{{ $valor->genero }}</div>
                        </td>
                        <td>{{ $valor->descriptivo ?? 'N/A' }}</td>
                        <td>
                            @php
                                $valorMin = rtrim(rtrim(number_format($valor->valor_min, 2, '.', ''), '0'), '.');
                                $valorMax = rtrim(rtrim(number_format($valor->valor_max, 2, '.', ''), '0'), '.');
                            @endphp

                            @if($valor->operador == 'rango')
                                {{ $valorMin }} - {{ $valorMax }}
                            @elseif($valor->operador == '<=')
                                ≤ {{ $valorMax }}
                            @elseif($valor->operador == '>=')
                                ≥ {{ $valorMin }}
                            @elseif($valor->operador == '<')
                                < {{ $valorMax }}
                            @elseif($valor->operador == '>')
                                > {{ $valorMin }}
                            @elseif($valor->operador == '=')
                                = {{ $valorMin }}
                            @endif
                        </td>
                        <td>{{ $valor->unidades }}</td>
                        <td>{{ $valor->nota }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <div class="no-records">
            No hay valores de referencia registrados para este reactivo.
        </div>
    @endif
</div>