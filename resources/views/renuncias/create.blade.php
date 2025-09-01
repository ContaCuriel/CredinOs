<x-app-layout>
    <div class="container-fluid py-4">
        <div class="card">
            <div class="card-header"><h5 class="mb-0">Generador de Cartas de Renuncia Voluntaria</h5></div>
            <div class="card-body">

                {{-- Muestra errores de validación si los hay --}}
                @if ($errors->any())
                    <div class="alert alert-danger">
                        <ul>
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                {{-- El formulario POST apuntará a nuestra nueva ruta --}}
                <form action="{{ route('renuncias.exportar.pdf') }}" method="POST" target="_blank">
                    @csrf
                    <div class="row p-3">
                        {{-- 1. Selección de Empleado --}}
                        <div class="col-md-12 mb-3">
                            <label for="id_empleado" class="form-label">Empleado <span class="text-danger">*</span></label>
                            <select class="form-select" id="id_empleado" name="id_empleado" required>
                                <option value="">Seleccione un empleado...</option>
                                @foreach ($empleados as $empleado)
                                    <option value="{{ $empleado->id_empleado }}"
                                            data-fecha_ingreso="{{ $empleado->fecha_ingreso?->format('Y-m-d') }}"
                                            data-fecha_baja="{{ $empleado->fecha_baja?->format('Y-m-d') }}">
                                        {{ $empleado->nombre_completo }} - ({{ $empleado->status }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- 2. Fechas --}}
                        <div class="col-md-6 mb-3">
                            <label for="fecha_ingreso" class="form-label">Fecha de Ingreso</label>
                            <input type="date" class="form-control" id="fecha_ingreso" readonly>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="fecha_baja" class="form-label">Fecha de Baja (Renuncia) <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="fecha_baja" name="fecha_baja" required>
                        </div>

                        {{-- 3. Selección de Patrón --}}
                        <div class="col-md-12 mb-4">
                            <label for="id_patron" class="form-label">Patrón para el Documento <span class="text-danger">*</span></label>
                            <select class="form-select" id="id_patron" name="id_patron" required>
                                <option value="">Seleccione un patrón...</option>
                                @foreach($patrones as $patron)
                                    <option value="{{ $patron->id_patron }}">{{ $patron->nombre_comercial }}</option>
                                @endforeach
                            </select>
                        </div>

                        {{-- 4. Botón de Generar --}}
                        <div class="col-12 text-end">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-journal-text me-2"></i>Generar Carta de Renuncia
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const empleadoSelect = document.getElementById('id_empleado');
            const fechaIngresoInput = document.getElementById('fecha_ingreso');
            const fechaBajaInput = document.getElementById('fecha_baja');

            empleadoSelect.addEventListener('change', function() {
                // Obtiene la opción seleccionada
                const selectedOption = this.options[this.selectedIndex];

                // Extrae las fechas de los atributos data-*
                const fechaIngreso = selectedOption.dataset.fecha_ingreso || '';
                const fechaBaja = selectedOption.dataset.fecha_baja || '';

                // Asigna los valores a los inputs
                fechaIngresoInput.value = fechaIngreso;
                // Si ya existe fecha de baja, la pone. Si no, el usuario la elige.
                fechaBajaInput.value = fechaBaja;
            });
        });
    </script>
    @endpush
</x-app-layout>