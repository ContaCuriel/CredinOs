<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Ticket de Pago</title>
    <style>
        @page { margin: 2mm 4mm; }
        body { font-family: 'Helvetica', 'Arial', sans-serif; font-size: 10pt; color: #000; margin: 0; line-height: 1.3; }
        .text-center { text-align: center; }
        .fw-bold { font-weight: bold; }
        .logo { max-width: 140px; max-height: 55px; margin-bottom: 5px; }
        .title { font-size: 12pt; margin-top: 5px; letter-spacing: 1px; }
        .divider { border-top: 1px dashed #000; margin: 8px 0; }
        .amount { font-size: 16pt; margin: 5px 0; }
        .table-info { width: 100%; font-size: 9pt; margin-top: 10px; }
        .table-info td { padding: 2px 0; vertical-align: top; }
        .lbl { font-weight: bold; width: 35%; }
        .firma-box { margin-top: 40px; text-align: center; font-size: 9pt; }
        .firma-line { border-top: 1px solid #000; width: 70%; margin: 0 auto; padding-top: 3px; }
    </style>
</head>
<body>
    <div class="text-center">
        <div class="fw-bold title">TICKET DE PAGO</div>
        @if(isset($logo_base64) && $logo_base64)
            <img src="{{ $logo_base64 }}" class="logo mt-1">
        @else
            <h3 style="margin:5px 0;">{{ $credito->patron->nombre_comercial ?? 'EMPRESA EMISORA' }}</h3>
        @endif
        <div style="font-size: 9pt;">
            Folio: {{ strtoupper(substr($transaccion->corteCaja->caja->sucursal->nombre_sucursal ?? 'SUC', 0, 3)) }}{{ str_pad($transaccion->id, 5, '0', STR_PAD_LEFT) }}
        </div>
    </div>
    <div class="divider"></div>
    <div style="font-size: 9pt; margin-bottom: 5px;">
        <span class="fw-bold">Emitido por:</span><br>
        {{ mb_strtoupper($transaccion->corteCaja->usuario->name ?? 'CAJERO EN TURNO') }}
    </div>
    <div class="text-center fw-bold" style="font-size: 11pt; margin-top: 8px;">
        {{ mb_strtoupper($transaccion->concepto) }}
    </div>
    <div class="text-center" style="font-size: 9pt;">{{ ucfirst($transaccion->metodo_pago) }}</div>
    <div class="text-center amount fw-bold">
        Cantidad Depositada<br>
        ${{ number_format($transaccion->monto, 2) }}
    </div>
    <div class="text-center" style="font-size: 8pt; margin-bottom: 10px; font-style: italic;">
        ({{ $letras }})
    </div>
    <div class="divider"></div>
    <table class="table-info">
        <tr>
            <td class="lbl">Sucursal:</td>
            <td>{{ mb_strtoupper($transaccion->corteCaja->caja->sucursal->nombre_sucursal ?? 'N/A') }}</td>
        </tr>
        <tr>
            <td class="lbl">Grupo:</td>
            <td>{{ mb_strtoupper($credito->grupo->nombre_grupo ?? 'N/A (INDIVIDUAL)') }}</td>
        </tr>
        <tr>
            <td class="lbl">No. Semana:</td>
            <td>{{ $cuota->numero_cuota ?? 'N/A' }}</td>
        </tr>
        <tr>
            <td class="lbl">Cliente:</td>
            <td>{{ mb_strtoupper($credito->cliente->nombre_completo ?? $credito->cliente->nombre ?? 'N/A') }}</td>
        </tr>
        <tr>
            <td class="lbl">Fecha y Hora:</td>
            <td>{{ \Carbon\Carbon::parse($transaccion->created_at)->format('d/m/Y h:i:s A') }}</td>
        </tr>
    </table>
    <div class="firma-box"><div class="firma-line">Firma Cobrador</div></div>
    <div class="text-center fw-bold" style="margin-top: 20px; font-size: 8pt; color: #333;">
        {{ mb_strtoupper($credito->patron->nombre_comercial ?? 'FINANCIERA') }}<br>¡Gracias por su pago!
    </div>
</body>
</html>