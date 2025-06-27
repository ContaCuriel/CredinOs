<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Estado de Resultados</title>
    <style>
        body { font-family: 'Helvetica', 'Arial', sans-serif; color: #333; }
        .header { text-align: center; margin-bottom: 20px; }
        .header h1 { margin: 0; font-size: 24px; }
        .header h2 { margin: 5px 0; font-size: 16px; color: #555; }
        .report-table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        .report-table th, .report-table td { border-bottom: 1px solid #ddd; padding: 12px; text-align: left; }
        .report-table td:last-child { text-align: right; }
        .total-row td { border-top: 2px solid #333; font-weight: bold; }
        .final-total { background-color: #f2f2f2; font-size: 1.1em; }
        .signatures { margin-top: 80px; width: 100%; }
        .signatures .signature-col { width: 48%; display: inline-block; text-align: center; }
        .signatures .signature-line { border-bottom: 1px solid #333; margin: 60px 20% 10px; }
        .signatures p { margin: 0; }
        .signatures .title { font-size: 0.9em; color: #555; }
        .text-success { color: #198754; }
        .text-danger { color: #dc3545; }
    </style>
</head>
<body>

    <div class="header">
        {{-- ===== CORRECCIÓN DE SINTAXIS PARA COMPATIBILIDAD ===== --}}
        <h1>{{ isset($companyName) && $companyName ? $companyName : 'Nombre de Empresa' }}</h1>
        <h2>Estado de Resultados</h2>
        <p>Del {{ \Carbon\Carbon::parse($startDate)->format('d/m/Y') }} al {{ \Carbon\Carbon::parse($endDate)->format('d/m/Y') }}</p>
    </div>

    <table class="report-table">
        <tbody>
            <tr><td>(+) Ingresos por Intereses</td><td class="text-success">${{ number_format($totalInterest, 2) }}</td></tr>
            <tr><td>(-) Gastos de Operación</td><td class="text-danger">(${{ number_format($totalOperationalExpenses, 2) }})</td></tr>
            <tr class="total-row"><td><strong>= Utilidad Operativa</strong></td><td><strong>${{ number_format($operatingProfit, 2) }}</strong></td></tr>
             <tr><td>(-) Castigo por Cuentas Incobrables</td><td class="text-danger">(${{ number_format($totalUnrecoverable, 2) }})</td></tr>
            <tr class="total-row final-total"><td><strong>= Utilidad (o Pérdida) Neta</strong></td><td><strong>${{ number_format($netIncome, 2) }}</strong></td></tr>
        </tbody>
    </table>

    
    <div class="signatures">
        <div class="signature-col">
            <div class="signature-line"></div>
            {{-- ===== SECCIÓN DEL REPRESENTANTE LEGAL (DINÁMICA) ===== --}}
            <p><strong>{{ isset($legalRepresentative) && $legalRepresentative ? $legalRepresentative : '____________________' }}</strong></p>
            <p class="title">Representante Legal</p>
        </div>
        <div class="signature-col" style="margin-left: 4%;">
            <div class="signature-line"></div>
            <p><strong>C.P. CARLOS ALBERTO MARTÍNEZ CURIEL</strong></p>
            <p class="title">Contador Público | Céd. Prof. 14713550</p>
        </div>
    </div>

</body>
</html>
