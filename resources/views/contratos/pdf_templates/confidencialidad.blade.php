<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Contrato de Confidencialidad y Privacidad</title>
    <style>
        /* INICIO DE AJUSTES PARA AHORRAR ESPACIO */
        @page {
            /* 1. Reducimos los márgenes de toda la página */
            margin: 2cm; 
        }

        body { 
            font-family: Arial, sans-serif; 
            /* 2. Reducimos el interlineado para compactar los párrafos */
            line-height: 1.3; 
            /* 3. Reducimos el tamaño de la fuente ligeramente */
            font-size: 12px; 
        }
        
        p {
            /* 4. Controlamos el espacio entre párrafos para que sea consistente y compacto */
            margin-top: 0;
            margin-bottom: 0.8em; /* Un poco menos que el espacio por defecto */
            text-align: justify;
        }
        /* FIN DE AJUSTES */

        h3, h4 { text-align: center; }
        .bold { font-weight: bold; }
    </style>
</head>
<body>
    <h3>CONTRATO DE CONFIDENCIALIDAD Y PRIVACIDAD.</h3>
    <p>
        QUE CELEBRAN POR UNA PARTE @if($patron->tipo_persona === 'fisica')EL C. @endif<span class="bold">{{ $patron->razon_social ?? 'PATRÓN NO ESPECIFICADO' }}</span>, QUIEN OSTENTA LA CALIDAD DE PATRÓN Y RESPONSABLE DEL RESGUARDO DE DATOS PERSONALES Y DE INFORMACIÓN FINANCIERA Y CREDITICIA;
        Y POR OTRA PARTE LA C. <span class="bold">{{ $empleado->nombre_completo ?? 'EMPLEADO NO ESPECIFICADO' }}</span>, EN SU CARÁCTER DE EMPLEADO MISMOS QUE LO SUJETAN A LAS SIGUIENTES DECLARACIONES Y CLAUSULAS.
    </p>

    <h4>D E C L A R A C I O N E S:</h4>
    <p>
        <span class="bold">PRIMERA:</span> Declara @if($patron->tipo_persona === 'fisica')el C. @endif<span class="bold">{{ $patron->razon_social ?? '' }}</span>, Ser una persona {{ $patron->tipo_persona ?? '' }} con actividad empresarial, cuya actividad principal es {{ $patron->actividad_principal ?? 'otorgar financiamientos a través de microcréditos dirigidos a personas físicas tendientes a fomentar e incrementar su liquidez' }}, con domicilio en <span class="bold">{{ $patron->direccion_fiscal ?? 'DIRECCIÓN FISCAL NO ESPECIFICADA' }}</span>, y que derivado de sus funciones y ocupaciones tiene la obligación legal de resguardar datos personales, así como diversos manuales y métodos de operación.
    </p>
    <p>
        <span class="bold">SEGUNDA:</span> Declara el EMPLEADO tener y contar con los conocimientos necesarios para lo que fue contratado por @if($patron->tipo_persona === 'fisica')el C. @endif<span class="bold">{{ $patron->razon_social ?? '' }}</span>, en relación con lo descrito en la declaración primera del presente y por ello el presente contrato lo sujetan a las siguientes:
    </p>

    <h4>C L A U S U L A S:</h4>
    <p>
        <span class="bold">PRIMERA: CONFIDENCIALIDAD PACTADA.</span> EL EMPLEADO se obliga en forma irrevocable ante @if($patron->tipo_persona === 'fisica')el C. @endif<span class="bold">{{ $patron->razon_social ?? '' }}</span>, a no revelar, divulgar o difundir, facilitar, transmitir, bajo cualquier forma, a ninguna persona física o jurídica, sea esta pública o privada, y a no utilizar para su propio beneficio o para beneficio de cualquier otra persona física o jurídica, pública o privada, toda la información relacionada con las funciones del @if($patron->tipo_persona === 'fisica')C. @endif<span class="bold">{{ $patron->razon_social ?? '' }}</span>, descritas en la declaración primera del presente así como cualquier otra información vinculada con las funciones y/o el giro comercial.
    </p>
    <p>
        EL EMPLEADO asume la obligación de confidencialidad acordada en la presente cláusula por todo el plazo de la relación laboral y por un plazo adicional de 15 años contados a partir de la extinción del contrato de trabajo.
    </p>
    <p>
        <span class="bold">SEGUNDA: INCUMPLIMIENTO.</span> Se deja constancia que la violación o el incumplimiento de la obligación de confidencialidad a cargo de EL EMPLEADO, así como la falsedad de la información que pudiere brindar a terceros, podrá resultar incluso en responsabilidad penal, siendo facultad del @if($patron->tipo_persona === 'fisica')C. @endif<span class="bold">{{ $patron->razon_social ?? '' }}</span> formular la denuncia del caso y constituirse en parte querellante. Además de ello, la Facultad del @if($patron->tipo_persona === 'fisica')C. @endif<span class="bold">{{ $patron->razon_social ?? '' }}</span> de requerir el resarcimiento económico de los daños ocasionados y/o el perjuicio sufrido conforme.
    </p>
    <p>
        <span class="bold">TERCERA: COMPENSACIÓN RESARCIMIENTO.</span> Queda expresamente aclarado que la remuneración que EL EMPLEADO percibe de <span class="bold">{{ $patron->razon_social ?? '' }}</span>, compensa a las obligaciones de confidencialidad, aclaradas en este documento. De igual forma, queda expresamente convenido que el incumplimiento total o parcial imputable al EMPLEADO con relación a las obligaciones de confidencialidad asumidas por el presente, facultará a @if($patron->tipo_persona === 'fisica')él C. @else la empresa @endif<span class="bold">{{ $patron->razon_social ?? '' }}</span> para disponer la extinción del contrato de trabajo con justa causa.
    </p>
    <p>
        <span class="bold">CUARTA:</span> Ambas partes están conformes que en este acto no existe dolo, lesión, error o violencia que pueda invalidarlo conforme a la Ley.
    </p>
    <p>
        <span class="bold">QUINTA:</span> Leídas y explicadas que fueron todas y cada una de las partes del presente contrato las partes manifiestan conformidad con las mismas, firmando este instrumento para su constancia y efectos que conforme a derecho convengan.
    </p>
    
    <p>
        Se firma el presente contrato en <span class="bold">{{ $patron->direccion_fiscal ?? 'DIRECCIÓN FISCAL NO ESPECIFICADA' }}</span>, al día {{ \Carbon\Carbon::parse($contrato->fecha_inicio)->day }} del mes de {{ \Carbon\Carbon::parse($contrato->fecha_inicio)->monthName }} del año {{ \Carbon\Carbon::parse($contrato->fecha_inicio)->year }}.
    </p>

    <br><br>
    
    <table style="width: 100%; text-align: center;">
        <tr>
            <td>EL PATRÓN</td>
            <td>EL EMPLEADO</td>
        </tr>
        <tr>
            <td style="padding-top: 2em;">_________________________</td>
            <td style="padding-top: 2em;">_________________________</td>
        </tr>
        <tr>
            <td>@if($patron->tipo_persona === 'fisica')C. @endif{{ $patron->razon_social ?? '' }}</td>
            <td>C. {{ $empleado->nombre_completo ?? '' }}</td>
        </tr>
    </table>
</body>
</html>