<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: 'Helvetica', sans-serif; line-height: 1.6; font-size: 11pt; margin: 45px; color: #333; }
        .header { text-align: right; margin-bottom: 40px; }
        .title { text-align: center; font-weight: bold; text-decoration: underline; margin-bottom: 30px; font-size: 14pt; }
        .content { text-align: justify; }
        .footer { margin-top: 100px; width: 100%; }
        .signature-box { text-align: center; width: 45%; display: inline-block; vertical-align: top; }
        .line { border-top: 1px solid #000; width: 80%; margin: 10px auto; }
    </style>
</head>
<body>
    <div class="header">
        <strong>{{ $empleado->sucursal->municipio ?? 'Mérida' }}, Yucatán a {{ \Carbon\Carbon::now()->translatedFormat('d \d\e F \d\e Y') }}</strong>
    </div>

    <div class="title">AVISO DE TERMINACIÓN POR VENCIMIENTO DE TÉRMINO</div>

    <div class="content">
        <p><strong>C. {{ $empleado->nombre_completo }}</strong><br>P R E S E N T E.</p>

        <p>Por medio de la presente, se le comunica que el contrato individual de trabajo por tiempo determinado que celebró con esta institución, el cual tiene como fecha de vencimiento el día <strong>{{ \Carbon\Carbon::parse($empleado->contratos->first()->fecha_fin)->translatedFormat('d \d\e F \d\e Y') }}</strong>, no será renovado.</p>

        <p>En virtud de lo anterior, su relación laboral concluirá formalmente en la fecha mencionada, dándose por terminada sin responsabilidad alguna para el patrón, de conformidad con las disposiciones aplicables de la Ley Federal del Trabajo.</p>

        <p>Agradecemos la colaboración prestada durante el tiempo que duró su relación de trabajo.</p>
    </div>

    <div class="footer">
        <div class="signature-box">
            <p>ATENTAMENTE,</p>
            <div style="margin-top: 60px;" class="line"></div>
            <p>REPRESENTANTE LEGAL<br><strong>{{ config('app.name') }}</strong></p>
        </div>
        <div class="signature-box" style="float: right;">
            <p>RECIBÍ ORIGINAL (ACUSE)</p>
            <div style="margin-top: 60px;" class="line"></div>
            <p>{{ $empleado->nombre_completo }}<br>FECHA: ____/____/_______</p>
        </div>
    </div>
</body>
</html>