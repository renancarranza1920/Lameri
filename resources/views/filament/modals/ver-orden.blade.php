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
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        @if ($record->examenes->isNotEmpty())
            <div>
                <h2 class="text-lg font-bold">Exámenes</h2>
                <ul class="list-disc ml-4">
                    @foreach ($record->examenes as $examen)
                        <li>{{ $examen->nombre }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if ($record->perfiles->isNotEmpty())
            <div>
                <h2 class="text-lg font-bold">Perfiles</h2>
                <ul class="ml-4">
                    @foreach ($record->perfiles as $perfil)
                        <li class="font-semibold">- Perfil {{ $perfil->nombre }}
                            <ul class="list-disc ml-6">
                                @foreach ($perfil->examenes as $examen)
                                    <li>{{ $examen->nombre }}</li>
                                @endforeach
                            </ul>
                        </li>
                    @endforeach
                </ul>
            </div>
        @endif
    </div>
</div>
