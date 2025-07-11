{{-- Dentro de la tarjeta "Plan de Pagos" --}}
<div class="table-responsive">
    <table class="table table-striped mb-0">
        <thead>
            <tr>
                <th># Pago</th>
                <th>Fecha de Vencimiento</th>
                <th class="text-end">Monto</th>
                <th class="text-center">Estatus</th>
                <th class="text-center">Acción</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($credito->paymentInstallments as $installment)
                <tr>
                    <td>{{ $installment->numero_pago }}</td>
                    <td>{{ \Carbon\Carbon::parse($installment->fecha_vencimiento)->format('d/m/Y') }}</td>
                    <td class="text-end">${{ number_format($installment->monto_pago, 2) }}</td>
                    <td class="text-center">
                        @if($installment->status == 'Pagado')
                            <span class="badge bg-success">{{ $installment->status }}</span>
                        @else
                            <span class="badge bg-secondary">{{ $installment->status }}</span>
                        @endif
                    </td>
                    <td class="text-center">
                        {{-- Mostramos el botón solo si el pago está pendiente --}}
                        @if($installment->status == 'Pendiente')
                            <form action="{{ route('payments.store', $installment->id) }}" method="POST">
                                @csrf
                                <button type="submit" class="btn btn-success btn-sm">Registrar Pago</button>
                            </form>
                        @else
                            -
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="text-center py-4">
                        <p class="text-muted mb-0">El plan de pagos aún no ha sido generado.</p>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>