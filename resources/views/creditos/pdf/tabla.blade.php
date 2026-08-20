<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Control de Pagos</title>
    <style>
        body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 11px; color: #333; margin: 0; padding: 0; }
        .header-table { width: 100%; border-collapse: collapse; margin-bottom: 25px; }
        .header-table td { vertical-align: middle; }
        .logo-placeholder { font-size: 18px; font-weight: bold; color: #2c3e50; letter-spacing: 1px; }
        .doc-title { text-align: right; font-size: 18px; font-weight: 300; letter-spacing: 2px; color: #555; text-transform: uppercase; }
        
        .info-box { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .info-box td { padding: 4px 8px; vertical-align: top; font-size: 10px; }
        .info-label { color: #888; text-transform: uppercase; font-size: 9px; letter-spacing: 0.5px; }
        .info-value { font-weight: bold; font-size: 11px; color: #222; margin-top: 2px; }
        
        .cuotas-table { width: 100%; border-collapse: collapse; text-align: center; margin-bottom: 30px; }
        .cuotas-table th { background-color: #f8f9fa; color: #555; font-size: 9px; text-transform: uppercase; letter-spacing: 1px; padding: 8px 4px; border-bottom: 1px solid #dee2e6; }
        .cuotas-table td { padding: 6px 4px; border-bottom: 1px solid #f1f3f5; color: #444; }
        .cuotas-table tr:last-child td { border-bottom: 2px solid #dee2e6; }
        
        .fw-bold { font-weight: bold; }
        .text-left { text-align: left; }
        .text-right { text-align: right; }
        
        .footer { position: fixed; bottom: 30px; width: 100%; text-align: center; }
        .linea-firma { width: 250px; border-top: 1px solid #333; margin: 0 auto 8px auto; }
        .firma-text { font-size: 10px; color: #555; letter-spacing: 1px; }
        .slogan { font-size: 9px; color: #999; margin-top: 4px; }
    </style>
</head>
<body>

    <table class="header-table">
        <tr>
            <td width="50%">
                <div class="logo-placeholder">
                    @if($credito->patron && $credito->patron->logo)
                        <img src="{{ public_path('storage/' . $credito->patron->logo) }}" height="45" alt="Logo">
                    @else
                        {{ $credito->patron->nombre_comercial ?? 'EMPRESA EMISORA' }}
                    @endif
                </div>
            </td>
            <td width="50%" class="doc-title">
                Control de Pagos
                <div style="font-size: 10px; font-weight: normal; color: #888; margin-top: 4px; letter-spacing: 0;">
                    {{ strtoupper($credito->sucursal->nombre_sucursal ?? 'TEXCOCO') }} a {{ now()->format('d/m/Y') }}
                </div>
            </td>
        </tr>
    </table>

    <table class="info-box">
        <tr>
            <td width="33%">
                <div class="info-label">Nombre del Crédito / Grupo</div>
                <div class="info-value">{{ $credito->nombre_credito ?? ($credito->grupo->nombre_grupo ?? 'Crédito Individual') }}</div>
            </td>
            <td width="33%">
                <div class="info-label">Cliente</div>
                <div class="info-value">{{ $credito->cliente->nombre_completo ?? $credito->cliente->nombre }}</div>
            </td>
            <td width="33%" class="text-right">
                <div class="info-label">Folio de Crédito</div>
                <div class="info-value text-primary">{{ $credito->folio }}</div>
            </td>
        </tr>
        <tr>
            <td>
                <div class="info-label">Monto Aprobado</div>
                <div class="info-value">${{ number_format($credito->monto_aprobado, 2) }}</div>
            </td>
            <td>
                <div class="info-label">Monto de Pago Fijo</div>
                <div class="info-value text-success">${{ number_format($monto_pago, 2) }}</div>
            </td>
            <td class="text-right">
                <div class="info-label">Frecuencia</div>
                <div class="info-value">{{ ucfirst($credito->producto->frecuencia_pago) }}</div>
            </td>
        </tr>
        <tr>
            <td>
                <div class="info-label">Fecha Desembolso</div>
                <div class="info-value">{{ \Carbon\Carbon::parse($credito->fecha_desembolso)->format('d/m/Y') }}</div>
            </td>
            <td>
                <div class="info-label">Fecha Fin Programada</div>
                <div class="info-value">{{ \Carbon\Carbon::parse($fecha_fin)->format('d/m/Y') }}</div>
            </td>
            <td class="text-right">
                <div class="info-label">Asesor Responsable</div>
                <div class="info-value">{{ $credito->asesor->nombre_completo ?? 'N/A' }}</div>
            </td>
        </tr>
    </table>

    <table class="cuotas-table">
        <thead>
            <tr>
                <th width="10%">Pago</th>
                <th width="35%" class="text-left">Fecha Programada</th>
                <th width="18%">Capital</th>
                <th width="18%">Interés</th>
                <th width="19%" class="text-right">Total a Pagar</th>
            </tr>
        </thead>
        <tbody>
            @foreach($credito->amortizaciones as $cuota)
            <tr>
                <td>{{ $cuota->numero_cuota }}</td>
                <td class="text-left fw-bold">{{ ucwords(\Carbon\Carbon::parse($cuota->fecha_pago)->locale('es')->isoFormat('DD MMMM YYYY')) }}</td>
                <td>${{ number_format($cuota->capital, 2) }}</td>
                <td>${{ number_format($cuota->interes + $cuota->iva, 2) }}</td>
                <td class="text-right fw-bold">${{ number_format($cuota->total_cuota, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        <div class="linea-firma"></div>
        <div class="firma-text">FIRMA DEL CLIENTE / DE CONFORMIDAD</div>
        <div class="slogan">Tu crédito de la mano</div>
    </div>

</body>
</html>