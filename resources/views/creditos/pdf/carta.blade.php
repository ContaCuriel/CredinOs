<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Carta Compromiso</title>
    <style>
        /* Márgenes reducidos para maximizar el espacio de la hoja */
        @page { margin: 1.5cm 2cm; }
        
        /* Fuente más compacta e interlineado ajustado */
        body { font-family: 'Helvetica', 'Arial', sans-serif; font-size: 9.5pt; color: #2d3748; margin: 0; line-height: 1.4; text-align: justify; }
        
        /* Cabecera compacta */
        .header { width: 100%; margin-bottom: 15px; display: table; border-bottom: 2px solid #003a70; padding-bottom: 10px; }
        .logo-cell { display: table-cell; width: 50%; vertical-align: middle; }
        .logo-cell img { max-width: 200px; max-height: 65px; }
        .logo-cell h3 { margin: 0; font-size: 16px; color: #003a70; text-transform: uppercase; }
        
        .title-cell { display: table-cell; width: 50%; text-align: right; vertical-align: bottom; }
        .doc-title { font-size: 16px; font-weight: bold; color: #003a70; text-transform: uppercase; letter-spacing: 2px; }
        .doc-folio { font-size: 10pt; font-weight: bold; color: #4a5568; margin-top: 4px; }
        .doc-date { font-size: 9pt; color: #718096; margin-top: 4px; text-transform: uppercase; }

        .fw-bold { font-weight: bold; color: #1a202c; }
        
        /* Párrafos con menos separación */
        p { margin-bottom: 12px; }

        /* Lista de Cuentas más pequeña */
        .cuentas-list { margin-top: 4px; margin-bottom: 4px; padding-left: 25px; font-size: 9pt; }
        .cuentas-list li { margin-bottom: 2px; }

        /* Tabla de Firmas Ultra-optimizada */
        .firmas-table { width: 100%; border-collapse: collapse; margin-top: 20px; text-align: center; }
        .firmas-table tr { page-break-inside: avoid; } /* Evita que una firma se parta a la mitad si salta de hoja */
        .firmas-table th { background-color: #003a70; color: #ffffff; border: 1px solid #003a70; padding: 6px; font-size: 8.5pt; text-transform: uppercase; letter-spacing: 1px; }
        .firmas-table td { border: 1px solid #cbd5e0; padding: 8px 6px; vertical-align: middle; font-size: 9pt; height: 35px; color: #2d3748; }
        .firmas-table tr:nth-child(even) { background-color: #f8fafc; }
    </style>
</head>
<body>

    @php
        $esGrupal = $credito->grupo_id ? true : false;
        $empresa = $credito->patron->nombre_comercial ?? 'LA EMPRESA EMISORA';
        
        // Formateo de fechas y frecuencias
        $freq = strtolower($credito->producto->frecuencia_pago);
        $diasFreq = ($freq == 'semanal') ? '7 días' : (($freq == 'catorcenal') ? '14 días' : (($freq == 'quincenal') ? '15 días' : '1 mes'));
        $periodos = ($freq == 'semanal') ? 'semanas' : (($freq == 'catorcenal') ? 'catorcenas' : (($freq == 'quincenal') ? 'quincenas' : 'meses'));
    @endphp

    <div class="header">
        <div class="logo-cell">
            @if(isset($logo_base64) && $logo_base64)
                <img src="{{ $logo_base64 }}" alt="Logo">
            @else
                <h3>{{ $empresa }}</h3>
            @endif
        </div>
       <div class="title-cell">
            <div class="doc-title">Carta Compromiso</div>
            <div class="doc-folio">FOLIO: {{ $credito->folio }}</div>
            <div class="doc-date">{{ $credito->sucursal->nombre_sucursal ?? 'TEXCOCO DE MORA' }} a {{ \Carbon\Carbon::parse($credito->fecha_desembolso)->format('d/m/Y') }}</div>
        </div>
    </div>

    <!-- PÁRRAFO 1: Solicitud y Fondeo -->
    <p>
        @if($esGrupal)
            <span class="fw-bold">Nosotros</span>, los integrantes del grupo: <span class="fw-bold">{{ $credito->grupo->nombre_grupo }}</span> hemos conformado un grupo "SOLIDARIO", para solicitar un préstamo a <span class="fw-bold">{{ strtoupper($empresa) }}</span>, 
        @else
            <span class="fw-bold">Yo</span>, <span class="fw-bold">{{ $credito->cliente->nombre_completo ?? $credito->cliente->nombre }}</span>, he solicitado un préstamo a <span class="fw-bold">{{ strtoupper($empresa) }}</span>, 
        @endif
        
        por la cantidad de <span class="fw-bold">${{ number_format($credito->monto_aprobado, 2) }} ({{ $letras_monto_aprobado }} 00/100 M.N.)</span> el cual será entregado en efectivo
        
        {{-- LISTA DINÁMICA DE CUENTAS BANCARIAS --}}
        @if($credito->cuentasDesembolso->count() > 0)
            o depositado en las siguientes cuentas bancarias:
            <ul class="cuentas-list">
                @foreach($credito->cuentasDesembolso as $cta)
                    <li>No. Cuenta/CLABE: <span class="fw-bold">{{ $cta->numero_cuenta }}</span> a nombre de <span class="fw-bold">{{ strtoupper($cta->titular) }}</span>, del banco <span class="fw-bold">{{ strtoupper($cta->banco) }}</span>.</li>
                @endforeach
            </ul>
        @else
            , 
        @endif

        {{ $esGrupal ? 'la cual, nos comprometemos a pagar' : 'el cual, me comprometo a pagar' }} de manera puntual cada {{ $diasFreq }}, a partir del día <span class="fw-bold">{{ \Carbon\Carbon::parse($credito->fecha_primer_pago)->format('d/m/Y') }}</span> durante {{ $credito->plazo_aprobado }} {{ $periodos }} consecutivas y serán pagaderos en efectivo en la sucursal <span class="fw-bold">{{ strtoupper($credito->sucursal->nombre_sucursal ?? 'TEXCOCO') }}</span>, por la cantidad de <span class="fw-bold">${{ number_format($cuota_monto, 2) }}</span>.
    </p>

    <!-- PÁRRAFO 2: Mora y Multas -->
    <p>
        También {{ $esGrupal ? 'somos sabedores y aceptamos pagar' : 'soy sabedor(a) y acepto pagar' }} el interés moratorio {{ $freq }} del {{ $credito->producto->mora_valor ?? 10 }}% del crédito total {{ $esGrupal ? 'del grupo' : '' }}, por cada uno de los atrasos que {{ $esGrupal ? 'presentemos' : 'presente' }}, el cual será por la cantidad de <span class="fw-bold">${{ number_format($monto_mora_calculado, 2) }}</span>, con un horario máximo de pago de las <span class="fw-bold">{{ \Carbon\Carbon::parse($credito->producto->hora_maxima_pago ?? '10:00:00')->format('H:i') }} hrs</span>, de lo contrario se genera una multa por la cantidad de <span class="fw-bold">${{ number_format($credito->producto->multa_valor ?? 500, 2) }} ({{ $letras_multa }} 00/100 M.N.)</span>.
    </p>

    <!-- PÁRRAFO 3: Comisión -->
    <p>
        Al momento del desembolso, {{ $esGrupal ? 'se nos descontará' : 'se me descontará' }} el {{ $credito->comision_apertura_aplicada }}% del préstamo solicitado por concepto de COMISION, el cual, {{ $esGrupal ? 'estamos de acuerdo en pagar, y entendemos' : 'estoy de acuerdo en pagar, y entiendo' }} perfectamente que NO se devolverá.
    </p>

    <!-- PÁRRAFO 4: Voluntad y Legalidad -->
    <p>
        {{ $esGrupal ? 'Firmamos este documento por nuestra propia voluntad, y hacemos hincapié en que nadie nos' : 'Firmo este documento por mi propia voluntad, y hago hincapié en que nadie me' }} está obligando a hacerlo, también {{ $esGrupal ? 'aceptamos y entendemos' : 'acepto y entiendo' }}, todas las condiciones que en él se mencionan, dando total autorización que si {{ $esGrupal ? 'incumpliéramos en los pagos se actué de forma legal en nuestra contra' : 'incumpliera en los pagos se actué de forma legal en mi contra' }}.
    </p>

    <!-- TABLA DE FIRMAS DINÁMICA REFINADA -->
    <table class="firmas-table">
        <thead>
            <tr>
                <th width="8%">No.</th>
                <th width="45%">NOMBRE INTEGRANTE</th>
                <th width="17%">MONTO</th>
                <th width="30%">FIRMA</th>
            </tr>
        </thead>
        <tbody>
            @foreach($credito->integrantes as $index => $integrante)
            <tr>
                <td class="fw-bold text-muted">{{ $index + 1 }}</td>
                <td class="fw-bold text-left" style="text-align: left; padding-left: 10px;">
                    {{ mb_strtoupper($integrante->nombre_completo ?? $integrante->nombre . ' ' . $integrante->apellido_paterno) }}
                    @if($integrante->pivot->es_lider)
                        <br><span style="font-size: 7.5pt; color: #718096; font-weight: normal;">(REPRESENTANTE / LÍDER)</span>
                    @endif
                </td>
                <td class="fw-bold">${{ number_format($integrante->pivot->monto_individual, 2) }}</td>
                <td></td>
            </tr>
            @endforeach
        </tbody>
    </table>

</body>
</html>