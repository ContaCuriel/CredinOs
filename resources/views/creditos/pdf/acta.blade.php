<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Acta de Instalación</title>
    <style>
        /* 🔥 Forzar hoja tamaño carta en formato Horizontal (Landscape) */
        @page { size: letter landscape; margin: 1.5cm 2cm; }
        
        body { font-family: 'Helvetica', 'Arial', sans-serif; font-size: 12pt; color: #1a202c; margin: 0; line-height: 1.4; }
        
        /* Layout Superior */
        .top-table { width: 100%; margin-bottom: 20px; border-collapse: collapse; }
        .top-table td { vertical-align: top; }
        
        /* Títulos */
        .main-title { font-size: 20pt; font-weight: bold; color: #003a70; text-align: center; text-transform: uppercase; margin-bottom: 5px; letter-spacing: 2px; }
        .sub-title { font-size: 14pt; font-weight: bold; background-color: #003a70; color: #fff; text-align: center; padding: 6px 0; text-transform: uppercase; margin-bottom: 30px; border-radius: 4px; }
        
        /* Información del Cliente */
        .info-label { font-weight: bold; color: #4a5568; font-size: 11pt; text-transform: uppercase; }
        .info-value { font-weight: bold; color: #1a202c; font-size: 12pt; margin-bottom: 12px; border-bottom: 1px solid #e2e8f0; padding-bottom: 3px; }
        
        /* Logo y Fecha */
        .logo-box { text-align: right; }
        .logo-box img { max-width: 200px; max-height: 80px; }
        .logo-box h3 { margin: 0; font-size: 18px; color: #003a70; text-transform: uppercase; }
        .date-text { font-size: 12pt; font-weight: bold; color: #2d3748; margin-top: 10px; }

        /* Contabilidad (Tablas divididas) */
        .math-wrapper { width: 100%; border-collapse: collapse; margin-bottom: 40px; }
        .math-wrapper td.col-spacer { width: 6%; } /* Espacio entre columnas */
        
        .math-table { width: 100%; border-collapse: collapse; border: 2px solid #cbd5e0; }
        .math-table td { padding: 12px 10px; border-bottom: 1px solid #e2e8f0; font-size: 11.5pt; }
        .math-table tr:nth-child(even) { background-color: #f8fafc; }
        
        .text-right { text-align: right; }
        .text-danger { color: #e53e3e; }
        .fw-bold { font-weight: bold; }
        
        .row-total td { font-weight: bold; font-size: 14pt; background-color: #edf2f7; border-top: 2px solid #a0aec0; }
        .highlight-total { color: #003a70; font-size: 16pt !important; }

        /* Firmas */
        .signatures { width: 100%; margin-top: 50px; border-collapse: collapse; }
        .signatures td { text-align: center; vertical-align: bottom; height: 60px; width: 50%; }
        .sig-line { border-top: 1px solid #1a202c; width: 60%; margin: 0 auto; padding-top: 5px; font-weight: bold; font-size: 12pt; text-transform: uppercase; }
    </style>
</head>
<body>

    <!-- SECCIÓN SUPERIOR: Info del Cliente + Logo -->
    <table class="top-table">
        <tr>
            <td width="60%">
                <div class="info-label">NOMBRE DEL CLIENTE:</div>
                <div class="info-value">{{ $credito->cliente->nombre_completo ?? $credito->cliente->nombre }}</div>
                
                <div class="info-label">DIRECCIÓN:</div>
                <div class="info-value">{{ strtoupper($direccion) }}</div>
                
                <div class="info-label">TELÉFONO:</div>
                <div class="info-value" style="border: none;">{{ $telefono }}</div>
            </td>
            <td width="40%" class="logo-box">
                @if(isset($logo_base64) && $logo_base64)
                    <img src="{{ $logo_base64 }}" alt="Logo">
                @elseif(isset($credito->patron->nombre_comercial))
                    <h3>{{ $credito->patron->nombre_comercial }}</h3>
                @else
                    <h3>EMPRESA EMISORA</h3>
                @endif
                
                <div class="date-text">FECHA: {{ \Carbon\Carbon::parse($credito->fecha_aprobacion)->format('d/m/Y') }}</div>
            </td>
        </tr>
    </table>

    <!-- TÍTULOS CENTRALES -->
    <div class="main-title">ACTA DE INSTALACIÓN</div>
    <div class="sub-title">CONTABILIDAD DEL CRÉDITO</div>

    <!-- SECCIÓN MATEMÁTICA: 2 Columnas Inteligentes -->
    <table class="math-wrapper">
        <tr>
            <!-- Columna Izquierda (Retenciones) -->
            <td width="47%" valign="top">
                <table class="math-table">
                    @if($comision > 0)
                    <tr>
                        <td>Comisión del Nuevo Crédito</td>
                        <td class="text-right fw-bold text-danger">${{ number_format($comision, 2) }}</td>
                    </tr>
                    @endif
                    
                    @if($retencion_seguro > 0)
                    <tr>
                        <td>Retención de Seguro</td>
                        <td class="text-right fw-bold text-danger">${{ number_format($retencion_seguro, 2) }}</td>
                    </tr>
                    @endif

                    @if($comision == 0 && $retencion_seguro == 0)
                    <tr>
                        <td class="text-muted fst-italic">Sin deducciones aplicadas</td>
                        <td class="text-right fw-bold">$0.00</td>
                    </tr>
                    @endif

                    <tr class="row-total">
                        <td>TOTAL DEDUCCIONES:</td>
                        <td class="text-right text-danger">${{ number_format($total_deducciones, 2) }}</td>
                    </tr>
                </table>
            </td>
            
            <td class="col-spacer"></td>
            
            <!-- Columna Derecha (Fondeo Final) -->
            <td width="47%" valign="top">
                <table class="math-table">
                    <tr>
                        <td>Monto del Crédito Autorizado</td>
                        <td class="text-right fw-bold">${{ number_format($monto_credito, 2) }}</td>
                    </tr>
                    
                    @if($total_deducciones > 0)
                    <tr>
                        <td>Menos Deducciones</td>
                        <td class="text-right fw-bold text-danger">-${{ number_format($total_deducciones, 2) }}</td>
                    </tr>
                    @endif
                    
                    <tr class="row-total">
                        <td class="highlight-total">TOTAL A FONDEAR:</td>
                        <td class="text-right highlight-total fw-bold">${{ number_format($total_fondear, 2) }}</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    <!-- FIRMAS -->
    <table class="signatures">
        <tr>
            <td>
                <div class="sig-line">FIRMA CLIENTE</div>
            </td>
            <td>
                <div class="sig-line">AUTORIZADO FIRMA GERENTE</div>
            </td>
        </tr>
    </table>

</body>
</html>