<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Reporte de Resultados</title>
    <style>
        @page {
            /* Margen estándar, firma dinámica dentro del flujo */
            margin: 40px 50px 50px 50px;
        }

        body {
            font-family: 'Helvetica', sans-serif;
            font-size: 10px;
            color: #333;
        }

        footer {
            position: fixed;
            bottom: -30px;
            left: 0px;
            right: 0px;
            height: 30px;
            text-align: center;
            font-size: 8px;
            color: #888;
            border-top: 1px solid #eee;
            padding-top: 5px;
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

        .header-left img {
            max-width: 150px;
            margin-bottom: 10px;
        }

        .patient-info {
            border: 1px solid #000;
            padding: 8px;
            font-size: 10px;
            margin-bottom: 15px;
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

        .results-table thead {
            display: table-header-group;
        }

        .results-table tr {
            page-break-inside: avoid; 
            page-break-after: auto;
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

        .result-row {
            border-bottom: 1px solid #eee;
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

        footer .page-number:before {
            content: "Página " counter(page);
        }

        /* --- SECCIONES Y FIRMAS --- */
        .seccion-laboratorista {
            margin-bottom: 20px;
        }

        .firma-container {
            margin-top: 40px;
            page-break-inside: avoid;
            width: 100%;
        }
        
        .firma-table {
            width: 100%;
        }

        .firma-wrapper {
            position: relative;
            width: 140px;
            height: 90px;
            margin: 0 auto;
        }

        .salto-pagina {
            page-break-after: always;
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

        .fuera-de-rango {
            color: #D90000 !important;
            font-weight: bold;
        }
    </style>
</head>

<body>
    @php
        $esFueraDeRango = function($resultado, $referencia) {
            if (empty($resultado) || empty($referencia)) return false;
            $valorStr = preg_replace('/[^0-9\.,]/', '', str_replace(',', '.', $resultado));
            if (!is_numeric($valorStr)) return false;
            $valor = (float) $valorStr;
            preg_match_all('/[0-9]+(\.[0-9]+)?/', str_replace(',', '.', $referencia), $matches);
            $nums = $matches[0];
            if (count($nums) >= 2) {
                $min = (float) $nums[0];
                $max = (float) $nums[1];
                return ($valor < $min || $valor > $max);
            }
            if (count($nums) == 1) {
                $limite = (float) $nums[0];
                if (str_contains($referencia, '<')) return $valor >= $limite;
                if (str_contains($referencia, '>')) return $valor <= $limite;
            }
            return false;
        };
    @endphp

    <footer>
        <div style="margin-top: 5px;">
            <div style="float: left;">{{ $orden->cliente->nombre ?? 'Laboratorio' }} - Orden #{{ $orden->id }}</div>
            <div style="float: right;" class="page-number"></div>
            <div style="clear: both;"></div>
        </div>
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
                <span style="font-weight: bold;">2606-6596</span>
                <span style="margin-left: 8px; margin-right: 8px; color: #888;">|</span>
                <span style="font-weight: bold;">WhatsApp: 7595-4210</span>
            </p>
        </div>
        <div class="header-right" style="width: 30%; text-align: right;">
            @if(isset($logo_b64) && $logo_b64)
                <img src="{{ $logo_b64 }}" alt="Logo" style="max-width: 90px; vertical-align: middle;">
            @else
                <span style="color:red; font-weight:bold;">ERROR IMG</span>
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
                <td style="font-weight: bold; color: #1E73BE; padding: 2px;">Fecha de Reporte:</td>
                <td style="border-bottom: 1px solid #ccc; padding: 2px;">{{ now()->format('d/m/Y') }}</td>
            </tr>
        </table>

        @if (!empty($orden->observaciones))
            <div class="observaciones-box">
                <strong style="color: #1E73BE;">Observaciones Generales:</strong>
                <p style="margin: 0; padding: 0; color: #333;">{!! nl2br(e($orden->observaciones)) !!}</p>
            </div>
        @endif
    </div>

    {{-- BUCLE PRINCIPAL POR LABORATORISTA --}}
    @foreach($grupos_por_usuario as $grupo)
        
        <div class="seccion-laboratorista">
            
            @foreach($grupo['datos'] as $tipoExamenNombre => $examenes)
                <div class="tipo-examen-header">{{ $tipoExamenNombre }}</div>

                @foreach($examenes as $examen)
                    @php
                        $tieneReferencias = false;
                        if (!empty($examen['pruebas_unitarias'])) {
                            foreach($examen['pruebas_unitarias'] as $p) {
                                $ref = trim(strip_tags($p['referencia'] ?? ''));
                                $uni = trim($p['unidades'] ?? '');
                                if (($ref !== '' && $ref !== 'N/A') || $uni !== '') {
                                    $tieneReferencias = true; break;
                                }
                            }
                        }
                    @endphp

                    <table class="results-table">
                        <thead>
                            @if (!empty($examen['pruebas_unitarias']))
                                <tr>
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
                            
                            @foreach($examen['pruebas_unitarias'] as $pruebaData)
                                <tr class="result-row">
                                    <td>
                                        <div class="result-prueba-name">{{ $pruebaData['nombre'] }}</div>
                                    </td>
                                    <td>
                                        <div class="result-value @if($esFueraDeRango($pruebaData['resultado'], $pruebaData['referencia'] ?? '')) fuera-de-rango @endif">
                                            {!! $pruebaData['resultado'] !!}
                                        </div>
                                    </td>
                                    @if($tieneReferencias)
                                        <td>{!! $pruebaData['referencia'] !!} {{ $pruebaData['unidades'] }}</td>
                                    @endif
                                </tr>
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
                                                    $fueraRangoMatriz = ($celda && isset($celda['resultado']))
                                                        ? $esFueraDeRango($celda['resultado'], $celda['referencia'] ?? '') 
                                                        : false;
                                                @endphp
                                                <td class="@if($fueraRangoMatriz) fuera-de-rango @endif">
                                                    {{ $celda['resultado'] ?? '-' }}
                                                </td>
                                            @endforeach
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        @endforeach
                    @endif
                    
                    <div style="height: 10px;"></div>
                @endforeach
            @endforeach

            {{-- FIRMA DEL USUARIO --}}
            <div class="firma-container">
                <table class="firma-table">
                    <tr>
                        <td style="width: 50%; text-align: center; vertical-align: bottom;">
                            @if ($sello_registro_b64)
                                <img src="{{ $sello_registro_b64 }}" style="width: 110px;">
                            @endif
                        </td>
                        <td style="width: 50%; text-align: center; vertical-align: bottom;">
                            <div class="firma-wrapper">
                                @if ($grupo['sello_b64'])
                                    <img src="{{ $grupo['sello_b64'] }}" style="position: absolute; top:0; left:0; width: 100%; height: 100%; object-fit: contain; opacity: 0.8;">
                                @endif
                                @if ($grupo['firma_b64'])
                                    <img src="{{ $grupo['firma_b64'] }}" style="position: absolute; top: 10%; left: 10%; width: 80%; object-fit: contain; z-index: 10;">
                                @endif
                            </div>
                            <div style="border-top: 1px solid #000; display: inline-block; padding: 5px 20px; font-weight: bold; margin-top: 5px;">
                                {{ $grupo['laboratorista'] }}
                            </div>
                            <div style="font-size: 8px;">Laboratorista Clínico</div>
                        </td>
                    </tr>
                </table>
            </div>

        </div> 
        
        @if(!$loop->last)
            <div class="salto-pagina"></div>
        @endif

    @endforeach

</body>
</html>
