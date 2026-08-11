<x-app-layout>
    <div class="container mt-4">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card shadow">
                    <div class="card-header bg-dark text-white">
                        <h5 class="mb-0"><i class="fas fa-balance-scale"></i> Configurar Saldos Iniciales (Póliza de Apertura)</h5>
                    </div>
                    <div class="card-body">
                        
                        {{-- BLOQUE DE ALERTAS Y ERRORES --}}
                        @if(session('error'))
                            <div class="alert alert-danger font-weight-bold">
                                <i class="fas fa-exclamation-triangle"></i> {{ session('error') }}
                            </div>
                        @endif
                        @if($errors->any())
                            <div class="alert alert-danger">
                                <ul class="mb-0">
                                    @foreach($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                        {{-- FIN BLOQUE DE ALERTAS --}}

                        @if($existePoliza)
                            <div class="alert alert-warning">
                                <strong>Aviso:</strong> Ya existe una Póliza de Apertura en el sistema. Volver a generarla podría duplicar tus saldos. Si deseas corregirla, te recomendamos editar o eliminar la póliza directamente desde el Libro de Diario.
                            </div>
                        @endif

                        <p class="text-muted">Ingresa los montos reales con los que la empresa inicia operaciones en este sistema. El Capital Contable se calculará y cuadrará automáticamente.</p>

                        <form action="{{ route('saldos-iniciales.store') }}" method="POST">
                            @csrf
                            
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label class="form-label font-weight-bold">Fecha de Corte</label>
                                    <input type="date" name="fecha_apertura" class="form-control" value="{{ old('fecha_apertura', '2026-07-31') }}" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label font-weight-bold">Sucursal Matriz</label>
                                    <select name="sucursal_id" class="form-control" required>
                                        <option value="">Selecciona la sucursal...</option>
                                        @foreach($sucursales as $suc)
                                            <option value="{{ $suc->id_sucursal }}" {{ old('sucursal_id') == $suc->id_sucursal ? 'selected' : '' }}>
                                                {{ $suc->nombre_sucursal }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <h6 class="mt-4 border-bottom pb-2 text-primary">ACTIVOS (A favor de la empresa)</h6>
                            
                            <div class="row mb-3">
                                <div class="col-md-4">
                                    <label>Dinero en Bancos/Caja</label>
                                    <input type="number" step="0.01" name="bancos" class="form-control" value="{{ old('bancos', '0.00') }}" required>
                                </div>
                                <div class="col-md-4">
                                    <label>Cartera de Clientes</label>
                                    <input type="number" step="0.01" name="clientes" class="form-control" value="{{ old('clientes', '0.00') }}" required>
                                </div>
                                <div class="col-md-4">
                                    <label>Activo Fijo</label>
                                    <input type="number" step="0.01" name="activo_fijo" class="form-control" value="{{ old('activo_fijo', '0.00') }}" required>
                                </div>
                            </div>

                            <h6 class="mt-4 border-bottom pb-2 text-danger">PASIVOS (Deudas de la empresa)</h6>
                            
                            <div class="row mb-4">
                                <div class="col-md-6">
                                    <label>Deudas a Proveedores / Acreedores</label>
                                    <input type="number" step="0.01" name="pasivos" class="form-control" value="{{ old('pasivos', '0.00') }}" required>
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