<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; text-align: justify; line-height: 1.5; }
        .titulo { text-align: center; font-weight: bold; margin-bottom: 20px; }
        .clausula { font-weight: bold; }
    </style>
</head>
<body>

<p>En el municipio de Texcoco, Estado de México, de fecha {{ \Carbon\Carbon::parse($credito->fecha_aprobacion)->isoFormat('LL') }}, celebran CONTRATO DE MUTUO CON @if($credito->garantia->tipo_garantia == 'vehiculo') GARANTIA PRENDARIA @else INTERES E HIPOTECA @endif, compareciendo por un lado el C. {{ $credito->patron->representante_legal ?? 'RENE ALVA RODRIGUEZ' }} a quien en lo sucesivo se le denominara "PARTE ACREEDORA", y por otra el C. {{ $credito->cliente->nombre_completo ?? $credito->cliente->nombre }}, a quien se le denominara "PARTE DEUDORA", ambas partes se reconocen la personalidad con que se ostentan y por ciertos de estar vigente la misma, sometiéndose al tenor de las siguientes:</p>

<div class="titulo">CLAUSULAS</div>

<p><span class="clausula">PRIMERA. - MONTO DEL MUTUO.</span> La "PARTE ACREEDORA" entrega en este acto en calidad de mutuo con interés a la "PARTE DEUDORA" y esta recibe a su más entera satisfacción, la cantidad de ${{ number_format($credito->monto_aprobado, 2) }} ({{ $letras_monto_aprobado }} 00/100 M.N.)</p>

<p><span class="clausula">SEGUNDA. - COMISION.</span> La "PARTE DEUDORA" acepta pagar en este acto y en efectivo la cantidad de ${{ number_format($credito->comision_apertura_aplicada, 2) }} ({{ $letras_comision }} 00/100 M.N.) por concepto de comisión, mismos que se pagaran el día que se entregue la cantidad mencionada en la cláusula primera del presente contrato.</p>

<p><span class="clausula">TERCERA. - FECHA DE PAGO.</span> El pago se parcializará de manera {{ strtolower($credito->producto->frecuencia_pago) }}, entregando al acreedor la cantidad de ${{ number_format($cuota_monto, 2) }} ({{ $letras_cuota }} 00/100 M.N.) todos los días {{ $dia_pago }}, durante {{ $credito->plazo_aprobado }} {{ strtolower($credito->producto->frecuencia_pago) }}s consecutivas, importe que incluye pago de capital e interés convencional, a partir del día {{ \Carbon\Carbon::parse($credito->fecha_primer_pago)->isoFormat('LL') }}; dicho pago se realizará en efectivo por los conceptos mencionados en la cláusula primera y segunda del presente contrato, y será pagadero en Sucursal {{ $sucursal_nombre }}. Con un horario máximo de las {{ $credito->producto->hora_maxima_pago ?? '10:00' }} hrs, de lo contrario acepto pagar ${{ number_format($credito->producto->multa_valor, 2) }} ({{ $letras_multa }} 00/100 M.N.), por concepto de MULTA.</p>

<p><span class="clausula">CUARTA. PENA CONVENCIONAL.</span> En caso de que la "PARTE ACREEDORA" requiera acudir a la vía jurisdiccional para coaccionar el cumplimiento del presente contrato se establece como pena convencional de gastos de cobranza el 10% del total de la deuda incluyendo interés convencional y moratorios de la deuda.</p>

<p><span class="clausula">QUINTA. - GARANTÍA.</span> 
@if($credito->garantia->tipo_garantia == 'vehiculo')
Las partes convienen dejar en poder del ACREEDOR el original del documento {{ $credito->garantia->vehiculo_documento }} que ampara los derechos de propiedad del automóvil que a continuación se describe (AUTOMOVIL / {{ $credito->garantia->vehiculo_tipo }} VERSION {{ $credito->garantia->vehiculo_modelo }} / MARCA {{ $credito->garantia->vehiculo_marca }} / MODELO {{ $credito->garantia->vehiculo_anio }} / MOTOR {{ $credito->garantia->vehiculo_motor }} / COLOR {{ $credito->garantia->vehiculo_color }} / No. de SERIE {{ $credito->garantia->vehiculo_serie }}) sirviendo estos mismos derechos como garantía sobre el adeudo adquirido con la figura antes mencionada, quien, en caso de incumplimiento del pago referido y accesorios, podrá ejercer acción por la vía legal correspondiente, teniendo preconstituida la garantía especificada en el presente.<br><br>
Declaro bajo protesta de decir la verdad que, al momento de adquirir dicho adeudo, el automóvil y los derechos que sobre este ostenta son únicos y exclusivos de su persona, por lo tanto, se encuentra facultada para otorgar garantía sobre él, así mismo adquiere la obligación de no trasmitir, enajenar o alterar la garantía otorgada.
@else
En este acto las partes convienen establecer como garantía en favor del acreedor los derechos de propiedad de {{ $credito->garantia->propiedad_ubicacion }}, CUYAS MEDIDAS Y COLINDANCIAS SON: {{ $credito->garantia->propiedad_superficie }}.<br><br>
Documento que se entrega en original al ACREEDOR; declarando bajo protesta de decir verdad EL DEUDOR que es único propietario de dicho inmueble, del cual tiene en posesión y que no ha realizado transacción alguna con el, por lo que se encuentra en total libertad de dejarlo como garantía.<br><br>
Para los efectos procesales la "PARTE DEUDORA", renuncia desde ahora para el caso de ser exigibles las obligaciones que contrae en este contrato, a la posesión del inmueble, y está conforme en que se entregue la tenencia material del mismo a la "PARTE ACREEDORA".
@endif
</p>

<p><span class="clausula">SEXTA. - INCUMPLIMIENTO.</span> Las partes convienen que en caso de ATRASO sea cual sea el número parcialidad que corresponda se establece un interés moratorio de ${{ number_format($credito->producto->mora_valor, 2) }} ({{ $letras_mora }} 00/100 M.N.) {{ strtolower($credito->producto->frecuencia_pago) }}, el que se agregara a la deuda haciéndolo pagadero como una sola unidad, en este caso es optativo para el ACREEDOR hacer efectiva la garantía, la cual consiste en incorporar para sí y sin necesidad de declaración judicial los derechos de propiedad que el deudor acepta darle, teniéndose esta operación como una COMPRAVENTA con precio pactado, equivalente al monto del adeudo restante más intereses y gastos de cobranza.</p>

<p><span class="clausula">SEPTIMA. JURISDICCIÓN.</span> Los jueces de Texcoco, Estado de México, serán los únicos competentes para conocer y fallar en todas sus instancias las cuestiones que se susciten con motivo de la interpretación y cumplimiento de este contrato, a cuyo efecto los contratantes renuncian a cualquier fuero por razón de domicilio presente o futuro.</p>

<p>Leído que fue por las partes el presente contrato, firman de conformidad.</p>

<br><br>
<table width="100%" style="text-align: center; margin-top: 50px;">
    <tr>
        <td width="50%">
            ___________________________________<br>
            "PARTE ACREEDORA"<br>
            C. {{ $credito->patron->representante_legal ?? 'RENE ALVA RODRIGUEZ' }}
        </td>
        <td width="50%">
            ___________________________________<br>
            "PARTE DEUDORA"<br>
            C. {{ $credito->cliente->nombre_completo ?? $credito->cliente->nombre }}
        </td>
    </tr>
</table>

</body>
</html>