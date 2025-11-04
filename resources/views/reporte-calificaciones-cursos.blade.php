<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte de Calificaciones</title>
    <style>
        /* --------- Configuración general y de impresión --------- */
        @page { margin: 20mm; }
        * { box-sizing: border-box; }
        html, body { height: 100%; }
        body {
            font-family: 'DejaVu Sans', Arial, Helvetica, sans-serif;
            color: #1a1a1a;
            font-size: 12px;
            line-height: 1.35;
        }
        h1, h2, h3, h4, h5 { margin: 0; }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .text-left { text-align: left; }
        .mt-1 { margin-top: .25rem; } .mt-2 { margin-top: .5rem; } .mt-3 { margin-top: .75rem; }
        .mb-1 { margin-bottom: .25rem; } .mb-2 { margin-bottom: .5rem; } .mb-3 { margin-bottom: .75rem; }
        .py-1 { padding-top: .25rem; padding-bottom: .25rem; }
        .py-2 { padding-top: .5rem;  padding-bottom: .5rem; }
        .px-2 { padding-left: .5rem; padding-right: .5rem; }
        .small { font-size: 11px; }
        .muted { color: #666; }
        .brand-green { color: #2e7d32; }
        .bg-brand { background: #e8f5e9; }
        .fw-bold { font-weight: 700; }
        .avoid-break { page-break-inside: avoid; }
        .hr { height: 1px; background: #ddd; margin: 10px 0; }

        /* Layout de encabezado */
        .header {
            display: grid;
            grid-template-columns: 120px 1fr 120px;
            gap: 12px;
            align-items: center;
        }
        .header .title {
            text-align: center;
        }
        .header .title h1 {
            font-size: 20px;
            letter-spacing: .3px;
        }
        .header .title .subtitle { font-size: 12px; }
        .logo {
            width: 100%;
            height: 70px;
            object-fit: contain;
        }

        /* Tablas base */
        table { width: 100%; border-collapse: collapse; }
        .table { border: 1px solid #bdbdbd; }
        .table th, .table td { border: 1px solid #bdbdbd; padding: 6px 8px; }
        .table thead th {
            background: #2e7d32;
            color: #fff;
            font-weight: 700;
            text-transform: uppercase;
            font-size: 11px;
            letter-spacing: .3px;
        }
        .table tfoot td {
            background: #f3f3f3;
            font-weight: 700;
        }
        .table.striped tbody tr:nth-child(odd) { background: #fafafa; }

        /* Tabla de ficha del estudiante */
        .card-table th {
            background: #e0e0e0;
            color: #111;
            width: 15%;
            text-align: left;
            font-weight: 700;
            white-space: nowrap;
        }
        .card-table td { background: #fff; }

        /* Asistencia en 4 columnas */
        .grid-4 {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 10px;
        }
        .mini-card {
            border: 1px solid #bdbdbd;
            border-radius: 4px;
            overflow: hidden;
        }
        .mini-card .mini-header {
            background: #2e7d32;
            color: #fff;
            font-weight: 700;
            text-align: center;
            padding: 6px 8px;
            text-transform: uppercase;
            font-size: 11px;
            letter-spacing: .4px;
        }
        .mini-card table { width: 100%; border-collapse: collapse; }
        .mini-card td { border-top: 1px solid #e0e0e0; padding: 6px 8px; }
        .mini-card td.label { color: #444; }
        .mini-card td.value { text-align: right; font-weight: 700; }

        /* Observaciones */
        .observaciones {
            border: 1px solid #bdbdbd;
            border-radius: 4px;
            padding: 10px 12px;
            background: #fafafa;
            font-style: italic;
        }

        /* Asegurar buena salida a PDF */
        .print-block { margin-top: 10px; margin-bottom: 14px; }
        .w-100 { width: 100%; }
    </style>
</head>
<body>
    <!-- Encabezado institucional -->
    <section class="print-block avoid-break">
        <div class="header">
            <div>
                @if(isset($isPdf) && $isPdf)
                    <img class="logo" src="{{ public_path('images/logo-sagrada-familia.png') }}" alt="Logo">
                @else
                    <img class="logo" src="{{ asset('images/logo-sagrada-familia.png') }}" alt="Logo">
                @endif
            </div>
            <div class="title">
                <h1 class="brand-green">Centro Cultural Sagrada Familia 2</h1>
                <div class="subtitle">
                    Lotes 64, 65 y 66, manzana A, sector 10 – Prados de Villa Hermosa, San Miguel Petapa<br>
                    Tel. 24646082 — Prados de Villa Hermosa San Miguel Petapa — Guatemala
                </div>
            </div>
            <div></div>
        </div>
    </section>

    <!-- Datos del curso -->
    <section class="print-block avoid-break">
        <table class="table card-table">
            <tbody>
                <tr>
                    <th>Curso:</th>
                    <td colspan="5">{{ $curso->nombre ?? 'N/A' }}</td>
                </tr>
            </tbody>
        </table>
    </section>

    <!-- Tabla de calificaciones -->
    <section class="print-block avoid-break">
        <table class="table striped">
            <thead>
                <tr>
                    <th style="width: 40px;">No.</th>
                    <th>Estudiante</th>
                    @foreach($actividades as $actividad)
                        <th style="width: 90px;">{{ $actividad->nombre }}</th>
                    @endforeach
                    <th style="width: 90px;">Promedio</th>
                </tr>
            </thead>
            <tbody>
                @foreach($estudiantes as $index => $estudiante)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ strtoupper($estudiante['nombre']) }}</td>
                    @foreach($actividades as $actividad)
                        @php
                            $nota = $notas[$estudiante['id']][$actividad->id] ?? null;
                            $valor = $nota !== null ? number_format($nota, 2) : '—';
                            $esReprobado = $nota !== null && $nota < 60;
                        @endphp
                        <td>
                            @if($esReprobado)
                                <span style="color:#c62828; font-weight:700;">{{ $valor }}</span>
                            @else
                                {{ $valor }}
                            @endif
                        </td>
                    @endforeach
                    @php
                        $notasEstudiante = array_filter($notas[$estudiante['id']] ?? [], fn($n) => $n !== null);
                        $promedio = count($notasEstudiante) > 0 
                            ? number_format(array_sum($notasEstudiante) / count($notasEstudiante), 2)
                            : '—';
                        $promedioReprobado = is_numeric(str_replace(',', '', $promedio)) && floatval(str_replace(',', '', $promedio)) < 60;
                    @endphp
                    <td>
                        @if($promedioReprobado)
                            <span style="color:#c62828; font-weight:700;">{{ $promedio }}</span>
                        @else
                            {{ $promedio }}
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="{{ count($actividades) + 2 }}" class="text-right">PROMEDIO GENERAL</td>
                    @php
                        $todasNotas = [];
                        foreach($notas as $estudianteNotas) {
                            foreach($estudianteNotas as $nota) {
                                if($nota !== null) $todasNotas[] = $nota;
                            }
                        }
                        $promedioGeneral = count($todasNotas) > 0 
                            ? number_format(array_sum($todasNotas) / count($todasNotas), 2)
                            : '—';
                    @endphp
                    <td>{{ $promedioGeneral }}</td>
                </tr>
            </tfoot>
        </table>
    </section>
</body>
</html>