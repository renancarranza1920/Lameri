<div class="space-y-4">
    <div>
        <h2 class="text-lg font-bold">Cliente</h2>
        <p><strong>Expediente:</strong> {{ $record->cliente->NumeroExp }}</p>
        <p><strong>Nombre:</strong> {{ $record->cliente->nombre }} {{ $record->cliente->apellido }}</p>
    </div>

    <div>
        <h2 class="text-lg font-bold">Orden</h2>
        <p><strong>Fecha:</strong> {{ \Carbon\Carbon::parse($record->fecha)->format('d/m/Y') }}</p>
        <p><strong>Estado:</strong> {{ ucfirst($record->estado) }}</p>
        <p><strong>Total:</strong> ${{ number_format($record->total, 2) }}</p>
    </div>

    <div>
        <h2 class="text-lg font-bold">Detalle de Exámenes</h2>

        {{-- Exámenes agrupados por perfil --}}
        @php
            $agrupadoPorPerfil = $record->detalleOrden->groupBy('perfil_id');
        @endphp

        @foreach ($agrupadoPorPerfil as $perfilId => $items)
            @if ($perfilId)
                <div class="mt-4">
                    <h3 class="font-semibold">Perfil: {{ $items->first()->nombre_perfil ?? 'Sin nombre' }} ( ${{ number_format($items->first()->precio_perfil, 2) }} )</h3>
                    <ul class="list-disc ml-6">
                        @foreach ($items as $detalle)
                            <li>{{ $detalle->nombre_examen }} - ${{ number_format($detalle->precio_examen, 2) }} ({{ $detalle->recipiente }})</li>
                        @endforeach
                    </ul>
                </div>
            @endif
        @endforeach

        {{-- Exámenes independientes --}}
        @if ($agrupadoPorPerfil->has(null))
            <div class="mt-6">
                <h3 class="font-semibold">Exámenes Individuales</h3>
                <ul class="list-disc ml-6">
                    @foreach ($agrupadoPorPerfil[null] as $detalle)
                        <li>{{ $detalle->nombre_examen }} - ${{ number_format($detalle->precio_examen, 2) }} ({{ $detalle->recipiente }})</li>
                    @endforeach
                </ul>
            </div>
        @endif
    </div>
</div>
