<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Reporte de Resultados</title>
    <style>
        /* --- 1. CONFIGURACIÓN DE PÁGINA PARA EVITAR CHOQUES CON EL SELLO --- */
        @page {
            /* Margen inferior de 200px: 
               Crea una "zona prohibida" al final de la hoja donde la tabla no puede entrar.
               Esto fuerza el salto de página automático. */
            margin: 40px 50px 200px 50px;
        }

        body {
            font-family: 'Helvetica', sans-serif;
            font-size: 10px;
            color: #333;
        }

        /* --- 2. POSICIONAMIENTO DEL FOOTER (SELLOS) --- */
        footer {
            position: fixed;
            /* Usamos coordenadas negativas para colocar el footer DENTRO 
               del margen inferior de 200px que definimos arriba. */
            bottom: -180px; 
            left: 0px;
            right: 0px;
            height: 160px; /* Altura suficiente para el sello y firmas */
            
            text-align: center;
            font-size: 9px;
            color: #888;
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

        /* --- 3. TABLA DE RESULTADOS "INTELLIGENTE" --- */
        .results-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
            /* Permite que la tabla se rompa entre páginas */
            page-break-inside: auto; 
        }

        .results-table thead {
            display: table-header-group; /* Repite encabezados si salta página */
        }

        .results-table tr {
            /* Evita que una fila se parta por la mitad (texto arriba, borde abajo) */
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

        .examen-title-row {
            page-break-after: avoid; /* Intenta mantener el título con la primera fila */
        }

        .examen-title-row td {
            font-weight: bold;
            font-size: 11px;
            background-color: #f2f2f2;
            border-bottom: 1px solid #ccc;
            padding: 8px 6px;
        }

        .group-title-row {
            page-break-after: avoid;
        }

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

        .footer-content {
            margin-top: 10px;
            text-align: right;
            width: 100%;
        }

        .signatures-container {
            display: inline-block;
            margin-top: 10px;
        }

        .signature-block {
            display: inline-block;
            margin-left: 20px;
            vertical-align: top;
        }

        .signature-wrapper {
            position: relative;
            width: 130px;
            height: 80px;
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
            color: #D90000 !important;
            font-weight: bold;
        }
    </style>
</head>

<body>
    {{-- LÓGICA PHP INCRUSTADA PARA CALCULAR RANGOS (ROJO SI ESTÁ MAL) --}}
    @php
        $esFueraDeRango = function($resultado, $referencia) {
            if (empty($resultado) || empty($referencia)) return false;

            // Limpiar resultado (quitar textos y dejar solo numero float)
            $valorStr = preg_replace('/[^0-9\.,]/', '', str_replace(',', '.', $resultado));
            
            if (!is_numeric($valorStr)) return false;
            
            $valor = (float) $valorStr;

            // Extraer números de la referencia
            preg_match_all('/[0-9]+(\.[0-9]+)?/', str_replace(',', '.', $referencia), $matches);
            $nums = $matches[0];

            // CASO A: Rango "min - max" (ej: 8.6 - 11)
            if (count($nums) >= 2) {
                $min = (float) $nums[0];
                $max = (float) $nums[1];
                return ($valor < $min || $valor > $max);
            }

            // CASO B: Limites "<" o ">" (ej: < 150)
            if (count($nums) == 1) {
                $limite = (float) $nums[0];
                if (str_contains($referencia, '<')) return $valor >= $limite;
                if (str_contains($referencia, '>')) return $valor <= $limite;
            }

            return false;
        };
    @endphp

    <footer>
        {{-- EL FOOTER SE IMPRIME EN TODAS LAS PÁGINAS DENTRO DEL MARGEN RESERVADO --}}
        <div class="footer-content">
            <div class="signatures-container">
                
                {{-- BLOQUE 1: SELLO DEL REGISTRO --}}
                <div class="signature-block">
                    @if ($sello_registro_b64)
                        <img src="{{ $sello_registro_b64 }}" alt="Sello Registro" style="width: 130px; height: auto;">
                    @else
                        <div style="width: 130px; height: 80px; border: 1px dashed #ccc; display:flex; align-items:center; justify-content:center; font-size:9px;">
                            [Sello Reg.]
                        </div>
                    @endif
                </div>

                {{-- BLOQUE 2: FIRMA Y SELLO DEL LICENCIADO --}}
                <div class="signature-block">
                    <div class="signature-wrapper">
                        
                        @if ($sello_usuario_b64)
                            <img src="{{ $sello_usuario_b64 }}" alt="Sello Usuario" style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; object-fit: contain;">
                        @endif

                        @if ($firma_usuario_b64)
                            <img src="{{ $firma_usuario_b64 }}" alt="Firma" style="position: absolute; top: 8%; left: 50%; transform: translate(-50%, -50%); max-width: 90%; height: auto; object-fit: contain; z-index: 10;">
                        @endif

                        @if (!$sello_usuario_b64 && !$firma_usuario_b64)
                            <div style="width: 100%; height: 100%; border: 1px dashed #ccc; display:flex; align-items:center; justify-content:center; font-size:9px;">
                                [Sin Firma]
                            </div>
                        @endif
                        
                    </div>
                </div>

            </div>
        </div>
        
        <div style="margin-top: 5px;">
            <div>{{ $orden->cliente->nombre ?? 'Nombre Laboratorio' }} - Reporte de Resultados</div>
            <div class="page-number"></div>
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

    {{-- BUCLE PRINCIPAL DE EXÁMENES --}}
    @foreach($datos_agrupados as $tipoExamenNombre => $examenes)
        <div class="tipo-examen-header">{{ $tipoExamenNombre }}</div>

        @foreach($examenes as $examen)
            @php
                // Detectar si mostrar col referencia
                $tieneReferencias = false;
                if (!empty($examen['pruebas_unitarias'])) {
                    foreach($examen['pruebas_unitarias'] as $p) {
                        $refLimpia = trim(strip_tags($p['referencia'] ?? ''));
                        $uniLimpia = trim($p['unidades'] ?? '');
                        if ( ($refLimpia !== '' && $refLimpia !== 'N/A') || $uniLimpia !== '') {
                            $tieneReferencias = true;
                            break;
                        }
                    }
                }

                // Agrupar pruebas
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

                    {{-- 1. Pruebas sin grupo --}}
                    @foreach($sinGrupo as $pruebaData)
                        <tr class="result-row">
                            <td>
                                <div class="result-prueba-name">{{ $pruebaData['nombre'] }}</div>
                            </td>
                            <td>
                                {{-- USO DE LA FUNCIÓN $esFueraDeRango --}}
                                <div class="result-value @if($esFueraDeRango($pruebaData['resultado'], $pruebaData['referencia'] ?? '')) fuera-de-rango @endif">
                                    {!! $pruebaData['resultado'] !!}
                                </div>
                            </td>
                            @if($tieneReferencias)
                                <td>{!! $pruebaData['referencia'] !!} {{ $pruebaData['unidades'] }}</td>
                            @endif
                        </tr>
                    @endforeach

                    {{-- 2. Pruebas agrupadas --}}
                    @foreach($conGrupo as $nombreGrupo => $items)
                        <tr class="group-title-row">
                            <td colspan="{{ $tieneReferencias ? 3 : 2 }}">{{ $nombreGrupo }}</td>
                        </tr>
                        @foreach($items as $pruebaData)
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
                    @endforeach

                </tbody>
            </table>

            {{-- 3. Matrices --}}
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
                                            $fueraRangoMatriz = $celda 
                                                ? $esFueraDeRango($celda['resultado'], $celda['referencia'] ?? '') 
                                                : false;
                                        @endphp
                                        <td class="@if($fueraRangoMatriz) fuera-de-rango @endif">
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
</body>
</html>
