{{-- Vista simple de tabla para exportar a Excel --}}
<table>
    <thead>
        <tr>
            <th colspan="2" style="font-weight: bold; text-align: center; font-size: 16px;">Balance General</th>
        </tr>
        <tr>
            <th colspan="2" style="font-weight: bold; text-align: center;">Al {{ \Carbon\Carbon::parse($endDate)->format('d/m/Y') }}</th>
        </tr>
        <tr><th></th></tr>
    </thead>
    <tbody>
        <tr>
            <td style="font-weight: bold;">ACTIVOS</td>
        </tr>
        @include('reportes.partials._balance_sheet_row', ['account' => $assetAccount, 'endDate' => $endDate, 'level' => 0])
        <tr style="font-weight: bold; border-top: 1px solid #000;">
            <td>Total Activos</td>
            <td>{{ $totalAssets }}</td>
        </tr>
        <tr><th></th></tr>
        
        <tr>
            <td style="font-weight: bold;">PASIVOS</td>
        </tr>
        @include('reportes.partials._balance_sheet_row', ['account' => $liabilityAccount, 'endDate' => $endDate, 'level' => 0])
        <tr style="font-weight: bold; border-top: 1px solid #000;">
            <td>Total Pasivos</td>
            <td>{{ $totalLiabilities }}</td>
        </tr>
        <tr><th></th></tr>

        <tr>
            <td style="font-weight: bold;">CAPITAL CONTABLE</td>
        </tr>
        @include('reportes.partials._balance_sheet_row', ['account' => $equityAccount, 'endDate' => $endDate, 'level' => 0])
        <tr>
            <td>Utilidad (o Pérdida) del Ejercicio</td>
            <td>{{ $netIncomeForPeriod }}</td>
        </tr>
        <tr style="font-weight: bold; border-top: 1px solid #000;">
            <td>Total Capital Contable</td>
            <td>{{ $totalEquity }}</td>
        </tr>
        <tr><th></th></tr>

        <tr style="font-weight: bold; background-color: #f2f2f2; border-top: 2px solid #000;">
            <td>TOTAL PASIVO + CAPITAL</td>
            <td>{{ $totalLiabilitiesAndEquity }}</td>
        </tr>
    </tbody>
</table>