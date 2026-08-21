<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Control de Pagos</title>
    <style>
        body { font-family: 'Helvetica', 'Arial', sans-serif; font-size: 11px; color: #333; line-height: 1.4; margin: 0; padding: 0; }
        .container { width: 100%; margin: 0 auto; }
        
        /* Cabecera con Logo */
        .header { width: 100%; height: 80px; margin-bottom: 15px; border-bottom: 2px solid #003a70; padding-bottom: 10px; display: table; }
        .logo-cell { display: table-cell; width: 50%; vertical-align: middle; }
        .logo-cell img { max-width: 220px; max-height: 75px; }
        .logo-cell h3 { margin: 0; font-size: 18px; color: #003a70; text-transform: uppercase; }
        
        .title-cell { display: table-cell; width: 50%; vertical-align: middle; text-align: right; }
        .doc-title { font-size: 16px; font-weight: bold; color: #333; text-transform: uppercase; letter-spacing: 1px; }
        .doc-date { font-size: 10px; color: #666; margin-top: 5px; }

        /* Títulos de sección */
        .section-title { background-color: #f2f2f2; padding: 6px; text-align: center; font-weight: bold; font-size: 12px; border: 1px solid #ddd; text-transform: uppercase; margin-bottom: 10px; color: #003a70; }

        /* Tabla de Información General */
        .info-table { width: 100%; border-collapse: collapse; margin-bottom: 25px; }
        .info-table td { padding: 5px 8px; border-bottom: 1px solid #f0f0f0; }
        .lbl { font-weight: bold; color: #555; width: 20%; font-size: 10px; text-transform: uppercase; }
        .val { color: #222; font-size: 11px; width: 30%; }
        .text-right { text-align: right; }
        .val-highlight { font-weight: bold; color: #003a70; }

        /* Tabla de Cuotas (Centrada, más compacta) */
        .cuotas-table { width: 75%; margin: 0 auto 30px auto; border-collapse: collapse; }
        .cuotas-table th { background-color: #f9f9f9; color: #333; font-size: 10px; text-transform: uppercase; padding: 8px; border: 1px solid #ddd; text-align: center; }
        .cuotas-table td { padding: 7px; border: 1px solid #ddd; text-align: center; font-size: 11px; }
        .cuotas-table tr:nth-child(even) { background-color: #fafafa; }

        /* Firma */
        .signature-section { margin-top: 50px; text-align: center; page-break-inside: avoid; }
        .signature-line { border-top: 1px solid #333; width: 280px; margin: 0 auto; padding-top: 8px; }
        .firma-name { font-weight: bold; font-size: 11px; text-transform: uppercase; margin-bottom: 3px; }
        .slogan { font-size: 9px; color: #888; font-style: italic; margin-top: 5px; }
    </style>
</head>
<body>
    <div class="container">
        
        {{-- ENCABEZADO --}}
        <div class="header">
            <div class="logo-cell">
                {{-- Si hay logo en Base64, lo pinta. Si no, usa el Nombre Comercial. --}}
                @if(isset($logo_base64) && $logo_base64)
                    <img src="{{ $logo_base64 }}" alt="Logo">
                @elseif(isset($credito->patron->nombre_comercial))
                    <h3>{{ $credito->patron->nombre_comercial }}</h3>
                @else
                    <h3>EMPRESA EMISORA</h3>
                @endif
            </div>
            <div class="title-cell">
                <div class="doc-title">Control de Pagos</div>
                <div class="doc-date">{{ strtoupper($credito->sucursal->nombre_sucursal ?? 'TEXCOCO') }}, {{ now()->format('d/m/Y') }}</div>
            </div>
        </div>

        {{-- INFORMACIÓN DEL CRÉDITO --}}
        <div class="section-title">Información del Crédito</div>
        
        <table class="info-table">
            <tr>
                <td class="lbl">Titular:</td>
                <td class="val fw-bold">{{ $credito->cliente->nombre_completo ?? $credito->cliente->nombre }}</td>
                <td class="lbl text-right">Folio:</td>
                <td class="val text-right val-highlight">{{ $credito->folio }}</td>
            </tr>
            <tr>
                <td class="lbl">Crédito / Grupo:</td>
                <td class="val">{{ $credito->nombre_credito ?? ($credito->grupo->nombre_grupo ?? 'Individual') }}</td>
                <td class="lbl text-right">Monto Autorizado:</td>
                <td class="val text-right">${{ number_format($credito->monto_aprobado, 2) }}</td>
            </tr>
            <tr>
                <td class="lbl">Frecuencia:</td>
                <td class="val">{{ ucfirst($credito->producto->frecuencia_pago) }}</td>
                <td class="lbl text-right">Cuota Fija:</td>
                <td class="val text-right val-highlight">${{ number_format($monto_pago, 2) }}</td>
            </tr>
            <tr>
                <td class="lbl">Periodo:</td>
                <td class="val">{{ \Carbon\Carbon::parse($credito->fecha_primer_pago)->format('d/m/Y') }} al {{ \Carbon\Carbon::parse($fecha_fin)->format('d/m/Y') }}</td>
                <td class="lbl text-right">Asesor:</td>
                <td class="val text-right">{{ $credito->asesor->nombre_completo ?? 'N/A' }}</td>
            </tr>
        </table>

        {{-- TABLA DE PAGOS --}}
        <div class="section-title">Calendario de Pagos</div>

        <table class="cuotas-table">
            <thead>
                <tr>
                    <th width="20%">NO. PAGO</th>
                    <th width="45%">FECHA PROGRAMADA</th>
                    <th width="35%">MONTO A PAGAR</th>
                </tr>
            </thead>
            <tbody>
                @foreach($credito->amortizaciones as $cuota)
                <tr>
                    <td class="fw-bold">{{ $cuota->numero_cuota }}</td>
                    {{-- Fechas centradas --}}
                    <td>{{ ucwords(\Carbon\Carbon::parse($cuota->fecha_pago)->locale('es')->isoFormat('DD \d\e MMMM \d\e YYYY')) }}</td>
                    <td class="val-highlight">${{ number_format($cuota->total_cuota, 2) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        {{-- FIRMA --}}
        <div class="signature-section">
            <div class="signature-line"></div>
            <div class="firma-name">{{ $credito->cliente->nombre_completo ?? $credito->cliente->nombre }}</div>
            <div>Firma del Cliente / De Conformidad</div>
            <div class="slogan">Tu crédito de la mano</div>
        </div>

    </div>
</body>
</html>