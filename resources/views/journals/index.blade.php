<x-app-layout>
    <div class="card shadow-sm">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Libro de Diario - Pólizas Contables</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Folio</th>
                            <th>Fecha</th>
                            <th>Concepto</th>
                            <th>Origen</th>
                            <th class="text-end">Monto Total</th>
                            <th class="text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($journals as $journal)
                            <tr>
                                <td><strong>#{{ $journal->id }}</strong></td>
                                <td>{{ \Carbon\Carbon::parse($journal->date)->format('d/m/Y') }}</td>
                                <td>{{ $journal->concept }}</td>
                                <td>
                                    @if ($journal->sourceable)
                                        <span class="badge text-bg-info">
                                            Gasto #{{ $journal->sourceable->id }}
                                        </span>
                                    @else
                                        <span class="badge text-bg-secondary">Manual</span>
                                    @endif
                                </td>
                                {{-- Calculamos el total sumando los débitos (es igual a la suma de créditos) --}}
                                <td class="text-end">${{ number_format($journal->entries->sum('debit'), 2) }}</td>
                                <td class="text-center">
                                    <a href="{{ route('journals.show', $journal) }}" class="btn btn-sm btn-primary" title="Ver Detalles">
                                        <i class="bi bi-eye-fill"></i> Ver Póliza
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted py-4">
                                    No se han generado pólizas contables todavía.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Enlaces de Paginación --}}
            @if ($journals->hasPages())
            <div class="mt-3">
                {{ $journals->links() }}
            </div>
            @endif
        </div>
    </div>
</x-app-layout>
