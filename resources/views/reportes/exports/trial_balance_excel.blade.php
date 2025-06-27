{{-- Esta vista solo contiene la tabla, sin estilos ni layouts, para una exportación limpia. --}}
<table>
    <thead>
        <tr>
            <th colspan="8" style="text-align: center; font-weight: bold; font-size: 16px;">Balanza de Comprobación</th>
        </tr>
        <tr>
            <th colspan="8" style="text-align: center; font-weight: bold;">Del {{ $startDate }} al {{ $endDate }}</th>
        </tr>
        <tr>
            <th style="font-weight: bold;">Cuenta</th>
            <th style="font-weight: bold;">Nombre</th>
            <th style="font-weight: bold;">Saldo Inicial Deudor</th>
            <th style="font-weight: bold;">Saldo Inicial Acreedor</th>
            <th style="font-weight: bold;">Debe</th>
            <th style="font-weight: bold;">Haber</th>
            <th style="font-weight: bold;">Saldo Final Deudor</th>
            <th style="font-weight: bold;">Saldo Final Acreedor</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($accounts as $account)
            @include('reportes.partials._trial_balance_row', ['account' => $account, 'level' => 0, 'startDate' => $startDate, 'endDate' => $endDate])
        @endforeach
    </tbody>
</table>
