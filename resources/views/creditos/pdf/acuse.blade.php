<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Acuse de Desembolso</title>
    <style>
        body { font-family: 'Helvetica', 'Arial', sans-serif; font-size: 11pt; color: #333; margin: 0; line-height: 1.5; }
        
        /* Cabecera */
        .header { width: 100%; margin-bottom: 30px; display: table; border-bottom: 2px solid #003a70; padding-bottom: 15px; }
        .logo-cell { display: table-cell; width: 50%; vertical-align: middle; }
        .logo-cell img { max-width: 220px; max-height: 75px; }
        .logo-cell h3 { margin: 0; font-size: 18px; color: #003a70; text-transform: uppercase; }
        .title-cell { display: table-cell; width: 50%; text-align: right; vertical-align: middle; }
        .doc-title { font-size: 20px; font-weight: bold; color: #003a70; text-transform: uppercase; letter-spacing: 1px; }
        .doc-date { font-size: 10pt; color: #555; margin-top: 8px; }

        /* Información General */
        .info-section { margin-bottom: 25px; font-size: 12pt; }
        .info-row { margin-bottom: 5px; }
        .fw-bold { font-weight: bold; }

        /* Caja de Advertencias */
        .warning-box { border: 2px solid #dc3545; border-radius: 8px; padding: 20px; background-color: #fff9fa; margin-bottom: 30px; }
        .warning-title { color: #dc3545; font-size: 16pt; font-weight: bold; text-align: center; margin-bottom: 15px; text-transform: uppercase; letter-spacing: 2px; }
        .warning-text { font-size: 12pt; margin-bottom: 15px; }
        .rules-list { margin: 0 0 20px 0; padding-left: 20px; font-size: 11.5pt; }
        .rules-list li { margin-bottom: 10px; }
        .note-box { background-color: #dc3545; color: #fff; padding: 10px; border-radius: 4px; font-size: 10pt; text-align: justify; font-weight: bold; }

        /* Firma */
        .signatures { width: 100%; margin-top: 80px; text-align: center; page-break-inside: avoid; }
        .sig-box { display: inline-block; width: 60%; vertical-align: top; }
        .sig-line { border-top: 1px solid #000; width: 100%; margin: 0 auto 8px auto; padding-top: 5px; }
        .sig-title { font-weight: bold; font-size: 12pt; text-transform: uppercase; }
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
            <div class="doc-title">Acuse de Desembolso</div>
            <div class="doc-date">{{ strtoupper($credito->sucursal->nombre_sucursal ?? 'TEXCOCO') }} a {{ \Carbon\Carbon::parse($credito->fecha_aprobacion)->format('d/m/Y') }}</div>
        </div>
    </div>

    <div class="info-section">
        <div class="info-row">
            <span class="fw-bold">Grupo / Crédito:</span> {{ $credito->nombre_credito ?? ($credito->grupo->nombre_grupo ?? 'Individual') }}
        </div>
        <div class="info-row">
            <span class="fw-bold">Asesor:</span> {{ $credito->asesor->nombre_completo ?? 'N/A' }}
        </div>
    </div>

    <div class="warning-box">
        <div class="warning-title">¡IMPORTANTE!</div>
        
        <div class="warning-text fw-bold">Notificamos que el personal de la empresa:</div>
        
        <ol class="rules-list">
            <li>NO recibe efectivo sin comprobante de pago emitido por sucursal.</li>
            <li>NO recibe productos como garantía para el cierre de fichas.</li>
            <li>NO cobra el trámite, es gratuito.</li>
            <li>NO recibe estímulos por agilidad de trámite.</li>
            <li>NO solicita transferencias o depósitos en cuentas distintas a la proporcionada en el desembolso.</li>
        </ol>

        <div class="note-box">
            NOTA: En caso de incurrir en los puntos antes mencionados, LA EMPRESA, NO se hace responsable por algún reclamo.
        </div>
    </div>

    <div class="signatures">
        <div class="sig-box">
            <div class="sig-line"></div>
            <div class="sig-title">{{ $credito->cliente->nombre_completo ?? $credito->cliente->nombre }}</div>
        </div>
    </div>

</body>
</html>