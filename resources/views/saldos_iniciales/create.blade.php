<x-app-layout>
    <div class="container mt-4">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card shadow">
                    <div class="card-header bg-dark text-white">
                        <h5 class="mb-0"><i class="fas fa-balance-scale"></i> Configurar Saldos Iniciales (Póliza de Apertura)</h5>
                    </div>
                    <div class="card-body">
                        @if($existePoliza)
                            <div class="alert alert-warning">
                                <strong>Aviso:</strong> Ya existe una Póliza de Apertura en el sistema. Volver a generarla podría duplicar tus saldos. Si deseas corregirla, te recomendamos editar o eliminar la póliza directamente desde el Libro de Diario.
                            </div>
                        @endif

                        <p class="text-muted">Ingresa los montos reales con los que la empresa inicia operaciones en este sistema. El Capital Contable se calculará y cuadrará automáticamente.</p>

                        <form action="{{ route('saldos-iniciales.store') }}" method="POST">
                            @csrf
                            
                            <div class="mb-3">
                                <label class="form-label font-weight-bold">Fecha de Corte (Póliza de Apertura)</label>
                                <input type="date" name="fecha_apertura" class="form-control" value="2026-07-31" required>
                                <small class="form-text text-muted">Recomendación: Usar el último día del mes anterior al inicio de operaciones (Ej. 31/07/2026).</small>
                            </div>

                            <h6 class="mt-4 border-bottom pb-2 text-primary">ACTIVOS (A favor de la empresa)</h6>
                            
                            <div class="row mb-3">
                                <div class="col-md-4">
                                    <label>Dinero en Bancos/Caja</label>
                                    <input type="number" step="0.01" name="bancos" class="form-control" value="0.00" required>
                                </div>
                                <div class="col-md-4">
                                    <label>Cartera de Clientes (Por cobrar)</label>
                                    <input type="number" step="0.01" name="clientes" class="form-control" value="0.00" required>
                                </div>
                                <div class="col-md-4">
                                    <label>Activo Fijo (Muebles, Equipos)</label>
                                    <input type="number" step="0.01" name="activo_fijo" class="form-control" value="0.00" required>
                                </div>
                            </div>

                            <h6 class="mt-4 border-bottom pb-2 text-danger">PASIVOS (Deudas de la empresa)</h6>
                            
                            <div class="row mb-4">
                                <div class="col-md-6">
                                    <label>Deudas a Proveedores / Acreedores</label>
                                    <input type="number" step="0.01" name="pasivos" class="form-control" value="0.00" required>
                                    <small class="form-text text-muted">Deja en 0 si no hay deudas operativas iniciales.</small>
                                </div>
                            </div>

                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary btn-lg" onclick="return confirm('¿Estás seguro de registrar estos saldos iniciales? Se generará una póliza contable maestra.')">
                                    Generar Póliza de Apertura
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>