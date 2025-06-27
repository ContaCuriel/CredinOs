<x-app-layout>
    <div class="card shadow-sm">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Historial de Colocaciones Mensuales</h5>
            <a href="{{ route('placements.create') }}" class="btn btn-success">
                <i class="bi bi-plus-lg"></i> Nuevo Registro
            </a>
        </div>
        <div class="card-body">
            @if (session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif
            <div class="table-responsive">
                <table class="table table-striped table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Periodo</th>
                            <th>Sucursal</th>
                            <th class="text-end">Monto Colocado</th>
                            <th>Registrado por</th>
                            <th class="text-center">Póliza Contable</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($placements as $placement)
                            <tr>
                                <td><strong>{{ \Carbon\Carbon::create($placement->year, $placement->month)->translatedFormat('F Y') }}</strong></td>
                                <td>{{ $placement->sucursal->nombre_sucursal }}</td>
                                <td class="text-end">${{ number_format($placement->amount, 2) }}</td>
                                <td>{{ $placement->user->name }}</td>
                                <td class="text-center">
                                    @if ($placement->journal)
                                        <a href="{{ route('journals.show', $placement->journal) }}" class="btn btn-sm btn-outline-primary" title="Ver Póliza">
                                            Póliza #{{ $placement->journal->id }}
                                        </a>
                                    @else
                                        <span class="badge text-bg-warning">Procesando...</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted py-4">
                                    No hay registros de colocación todavía.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if ($placements->hasPages())
            <div class="mt-3">
                {{ $placements->links() }}
            </div>
            @endif
        </div>
    </div>
</x-app-layout>
