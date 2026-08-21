<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Solicitud de Crédito</title>
    <style>
        @page { margin: 1cm 1.5cm; }
        body { font-family: 'Helvetica', 'Arial', sans-serif; font-size: 8.5pt; color: #000; margin: 0; line-height: 1.2; }
        
        /* Cabecera */
        .header-table { width: 100%; margin-bottom: 15px; }
        .logo-cell { width: 30%; vertical-align: middle; }
        .logo-cell img { max-width: 160px; max-height: 60px; }
        .logo-cell h3 { margin: 0; font-size: 14pt; color: #003a70; text-transform: uppercase; }
        .title-cell { width: 70%; text-align: right; vertical-align: middle; }
        .doc-title { font-size: 16pt; font-weight: bold; color: #000; text-transform: uppercase; letter-spacing: 1px; }

        /* Títulos de Sección */
        .section-title { 
            background-color: #003a70; 
            color: #fff; 
            font-weight: bold; 
            padding: 4px 8px; 
            font-size: 9.5pt; 
            text-transform: uppercase; 
            margin-top: 10px; 
            margin-bottom: 5px;
        }

        /* Tablas de Formulario */
        .form-table { width: 100%; border-collapse: collapse; margin-bottom: 10px; }
        .form-table td { border: 1px solid #000; padding: 4px; vertical-align: middle; }
        .lbl { background-color: #f2f2f2; font-weight: bold; font-size: 7.5pt; color: #333; text-transform: uppercase; width: 1%; white-space: nowrap; }
        .val { font-weight: bold; font-size: 8.5pt; color: #000; text-transform: uppercase; }

        /* Croquis y Autorización */
        .croquis-box { border: 1px solid #000; height: 120px; width: 100%; text-align: center; color: #ccc; padding-top: 50px; box-sizing: border-box; }
        .legal-text { font-size: 7.5pt; text-align: justify; margin: 10px 0; line-height: 1.3; }

        /* Firmas */
        .signatures { width: 100%; margin-top: 40px; table-layout: fixed; border-collapse: collapse; }
        .signatures td { text-align: center; vertical-align: bottom; height: 50px; }
        .sig-line { border-top: 1px solid #000; width: 80%; margin: 0 auto; padding-top: 4px; font-weight: bold; font-size: 8pt; text-transform: uppercase; }
    </style>
</head>
<body>

    @php
        $empresa = $credito->patron->nombre_comercial ?? 'LA EMPRESA';
        $monto_solicitado = $credito->monto_solicitado ?? $credito->monto_aprobado; 
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
            <td class="title-cell">
                <div class="doc-title">Solicitud de Crédito</div>
            </td>
        </tr>
    </table>

    <div class="section-title">Información del Solicitante</div>
    <table class="form-table">
        <tr>
            <td class="lbl">Sucursal:</td>
            <td class="val" colspan="2">{{ $credito->sucursal->nombre_sucursal ?? 'N/A' }}</td>
            <td class="lbl">Fecha:</td>
            <td class="val" colspan="2">{{ \Carbon\Carbon::parse($credito->created_at)->format('d/m/Y') }}</td>
        </tr>
        <tr>
            <td class="lbl">Asesor de Crédito:</td>
            <td class="val" colspan="2">{{ $credito->asesor->nombre_completo ?? 'N/A' }}</td>
            <td class="lbl">Gerente:</td>
            <td class="val" colspan="2">AUTORIZADO</td>
        </tr>
        <tr>
            <td class="lbl">Monto Solicitado:</td>
            <td class="val" colspan="2">${{ number_format($monto_solicitado, 2) }}</td>
            <td class="lbl">Grupo:</td>
            <td class="val" colspan="2">{{ $credito->grupo->nombre_grupo ?? 'INDIVIDUAL' }}</td>
        </tr>
    </table>

    <div class="section-title">Datos del Solicitante</div>
    <table class="form-table">
        <tr>
            <td class="lbl">Apellido Paterno:</td>
            <td class="val">{{ $cliente->apellido_paterno }}</td>
            <td class="lbl">Apellido Materno:</td>
            <td class="val">{{ $cliente->apellido_materno }}</td>
            <td class="lbl">Nombre (S):</td>
            <td class="val">{{ $cliente->nombre }}</td>
        </tr>
        <tr>
            <td class="lbl">RFC:</td>
            <td class="val">{{ $cliente->rfc }}</td>
            <td class="lbl">CURP:</td>
            <td class="val" colspan="3">{{ $cliente->curp }}</td>
        </tr>
        <tr>
            <td class="lbl">Fecha de Nacimiento:</td>
            <td class="val">{{ $cliente->fecha_nacimiento ? \Carbon\Carbon::parse($cliente->fecha_nacimiento)->format('d/m/Y') : '' }}</td>
            <td class="lbl">Estado Civil:</td>
            <td class="val">{{ $cliente->estado_civil }}</td>
            <td class="lbl">Género:</td>
            <td class="val">{{ $cliente->genero }}</td>
        </tr>
        <tr>
            <td class="lbl">Estado de Nacimiento:</td>
            <td class="val">{{ $cliente->estado_nacimiento }}</td>
            <td class="lbl">Nacionalidad:</td>
            <td class="val">{{ $cliente->nacionalidad }}</td>
            <td class="lbl">Ocupación:</td>
            <td class="val">{{ $cliente->ocupacion }}</td>
        </tr>
        <tr>
            <td class="lbl">Número de Hijos:</td>
            <td class="val">{{ $cliente->numero_hijos }}</td>
            <td class="lbl">Dependientes Económicos:</td>
            <td class="val">{{ $cliente->dependientes_economicos }}</td>
            <td class="lbl">Escolaridad:</td>
            <td class="val">{{ $cliente->escolaridad ?? 'N/A' }}</td>
        </tr>
    </table>

    <div class="section-title">Domicilio Particular</div>
    <table class="form-table">
        <tr>
            <td class="lbl">Calle:</td>
            <td class="val" colspan="3">{{ $cliente->calle }}</td>
            <td class="lbl">Núm:</td>
            <td class="val">{{ $cliente->numero }}</td>
        </tr>
        <tr>
            <td class="lbl">Colonia:</td>
            <td class="val">{{ $cliente->colonia }}</td>
            <td class="lbl">Municipio:</td>
            <td class="val">{{ $cliente->municipio }}</td>
            <td class="lbl">C.P.:</td>
            <td class="val">{{ $cliente->codigo_postal }}</td>
        </tr>
        <tr>
            <td class="lbl">Estado:</td>
            <td class="val">{{ $cliente->estado }}</td>
            <td class="lbl">Tipo Vivienda:</td>
            <td class="val">{{ $cliente->tipo_vivienda }}</td>
            <td class="lbl">Años Domicilio:</td>
            <td class="val">{{ $cliente->anios_domicilio }}</td>
        </tr>
        <tr>
            <td class="lbl">Teléfono Fijo:</td>
            <td class="val" colspan="2">{{ $cliente->telefono_fijo }}</td>
            <td class="lbl">Celular:</td>
            <td class="val" colspan="2">{{ $cliente->telefono_celular }}</td>
        </tr>
    </table>

    <div class="section-title">Referencias Personales</div>
    @if($cliente->referencias && $cliente->referencias->count() > 0)
        @foreach($cliente->referencias->take(2) as $index => $referencia)
        <table class="form-table">
            <tr>
                <td class="lbl">Referencia {{ $index + 1 }}:</td>
                <td class="val" colspan="3">{{ $referencia->nombre_completo ?? $referencia->nombre }}</td>
                <td class="lbl">Teléfono:</td>
                <td class="val">{{ $referencia->telefono }}</td>
            </tr>
            <tr>
                <td class="lbl">Domicilio:</td>
                <td class="val" colspan="5">{{ $referencia->direccion ?? 'N/A' }}</td>
            </tr>
        </table>
        @endforeach
    @else
        <table class="form-table"><tr><td class="val text-center" style="text-align: center; color: #666; font-style: italic;">Sin referencias capturadas en sistema.</td></tr></table>
    @endif

    <div class="section-title">Datos del Negocio / Laborales</div>
    <table class="form-table">
        <tr>
            <td class="lbl">Nombre del Negocio:</td>
            <td class="val">{{ $cliente->nombre_negocio }}</td>
            <td class="lbl">Giro:</td>
            <td class="val">{{ $cliente->giro_negocio }}</td>
            <td class="lbl">Antigüedad:</td>
            <td class="val">{{ $cliente->antiguedad_negocio }} Años</td>
        </tr>
        <tr>
            <td class="lbl">Ingresos Mensuales:</td>
            <td class="val">${{ number_format($cliente->ingresos_mensuales, 2) }}</td>
            <td class="lbl">Gastos Mensuales:</td>
            <td class="val">${{ number_format($cliente->gastos_mensuales, 2) }}</td>
            <td class="lbl">Destino Crédito:</td>
            <td class="val">{{ $cliente->destino_credito }}</td>
        </tr>
        @if(!$cliente->mismo_domicilio_laboral)
        <tr>
            <td class="lbl">Dirección Negocio:</td>
            <td class="val" colspan="5">
                {{ $cliente->calle_negocio }} {{ $cliente->numero_negocio }}, Col. {{ $cliente->colonia_negocio }}, {{ $cliente->municipio_negocio }}, {{ $cliente->estado_negocio }}, C.P. {{ $cliente->codigo_postal_negocio }}
            </td>
        </tr>
        @endif
    </table>

    <div class="section-title">Croquis de Ubicación</div>
    <div class="croquis-box">
        (Área destinada para dibujo de croquis)
    </div>

    <div class="section-title">Autorización para Solicitar Reportes de Crédito</div>
    <div class="legal-text">
        POR ESTE MEDIO AUTORIZO EXPRESAMENTE A {{ strtoupper($empresa) }} PARA QUE POR MEDIO DE LAS PERSONAS FACULTADAS, LLEVE A CABO INVESTIGACIONES SOBRE MI COMPORTAMIENTO CREDITICIO Y OBTENGA VALORACIONES NUMERICAS EN LAS SOCIEDADES DE INFORMACION CREDITICIA (SIC) QUE CREA CONVENIENTES.
    </div>

    <table class="signatures">
        <tr>
            <td>
                <div class="sig-line">FIRMA DEL SOLICITANTE</div>
            </td>
            <td>
                <div class="sig-line">FIRMA DEL ASESOR</div>
            </td>
            <td>
                <div class="sig-line">FIRMA GERENTE DE SUCURSAL</div>
            </td>
        </tr>
    </table>

</body>
</html>