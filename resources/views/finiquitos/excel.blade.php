<table>
    <thead>
        <tr>
            <th colspan="2" style="height: 80px; text-align: center; vertical-align: middle;">
                </th>
        </tr>
        <tr>
            <th colspan="2" style="font-weight: bold; font-size: 16px; text-align: center; background-color: #f2f2f2; padding: 5px; border-bottom: 2px solid #333333;">{{ $titulo_documento }}</th>
        </tr>
        <tr>
            <th colspan="2" style="font-weight: bold; font-size: 14px; text-align: center; padding: 10px 0;">{{ $empleado->nombre_completo }}</th>
        </tr>
        <tr>
            <td colspan="2"></td> </tr>
        <tr>
            <th colspan="2" style="font-weight: bold; border-bottom: 1px solid #eeeeee; padding-bottom: 5px;">Información Laboral</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>Fecha de ingreso:</td>
            <td style="text-align: right;">{{ $empleado->fecha_ingreso->format('d/m/Y') }}</td>
        </tr>
        <tr>
            <td>Salario por día:</td>
            <td style="text-align: right;">${{ number_format($salarioDiario, 2) }}</td>
        </tr>
        <tr>
            <td>Último día laborado:</td>
            <td style="text-align: right;">{{ $fecha_final_formateada }}</td>
        </tr>
        <tr>
            <td colspan="2" style="height: 20px;"></td> </tr>
        <tr>
            <th colspan="2" style="font-weight: bold; border-bottom: 1px solid #eeeeee; padding-bottom: 5px;">Desglose de Conceptos</th>
        </tr>
        <tr>
            <th style="font-weight: bold; background-color: #f9f9f9; text-align: left; border: 1px solid #dddddd;">CONCEPTO</th>
            <th style="font-weight: bold; background-color: #f9f9f9; text-align: right; border: 1px solid #dddddd;">MONTO</th>
        </tr>
        
        @if($dias_laborados_monto > 0)
        <tr>
            <td style="border: 1px solid #dddddd;">Días Laborados ({{ $dias_laborados_dias }} días)</td>
            <td style="text-align: right; border: 1px solid #dddddd;">${{ number_format($dias_laborados_monto, 2) }}</td>
        </tr>
        @endif
        @if($aguinaldo_monto > 0)
        <tr>
            <td style="border: 1px solid #dddddd;">Aguinaldo</td>
            <td style="text-align: right; border: 1px solid #dddddd;">${{ number_format($aguinaldo_monto, 2) }}</td>
        </tr>
        @endif
        @if($vacaciones_monto > 0)
        <tr>
            <td style="border: 1px solid #dddddd;">Vacaciones</td>
            <td style="text-align: right; border: 1px solid #dddddd;">${{ number_format($vacaciones_monto, 2) }}</td>
        </tr>
        @endif
        @if($prima_vacacional_monto > 0)
        <tr>
            <td style="border: 1px solid #dddddd;">Prima vacacional</td>
            <td style="text-align: right; border: 1px solid #dddddd;">${{ number_format($prima_vacacional_monto, 2) }}</td>
        </tr>
        @endif
        @if(isset($gratificacion_monto) && $gratificacion_monto > 0)
        <tr>
            <td style="border: 1px solid #dddddd;">Gratificación</td>
            <td style="text-align: right; border: 1px solid #dddddd;">${{ number_format($gratificacion_monto, 2) }}</td>
        </tr>
        @endif
        @if($caja_ahorro_monto > 0)
        <tr>
            <td style="border: 1px solid #dddddd;">Caja de ahorro</td>
            <td style="text-align: right; border: 1px solid #dddddd;">${{ number_format($caja_ahorro_monto, 2) }}</td>
        </tr>
        @endif
        @if($monto_3_meses > 0)
        <tr>
            <td style="border: 1px solid #dddddd;">3 Meses de salario (Indemnización)</td>
            <td style="text-align: right; border: 1px solid #dddddd;">${{ number_format($monto_3_meses, 2) }}</td>
        </tr>
        @endif
        @if($monto_prima_antiguedad > 0)
        <tr>
            <td style="border: 1px solid #dddddd;">Prima de antigüedad</td>
            <td style="text-align: right; border: 1px solid #dddddd;">${{ number_format($monto_prima_antiguedad, 2) }}</td>
        </tr>
        @endif

        <tr>
            <td style="font-weight: bold; font-size: 14px; text-align: left; background-color: #e9ecef; border: 1px solid #dddddd;">TOTAL A PAGAR AL TRABAJADOR</td>
            <td style="font-weight: bold; font-size: 14px; text-align: right; background-color: #e9ecef; border: 1px solid #dddddd;">${{ number_format($neto_a_pagar, 2) }}</td>
        </tr>
        
        <tr>
            <td colspan="2" style="height: 60px;"></td> </tr>
        <tr>
            <td colspan="2" style="text-align: center; border-top: 1px solid #333333; padding-top: 5px;">Recibí de entera conformidad</td>
        </tr>
        <tr>
            <td colspan="2" style="text-align: center; font-weight: bold;">{{ $empleado->nombre_completo }}</td>
        </tr>
    </tbody>
</table>