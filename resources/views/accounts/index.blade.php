{{-- resources/views/accounts/index.blade.php --}}
<x-app-layout>
    {{-- No necesitamos un panel de filtros complejo por ahora, pero lo dejamos preparado --}}
    
    <div class="card shadow-sm">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Catálogo de Cuentas Contables</h5>
            <div>
                <a href="{{ route('accounts.create') }}" class="btn btn-success">
                    <i class="bi bi-plus-lg"></i> Nueva Cuenta Contable
                </a>
            </div>
        </div>
        <div class="card-body">
            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show mb-3" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <div class="table-responsive">
                <table class="table table-striped table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 15%;">Código</th>
                            <th>Nombre de la Cuenta</th>
                            <th style="width: 15%;">Tipo</th>
                            <th class="text-center" style="width: 15%;">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($accounts as $account)
                            {{-- Renderizamos la fila de la cuenta y sus hijas de forma recursiva --}}
                            @include('accounts.partials.account_row', ['account' => $account, 'level' => 0])
                        @empty
                            <tr>
                                <td colspan="4" class="text-center text-muted py-4">
                                    No hay cuentas contables registradas. ¡Crea la primera!
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Enlaces de Paginación --}}
            @if ($accounts->hasPages())
            <div class="mt-3">
                {{ $accounts->links() }}
            </div>
            @endif

        </div>
    </div>
</x-app-layout>