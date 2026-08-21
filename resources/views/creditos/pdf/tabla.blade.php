<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Control de Pagos</title>
    <style>
        /* ========================================================
           LÓGICA DE ORIENTACIÓN DINÁMICA (HORIZONTAL VS VERTICAL)
           ======================================================== */
        @if($credito->grupo_id)
            @page { size: letter landscape; margin: 1cm 1.5cm; }
        @else
            /* INDIVIDUAL: Márgenes súper reducidos para que quepa en 1 hoja */
            @page { size: letter portrait; margin: 0.8cm 1.5cm; } 
        @endif

        /* INDIVIDUAL: Letra, padding e interlineado comprimidos al máximo */
        body { font-family: 'Helvetica', 'Arial', sans-serif; font-size: 9.5px; color: #333; line-height: 1.15; margin: 0; padding: 0; }
        .container { width: 100%; margin: 0 auto; }
        
        /* Cabecera con Logo */
        .header { width: 100%; height: 50px; margin-bottom: 8px; border-bottom: 2px solid #003a70; padding-bottom: 2px; display: table; }
        .logo-cell { display: table-cell; width: 50%; vertical-align: middle; }
        .logo-cell img { max-width: 160px; max-height: 45px; }
        .logo-cell h3 { margin: 0; font-size: 14px; color: #003a70; text-transform: uppercase; }
        
        .title-cell { display: table-cell; width: 50%; vertical-align: middle; text-align: right; }
        .doc-title { font-size: 14px; font-weight: bold; color: #333; text-transform: uppercase; letter-spacing: 1px; }
        .doc-date { font-size: 9px; color: #666; margin-top: 2px; }

        /* Títulos de sección */
        .section-title { background-color: #f2f2f2; padding: 3px; text-align: center; font-weight: bold; font-size: 10px; border: 1px solid #ddd; text-transform: uppercase; margin-bottom: 5px; color: #003a70; }

        /* Tabla de Información General (Individual) */
        .info-table { width: 100%; border-collapse: collapse; margin-bottom: 10px; }
        .info-table td { padding: 2px 4px; border-bottom: 1px solid #f0f0f0; }
        .lbl { font-weight: bold; color: #555; width: 20%; font-size: 8.5px; text-transform: uppercase; }
        .val { color: #222; font-size: 9.5px; width: 30%; }
        .text-right { text-align: right; }
        .val-highlight { font-weight: bold; color: #003a70; }

        /* Tabla de Cuotas (Individual) */
        .cuotas-table { width: 100%; margin: 0 auto 10px auto; border-collapse: collapse; }
        .cuotas-table th { background-color: #f9f9f9; color: #333; font-size: 8.5px; text-transform: uppercase; padding: 3px; border: 1px solid #ddd; text-align: center; }
        .cuotas-table td { padding: 3px; border: 1px solid #ddd; text-align: center; font-size: 9px; }
        .cuotas-table tr:nth-child(even) { background-color: #fafafa; }

        /* Firma Individual */
        .signature-section { margin-top: 15px; text-align: center; page-break-inside: avoid; }
        .signature-line { border-top: 1px solid #333; width: 250px; margin: 0 auto; padding-top: 4px; }
        .firma-name { font-weight: bold; font-size: 9.5px; text-transform: uppercase; margin-bottom: 2px; }
        
        /* Etiqueta de Copia */
        .copy-label { text-align: right; font-size: 8px; font-weight: bold; color: #888; text-transform: uppercase; margin-bottom: -5px; }

        /* ========================================================
           ESTILOS ESPECÍFICOS PARA GRUPAL (LANDSCAPE)
           ======================================================== */
        .header-grupal { width: 100%; border-collapse: collapse; margin-bottom: 15px; font-size: 9.5px; text-transform: uppercase; }
        .header-grupal td { padding: 3px 5px; border-bottom: 1px solid #eee; }
        .lbl-g { font-weight: bold; color: #555; width: 15%; }
        .val-g { color: #000; font-weight: bold; width: 35%; }
        
        .grupal-table { width: 100%; border-collapse: collapse; margin-bottom: 15px; border: 2px solid #333; }
        .grupal-table th { background-color: #f0f0f0; color: #333; font-size: 9px; text-transform: uppercase; padding: 6px 2px; border: 1px solid #333; text-align: center; }
        .grupal-table td { padding: 5px 3px; border: 1px solid #333; font-size: 9px; vertical-align: middle; }
        .grupal-table .text-left { text-align: left; padding-left: 5px; }
        .grupal-table .text-center { text-align: center; }
        
        .total-row td { background-color: #e2e8f0; font-weight: bold; font-size: 9.5px; border-top: 2px solid #333; }

        /* Firma Grupal Única (Centrada y con mucho espacio arriba) */
        .signature-section-grupal { margin-top: 100px; text-align: center; page-break-inside: avoid; }
        .sig-line-g-center { border-top: 1px solid #000; width: 350px; margin: 0 auto; padding-top: 5px; font-weight: bold; font-size: 11px; text-transform: uppercase; }
    </style>
</head>
<body>

    @php
        // Extraemos al líder para ambos casos
        $lider = null;
        if($credito->integrantes) {
            $lider = $credito->integrantes->where('pivot.es_lider', true)->first();
        }
        $nombreTitularOLider = $lider ? mb_strtoupper($lider->nombre_completo ?? $lider->nombre . ' ' . $lider->apellido_paterno) : mb_strtoupper($credito->cliente->nombre_completo ?? $credito->cliente->nombre ?? 'SIN ASIGNAR');
        
        $copias = [
            ['tipo' => 'Copia Cliente / Grupo', 'firma_cliente' => $nombreTitularOLider, 'desc_firma' => ($credito->grupo_id ? 'LÍDER DEL GRUPO' : 'Firma del Cliente / De Conformidad')],
            ['tipo' => 'Copia Expediente', 'firma_cliente' => 'AUTORIZADO', 'desc_firma' => 'Sello de la Empresa']
        ];
    @endphp

    @foreach($copias as $indexCopia => $copia)
        
        <div class="container">
            <div class="copy-label">{{ $copia['tipo'] }}</div>
            
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
                    <div class="doc-title">Control de Pagos</div>
                    <div class="doc-date">{{ strtoupper($credito->sucursal->nombre_sucursal ?? 'TEXCOCO') }}, {{ now()->format('d/m/Y') }}</div>
                </div>
            </div>

            @if($credito->grupo_id)
                {{-- ========================================================================= --}}
                {{-- FORMATO GRUPAL (LANDSCAPE) --}}
                {{-- ========================================================================= --}}
                
                <table class="header-grupal">
                    <tr>
                        <td class="lbl-g">NOMBRE DEL GRUPO:</td>
                        <td class="val-g text-primary">{{ mb_strtoupper($credito->grupo->nombre_grupo) }}</td>
                        <td class="lbl-g">MONTO GRUPAL:</td>
                        <td class="val-g text-success">${{ number_format($credito->monto_aprobado, 2) }}</td>
                    </tr>
                    <tr>
                        <td class="lbl-g">LÍDER DEL GRUPO:</td>
                        <td class="val-g">{{ $nombreTitularOLider }}</td>
                        <td class="lbl-g">PAGO {{ strtoupper($credito->producto->frecuencia_pago) }}:</td>
                        <td class="val-g">${{ number_format($monto_pago, 2) }}</td>
                    </tr>
                    <tr>
                        <td class="lbl-g">FECHA DESEMBOLSO:</td>
                        <td class="val-g">{{ \Carbon\Carbon::parse($credito->fecha_desembolso)->format('d/m/Y') }}</td>
                        <td class="lbl-g">FECHA INICIO:</td>
                        <td class="val-g">{{ \Carbon\Carbon::parse($credito->fecha_primer_pago)->format('d/m/Y') }}</td>
                    </tr>
                    <tr>
                        <td class="lbl-g">FOLIO:</td>
                        <td class="val-g">{{ $credito->folio }}</td>
                        <td class="lbl-g">FECHA FIN:</td>
                        <td class="val-g">{{ \Carbon\Carbon::parse($fecha_fin)->format('d/m/Y') }}</td>
                    </tr>
                    <tr>
                        <td class="lbl-g">ASESOR:</td>
                        <td class="val-g" colspan="3">{{ mb_strtoupper($credito->asesor->nombre_completo ?? 'N/A') }}</td>
                    </tr>
                </table>

                @php
                    // Dividimos los pagos en bloques de 8 semanas para que quepan en la hoja acostada
                    $chunks = $credito->amortizaciones->chunk(8);
                @endphp

                @foreach($chunks as $indexChunk => $chunk)
                    <table class="grupal-table">
                        <thead>
                            <tr>
                                <th width="22%">INTEGRANTES</th>
                                @if($indexChunk == 0)
                                    <th width="8%">MONTO</th>
                                    <th width="8%">PAGO</th>
                                @endif
                                
                                @foreach($chunk as $cuota)
                                    <th>
                                        <div style="font-size: 13px;">{{ $cuota->numero_cuota }}</div>
                                        <div style="font-size: 8px; font-weight: normal; margin-top: 2px;">
                                            @if($cuota->estatus == 'pagado')
                                                <span style="text-decoration: line-through; color: #666;">{{ \Carbon\Carbon::parse($cuota->fecha_pago)->format('d/m/Y') }}</span>
                                            @else
                                                {{ \Carbon\Carbon::parse($cuota->fecha_pago)->format('d/m/Y') }}
                                            @endif
                                        </div>
                                    </th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($credito->integrantes as $integrante)
                                @php
                                    // Cálculo de la cuota proporcional por integrante
                                    $cuota_ind = ($credito->monto_aprobado > 0) ? ($integrante->pivot->monto_individual / $credito->monto_aprobado) * $monto_pago : 0;
                                @endphp
                                <tr>
                                    <td class="text-left">{{ mb_strtoupper($integrante->nombre_completo ?? $integrante->nombre . ' ' . $integrante->apellido_paterno) }}</td>
                                    @if($indexChunk == 0)
                                        <td class="text-center fw-bold">${{ number_format($integrante->pivot->monto_individual, 2) }}</td>
                                        <td class="text-center fw-bold">${{ number_format($cuota_ind, 2) }}</td>
                                    @endif
                                    
                                    @foreach($chunk as $cuota)
                                        <td class="text-center">
                                            @if($cuota->estatus == 'pagado')
                                                <span style="color: #28a745; font-size: 14px;">✔</span>
                                            @endif
                                        </td>
                                    @endforeach
                                </tr>
                            @endforeach
                            
                            {{-- Fila de Totales --}}
                            <tr class="total-row">
                                <td style="text-align: right; padding-right: 10px;">TOTAL</td>
                                @if($indexChunk == 0)
                                    <td class="text-center">${{ number_format($credito->monto_aprobado, 2) }}</td>
                                    <td class="text-center">${{ number_format($monto_pago, 2) }}</td>
                                @endif
                                @foreach($chunk as $cuota)
                                    <td class="text-center">
                                        @if($cuota->estatus == 'pagado')
                                            <span style="font-size: 8px; color: #28a745;">RETENIDO</span>
                                        @endif
                                    </td>
                                @endforeach
                            </tr>
                        </tbody>
                    </table>
                @endforeach

                {{-- Firma Grupal Única y Centrada --}}
                <div class="signature-section-grupal">
                    <div class="sig-line-g-center"></div>
                    <div style="font-weight: bold; font-size: 11px; margin-bottom: 2px;">{{ $copia['firma_cliente'] }}</div>
                    <div style="font-size: 10px;">{{ $copia['desc_firma'] }}</div>
                </div>

            @else
                {{-- ========================================================================= --}}
                {{-- FORMATO INDIVIDUAL (PORTRAIT COMPRIMIDO) --}}
                {{-- ========================================================================= --}}
                <div class="section-title">Información del Crédito</div>
                
                <table class="info-table">
                    <tr>
                        <td class="lbl">Titular:</td>
                        <td class="val fw-bold">{{ $nombreTitularOLider }}</td>
                        <td class="lbl text-right">Folio:</td>
                        <td class="val text-right val-highlight">{{ $credito->folio }}</td>
                    </tr>
                    <tr>
                        <td class="lbl">Crédito:</td>
                        <td class="val">{{ $credito->nombre_credito ?? 'Individual' }}</td>
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
                        <td class="val text-right">{{ mb_strtoupper($credito->asesor->nombre_completo ?? 'N/A') }}</td>
                    </tr>
                </table>

                <div class="section-title">Calendario de Pagos</div>

                <table class="cuotas-table">
                    <thead>
                        <tr>
                            <th width="15%">NO. PAGO</th>
                            <th width="40%">FECHA PROGRAMADA</th>
                            <th width="45%">MONTO A PAGAR</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($credito->amortizaciones as $cuota)
                        <tr @if($cuota->estatus == 'pagado') style="color: #999; text-decoration: line-through; background-color: #f8f9fa;" @endif>
                            <td class="fw-bold">{{ $cuota->numero_cuota }}</td>
                            <td>{{ ucwords(\Carbon\Carbon::parse($cuota->fecha_pago)->locale('es')->isoFormat('DD \d\e MMMM \d\e YYYY')) }}</td>
                            <td class="val-highlight">
                                ${{ number_format($cuota->total_cuota, 2) }}
                                @if($cuota->estatus == 'pagado') 
                                    <span style="font-size: 7.5px; font-weight: bold; color: #28a745; text-decoration: none !important; margin-left: 5px;">(RETENIDO EN DESEMBOLSO)</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>

                <div class="signature-section">
                    <div class="signature-line"></div>
                    <div class="firma-name">{{ $copia['firma_cliente'] }}</div>
                    <div style="font-size: 9px;">{{ $copia['desc_firma'] }}</div>
                </div>
            @endif
        </div>

        {{-- Salto de página para la segunda copia --}}
        @if(!$loop->last)
            <div style="page-break-before: always;"></div>
        @endif

    @endforeach

</body>
</html>