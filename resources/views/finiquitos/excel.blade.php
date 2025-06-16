<table>
    <thead>
        <tr>
            <th colspan="2" style="font-weight: bold; font-size: 16px; text-align: center;">{{ $titulo_documento }}</th>
        </tr>
        <tr>
            <th colspan="2" style="font-weight: bold; font-size: 14px; text-align: center;">{{ $empleado->nombre_completo }}</th>
        </tr>
        <tr></tr>
        <tr>
            <th colspan="2" style="font-weight: bold;">Información laboral proporcionada para el cálculo:</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>Fecha de ingreso:</td>
            <td>{{ $empleado->fecha_ingreso->format('d/m/Y') }}</td>
        </tr>
        <tr>
            <td>Salario por día:</td>
            <td>{{ number_format($salarioDiario, 2) }}</td>
        </tr>
        <tr>
            <td>Último día laborado:</td>
            <td>{{ $fecha_final_formateada }}</td>
        </tr>
        <tr></tr>
        <tr>
            <th style="font-weight: bold;">CONCEPTO</th>
            <th style="font-weight: bold; text-align: right;">MONTO</th>
        </tr>
        @if($dias_laborados_monto > 0)
        <tr>
            <td>Días Laborados ({{ $dias_laborados_dias }} días)</td>
            <td style="text-align: right;">{{ $dias_laborados_monto }}</td>
        </tr>
        @endif
        @if($aguinaldo_monto > 0)
        <tr>
            <td>Aguinaldo</td>
            <td style="text-align: right;">{{ $aguinaldo_monto }}</td>
        </tr>
        @endif
        @if($vacaciones_monto > 0)
        <tr>
            {{-- CAMBIO AQUÍ --}}
            <td>Vacaciones</td>
            <td style="text-align: right;">{{ $vacaciones_monto }}</td>
        </tr>
        @endif
        @if($prima_vacacional_monto > 0)
        <tr>
            <td>Prima vacacional</td>
            <td style="text-align: right;">{{ $prima_vacacional_monto }}</td>
        </tr>
        @endif
        @if($caja_ahorro_monto > 0)
        <tr>
            <td>Caja de ahorro</td>
            <td style="text-align: right;">{{ $caja_ahorro_monto }}</td>
        </tr>
        @endif
        @if($monto_3_meses > 0)
        <tr>
            <td>3 Meses de salario (Indemnización)</td>
            <td style="text-align: right;">{{ $monto_3_meses }}</td>
        </tr>
        @endif
        @if($monto_prima_antiguedad > 0)
        <tr>
            <td>Prima de antigüedad</td>
            <td style="text-align: right;">{{ $monto_prima_antiguedad }}</td>
        </tr>
        @endif
        <tr>
            <th style="font-weight: bold; text-align: right;">TOTAL A PAGAR</th>
            <th style="font-weight: bold; text-align: right;">{{ $neto_a_pagar }}</th>
        </tr>
    </tbody>
</table>
