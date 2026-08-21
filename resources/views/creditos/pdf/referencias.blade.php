<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Referencias de Pago</title>
    <style>
        @page { margin: 1.5cm 2cm; }
        body { font-family: 'Helvetica', 'Arial', sans-serif; color: #2d3748; margin: 0; }
        
        .ticket-box { 
            width: 100%; 
            border: 2px solid #cbd5e0; 
            border-radius: 8px; 
            padding: 20px; 
            margin-bottom: 30px; 
            page-break-inside: avoid;
            position: relative;
        }

        /* Línea punteada de recorte (excepto en el último elemento) */
        .ticket-wrapper:not(:last-child) .cut-line {
            border-bottom: 2px dashed #a0aec0;
            margin: 0 auto 30px auto;
            width: 90%;
            position: relative;
        }
        
        .cut-icon {
            position: absolute;
            top: -10px;
            left: 5%;
            font-size: 14px;
            background: #fff;
            padding: 0 5px;
            color: #718096;
        }

        .header-table { width: 100%; margin-bottom: 15px; border-collapse: collapse; border-bottom: 2px solid #003a70; padding-bottom: 10px; }
        .logo-cell { width: 50%; vertical-align: middle; }
        .logo-cell img { max-width: 150px; max-height: 50px; }
        .logo-cell h3 { margin: 0; font-size: 14pt; color: #003a70; text-transform: uppercase; }
        .title-cell { width: 50%; text-align: right; vertical-align: bottom; font-size: 14pt; font-weight: bold; color: #003a70; text-transform: uppercase; }

        .data-table { width: 100%; border-collapse: collapse; }
        .data-table td { padding: 8px 5px; font-size: 11pt; border-bottom: 1px solid #edf2f7; }
        .lbl { font-weight: bold; color: #718096; width: 32%; text-transform: uppercase; font-size: 10pt; }
        .val { font-weight: bold; color: #1a202c; }
        
        .monto-box { 
            background-color: #f8fafc; 
            border: 1px solid #e2e8f0; 
            text-align: center; 
            padding: 10px; 
            margin-top: 15px; 
            border-radius: 4px;
        }
        .monto-lbl { font-size: 10pt; font-weight: bold; color: #4a5568; text-transform: uppercase; margin-bottom: 5px; }
        .monto-val { font-size: 16pt; font-weight: bold; color: #003a70; }

        .no-data { text-align: center; font-size: 12pt; color: #718096; margin-top: 50px; font-style: italic; }
    </style>
</head>
<body>

    @php
        $empresa = $credito->patron->nombre_comercial ?? 'LA EMPRESA';
        // Buscamos si es un crédito grupal o individual para poner el nombre en la referencia
        $nombreCliente = $credito->nombre_credito ?? ($credito->grupo->nombre_grupo ?? ($credito->cliente->nombre_completo ?? $credito->cliente->nombre));
        
        $hayOpciones = $credito->sucursalesParaPago->count() > 0 || $credito->cuentasParaPago->count() > 0;
    @endphp

    @if(!$hayOpciones)
        <div class="no-data">No se asignaron referencias de pago para este crédito.</div>
    @else

        <!-- 1. FICHAS PARA PAGO EN SUCURSALES FÍSICAS -->
        @foreach($credito->sucursalesParaPago as $sucursal)
        <div class="ticket-wrapper">
            <div class="ticket-box">
                <table class="header-table">
                    <tr>
                        <td class="logo-cell">
                            @if(isset($logo_base64) && $logo_base64)
                                <img src="{{ $logo_base64 }}" alt="Logo">
                            @else
                                <h3>{{ $empresa }}</h3>
                            @endif
                        </td>
                        <td class="title-cell">Ficha de Pago</td>
                    </tr>
                </table>

                <table class="data-table">
                    <tr>
                        <td class="lbl">Depósito en:</td>
                        <td class="val">SUCURSAL</td>
                    </tr>
                    <tr>
                        <td class="lbl">Nombre Sucursal:</td>
                        <td class="val">{{ strtoupper($sucursal->nombre_sucursal) }}</td>
                    </tr>
                    <tr>
                        <td class="lbl">Cliente / Grupo:</td>
                        <td class="val">{{ strtoupper($nombreCliente) }}</td>
                    </tr>
                </table>

                <div class="monto-box">
                    <div class="monto-lbl">Monto Fijo a Depositar</div>
                    <div class="monto-val">${{ number_format($cuota_monto, 2) }}</div>
                </div>
            </div>
            <div class="cut-line"><span class="cut-icon">✂</span></div>
        </div>
        @endforeach

        <!-- 2. FICHAS PARA PAGO EN CUENTAS BANCARIAS -->
        @foreach($credito->cuentasParaPago as $cuenta)
        <div class="ticket-wrapper">
            <div class="ticket-box">
                <table class="header-table">
                    <tr>
                        <td class="logo-cell">
                            @if(isset($logo_base64) && $logo_base64)
                                <img src="{{ $logo_base64 }}" alt="Logo">
                            @else
                                <h3>{{ $empresa }}</h3>
                            @endif
                        </td>
                        <td class="title-cell">Ficha de Pago</td>
                    </tr>
                </table>

                <table class="data-table">
                    <tr>
                        <td class="lbl">Banco:</td>
                        <td class="val">{{ strtoupper($cuenta->banco) }}</td>
                    </tr>
                    <tr>
                        <td class="lbl">Beneficiario:</td>
                        <td class="val">{{ strtoupper($cuenta->titular) }}</td>
                    </tr>
                    <tr>
                        <td class="lbl">No. Cuenta / Tarjeta:</td>
                        <td class="val">{{ $cuenta->numero_cuenta }}</td>
                    </tr>
                    @if($cuenta->clabe)
                    <tr>
                        <td class="lbl">CLABE Interbancaria:</td>
                        <td class="val">{{ $cuenta->clabe }}</td>
                    </tr>
                    @endif
                    <tr>
                        <td class="lbl">Referencia / Cliente:</td>
                        <td class="val">{{ strtoupper($nombreCliente) }}</td>
                    </tr>
                </table>

                <div class="monto-box">
                    <div class="monto-lbl">Monto Fijo a Depositar</div>
                    <div class="monto-val">${{ number_format($cuota_monto, 2) }}</div>
                </div>
            </div>
            <div class="cut-line"><span class="cut-icon">✂</span></div>
        </div>
        @endforeach

    @endif

</body>
</html>