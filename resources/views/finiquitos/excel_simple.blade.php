<table>
    <thead>
        <tr><td colspan="2" style="height: 15px;"></td></tr>
        <tr><td colspan="2" style="height: 15px;"></td></tr>
        <tr><td colspan="2" style="height: 15px;"></td></tr>
        <tr><td colspan="2" style="height: 15px;"></td></tr>
        <tr><td colspan="2" style="height: 15px;"></td></tr>
        <tr><td colspan="2" style="height: 15px;"></td></tr>
        <tr><td colspan="2" style="height: 15px;"></td></tr>
        <tr><th colspan="2">{{ $titulo_documento }}</th></tr>
        <tr><th colspan="2">{{ $empleado->nombre_completo }}</th></tr>
        <tr><td colspan="2"></td></tr>
        <tr><th colspan="2">Información Laboral</th></tr>
    </thead>
    <tbody>
        <tr>
            <td>Fecha de ingreso:</td>
            <td>{{ $empleado->fecha_ingreso->format('d/m/Y') }}</td>
        </tr>
        <tr>
            <td>Salario por día:</td>
            <td>${{ number_format($salarioDiario, 2) }}</td>
        </tr>
        <tr>
            <td>Último día laborado:</td>
            <td>{{ $fecha_final_formateada }}</td>
        </tr>
        <tr><td colspan="2"></td></tr>
        <tr><th colspan="2">Desglose de Conceptos</th></tr>
        <tr><th>CONCEPTO</th><th>MONTO</th></tr>

        @if($dias_laborados_monto > 0)
        <tr>
            <td>Días Laborados ({{ $dias_laborados_dias }} días)</td>
            <td>{{ $dias_laborados_monto }}</td>
        </tr>
        @endif
        @if($aguinaldo_monto > 0)
        <tr>
            <td>Aguinaldo</td>
            <td>{{ $aguinaldo_monto }}</td>
        </tr>
        @endif
        @if($vacaciones_monto > 0)
        <tr>
            <td>Vacaciones</td>
            <td>{{ $vacaciones_monto }}</td>
        </tr>
        @endif
        @if($prima_vacacional_monto > 0)
        <tr>
            <td>Prima vacacional</td>
            <td>{{ $prima_vacacional_monto }}</td>
        </tr>
        @endif
        @if(isset($gratificacion_monto) && $gratificacion_monto > 0)
        <tr>
            <td>Gratificación</td>
            <td>{{ $gratificacion_monto }}</td>
        </tr>
        @endif
        @if($caja_ahorro_monto > 0)
        <tr>
            <td>Caja de ahorro</td>
            <td>{{ $caja_ahorro_monto }}</td>
        </tr>
        @endif
        @if($monto_3_meses > 0)
        <tr>
            <td>3 Meses de salario (Indemnización)</td>
            <td>{{ $monto_3_meses }}</td>
        </tr>
        @endif
        @if($monto_prima_antiguedad > 0)
        <tr>
            <td>Prima de antigüedad</td>
            <td>{{ $monto_prima_antiguedad }}</td>
        </tr>
        @endif

        <tr>
            <td>TOTAL A PAGAR AL TRABAJADOR</td>
            <td></td>
        </tr>

        <tr><td colspan="2" style="height: 60px;"></td></tr>
        <tr><td colspan="2"></td></tr>
        <tr>
            <td colspan="2">Recibí de entera conformidad</td>
        </tr>
        <tr>
            <td colspan="2"><strong>{{ $empleado->nombre_completo }}</strong></td>
        </tr>

        <tr><td colspan="2" style="height: 15px;"></td></tr>
        <tr><td colspan="2" style="height: 15px;"></td></tr>
    </tbody>
</table>