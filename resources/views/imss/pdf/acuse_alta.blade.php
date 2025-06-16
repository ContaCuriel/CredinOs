<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Acuse de Alta IMSS</title>
    <style>
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 10px;
            line-height: 1.4;
            margin: 35px;
        }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .text-justify { text-align: justify; }
        .text-bold { font-weight: bold; }
        .uppercase { text-transform: uppercase; }
        .mt-1 { margin-top: 0.25rem; }
        .mt-2 { margin-top: 0.5rem; }
        .mt-3 { margin-top: 1rem; }
        .mt-4 { margin-top: 1.5rem; }
        .mt-5 { margin-top: 3rem; }
        .mb-1 { margin-bottom: 0.25rem; }
        .mb-2 { margin-bottom: 0.5rem; }
        .mb-3 { margin-bottom: 1rem; }
        .mb-4 { margin-bottom: 1.5rem; }

        .header-logo-patron {
            text-align: left; /* O center, según prefieras */
            margin-bottom: 20px;
            max-height: 60px; /* Ajusta según el tamaño de tus logos */
        }
        .header-logo-patron img {
            max-height: 60px;
        }
        .signature-section {
            margin-top: 50px;
        }
        .signature-line {
            border-bottom: 1px solid #000;
            width: 250px;
            margin: 30px auto 5px auto;
        }
        .etiqueta { font-weight: bold; } /* Definición de .etiqueta que faltaba en el CSS */
    </style>
</head>
<body>
    @php
        $nombrePatronParaDocumento = $patronImss->razon_social ?? $patronImss->nombre_comercial ?? 'PATRÓN NO ESPECIFICADO';
        
        // Lugar y fecha de emisión del documento
        $lugarYFechaDocumento = 'TEXCOCO, ESTADO DE MÉXICO, a ' . now()->translatedFormat('d \d\e F \d\e Y');
    @endphp

    {{-- Mostrar el logo del Patrón si existe --}}
    @if ($patronImss->logo_path)
        <div class="header-logo-patron">
            {{-- Para que DOMPDF encuentre la imagen desde la carpeta storage/app/public --}}
            <img src="{{ storage_path('app/public/' . $patronImss->logo_path) }}" alt="Logo Patrón">
        </div>
    @endif

    <p class="text-right text-bold uppercase">{{ $lugarYFechaDocumento }}</p>
    <br>
    <p class="text-bold uppercase">{{ $empleado->nombre_completo }}</p>
    <p class="text-bold">P R E S E N T E.</p>
    <br>
    <p class="text-bold uppercase">ASUNTO: AFILIACIÓN AL IMSS Y RETENCIONES.</p>
    <br>

    <p class="text-justify">
        Por medio de la presente le hago sabedor que <span class="etiqueta uppercase">{{ $nombrePatronParaDocumento }}</span>@if ($patronImss->tipo_persona == 'moral' && $patronImss->representante_legal), por medio de su Representante Legal el C. <span class="etiqueta uppercase">{{ $patronImss->representante_legal }}</span>,@endif le da aviso sobre su incorporación al IMSS con fecha <span class="etiqueta">{{ $empleado->fecha_alta_imss ? $empleado->fecha_alta_imss->translatedFormat('d \d\e F \d\e Y') : 'FECHA NO ESPECIFICADA' }}</span> y las posibles retenciones de créditos a su cargo que tenga con el INFONAVIT y FONACOT por motivo de créditos inmobiliarios o prestamos en dinero y/o especie, que usted haya adquirido en el pasado.
    </p>

    <p class="text-justify">
        Asimismo le recordamos que los derechos son irrenunciables por lo que nosotros tenemos la obligación de inscribirlo como parte de sus derechos; No obstante, lo anterior, en caso de no estar de acuerdo está en su derecho de comunicárnoslo. Le pedimos se comunique con nosotros para saber la respuesta.
    </p>

    <p class="text-justify">
        Por lo anterior le informamos que <span class="etiqueta uppercase">{{ $nombrePatronParaDocumento }}</span> no absorberá deuda de ninguna especie que tenga a su cargo y si el INFONAVIT o FONACOT hiciera algún cargo a la empresa, se le notificará y se procederá a la retención de sus ingresos a fin de subsanar dichos cargos.
    </p>
    <br>
    <p class="text-justify">
        Firma para aceptar la incorporación al IMSS, o por el contrario exprese el motivo por el cual no quiere ingresar.
    </p>
    <p class="text-justify">
        Sin más por el momento quedamos a sus órdenes.
    </p>
    <br><br>

    <div class="text-center signature-section">
        <p class="text-bold">ATENTAMENTE</p>
        <div class="signature-line"></div>
        <p class="text-bold uppercase">{{ $empleado->nombre_completo }}</p>
        <p>(NOMBRE Y FIRMA DEL EMPLEADO)</p>
    </div>
    <br><br>
    <div class="text-center" style="margin-top: 30px;">
    <p class="text-bold" style="margin-bottom: 5px;">EMITIDO POR:</p> {{-- Margen inferior reducido --}}
    {{-- <br> --}} {{-- Se eliminó el <br> --}}
    <p style="margin-top: 0; margin-bottom: 0;">Carlos Alberto Martinez Curiel</p>
    <p style="margin-top: 2px; margin-bottom: 0;">Contador General de <span class="text-bold">{{ $patronImss->nombre_comercial ?: $patronImss->razon_social }}</span></p> {{-- Pequeño margen superior --}}
    <p style="margin-top: 2px; margin-bottom: 0;">Cel: 55 3075 3784</p>
    <p style="margin-top: 2px; margin-bottom: 0;">Correo: contador@credintegra.mx</p>
</div>

</body>
</html>