<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Aviso de Terminación</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; line-height: 1.6; color: #333; margin: 40px; }
        .text-end { text-align: right; }
        .text-justify { text-align: justify; }
        .text-center { text-align: center; }
        .fw-bold { font-weight: bold; }
        .mt-4 { margin-top: 2rem; }
        .mt-5 { margin-top: 3rem; }
        .mb-4 { margin-bottom: 2rem; }
        .signature-line { width: 250px; border-bottom: 1px solid black; margin: 0 auto; margin-top: 4rem; }
    </style>
</head>
<body>

    <div class="text-center fw-bold mb-4" style="text-decoration: underline;">
        AVISO DE TERMINACIÓN DE RELACIÓN JURÍDICA POR VENCIMIENTO DE TÉRMINO
    </div>

    {{-- LÍNEA CORREGIDA: Lugar y fecha basados en la sucursal y la fecha de baja --}}
    <p class="text-end fw-bold">
        {{ $empleado->sucursal->municipio ?? 'Municipio No Especificado' }}, {{ $empleado->sucursal->estado ?? 'Estado No Especificado' }} a {{ \Carbon\Carbon::parse($empleado->fecha_baja)->translatedFormat('d \d\e F \d\e Y') }}.
    </p>

    <div class="mt-4">
        <p>A la atención de: <span class="fw-bold">C. {{ $empleado->nombre_completo }}</span></p>
        <p class="fw-bold">Presente.</p>
    </div>

    <div class="mt-4 text-justify">
        <p>Por medio de la presente, <span class="fw-bold">{{ $empleado->sucursal->empresa->razon_social ?? config('app.name') }}</span>, le notifica formalmente la conclusión de la prestación de sus servicios, con base en los siguientes puntos:</p>

        <p><span class="fw-bold">1. ANTECEDENTE:</span> Con fecha <span class="fw-bold">{{ \Carbon\Carbon::parse($empleado->contratos->first()->fecha_inicio)->translatedFormat('d \d\e F \d\e Y') }}</span>, usted suscribió un Contrato de Prestación de Servicios Profesionales con esta empresa para el puesto de <span class="fw-bold">{{ $empleado->puesto }}</span>, con una vigencia determinada.</p>

        <p><span class="fw-bold">2. VENCIMIENTO:</span> De acuerdo con la <span class="fw-bold">CLÁUSULA SEGUNDA</span> de dicho contrato, la vigencia del mismo concluye precisamente el día de hoy, <span class="fw-bold">{{ \Carbon\Carbon::parse($empleado->fecha_baja)->translatedFormat('d \d\e F \d\e Y') }}</span>.</p>

        <p><span class="fw-bold">3. NO RENOVACIÓN:</span> Se le informa que la empresa ha decidido no hacer uso de la facultad de prórroga o renovación, por lo que la relación jurídica se da por terminada en este momento de manera natural y por cumplimiento del plazo pactado.</p>

        <p>Se hace constar que no existe adeudo de honorarios a su favor, quedando cubiertos íntegramente los servicios prestados durante la vigencia del contrato. Lo anterior de conformidad con la <span class="fw-bold">CLÁUSULA QUINTA</span> del contrato vigente, donde ambas partes reconocieron la naturaleza civil de esta relación y la ausencia de subordinación laboral.</p>
    </div>

    <div class="mt-5" style="width: 100%;">
        <div style="float: left; width: 50%; text-align: center;">
            <p>Atentamente,</p>
            <div class="signature-line"></div>
            <p class="fw-bold">Representante Legal<br>{{ $empleado->sucursal->empresa->nombre_comercial ?? config('app.name') }}</p>
        </div>
        <div style="float: right; width: 50%; text-align: center;">
            <p>RECIBÍ ORIGINAL (ACUSE)</p>
            <div class="signature-line"></div>
            <p class="fw-bold">{{ $empleado->nombre_completo }}</p>
        </div>
    </div>

</body>
</html>