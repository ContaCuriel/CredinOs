<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: 'Helvetica', sans-serif; color: #333; }
        .header { text-align: center; border-bottom: 2px solid #0d6efd; padding-bottom: 10px; }
        .kpi-box { width: 30%; display: inline-block; background: #f8f9fa; padding: 15px; text-align: center; border-radius: 10px; margin: 1%; }
        .table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        .table th, .table td { border: 1px solid #dee2e6; padding: 8px; text-align: left; }
        .table th { background-color: #0d6efd; color: white; }
        .analysis-section { background: #e9ecef; padding: 20px; border-radius: 10px; margin-top: 30px; }
        .footer { position: fixed; bottom: 0; width: 100%; text-align: center; font-size: 10px; color: #777; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Reporte Ejecutivo de Rentabilidad</h1>
        <p>Periodo: {{ $periodo }} | Generado: {{ $fecha }}</p>
    </div>

    <div style="text-align: center; margin-top: 20px;">
        <div class="kpi-box">
            <small>Intereses Totales</small><br>
            <strong style="color: #198754; font-size: 20px;">${{ number_format($totalInteres, 2) }}</strong>
        </div>
        <div class="kpi-box">
            <small>Gastos Operativos</small><br>
            <strong style="color: #dc3545; font-size: 20px;">${{ number_format($totalGastos, 2) }}</strong>
        </div>
        <div class="kpi-box">
            <small>Utilidad Neta</small><br>
            <strong style="color: #0d6efd; font-size: 20px;">${{ number_format($totalInteres - $totalGastos, 2) }}</strong>
        </div>
    </div>

    <table class="table">
        <thead>
            <tr>
                <th>Sucursal</th>
                <th>Intereses</th>
                <th>Gastos</th>
                <th>Utilidad Neta</th>
            </tr>
        </thead>
        <tbody>
            @foreach($stats as $s)
<tr>
    <td>
        {{ $s['sucursal'] }} 
        @if($s['tipo'] == 'Administrativa') 
            <small style="color: #6c757d;">(Soporte Admivo.)</small> 
        @endif
    </td>
    <td>${{ number_format($s['intereses'], 2) }}</td>
    <td>${{ number_format($s['gastos'], 2) }}</td>
    <td style="font-weight: bold; color: {{ $s['tipo'] == 'Administrativa' ? '#333' : ($s['utilidad'] >= 0 ? '#198754' : '#dc3545') }}">
        ${{ number_format($s['utilidad'], 2) }}
    </td>
</tr>
@endforeach
        </tbody>
    </table>

    <div class="analysis-section">
        <h3 style="margin-top: 0; color: #0d6efd;">Análisis Estratégico (IA)</h3>
        <p style="white-space: pre-line;">{{ $analysis }}</p>
    </div>

    <div class="footer">
        CredinOs SaaS - Inteligencia Financiera aplicada. Confidencial.
    </div>
</body>
</html>