<x-app-layout>
    <div class="container-fluid py-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Editar Deducción para: {{ $deduccion->empleado->nombre_completo }}</h5>
            </div>
            <div class="card-body">
                @if ($errors->any())
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <h6 class="alert-heading fw-bold">¡Por favor corrige los siguientes errores!</h6>
                        <ul>
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                <form action="{{ route('deducciones.update', $deduccion->id) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Empleado</label>
                            <input type="text" class="form-control" value="{{ $deduccion->empleado->nombre_completo }}" readonly>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="tipo_deduccion" class="form-label">Tipo de Deducción <span class="text-danger">*</span></label>
                            <select class="form-select @error('tipo_deduccion') is-invalid @enderror" id="tipo_deduccion" name="tipo_deduccion" required>
                                <option value="">Seleccione un tipo...</option>
                                @foreach ($tipos_deduccion as $valor => $texto)
                                    <option value="{{ $valor }}" {{ old('tipo_deduccion', $deduccion->tipo_deduccion) == $valor ? 'selected' : '' }}>{{ $texto }}</option>
                                @endforeach
                            </select>
                            @error('tipo_deduccion') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="fecha_solicitud" class="form-label">Fecha de Inicio de la Deducción <span class="text-danger">*</span></label>
                        <input type="date" class="form-control @error('fecha_solicitud') is-invalid @enderror" id="fecha_solicitud" name="fecha_solicitud" value="{{ old('fecha_solicitud', $deduccion->fecha_solicitud->format('Y-m-d')) }}" required>
                        @error('fecha_solicitud') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    
                    <div id="campos_deduccion_container">
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label for="monto_quincenal" class="form-label">Monto a Descontar (Quincenal) <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text">$</span>
                                    <input type="number" class="form-control @error('monto_quincenal') is-invalid @enderror" id="monto_quincenal" name="monto_quincenal" value="{{ old('monto_quincenal', $deduccion->monto_quincenal) }}" step="0.01" min="0.01" required>
                                </div>
                                @error('monto_quincenal') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-4 mb-3" id="campo_plazo" style="display: none;">
                                <label for="plazo_quincenas" class="form-label">Plazo (en quincenas)</label>
                                <input type="number" class="form-control @error('plazo_quincenas') is-invalid @enderror" id="plazo_quincenas" name="plazo_quincenas" value="{{ old('plazo_quincenas', $deduccion->plazo_quincenas) }}" min="1">
                                @error('plazo_quincenas') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-4 mb-3" id="campo_monto_total" style="display: none;">
                                <label for="monto_total_display" class="form-label">Monto Total del Préstamo (calculado)</label>
                                <div class="input-group">
                                    <span class="input-group-text">$</span>
                                    <input type="text" class="form-control" id="monto_total_display" value="{{ old('monto_total_prestamo', $deduccion->monto_total_prestamo) }}" readonly>
                                </div>
                            </div>
                        </div>

                        {{-- =====> CAMPO FALTANTE AÑADIDO AQUÍ <===== --}}
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="status" class="form-label">Estado de la Deducción <span class="text-danger">*</span></label>
                                <select class="form-select @error('status') is-invalid @enderror" id="status" name="status" required>
                                    <option value="Activo" {{ old('status', $deduccion->status) == 'Activo' ? 'selected' : '' }}>Activo</option>
                                    <option value="Pagado" {{ old('status', $deduccion->status) == 'Pagado' ? 'selected' : '' }}>Pagado</option>
                                    <option value="Inactivo" {{ old('status', $deduccion->status) == 'Inactivo' ? 'selected' : '' }}>Inactivo</option>
                                    <option value="Cancelado" {{ old('status', $deduccion->status) == 'Cancelado' ? 'selected' : '' }}>Cancelado</option>
                                </select>
                                @error('status') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                <div class="form-text">"Activo" se incluirá en la Lista de Raya. "Inactivo" o "Pagado" no se incluirán.</div>
                            </div>
                        </div>
                        {{-- ========================================== --}}
                        
                        <div class="mb-3">
                            <label for="descripcion" class="form-label">Descripción / Notas (Opcional)</label>
                            <textarea class="form-control @error('descripcion') is-invalid @enderror" id="descripcion" name="descripcion" rows="3">{{ old('descripcion', $deduccion->descripcion) }}</textarea>
                            @error('descripcion') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <hr>
                    <div class="text-end">
                        <a href="{{ route('deducciones.index') }}" class="btn btn-secondary">Cancelar</a>
                        <button type="submit" class="btn btn-primary">Actualizar Deducción</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
        {{-- Reutilizamos el mismo script de la vista de creación --}}
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const tipoDeduccionSelect = document.getElementById('tipo_deduccion');
                const campoPlazo = document.getElementById('campo_plazo');
                const plazoInput = document.getElementById('plazo_quincenas');
                const campoMontoTotal = document.getElementById('campo_monto_total');
                const montoPagoInput = document.getElementById('monto_quincenal');
                const montoTotalDisplay = document.getElementById('monto_total_display');

                function toggleCamposPrestamo() {
                    const tipoSeleccionado = tipoDeduccionSelect.value;
                    if (tipoSeleccionado === 'Préstamo') {
                        campoPlazo.style.display = 'block';
                        plazoInput.setAttribute('required', 'required');
                        campoMontoTotal.style.display = 'block';
                    } else {
                        campoPlazo.style.display = 'none';
                        plazoInput.removeAttribute('required');
                        campoMontoTotal.style.display = 'none';
                    }
                }

                function calcularMontoTotalPrestamo() {
                    if (tipoDeduccionSelect.value === 'Préstamo') {
                        const montoPago = parseFloat(montoPagoInput.value) || 0;
                        const plazo = parseInt(plazoInput.value) || 0;
                        if (montoPago > 0 && plazo > 0) {
                            montoTotalDisplay.value = (montoPago * plazo).toFixed(2);
                        } else {
                            montoTotalDisplay.value = '';
                        }
                    }
                }

                if (tipoDeduccionSelect) {
                    tipoDeduccionSelect.addEventListener('change', toggleCamposPrestamo);
                    toggleCamposPrestamo();
                }
                if (montoPagoInput && plazoInput) {
                    montoPagoInput.addEventListener('input', calcularMontoTotalPrestamo);
                    plazoInput.addEventListener('input', calcularMontoTotalPrestamo);
                }
            });
        </script>
    @endpush
</x-app-layout>