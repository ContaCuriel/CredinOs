<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Control de Pagos</title>
    <style>
        body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 10pt; color: #2d3748; margin: 0; }
        
        /* Cabecera */
        .header { border-bottom: 3px solid #003a70; padding-bottom: 10px; margin-bottom: 20px; width: 100%; display: table; }
        .logo-box { display: table-cell; width: 50%; vertical-align: middle; font-size: 22px; font-weight: 800; color: #003a70; letter-spacing: 1px; }
        .title-box { display: table-cell; width: 50%; text-align: right; vertical-align: middle; }
        .doc-title { font-size: 18px; font-weight: 700; color: #4a5568; text-transform: uppercase; letter-spacing: 1px; }
        .date-text { font-size: 9pt; color: #718096; margin-top: 5px; }

        /* Cajas de Resumen */
        .summary-wrapper { width: 100%; border-collapse: collapse; margin-bottom: 25px; }
        .summary-box { background-color: #f7fafc; border: 1px solid #e2e8f0; border-radius: 6px; padding: 12px; vertical-align: top; width: 50%; }
        .box-title { font-size: 8.5pt; font-weight: bold; color: #003a70; text-transform: uppercase; border-bottom: 1px solid #cbd5e0; padding-bottom: 4px; margin-bottom: 8px; }
        .info-table { width: 100%; border-collapse: collapse; }
        .info-table td { padding: 4px 0; font-size: 9pt; }
        .lbl { font-weight: 600; color: #718096; width: 45%; }
        .val { font-weight: 700; color: #1a202c; text-align: right; }
        .val-highlight { color: #003a70; font-size: 11pt; }

        /* Tabla Principal */
        .data-table { width: 100%; border-collapse: collapse; margin-bottom: 40px; }
        .data-table th { background-color: #003a70; color: #ffffff; padding: 10px; font-size: 8.5pt; text-transform: uppercase; letter-spacing: 0.5px; border: 1px solid #003a70; }
        .data-table td { padding: 8px 10px; font-size: 9.5pt; border: 1px solid #e2e8f0; color: #2d3748; text-align: center; }
        .data-table tr:nth-child(even) { background-color: #f8fafc; }
        .text-left { text-align: left !important; }
        .text-right { text-align: right !important; }
        .fw-bold { font-weight: 700; }
        
        /* Footer */
        .footer { width: 100%; text-align: center; margin-top: 40px; page-break-inside: avoid; }
        .linea-firma { width: 250px; border-top: 1px solid #4a5568; margin: 0 auto 8px auto; }
        .firma-text { font-size: 9pt; font-weight: bold; color: #4a5568; text-transform: uppercase; }
        .slogan { font-size: 8.5pt; color: #a0aec0; margin-top: 4px; font-style: italic; }
    </style>
</head>
<body>

    <div class="header">
        <div class="logo-box">
            @if($credito->patron && $credito->patron->logo)
                <img src="{{ public_path('storage/' . $credito->patron->logo) }}" height="40" alt="Logo">
            @else
                {{ strtoupper($credito->patron->nombre_comercial ?? 'EMPRESA EMISORA') }}
            @endif
        </div>
        <div class="title-box">
            <div class="doc-title">Control de Pagos</div>
            <div class="date-text">{{ strtoupper($credito->sucursal->nombre_sucursal ?? 'TEXCOCO') }} a {{ now()->format('d/m/Y') }}</div>
        </div>
    </div>

    <table class="summary-wrapper">
        <tr>
            <td class="summary-box" style="border-right: 8px solid #fff;">
                <div class="box-title">Datos del Cliente</div>
                <table class="info-table">
                    <tr>
                        <td class="lbl">Titular:</td>
                        <td class="val">{{ $credito->cliente->nombre_completo ?? $credito->cliente->nombre }}</td>
                    </tr>
                    <tr>
                        <td class="lbl">Crédito / Grupo:</td>
                        <td class="val">{{ $credito->nombre_credito ?? ($credito->grupo->nombre_grupo ?? 'Individual') }}</td>
                    </tr>
                    <tr>
                        <td class="lbl">Folio:</td>
                        <td class="val">{{ $credito->folio }}</td>
                    </tr>
                    <tr>
                        <td class="lbl">Asesor:</td>
                        <td class="val">{{ $credito->asesor->nombre_completo ?? 'N/A' }}</td>
                    </tr>
                </table>
            </td>
            <td class="summary-box">
                <div class="box-title">Resumen Financiero</div>
                <table class="info-table">
                    <tr>
                        <td class="lbl">Monto Desembolsado:</td>
                        <td class="val">${{ number_format($credito->monto_aprobado, 2) }}</td>
                    </tr>
                    <tr>
                        <td class="lbl">Plazo y Frecuencia:</td>
                        <td class="val">{{ $credito->plazo_aprobado }} pagos {{ ucfirst($credito->producto->frecuencia_pago) }}s</td>
                    </tr>
                    <tr>
                        <td class="lbl">Periodo:</td>
                        <td class="val">{{ \Carbon\Carbon::parse($credito->fecha_primer_pago)->format('d/m/Y') }} - {{ \Carbon\Carbon::parse($fecha_fin)->format('d/m/Y') }}</td>
                    </tr>
                    <tr>
                        <td class="lbl">Cuota Fija a Pagar:</td>
                        <td class="val val-highlight">${{ number_format($monto_pago, 2) }}</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    <table class="data-table">
        <thead>
            <tr>
                <th width="8%">Pago</th>
                <th width="32%" class="text-left">Fecha Programada</th>
                <th width="20%">Capital</th>
                <th width="20%">Interés</th>
                <th width="20%" class="text-right">Total a Pagar</th>
            </tr>
        </thead>
        <tbody>
            @foreach($credito->amortizaciones as $cuota)
            <tr>
                <td class="fw-bold text-muted">{{ $cuota->numero_cuota }}</td>
                <td class="text-left fw-bold">{{ ucwords(\Carbon\Carbon::parse($cuota->fecha_pago)->locale('es')->isoFormat('DD MMMM YYYY')) }}</td>
                <td>${{ number_format($cuota->capital, 2) }}</td>
                <td>${{ number_format($cuota->interes + $cuota->iva, 2) }}</td>
                <td class="text-right fw-bold" style="color: #003a70;">${{ number_format($cuota->total_cuota, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        <div class="linea-firma"></div>
        <div class="firma-text">Firma del Cliente de Conformidad</div>
        <div class="slogan">Tu crédito de la mano</div>
    </div>

</body>
</html>