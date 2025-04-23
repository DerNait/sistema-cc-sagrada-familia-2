<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Planilla de Salarios - {{ $periodo->mes }}/{{ $periodo->anio }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
        }
        h2 {
            text-align: center;
            margin-bottom: 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 12px;
        }
        thead {
            background-color: #f0f0f0;
        }
        th, td {
            border: 1px solid #333;
            padding: 5px;
            text-align: left;
        }
        .text-right {
            text-align: right;
        }
    </style>
</head>
<body>

    <h2>Planilla de Salarios - {{ str_pad($periodo->mes, 2, '0', STR_PAD_LEFT) }}/{{ $periodo->anio }}</h2>

    <table>
        <thead>
            <tr>
                <th>No.</th>
                <th>Nombre</th>
                <th>DPI</th>
                <th>Cargo</th>
                <th>Fecha de Ingreso</th>
                <th>Cuenta</th>
                <th>Banco</th>
                <th class="text-right">Salario Base</th>
                <th class="text-right">Bonificaciones</th>
                <th class="text-right">Descuentos</th>
                <th class="text-right">Total a Depositar</th>
            </tr>
        </thead>
        <tbody>
            @php $contador = 1; @endphp
            @foreach ($datosPlanilla as $empleado)
                <tr>
                    <td>{{ $contador++ }}</td>
                    <td>{{ $empleado['name'] }}</td>
                    <td>{{ $empleado['dpi'] }}</td>
                    <td>{{ $empleado['cargo'] }}</td>
                    <td>{{ \Carbon\Carbon::parse($empleado['fecha_ingreso'])->format('d/m/Y') }}</td>
                    <td>{{ $empleado['cuenta'] }}</td>
                    <td>{{ $empleado['banco'] }}</td>
                    <td class="text-right">Q{{ number_format($empleado['salario_base'], 2) }}</td>
                    <td class="text-right">Q{{ number_format($empleado['bonificaciones'], 2) }}</td>
                    <td class="text-right">Q{{ number_format($empleado['descuentos'], 2) }}</td>
                    <td class="text-right">Q{{ number_format($empleado['total'], 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

</body>
</html>