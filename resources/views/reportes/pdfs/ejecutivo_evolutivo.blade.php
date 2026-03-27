<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Informe Ejecutivo - Carlos Curiel</title>
    <style>
        @page { margin: 2cm; }
        body { font-family: 'Helvetica', sans-serif; color: #333; font-size: 11px; line-height: 1.6; }
        
        .header { text-align: center; margin-bottom: 25px; }
        .header h1 { margin: 0; color: #0d6efd; text-transform: uppercase; font-size: 18px; }
        .header p { font-size: 11px; color: #666; }

        .preparado-por { text-align: right; margin-bottom: 20px; font-size: 10px; color: #555; border-bottom: 1px solid #eee; padding-bottom: 5px; }

        .kpi-table { width: 100%; margin-bottom: 20px; border-collapse: collapse; }
        .kpi-card { background: #f9f9f9; border: 1px solid #eee; padding: 10px; text-align: center; border-radius: 5px; }
        .kpi-label { font-size: 9px; color: #888; text-transform: uppercase; display: block; margin-bottom: 3px; }
        .kpi-value { font-size: 14px; font-weight: bold; color: #333; }

        .main-table { width: 100%; border-collapse: collapse; margin-bottom: 30px; }
        .main-table th { background: #0d6efd; color: #fff; font-size: 9px; padding: 8px; text-transform: uppercase; border: 1px solid #0d6efd; }
        .main-table td { padding: 7px; border: 1px solid #eee; text-align: right; }
        .main-table td:first-child { text-align: left; font-weight: bold; background: #fafafa; }

        .analysis-container { 
            padding: 20px 0; 
            border-top: 2px solid #0d6efd; 
            margin-top: 20px;
        }
        .analysis-container h3 { color: #0d6efd; font-size: 14px; margin-bottom: 15px; border-bottom: 1px solid #eee; padding-bottom: 5px; }
        
        /* Formateo del contenido de la IA */
        .analysis-text { 
            text-align: justify; 
            white-space: pre-line; 
            font-size: 11px;
            color: #444;
        }
        .analysis-text h3 { font-size: 12px; color: #333; margin-top: 15px; margin-bottom: 5px; border: none; }

        .signature { margin-top: 40px; text-align: right; font-weight: bold; color: #0d6efd; font-size: 12px; }

        .footer { position: fixed; bottom: 0; width: 100%; text-align: center; font-size: 8px; color: #ccc; }
    </style>
</head>
<body>
    <div class="preparado-por">
        Preparado por: <strong>Carlos Curiel & Facturame.org</strong>
    </div>

    <div class="header">
        <h1>Informe de Resultados Operativos</h1>
        <p>Análisis del periodo: {{ $rangoFechas }}</p>
    </div>

    <table class="kpi-table" width="100%">
        <tr>
            <td width="25%"><div class="kpi-card"><span class="kpi-label">Préstamos Totales</span><span class="kpi-value">${{ number_format(array_sum(array_column($stats, 'colocacion')), 2) }}</span></div></td>
            <td width="25%"><div class="kpi-card"><span class="kpi-label">Ingresos Cobrados</span><span class="kpi-value" style="color: #198754;">${{ number_format(array_sum(array_column($stats, 'intereses')), 2) }}</span></div></td>
            <td width="25%"><div class="kpi-card"><span class="kpi-label">Gastos Operativos</span><span class="kpi-value" style="color: #dc3545;">${{ number_format(array_sum(array_column($stats, 'gastos')), 2) }}</span></div></td>
            @php $uTotal = array_sum(array_column($stats, 'utilidad')); @endphp
            <td width="25%"><div class="kpi-card" style="border: 1px solid #0d6efd; background: #f0f7ff;"><span class="kpi-label" style="color: #0d6efd;">Ganancia Neta</span><span class="kpi-value" style="color: #0d6efd;">${{ number_format($uTotal, 2) }}</span></div></td>
        </tr>
    </table>

    <table class="main-table">
        <thead>
            <tr>
                <th>Sucursal</th>
                <th>Colocación</th>
                <th>Intereses</th>
                <th>Gastos</th>
                <th>Ganancia</th>
                <th>Eficiencia</th>
            </tr>
        </thead>
        <tbody>
            @foreach($stats as $s)
            <tr>
                <td>{{ $s['sucursal'] }}</td>
                <td>${{ number_format($s['colocacion'], 2) }}</td>
                <td>${{ number_format($s['intereses'], 2) }}</td>
                <td>${{ number_format($s['gastos'], 2) }}</td>
                <td style="color: {{ $s['utilidad'] >= 0 ? '#198754' : '#dc3545' }}">
                    ${{ number_format($s['utilidad'], 2) }}
                </td>
                <td style="font-weight: bold;">
                    {{ $s['intereses'] > 0 ? round(($s['utilidad'] / $s['intereses']) * 100, 1) : 0 }}%
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="analysis-container">
        <h3>Dictamen de Negocio</h3>
        <div class="analysis-text">
            {{ $analysis }}
        </div>
        <div class="signature">
            Atentamente,<br>
            Carlos Curiel
        </div>
    </div>

    <div class="footer">
        Confidencial - Generado por CredinOs SaaS el {{ date('d/m/Y') }}
    </div>
</body>
</html>