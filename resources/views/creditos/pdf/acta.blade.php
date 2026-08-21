<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Acta de Instalación</title>
    <style>
        body { font-family: 'Helvetica', 'Arial', sans-serif; font-size: 11pt; color: #333; margin: 0; }
        
        /* Cabecera */
        .header { width: 100%; margin-bottom: 20px; display: table; }
        .logo-cell { display: table-cell; width: 50%; vertical-align: middle; }
        .logo-cell img { max-width: 200px; max-height: 70px; }
        .logo-cell h3 { margin: 0; font-size: 18px; color: #003a70; text-transform: uppercase; }
        .title-cell { display: table-cell; width: 50%; text-align: right; vertical-align: middle; }
        .doc-title { font-size: 18px; font-weight: bold; color: #003a70; text-transform: uppercase; }
        .doc-date { font-size: 10pt; color: #555; margin-top: 5px; }

        /* Información del Cliente */
        .info-box { width: 100%; border: 1px solid #000; padding: 10px; margin-bottom: 20px; border-radius: 4px; }
        .info-row { margin-bottom: 8px; font-size: 10.5pt; }
        .info-label { font-weight: bold; text-transform: uppercase; }

        /* Sección Contabilidad */
        .section-title { background-color: #003a70; color: #fff; padding: 8px; text-align: center; font-weight: bold; font-size: 12pt; text-transform: uppercase; margin-bottom: 15px; }
        
        /* Tablas Financieras a dos columnas */
        .math-container { width: 100%; display: table; }
        .math-col-left { display: table-cell; width: 48%; padding-right: 2%; }
        .math-col-right { display: table-cell; width: 48%; padding-left: 2%; }
        
        .math-table { width: 100%; border-collapse: collapse; }
        .math-table td { padding: 8px 5px; border-bottom: 1px solid #eee; font-size: 10.5pt; }
        .text-right { text-align: right; }
        .text-danger { color: #dc3545; }
        .row-total td { font-weight: bold; font-size: 12pt; border-top: 2px solid #000; border-bottom: none; padding-top: 10px; }
        .highlight-total { color: #003a70; }

        /* Firmas */
        .signatures { width: 100%; margin-top: 80px; text-align: center; page-break-inside: avoid; }
        .sig-box { display: inline-block; width: 45%; vertical-align: top; }
        .sig-line { border-top: 1px solid #000; width: 80%; margin: 0 auto 5px auto; padding-top: 5px; }
        .sig-title { font-weight: bold; font-size: 11pt; text-transform: uppercase; }
        .sig-subtitle { font-size: 9pt; color: #666; margin-top: 3px; }
    </style>
</head>
<body>

    <div class="header">
        <div class="logo-cell">
            @if(isset($logo_base64) && $logo_base64)
                <img src="{{ $logo_base64 }}" alt="Logo">
            @elseif(isset($credito->patron->nombre_comercial))
                <h3>{{ $credito->patron->nombre_comercial }}</h3>
            @else
                <h3>EMPRESA EMISORA</h3>
            @endif
        </div>
        <div class="title-cell">
            <div class="doc-title">Acta de Instalación</div>
            <div class="doc-date">FECHA: {{ \Carbon\Carbon::parse($credito->fecha_aprobacion)->format('d/m/Y') }}</div>
        </div>
    </div>

    <div class="info-box">
        <div class="info-row">
            <span class="info-label">Nombre del Cliente:</span> 
            {{ $credito->cliente->nombre_completo ?? $credito->cliente->nombre }}
        </div>
        <div class="info-row">
            <span class="info-label">Dirección:</span> 
            {{ strtoupper($direccion) }}
        </div>
        <div class="info-row" style="margin-bottom: 0;">
            <span class="info-label">Teléfono:</span> 
            {{ $telefono }}
        </div>
    </div>

    <div class="section-title">Contabilidad del Crédito</div>

    <div class="math-container">
        <!-- Columna Izquierda: Desgloses y Retenciones -->
        <div class="math-col-left">
            <table class="math-table">
                <tr>
                    <td>Pagos Pendientes por Realizar</td>
                    <td class="text-right">$0.00</td>
                </tr>
                <tr>
                    <td>Pago Adelantado 1</td>
                    <td class="text-right">$0.00</td>
                </tr>
                <tr>
                    <td>Multas y/o Moratorios Generados</td>
                    <td class="text-right">$0.00</td>
                </tr>
                <tr>
                    <td>Comisión del Nuevo Crédito</td>
                    <td class="text-right text-danger">${{ number_format($comision, 2) }}</td>
                </tr>
                @if($retencion_seguro > 0)
                <tr>
                    <td>Retención de Seguro</td>
                    <td class="text-right text-danger">${{ number_format($retencion_seguro, 2) }}</td>
                </tr>
                @endif
                <tr class="row-total">
                    <td>TOTAL:</td>
                    <td class="text-right text-danger">${{ number_format($total_deducciones, 2) }}</td>
                </tr>
            </table>
        </div>

        <!-- Columna Derecha: Fondeo Neto -->
        <div class="math-col-right">
            <table class="math-table">
                <tr>
                    <td>Monto del Crédito</td>
                    <td class="text-right fw-bold">${{ number_format($monto_credito, 2) }}</td>
                </tr>
                <tr>
                    <td>Deducciones</td>
                    <td class="text-right text-danger">-${{ number_format($total_deducciones, 2) }}</td>
                </tr>
                <tr>
                    <td>Devolución Comisión</td>
                    <td class="text-right">$0.00</td>
                </tr>
                <tr>
                    <td style="border: none;">&nbsp;</td>
                    <td style="border: none;">&nbsp;</td>
                </tr>
                @if($retencion_seguro > 0)
                <tr>
                    <td style="border: none;">&nbsp;</td>
                    <td style="border: none;">&nbsp;</td>
                </tr>
                @endif
                <tr class="row-total">
                    <td class="highlight-total">TOTAL A FONDEAR:</td>
                    <td class="text-right highlight-total">${{ number_format($total_fondear, 2) }}</td>
                </tr>
            </table>
        </div>
    </div>

    <div class="signatures">
        <div class="sig-box">
            <div class="sig-line"></div>
            <div class="sig-title">CLIENTE</div>
            <div class="sig-subtitle">Firma de Conformidad</div>
        </div>
        <div class="sig-box">
            <div class="sig-line"></div>
            <div class="sig-title">GERENTE</div>
            <div class="sig-subtitle">Autorizado</div>
        </div>
    </div>

</body>
</html>