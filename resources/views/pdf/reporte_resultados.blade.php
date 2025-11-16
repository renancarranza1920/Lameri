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
            padding-left: 10px;
        }

        /* Estilos para la tabla de matriz */
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

        /* --- ¡NUEVO ESTILO! --- */
        .fuera-de-rango {
            color: #D90000;
            /* Un rojo oscuro para impresión */
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
        <div class="header-left">
            <img src="{{ public_path('storage/logo.png') }}" alt="Logo"
                style="max-width: 90px; vertical-align: middle;">

            <div style="font-weight: bold; font-size: 13px; color: #003366; margin-top: 3px;">
                LABORATORIO CLÍNICO MERINO
            </div>

            <p style="font-size: 9px; margin: 2px 0;">
                <span style="color:#444;">4ª CALLE ORIENTE #6, B° SAN FRANCISCO, SAN VICENTE.</span>
            </p>

            <p style="font-size: 9px; margin: 0; color: #333; line-height: 1.5;">

                <!-- Grupo de Teléfono -->
                <span style="display: inline-block; vertical-align: middle;">
                    <!-- CAMBIO: width y height de 10 a 8 -->
                    <img src="data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSIxMiIgaGVpZ2h0PSIxMiIgdmlld0JveD0iMCAwIDI0IDI0IiBmaWxsPSJub25lIiBzdHJva2U9IiMzMzMiIHN0cm9rZS13aWR0aD0iMiIgc3Ryb2tlLWxpbmVjYXA9InJvdW5kIiBzdHJva2UtbGluZWpvaW49InJvdW5kIj48cGF0aCBkPSJNMjIgMTYuOTJ2M2EyIDIgMCAwIDEtMi4xOCAyIDE5Ljc5IDE5Ljc5IDAgMCAxLTguNjMtMy4wNyAxOS41IDE5LjUgMCAwIDEtNi02IDE5Ljc5IDE5Ljc5IDAgMCAxLTMuMDctOC42M0EyIDIgMCAwIDEgNC4xMSAySDdBMiAyIDAgMCAxIDkgMy4yNmExMi44NCAxMi44NCAwIDAgMCAuNyAyLjgxIDIgMiAwIDAgMS0uNDUgMi4xMUw4LjA5IDkuOTFhMTYgMTYgMCAwIDAgNiA2bDEuMjctMS4yN2EyIDIgMCAwIDEgMi4xMS0uNDUgMTIuODQgMTIuODQgMCAwIDAgMi44MS43QTIgMiAwIDAgMSAyMiAxNi45MnoiPjwvcGF0aD48L3N2Zz4="
                        width="8" height="8" style="vertical-align: middle; margin-right: 3px;" />
                </span>
                <span style="display: inline-block; vertical-align: middle; font-weight: bold;">
                    2606-6596
                </span>

                <!-- Separador -->
                <span
                    style="display: inline-block; vertical-align: middle; margin-left: 8px; margin-right: 8px; color: #888;">
                    |
                </span>

                <!-- Grupo de WhatsApp (con icono SVG corregido) -->
                <span style="display: inline-block; vertical-align: middle;">
                    <!-- CAMBIO: width y height de 10 a 8 -->
                    <img src="data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSIxMiIgaGVpZ2h0PSIxMiIgdmlld0JveD0iMCAwIDI0IDI0IiBmaWxsPSJub25lIiBzdHJva2U9IiMzMzMiIHN0cm9rZS13aWR0aD0iMiIgc3Ryb2tlLWxpbmVjYXA9InJvdW5kIiBzdHJva2UtbGluZWpvaW49InJvdW5kIj48cGF0aCBkPSJNMjEgMTEuNWE4LjM4IDguMzggMCAwIDEtLjkgMy44IDguNSA4LjUgMCAwIDEtNy42IDQuNyA4LjM4IDguMzggMCAwIDEtMy44LS45TDMgMjFsMS45LTUuN2E4LjM4IDguMzggMCAwIDEtLjktMy44IDguNSA4LjUgMCAwIDEgNC43LTcuNiA4LjM4IDguMzggMCAwIDEgMy44LS45aC41YTguNDggOC40OCAwIDAgMSA4IDh2LjV6Ij48L3BhdGg+PC9zdmc+Cg=="
                        width="8" height="8" style="vertical-align: middle; margin-right: 3px;" />
                </span>
                <span style="display: inline-block; vertical-align: middle; font-weight: bold;">
                    WhatsApp: 7595-4210
                </span>
            </p>
        </div>

        <div class="header-right">
            <div class="patient-info">
                <table>
                    <tr>
                        <td width="35%"><strong>PACIENTE:</strong></td>
                        <td width="65%">{{ $orden->cliente->nombre }} {{ $orden->cliente->apellido }}</td>
                    </tr>
                    <tr>
                        <td><strong>EDAD:</strong></td>
                        <td>{{ \Carbon\Carbon::parse($orden->cliente->fecha_nacimiento)->age }} AÑOS</td>
                    </tr>
                    <tr>
                        <td><strong>GÉNERO:</strong></td>
                        <td>{{ $orden->cliente->genero ?? 'No especificado' }}</td>
                    </tr>
                    <tr>
                        <td><strong>NRO. ORDEN:</strong></td>
                        <td>{{ $orden->id }}</td>
                    </tr>
                    <tr>
                        <td><strong>FECHA DE REPORTE:</strong></td>
                        <td>{{ now()->format('d/m/Y') }}</td>
                    </tr>
                </table>
            </div>
        </div>
    </div>


    @foreach($datos_agrupados as $tipoExamenNombre => $examenes)
        <div class="tipo-examen-header">{{ $tipoExamenNombre }}</div>

        @foreach($examenes as $examen)
            <table class="results-table">
                <thead>
                    @if (!empty($examen['pruebas_unitarias']))
                        <tr>
                            <th style="width: 35%;">RESULTADO</th>
                            <th style="width: 35%;">RANGO DE REFERENCIA</th>
                            <th style="width: 15%;">UNIDAD</th>
                            <th style="width: 15%;">FECHA RESULTADO</th>
                        </tr>
                    @endif
                </thead>
                <tbody>
                    <tr class="examen-title-row">
                        <td colspan="4">EXAMEN: {{ $examen['nombre'] }} | CÓDIGO: {{ $examen['codigo'] ?? 'N/A' }}</td>
                    </tr>

                    @foreach($examen['pruebas_unitarias'] as $pruebaData)
                        <tr class="result-row">
                            <td>
                                <div class="result-prueba-name">{{ $pruebaData['nombre'] }}</div>
                                <!-- --- ¡CAMBIO AQUÍ! --- -->
                                <div class="result-value @if($pruebaData['es_fuera_de_rango']) fuera-de-rango @endif">
                                    {!! $pruebaData['resultado'] !!}
                                </div>
                            </td>
                            <td>{!! $pruebaData['referencia'] !!}</td>
                            <td>{{ $pruebaData['unidades'] }}</td>
                            <td>{{ $pruebaData['fecha_resultado'] }}</td>
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
                                        @endphp

                                        <!-- --- ¡CAMBIO AQUÍ! --- -->
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

    {{-- Fragmento de pdf/reporte_resultados.blade.php (Sección de Firmas) --}}

    {{-- Contenedor de firmas alineado a la derecha --}}
    <div style="margin-top: 40px; text-align: right; width: 100%;">

        <div style="display: inline-block;">

            {{-- Bloque de Sello de Registro (El estático del laboratorio) --}}
            <div style="display: inline-block; margin-left: 20px; vertical-align: top;">
                @php
                    // Usamos 130px de ancho y 80px de alto como referencia del sello estático
                    $size = '130px'; 
                @endphp
                @if (file_exists($ruta_sello_registro))
                    <img src="{{ $ruta_sello_registro }}" alt="Sello Registro" style="width: {{ $size }}; height: auto;">
                @else
                    <div style="width: {{ $size }}; height: 80px; border: 1px dashed #ccc;">[Sello Reg. Faltante]</div>
                @endif
            </div>

            {{-- Bloque Combinado: Sello del Usuario + Firma del Usuario (Sobrepuestos) --}}
            <div style="display: inline-block; margin-left: 20px; vertical-align: top;">
                @php
                    $pathFirmaUsuario = isset($ruta_firma_digital) ? storage_path('app/public/' . $ruta_firma_digital) : null;
                    $pathSelloUsuario = isset($ruta_sello_digital) ? storage_path('app/public/' . $ruta_sello_digital) : null;
                @endphp

                {{-- Contenedor Relativo para la Superposición (Mantiene el tamaño de referencia) --}}
                <div style="position: relative; width: {{ $size }}; height: 80px; margin-top: 0;">

                    {{-- 1. IMAGEN DE BASE (Sello del Usuario) --}}
                    @if ($pathSelloUsuario && file_exists($pathSelloUsuario))
                        <img src="{{ $pathSelloUsuario }}" alt="Sello Usuario" style="position: absolute; 
                                        top: 0; 
                                        left: 0; 
                                        width: 100%; 
                                        height: 100%; 
                                        object-fit: contain;">
                    @else
                        <div style="width: 100%; height: 100%; border: 1px dashed #ccc;">[Sello Usr Faltante]</div>
                    @endif

                    {{-- 2. IMAGEN DE ARRIBA (Firma del Usuario) --}}
                    @if ($pathFirmaUsuario && file_exists($pathFirmaUsuario))
                        <img src="{{ $pathFirmaUsuario }}" alt="Firma" style="position: absolute; 
                                        top: 50%; 
                                        left: 50%; 
                                        transform: translate(-50%, -50%); /* Centrado */
                                        max-width: 90%; 
                                        height: auto; 
                                        object-fit: contain;
                                        z-index: 10;">
                    @endif
                </div>
            </div>
        </div>
    </div>
</body>

</html>