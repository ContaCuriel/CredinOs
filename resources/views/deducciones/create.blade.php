<x-app-layout>
    <div class="container-fluid py-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Registrar Nueva Deducción</h5>
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

                <form action="{{ route('deducciones.store') }}" method="POST">
                    @csrf

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="id_empleado" class="form-label">Empleado <span class="text-danger">*</span></label>
                            <select class="form-select @error('id_empleado') is-invalid @enderror" id="id_empleado" name="id_empleado" required>
                                <option value="">Seleccione un empleado...</option>
                                @if(isset($empleados))
                                    @foreach ($empleados as $empleado)
                                        <option value="{{ $empleado->id_empleado }}" {{ old('id_empleado') == $empleado->id_empleado ? 'selected' : '' }}>
                                            {{ $empleado->nombre_completo }}
                                        </option>
                                    @endforeach
                                @endif
                            </select>
                            @error('id_empleado') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="tipo_deduccion" class="form-label">Tipo de Deducción <span class="text-danger">*</span></label>
                            <select class="form-select @error('tipo_deduccion') is-invalid @enderror" id="tipo_deduccion" name="tipo_deduccion" required>
                                <option value="">Seleccione un tipo...</option>
                                @if(isset($tipos_deduccion))
                                    @foreach ($tipos_deduccion as $valor => $texto)
                                        <option value="{{ $valor }}" {{ old('tipo_deduccion') == $valor ? 'selected' : '' }}>{{ $texto }}</option>
                                    @endforeach
                                @endif
                            </select>
                            @error('tipo_deduccion') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="fecha_solicitud" class="form-label">Fecha de Inicio de la Deducción <span class="text-danger">*</span></label>
                        <input type="date" class="form-control @error('fecha_solicitud') is-invalid @enderror" id="fecha_solicitud" name="fecha_solicitud" value="{{ old('fecha_solicitud', now()->toDateString()) }}" required>
                        @error('fecha_solicitud') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div id="campos_deduccion_container">
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label for="monto_quincenal" class="form-label">Monto a Descontar (Quincenal) <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text">$</span>
                                    <input type="number" class="form-control @error('monto_quincenal') is-invalid @enderror" id="monto_quincenal" name="monto_quincenal" value="{{ old('monto_quincenal') }}" step="0.01" min="0.01" required>
                                </div>
                                @error('monto_quincenal') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                            </div>
                            
                            <div class="col-md-4 mb-3" id="campo_plazo" style="display: none;">
                                <label for="plazo_quincenas" class="form-label">Plazo (en quincenas) <span class="text-danger">*</span></label>
                                <input type="number" class="form-control @error('plazo_quincenas') is-invalid @enderror" id="plazo_quincenas" name="plazo_quincenas" value="{{ old('plazo_quincenas') }}" min="1">
                                @error('plazo_quincenas') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            
                            <div class="col-md-4 mb-3" id="campo_monto_total" style="display: none;">
                                <label for="monto_total_display" class="form-label">Monto Total del Préstamo</label>
                                <div class="input-group">
                                    <span class="input-group-text">$</span>
                                    <input type="text" class="form-control" id="monto_total_display" readonly>
                                </div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="descripcion" class="form-label">Descripción / Notas (Opcional)</label>
                            <textarea class="form-control @error('descripcion') is-invalid @enderror" id="descripcion" name="descripcion" rows="3">{{ old('descripcion') }}</textarea>
                            @error('descripcion') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <hr>
                    <div class="text-end">
                        <a href="{{ route('deducciones.index') }}" class="btn btn-secondary">Cancelar</a>
                        <button type="submit" class="btn btn-primary">Guardar Deducción</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
    {{-- Script para mostrar/ocultar campos dinámicamente --}}
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const tipoDeduccionSelect = document.getElementById('tipo_deduccion');
            const campoPlazo = document.getElementById('campo_plazo');
            const campoMontoTotal = document.getElementById('campo_monto_total');
            const plazoInput = document.getElementById('plazo_quincenas');
            const montoQuincenalInput = document.getElementById('monto_quincenal');
            const montoTotalDisplay = document.getElementById('monto_total_display');

            function toggleCamposPrestamo() {
                // CORRECCIÓN: Se usa 'Préstamo' para que coincida con el valor del <option>
                const esPrestamoConPlazo = tipoDeduccionSelect.value === 'Préstamo'; 

                if (esPrestamoConPlazo) {
                    campoPlazo.style.display = 'block';
                    plazoInput.setAttribute('required', 'required');
                    campoMontoTotal.style.display = 'block';
                } else {
                    campoPlazo.style.display = 'none';
                    plazoInput.removeAttribute('required');
                    campoMontoTotal.style.display = 'none';
                }
            }

            function calcularMontoTotal() {
                if (tipoDeduccionSelect.value === 'Préstamo') {
                    const montoQuincenal = parseFloat(montoQuincenalInput.value) || 0;
                    const plazo = parseInt(plazoInput.value) || 0;
                    const total = montoQuincenal * plazo;
                    montoTotalDisplay.value = total > 0 ? total.toFixed(2) : '';
                }
            }

            // Añadir los eventos
            tipoDeduccionSelect.addEventListener('change', toggleCamposPrestamo);
            montoQuincenalInput.addEventListener('input', calcularMontoTotal);
            plazoInput.addEventListener('input', calcularMontoTotal);

            // Ejecutar las funciones al cargar la página para reflejar el estado inicial
            toggleCamposPrestamo();
            if (tipoDeduccionSelect.value === 'Préstamo') {
                calcularMontoTotal();
            }
        });
    </script>
    @endpush
</x-app-layout>
