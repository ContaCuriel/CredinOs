<x-app-layout>
    <div class="container-fluid py-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Registrar Periodo Vacacional</h5>
            </div>
            <div class="card-body">
                @if (session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif
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

                <form action="{{ route('vacaciones.store') }}" method="POST">
                    @csrf

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="id_empleado" class="form-label">Empleado <span class="text-danger">*</span></label>
                            <select class="form-select @error('id_empleado') is-invalid @enderror" id="id_empleado" name="id_empleado" required>
                                <option value="">Seleccione un empleado...</option>
                                @if(isset($empleados) && $empleados->count() > 0)
                                    @foreach ($empleados as $empleado_item)
                                        <option value="{{ $empleado_item->id_empleado }}"
                                                {{ (old('id_empleado', $preseleccionado_empleado_id ?? null) == $empleado_item->id_empleado) ? 'selected' : '' }}
                                                data-fecha_ingreso="{{ $empleado_item->fecha_ingreso ? \Carbon\Carbon::parse($empleado_item->fecha_ingreso)->toDateString() : '' }}">
                                            {{ $empleado_item->nombre_completo }} ({{ $empleado_item->rfc }})
                                        </option>
                                    @endforeach
                                @else
                                    <option value="" disabled>No hay empleados activos para seleccionar</option>
                                @endif
                            </select>
                            @error('id_empleado') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="ano_servicio_correspondiente" class="form-label">Año de Servicio al que Corresponden <span class="text-danger">*</span></label>
                            <input type="number" class="form-control @error('ano_servicio_correspondiente') is-invalid @enderror" id="ano_servicio_correspondiente" name="ano_servicio_correspondiente" value="{{ old('ano_servicio_correspondiente') }}" min="1" placeholder="Ej: 1, 2, etc." required>
                            @error('ano_servicio_correspondiente') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            <div class="form-text">Indica el N.º de año de servicio completado cuyas vacaciones se están tomando.</div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="fecha_inicio" class="form-label">Fecha de Inicio de Vacaciones <span class="text-danger">*</span></label>
                            <input type="date" class="form-control @error('fecha_inicio') is-invalid @enderror" id="fecha_inicio" name="fecha_inicio" value="{{ old('fecha_inicio') }}" required>
                            @error('fecha_inicio') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="fecha_fin" class="form-label">Fecha de Fin de Vacaciones <span class="text-danger">*</span></label>
                            <input type="date" class="form-control @error('fecha_fin') is-invalid @enderror" id="fecha_fin" name="fecha_fin" value="{{ old('fecha_fin') }}" required>
                            @error('fecha_fin') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="dias_tomados_display" class="form-label">Número de Días Tomados (calculado)</label>
                        {{-- Este campo es solo para mostrar, el cálculo real se hará en backend --}}
                        {{-- Podríamos hacerlo readonly o simplemente un span --}}
                        <input type="text" class="form-control" id="dias_tomados_display" readonly placeholder="Se calculará automáticamente">
                        {{-- No enviamos 'dias_tomados' desde el form, se calculará en el backend --}}
                        @error('dias_tomados') {{-- Por si alguna validación futura lo usa --}}
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="comentarios" class="form-label">Comentarios (Opcional)</label>
                        <textarea class="form-control @error('comentarios') is-invalid @enderror" id="comentarios" name="comentarios" rows="3">{{ old('comentarios') }}</textarea>
                        @error('comentarios') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <hr>
                    <div class="text-end">
                        <a href="{{ route('vacaciones.index') }}" class="btn btn-secondary">Cancelar</a>
                        <button type="submit" class="btn btn-primary">Guardar Periodo Vacacional</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const empleadoSelect = document.getElementById('id_empleado');
            const anoServicioInput = document.getElementById('ano_servicio_correspondiente');
            const fechaInicioInput = document.getElementById('fecha_inicio');
            const fechaFinInput = document.getElementById('fecha_fin');
            const diasTomadosDisplay = document.getElementById('dias_tomados_display');

            function actualizarAnoServicioSugerido() {
                const selectedOption = empleadoSelect.options[empleadoSelect.selectedIndex];
                const fechaIngresoStr = selectedOption.dataset.fecha_ingreso;

                if (fechaIngresoStr && anoServicioInput) {
                    const fechaIngreso = new Date(fechaIngresoStr + 'T00:00:00');
                    const hoy = new Date();
                    let anosCompletos = hoy.getFullYear() - fechaIngreso.getFullYear();
                    const mesActual = hoy.getMonth();
                    const diaActual = hoy.getDate();
                    const mesIngreso = fechaIngreso.getMonth();
                    const diaIngreso = fechaIngreso.getDate();
                    if (mesActual < mesIngreso || (mesActual === mesIngreso && diaActual < diaIngreso)) {
                        anosCompletos--;
                    }
                    anosCompletos = Math.max(0, anosCompletos);
                    anoServicioInput.value = (anosCompletos >= 1) ? anosCompletos : 1;
                } else if (anoServicioInput) {
                    anoServicioInput.value = '';
                }
            }

            function calcularDiasTomados() {
                if (fechaInicioInput.value && fechaFinInput.value && diasTomadosDisplay) {
                    const inicio = new Date(fechaInicioInput.value + 'T00:00:00');
                    const fin = new Date(fechaFinInput.value + 'T00:00:00');

                    if (fin >= inicio) {
                        // Calcula la diferencia en milisegundos y convierte a días
                        const diffTime = Math.abs(fin - inicio);
                        const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24)) + 1; // +1 para incluir ambos días
                        diasTomadosDisplay.value = diffDays;
                    } else {
                        diasTomadosDisplay.value = ''; // O un mensaje de error
                    }
                } else if (diasTomadosDisplay) {
                    diasTomadosDisplay.value = '';
                }
            }

            if (empleadoSelect) {
                empleadoSelect.addEventListener('change', actualizarAnoServicioSugerido);
                if (empleadoSelect.value) {
                    actualizarAnoServicioSugerido();
                }
            }

            if (fechaInicioInput && fechaFinInput) {
                fechaInicioInput.addEventListener('change', calcularDiasTomados);
                fechaFinInput.addEventListener('change', calcularDiasTomados);
                // Calcular al cargar la página si hay fechas old()
                if (fechaInicioInput.value && fechaFinInput.value) {
                     calcularDiasTomados();
                }
            }
        });
    </script>
    @endpush
</x-app-layout>