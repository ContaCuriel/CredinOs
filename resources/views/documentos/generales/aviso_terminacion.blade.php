<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Aviso de Terminación</title>
    <style>
        /* Reducimos márgenes exteriores para ganar espacio */
        body { font-family: Arial, sans-serif; font-size: 11px; line-height: 1.3; color: #000; margin: 30px 45px; }
        .text-end { text-align: right; }
        .text-justify { text-align: justify; }
        .text-center { text-align: center; }
        .fw-bold { font-weight: bold; }
        .uppercase { text-transform: uppercase; }
        .mt-2 { margin-top: 1rem; }
        .mt-4 { margin-top: 1.5rem; }
        .signature-box { text-align: center; margin-top: 25px; }
        .line { width: 200px; border-bottom: 1px solid black; margin: 0 auto; margin-top: 35px; }
    </style>
</head>
<body>

    <div class="text-center fw-bold" style="text-decoration: underline; font-size: 12px; margin-bottom: 20px;">
        AVISO DE TERMINACIÓN DE RELACIÓN JURÍDICA POR VENCIMIENTO DE TÉRMINO
    </div>

    <p class="text-end fw-bold">
        {{ $empleado->sucursal->municipio ?? 'Municipio No Especificado' }}, {{ $empleado->sucursal->estado ?? 'Estado No Especificado' }} a {{ \Carbon\Carbon::parse($empleado->fecha_baja)->translatedFormat('d \d\e F \d\e Y') }}.
    </p>

    <div class="mt-2">
        <p class="fw-bold">C. {{ $empleado->nombre_completo }}</p>
        <p class="fw-bold">P R E S E N T E.</p>
    </div>

    <div class="mt-2 text-justify">
        <p>
            Por medio de la presente, <span class="fw-bold uppercase">{{ $patron->razon_social ?? $patron->nombre_comercial }}</span>, 
            @if ($patron->tipo_persona == 'moral' && $patron->representante_legal)
                REPRESENTADA EN ESTE ACTO POR EL C. <span class="fw-bold uppercase">{{ $patron->representante_legal }}</span>,
            @elseif ($patron->tipo_persona == 'fisica')
                POR SU PROPIO DERECHO,
            @endif
            en su carácter de "EL CONTRATANTE", le notifica formalmente la conclusión de la prestación de sus servicios, con base en los siguientes puntos:
        </p>

        <p style="margin-bottom: 8px;"><span class="fw-bold">1. ANTECEDENTE:</span> Con fecha <span class="fw-bold">{{ \Carbon\Carbon::parse($contrato->fecha_inicio)->translatedFormat('d \d\e F \d\e Y') }}</span>, usted suscribió un Contrato de Prestación de Servicios Profesionales con esta empresa para el puesto de <span class="fw-bold">{{ $empleado->puesto->nombre_puesto ?? 'NO ESPECIFICADO' }}</span>, con una vigencia determinada.</p>

        <p style="margin-bottom: 8px;"><span class="fw-bold">2. VENCIMIENTO:</span> De acuerdo con la <span class="fw-bold">CLÁUSULA SEGUNDA</span> de dicho contrato, la vigencia del mismo concluye precisamente el día <span class="fw-bold">{{ \Carbon\Carbon::parse($contrato->fecha_fin)->translatedFormat('d \d\e F \d\e Y') }}</span>.</p>

        <p style="margin-bottom: 8px;"><span class="fw-bold">3. NO RENOVACIÓN:</span> Se le informa que la empresa ha decidido no hacer uso de la facultad de prórroga o renovación, por lo que la relación jurídica se da por terminada en este momento de manera natural y por cumplimiento del plazo pactado.</p>

        <p>Se hace constar que no existe adeudo de honorarios a su favor, quedando cubiertos íntegramente los servicios prestados durante la vigencia del contrato. Lo anterior de conformidad con la <span class="fw-bold">CLÁUSULA QUINTA</span> del contrato vigente, donde ambas partes reconocieron la naturaleza civil de esta relación y la ausencia de subordinación laboral.</p>
    </div>

    <div class="signature-box">
        <p class="fw-bold">ATENTAMENTE,</p>
        <div class="line"></div>
        <p class="fw-bold" style="margin-top: 5px;">
            @if ($patron->tipo_persona == 'moral' && $patron->representante_legal)
                {{ $patron->representante_legal }}<br>
                <small>Representante Legal de {{ $patron->razon_social }}</small>
            @else
                {{ $patron->razon_social }}
            @endif
        </p>
    </div>

    <div style="margin-top: 30px; border: 1px solid #000; padding: 10px; background-color: #f9f9f9;">
        <p class="text-center fw-bold" style="margin-bottom: 5px; text-decoration: underline;">ACUSE DE RECIBO Y CONFORMIDAD POR EL TRABAJADOR</p>
        <p class="text-justify" style="font-size: 10px; margin-bottom: 10px;">
            Recibí el original del presente aviso y manifiesto estar de acuerdo en que la relación jurídica termina por vencimiento de contrato, dándome por pagado de cualquier concepto a mi favor.
        </p>
        <table style="width: 100%; margin-top: 5px;">
            <tr>
                <td style="width: 50%; vertical-align: bottom;">
                    Nombre: <strong>{{ $empleado->nombre_completo }}</strong><br><br>
                    Fecha: ____/____/_______
                </td>
                <td style="width: 50%; text-align: center;">
                    <div style="width: 180px; border-bottom: 1px solid black; margin: 0 auto;"></div>
                    <p class="fw-bold">FIRMA DEL EMPLEADO</p>
                </td>
            </tr>
        </table>
    </div>

</body>
</html>