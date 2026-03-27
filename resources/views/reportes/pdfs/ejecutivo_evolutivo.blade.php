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
        
        .table { width: 100%; border-collapse: collapse; margin-top: 10px; background: #fff; }
        .table th { background-color: #0d6efd; color: white; padding: 10px; font-size: 11px; text-transform: uppercase; }
        .table td { border: 1px solid #dee2e6; padding: 8px; text-align: right; }
        .table td:first-child { text-align: left; font-weight: bold; background: #f8f9fa; }
        
        .analysis-section { 
            margin-top: 20px; 
            padding: 20px; 
            background: #f8f9fa; 
            border-left: 5px solid #0d6efd;
            page-break-inside: avoid;
            font-size: 11.5px; /* Letra perfecta para lectura de dictámenes largos */
            line-height: 1.6;
            text-align: justify;
        }
        .analysis-section h3 { margin-top: 0; color: #0d6efd; border-bottom: 1px solid #ccc; padding-bottom: 5px; margin-bottom: 15px;}
        
        .badge-admin { font-size: 9px; color: #666; display: block; font-weight: normal; }
        .text-success { color: #198754; font-weight: bold; }
        .text-danger { color: #dc3545; font-weight: bold; }
        
        .footer { position: fixed; bottom: 0; width: 100%; text-align: center; font-size: 9px; color: #999; border-top: 1px solid #eee; padding-top: 5px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Informe de Inteligencia Financiera</h1>
        <p>Análisis Estratégico: <strong>{{ $rangoFechas }}</strong></p>
    </div>

    <table width="100%" cellspacing="10" cellpadding="0" style="margin-bottom: 20px; border-collapse: separate;">
        <tr>
            <td style="background: #f8f9fa; padding: 15px 5px; text-align: center; border-radius: 8px; border: 1px solid #d1d9e6; width: 25%;">
                <span style="font-size: 10px; color: #6c757d; text-transform: uppercase;">Total Colocación</span><br>
                <strong style="font-size: 16px; color: #333;">${{ number_format(array_sum(array_column($stats, 'colocacion')), 2) }}</strong>
            </td>
            <td style="background: #f8f9fa; padding: 15px 5px; text-align: center; border-radius: 8px; border: 1px solid #d1d9e6; width: 25%;">
                <span style="font-size: 10px; color: #6c757d; text-transform: uppercase;">Intereses (Cosecha)</span><br>
                <strong style="font-size: 16px; color: #198754;">${{ number_format(array_sum(array_column($stats, 'intereses')), 2) }}</strong>
            </td>
            <td style="background: #f8f9fa; padding: 15px 5px; text-align: center; border-radius: 8px; border: 1px solid #d1d9e6; width: 25%;">
                <span style="font-size: 10px; color: #6c757d; text-transform: uppercase;">Gastos Operativos</span><br>
                <strong style="font-size: 16px; color: #dc3545;">${{ number_format(array_sum(array_column($stats, 'gastos')), 2) }}</strong>
            </td>
            @php $uTotal = array_sum(array_column($stats, 'utilidad')); @endphp
            <td style="background: #e9f2ff; padding: 15px 5px; text-align: center; border-radius: 8px; border: 1px solid #0d6efd; width: 25%;">
                <span style="font-size: 10px; color: #0d6efd; text-transform: uppercase; font-weight: bold;">Utilidad Neta Real</span><br>
                <strong style="font-size: 16px; color: {{ $uTotal >= 0 ? '#0d6efd' : '#dc3545' }};">
                    ${{ number_format($uTotal, 2) }}
                </strong>
            </td>
        </tr>
    </table>

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
        <h3>Dictamen Estratégico (IA Pro)</h3>
        <div style="white-space: pre-line; text-align: justify;">
            {{ $analysis }}
        </div>
    </div>

    <div class="footer">
        Este documento contiene información sensible procesada con Inteligencia Artificial Avanzada. Generado por CredinOs SaaS el {{ $fecha ?? date('d/m/Y') }}.
    </div>
</body>
</html>