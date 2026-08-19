<x-app-layout>
    <div class="container-fluid py-4">
        <div class="card shadow-sm border-0" style="max-width: 800px; margin: 0 auto;">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <h5 class="mb-0 fw-bold text-dark"><i class="bi bi-pencil-square me-2 text-warning"></i>Editar Cuenta Receptora</h5>
                <a href="{{ route('cuentas_bancarias.index') }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left me-1"></i> Regresar</a>
            </div>
            <div class="card-body p-4 bg-light">
                
                @if ($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0 small">
                            @foreach ($errors->all() as $error) <li>{{ $error }}</li> @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('cuentas_bancarias.update', $cuenta->id) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Banco (Institución) <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="banco" value="{{ old('banco', $cuenta->banco) }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Nombre del Titular <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="titular" value="{{ old('titular', $cuenta->titular) }}" required>
                        </div>
                    </div>
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Número de Cuenta</label>
                            <input type="text" class="form-control font-monospace" name="numero_cuenta" value="{{ old('numero_cuenta', $cuenta->numero_cuenta) }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">CLABE Interbancaria</label>
                            <input type="text" class="form-control font-monospace" name="clabe" value="{{ old('clabe', $cuenta->clabe) }}">
                        </div>
                    </div>

                    <div class="row border-top pt-3 mt-3">
                        <div class="col-md-12">
                            <div class="form-check form-switch fs-5">
                                <input class="form-check-input" type="checkbox" role="switch" name="activa" id="activaSwitch" value="1" {{ $cuenta->activa ? 'checked' : '' }}>
                                <label class="form-check-label ms-2" for="activaSwitch">Cuenta Activa (Visible para recibir pagos)</label>
                            </div>
                        </div>
                    </div>

                    <div class="text-end mt-4">
                        <button type="submit" class="btn btn-warning fw-bold text-dark shadow-sm px-4"><i class="bi bi-arrow-clockwise me-1"></i> Actualizar Cuenta</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>