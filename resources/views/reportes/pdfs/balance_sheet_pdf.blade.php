<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Balance General</title>
    <style>
        body { font-family: 'Helvetica', 'Arial', sans-serif; color: #333; }
        .header { text-align: center; margin-bottom: 20px; }
        .header h1 { margin: 0; font-size: 24px; }
        .header h2 { margin: 5px 0; font-size: 16px; color: #555; }
        .report-section { margin-top: 20px; }
        .report-table { width: 100%; border-collapse: collapse; }
        .report-table td { padding: 8px 0; border-bottom: 1px solid #eee; }
        .report-table td:last-child { text-align: right; }
        .total-row td { border-top: 2px solid #333; font-weight: bold; }
        .signatures { margin-top: 80px; width: 100%; }
        .signatures .signature-col { width: 48%; display: inline-block; text-align: center; }
        .signatures .signature-line { border-bottom: 1px solid #333; margin: 60px 20% 10px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>{{ $companyName ?? 'Nombre de Empresa' }}</h1>
        <h2>Balance General</h2>
        {{-- ===== CORRECCIÓN DE FORMATO DE FECHA ===== --}}
        <p>Al {{ \Carbon\Carbon::parse($endDate)->translatedFormat('d \de F \de Y') }}</p>
    </div>
    <div class="report-section">
        <table class="report-table">
            <tr><td><h3>Activos</h3></td><td></td></tr>
            @if($assetAccount)
                @include('reportes.partials._balance_sheet_row', ['account' => $assetAccount, 'endDate' => $endDate, 'level' => 0])
            @endif
            <tr class="total-row"><td><strong>Total Activos</strong></td><td><strong>${{ number_format($totalAssets, 2) }}</strong></td></tr>
        </table>
    </div>
    <div class="report-section">
        <table class="report-table">
            <tr><td><h3>Pasivos</h3></td><td></td></tr>
            @if($liabilityAccount)
                @include('reportes.partials._balance_sheet_row', ['account' => $liabilityAccount, 'endDate' => $endDate, 'level' => 0])
            @endif
            <tr class="total-row"><td><strong>Total Pasivos</strong></td><td><strong>${{ number_format($totalLiabilities, 2) }}</strong></td></tr>
        </table>
    </div>
    <div class="report-section">
        <table class="report-table">
            <tr><td><h3>Capital Contable</h3></td><td></td></tr>
            @if($equityAccount)
                @include('reportes.partials._balance_sheet_row', ['account' => $equityAccount, 'endDate' => $endDate, 'level' => 0])
            @endif
            <tr><td>Utilidad (o Pérdida) del Ejercicio</td><td>${{ number_format($netIncomeForPeriod, 2) }}</td></tr>
            <tr class="total-row"><td><strong>Total Capital Contable</strong></td><td><strong>${{ number_format($totalEquity, 2) }}</strong></td></tr>
        </table>
    </div>
    <div class="report-section" style="background-color: #f2f2f2; padding: 10px; margin-top: 20px;">
        <table class="report-table">
            <tr class="total-row">
                <td><strong>TOTAL PASIVO + CAPITAL</strong></td>
                <td><strong>${{ number_format($totalLiabilitiesAndEquity, 2) }}</strong></td>
            </tr>
        </table>
    </div>
</body>
</html>
