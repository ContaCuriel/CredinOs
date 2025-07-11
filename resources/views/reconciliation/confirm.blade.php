<x-app-layout>
    <div class="container-fluid py-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Conciliación Bancaria - Confirmar Pagos</h5>
            </div>
            <div class="card-body">
                <p>El sistema ha encontrado las siguientes coincidencias. Por favor, selecciona los pagos que deseas procesar y aplicar.</p>

                <form action="{{ route('reconciliation.process') }}" method="POST">
                    @csrf
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th><input type="checkbox" id="select-all"></th>
                                    <th>Descripción (Banco)</th>
                                    <th class="text-end">Monto Depósito</th>
                                    <th>Titular del Crédito</th>
                                    <th>Cuota #</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($matches as $match)
                                    <tr>
                                        <td>
                                            <input type="checkbox" name="installments[]" value="{{ $match['installment']->id }}" checked>
                                        </td>
                                        <td>{{ $match['csv_row'][2] ?? 'N/A' }}</td>
                                        <td class="text-end">${{ number_format(floatval($match['csv_row'][4] ?? 0), 2) }}</td>
                                        <td>
                                            @if ($match['credito']->loanable_type == 'App\\Models\\Cliente')
                                                {{ $match['credito']->loanable->nombre }} {{ $match['credito']->loanable->apellido_paterno }}
                                            @else
                                                {{ $match['credito']->loanable->nombre_grupo }}
                                            @endif
                                        </td>
                                        <td>{{ $match['installment']->numero_pago }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center">No se encontraron coincidencias.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="text-end mt-3">
                        <a href="{{ route('reconciliation.create') }}" class="btn btn-secondary">Cancelar</a>
                        <button type="submit" class="btn btn-success">Confirmar y Procesar Pagos Seleccionados</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @push('scripts')
    <script>
        // Script simple para seleccionar/deseleccionar todas las casillas
        document.getElementById('select-all').addEventListener('click', function(event) {
            const checkboxes = document.querySelectorAll('input[name="installments[]"]');
            checkboxes.forEach(checkbox => {
                checkbox.checked = event.target.checked;
            });
        });
    </script>
    @endpush
</x-app-layout>