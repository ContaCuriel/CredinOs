<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Contrato</title>
    <style>
        body { font-family: 'DejaVu Sans', sans-serif; margin: 35px; font-size: 9px; line-height: 1.2; } /* Reducido tamaño de fuente y line-height */
        .text-center { text-align: center; }
        .text-justify { text-align: justify; }
        .text-bold { font-weight: bold; }
        .uppercase { text-transform: uppercase; }
        .etiqueta { font-weight: bold; }
        
        h1.contract-title { font-size: 11px; font-weight: bold; text-transform: uppercase; text-align: center; margin-bottom: 15px; }
        h2.section-title { font-size: 10px; font-weight: bold; text-transform: uppercase; text-align: center; margin-top: 15px; margin-bottom: 8px; }
        
        p { margin-top: 0; margin-bottom: 6px; text-align: justify; }
        
        .declaraciones-list, .clausulas-list-main { padding-left: 0; list-style-type: none; }
        .declaraciones-list > li, .clausulas-list-main > li { margin-bottom: 8px; }
        .declaraciones-list > li > ol { padding-left: 20px; list-style-type: lower-alpha; margin-top: 2px; }
        .declaraciones-list > li > ol > li { margin-bottom: 4px; }

        .clausula-item .clausula-numero { font-weight: bold; text-transform: uppercase; }
        .clausula-item p { margin-left: 15px; } /* Indentar el texto de la cláusula */
        .clausula-item ol[type="A"] { padding-left: 35px; list-style-type: upper-alpha; }
        .clausula-item ol[type="A"] > li { margin-bottom: 3px; }

        .firmas-container { margin-top: 30px; width: 100%; overflow: auto; page-break-inside: avoid; }
        .firma { width: 45%; text-align: center; }
        .firma-izquierda { float: left; margin-left: 2%; }
        .firma-derecha { float: right; margin-right: 2%; }
        .linea-firma { border-top: 1px solid #000; margin-top: 30px; width: 80%; margin-left: auto; margin-right: auto; margin-bottom: 5px; }

        .footer-text { text-align: center; font-size: 8px; margin-top: 20px; }
    </style>
</head>
<body>
    @php
        $denominacionPatron = ($contrato->tipo_contrato == 'Honorarios') ? "EL CONTRATANTE" : "EL PATRÓN";
        $denominacionTrabajador = ($contrato->tipo_contrato == 'Honorarios') ? "EL PRESTADOR DE SERVICIOS PROFESIONALES" : "EL TRABAJADOR";

        $lugarContrato = $contrato->patron && $contrato->patron->direccion_fiscal ? (explode(',', $contrato->patron->direccion_fiscal)[1] ?? 'Texcoco') : 'Texcoco'; // Intenta tomar la ciudad del patrón
        $estadoContrato = 'Estado de México'; // Asumir o hacerlo dinámico
        $lugarFirmaCompleto = $lugarContrato . ', ' . $estadoContrato;
        $fechaFirma = $contrato->fecha_inicio ? $contrato->fecha_inicio->translatedFormat('d \d\e F \d\e Y') : now()->translatedFormat('d \d\e F \d\e Y');
        $mostrarTestigos = true; // Cambia a false si no quieres la sección de testigos por defecto
    @endphp

    <h1 class="contract-title text-center">
        @if ($contrato->tipo_contrato == 'Honorarios')
            CONTRATO DE PRESTACIÓN DE SERVICIOS PROFESIONALES
        @else
            CONTRATO INDIVIDUAL DE TRABAJO POR TIEMPO {{ strtoupper($contrato->tipo_contrato) }}
        @endif
    </h1>

    <p class="text-justify">
        QUE CELEBRAN, POR UNA PARTE, <span class="etiqueta uppercase">{{ $contrato->patron->razon_social ?? 'PATRÓN NO DEFINIDO' }}</span>,
        @if ($contrato->patron->tipo_persona == 'moral' && $contrato->patron->representante_legal)
            REPRESENTADA EN ESTE ACTO POR EL C. <span class="etiqueta uppercase">{{ $contrato->patron->representante_legal }}</span>,
        @elseif ($contrato->patron->tipo_persona == 'fisica')
            POR SU PROPIO DERECHO,
        @endif
        A QUIEN EN LO SUCESIVO Y PARA LOS EFECTOS DEL PRESENTE CONTRATO SE LE DENOMINARÁ "<span class="uppercase">{{ $denominacionPatron }}</span>",
        Y POR LA OTRA PARTE, EL C. <span class="etiqueta uppercase">{{ $empleado->nombre_completo ?? 'EMPLEADO NO DEFINIDO' }}</span>,
        A QUIEN EN LO SUCESIVO SE LE DENOMINARÁ "<span class="uppercase">{{ $denominacionTrabajador }}</span>", AL TENOR DE LAS SIGUIENTES DECLARACIONES Y CLÁUSULAS:
    </p>

    <h2 class="section-title">DECLARACIONES</h2>
    <ol class="declaraciones-list">
        <li>
            <p><span class="etiqueta">I. DECLARA "<span class="uppercase">{{ $denominacionPatron }}</span>":</span></p>
            <ol>
                <li>Ser 
                    @if ($contrato->patron->tipo_persona == 'moral')
                        una persona moral,
                    @else
                        una persona física con actividad empresarial,
                    @endif
                    cuya principal actividad es la de <span class="etiqueta uppercase">{{ $contrato->patron->actividad_principal ?: '________________' }}</span>.
                </li>
                <li>Tener su domicilio fiscal ubicado en <span class="etiqueta uppercase">{{ $contrato->patron->direccion_fiscal ?: '________________' }}</span>.</li>
                <li>Encontrarse debidamente inscrito en el Registro Federal de Contribuyentes con la clave: <span class="etiqueta uppercase">{{ $contrato->patron->rfc ?: '________________' }}</span>.</li>
                <li>Contar con capacidad legal para obligarse y hacerlo de manera voluntaria.</li>
                <li>Que es su voluntad contratar los servicios de "<span class="uppercase">{{ $denominacionTrabajador }}</span>" para desempeñar las actividades consistentes en <span class="etiqueta uppercase">{{ $empleado->puesto ? $empleado->puesto->nombre_puesto : '________________' }}</span>.</li>
                <li>Que cuenta con los recursos económicos suficientes para cubrir a "<span class="uppercase">{{ $denominacionTrabajador }}</span>", los honorarios o salarios correspondientes.</li>
            </ol>
        </li>
        <li>
            <p><span class="etiqueta">II. DECLARA "<span class="uppercase">{{ $denominacionTrabajador }}</span>":</span></p>
            <ol>
                <li>Ser persona física, de nacionalidad <span class="etiqueta">{{ $empleado->nacionalidad ?: 'Mexicana' }}</span>, tener {{ $empleado->fecha_nacimiento ? \Carbon\Carbon::parse($empleado->fecha_nacimiento)->age : '____' }} años de edad, de sexo <span class="etiqueta">{{ $empleado->sexo ?: '________________' }}</span>, estado civil <span class="etiqueta">{{ $empleado->estado_civil ?: '________________' }}</span>.</li>
                <li>Contar con Clave Única de Registro de Población: <span class="etiqueta uppercase">{{ $empleado->curp ?: '________________' }}</span> y con Registro Federal de Contribuyentes: <span class="etiqueta uppercase">{{ $empleado->rfc ?: '________________' }}</span>.</li>
                @if ($contrato->tipo_contrato != 'Honorarios')
                    <li>Contar con Número de Seguridad Social: <span class="etiqueta">{{ $empleado->nss ?: '________________' }}</span>.</li>
                @endif
                <li>Señalar como su domicilio para los fines y efectos legales de este contrato el ubicado en <span class="etiqueta uppercase">{{ $empleado->direccion ?: '________________' }}</span>.</li>
                <li>Contar con los conocimientos y experiencia necesaria para desempeñar el puesto de <span class="etiqueta uppercase">{{ $empleado->puesto ? $empleado->puesto->nombre_puesto : '________________' }}</span> y que lo acredita con cartas de recomendación, tal como: <span class="etiqueta">{{ $empleado->info_cartas_recomendacion ?: 'No especificada' }}</span>.</li>
                @if ($contrato->tipo_contrato == 'Honorarios')
                    <li>Que reconoce expresamente el motivo de su contratación por parte de "<span class="uppercase">{{ $denominacionPatron }}</span>", que es única y exclusivamente para la prestación de servicios profesionales eventuales, y que la vigencia del presente contrato no generará derecho alguno de índole laboral, por lo que su relación jurídica será de carácter civil, regulada por las disposiciones vigentes y aplicables a la prestación de servicios profesionales independientes.</li>
                @endif
                <li>No tener impedimento legal alguno para obligarse en los términos del presente contrato.</li>
            </ol>
        </li>
        <li>
            <p><span class="etiqueta">III. DECLARAN "AMBAS PARTES":</span></p>
            <ol>
                <li>Reconocerse mutuamente la personalidad con la que se ostentan y se obligan a no objetarla ni revocarla en lo futuro.</li>
                <li>Haber negociado libremente los términos de este documento, y que no existe dolo, error, mala fe, lesión, violencia o cualquier otro vicio del consentimiento que pudiera invalidar el presente contrato.</li>
                <li>Que para la celebración del presente contrato se someten a lo dispuesto por los artículos aplicables 
                    @if ($contrato->tipo_contrato == 'Honorarios')
                        del Código Civil para el Estado de México en vigor.
                    @else
                        de la Ley Federal del Trabajo en vigor.
                    @endif
                </li>
            </ol>
        </li>
    </ol>
    <p class="text-justify">Estando en común acuerdo y siendo su deseo obligarse recíprocamente, las partes otorgan y se sujetan a las siguientes:</p>

    <h2 class="section-title">CLÁUSULAS</h2>
    <div class="seccion">
        @if ($contrato->tipo_contrato == 'Honorarios')
            {{-- CLÁUSULAS PARA CONTRATO DE HONORARIOS --}}
            <div class="clausula-item">
                <p><span class="clausula-numero">PRIMERA.- OBJETO.</span> "<span class="uppercase">{{ $denominacionPatron }}</span>" encomienda a "<span class="uppercase">{{ $denominacionTrabajador }}</span>", y éste se obliga a prestar sus servicios profesionales de manera personal e independiente para realizar las actividades consistentes en <span class="etiqueta uppercase">{{ $empleado->puesto ? $empleado->puesto->nombre_puesto : '________________' }}</span>.</p>
            </div>
            <div class="clausula-item">
                <p><span class="clausula-numero">SEGUNDA.- VIGENCIA.</span> Los servicios profesionales objeto de este contrato se ejecutarán en un periodo que inicia el día <span class="etiqueta">{{ $contrato->fecha_inicio ? $contrato->fecha_inicio->translatedFormat('d \d\e F \d\e Y') : 'PENDIENTE' }}</span> al <span class="etiqueta">{{ $contrato->fecha_fin ? $contrato->fecha_fin->translatedFormat('d \d\e F \d\e Y') : 'PENDIENTE' }}</span>. La duración de los servicios no podrá ser modificada a menos que "<span class="uppercase">{{ $denominacionPatron }}</span>" así lo considere, lo que hará del conocimiento de "<span class="uppercase">{{ $denominacionTrabajador }}</span>" con cinco días naturales de anticipación. No obstante lo anterior, cualquiera de las partes podrá dar por terminado el presente contrato con antelación a su vencimiento, mediante aviso por escrito entregado a la otra parte con una anticipación de quince (15) días naturales, para efecto de que se haga la liquidación correspondiente de los honorarios devengados y no pagados, y en su caso, la entrega de expedientes, documentos e informes.</p>
            </div>
            <div class="clausula-item">
                <p><span class="clausula-numero">TERCERA.- HONORARIOS Y FORMA DE PAGO.</span> "<span class="uppercase">{{ $denominacionPatron }}</span>" se obliga a pagar a "<span class="uppercase">{{ $denominacionTrabajador }}</span>" por concepto de los servicios profesionales independientes objeto de este contrato, la cantidad de <span class="etiqueta">$ {{ $empleado->puesto ? number_format($empleado->puesto->salario_mensual, 2) : '0.00' }}</span> ({{ (new \Luecano\NumeroALetras\NumeroALetras())->toWords($empleado->puesto->salario_mensual ?? 0) }} PESOS 00/100 M.N.) <span class="etiqueta">MENSUALES</span>. Dicha cantidad será pagada en efectivo en el domicilio fiscal de "<span class="uppercase">{{ $denominacionPatron }}</span>", los días 1 y 16 de cada mes. "<span class="uppercase">{{ $denominacionTrabajador }}</span>" se obliga a expedir y entregar a "<span class="uppercase">{{ $denominacionPatron }}</span>" el Comprobante Fiscal Digital por Internet (CFDI) que cumpla con todos los requisitos fiscales vigentes y que ampare el pago de los honorarios devengados, como condición para recibir el pago. La cantidad mencionada retribuye a "<span class="uppercase">{{ $denominacionTrabajador }}</span>" tanto por la prestación, calidad y tiempo dedicado a los servicios, por lo que no podrá exigir mayor retribución por ningún otro concepto, salvo acuerdo expreso y por escrito entre las partes.</p>
            </div>
            <div class="clausula-item">
                <p><span class="clausula-numero">CUARTA.- IMPUESTOS Y OBLIGACIONES FISCALES.</span> Cada una de las partes será responsable de cumplir con sus respectivas obligaciones fiscales. "<span class="uppercase">{{ $denominacionTrabajador }}</span>" será responsable del pago del Impuesto Sobre la Renta (ISR) y demás contribuciones que le correspondan por los honorarios percibidos. "<span class="uppercase">{{ $denominacionPatron }}</span>" realizará las retenciones que en su caso procedan conforme a las disposiciones fiscales vigentes.</p>
            </div>
            <div class="clausula-item">
                <p><span class="clausula-numero">QUINTA.- NATURALEZA CIVIL DEL CONTRATO Y NO SUBORDINACIÓN.</span> Las partes reconocen y aceptan que el presente contrato es de naturaleza exclusivamente civil y se refiere a una prestación de servicios profesionales independientes. En consecuencia, no se genera ni existirá relación laboral alguna de subordinación entre "<span class="uppercase">{{ $denominacionPatron }}</span>" y "<span class="uppercase">{{ $denominacionTrabajador }}</span>", en los términos del artículo 20 de la Ley Federal del Trabajo, por lo que "<span class="uppercase">{{ $denominacionTrabajador }}</span>" no estará sujeto a la dirección y dependencia de "<span class="uppercase">{{ $denominacionPatron }}</span>" en cuanto a horario, jornada o lugar específico e inamovible para la prestación de los servicios, más allá de la coordinación necesaria para la consecución de los objetivos del contrato.</p>
            </div>
            <div class="clausula-item">
                <p><span class="clausula-numero">SEXTA.- OBLIGACIONES DE "<span class="uppercase">{{ $denominacionTrabajador }}</span>".</span> "<span class="uppercase">{{ $denominacionTrabajador }}</span>" se obliga a:</p>
                <ol type="A">
                    <li>Dar a conocer a "<span class="uppercase">{{ $denominacionPatron }}</span>" su clave de Registro Federal de Contribuyentes y régimen fiscal.</li>
                    <li>Firmar los recibos correspondientes por los honorarios pagados.</li>
                    <li>Presentar la documentación personal necesaria que le sea requerida para la integración de su expediente.</li>
                    <li>Rendir los informes sobre el trabajo contratado que le sean solicitados por "<span class="uppercase">{{ $denominacionPatron }}</span>", incluyendo un informe mensual de actividades y un informe general al término del contrato.</li>
                    <li>Prestar los servicios con la pericia, diligencia y profesionalismo requeridos, aplicando sus conocimientos y experiencia.</li>
                    <li>Guardar estricta confidencialidad sobre toda información y documentación relacionada con "<span class="uppercase">{{ $denominacionPatron }}</span>" y sus clientes, obligación que subsistirá aun después de terminado el contrato. En caso de incumplimiento, "<span class="uppercase">{{ $denominacionPatron }}</span>" podrá ejercer las acciones civiles y penales que procedan.</li>
                </ol>
            </div>
            <div class="clausula-item">
                <p><span class="clausula-numero">SÉPTIMA.- SUPERVISIÓN Y RECURSOS.</span> "<span class="uppercase">{{ $denominacionPatron }}</span>" podrá supervisar la adecuada prestación de los servicios y proporcionará a "<span class="uppercase">{{ $denominacionTrabajador }}</span>" la información y documentos necesarios para la ejecución de los mismos.</p>
            </div>
            <div class="clausula-item">
                <p><span class="clausula-numero">OCTAVA.- CAUSAS DE RESCISIÓN.</span> Son causas de rescisión del presente contrato, imputables a "<span class="uppercase">{{ $denominacionTrabajador }}</span>", sin responsabilidad para "<span class="uppercase">{{ $denominacionPatron }}</span>", las siguientes:</p>
                <ol type="A">
                    <li>Que "<span class="uppercase">{{ $denominacionTrabajador }}</span>" deje de prestar los informes que le sean solicitados por "<span class="uppercase">{{ $denominacionPatron }}</span>".</li>
                    <li>Que "<span class="uppercase">{{ $denominacionTrabajador }}</span>" abandone la prestación de sus servicios o no cumpla con ellos en los plazos establecidos por "<span class="uppercase">{{ $denominacionPatron }}</span>".</li>
                    <li>La comisión por parte de "<span class="uppercase">{{ $denominacionTrabajador }}</span>" de algún delito que amerite pena corporal.</li>
                    <li>Que incumpla con lo estipulado en cualquiera de las cláusulas establecidas en el presente contrato.</li>
                </ol>
                <p>El incumplimiento de cualquiera de las obligaciones consignadas en este contrato, faculta a "<span class="uppercase">{{ $denominacionPatron }}</span>" a rescindirlo unilateralmente sin necesidad de declaración judicial y sin responsabilidad alguna, bastando la notificación que al efecto se le haga a "<span class="uppercase">{{ $denominacionTrabajador }}</span>" sin necesidad de previo aviso.</p>
            </div>
            <div class="clausula-item">
                <p><span class="clausula-numero">NOVENA.- JURISDICCIÓN Y LEY APLICABLE.</span> Para la interpretación y cumplimiento del presente contrato, las partes se someten expresamente a la jurisdicción de los tribunales competentes de <span class="etiqueta uppercase">{{ $lugarFirmaCompleto }}</span>, y a las disposiciones del Código Civil para el Estado de México vigente, renunciando a cualquier otro fuero que pudiera corresponderles por razón de sus domicilios presentes o futuros.</p>
            </div>

        @elseif (in_array($contrato->tipo_contrato, ['Determinado', 'Indeterminado', 'Obra Determinada']))
            {{-- CLÁUSULAS PARA CONTRATO LABORAL (TIEMPO DETERMINADO) --}}
            <div class="clausula-item">
                <p><span class="clausula-numero">PRIMERA.- DE LA RELACIÓN DE TRABAJO Y VIGENCIA.</span> El presente Contrato Individual de Trabajo se celebra por tiempo <span class="etiqueta uppercase">{{ $contrato->tipo_contrato }}</span>, 
                @if($contrato->tipo_contrato == 'Determinado' || $contrato->tipo_contrato == 'Obra Determinada')
                    con una vigencia del <span class="etiqueta">{{ $contrato->fecha_inicio ? $contrato->fecha_inicio->translatedFormat('d \d\e F \d\e Y') : 'PENDIENTE' }}</span> al <span class="etiqueta">{{ $contrato->fecha_fin ? $contrato->fecha_fin->translatedFormat('d \d\e F \d\e Y') : 'PENDIENTE' }}</span>,
                @else {{-- Indeterminado --}}
                    iniciando su vigencia el <span class="etiqueta">{{ $contrato->fecha_inicio ? $contrato->fecha_inicio->translatedFormat('d \d\e F \d\e Y') : 'PENDIENTE' }}</span>,
                @endif
                de conformidad con lo dispuesto en los Artículos 25 Fracción II y 35 de la Ley Federal de Trabajo.</p>
            </div>
            <div class="clausula-item">
                <p><span class="clausula-numero">SEGUNDA.- DEL SERVICIO O SERVICIOS.</span> "<span class="uppercase">{{ $denominacionTrabajador }}</span>" se obliga a prestar en forma personal y subordinada, sus servicios a favor de "<span class="uppercase">{{ $denominacionPatron }}</span>", en el puesto de <span class="etiqueta uppercase">{{ $empleado->puesto ? $empleado->puesto->nombre_puesto : '________________' }}</span>. Así mismo "<span class="uppercase">{{ $denominacionTrabajador }}</span>" se obliga a llevar a cabo todas las labores accesorias o conexas relacionadas con su puesto, debiendo desempeñarlos en el lugar que a efecto se ha señalado por "<span class="uppercase">{{ $denominacionPatron }}</span>".</p>
            </div>
            <div class="clausula-item">
                <p><span class="clausula-numero">TERCERA.- DEL LUGAR DE PRESTACIÓN DE SERVICIOS.</span> Los servicios señalados en la cláusula que antecede "<span class="uppercase">{{ $denominacionTrabajador }}</span>" deberá desempeñarlos en el domicilio de <span class="etiqueta uppercase">{{ $empleado->sucursal ? $empleado->sucursal->nombre_sucursal : ($contrato->patron ? $contrato->patron->direccion_fiscal : 'DOMICILIO NO ESPECIFICADO') }}</span>. Así mismo "<span class="uppercase">{{ $denominacionTrabajador }}</span>", otorga su consentimiento para que "<span class="uppercase">{{ $denominacionPatron }}</span>" pueda modificar la dirección, el domicilio, la plaza o la ciudad donde deba prestar los servicios, quedando obligado este último a dar aviso por escrito a "<span class="uppercase">{{ $denominacionTrabajador }}</span>" con quince días de anticipación, por lo menos en que se le notifique tal circunstancia.</p>
            </div>
            <div class="clausula-item">
    <p><span class="clausula-numero">CUARTA.- DE LA DURACIÓN DE LA JORNADA DE TRABAJO.</span>
        @if ($empleado->horario)
            "{{ $denominacionTrabajador }}" se obliga a prestar sus servicios a favor de "{{ $denominacionPatron }}", dentro de una jornada de trabajo tipo <span class="etiqueta uppercase">{{ $empleado->horario->nombre_horario }}</span>, la cual será distribuida de la siguiente manera:

            {{-- Verifica si hay horario definido para Lunes --}}
            @if($empleado->horario->lunes_entrada && $empleado->horario->lunes_salida)
                de <span class="etiqueta">Lunes a Viernes de {{ \Carbon\Carbon::parse($empleado->horario->lunes_entrada)->format('H:i') }} a {{ \Carbon\Carbon::parse($empleado->horario->lunes_salida)->format('H:i') }} horas</span>, con un intermedio de {{ $empleado->horario->tiempo_comida_minutos ?? '60' }} minutos para tomar sus alimentos.
            @endif

            {{-- Verifica por separado si hay horario para Sábado --}}
            @if($empleado->horario->sabado_entrada && $empleado->horario->sabado_salida)
                Adicionalmente, laborará los días <span class="etiqueta">Sábado de {{ \Carbon\Carbon::parse($empleado->horario->sabado_entrada)->format('H:i') }} a {{ \Carbon\Carbon::parse($empleado->horario->sabado_salida)->format('H:i') }} horas</span>.
            @endif

            El día de descanso para "{{ $denominacionTrabajador }}" será preferentemente el día <span class="etiqueta uppercase">{{ $empleado->horario->dia_descanso ?? 'Domingo' }}</span> de cada semana.
        @else
            <span class="text-bold">JORNADA DE TRABAJO NO ESPECIFICADA.</span> Las partes acordarán por escrito el horario y jornada de trabajo conforme a las necesidades del servicio y lo estipulado por la Ley Federal del Trabajo.
        @endif
    </p>
</div>
            <div class="clausula-item">
                <p><span class="clausula-numero">QUINTA.- DE LA FORMA Y MONTO DEL SALARIO.</span> "<span class="uppercase">{{ $denominacionPatron }}</span>" se obliga a pagar a "<span class="uppercase">{{ $denominacionTrabajador }}</span>" un salario mensual bruto de <span class="etiqueta">$ {{ $empleado->puesto ? number_format($empleado->puesto->salario_mensual, 2) : '0.00' }}</span> ({{ (new \Luecano\NumeroALetras\NumeroALetras())->toWords($empleado->puesto->salario_mensual ?? 0) }} PESOS 00/100 M.N.). Dicho salario será cubierto en forma quincenal, en el domicilio de "<span class="uppercase">{{ $denominacionPatron }}</span>" @if ($empleado->cuenta_bancaria && $empleado->banco)(o mediante depósito en la cuenta bancaria No. <span class="etiqueta">{{ $empleado->cuenta_bancaria }}</span> del banco <span class="etiqueta">{{ $empleado->banco }}</span>)@endif, los días primero y dieciséis de cada mes, salvo que esos días sean no laborables, caso en el cual se pagará el día laboral anterior. "<span class="uppercase">{{ $denominacionTrabajador }}</span>" se obliga a firmar los recibos correspondientes a las cantidades recibidas.</p>
            </div>
            <div class="clausula-item">
                <p><span class="clausula-numero">SEXTA.- DE LA CAPACITACIÓN.</span> "<span class="uppercase">{{ $denominacionPatron }}</span>" se obliga a capacitar y/o adiestrar a "<span class="uppercase">{{ $denominacionTrabajador }}</span>" en los términos de los planes y programas establecidos o que se establezcan en la empresa, conforme a lo dispuesto por el Artículo 153-A y demás relativos de la Ley Federal del Trabajo, y a entregar la constancia de competencias o de habilidades laborales de conformidad con el Artículo 153-V de la Ley Federal del Trabajo.</p>
            </div>
            <div class="clausula-item">
                <p><span class="clausula-numero">SÉPTIMA.- DE LAS VACACIONES.</span> "<span class="uppercase">{{ $denominacionTrabajador }}</span>" disfrutará de un periodo anual de vacaciones pagadas de acuerdo a lo establecido en el Artículo 76 de la Ley Federal del Trabajo, que actualmente establece un mínimo de doce (12) días laborables por el primer año de servicios, aumentando en dos días laborables por cada año subsecuente de servicios, hasta llegar a veinte. A partir del sexto año, el periodo de vacaciones aumentará en dos días por cada cinco años de servicio.</p>
            </div>
            <div class="clausula-item">
                <p><span class="clausula-numero">OCTAVA.- DE LA PRIMA VACACIONAL.</span> "<span class="uppercase">{{ $denominacionTrabajador }}</span>" gozará de una prima vacacional equivalente al veinticinco por ciento (25%) sobre el monto del salario correspondiente a los días de vacaciones a que tenga derecho, la cual se cubrirá al momento de disfrutar de estas, de conformidad con lo dispuesto por el Artículo 80 de la Ley Federal del Trabajo.</p>
            </div>
            <div class="clausula-item">
                <p><span class="clausula-numero">NOVENA.- DEL AGUINALDO.</span> "<span class="uppercase">{{ $denominacionTrabajador }}</span>" tendrá derecho a un aguinaldo anual que deberá pagarse antes del día veinte de diciembre, equivalente a quince días de salario, por lo menos. Los que no hayan cumplido el año de servicios, independientemente de que se encuentren laborando o no en la fecha de liquidación del aguinaldo, tendrán derecho a que se les pague la parte proporcional, de conformidad con lo dispuesto por el Artículo 87 de la Ley Federal del Trabajo.</p>
            </div>
            <div class="clausula-item">
                <p><span class="clausula-numero">DÉCIMA.- DE LOS INSTRUMENTOS DE TRABAJO.</span> "<span class="uppercase">{{ $denominacionTrabajador }}</span>" reconoce que toda herramienta, maquinaria, medio de transporte, material, uniforme, papelería o instrumento en general, que "<span class="uppercase">{{ $denominacionPatron }}</span>" entregue a "<span class="uppercase">{{ $denominacionTrabajador }}</span>" para la prestación de sus servicios, es propiedad de "<span class="uppercase">{{ $denominacionPatron }}</span>", por lo que al término del presente contrato, sea cual fuere la causa que de origen a dicha terminación, "<span class="uppercase">{{ $denominacionTrabajador }}</span>" se encuentra obligado a restituirlos de inmediato, sin necesidad de requerimiento judicial o extrajudicial.</p>
            </div>
            <div class="clausula-item">
                <p><span class="clausula-numero">DÉCIMA PRIMERA.- DE LA CONFIDENCIALIDAD.</span> "<span class="uppercase">{{ $denominacionTrabajador }}</span>" se obliga a no divulgar, reproducir o hacer del conocimiento de terceras personas ajenas al presente contrato, por ningún medio, bien sea verbal, escrito, en grabaciones magnéticas o de cualquier otra clase, o por cualquier otro medio, durante la vigencia del presente contrato y posterior al término de la relación laboral, información alguna de la cual tenga conocimiento en relación con la empresa o los servicios, o los sistemas que esta última presta y utiliza. En caso de que "<span class="uppercase">{{ $denominacionTrabajador }}</span>" contravenga a lo dispuesto, será causa suficiente para que "<span class="uppercase">{{ $denominacionPatron }}</span>" quede facultado para rescindir el presente contrato, independientemente de las acciones civiles y/o penales que se puedan ejercitar en su contra.</p>
            </div>
            <div class="clausula-item">
                <p><span class="clausula-numero">DÉCIMA SEGUNDA.- DE LA RESCISIÓN.</span> El presente contrato y las relaciones de trabajo derivadas del mismo, solo podrán ser modificadas, suspendidas, rescindidas o terminadas en los casos y requisitos establecidos en los Artículos 42, 46, 47, 51 y 53 de la Ley Federal del Trabajo.</p>
            </div>
            <div class="clausula-item">
                <p><span class="clausula-numero">DÉCIMA TERCERA.- DE LA SUPLETORIEDAD.</span> Para todo lo no previsto en el presente contrato, le serán aplicables en forma supletoria, las disposiciones del Reglamento Interior de Trabajo (si existiere) y de la Ley Federal del Trabajo en vigor.</p>
            </div>
            <div class="clausula-item">
                <p><span class="clausula-numero">DÉCIMA CUARTA.- LA DESIGNACIÓN DE BENEFICIARIOS.</span> Para el pago de los salarios y prestaciones devengadas y no cobradas a la muerte de "<span class="uppercase">{{ $denominacionTrabajador }}</span>" o las que se generen por su fallecimiento o desaparición derivada de un acto delincuencial, dicho pago se realizará de acuerdo a lo estipulado en los Artículos 25 Fracción X y 501 de la Ley Federal del Trabajo.</p>
            </div>
        @else
            <p class="text-center"><span class="etiqueta">CLÁUSULAS NO DEFINIDAS PARA ESTE TIPO DE CONTRATO ({{ $contrato->tipo_contrato }}) EN LA PLANTILLA.</span></p>
        @endif
    </div>

    <div class="firmas footer-text"> {{-- Usé footer-text para la parte final de firma --}}
        <p class="text-justify">HABIENDO LEÍDO EL PRESENTE CONTRATO Y EXPLICADO EL VALOR, ALCANCES Y CONSECUENCIAS DEL MISMO A LAS PARTES, LO FIRMAN PARA CONSTANCIA Y POR DUPLICADO, EN <span class="etiqueta uppercase">{{ $contrato->patron->direccion_fiscal ?? 'DOMICILIO NO ESPECIFICADO' }}</span>, EL DÍA <span class="etiqueta">{{ $fechaFirma }}</span>.</p>
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
    
    @if($mostrarTestigos)
    <br style="page-break-before: auto; clear:both;"> {{-- Ajustado para que el salto no sea tan agresivo si no hay testigos --}}
    <div class="firmas-container" style="margin-top: 30px;">
        <div class="firma firma-izquierda">
            <div class="linea-firma"></div>
            <p class="uppercase">Testigo</p>
            <p style="margin-top:10px;">_________________________</p>
        </div>
        <div class="firma firma-derecha">
            <div class="linea-firma"></div>
            <p class="uppercase">Testigo</p>
            <p style="margin-top:10px;">_________________________</p>
        </div>
    </div>
    @endif

</body>
</html>