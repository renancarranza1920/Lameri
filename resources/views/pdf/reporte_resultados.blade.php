
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte de Resultados</title>
    <style>
        @page { margin: 40px 50px; }
        body { font-family: 'Helvetica', sans-serif; font-size: 10px; color: #333; }
        
        .header { display: table; width: 100%; border-bottom: 2px solid #333; padding-bottom: 10px; margin-bottom: 20px; }
        .header-left { display: table-cell; vertical-align: top; width: 45%; }
        .header-left img { max-width: 150px; margin-bottom: 10px; }
        .header-left .lab-name { font-size: 14px; font-weight: bold; color: #1E73BE; margin: 0; }
        .header-left .lab-address { font-size: 9px; margin: 0; }

        .header-right { display: table-cell; vertical-align: top; width: 55%; }
        .patient-info { border: 1px solid #000; padding: 8px; font-size: 10px; }
        .patient-info table { width: 100%; }
        .patient-info td { padding: 2px; }

        .tipo-examen-header { font-size: 14px; font-weight: bold; color: #1E73BE; padding: 10px 0; margin-top: 15px; border-bottom: 1px solid #1E73BE; }
        .results-table { width: 100%; border-collapse: collapse; margin-top: 10px; page-break-inside: auto; }
        .results-table thead th { font-weight: bold; text-transform: uppercase; border-bottom: 2px solid #000; padding: 6px; text-align: left; }
        .examen-title-row td { font-weight: bold; font-size: 11px; background-color: #f2f2f2; border-bottom: 1px solid #ccc; padding: 8px 6px;}
        .result-row { border-bottom: 1px solid #eee; page-break-inside: avoid; }
        .results-table td { padding: 6px; text-align: left; vertical-align: top; }
        .result-prueba-name { text-transform: uppercase; }
        .result-value { font-weight: bold; padding-left: 10px; }
        
        /* Estilos para la tabla de matriz */
        .matrix-table { width: 100%; border-collapse: collapse; margin-top: 15px; page-break-inside: auto; }
        .matrix-table th, .matrix-table td { border: 1px solid #ccc; text-align: center; padding: 5px; vertical-align: middle; }
        .matrix-table thead th { font-weight: bold; text-transform: uppercase; background-color: #f2f2f2; border-bottom: 2px solid #000; }
        .matrix-table tbody th { font-weight: bold; background-color: #f2f2f2; } /* Cabeceras de fila */
        .matrix-table td { font-size: 11px; font-weight: bold; }
        .matrix-table thead th:first-child { background-color: transparent; border: none; } /* Esquina vacía */

        footer { position: fixed; bottom: -20px; left: 0px; right: 0px; height: 40px; text-align: center; font-size: 9px; color: #888; border-top: 1px solid #ccc; padding-top: 5px;}
        footer .page-number:before { content: "Página " counter(page); }
    </style>
</head>
<body>
 <footer>
        <div>{{ $orden->cliente->nombre ?? 'Nombre Laboratorio' }} - Reporte de Resultados</div>
        <div class="page-number"></div>
    </footer>

    <div class="header">
        <div class="header-left">
            <img src="{{ public_path('storage/logo.png') }}" alt="Logo">
           
            <p class="lab-address">
               4TA CALLE ORIENTE BARRIO SAN FRANCISCO #6 SAN VICENTE San Vicente CP, 1701<br>
                Tels. (503) 75954210
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
                    {{-- Solo mostramos la cabecera si hay pruebas unitarias --}}
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
                    
                    {{-- RENDERIZAR PRUEBAS UNITARIAS --}}
                    @foreach($examen['pruebas_unitarias'] as $pruebaData)
                        <tr class="result-row">
                            <td>
                                <div class="result-prueba-name">{{ $pruebaData['nombre'] }}</div>
                                <div class="result-value">{!! $pruebaData['resultado'] !!}</div>
                            </td>
                            <td>{!! $pruebaData['referencia'] !!}</td>
                            <td>{{ $pruebaData['unidades'] }}</td>
                            <td>{{ $pruebaData['fecha_resultado'] }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            {{-- RENDERIZAR MATRICES --}}
            @if (!empty($examen['matrices']))
                @foreach ($examen['matrices'] as $matriz)
                    <table class="matrix-table">
                        <thead>
                            <tr>
                                <th></th> {{-- Esquina vacía --}}
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
                                        <td>
                                            {{ $matriz['data'][$fila][$columna]['resultado'] ?? 'N/A' }}
                                        </td>
                                    @endforeach
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endforeach
            @endif
             <div style="height: 15px;"></div> {{-- Espacio entre exámenes --}}
        @endforeach
    @endforeach
</body>
</html>

