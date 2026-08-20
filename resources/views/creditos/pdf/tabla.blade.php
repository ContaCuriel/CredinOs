<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Control de Pagos</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 13px; color: #333; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .fw-bold { font-weight: bold; }
        .title { font-size: 18px; margin-bottom: 20px; letter-spacing: 2px; }
        
        /* Tabla de Información General */
        .info-table { width: 100%; border-collapse: collapse; margin-bottom: 30px; }
        .info-table td { padding: 5px; vertical-align: top; }
        .label { font-weight: bold; color: #555; }
        
        /* Tabla de Cuotas */
        .cuotas-table { width: 100%; border-collapse: collapse; margin-bottom: 50px; }
        .cuotas-table th { background-color: #f0f0f0; border-bottom: 2px solid #ccc; padding: 10px; text-align: left; font-weight: bold; }
        .cuotas-table td { padding: 10px; border-bottom: 1px solid #eee; }
        .cuotas-table th.center, .cuotas-table td.center { text-align: center; }
        
        /* Footer / Firma */
        .footer-firma { width: 100%; text-align: center; margin-top: 50px; }
        .linea-firma { width: 300px; border-top: 1px solid #000; margin: 0 auto 10px auto; }
        .brand-text { color: #007bff; font-weight: bold; font-size: 16px; margin-top: 20px; }
        .slogan { font-style: italic; color: #666; font-size: 11px; }
    </style>
</head>
<body>

    <div class="text-right fw-bold" style="margin-bottom: 10px;">
        {{ strtoupper($credito->sucursal->nombre_sucursal ?? 'TEXCOCO') }} a {{ now()->format('d/m/Y') }}
    </div>

    <div class="text-center title fw-bold">CONTROL DE PAGOS</div>

    <table class="info-table">
        <tr>
            <td width="50%">
                <span class="label">Grupo:</span><br>
                {{ $credito->grupo ? $credito->grupo->nombre_grupo : 'N/A' }}
            </td>
            <td width="50%">
                <span class="label">Cliente:</span><br>
                {{ $credito->cliente ? $credito->cliente->nombre_completo ?? $credito->cliente->nombre : 'N/A' }}
            </td>
        </tr>
        <tr>
            <td>
                <span class="label">Fecha Desembolso:</span><br>
                {{ \Carbon\Carbon::parse($credito->fecha_desembolso)->format('d/m/Y') }}
            </td>
            <td>
                <span class="label">Fecha Fin:</span><br>
                {{ \Carbon\Carbon::parse($fecha_fin)->format('d/m/Y') }}
            </td>
        </tr>
        <tr>
            <td>
                <span class="label">Monto:</span><br>
                ${{ number_format($credito->monto_aprobado, 2) }}
            </td>
            <td>
                <span class="label">Monto de Pago:</span><br>
                ${{ number_format($monto_pago, 2) }}
            </td>
        </tr>
        <tr>
            <td>
                <span class="label">Primer Pago:</span><br>
                {{ \Carbon\Carbon::parse($credito->fecha_primer_pago)->format('d/m/Y') }}
            </td>
            <td>
                <span class="label">Frecuencia:</span><br>
                {{ ucfirst($credito->producto->frecuencia_pago) }}
            </td>
        </tr>
        <tr>
            <td>
                <span class="label">Folio:</span><br>
                {{ $credito->folio }}
            </td>
            <td>
                <span class="label">Sucursal:</span><br>
                {{ strtoupper($credito->sucursal->nombre_sucursal ?? 'TEXCOCO') }}
            </td>
        </tr>
        <tr>
            <td colspan="2">
                <span class="label">Asesor:</span><br>
                {{ strtoupper($credito->asesor->nombre_completo ?? 'SIN ASIGNAR') }}
            </td>
        </tr>
    </table>

    <table class="cuotas-table">
        <thead>
            <tr>
                <th width="15%" class="center">No. PAGO</th>
                <th width="50%">FECHA</th>
                <th width="35%">MONTO</th>
            </tr>
        </thead>
        <tbody>
            @foreach($credito->amortizaciones as $cuota)
            <tr>
                <td class="center">{{ $cuota->numero_cuota }}</td>
                {{-- isoFormat('LL') convierte la fecha a "23 de junio de 2025" --}}
                <td>{{ ucwords(\Carbon\Carbon::parse($cuota->fecha_pago)->locale('es')->isoFormat('LL')) }}</td>
                <td class="fw-bold">${{ number_format($cuota->total_cuota, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer-firma">
        <div class="linea-firma"></div>
        <div class="fw-bold">FIRMA DEL CLIENTE</div>
        <div class="brand-text">credintegra</div>
        <div class="slogan">Tu crédito de la mano</div>
    </div>

</body>
</html>