<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Acuse de Desembolso</title>
    <style>
        /* Forzar hoja tamaño carta en formato Horizontal */
        @page { size: letter landscape; margin: 1.5cm 2.5cm; }
        
        body { font-family: 'Helvetica', 'Arial', sans-serif; font-size: 11pt; color: #2d3748; margin: 0; line-height: 1.5; }
        
        /* Cabecera (Logo Izquierda, Fecha Derecha) */
        .header-table { width: 100%; margin-bottom: 20px; border-collapse: collapse; }
        .logo-cell { width: 50%; vertical-align: top; }
        .logo-cell img { max-width: 180px; max-height: 60px; }
        .logo-cell h3 { margin: 0; font-size: 16px; color: #003a70; text-transform: uppercase; }
        .date-cell { width: 50%; vertical-align: top; text-align: right; font-size: 10pt; font-weight: bold; color: #4a5568; text-transform: uppercase; }
        
        /* Título Central */
        .doc-title { text-align: center; font-size: 18pt; font-weight: bold; color: #003a70; text-transform: uppercase; margin-bottom: 5px; letter-spacing: 2px; }
        .doc-subtitle { text-align: center; font-size: 14pt; font-weight: bold; color: #1a202c; letter-spacing: 1px; margin-bottom: 25px; }

        /* Información General (Alineada como el original) */
        .info-section { margin-bottom: 20px; font-size: 11.5pt; }
        .info-row { margin-bottom: 6px; }
        .fw-bold { font-weight: bold; color: #1a202c; }

        /* Caja de Advertencias Ejecutiva */
        .warning-box { border: 2px solid #cbd5e0; border-radius: 4px; padding: 20px; background-color: #f8fafc; margin-bottom: 35px; }
        .warning-text { font-size: 12pt; font-weight: bold; margin-bottom: 15px; color: #003a70; }
        
        .rules-list { margin: 0 0 20px 0; padding-left: 25px; font-size: 11pt; }
        .rules-list li { margin-bottom: 10px; }
        
        .note-box { font-size: 10.5pt; font-weight: bold; font-style: italic; color: #4a5568; text-align: justify; }

        /* Firma (Centrada Abajo, con más espacio) */
        .signatures { width: 100%; margin-top: 50px; text-align: center; page-break-inside: avoid; }
        .sig-box { display: inline-block; width: 45%; vertical-align: top; }
        .sig-line { border-top: 1px solid #1a202c; width: 85%; margin: 0 auto 5px auto; padding-top: 5px; }
        .sig-title { font-weight: bold; font-size: 11pt; text-transform: uppercase; color: #1a202c; letter-spacing: 1px; }
    </style>
</head>
<body>

    @php
        $empresa = $credito->patron->nombre_comercial ?? 'LA EMPRESA';
    @endphp

    <table class="header-table">
        <tr>
            <td class="logo-cell">
                @if(isset($logo_base64) && $logo_base64)
                    <img src="{{ $logo_base64 }}" alt="Logo">
                @else
                    <h3>{{ $empresa }}</h3>
                @endif
            </td>
            <td class="date-cell">
                {{ $credito->sucursal->nombre_sucursal ?? 'TEXCOCO DE MORA' }} a {{ \Carbon\Carbon::parse($credito->fecha_aprobacion)->format('d/m/Y') }}
            </td>
        </tr>
    </table>

    <div class="doc-title">Acuse de Desembolso</div>
    <div class="doc-subtitle">¡IMPORTANTE!</div>

    <div class="info-section">
        <div class="info-row">
            <span class="fw-bold">Grupo / Crédito:</span> {{ $credito->nombre_credito ?? ($credito->grupo->nombre_grupo ?? 'Individual') }}
        </div>
        <div class="info-row">
            <span class="fw-bold">Asesor:</span> {{ $credito->asesor->nombre_completo ?? 'SIN ASIGNAR' }}
        </div>
    </div>

    <div class="warning-box">
        <div class="warning-text">Notificamos que el personal de {{ strtoupper($empresa) }}:</div>
        
        <ol class="rules-list">
            <li>NO recibe efectivo sin comprobante de pago emitido por sucursal.</li>
            <li>NO recibe productos como garantía para el cierre de fichas.</li>
            <li>NO cobra el trámite, es gratuito.</li>
            <li>NO recibe estímulos por agilidad de trámite.</li>
            <li>NO solicita transferencias o depósitos en cuentas distintas a la proporcionada en el desembolso.</li>
        </ol>

        <div class="note-box">
            NOTA: En caso de incurrir en los puntos antes mencionados, {{ strtoupper($empresa) }}, NO se hace responsable por algún reclamo.
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