<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Reporte Ejecutivo Evolutivo</title>
    <style>
        @page { margin: 1cm; }
        body { font-family: 'Helvetica', sans-serif; color: #333; font-size: 12px; line-height: 1.4; }
        .header { text-align: center; border-bottom: 3px solid #0d6efd; padding-bottom: 10px; margin-bottom: 20px; }
        .header h1 { margin: 0; color: #0d6efd; text-transform: uppercase; }
        
        .kpi-container { width: 100%; margin-bottom: 20px; }
        .kpi-box { 
            width: 23%; 
            display: inline-block; 
            background: #f1f4f9; 
            padding: 15px 10px; 
            text-align: center; 
            border-radius: 8px; 
            border: 1px solid #d1d9e6;
        }
        
        .table { width: 100%; border-collapse: collapse; margin-top: 10px; background: #fff; }
        .table th { background-color: #0d6efd; color: white; padding: 10px; font-size: 11px; text-transform: uppercase; }
        .table td { border: 1px solid #dee2e6; padding: 8px; text-align: right; }
        .table td:first-child { text-align: left; font-weight: bold; background: #f8f9fa; }
        
        .analysis-section { 
            margin-top: 30px; 
            padding: 20px; 
            background: #f8f9fa; 
            border-left: 5px solid #0d6efd;
            page-break-inside: avoid;
        }
        .analysis-section h3 { margin-top: 0; color: #0d6efd; border-bottom: 1px solid #ccc; padding-bottom: 5px; }
        
        .badge-admin { font-size: 9px; color: #666; display: block; font-weight: normal; }
        .text-success { color: #198754; font-weight: bold; }
        .text-danger { color: #dc3545; font-weight: bold; }
        
        .footer { position: fixed; bottom: 0; width: 100%; text-align: center; font-size: 9px; color: #999; border-top: 1px solid #eee; padding-top: 5px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Informe de Inteligencia Financiera</h1>
        <p>Análisis Evolutivo: <strong>{{ $rangoFechas }}</strong></p>
    </div>

    <div class="kpi-container">
        <div class="kpi-box">
            <small>Total Colocación</small><br>
            <strong style="font-size: 16px;">${{ number_format(array_sum(array_column($stats, 'colocacion')), 2) }}</strong>
        </div>
        <div class="kpi-box">
            <small>Intereses (Cosecha)</small><br>
            <strong class="text-success" style="font-size: 16px;">${{ number_format(array_sum(array_column($stats, 'intereses')), 2) }}</strong>
        </div>
        <div class="kpi-box">
            <small>Gastos Operativos</small><br>
            <strong class="text-danger" style="font-size: 16px;">${{ number_format(array_sum(array_column($stats, 'gastos')), 2) }}</strong>
        </div>
        <div class="kpi-box">
            <small>Utilidad Neta Real</small><br>
            @php $uTotal = array_sum(array_column($stats, 'utilidad')); @endphp
            <strong style="font-size: 16px; color: {{ $uTotal >= 0 ? '#0d6efd' : '#000' }};">
                ${{ number_format($uTotal, 2) }}
            </strong>
        </div>
    </div>

    <table class="table">
        <thead>
            <tr>
                <th width="20%">Sucursal</th>
                <th>Colocación Total</th>
                <th>Intereses Totales</th>
                <th>Gastos Totales</th>
                <th>Utilidad Acumulada</th>
                <th>Margen s/Int</th>
            </tr>
        </thead>
        <tbody>
            @foreach($stats as $s)
            <tr>
                <td>
                    {{ $s['sucursal'] }}
                    @if($s['tipo'] == 'Administrativa')
                        <span class="badge-admin">(Centro de Costos)</span>
                    @endif
                </td>
                <td>${{ number_format($s['colocacion'], 2) }}</td>
                <td>${{ number_format($s['intereses'], 2) }}</td>
                <td>${{ number_format($s['gastos'], 2) }}</td>
                <td class="{{ $s['utilidad'] >= 0 ? 'text-success' : 'text-danger' }}">
                    ${{ number_format($s['utilidad'], 2) }}
                </td>
                <td>
                    {{ $s['intereses'] > 0 ? round(($s['utilidad'] / $s['intereses']) * 100, 1) : 0 }}%
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="analysis-section">
        <h3>Análisis Estratégico de Tendencias</h3>
        <div style="white-space: pre-line; text-align: justify;">
            {{ $analysis }}
        </div>
    </div>

    <div class="footer">
        Este documento contiene información sensible procesada con IA. Generado por CredinOs SaaS el {{ $fecha ?? date('d/m/Y') }}.
    </div>
</body>
</html>