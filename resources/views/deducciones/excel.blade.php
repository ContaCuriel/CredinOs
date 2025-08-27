<table>
    <thead>
        <tr>
            <th>Empleado</th>
            <th>Tipo de Deducción</th>
            <th>Fecha Inicio</th>
            <th>Monto Quincenal</th>
            <th>Monto Acumulado / Saldo Pendiente</th>
            <th>Monto Total Préstamo</th>
            <th>Plazo (Quincenas)</th>
            <th>Quincenas Pagadas</th>
            <th>Status</th>
            <th>Descripción</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($deducciones as $deduccion)
            <tr>
                <td>{{ $deduccion->empleado ? $deduccion->empleado->nombre_completo : 'N/A' }}</td>
                <td>{{ $deduccion->tipo_deduccion }}</td>
                <td>{{ $deduccion->fecha_solicitud->format('d/m/Y') }}</td>
                <td>{{ $deduccion->monto_quincenal }}</td>
                <td>
                    @if ($deduccion->tipo_deduccion == 'Préstamo')
                        {{ $deduccion->saldo_pendiente }}
                    @elseif ($deduccion->tipo_deduccion == 'Caja de Ahorro')
                        {{ $deduccion->monto_acumulado }}
                    @else
                        0
                    @endif
                </td>
                {{-- NUEVAS COLUMNAS --}}
                <td>
                    {{ $deduccion->tipo_deduccion == 'Préstamo' ? $deduccion->monto_total_prestamo : 'N/A' }}
                </td>
                <td>
                    {{ $deduccion->tipo_deduccion == 'Préstamo' ? $deduccion->plazo_quincenas : 'N/A' }}
                </td>
                <td>
                    {{ $deduccion->tipo_deduccion == 'Préstamo' ? $deduccion->quincenas_pagadas : 'N/A' }}
                </td>
                <td>{{ $deduccion->status }}</td>
                <td>{{ $deduccion->descripcion }}</td>
            </tr>
        @endforeach
    </tbody>
</table>