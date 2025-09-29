{{-- resources/views/filament/modals/ver-orden-pruebas.blade.php --}}

@props(['examenes'])

<style>
    .examen-block { margin-bottom: 1.5rem; }
    .examen-header { font-size: 1.125rem; font-weight: bold; color: #1E73BE; margin-bottom: 0.5rem; }
    .pruebas-table { width: 100%; border-collapse: collapse; }
    .pruebas-table th, .pruebas-table td { border: 1px solid #e5e7eb; padding: 0.75rem; text-align: left; font-size: 0.875rem; }
    .pruebas-table th { background-color: #f9fafb; }
    .no-records { padding: 1rem; text-align: center; color: #6b7280; }
</style>

<div class="space-y-4">
    @if($examenes && $examenes->count() > 0)
        @foreach ($examenes as $examen)
            <div class="examen-block">
                <h3 class="examen-header">Examen: {{ $examen->nombre }}</h3>
                
                @if($examen->pruebas && $examen->pruebas->count() > 0)
                    <div class="rounded-lg border border-gray-200 overflow-hidden">
                        <table class="pruebas-table">
                            <thead>
                                <tr>
                                    <th>Prueba a Realizar</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($examen->pruebas as $prueba)
                                    <tr>
                                        <td>{{ $prueba->nombre }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="no-records border rounded-lg">
                        Este examen no tiene pruebas definidas.
                    </div>
                @endif
            </div>
        @endforeach
    @else
        <div class="no-records">
            No hay exámenes con pruebas en esta orden.
        </div>
    @endif
</div>