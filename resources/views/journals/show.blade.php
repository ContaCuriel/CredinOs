<x-app-layout>
    <div class="card shadow-sm">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Detalle de Póliza de Diario #{{ $journal->id }}</h5>
                <a href="{{ route('journals.index') }}" class="btn btn-secondary">
                    <i class="bi bi-arrow-left"></i> Volver al Listado
                </a>
            </div>
        </div>
        <div class="card-body">
            {{-- SECCIÓN DE INFORMACIÓN GENERAL --}}
            <div class="row mb-4">
                <div class="col-md-6">
                    <strong>Fecha:</strong>
                    <p>{{ \Carbon\Carbon::parse($journal->date)->format('d \de F \de Y') }}</p>
                </div>
                <div class="col-md-6">
                    <strong>Concepto:</strong>
                    <p>{{ $journal->concept }}</p>
                </div>
            </div>

            {{-- TABLA DE ASIENTOS CONTABLES --}}
            <h6 class="mb-3">Asientos Contables</h6>
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead class="table-light">
                        <tr>
                            <th>Código</th>
                            <th>Cuenta Contable</th>
                            <th class="text-end">Debe (Cargo)</th>
                            <th class="text-end">Haber (Abono)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($journal->entries as $entry)
                        <tr>
                            <td>{{ $entry->account->code }}</td>
                            <td>{{ $entry->account->name }}</td>
                            <td class="text-end">{{ $entry->debit > 0 ? '$' . number_format($entry->debit, 2) : '-' }}</td>
                            <td class="text-end">{{ $entry->credit > 0 ? '$' . number_format($entry->credit, 2) : '-' }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        {{-- Fila de totales para asegurar que la póliza está cuadrada --}}
                        <tr class="fw-bold">
                            <td colspan="2" class="text-end">Totales:</td>
                            <td class="text-end">${{ number_format($journal->entries->sum('debit'), 2) }}</td>
                            <td class="text-end">${{ number_format($journal->entries->sum('credit'), 2) }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            {{-- PIE DE LA PÓLIZA (VALIDACIÓN) --}}
            <div class="text-center mt-3">
                @if($journal->entries->sum('debit') == $journal->entries->sum('credit'))
                    <span class="badge bg-success fs-6">
                        <i class="bi bi-check-circle-fill"></i> Póliza Cuadrada
                    </span>
                @else
                    <span class="badge bg-danger fs-6">
                        <i class="bi bi-exclamation-triangle-fill"></i> Póliza Descuadrada
                    </span>
                @endif
            </div>

        </div>
    </div>
</x-app-layout>
