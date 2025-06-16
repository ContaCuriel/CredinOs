<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Contrato por Sueldo Variable</title>
    {{-- Estilos unificados basados en tu contrato genérico --}}
    <style>
        body { font-family: 'DejaVu Sans', sans-serif; margin: 35px; font-size: 9px; line-height: 1.2; }
        .text-center { text-align: center; }
        .text-justify { text-align: justify; }
        .text-bold { font-weight: bold; }
        .uppercase { text-transform: uppercase; }
        .etiqueta { font-weight: bold; }
        h1.contract-title { font-size: 11px; font-weight: bold; text-transform: uppercase; text-align: center; margin-bottom: 15px; }
        h2.section-title { font-size: 10px; font-weight: bold; text-transform: uppercase; text-align: center; margin-top: 15px; margin-bottom: 8px; }
        p { margin-top: 0; margin-bottom: 6px; text-align: justify; }
        .declaraciones-list { padding-left: 0; list-style-type: none; }
        .declaraciones-list > li { margin-bottom: 8px; }
        .declaraciones-list > li > ol { padding-left: 20px; list-style-type: upper-alpha; margin-top: 2px; }
        .declaraciones-list > li > ol > li { margin-bottom: 4px; }
        .clausula-item { margin-bottom: 8px; text-align: justify; }
        .clausula-numero { font-weight: bold; text-transform: uppercase; }
        .clausula-item ul { padding-left: 30px; margin-top: 5px; margin-bottom: 5px;}
        .firmas-container { margin-top: 30px; width: 100%; overflow: auto; page-break-inside: avoid; }
        .firma { width: 45%; text-align: center; }
        .firma-izquierda { float: left; margin-left: 2%; }
        .firma-derecha { float: right; margin-right: 2%; }
        .linea-firma { border-top: 1px solid #000; margin-top: 40px; width: 80%; margin-left: auto; margin-right: auto; margin-bottom: 5px; }
    </style>
</head>
<body>
    @php
        $denominacionPatron = "EL CONTRATANTE";
        $denominacionTrabajador = "EL PRESTADOR DE SERVICIOS PROFESIONALES";
        $lugarFirmaCompleto = 'Texcoco, Estado de México';
        // Se usa la fecha actual para la firma del documento
        $fechaFirma = now()->day . ' de ' . Str::ucfirst(now()->monthName) . ' de ' . now()->year;
    @endphp

    <h1 class="contract-title text-center">CONTRATO DE PRESTACIÓN DE SERVICIOS PROFESIONALES</h1>

    <p class="text-justify">
        CONTRATO DE PRESTACIÓN DE SERVICIOS PROFESIONALES QUE CELEBRAN, POR UNA PARTE, <span class="etiqueta uppercase">{{ $patron->razon_social ?? 'PATRÓN NO DEFINIDO' }}</span>,
        @if ($patron->tipo_persona == 'moral' && $patron->representante_legal)
            REPRESENTADA EN ESTE ACTO POR EL C. <span class="etiqueta uppercase">{{ $patron->representante_legal }}</span>,
        @elseif ($patron->tipo_persona == 'fisica')
            POR SU PROPIO DERECHO,
        @endif
        A QUIEN EN LO SUCESIVO SE LE DENOMINARÁ "<span class="uppercase">{{ $denominacionPatron }}</span>", Y POR LA OTRA PARTE, EL C. <span class="etiqueta uppercase">{{ $empleado->nombre_completo ?? 'EMPLEADO NO DEFINIDO' }}</span>, A QUIEN EN LO SUCESIVO SE LE DENOMINARÁ "<span class="uppercase">{{ $denominacionTrabajador }}</span>", AL TENOR DE LAS SIGUIENTES:
    </p>

    <h2 class="section-title">DECLARACIONES</h2>
<ol class="declaraciones-list">
     

        <li>
            <p><span class="etiqueta">I. DECLARA "<span class="uppercase">{{ $denominacionPatron }}</span>":</span></p>
            <ol>
                <li>Ser una persona <span class="etiqueta uppercase">{{$patron->tipo_persona}}</span> con actividad empresarial, cuya principal actividad es la de <span class="etiqueta uppercase">{{ $patron->actividad_principal ?: '________________' }}</span>.</li>
                <li>Tener su domicilio ubicado en <span class="etiqueta uppercase">{{ $patron->direccion_fiscal ?: '________________' }}</span>.</li>
                <li>Encontrarse inscrito en el Registro Federal de Contribuyentes con la clave: <span class="etiqueta uppercase">{{ $patron->rfc ?: '________________' }}</span>.</li>
                <li>Contar con capacidad legal para obligarse y hacerlo de manera voluntaria.</li>
                <li>Que es su voluntad contratar los servicios profesionales consistentes en <span class="etiqueta uppercase">{{ $empleado->puesto ? $empleado->puesto->nombre_puesto : 'ASESOR(A) DE CRÉDITO' }}</span>, por la temporalidad del <span class="etiqueta">{{ $contrato->fecha_inicio->translatedFormat('d \d\e F \d\e Y') }}</span> al <span class="etiqueta">{{ $contrato->fecha_fin->translatedFormat('d \d\e F \d\e Y') }}</span>.</li>
                <li>Que cuenta con los recursos económicos suficientes para cubrir a "<span class="uppercase">{{ $denominacionTrabajador }}</span>", los honorarios correspondientes a los servicios que en virtud de este instrumento se formalicen.</li>
            </ol>
        </li>
        <li>
            <p><span class="etiqueta">II. DECLARA "<span class="uppercase">{{ $denominacionTrabajador }}</span>":</span></p>
            <ol>
                <li>Tener capacidad jurídica para contratar y obligarse en los términos del presente contrato, que cuenta con Clave Única de Población: <span class="etiqueta uppercase">{{ $empleado->curp ?: '________________' }}</span> y con Registro Federal de Contribuyentes <span class="etiqueta uppercase">{{ $empleado->rfc ?: '________________' }}</span>.</li>
                <li>Señalar como su domicilio para los fines y efectos legales de este contrato el ubicado en <span class="etiqueta uppercase">{{ $empleado->direccion ?: '________________' }}</span>.</li>
                <li>Ser persona física de nacionalidad mexicana, que cuenta con <span class="etiqueta">{{ $empleado->fecha_nacimiento ? $empleado->fecha_nacimiento->age : '____' }}</span> años de edad, de sexo <span class="etiqueta uppercase">{{ $empleado->sexo ?: '____' }}</span>, estado civil <span class="etiqueta uppercase">{{ $empleado->estado_civil ?: '____' }}</span> y sin impedimento legal para obligarse.</li>
                <li>Que reconoce expresamente que el motivo de su contratación por parte de <span class="etiqueta uppercase">{{ $patron->razon_social }}</span> es única y exclusivamente para la prestación de servicios profesionales eventuales y que la vigencia del presente contrato no generará derecho alguno de índole laboral, por lo que su relación jurídica será de carácter civil.</li>
                <li>Que cuenta con los conocimientos y experiencia necesaria para desempeñar el puesto de <span class="etiqueta uppercase">{{ $empleado->puesto ? $empleado->puesto->nombre_puesto : 'ASESOR(A) DE CRÉDITO' }}</span>.</li>
            </ol>
        </li>
    </ol>



    {{-- INICIO DE LAS CLÁUSULAS INTEGRADAS --}}
    <p class="text-justify" style="text-align:center; font-weight:bold;">D E C L A R A N “A M B A S P A R T E S”</p>
    <div class="clausula-item">
        <p><span class="clausula-numero">PRIMERA.-</span> SE RECONOCEN MUTUAMENTE LA PERSONALIDAD CON LA QUE SE OSTENTAN, POR LO TANTO DESDE ESTE MOMENTO SE OBLIGAN A NO OBJETARLA NI REVOCARLA EN LO FUTURO POR NINGUNA CAUSA, Y POR CONSIGUIENTE NO PODRÁN ALEGAR POSTERIORMENTE LA NULIDAD DE LOS ACTOS AQUÍ ESTABLECIDOS, POR DICHO CONCEPTO Y EXPRESAN SU VOLUNTAD PARA LA CELEBRACIÓN DEL PRESENTE CONTRATO, RECONOCIENDO Y ACEPTANDO QUE EL MISMO SE RIGE POR LO DISPUESTO POR LOS ARTÍCULOS 7.825, 7.826, 7.827, 7.828 Y 7.829 Y DEMÁS RELATIVOS Y APLICABLES DEL CÓDIGO CIVIL PARA EL ESTADO DE MÉXICO EN VIGOR.</p>
        <p><span class="clausula-numero">SEGUNDA.-</span> -HABER NEGOCIADO LIBREMENTE LOS TÉRMINOS DE ESTE DOCUMENTO, QUE NO EXISTE NINGUNA LIMITANTE A SU INTENCIÓN O VOLUNTAD Y CONSECUENTEMENTE SE ENCUENTRAN CON CAPACIDAD Y APTITUD LEGAL PARA CELEBRAR EL PRESENTE CONTRATO.</p>
        <p><span class="clausula-numero">TERCERA.-</span> -MANIFIESTAN LAS PARTES QUE ESTÁN DE ACUERDO CON LAS DECLARACIONES ANTERIORES, Y SIENDO SU DESEO OBLIGARSE RECÍPROCAMENTE, AMBAS PARTES ACUERDAN SUJETAR ESTANDO EN COMÚN ACUERDO, LAS PARTES OTORGAN PARA EL CUMPLIMIENTO DEL PRESENTE CONTRATO, LAS SIGUIENTES:</p>
    </div>

    <h2 class="section-title">CLÁUSULAS</h2>

    <div class="clausula-item">
        <p><span class="clausula-numero">PRIMERA.-</span> “EL PRESTADOR” se obliga a proporcionar a “EL CONTRATANTE”, sus servicios de manera personal e independiente para realizar las actividades consistentes en <span class="etiqueta uppercase">{{ $empleado->puesto ? $empleado->puesto->nombre_puesto : 'ASESOR(A) DE CRÉDITO' }}</span>.</p>
        <p>LOS SERVICIOS PROFESIONALES OBJETO DE ESTE CONTRATO SE EJECUTARÁN EN UN PERIODO A PARTIR DEL DÍA <span class="etiqueta">{{ $contrato->fecha_inicio->translatedFormat('d \d\e F \d\e Y') }}</span> al <span class="etiqueta">{{ $contrato->fecha_fin->translatedFormat('d \d\e F \d\e Y') }}</span>, SIN EMBARGO, A JUICIO DE EL “CONTRATANTE”, O DE “EL PRESTADOR DE SERVICIOS PROFESIONALES”, DESEAN DAR POR TERMINADO EL CONTRATO CON ANTELACIÓN A SU VENCIMIENTO, PODRÁN HACERLO, DANDO AVISO A LA OTRA PARTE, CON UNA ANTICIPACIÓN DE 15 DÍAS NATURALES, PARA EFECTO DE QUE SE HAGA LA LIQUIDACIÓN CORRESPONDIENTE, QUE INCLUIRÁ LA ENTREGA DE LOS EXPEDIENTES, DOCUMENTOS E INFORMES QUE AL MISMO CORRESPONDAN.</p>
    </div>

    <div class="clausula-item">
        <p><span class="clausula-numero">SEGUNDA.-</span> LAS OBLIGACIONES CONTENIDAS EN ESTE CONTRATO NO IMPLICAN PARA “EL PRESTADOR” RESTRICCIÓN AL LIBRE EJERCICIO DE LA PROFESIÓN, PERO ELLO NO DEBE AFECTAR LA ATENCIÓN QUE ESTÁ OBLIGADO A PROPORCIONAR POR LOS SERVICIOS CONTRATADOS CON “EL CONTRATANTE”.</p>
    </div>
    
    <div class="clausula-item">
        <p><span class="clausula-numero">TERCERA.-</span> EN LA PRESTACIÓN DEL SERVICIO, "EL PRESTADOR DE SERVICIOS PROFESIONALES” SE OBLIGA CON “EL CONTRATANTE”, A CUMPLIR LO SIGUIENTE:</p>
        <ol type="A">
            <li>ANTES DE INICIAR LA PRESTACIÓN DEL SERVICIO, DARLE A CONOCER SU CLAVE DE INSCRIPCIÓN AL REGISTRO FEDERAL DE CONTRIBUYENTES Y REGIMEN FISCAL.</li>
            <li>FIRMAR LOS RECIBOS CORRESPONDIENTES, QUE LE SEAN PROPORCIONADOS POR “CONTRATANTE”.</li>
            <li>PRESENTAR TODA LA DOCUMENTACIÓN PERSONAL NECESARIA PARA QUE SE INTEGRE EL EXPEDIENTE CORRESPONDIENTE.</li>
            <li>A RENDIR LOS INFORMES QUE RESPECTO DEL TRABAJO CONTRATADO LE SEAN SOLICITADOS.</li>
        </ol>
    </div>

    <div class="clausula-item">
        <p><span class="clausula-numero">CUARTA.-</span> “EL CONTRATANTE” SE OBLIGA A PAGAR A “EL PRESTADOR DE SERVICIOS PROFESIONALES” POR CONCEPTO DE HONORARIO UN PAGO MENSUAL VARIABLE, EL CUAL ESTARÁ SUJETO A ALCANCE DE META DE COLOCACIÓN MENSUAL, LA CUAL SERÁ ESTABLECIDA A INICIO DE MES Y SE INFORMARÁ DE MANERA PUNTUAL A “EL PRESTADOR DE SERVICIOS PROFESIONALES”. CORRESPONDIÉNDOLE A CADA UNA DE LAS PARTES LAS OBLIGACIONES FISCALES QUE LAS LEYES MEXICANAS LES ASIGNAN.</p>
        <p>DICHO PAGO SE CALCULARÁ DE LA SIGUIENTE MANERA:</p>
        <ul>
            <li>CUANDO EL ALCANCE MENSUAL CORRESPONDA DEL 0% AL 60.9% DE SU META DE COLOCACIÓN, SE LE OTORGARA UN PAGO MENSUAL POR LA CANTIDAD DE $6,000.00 (SEIS MIL PESOS 00/100 M.N.).</li>
            <li>CUANDO EL ALCANCE MENSUAL CORRESPONDA DEL 61% AL 80.9% DE SU META DE COLOCACIÓN, SE LE OTORGARA UN PAGO MENSUAL POR LA CANTIDAD DE $7,000.00 (SIETE MIL PESOS 00/100 M.N.).</li>
            <li>CUANDO EL ALCANCE MENSUAL CORRESPONDA DEL 81% AL 99.9% DE SU META DE COLOCACIÓN, SE LE OTORGARA UN PAGO MENSUAL POR LA CANTIDAD DE $8,000.00 (OCHO MIL PESOS 00/100 M.N.).</li>
            <li>CUANDO EL ALCANCE MENSUAL CORRESPONDA AL 100% O POR ARRIBA DE SU META DE COLOCACIÓN, SE LE OTORGARA UN PAGO MENSUAL POR LA CANTIDAD DE $9,000.00 (NUEVE MIL PESOS 00/100 M.N.).</li>
        </ul>
        <p>DICHO PAGO SE REALIZARÁ DE LA SIGUIENTE MANERA:</p>
        <ul>
            <li>PRIMERA QUINCENA, SE OTORGARÁ A “EL PRESTADOR DE SERVICIOS PROFESIONALES”LA CANTIDAD DE $3,000.00 (TRES MIL PESOS 00/100 M.N.).</li>
            <li>SEGUNDA QUINCENA, SE OTORGARÁ A “EL PRESTADOR DE SERVICIOS PROFESIONALES” LA CANTIDAD RESTANTE AL HONORARIO QUE LE CORRESPONDA DE ACUERDO AL ESQUEMA ANTERIORMENTE MENCIONADO.</li>
        </ul>
        <p>LA CANTIDAD RETRIBUYE A “EL PRESTADOR DE SERVICIOS PROFESIONALES” TANTO POR LA PRESTACIÓN COMO POR LA CALIDAD Y EL TIEMPO QUE LE DEDIQUE A LA REALIZACIÓN DE LOS SERVICIOS OBJETO DEL PRESENTE CONTRATO, POR LO QUE NO PODRÁ EXIGIR MAYOR RETRIBUCIÓN POR NINGÚN OTRO CONCEPTO.</p>
    </div>
    
    <div class="clausula-item">
        <p><span class="clausula-numero">QUINTA.-</span> "EL PRESTADOR DE SERVICIOS PROFESIONALES” NO TENDRÁ DERECHO A NINGUNA OTRA PERCEPCIÓN ECONÓMICA DIVERSA A LA MENCIONADA EN LA CLÁUSULA PRECEDENTE Y EN CASO DE QUE EL PRESENTE CONTRATO SE DÉ POR TERMINADO EN FORMA ANTICIPADA, INDEPENDIENTEMENTE DE LA CAUSA, LA RESPONSABILIDAD DE “EL CONTRATANTE” COMPRENDERÁ EXCLUSIVAMENTE LOS HONORARIOS QUE SE HAYAN GENERADO HASTA LA FECHA DE LA TERMINACIÓN Y QUE NO SE HUBIESEN PAGADO PREVIAMENTE A "EL PRESTADOR DE SERVICIOS PROFESIONALES”.</p>
        
        <p>ASÍ MISMO, SERÁ RESPONSABILIDAD DE “EL PRESTADOR DE SERVICIOS PROFESIONALES”, PAGAR EL IMPUESTO SOBRE LA RENTA EN TÉRMINOS DE LO DISPUESTO EN EL ARTÍCULO 110, FRACCIÓN V DEL CAPÍTULO I DEL TÍTULO IV DE LA LEY DEL IMPUESTO SOBRE LA RENTA.</p>

        <p><span class="clausula-numero">SEXTA.-</span> EL LUGAR DE PAGO SERÁ EN LAS OFICINAS DE {{ strtoupper($patron->razon_social) }}, UBICADAS EN {{ strtoupper($patron->direccion_fiscal) }}, DOMICILIO CONOCIDO POR LAS PARTES.</p>
        
        <p><span class="clausula-numero">SÉPTIMA.-</span> EL PRESTADOR DEL SERVICIO RECONOCE Y DESLINDA A “EL CONTRATANTE”, EN EL
SENTIDO DE QUE EL PRESENTE CONTRATO, NO CONTEMPLA EN NINGUNO DE SUS TÉRMINOS
LO ESTIPULADO EN EL ARTÍCULO 20 DE LA LEY FEDERAL DEL TRABAJO, POR LO QUE SE
ENTIENDE QUE NO EXISTE RELACIÓN DE SUPRASUBORDINACIÓN ENTRE “EL CONTRATANTE” Y
"EL PRESTADOR DE SERVICIOS PROFESIONALES¨.</p>
        
<p><span class="clausula-numero">OCTAVA.-</span> LAS PARTES CONVIENEN QUE “EL CONTRATANTE” EN CUALQUIER MOMENTO PODRÁ
SUPERVISAR Y VIGILAR LA ADECUADA PRESTACIÓN DE LOS SERVICIOS MATERIA DEL
PRESENTE INSTRUMENTO JURÍDICO; SERVICIOS QUE DEBERÁN PRESTARSE A SU ENTERA
SATISFACCIÓN.</p>

<p><span class="clausula-numero">NOVENA.-</span> “EL CONTRATANTE” PROPORCIONARÁ A “EL PRESTADOR” LA INFORMACIÓN Y
DOCUMENTOS QUE SEAN NECESARIOS PARA BRINDAR LOS SERVICIOS CONTRATADOS.</p>

<p><span class="clausula-numero">DÉCIMA.-</span> “EL PRESTADOR DE SERVICIOS PROFESIONALES”, SERÁ EL ÚNICO RESPONSABLE DE
LA EJECUCIÓN DE LOS SERVICIOS, POR LO QUE SE OBLIGA A PRESTARLOS CONFORME A LO
ESTIPULADO EN ESTE CONTRATO, ASÍ COMO A APLICAR EN SU MÁXIMA MEDIDA LOS
CONOCIMIENTOS, PERICIA Y EXPERIENCIA QUE POSEE.</p>

<p><span class="clausula-numero">DÉCIMA PRIMERA.-</span> CONVIENEN EXPRESAMENTE LAS PARTES QUE EL INCUMPLIMIENTO DE
CUALQUIERA DE LAS OBLIGACIONES CONSIGNADAS EN ESTE CONTRATO, FACULTA A “EL
CONTRATANTE” A RESCINDIRLO UNILATERALMENTE SIN NECESIDAD DE DECLARACIÓN
JUDICIAL Y SIN RESPONSABILIDAD ALGUNA, BASTANDO LA NOTIFICACIÓN QUE AL EFECTO SE
LE HAGA A "EL PRESTADOR DE SERVICIOS PROFESIONALES” SIN NECESIDAD DE PREVIO AVISO.</p>

<p><span class="clausula-numero">DÉCIMA SEGUNDA.-</span> “EL PRESTADOR DE SERVICIOS PROFESIONALES” SE OBLIGA A INFORMAR
A “EL CONTRATANTE” CUANTAS VECES LE SEA REQUERIDO, DEL ESTADO QUE GUARDA EL
DESARROLLO DE LAS ACTIVIDADES CONTRATADAS, ASÍ COMO A RENDIR UN INFORME GENERAL
AL TÉRMINO DEL PRESENTE CONTRATO.</p>

<p><span class="clausula-numero">DÉCIMA TERCERA.-</span> SON CAUSAS DE RESCISIÓN, LAS SIGUIENTES:</p>
     <ol type="A">
<li>QUE "EL PRESTADOR DE SERVICIOS PROFESIONALES” DEJE DE PRESTAR LOS INFORMES
QUE LE SEAN SOLICITADOS POR “EL CONTRATANTE”.</li>
<li>QUE “EL PRESTADOR DE SERVICIOS PROFESIONALES” ABANDONE LA PRESTACIÓN DE SUS
SERVICIOS O NO CUMPLA CON ELLOS EN LOS PLAZOS ESTABLECIDOS POR “EL CONTRATANTE”.</li>
<li>LA COMISIÓN POR PARTE DE "EL PRESTADOR DE SERVICIOS PROFESIONALES” DE ALGÚN
DELITO QUE AMERITE PENA CORPORAL.</li>
<li>QUE INCUMPLA CON LO ESTIPULADO EN CUALQUIERA DE LAS CLÁUSULAS ESTABLECIDAS
EN EL PRESENTE CONTRATO.</li>
</ol>

<p><span class="clausula-numero">DÉCIMA CUARTA.-</span> “EL PRESTADOR DE SERVICIOS PROFESIONALES” SE OBLIGA A TENER
INFORMADO A “EL CONTRATANTE”, DE TODAS LAS ACTIVIDADES PROFESIONALES OBJETO DE
ESTE CONTRATO, POR CONDUCTO DE LA O LAS PERSONAS QUE DESIGNEN, ASIMISMO
REALIZAR LA ENTREGA DE UN INFORME MENSUAL SOBRE LAS ACTIVIDADES DESARROLLADAS.</p>

<p><span class="clausula-numero">DÉCIMA QUINTA.-</span> CONFIDENCIALIDAD DE LOS SERVICIOS. “EL PRESTADOR DE SERVICIOS PROFESIONALES” SE OBLIGA A GUARDAR ESTRICTA CONFIDENCIALIDAD Y A NO REPRODUCIR, NI REVELAR O PERMITIR QUE SEA REVELADA A NINGUNA PERSONA O ENTIDAD LA INFORMACIÓN CONFIDENCIAL, Y TOMAR LAS MEDIDAS ADECUADAS PARA EL ESTRICTO CUMPLIMIENTO DE ESTA OBLIGACIÓN CON TODAS LAS PERSONAS QUE TENGA ACCESO A LA
MISMA, YA SEA QUE ESTA SE ENCUENTRE EN PAPEL, MEDIOS MAGNÉTICOS U OTROS, EN QUE
SE CONTENGA DATOS RELATIVOS A LAS ACTIVIDADES QUE SE REALICEN, Y QUE PUDIERAN
CONTRAVENIR LAS NORMAS DE SECRETO Y RESERVA DE LA INFORMACIÓN CONFIDENCIAL.
LA OBLIGACIÓN A QUE SE REFIERE ESTA CLAUSULA ES DE CARÁCTER PERMANENTE Y NO
ESTARÁ CONDICIONADA A LA TERMINACIÓN, POR CUALQUIER CAUSA, DEL PRESENTE
CONTRATO.
EN CASO DE QUE “EL PRESTADOR” CONTRAVENGA ESTA DISPOSICIÓN, “EL CONTRATANTE” SE
RESERVA EL DERECHO DE RESCINDIR EL PRESENTE CONTRATO Y EJERCER LAS ACCIONES
CIVILES Y PENALES QUE PROCEDAN.</p>

<p><span class="clausula-numero">DÉCIMA SEXTA.-</span> “EL CONTRATANTE” PODRÁ EN TODO TIEMPO, A TRAVÉS DE LOS
REPRESENTANTES QUE AL EFECTO DESIGNE, VERIFICAR, SUPERVISAR Y VIGILAR QUE LOS
SERVICIOS OBJETO DE ESTE CONTRATO SE REALICEN CONFORME A LOS TÉRMINOS
CONVENIDOS Y, EN SU CASO, HARÁ A “EL PRESTADOR” LAS OBSERVACIONES QUE ESTIME
CONVENIENTES RELACIONADAS CON SU EJECUCIÓN, A FIN DE QUE SE AJUSTE A LO PACTADO.</p>

<p><span class="clausula-numero">DÉCIMA SEPTIMA.-</span> “EL CONTRATANTE” PODRÁ SUSPENDER LA PRESTACIÓN DE LOS
SERVICIOS OBJETO DEL PRESENTE CONTRATO, PARA ELLO DARÁ AVISO POR ESCRITO CON
UNA ANTICIPACIÓN DE DIEZ DÍAS HÁBILES A “EL PRESTADOR DE SERVICIOS PROFESIONALES”.</p>

<p><span class="clausula-numero">DÉCIMA OCTAVA.-</span>  LAS PARTES MANIFIESTAN QUE EL PRESENTE CONTRATO ES PRODUCTO DE
SU BUENA FE, POR LO QUE REALIZARAN TODAS LAS ACCIONES POSIBLES PARA SU
CUMPLIMIENTO, PERO EN CASO DE PRESENTARSE ALGUNA DISCREPANCIA SOBRE SU
INTERPRETACIÓN O CUMPLIMIENTO SE SOMETERÁN A LO QUE DISPONE LOS ARTÍCULOS 7.825
AL 7.835 DEL CÓDIGO CIVIL VIGENTE EN EL ESTADO DE MÉXICO, RENUNCIANDO A CUALQUIER
OTRO FUERO QUE, POR RAZÓN A SU DOMICILIO, PRESENTE O FUTURO, LE PUDIERA
CORRESPONDER.</p>

<p><span class="clausula-numero">DÉCIMA NOVENA.-</span>  “EL PRESTADOR” SE OBLIGA A PROPORCIONAR LOS SERVICIOS MATERIA DE
ESTE CONTRATO DEL {{ $contrato->fecha_inicio->format('d \d\e F \d\e Y') }} AL {{ $contrato->fecha_fin->format('d \d\e F \d\e Y') }}.
LA DURACIÓN DE LOS SERVICIOS NO PODRÁ SER MODIFICADA A MENOS QUE “EL
CONTRATANTE” ASÍ LO CONSIDERE, LO QUE HARÁ DEL CONOCIMIENTO DE “EL PRESTADOR”
CON CINCO DÍAS NATURALES DE ANTICIPACIÓN.</p>

        <div class="firmas-container" style="margin-top:20px;">
        <p class="text-justify">LEÍDO EL CONTRATO Y ENTERADAS LAS PARTES DEL CONTENIDO, VALOR, ALCANCE Y FUERZA LEGAL DEL PRESENTE CONTRATO, LO FIRMAN AL MARGEN Y AL CALCE PARA SU DEBIDA CONSTANCIA POR DUPLICADO, EN {{strtoupper($lugarFirmaCompleto)}}, EL DÍA {{$fechaFirma}}.</p>
    </div>

    <div class="firmas-container">
        <div class="firma firma-izquierda">
            <div class="linea-firma"></div>
            <p>"<span class="uppercase">{{ $denominacionPatron }}</span>"</p>
            <p class="uppercase">{{ $contrato->patron->razon_social ?? $contrato->patron->nombre_comercial ?? 'PATRÓN NO DEFINIDO' }}</p>
            @if ($contrato->patron->tipo_persona == 'moral' && $contrato->patron->representante_legal)
                <p class="uppercase">P.P. {{ $contrato->patron->representante_legal }}</p>
            @endif
        </div>
        <div class="firma firma-derecha">
            <div class="linea-firma"></div>
            <p>"<span class="uppercase">{{ $denominacionTrabajador }}</span>"</p>
            <p class="uppercase">{{ $empleado->nombre_completo ?? '____________________________' }}</p>
        </div>
    </div>
</body>
</html>
