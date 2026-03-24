<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>{{ $titulo_documento ?? 'Documento de Pago' }}</title>
    <style>
        body { font-family: 'Helvetica', 'Arial', sans-serif; font-size: 11px; color: #333; line-height: 1.4; }
        .container { width: 100%; margin: 0 auto; }
        .header { text-align: center; height: 90px; margin-bottom: 10px;}
        .header img { max-width: 200px; max-height: 80px; }
        .title { background-color: #f2f2f2; padding: 8px; text-align: center; font-weight: bold; font-size: 15px; border-bottom: 2px solid #333; text-transform: uppercase; }
        .employee-name { text-align: center; font-weight: bold; font-size: 13px; margin: 15px 0; text-transform: uppercase; }
        .section-title { font-weight: bold; margin-top: 15px; margin-bottom: 5px; border-bottom: 1px solid #eee; padding-bottom: 3px; text-transform: uppercase; font-size: 10px; color: #555;}
        .info-table, .breakdown-table { width: 100%; border-collapse: collapse; }
        .info-table td { padding: 3px; }
        .breakdown-table th, .breakdown-table td { padding: 6px; border: 1px solid #ddd; }
        .breakdown-table th { background-color: #f9f9f9; text-align: left; font-size: 10px; }
        .text-right { text-align: right; }
        .total-row td { font-weight: bold; background-color: #e9ecef; font-size: 13px; }
        .signature-section { margin-top: 60px; text-align: center; }
        .signature-line { border-bottom: 1px solid #333; width: 50%; margin: 0 auto; padding-top: 30px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            @if(isset($logo_base64) && $logo_base64)
                <img src="{{ $logo_base64 }}" alt="Logo">
            @elseif(isset($patron->razon_social))
                <h3 style="margin:0;">{{ $patron->razon_social }}</h3>
            @endif
        </div>

        <div class="title">
            {{ $esContratoDeHonorarios ? 'PAGO DE HONORARIOS DEVENGADOS' : ($titulo_documento ?? 'RECIBO DE PAGO') }}
        </div>
        
        <div class="employee-name">{{ $empleado->nombre_completo }}</div>

        <div class="section-title">
            {{ $esContratoDeHonorarios ? 'Información Del Prestador de Servicios' : 'Información Laboral' }}
        </div>
        
        <table class="info-table">
            <tr>
                <td>Fecha de ingreso:</td>
                <td class="text-right">{{ \Carbon\Carbon::parse($empleado->fecha_ingreso)->format('d/m/Y') }}</td>
            </tr>
            <tr>
                <td>{{ $esContratoDeHonorarios ? 'Honorarios diarios equivalentes:' : 'Salario por día:' }}</td>
                <td class="text-right">${{ number_format($salarioDiario ?? 0, 2) }}</td>
            </tr>
            <tr>
                <td>Último día laborado:</td>
                <td class="text-right">{{ $fecha_final_formateada ?? '' }}</td>
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
                @if(($dias_laborados_monto ?? 0) > 0)
                <tr>
                    <td>{{ $esContratoDeHonorarios ? 'Honorarios Devengados' : 'Días Laborados' }} ({{ $dias_laborados_dias ?? 0 }} días)</td>
                    <td class="text-right">${{ number_format($dias_laborados_monto, 2) }}</td>
                </tr>
                @endif
                
                @if(($aguinaldo_monto ?? 0) > 0)
                <tr>
                    <td>{{ $esContratoDeHonorarios ? 'Gratificación Anual (Aguinaldo)' : 'Aguinaldo' }}</td>
                    <td class="text-right">${{ number_format($aguinaldo_monto, 2) }}</td>
                </tr>
                @endif

                @if(($vacaciones_monto ?? 0) > 0)
                <tr>
                    <td>Vacaciones</td>
                    <td class="text-right">${{ number_format($vacaciones_monto, 2) }}</td>
                </tr>
                @endif

                @if(($prima_vacacional_monto ?? 0) > 0)
                <tr>
                    <td>Prima vacacional</td>
                    <td class="text-right">${{ number_format($prima_vacacional_monto, 2) }}</td>
                </tr>
                @endif

                {{-- CONCEPTOS DE LIQUIDACIÓN --}}
                @if(($monto_3_meses ?? 0) > 0)
                <tr>
                    <td>Indemnización Constitucional (3 meses)</td>
                    <td class="text-right">${{ number_format($monto_3_meses, 2) }}</td>
                </tr>
                @endif

                @if(($monto_prima_antiguedad ?? 0) > 0)
                <tr>
                    <td>Prima de antigüedad</td>
                    <td class="text-right">${{ number_format($monto_prima_antiguedad, 2) }}</td>
                </tr>
                @endif

                @if(($gratificacion_monto ?? 0) > 0)
                <tr>
                    <td>Gratificación Especial</td>
                    <td class="text-right">${{ number_format($gratificacion_monto, 2) }}</td>
                </tr>
                @endif

                @if(($caja_ahorro_monto ?? 0) > 0)
                <tr>
                    <td>Fondo de Caja de ahorro</td>
                    <td class="text-right">${{ number_format($caja_ahorro_monto, 2) }}</td>
                </tr>
                @endif

                @if(($prestamo_saldo ?? 0) > 0)
                <tr>
                    <td>Descuento por préstamo / Adeudos</td>
                    <td class="text-right">-${{ number_format($prestamo_saldo, 2) }}</td>
                </tr>
                @endif

                <tr class="total-row">
                    <td>{{ $esContratoDeHonorarios ? 'TOTAL A PAGAR POR HONORARIOS DEVENGADOS' : 'TOTAL A PAGAR AL TRABAJADOR' }}</td>
                    <td class="text-right">${{ number_format($neto_a_pagar ?? 0, 2) }}</td>
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