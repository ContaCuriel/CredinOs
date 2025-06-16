<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>{{ $titulo_documento }}</title>
    <style>
        body { font-family: 'Helvetica', 'Arial', sans-serif; font-size: 12px; color: #333; }
        .container { width: 100%; margin: 0 auto; }
        .header { text-align: center; height: 100px; margin-bottom: 20px;}
        .header img { max-width: 250px; max-height: 90px; }
        .title { background-color: #f2f2f2; padding: 5px; text-align: center; font-weight: bold; font-size: 16px; margin-bottom: 5px; border-bottom: 2px solid #333; }
        .employee-name { text-align: center; font-weight: bold; font-size: 14px; margin-bottom: 20px; }
        .section-title { font-weight: bold; margin-top: 20px; margin-bottom: 10px; border-bottom: 1px solid #eee; padding-bottom: 5px;}
        .info-table, .breakdown-table { width: 100%; border-collapse: collapse; }
        .info-table td { padding: 5px; }
        .breakdown-table th, .breakdown-table td { padding: 8px; border: 1px solid #ddd; }
        .breakdown-table th { background-color: #f9f9f9; text-align: left; }
        .text-right { text-align: right; }
        .total-row td { font-weight: bold; background-color: #e9ecef; font-size: 14px; }
        .signature-section { margin-top: 80px; text-align: center; }
        .signature-line { border-bottom: 1px solid #333; width: 60%; margin: 0 auto; padding-top: 40px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            @if(isset($logo_base64))
                <img src="{{ $logo_base64 }}" alt="Logo Patrón">
            @elseif(isset($patron))
                <h3>{{ $patron->razon_social ?? 'Empresa' }}</h3>
            @endif
        </div>

        <div class="title">{{ $titulo_documento }}</div>
        <div class="employee-name">{{ $empleado->nombre_completo }}</div>

        <div class="section-title">Información Laboral</div>
        <table class="info-table">
            <tr>
                <td>Fecha de ingreso:</td>
                <td class="text-right">{{ $empleado->fecha_ingreso->format('d/m/Y') }}</td>
            </tr>
            <tr>
                <td>Salario por día:</td>
                <td class="text-right">${{ number_format($salarioDiario, 2) }}</td>
            </tr>
            <tr>
                <td>Último día laborado:</td>
                <td class="text-right">{{ $fecha_final_formateada }}</td>
            </tr>
        </table>

        <div class="section-title">Desglose de Conceptos</div>
        <table class="breakdown-table">
            <thead>
                <tr>
                    <th>CONCEPTO</th>
                    <th class="text-right">MONTO</th>
                </tr>
            </thead>
            <tbody>
                @if($dias_laborados_monto > 0)
                <tr>
                    <td>Días Laborados ({{ $dias_laborados_dias }} días)</td>
                    <td class="text-right">${{ number_format($dias_laborados_monto, 2) }}</td>
                </tr>
                @endif
                @if($aguinaldo_monto > 0)
                <tr>
                    <td>Aguinaldo</td>
                    <td class="text-right">${{ number_format($aguinaldo_monto, 2) }}</td>
                </tr>
                @endif
                @if($vacaciones_monto > 0)
                <tr>
                    {{-- CAMBIO AQUÍ --}}
                    <td>Vacaciones</td>
                    <td class="text-right">${{ number_format($vacaciones_monto, 2) }}</td>
                </tr>
                @endif
                @if($prima_vacacional_monto > 0)
                <tr>
                    <td>Prima vacacional</td>
                    <td class="text-right">${{ number_format($prima_vacacional_monto, 2) }}</td>
                </tr>
                @endif
                @if($caja_ahorro_monto > 0)
                <tr>
                    <td>Caja de ahorro</td>
                    <td class="text-right">${{ number_format($caja_ahorro_monto, 2) }}</td>
                </tr>
                @endif
                @if($monto_3_meses > 0)
                <tr>
                    <td>3 Meses de salario (Indemnización)</td>
                    <td class="text-right">${{ number_format($monto_3_meses, 2) }}</td>
                </tr>
                @endif
                @if($monto_prima_antiguedad > 0)
                <tr>
                    <td>Prima de antigüedad</td>
                    <td class="text-right">${{ number_format($monto_prima_antiguedad, 2) }}</td>
                </tr>
                @endif
                <tr class="total-row">
                    <td>TOTAL A PAGAR AL TRABAJADOR</td>
                    <td class="text-right">${{ number_format($neto_a_pagar, 2) }}</td>
                </tr>
            </tbody>
        </table>

        <div class="signature-section">
            <div class="signature-line"></div>
            Recibí de entera conformidad
            <br>
            <strong>{{ $empleado->nombre_completo }}</strong>
        </div>
    </div>
</body>
</html>
