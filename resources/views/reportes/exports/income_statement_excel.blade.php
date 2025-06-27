{{-- Esta vista es solo una tabla simple para la exportación a Excel --}}
<table>
    <thead>
        <tr>
            <th colspan="2" style="font-weight: bold; text-align: center; font-size: 16px;">Estado de Resultados</th>
        </tr>
        <tr>
            <th colspan="2" style="font-weight: bold; text-align: center;">Del {{ $startDate }} al {{ $endDate }}</th>
        </tr>
        <tr>
            <th colspan="2"></th> {{-- Fila vacía para espaciar --}}
        </tr>
        <tr>
            <th style="font-weight: bold;">Concepto</th>
            <th style="font-weight: bold;">Monto</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>(+) Ingresos por Intereses</td>
            <td>{{ $totalInterest }}</td>
        </tr>
        <tr>
            <td>(-) Gastos de Operación</td>
            <td>{{ $totalOperationalExpenses }}</td>
        </tr>
        <tr>
            <td style="font-weight: bold;">= Utilidad Operativa</td>
            <td style="font-weight: bold;">{{ $operatingProfit }}</td>
        </tr>
         <tr>
            <td>(-) Castigo por Cuentas Incobrables</td>
            <td>{{ $totalUnrecoverable }}</td>
        </tr>
        <tr style="font-weight: bold; background-color: #f2f2f2;">
            <td>= Utilidad (o Pérdida) Neta</td>
            <td>{{ $netIncome }}</td>
        </tr>
    </tbody>
</table>
