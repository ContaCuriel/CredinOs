<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Acta de Instalación</title>
    <style>
        /* ========================================================
           LÓGICA DE ORIENTACIÓN DINÁMICA (HORIZONTAL VS VERTICAL)
           ======================================================== */
        @if($credito->grupo_id)
            @page { size: letter landscape; margin: 1cm 1.5cm; }
        @else
            @page { size: letter portrait; margin: 1.5cm 2cm; }
        @endif
        
        /* Fuente general */
        body { font-family: 'Helvetica', 'Arial', sans-serif; font-size: 9.5pt; color: #1a202c; margin: 0; line-height: 1.4; }
        
        /* Layout Superior Individual */
        .top-table { width: 100%; margin-bottom: 25px; border-collapse: collapse; }
        .top-table td { vertical-align: top; }
        
        /* Títulos refinados */
        .main-title { font-size: 16pt; font-weight: bold; color: #003a70; text-align: center; text-transform: uppercase; margin-bottom: 4px; letter-spacing: 2px; }
        .sub-title { font-size: 10.5pt; font-weight: bold; background-color: #003a70; color: #fff; text-align: center; padding: 5px 0; text-transform: uppercase; margin-bottom: 35px; border-radius: 3px; letter-spacing: 1px; }
        
        /* Información del Cliente Individual */
        .info-label { font-weight: bold; color: #718096; font-size: 8.5pt; text-transform: uppercase; letter-spacing: 0.5px; }
        .info-value { font-weight: bold; color: #1a202c; font-size: 10pt; margin-bottom: 12px; border-bottom: 1px solid #e2e8f0; padding-bottom: 3px; }
        
        /* Logo y Fecha */
        .logo-box { text-align: right; }
        .logo-box img { max-width: 180px; max-height: 60px; }
        .logo-box h3 { margin: 0; font-size: 14pt; color: #003a70; text-transform: uppercase; }
        .date-text { font-size: 9pt; font-weight: bold; color: #4a5568; margin-top: 10px; letter-spacing: 0.5px; }

        /* Contabilidad */
        .math-wrapper { width: 100%; border-collapse: collapse; margin-bottom: 40px; }
        .math-wrapper td.col-spacer { width: 8%; }
        
        .math-table { width: 100%; border-collapse: collapse; border: 1px solid #cbd5e0; }
        .math-table td { padding: 10px 12px; border-bottom: 1px solid #edf2f7; font-size: 9.5pt; color: #2d3748; }
        .math-table tr:nth-child(even) { background-color: #f8fafc; }
        
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .text-danger { color: #c53030; }
        .fw-bold { font-weight: bold; color: #1a202c; }
        
        .row-total td { font-weight: bold; font-size: 10.5pt; background-color: #edf2f7; border-top: 2px solid #cbd5e0; color: #1a202c; }
        .highlight-total { color: #003a70 !important; font-size: 12.5pt !important; }

        /* Firmas Individual */
        .signatures { width: 100%; margin-top: 60px; border-collapse: collapse; }
        .signatures td { text-align: center; vertical-align: bottom; height: 60px; width: 33.33%; }
        .sig-line { border-top: 1px solid #4a5568; width: 75%; margin: 0 auto; padding-top: 6px; font-weight: bold; font-size: 9pt; text-transform: uppercase; color: #4a5568; letter-spacing: 1px; }

        /* ========================================================
           ESTILOS ESPECÍFICOS PARA GRUPAL (LANDSCAPE)
           ======================================================== */
        .header-grupal { width: 100%; border-collapse: collapse; margin-bottom: 10px; font-size: 9pt; text-transform: uppercase; }
        .header-grupal td { padding: 3px 5px; border-bottom: 1px solid #edf2f7; }
        .g-lbl { font-weight: bold; color: #4a5568; width: 12%; }
        .g-val { color: #1a202c; font-weight: bold; width: 38%; }

        .table-integrantes { width: 100%; border-collapse: collapse; margin-top: 15px; border: 1px solid #cbd5e0; }
        .table-integrantes th { background-color: #003a70; color: #fff; padding: 6px 4px; font-size: 8pt; text-align: center; border: 1px solid #003a70; text-transform: uppercase; }
        .table-integrantes td { border: 1px solid #cbd5e0; padding: 6px 4px; font-size: 8pt; text-align: center; vertical-align: middle; height: 28px; }
        .table-integrantes .text-left { text-align: left; padding-left: 8px; }
        
        .row-totales-grupal td { background-color: #edf2f7; font-weight: bold; font-size: 8.5pt; border-top: 2px solid #cbd5e0; }
        
        /* Firmas Grupal */
        .signatures-grupal { width: 100%; margin-top: 40px; border-collapse: collapse; }
        .signatures-grupal td { text-align: center; vertical-align: bottom; height: 50px; width: 33.33%; }
        .sig-line-g { border-top: 1px solid #000; width: 70%; margin: 0 auto; padding-top: 5px; font-weight: bold; font-size: 9pt; text-transform: uppercase; }
    </style>
</head>
<body>

    @php
        // Extraer líder para la vista
        $lider = null;
        if($credito->integrantes) {
            $lider = $credito->integrantes->where('pivot.es_lider', true)->first();
        }
        $nombreLider = $lider ? mb_strtoupper($lider->nombre_completo ?? $lider->nombre . ' ' . $lider->apellido_paterno) : 'SIN ASIGNAR';
        $telefonoLider = $lider ? ($lider->telefono_celular ?? ($lider->telefono_fijo ?? 'N/A')) : ($credito->cliente->telefono_celular ?? 'N/A');
        
        $direccionLider = '';
        if ($lider) {
            $calle = $lider->calle ?? '';
            $numero = $lider->numero ?? '';
            $colonia = $lider->colonia ?? '';
            $direccionLider = trim("$calle $numero, Col: $colonia", ', ');
        } else {
            $direccionLider = $direccion; // Variable inyectada desde el controlador
        }
    @endphp

    @if($credito->grupo_id)
        {{-- ========================================================================= --}}
        {{-- FORMATO GRUPAL (HOJA 1: INTEGRANTES) --}}
        {{-- ========================================================================= --}}
        
        <table style="width: 100%; margin-bottom: 10px;">
            <tr>
                <td width="70%">
                    <table class="header-grupal">
                        <tr>
                            <td class="g-lbl">NOMBRE DEL GRUPO:</td>
                            <td class="g-val text-primary">{{ mb_strtoupper($credito->grupo->nombre_grupo) }}</td>
                        </tr>
                        <tr>
                            <td class="g-lbl">DIRECCIÓN:</td>
                            <td class="g-val">{{ mb_strtoupper($direccionLider) }}</td>
                        </tr>
                        <tr>
                            <td class="g-lbl">LÍDER:</td>
                            <td class="g-val">{{ $nombreLider }}</td>
                        </tr>
                        <tr>
                            <td class="g-lbl">ASESOR:</td>
                            <td class="g-val">{{ mb_strtoupper($credito->asesor->nombre_completo ?? 'N/A') }}</td>
                        </tr>
                    </table>
                </td>
                <td width="30%" class="logo-box" valign="top">
                    @if(isset($logo_base64) && $logo_base64)
                        <img src="{{ $logo_base64 }}" alt="Logo" style="max-height: 50px;">
                    @else
                        <h3>{{ $credito->patron->nombre_comercial ?? 'EMPRESA EMISORA' }}</h3>
                    @endif
                    <div class="main-title" style="font-size: 14pt; margin-top: 5px;">ACTA DE INSTALACIÓN</div>
                </td>
            </tr>
        </table>

        <table class="header-grupal" style="margin-top: -10px;">
            <tr>
                <td class="g-lbl" style="width: 10%;">SUCURSAL:</td>
                <td class="g-val" style="width: 23%;">{{ strtoupper($credito->sucursal->nombre_sucursal ?? 'TEXCOCO') }}</td>
                <td class="g-lbl" style="width: 10%;">FECHA:</td>
                <td class="g-val" style="width: 23%;">{{ \Carbon\Carbon::parse($credito->fecha_desembolso)->format('d/m/Y') }}</td>
                <td class="g-lbl" style="width: 10%;">TELÉFONO:</td>
                <td class="g-val" style="width: 24%;">{{ $telefonoLider }}</td>
            </tr>
            <tr>
                <td class="g-lbl">HORARIO:</td>
                <td class="g-val"></td>
                <td class="g-lbl">NO. RENOVACIONES:</td>
                <td class="g-val">0</td>
                <td class="g-lbl">NO. CUENTA:</td>
                <td class="g-val">{{ $credito->cuentasDesembolso->first()->numero_cuenta ?? 'EFECTIVO' }}</td>
            </tr>
        </table>

        {{-- Tabla de Integrantes --}}
        <table class="table-integrantes">
            <thead>
                <tr>
                    <th width="3%">NO</th>
                    <th width="26%">NOMBRE CLIENTE</th>
                    <th width="6%">RENOV</th>
                    <th width="11%">MONTO ANTERIOR</th>
                    <th width="12%">MONTO SOLICITADO</th>
                    <th width="11%">PAGO FALTAS DE</th>
                    <th width="10%">TELÉFONO</th>
                    <th width="12%">MONTO AUTORIZADO</th>
                    <th width="9%">FIRMA</th>
                </tr>
            </thead>
            <tbody>
                @foreach($credito->integrantes as $index => $integrante)
                <tr>
                    <td class="fw-bold">{{ $index + 1 }}</td>
                    <td class="text-left fw-bold">{{ mb_strtoupper($integrante->nombre_completo ?? $integrante->nombre . ' ' . $integrante->apellido_paterno) }}</td>
                    <td>0.00</td>
                    <td>0.00</td>
                    <td>{{ number_format($integrante->pivot->monto_individual, 2) }}</td>
                    <td>0.00</td>
                    <td>{{ $integrante->telefono_celular ?? 'N/A' }}</td>
                    <td class="fw-bold">${{ number_format($integrante->pivot->monto_individual, 2) }}</td>
                    <td></td>
                </tr>
                @endforeach
                
                {{-- Fila de Totales --}}
                <tr class="row-totales-grupal">
                    <td colspan="3" class="text-right" style="padding-right: 10px;">MONTO TOTAL DEL CRÉDITO</td>
                    <td>$0.00</td>
                    <td>${{ number_format($credito->monto_aprobado, 2) }}</td>
                    <td></td>
                    <td></td>
                    <td>${{ number_format($credito->monto_aprobado, 2) }}</td>
                    <td></td>
                </tr>
            </tbody>
        </table>

        <table class="signatures-grupal">
            <tr>
                <td><div class="sig-line-g">NOMBRE Y FIRMA<br>LÍDER</div></td>
                <td><div class="sig-line-g">NOMBRE Y FIRMA<br>GERENTE</div></td>
                <td><div class="sig-line-g">NOMBRE Y FIRMA<br>ASESOR</div></td>
            </tr>
        </table>

        {{-- SALTO DE PÁGINA PARA CONTABILIDAD (REVERSO) --}}
        <div style="page-break-before: always;"></div>

        {{-- ========================================================================= --}}
        {{-- FORMATO GRUPAL (HOJA 2: CONTABILIDAD) --}}
        {{-- ========================================================================= --}}
        <div class="logo-box" style="text-align: center; margin-bottom: 20px;">
            @if(isset($logo_base64) && $logo_base64)
                <img src="{{ $logo_base64 }}" alt="Logo">
            @else
                <h3>{{ $credito->patron->nombre_comercial ?? 'EMPRESA EMISORA' }}</h3>
            @endif
        </div>

        <div class="main-title">CONTABILIDAD DEL CRÉDITO</div>

        <table class="math-wrapper" style="width: 80%; margin: 0 auto;">
            <tr>
                <td width="48%" valign="top">
                    <table class="math-table">
                        <tr>
                            <td>Pagos Pendientes por Realizar</td>
                            <td class="text-right fw-bold text-danger">$0.00</td>
                        </tr>
                        @if(isset($pago_adelantado) && $pago_adelantado > 0)
                        <tr>
                            <td>Pago Adelantado 1</td>
                            <td class="text-right fw-bold text-danger">${{ number_format($pago_adelantado, 2) }}</td>
                        </tr>
                        @else
                        <tr>
                            <td>Pago Adelantado 1</td>
                            <td class="text-right fw-bold text-danger">$0.00</td>
                        </tr>
                        @endif
                        <tr>
                            <td>Multas y/o Moratorios Generados</td>
                            <td class="text-right fw-bold text-danger">$0.00</td>
                        </tr>
                        <tr>
                            <td>Comisión del Nuevo Crédito</td>
                            <td class="text-right fw-bold text-danger">${{ number_format($comision, 2) }}</td>
                        </tr>
                        @if($retencion_seguro > 0)
                        <tr>
                            <td>Retención de Seguro</td>
                            <td class="text-right fw-bold text-danger">${{ number_format($retencion_seguro, 2) }}</td>
                        </tr>
                        @endif
                        <tr class="row-total">
                            <td>TOTAL:</td>
                            <td class="text-right text-danger">${{ number_format($total_deducciones, 2) }}</td>
                        </tr>
                    </table>
                </td>
                <td class="col-spacer"></td>
                <td width="48%" valign="top">
                    <table class="math-table">
                        <tr>
                            <td>Monto del Crédito</td>
                            <td class="text-right fw-bold">${{ number_format($monto_credito, 2) }}</td>
                        </tr>
                        <tr>
                            <td>Deducciones</td>
                            <td class="text-right fw-bold text-danger">${{ number_format($total_deducciones, 2) }}</td>
                        </tr>
                        <tr>
                            <td>Devolución Comisión</td>
                            <td class="text-right fw-bold">$0.00</td>
                        </tr>
                        <tr class="row-total">
                            <td class="highlight-total fw-bold">Total a Fondear</td>
                            <td class="text-right highlight-total fw-bold">${{ number_format($total_fondear, 2) }}</td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>

        <table class="signatures-grupal" style="margin-top: 60px;">
            <tr>
                <td><div class="sig-line-g">NOMBRE Y FIRMA<br>LÍDER</div></td>
                <td><div class="sig-line-g">SELLO Y FIRMA<br>AUTORIZADO</div></td>
                <td><div class="sig-line-g">NOMBRE Y FIRMA<br>GERENTE</div></td>
            </tr>
        </table>

    @else
        {{-- ========================================================================= --}}
        {{-- FORMATO INDIVIDUAL (PORTRAIT) --}}
        {{-- ========================================================================= --}}
        
        <table class="top-table">
            <tr>
                <td width="60%">
                    <div class="info-label">NOMBRE DEL CLIENTE:</div>
                    <div class="info-value">{{ mb_strtoupper($credito->cliente->nombre_completo ?? $credito->cliente->nombre) }}</div>
                    
                    <div class="info-label">DIRECCIÓN:</div>
                    <div class="info-value">{{ mb_strtoupper($direccion) }}</div>
                    
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
                    
                    <div class="date-text">FECHA: {{ \Carbon\Carbon::parse($credito->fecha_desembolso)->format('d/m/Y') }}</div>
                </td>
            </tr>
        </table>
        
        <div class="main-title">ACTA DE INSTALACIÓN</div>
        <div class="sub-title">CONTABILIDAD DEL CRÉDITO</div>

        <table class="math-wrapper">
            <tr>
                <td width="46%" valign="top">
                    <table class="math-table">
                        @if(isset($pago_adelantado) && $pago_adelantado > 0)
                        <tr>
                            <td>Pago Adelantado 1</td>
                            <td class="text-right fw-bold text-danger">${{ number_format($pago_adelantado, 2) }}</td>
                        </tr>
                        @endif

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

                        @if($comision == 0 && $retencion_seguro == 0 && (!isset($pago_adelantado) || $pago_adelantado == 0))
                        <tr>
                            <td class="text-muted" style="font-style: italic; color: #a0aec0;">Sin deducciones aplicadas</td>
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
                
                <td width="46%" valign="top">
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
                            <td class="highlight-total fw-bold">TOTAL A FONDEAR:</td>
                            <td class="text-right highlight-total fw-bold">${{ number_format($total_fondear, 2) }}</td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>

        <table class="signatures">
            <tr>
                <td><div class="sig-line">FIRMA CLIENTE</div></td>
                <td><div class="sig-line">AUTORIZADO</div></td>
                <td><div class="sig-line">FIRMA GERENTE</div></td>
            </tr>
        </table>
    @endif

</body>
</html>