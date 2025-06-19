<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Carta Patronal</title>
    <style>
        @page {
            margin: 2.5cm;
            margin-top: 4.5cm; /* Margen superior más grande para el encabezado */
        }
        
        header {
            position: fixed;
            top: -4cm;
            left: 0;
            right: 0;
            height: 4cm;
            text-align: center;
            line-height: 1.2;
            font-size: 11px;
        }

        body { 
            font-family: Arial, sans-serif; 
            line-height: 1.8; 
            font-size: 12px;
            text-align: justify;
        }

        .bold {
            font-weight: bold;
        }

        .footer-info {
            font-size: 10px;
            text-align: left;
            line-height: 1.4;
        }

        .signature-block {
            margin-top: 5em;
            text-align: center;
        }
    </style>
</head>
<body>
    @php
        setlocale(LC_TIME, 'es_ES.UTF-8', 'Spanish_Spain.1252');
        $fechaActual = \Carbon\Carbon::now()->translatedFormat('d \d\e F \d\e\l Y');
    @endphp

    <header>
        <p>
            <span class="bold">{{ $patron->razon_social ?? 'RAZÓN SOCIAL NO ESPECIFICADA' }}</span><br>
            RFC: {{ $patron->rfc ?? 'RFC NO ESPECIFICADO' }}<br>
            NPIE: {{ $patron->registro_patronal ?? 'REGISTRO PATRONAL NO ESPECIFICADO' }}<br>
            DIRECCION: {{ $patron->direccion_fiscal ?? 'DIRECCIÓN FISCAL NO ESPECIFICADA' }}
            <br>
            
            
        </p>
        <hr>
    </header>

    <main>
        <p style="text-align: right;">
            <span class="bold">Lugar y Fecha de expedición:</span> {{ optional($empleado->sucursal)->municipio ?? 'Texcoco, Estado de México' }}, a {{ $fechaActual }}.
        </p>

        <p class="bold" style="margin-top: 3em; margin-bottom: 3em;">
            A QUIEN CORRESPONDA:
        </p>

        <p>
            Por medio de la presente se <span class="bold">HACE CONSTAR</span> que el (la) C. <span class="bold">{{ $empleado->nombre_completo ?? 'EMPLEADO NO ESPECIFICADO' }}</span>, con Número de Seguridad Social <span class="bold">{{ $empleado->nss ?? 'NSS NO ESPECIFICADO' }}</span> y R.F.C. <span class="bold">{{ $empleado->rfc ?? 'RFC NO ESPECIFICADO' }}</span>, ingresó a laborar en esta empresa desde el <span class="bold">{{ isset($empleado->fecha_alta_imss) ? \Carbon\Carbon::parse($empleado->fecha_alta_imss)->translatedFormat('d \d\e F \d\e\l Y') : 'FECHA NO ESPECIFICADA' }}</span> desempeñando el puesto de <span class="bold">{{ $empleado->puesto->nombre_puesto ?? 'PUESTO NO ESPECIFICADO' }}</span>.
        </p>

        <p>
    Actualmente se encuentra laborando con nosotros, cumpliendo un horario de <span class="bold">{{ $horarioTexto }}</span> y percibiendo un Salario Diario de <span class="bold">$ {{ number_format($empleado->sdi ?? 0, 2) }} M.N.</span>
</p>
        
        <p>
            Asimismo, se informa que el (la) trabajador(a) se encuentra debidamente registrado(a) ante el Instituto Mexicano del Seguro Social (IMSS) con los datos arriba mencionados.
        </p>

        <p>
            Se extiende la presente para los fines que al interesado(a) convengan, principalmente para la realización de trámites ante el IMSS.
        </p>

        <div class="signature-block">
            <p>ATENTAMENTE</p>
            <br><br><br>
            <p>____________________________________</p>
            <div class="footer-info">
                <span class="bold">CARLOS ALBERTO MARTINEZ CURIEL</span><br>
                <span class="bold">CONTADOR GENERAL</span> {{ $patron->nombre_comercial ?? 'EMPRESA NO ESPECIFICADA' }}<br>
                <span class="bold">CORREO:</span> curiel@facturame.org
                <span class="bold">TELÉFONO:</span> 5530753784
            </div>
        </div>
    </main>
</body>
</html>