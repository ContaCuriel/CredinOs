<x-app-layout>
    <div class="card shadow-sm">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Historial de Recuperación Mensual</h5>
            <a href="{{ route('recoveries.create') }}" class="btn btn-success">
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
                            <th class="text-end">Capital Recuperado</th>
                            <th class="text-end">Interés Cobrado</th>
                            <th class="text-end">Monto Castigado</th>
                            <th class="text-center">Póliza</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($recoveries as $recovery)
                            <tr>
                                <td><strong>{{ \Carbon\Carbon::create($recovery->year, $recovery->month)->translatedFormat('F Y') }}</strong></td>
                                <td>{{ $recovery->sucursal->nombre_sucursal }}</td>
                                <td class="text-end">${{ number_format($recovery->capital_recovered, 2) }}</td>
                                <td class="text-end text-success fw-bold">${{ number_format($recovery->interest_collected, 2) }}</td>
                                <td class="text-end text-danger">(${{ number_format($recovery->unrecoverable_amount, 2) }})</td>
                                <td class="text-center">
                                    @if ($recovery->journal)
                                        <a href="{{ route('journals.show', $recovery->journal) }}" class="btn btn-sm btn-outline-primary">
                                            #{{ $recovery->journal->id }}
                                        </a>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted py-4">No hay registros de recuperación todavía.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
             @if ($recoveries->hasPages())
                <div class="mt-3">{{ $recoveries->links() }}</div>
            @endif
        </div>
    </div>
</x-app-layout>
