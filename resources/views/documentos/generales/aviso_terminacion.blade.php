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

<div class="mt-5" style="width: 100%;">
    <div style="width: 300px; margin: 0 auto; text-align: center;">
        <p>ATENTAMENTE,</p>
        <div class="signature-line"></div>
        <p class="fw-bold">
            @if ($patron->tipo_persona == 'moral' && $patron->representante_legal)
                {{ $patron->representante_legal }}<br>
                <small>Representante Legal de {{ $patron->razon_social }}</small>
            @else
                {{ $patron->razon_social }}
            @endif
        </p>
    </div>
</div>

    <div class="mt-5 border-top pt-4">
        <p class="text-center fw-bold">ACUSE DE RECIBO Y CONFORMIDAD</p>
        <p class="text-justify small">
            Recibí el original del presente aviso y manifiesto estar de acuerdo en que la relación jurídica termina por vencimiento de contrato, dándome por pagado de cualquier concepto a mi favor.
        </p>
        <p class="mt-3">Nombre: <strong>{{ $empleado->nombre_completo }}</strong></p>
        <p>Fecha: ____/____/_______</p>
        <p>Firma: ___________________________</p>
    </div>

</body>
</html>