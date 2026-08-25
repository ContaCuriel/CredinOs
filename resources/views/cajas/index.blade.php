<x-app-layout>
    <div class="container-fluid py-4">

        @if (session('error'))
            <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm mb-4" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-2"></i> {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <div class="row justify-content-center mt-4">
            <div class="col-md-6 col-lg-5">
                <div class="card border-0 shadow-lg rounded-4 overflow-hidden">
                    <div class="card-header bg-dark text-white text-center py-4 border-0">
                        <i class="bi bi-safe2 fs-1 d-block mb-2 text-warning"></i>
                        <h4 class="fw-bold mb-0">Apertura de Caja</h4>
                        <p class="mb-0 text-white-50 small">Inicia tu operación diaria para cobrar</p>
                    </div>
                    
                    <div class="card-body p-4 bg-white text-center">
                        <form action="{{ route('cajas.abrir') }}" method="POST">
                            @csrf
                            
                            <div class="mb-4 text-start">
                                <label class="form-label fw-bold text-muted small text-uppercase">Ubicación / Sucursal</label>
                                <select class="form-select form-select-lg shadow-sm" name="caja_id" required>
                                    <option value="" selected disabled>Seleccione la sucursal a operar...</option>
                                    @foreach($cajas as $caja)
                                        <option value="{{ $caja->id }}" {{ $caja->estatus == 'abierta' ? 'disabled' : '' }}>
                                            {{ $caja->sucursal->nombre_sucursal ?? 'Sucursal General' }} - {{ $caja->nombre }} 
                                            @if($caja->estatus == 'abierta') (En Uso) @endif
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="mb-4 text-start">
                                <label class="form-label fw-bold text-muted small text-uppercase">Fondo Fijo (Efectivo Inicial)</label>
                                <div class="input-group input-group-lg shadow-sm">
                                    <span class="input-group-text bg-light fw-bold text-dark border-end-0">$</span>
                                    <input type="number" step="0.01" min="0" class="form-control font-monospace text-primary fw-bold border-start-0" name="saldo_inicial" placeholder="Ej. 1000.00" required>
                                </div>
                                <div class="form-text text-muted small mt-2">
                                    <i class="bi bi-info-circle me-1"></i> Declara el efectivo con el que inicias en tu cajón para poder dar cambio.
                                </div>
                            </div>

                            <button type="submit" class="btn btn-success btn-lg w-100 fw-bold shadow-sm">
                                <i class="bi bi-unlock-fill me-2"></i> Confirmar y Abrir Turno
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

    </div>
</x-app-layout>