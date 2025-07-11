<x-app-layout>
    <div class="container-fluid py-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Conciliación Bancaria - Subir Estado de Cuenta</h5>
            </div>
            <div class="card-body">
                <p class="text-muted">Sube tu estado de cuenta en formato CSV. El sistema buscará los depósitos que coincidan con los números de referencia de los créditos pendientes.</p>

                @if (session('error'))
                    <div class="alert alert-danger">{{ session('error') }}</div>
                @endif

                <form action="{{ route('reconciliation.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="mb-3">
                        <label for="bank_statement" class="form-label">Archivo de Estado de Cuenta (.csv)</label>
                        <input class="form-control" type="file" id="bank_statement" name="bank_statement" required accept=".csv">
                    </div>
                    <div class="text-end">
                        <button type="submit" class="btn btn-primary">Procesar Archivo</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>