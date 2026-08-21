<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Contrato de Crédito</title>
    <style>
        /* Márgenes legales estándar (Superior, Derecho, Inferior, Izquierdo) */
        @page { margin: 3cm 2.5cm 3cm 3cm; } 
        body { 
            font-family: 'Arial', sans-serif; 
            font-size: 12pt; 
            text-align: justify; 
            line-height: 1.8; /* Doble espacio para formato legal */
            color: #000; 
        }
        .titulo-central { 
            text-align: center; 
            font-weight: bold; 
            margin: 30px 0; 
            font-size: 13pt; 
            letter-spacing: 2px; 
        }
        .clausula { font-weight: bold; text-transform: uppercase; }
        .resaltado { font-weight: bold; }
        p { margin-bottom: 20px; }
        
        /* Contenedor de Firmas que no se corta a la mitad */
        .firmas-container { 
            width: 100%; 
            margin-top: 80px; 
            page-break-inside: avoid; 
        }
        .firma-box { 
            width: 48%; 
            display: inline-block; 
            text-align: center; 
            vertical-align: top; 
        }
        .linea-firma { 
            border-top: 1px solid #000; 
            width: 85%; 
            margin: 0 auto 5px auto; 
        }
    </style>
</head>
<body>

    <p>En el municipio de Texcoco, Estado de México, de fecha <span class="resaltado">{{ \Carbon\Carbon::parse($credito->fecha_aprobacion)->locale('es')->isoFormat('LL') }}</span>, celebran CONTRATO DE MUTUO CON @if($credito->garantia && $credito->garantia->tipo_garantia == 'vehiculo') GARANTIA PRENDARIA @else INTERES E HIPOTECA @endif, compareciendo por un lado el <span class="resaltado">C. {{ $credito->patron->representante_legal ?? 'RENE ALVA RODRIGUEZ' }}</span> a quien en lo sucesivo se le denominara <span class="resaltado">"PARTE ACREEDORA"</span>, y por otra el <span class="resaltado">C. {{ $credito->cliente->nombre_completo ?? $credito->cliente->nombre }}</span>, a quien se le denominara <span class="resaltado">"PARTE DEUDORA"</span>, ambas partes se reconocen la personalidad con que se ostentan y por ciertos de estar vigente la misma, sometiéndose al tenor de las siguientes:</p>

    <div class="titulo-central">CLAUSULAS</div>

    <p><span class="clausula">PRIMERA. - MONTO DEL MUTUO.</span> La "PARTE ACREEDORA" entrega en este acto en calidad de mutuo con interés a la "PARTE DEUDORA" y esta recibe a su más entera satisfacción, la cantidad de <span class="resaltado">${{ number_format($credito->monto_aprobado, 2) }} ({{ $letras_monto_aprobado }} 00/100 M.N.)</span></p>

    <p><span class="clausula">SEGUNDA. - COMISION.</span> La "PARTE DEUDORA" acepta pagar en este acto y en efectivo la cantidad de <span class="resaltado">${{ number_format($credito->comision_apertura_aplicada, 2) }} ({{ $letras_comision }} 00/100 M.N.)</span> por concepto de comisión, mismos que se pagaran el día que se entregue la cantidad mencionada en la cláusula primera del presente contrato.</p>

    @php
        // Corrección gramatical de los periodos
        $freq = strtolower($credito->producto->frecuencia_pago);
        $periodos = '';
        if($freq == 'semanal') $periodos = 'semanas';
        elseif($freq == 'catorcenal') $periodos = 'catorcenas';
        elseif($freq == 'quincenal') $periodos = 'quincenas';
        elseif($freq == 'mensual') $periodos = 'meses';
        else $periodos = 'periodos';
    @endphp

    <p><span class="clausula">TERCERA. - FECHA DE PAGO.</span> El pago se parcializará de manera {{ $freq }}, entregando al acreedor la cantidad de <span class="resaltado">${{ number_format($cuota_monto, 2) }} ({{ $letras_cuota }} 00/100 M.N.)</span> todos los días {{ $dia_pago }}, durante <span class="resaltado">{{ $credito->plazo_aprobado }} {{ $periodos }}</span> consecutivas, importe que incluye pago de capital e interés convencional, a partir del día <span class="resaltado">{{ \Carbon\Carbon::parse($credito->fecha_primer_pago)->locale('es')->isoFormat('LL') }}</span>; dicho pago se realizará en efectivo por los conceptos mencionados en la cláusula primera y segunda del presente contrato, y será pagadero en Sucursal de <span class="resaltado">{{ strtoupper($sucursal_nombre) }}</span>. En un horario máximo de las {{ \Carbon\Carbon::parse($credito->producto->hora_maxima_pago ?? '10:00:00')->format('H:i') }} hrs, de lo contrario acepto pagar <span class="resaltado">${{ number_format($credito->producto->multa_valor ?? 500, 2) }} ({{ $letras_multa }} 00/100 M.N.)</span>, por concepto de MULTA.</p>

    <p><span class="clausula">CUARTA. PENA CONVENCIONAL.</span> En caso de que la "PARTE ACREEDORA" requiera acudir a la vía jurisdiccional para coaccionar el cumplimiento del presente contrato se establece como pena convencional de gastos de cobranza el 10% del total de la deuda incluyendo interés convencional y moratorios de la deuda.</p>

    <p><span class="clausula">QUINTA. - GARANTÍA.</span> 
    @if($credito->garantia && $credito->garantia->tipo_garantia == 'vehiculo')
    Las partes convienen dejar en poder del ACREEDOR el original del documento <span class="resaltado">{{ $credito->garantia->vehiculo_documento }}</span> que ampara los derechos de propiedad del automóvil que a continuación se describe (AUTOMOVIL / {{ $credito->garantia->vehiculo_tipo }} VERSION {{ $credito->garantia->vehiculo_modelo }} / MARCA {{ $credito->garantia->vehiculo_marca }} / MODELO {{ $credito->garantia->vehiculo_anio }} / MOTOR {{ $credito->garantia->vehiculo_motor }} / COLOR {{ $credito->garantia->vehiculo_color }} / No. de SERIE <span class="resaltado">{{ $credito->garantia->vehiculo_serie }}</span>) sirviendo estos mismos derechos como garantía sobre el adeudo adquirido con la figura antes mencionada, quien, en caso de incumplimiento del pago referido y accesorios, podrá ejercer acción por la vía legal correspondiente, teniendo preconstituida la garantía especificada en el presente.<br><br>
    Declaro bajo protesta de decir la verdad que, al momento de adquirir dicho adeudo, el automóvil y los derechos que sobre este ostenta son únicos y exclusivos de su persona, por lo tanto, se encuentra facultada para otorgar garantía sobre él, así mismo adquiere la obligación de no trasmitir, enajenar o alterar la garantía otorgada.
    @elseif($credito->garantia && $credito->garantia->tipo_garantia == 'propiedad')
    En este acto las partes convienen establecer como garantía en favor del acreedor los derechos de propiedad de <span class="resaltado">{{ $credito->garantia->propiedad_ubicacion }}</span>, CUYAS MEDIDAS Y COLINDANCIAS SON: {{ $credito->garantia->propiedad_superficie }}.<br><br>
    Documento que se entrega en original al ACREEDOR; declarando bajo protesta de decir verdad EL DEUDOR que es único propietario de dicho inmueble, del cual tiene en posesión y que no ha realizado transacción alguna con el, por lo que se encuentra en total libertad de dejarlo como garantía.<br><br>
    Para los efectos procesales la "PARTE DEUDORA", renuncia desde ahora para el caso de ser exigibles las obligaciones que contrae en este contrato, a la posesión del inmueble, y está conforme en que se entregue la tenencia material del mismo a la "PARTE ACREEDORA".
    @else
    En este acto, las partes manifiestan que el presente mutuo se otorga de manera quirografaria, sin que se establezca una garantía real específica, respondiendo la "PARTE DEUDORA" con todos sus bienes presentes y futuros.
    @endif
    </p>

    <p><span class="clausula">SEXTA. - INCUMPLIMIENTO.</span> Las partes convienen que en caso de ATRASO sea cual sea el número de parcialidad que corresponda se establece un interés moratorio de <span class="resaltado">${{ number_format($credito->producto->mora_valor ?? 1000, 2) }} ({{ $letras_mora }} 00/100 M.N.)</span> {{ $freq }}, el que se agregara a la deuda haciéndolo pagadero como una sola unidad, en este caso es optativo para el ACREEDOR hacer efectiva la garantía, la cual consiste en incorporar para sí y sin necesidad de declaración judicial los derechos de propiedad que el deudor acepta darle, teniéndose esta operación como una COMPRAVENTA con precio pactado, equivalente al monto del adeudo restante más intereses y gastos de cobranza.</p>

    <p><span class="clausula">SEPTIMA. JURISDICCIÓN.</span> Los jueces de Texcoco, Estado de México, serán los únicos competentes para conocer y fallar en todas sus instancias las cuestiones que se susciten con motivo de la interpretación y cumplimiento de este contrato, a cuyo efecto los contratantes renuncian a cualquier fuero por razón de domicilio presente o futuro.</p>

    <p>Leído que fue por las partes el presente contrato, firman de conformidad.</p>

    <div class="firmas-container">
        <div class="firma-box">
            <div class="linea-firma"></div>
            <span class="resaltado">"PARTE ACREEDORA"</span><br>
            C. {{ $credito->patron->representante_legal ?? 'RENE ALVA RODRIGUEZ' }}
        </div>
        <div class="firma-box">
            <div class="linea-firma"></div>
            <span class="resaltado">"PARTE DEUDORA"</span><br>
            C. {{ $credito->cliente->nombre_completo ?? $credito->cliente->nombre }}
        </div>
    </div>

</body>
</html>