@php
    // Usamos un bloque try-catch por si algún cálculo falla, para no detener todo el reporte.
    try {
        $initialBalance = $account->getInitialBalance($startDate);
        $movements = $account->getMovements($startDate, $endDate);
        
        $debits = $movements['debits'];
        $credits = $movements['credits'];

        $finalBalance = 0;
        $isDebtor = in_array($account->type, ['activo', 'gastos']);

        if ($isDebtor) {
            $finalBalance = $initialBalance + $debits - $credits;
        } else {
            $finalBalance = $initialBalance - $debits + $credits;
        }
    } catch (Exception $e) {
        // Si hay un error, inicializamos todo a cero para esta fila.
        $initialBalance = $debits = $credits = $finalBalance = 0;
        // Opcional: registrar el error
        // Log::error("Error al calcular saldos para cuenta {$account->code}: " . $e->getMessage());
    }
@endphp

<tr class="@if($level == 0) table-group-divider fw-bold @endif">
    {{-- Código y Nombre de la cuenta --}}
    <td>{{ $account->code }}</td>
    <td style="padding-left: {{ $level * 20 + 5 }}px;">{{ $account->name }}</td>

    {{-- Saldos Iniciales --}}
    <td class="text-end">{{ $isDebtor && $initialBalance != 0 ? number_format($initialBalance, 2) : '' }}</td>
    <td class="text-end">{{ !$isDebtor && $initialBalance != 0 ? number_format($initialBalance, 2) : '' }}</td>

    {{-- Movimientos del Periodo --}}
    <td class="text-end">{{ $debits > 0 ? number_format($debits, 2) : '' }}</td>
    <td class="text-end">{{ $credits > 0 ? number_format($credits, 2) : '' }}</td>

    {{-- Saldos Finales --}}
    <td class="text-end">{{ $isDebtor && $finalBalance != 0 ? number_format($finalBalance, 2) : '' }}</td>
    <td class="text-end">{{ !$isDebtor && $finalBalance != 0 ? number_format($finalBalance, 2) : '' }}</td>
</tr>

{{-- Si la cuenta tiene hijos, los mostramos recursivamente --}}
@if ($account->children->isNotEmpty())
    @foreach ($account->children->sortBy('code') as $child)
        @include('reportes.partials._trial_balance_row', ['account' => $child, 'level' => $level + 1, 'startDate' => $startDate, 'endDate' => $endDate])
    @endforeach
@endif