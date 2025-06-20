<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Carta de Renuncia Voluntaria</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            line-height: 1.6;
        }
        .text-center { text-align: center; }
        .text-end { text-align: right; }
        .text-justify { text-align: justify; }
        .fw-bold { font-weight: bold; }
        .mt-5 { margin-top: 3rem; }
        .mt-4 { margin-top: 2rem; }
        .mt-3 { margin-top: 1.5rem; }
        .mb-4 { margin-bottom: 2rem; }
        .signature-line {
            width: 70%;
            border-bottom: 1px solid black;
            margin: 0 auto;
            margin-top: 4rem;
        }
    </style>
</head>
<body>

    <p class="text-end fw-bold">RENUNCIA VOLUNTARIA</p>

    <p class="text-end">
        {{ $patron->direccion_fiscal ?? 'Ciudad No Especificada' }} a {{ $fecha_fin_letra }}.
    </p>

    <div class="mt-4">
        <p class="fw-bold">{{ $patron->razon_social ?? $patron->nombre_comercial }}</p>
        <p class="fw-bold">P R E S E N T E.</p>
    </div>

    <p class="mt-4 text-justify">
        Por medio de la presente, yo, <span class="fw-bold">{{ $empleado->nombre_completo }}</span>, por convenir así a mis intereses particulares, con fecha <span class="fw-bold">{{ $fecha_fin_letra }}</span>, he resuelto dar por terminado voluntariamente
        @if($esContratoDeHonorarios)
            el <span class="fw-bold">contrato de prestación de servicios profesionales</span>
        @else
            la <span class="fw-bold">relación laboral</span> y/o contrato individual de trabajo
        @endif
        que me unía con usted(es), en términos de la Fracción I del artículo 53 de la Ley Federal del Trabajo.
    </p>

    <p class="text-justify">
        Así mismo manifiesto que el último puesto que desempeñé fue el de: <span class="fw-bold">{{ $empleado->puesto->nombre_puesto ?? 'NO ESPECIFICADO' }}</span>, habiendo ingresado a prestar mis servicios el día <span class="fw-bold">{{ $fecha_ingreso_letra }}</span>.
    </p>

    <p class="text-justify">
        Durante el tiempo que presté mis servicios, nunca sufrí riesgo de trabajo. De igual modo, manifiesto que a la fecha no se me adeuda cantidad alguna por concepto de
        @if($esContratoDeHonorarios)
            <span class="fw-bold">honorarios devengados</span>, ni ninguna otra contraprestación derivada de los servicios prestados.
        @else
            <span class="fw-bold">vacaciones, prima vacacional, aguinaldo, séptimos días, días de descanso obligatorio, fondo de ahorro, salarios devengados, reparto de utilidades,</span> así como cualquier otra prestación a la que pudiera haber tenido derecho, ya que todo lo anterior se me cubrió puntualmente y en la forma establecida por la ley al recibir el pago de mi finiquito correspondiente.
        @endif
    </p>

    <p class="text-justify">
        Por último y en virtud de mi renuncia voluntaria, no me reservo acción o derecho que ejercitar de ninguna naturaleza en el futuro, ni en contra suya, ni de sus representantes, ni de la fuente de trabajo.
    </p>

    <div class="text-center mt-5">
        <p>ATENTAMENTE</p>
        <div class="signature-line"></div>
        <p class="fw-bold">{{ $empleado->nombre_completo }}</p>
    </div>

</body>
</html>

