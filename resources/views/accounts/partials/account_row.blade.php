{{-- resources/views/accounts/partials/account_row.blade.php --}}
@php
    // El nivel de indentación se pasa desde la vista principal o la llamada recursiva
    $level = $level ?? 0;
@endphp

<tr>
    <td><strong>{{ $account->code }}</strong></td>
    <td>
        {{-- Aplicamos un padding para simular la jerarquía de árbol --}}
        <span style="padding-left: {{ $level * 25 }}px; @if($level == 0) font-weight: 500; @endif">
            {{ $account->name }}
        </span>
    </td>
    <td>
        @php
            $typeClass = match($account->type) {
                'activo'   => 'bg-primary',
                'pasivo'   => 'bg-danger',
                'capital'  => 'bg-info text-dark',
                'ingresos' => 'bg-success',
                'costos'   => 'bg-warning text-dark',
                'gastos'   => 'bg-secondary',
                default    => 'bg-light text-dark',
            };
        @endphp
        <span class="badge {{ $typeClass }}">{{ ucfirst($account->type) }}</span>
    </td>
    <td class="text-center">
        <div class="btn-group" role="group">
            <a href="{{ route('accounts.edit', $account) }}" class="btn btn-sm btn-info" title="Editar Cuenta">
                <i class="bi bi-pencil-square"></i>
            </a>
            <form action="{{ route('accounts.destroy', $account) }}" method="POST" onsubmit="return confirm('¿Estás seguro? Se eliminarán también todas sus subcuentas.');">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-sm btn-danger" title="Eliminar Cuenta" style="border-top-left-radius: 0; border-bottom-left-radius: 0;">
                    <i class="bi bi-trash"></i>
                </button>
            </form>
        </div>
    </td>
</tr>

{{-- Si la cuenta tiene hijos, los recorremos y volvemos a llamar a esta misma vista --}}
@if ($account->children->isNotEmpty())
    @foreach ($account->children->sortBy('code') as $child)
        @include('accounts.partials.account_row', ['account' => $child, 'level' => $level + 1])
    @endforeach
@endif