<!DOCTYPE html>
<html>
<head>
    <title>Resumen de Incidencias</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ccc; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        .header { text-align: center; margin-bottom: 20px; }
        .text-danger { color: #dc3545; font-weight: bold; }
    </style>
</head>
<body>
    <div class="header">
        <h2>Reporte de Incidencias y Descuentos Sugeridos</h2>
        <p>Periodo: {{ $fechaInicio }} al {{ $fechaFin }}</p>
    </div>
    <table>
        <thead>
            <tr>
                <th>Empleado</th>
                <th>Sucursal</th>
                <th>Retardos</th>
                <th>Faltas</th>
                <th>Medios Días</th>
                <th>Total a Descontar</th>
            </tr>
        </thead>
        <tbody>
            @foreach($resumen as $item)
            <tr>
                <td>{{ $item['empleado'] }}</td>
                <td>{{ $item['sucursal'] }}</td>
                <td>{{ $item['retardos_acumulados'] }}</td>
                <td>{{ $item['faltas_directas'] + $item['faltas_por_retardos'] }}</td>
                <td>{{ $item['medios_dias'] }}</td>
                <td class="text-danger">{{ $item['total_faltas_final'] }} días</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>