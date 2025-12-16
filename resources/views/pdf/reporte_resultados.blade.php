<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Reporte de Resultados</title>
    <style>
        @page {
            margin: 40px 50px;
        }

        body {
            font-family: 'Helvetica', sans-serif;
            font-size: 10px;
            color: #333;
        }

        .header {
            display: table;
            width: 100%;
            border-bottom: 2px solid #333;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }

        .header-left {
            display: table-cell;
            vertical-align: top;
            width: 45%;
        }

        .header-left img {
            max-width: 150px;
            margin-bottom: 10px;
        }

        .sello {
            max-width: 140px;
            opacity: 0.95;
            margin-right: 140px;
        }

        .header-left .lab-name {
            font-size: 14px;
            font-weight: bold;
            color: #1E73BE;
            margin: 0;
        }

        .header-left .lab-address {
            font-size: 9px;
            margin: 0;
        }

        .header-right {
            display: table-cell;
            vertical-align: top;
            width: 55%;
        }

        .patient-info {
            border: 1px solid #000;
            padding: 8px;
            font-size: 10px;
        }

        .patient-info table {
            width: 100%;
        }

        .patient-info td {
            padding: 2px;
        }

        .tipo-examen-header {
            font-size: 14px;
            font-weight: bold;
            color: #1E73BE;
            padding: 10px 0;
            margin-top: 15px;
            border-bottom: 1px solid #1E73BE;
        }

        .results-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
            page-break-inside: auto;
        }

        .results-table thead th {
            font-weight: bold;
            text-transform: uppercase;
            border-bottom: 2px solid #000;
            padding: 6px;
            text-align: left;
        }

        .examen-title-row td {
            font-weight: bold;
            font-size: 11px;
            background-color: #f2f2f2;
            border-bottom: 1px solid #ccc;
            padding: 8px 6px;
        }

        /* --- ESTILO PARA SUBGRUPOS (TIPO PRUEBA) --- */
        .group-title-row td {
            font-weight: bold;
            font-size: 10px;
            background-color: #fafafa;
            color: #444;
            padding: 5px 6px;
            border-bottom: 1px solid #eee;
            font-style: italic;
        }

        .result-row {
            border-bottom: 1px solid #eee;
            page-break-inside: avoid;
        }

        .results-table td {
            padding: 6px;
            text-align: left;
            vertical-align: top;
        }

        .result-prueba-name {
            text-transform: uppercase;
        }

        .result-value {
            font-weight: bold;
            padding-left: 0;
        }

        .matrix-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
            page-break-inside: auto;
        }

        .matrix-table th,
        .matrix-table td {
            border: 1px solid #ccc;
            text-align: center;
            padding: 5px;
            vertical-align: middle;
        }

        .matrix-table thead th {
            font-weight: bold;
            text-transform: uppercase;
            background-color: #f2f2f2;
            border-bottom: 2px solid #000;
        }

        .matrix-table tbody th {
            font-weight: bold;
            background-color: #f2f2f2;
        }

        .matrix-table td {
            font-size: 11px;
            font-weight: bold;
        }

        .matrix-table thead th:first-child {
            background-color: transparent;
            border: none;
        }

        footer {
            position: fixed;
            bottom: -20px;
            left: 0px;
            right: 0px;
            height: 40px;
            text-align: center;
            font-size: 9px;
            color: #888;
            border-top: 1px solid #ccc;
            padding-top: 5px;
        }

        footer .page-number:before {
            content: "Página " counter(page);
        }

        .observaciones-box {
            background-color: #f9f9f9;
            border: 1px solid #eee;
            padding: 10px;
            font-size: 9px;
            margin-top: 15px;
            margin-bottom: 20px;
            page-break-inside: avoid;
        }
        .observaciones-box strong {
            display: block;
            margin-bottom: 5px;
            color: #333;
        }

        .fuera-de-rango {
            color: #D90000;
            font-weight: bold;
        }

        .header {
            display: table;
            width: 100%;
            border-bottom: 3px solid #1E73BE;
            padding-bottom: 8px;
            margin-bottom: 20px;
        }

        .header-left {
            display: table-cell;
            vertical-align: top;
            width: 55%;
        }

        .header-right {
            display: table-cell;
            vertical-align: top;
            width: 45%;
        }
    </style>
</head>

<body>
    <footer>
        <div>{{ $orden->cliente->nombre ?? 'Nombre Laboratorio' }} - Reporte de Resultados</div>
        <div class="page-number"></div>
    </footer>

    <div class="header">
        <div class="header-left" style="width: 70%; vertical-align: top;">
            <div style="font-weight: bold; font-size: 13px; color: #003366; margin-top: 3px;">
                LABORATORIO CLÍNICO MERINO
            </div>
            <p style="font-size: 9px; margin: 2px 0;">
                <span style="color:#444;">4ª CALLE ORIENTE #6, B° SAN FRANCISCO, SAN VICENTE.</span>
            </p>
            <p style="font-size: 9px; margin: 0; color: #333; line-height: 1.5;">
                <span>
                   
                </span>
                <a> </a>
                <span style="font-weight: bold;">2606-6596</span>
                <span style="margin-left: 8px; margin-right: 8px; color: #888;">|</span>
                <span>
                   
                </span>
                 <a> </a>
                <span style="font-weight: bold;">WhatsApp: 7595-4210</span>
            </p>
        </div>
   <div class="header-right" style="width: 30%; text-align: right;">
    @if(isset($logo_b64) && $logo_b64)
        <img src="{{ $logo_b64 }}" alt="Logo" style="max-width: 90px; vertical-align: middle;">
    @else
        <span style="color:red; font-weight:bold;">ERROR: IMAGEN NO ENCONTRADA</span>
    @endif
</div>
    </div>

    <div style="margin-top: 5px; padding: 5px; font-size: 9px;">
        <table style="width: 100%; border-collapse: collapse;">
            <tr>
                <td style="width: 35%; font-weight: bold; color: #1E73BE; padding: 2px;">Nombre del Paciente:</td>
                <td style="width: 65%; border-bottom: 1px solid #ccc; padding: 2px;">{{ $orden->cliente->nombre }} {{ $orden->cliente->apellido }}</td>
            </tr>
            <tr>
                <td style="font-weight: bold; color: #1E73BE; padding: 2px;">Edad:</td>
                <td style="border-bottom: 1px solid #ccc; padding: 2px;">{{ \Carbon\Carbon::parse($orden->cliente->fecha_nacimiento)->age }} años</td>
            </tr>
            <tr>
                <td style="font-weight: bold; color: #1E73BE; padding: 2px;">Género:</td>
                <td style="border-bottom: 1px solid #ccc; padding: 2px;">{{ $orden->cliente->genero ?? 'No especificado' }}</td>
            </tr>
            <tr>
                <td style="font-weight: bold; color: #1E73BE; padding: 2px;">Número de Orden:</td>
                <td style="border-bottom: 1px solid #ccc; padding: 2px;">{{ $orden->id }}</td>
            </tr>
            <tr>
                <td style="font-weight: bold; color: #1E73BE; padding: 2px;">Fecha de Reporte:</td>
                <td style="border-bottom: 1px solid #ccc; padding: 2px;">{{ now()->format('d/m/Y') }}</td>
            </tr>
        </table>

        @if (!empty($orden->observaciones))
            <div class="observaciones-box" style="margin-top: 10px;">
                <strong style="color: #1E73BE;">Observaciones:</strong>
                <p style="margin: 0; padding: 0; color: #333;">{!! nl2br(e($orden->observaciones)) !!}</p>
            </div>
        @endif
    </div>

    @foreach($datos_agrupados as $tipoExamenNombre => $examenes)
        <div class="tipo-examen-header">{{ $tipoExamenNombre }}</div>

        @foreach($examenes as $examen)
            @php
                // 1. LÓGICA INTELIGENTE: ¿Mostrar Columna Referencia?
                // Escaneamos todas las pruebas de este examen.
                $tieneReferencias = false;
                if (!empty($examen['pruebas_unitarias'])) {
                    foreach($examen['pruebas_unitarias'] as $p) {
                        // Si encontramos AL MENOS UNA referencia válida o una unidad, activamos la columna para todo el examen.
                        // Importante: Filtramos 'N/A' porque tu backend lo pone por defecto si no hay dato.
                        $refLimpia = trim(strip_tags($p['referencia'] ?? ''));
                        $uniLimpia = trim($p['unidades'] ?? '');
                        
                        if ( ($refLimpia !== '' && $refLimpia !== 'N/A') || $uniLimpia !== '') {
                            $tieneReferencias = true;
                            break; // Ya encontramos una, no necesitamos seguir buscando
                        }
                    }
                }

                // 2. AGRUPACIÓN POR TIPO DE PRUEBA
                $pruebasCollection = collect($examen['pruebas_unitarias']);
                
                $agrupadas = $pruebasCollection->groupBy(function($item) {
                    return $item['tipo_prueba'] ?? ''; 
                });

                $sinGrupo = $agrupadas->pull('') ?? collect();
                $conGrupo = $agrupadas->sortKeys();
            @endphp

            <table class="results-table">
                <thead>
                    @if (!empty($examen['pruebas_unitarias']))
                        <tr>
                            <!-- Ajuste dinámico de anchos -->
                            <th style="width: {{ $tieneReferencias ? '40%' : '60%' }};">PRUEBA</th>
                            <th style="width: {{ $tieneReferencias ? '25%' : '40%' }};">RESULTADO</th>
                            
                            @if($tieneReferencias)
                                <th style="width: 35%;">RANGO DE REFERENCIA</th>
                            @endif
                        </tr>
                    @endif
                </thead>
                <tbody>
                    <tr class="examen-title-row">
                        <td colspan="{{ $tieneReferencias ? 3 : 2 }}">EXAMEN: {{ $examen['nombre'] }}</td>
                    </tr>

                    {{-- A. PRUEBAS SIN GRUPO (Primero) --}}
                    @foreach($sinGrupo as $pruebaData)
                        <tr class="result-row">
                            <td>
                                <div class="result-prueba-name">{{ $pruebaData['nombre'] }}</div>
                            </td>
                            <td>
                                <div class="result-value @if($pruebaData['es_fuera_de_rango']) fuera-de-rango @endif">
                                    {!! $pruebaData['resultado'] !!}
                                </div>
                            </td>
                            @if($tieneReferencias)
                                <td>{!! $pruebaData['referencia'] !!} {{ $pruebaData['unidades'] }}</td>
                            @endif
                        </tr>
                    @endforeach

                    {{-- B. GRUPOS (Tipo A, Tipo B, etc.) --}}
                    @foreach($conGrupo as $nombreGrupo => $items)
                        <!-- Fila separadora del grupo -->
                        <tr class="group-title-row">
                            <td colspan="{{ $tieneReferencias ? 3 : 2 }}">{{ $nombreGrupo }}</td>
                        </tr>
                        
                        @foreach($items as $pruebaData)
                            <tr class="result-row">
                                <td>
                                    <div class="result-prueba-name">{{ $pruebaData['nombre'] }}</div>
                                </td>
                                <td>
                                    <div class="result-value @if($pruebaData['es_fuera_de_rango']) fuera-de-rango @endif">
                                        {!! $pruebaData['resultado'] !!}
                                    </div>
                                </td>
                                @if($tieneReferencias)
                                    <td>{!! $pruebaData['referencia'] !!} {{ $pruebaData['unidades'] }}</td>
                                @endif
                            </tr>
                        @endforeach
                    @endforeach

                </tbody>
            </table>

            @if (!empty($examen['matrices']))
                @foreach ($examen['matrices'] as $matriz)
                    <table class="matrix-table">
                        <thead>
                            <tr>
                                <th></th>
                                @foreach ($matriz['columnas'] as $columna)
                                    <th>{{ $columna }}</th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($matriz['filas'] as $fila)
                                <tr>
                                    <th>{{ $fila }}</th>
                                    @foreach ($matriz['columnas'] as $columna)
                                        @php
                                            $celda = $matriz['data'][$fila][$columna] ?? null;
                                        @endphp

                                        <td class="@if($celda && $celda['es_fuera_de_rango']) fuera-de-rango @endif">
                                            {{ $celda['resultado'] ?? 'N/A' }}
                                        </td>
                                    @endforeach
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endforeach
            @endif
            <div style="height: 15px;"></div>
        @endforeach
    @endforeach

    <div style="margin-top: 40px; text-align: right; width: 100%;">
        <div style="display: inline-block;">
            
            {{-- BLOQUE 1: SELLO DEL REGISTRO (Izquierda) --}}
            <div style="display: inline-block; margin-left: 20px; vertical-align: top;">
                @php $size = '130px'; @endphp
                @if ($sello_registro_b64)
                    <img src="{{ $sello_registro_b64 }}" alt="Sello Registro" style="width: {{ $size }}; height: auto;">
                @else
                    {{-- Espacio vacío o mensaje si no hay sello --}}
                    <div style="width: {{ $size }}; height: 80px; border: 1px dashed #ccc; display:flex; align-items:center; justify-content:center; font-size:9px;">
                        [Sello Reg.]
                    </div>
                @endif
            </div>

            {{-- BLOQUE 2: FIRMA Y SELLO DEL LICENCIADO (Derecha) --}}
            <div style="display: inline-block; margin-left: 20px; vertical-align: top;">
                <div style="position: relative; width: {{ $size }}; height: 80px; margin-top: 0;">
                    
                    {{-- Capa 1: Sello del Usuario (Fondo) --}}
                    @if ($sello_usuario_b64)
                        <img src="{{ $sello_usuario_b64 }}" alt="Sello Usuario" style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; object-fit: contain;">
                    @endif

                    {{-- Capa 2: Firma del Usuario (Superpuesta) --}}
                    @if ($firma_usuario_b64)
                        {{-- z-index: 10 asegura que la firma quede encima del sello --}}
                        <img src="{{ $firma_usuario_b64 }}" alt="Firma" style="position: absolute; top: 8%; left: 50%; transform: translate(-50%, -50%); max-width: 90%; height: auto; object-fit: contain; z-index: 10;">
                    @endif

                    {{-- Capa 3: Placeholder si no hay nada --}}
                    @if (!$sello_usuario_b64 && !$firma_usuario_b64)
                        <div style="width: 100%; height: 100%; border: 1px dashed #ccc; display:flex; align-items:center; justify-content:center; font-size:9px;">
                            [Sin Firma]
                        </div>
                    @endif
                    
                </div>
            </div>

        </div>
    </div>
</body>
</html>
</body>
</html>
