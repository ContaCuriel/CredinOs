<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Dictamen Estratégico - Carlos Curiel</title>
    <style>
        /* Márgenes seguros para evitar desbordes en Landscape */
        @page { margin: 40px 50px; } 
        body { 
            font-family: 'Helvetica', sans-serif; 
            color: #2c3e50; 
            font-size: 11px; 
            margin: 0;
            padding: 0;
        }
        
        /* Encabezado limpio */
        .top-bar { width: 100%; margin-bottom: 25px; border-bottom: 2px solid #0d6efd; padding-bottom: 10px; }
        .top-bar table { width: 100%; border-collapse: collapse; }
        .top-bar td { vertical-align: bottom; }
        .title { font-size: 18px; color: #0d6efd; font-weight: bold; text-transform: uppercase; margin: 0; }
        .subtitle { font-size: 10px; color: #666; margin-top: 5px; }
        .author { text-align: right; font-size: 10px; color: #555; }
        .author strong { color: #0d6efd; }

        /* Tarjetas de KPIs usando tabla para no romperse */
        .kpi-container { width: 100%; margin-bottom: 25px; border-spacing: 10px; border-collapse: separate; }
        .kpi-card { 
            background: #f8f9fa; 
            border: 1px solid #ddd; 
            padding: 15px; 
            text-align: center; 
            border-radius: 6px; 
            width: 25%;
        }
        .kpi-label { font-size: 9px; color: #777; text-transform: uppercase; letter-spacing: 0.5px; }
        .kpi-val { font-size: 16px; font-weight: bold; color: #333; margin-top: 5px; display: block; }

        /* Tabla Principal */
        .data-table { width: 100%; border-collapse: collapse; margin-bottom: 30px; }
        .data-table th { background: #0d6efd; color: white; padding: 10px 8px; font-size: 9px; text-transform: uppercase; }
        .data-table td { padding: 8px; border-bottom: 1px solid #eee; text-align: right; }
        .data-table .row-name { text-align: left; font-weight: bold; background: #fafafa; border-left: 3px solid #0d6efd; }

        /* Contenedor del Análisis Blindado */
        .analysis-section { 
            width: 100%; 
            background: #ffffff; 
            padding: 15px 0;
            border-top: 1px solid #ddd;
        }
        .analysis-title { 
            color: #0d6efd; 
            font-size: 14px; 
            text-transform: uppercase; 
            margin-bottom: 15px;
            font-weight: bold;
        }
        .analysis-text { 
            text-align: justify; 
            font-size: 11px; 
            line-height: 1.6; 
            color: #333;
        }

        .firma { margin-top: 40px; text-align: right; font-size: 11px; color: #555; }
        .firma-nombre { font-size: 13px; font-weight: bold; color: #0d6efd; }

        .footer { position: fixed; bottom: -20px; width: 100%; text-align: center; font-size: 9px; color: #aaa; }
    </style>
</head>
<body>

    <div class="top-bar">
        <table>
            <tr>
                <td width="60%">
                    <div class="title">Reporte de Desempeño Operativo</div>
                    <div class="subtitle">Análisis del periodo: {{ $rangoFechas }}</div>
                </td>
                <td width="40%" class="author">
                    Preparado por:<br>
                    <strong>Carlos Curiel & Facturame.org</strong>
                </td>
            </tr>
        </table>
    </div>

    <table class="kpi-container">
        <tr>
            <td class="kpi-card">
                <span class="kpi-label">Préstamos Totales</span>
                <span class="kpi-val">${{ number_format(array_sum(array_column($stats, 'colocacion')), 2) }}</span>
            </td>
            <td class="kpi-card">
                <span class="kpi-label">Ingresos Cobrados</span>
                <span class="kpi-val" style="color: #198754;">${{ number_format(array_sum(array_column($stats, 'intereses')), 2) }}</span>
            </td>
            <td class="kpi-card">
                <span class="kpi-label">Gastos Operativos</span>
                <span class="kpi-val" style="color: #dc3545;">${{ number_format(array_sum(array_column($stats, 'gastos')), 2) }}</span>
            </td>
            @php $uTotal = array_sum(array_column($stats, 'utilidad')); @endphp
            <td class="kpi-card" style="border: 2px solid #0d6efd; background: #f0f7ff;">
                <span class="kpi-label" style="color: #0d6efd;">Ganancia Neta</span>
                <span class="kpi-val" style="color: #0d6efd;">${{ number_format($uTotal, 2) }}</span>
            </td>
        </tr>
    </table>

    <table class="data-table">
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
                <td class="row-name">{{ $s['sucursal'] }}</td>
                <td>${{ number_format($s['colocacion'], 2) }}</td>
                <td>${{ number_format($s['intereses'], 2) }}</td>
                <td>${{ number_format($s['gastos'], 2) }}</td>
                <td style="color: {{ $s['utilidad'] >= 0 ? '#198754' : '#dc3545' }}">${{ number_format($s['utilidad'], 2) }}</td>
                <td style="font-weight: bold;">{{ $s['intereses'] > 0 ? round(($s['utilidad'] / $s['intereses']) * 100, 1) : 0 }}%</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="analysis-section">
        <div class="analysis-title">Dictamen Estratégico</div>
        
        <div class="analysis-text">
            {!! nl2br(e($analysis)) !!}
        </div>
        
        <div class="firma">
            Atentamente,<br>
            <span class="firma-nombre">Carlos Curiel</span><br>
            Facturame.org
        </div>
    </div>

    <div class="footer">
        Documento Estratégico Confidencial - Generado el {{ date('d/m/Y') }}
    </div>

</body>
</html>