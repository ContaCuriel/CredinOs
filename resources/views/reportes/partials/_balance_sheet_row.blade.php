@php
    $initialBalance = $account->getInitialBalance($endDate);
    $isDebtor = in_array($account->type, ['activo', 'gastos']);
    // El saldo final para el balance es simplemente el saldo inicial a la fecha de corte.
    $finalBalance = $initialBalance;
@endphp

{{-- Mostramos solo si la cuenta o sus hijas tienen saldo --}}
@if(abs($finalBalance) > 0.001)
    <tr class="@if($level == 0) table-group-divider fw-bold @endif">
        <td style="padding-left: {{ $level * 20 + 5 }}px;">{{ $account->name }}</td>
        <td class="text-end @if($level > 0) text-muted @endif">
            ${{ number_format($finalBalance, 2) }}
        </td>
    </tr>
@endif

{{-- Si la cuenta tiene hijos, los mostramos --}}
@if ($account->children->isNotEmpty())
    @foreach ($account->children->sortBy('code') as $child)
        @include('reportes.partials._balance_sheet_row', ['account' => $child, 'endDate' => $endDate, 'level' => $level + 1])
    @endforeach
@endif
