<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Aviso de Terminación</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; line-height: 1.6; color: #000; margin: 45px; }
        .text-end { text-align: right; }
        .text-justify { text-align: justify; }
        .text-center { text-align: center; }
        .fw-bold { font-weight: bold; }
        .uppercase { text-transform: uppercase; }
        .mt-4 { margin-top: 2rem; }
        .mt-5 { margin-top: 3.5rem; }
        .signature-line { width: 220px; border-bottom: 1px solid black; margin: 0 auto; margin-top: 4rem; }
    </style>
</head>
<body>

    <div class="text-center fw-bold" style="text-decoration: underline; font-size: 13px; margin-bottom: 30px;">
        AVISO DE TERMINACIÓN DE RELACIÓN JURÍDICA POR VENCIMIENTO DE TÉRMINO
    </div>

    <p class="text-end fw-bold">
        {{ $empleado->sucursal->municipio ?? 'Municipio No Especificado' }}, {{ $empleado->sucursal->estado ?? 'Estado No Especificado' }} a {{ \Carbon\Carbon::parse($empleado->fecha_baja)->translatedFormat('d \d\e F \d\e Y') }}.
    </p>

    <div class="mt-4">
        <p class="fw-bold">C. {{ $empleado->nombre_completo }}</p>
        <p class="fw-bold">P R E S E N T E.</p>
    </div>

    <div class="mt-4 text-justify">
        <p>
            Por medio de la presente, <span class="fw-bold uppercase">{{ $patron->razon_social ?? $patron->nombre_comercial }}</span>, 
            @if ($patron->tipo_persona == 'moral' && $patron->representante_legal)
                REPRESENTADA EN ESTE ACTO POR EL C. <span class="fw-bold uppercase">{{ $patron->representante_legal }}</span>,
            @elseif ($patron->tipo_persona == 'fisica')
                POR SU PROPIO DERECHO,
            @endif
            en su carácter de "EL CONTRATANTE", le notifica formalmente la conclusión de la prestación de sus servicios, con base en los siguientes puntos:
        </p>

        <p><span class="fw-bold">1. ANTECEDENTE:</span> Con fecha <span class="fw-bold">{{ \Carbon\Carbon::parse($contrato->fecha_inicio)->translatedFormat('d \d\e F \d\e Y') }}</span>, usted suscribió un Contrato de Prestación de Servicios Profesionales con esta empresa para el puesto de <span class="fw-bold">{{ $empleado->puesto->nombre_puesto ?? 'NO ESPECIFICADO' }}</span>, con una vigencia determinada.</p>

        <p><span class="fw-bold">2. VENCIMIENTO:</span> De acuerdo con la <span class="fw-bold">CLÁUSULA SEGUNDA</span> de dicho contrato, la vigencia del mismo concluye precisamente el día <span class="fw-bold">{{ \Carbon\Carbon::parse($contrato->fecha_fin)->translatedFormat('d \d\e F \d\e Y') }}</span>.</p>

        <p><span class="fw-bold">3. NO RENOVACIÓN:</span> Se le informa que la empresa ha decidido no hacer uso de la facultad de prórroga o renovación, por lo que la relación jurídica se da por terminada en este momento de manera natural y por cumplimiento del plazo pactado.</p>

        <p>Se hace constar que no existe adeudo de honorarios a su favor, quedando cubiertos íntegramente los servicios prestados durante la vigencia del contrato. Lo anterior de conformidad con la <span class="fw-bold">CLÁUSULA QUINTA</span> del contrato vigente, donde ambas partes reconocieron la naturaleza civil de esta relación y la ausencia de subordinación laboral.</p>
    </div>

<div style="margin-top: 30px; width: 100%; overflow: hidden;">
        <div style="float: left; width: 50%; text-align: center;">
            <p style="margin-bottom: 40px;">ATENTAMENTE,</p>
            <div style="width: 200px; border-bottom: 1px solid black; margin: 0 auto;"></div>
            <p class="fw-bold" style="font-size: 11px; margin-top: 5px;">
                @if ($patron->tipo_persona == 'moral' && $patron->representante_legal)
                    {{ $patron->representante_legal }}<br>
                    <small>Representante Legal de {{ $patron->razon_social }}</small>
                @else
                    {{ $patron->razon_social }}
                @endif
            </p>
        </div>

        <div style="float: right; width: 50%; text-align: center;">
            <p style="margin-bottom: 40px;">RECIBÍ ORIGINAL (ACUSE)</p>
            <div style="width: 200px; border-bottom: 1px solid black; margin: 0 auto;"></div>
            <p class="fw-bold" style="font-size: 11px; margin-top: 5px;">
                {{ $empleado->nombre_completo }}
            </p>
        </div>
    </div>

    <div style="margin-top: 20px; border-top: 1px solid #000; pt-2;">
        <p class="text-center fw-bold" style="font-size: 11px; margin-bottom: 5px;">ACUSE DE RECIBO Y CONFORMIDAD</p>
        <p class="text-justify small" style="font-size: 10px; line-height: 1.2; margin-bottom: 10px;">
            Recibí el original del presente aviso y manifiesto estar de acuerdo en que la relación jurídica termina por vencimiento de contrato, dándome por pagado de cualquier concepto a mi favor.
        </p>
        <table style="width: 100%; font-size: 11px;">
            <tr>
                <td style="width: 60%;">Nombre: <strong>{{ $empleado->nombre_completo }}</strong></td>
                <td>Fecha: ____/____/_______</td>
            </tr>
            <tr>
                <td colspan="2" style="padding-top: 10px;">Firma: ___________________________</td>
            </tr>
        </table>
    </div>

</body>
</html>