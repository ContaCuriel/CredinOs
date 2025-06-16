<x-app-layout>
    <div class="container-fluid py-4">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                    Lista de Empleados 
                    @if ($status_filter == 'alta')
                        (Activos)
                    @elseif ($status_filter == 'baja')
                        (Bajas)
                    @else
                        (Todos)
                    @endif
                </h5>
            
                @if (session('success'))
                    <div class="alert alert-success alert-dismissible fade show mb-0 py-2 me-2" role="alert">
                        {{ session('success') }}
                        <button type="button" class="btn-close py-2" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif
                @if (session('error'))
                    <div class="alert alert-danger alert-dismissible fade show mb-0 py-2 me-2" role="alert">
                        {{ session('error') }}
                        <button type="button" class="btn-close py-2" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif
            
                <a href="{{ route('empleados.create') }}" class="btn btn-success">
                    <i class="bi bi-plus-lg"></i> Nuevo Empleado
                </a>
            </div>

            <div class="card-body">
                {{-- Filtro de Status --}}
{{-- Filtros y Búsqueda --}}
                <form method="GET" action="{{ route('empleados.index') }}" class="mb-4">
                    <div class="row align-items-end g-2"> {{-- Usamos g-2 para un espacio menor entre elementos --}}
                        <div class="col-md-3">
                            <label for="status_filter" class="form-label mb-1">Status:</label>
                            <select name="status_filter" id="status_filter" class="form-select form-select-sm">
                                <option value="alta" {{ request('status_filter', 'alta') == 'alta' ? 'selected' : '' }}>Activos</option>
                                <option value="baja" {{ request('status_filter') == 'baja' ? 'selected' : '' }}>Bajas</option>
                                <option value="todos" {{ request('status_filter') == 'todos' ? 'selected' : '' }}>Todos</option>
                            </select>
                        </div>

                        <div class="col-md-3">
                            <label for="id_sucursal_filter" class="form-label mb-1">Sucursal:</label>
                            <select name="id_sucursal_filter" id="id_sucursal_filter" class="form-select form-select-sm">
                                <option value="">Todas las Sucursales</option>
                                @if(isset($sucursales))
                                    @foreach ($sucursales as $sucursal)
                                        <option value="{{ $sucursal->id_sucursal }}" {{ request('id_sucursal_filter') == $sucursal->id_sucursal ? 'selected' : '' }}>
                                            {{ $sucursal->nombre_sucursal }}
                                        </option>
                                    @endforeach
                                @endif
                            </select>
                        </div>

                        <div class="col-md-3"> {{-- Ajustamos ancho --}}
                            <label for="search_term" class="form-label mb-1">Buscar:</label>
                            <input type="text" name="search_term" id="search_term" class="form-control form-control-sm" 
                                   value="{{ old('search_term', request('search_term')) }}" placeholder="Nombre, CURP, RFC...">
                        </div>

                        <div class="col-md-3 d-flex"> {{-- Ajustamos ancho y usamos d-flex para alinear botones --}}
                            <button type="submit" class="btn btn-primary btn-sm flex-grow-1 me-1">Buscar</button>
                            {{-- =====> NUEVO BOTÓN "LIMPIAR FILTROS" <===== --}}
                            @if(request('status_filter') !== 'alta' || request('id_sucursal_filter') || request('search_term'))
                                <a href="{{ route('empleados.index') }}" class="btn btn-secondary btn-sm flex-grow-1 ms-1">Limpiar</a>
                            @endif
                            {{-- ========================================== --}}
                        </div>
                    </div>
                </form>
                {{-- Fin Filtros y Búsqueda --}}
                {{-- Fin Filtros y Búsqueda --}}
                {{-- Fin Filtro de Status --}}

                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>Nombre Completo</th>
                                <th>Puesto</th>
                                <th>Sucursal</th>
                                <th>Fecha Ingreso</th>
                                <th>RFC</th>
                                <th>CURP</th>
                                @if ($status_filter == 'baja' || $status_filter == 'todos')
                                    <th>Fecha Baja</th>
                                    <th>Motivo Baja</th>
                                @endif
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($empleados as $empleado)
                                <tr>
                                    <td>{{ $empleado->nombre_completo }}</td>
                                    <td>{{ $empleado->puesto ? $empleado->puesto->nombre_puesto : 'N/A' }}</td>
                                    <td>{{ $empleado->sucursal ? $empleado->sucursal->nombre_sucursal : 'N/A' }}</td>
                                    <td>{{ \Carbon\Carbon::parse($empleado->fecha_ingreso)->format('d/m/Y') }}</td>
                                    <td>{{ $empleado->rfc }}</td>
                                    <td>{{ $empleado->curp }}</td>
                                    @if ($status_filter == 'baja' || $status_filter == 'todos')
                                        <td>
                                            @if ($empleado->fecha_baja)
                                                {{ \Carbon\Carbon::parse($empleado->fecha_baja)->format('d/m/Y') }}
                                            @else
                                                N/A
                                            @endif
                                        </td>
                                        <td>{{ $empleado->motivo_baja ?: 'N/A' }}</td>
                                    @endif
                                    <td>
                                        @if ($empleado->status == 'Alta')
                                            <a href="{{ route('empleados.edit', $empleado->id_empleado) }}" class="btn btn-sm btn-info" title="Editar Empleado"><i class="bi bi-pencil-square"></i></a>
                                            
                                            <button type="button" class="btn btn-sm btn-danger btn-dar-baja" 
                                                    data-bs-toggle="modal" data-bs-target="#modalDarBaja"
                                                    data-id_empleado="{{ $empleado->id_empleado }}"
                                                    data-nombre_empleado="{{ $empleado->nombre_completo }}"
                                                    title="Dar de Baja">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                            
                                            <a href="#" class="btn btn-sm btn-warning ms-1" title="Generar Contrato"><i class="bi bi-file-earmark-text"></i></a>
                                        @else {{-- Si el status es 'Baja' --}}
                                            <span class="badge bg-secondary me-1">Dado de Baja</span>
                                            {{-- =====> NUEVO BOTÓN REACTIVAR <===== --}}
                                            <button type="button" class="btn btn-sm btn-success btn-reactivar-empleado"
                                                    data-bs-toggle="modal" data-bs-target="#modalReactivarEmpleado"
                                                    data-id_empleado="{{ $empleado->id_empleado }}"
                                                    data-nombre_empleado="{{ $empleado->nombre_completo }}"
                                                    data-id_puesto_actual="{{ $empleado->id_puesto }}"
                                                    data-id_sucursal_actual="{{ $empleado->id_sucursal }}"
                                                    title="Reactivar Empleado">
                                                <i class="bi bi-person-check-fill"></i> Reactivar
                                            </button>
                                            {{-- El botón de contrato no se muestra para bajas --}}
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="{{ ($status_filter == 'baja' || $status_filter == 'todos') ? '9' : '7' }}" class="text-center">
                                        @if ($status_filter == 'baja')
                                            No hay empleados de baja registrados.
                                        @elseif ($status_filter == 'todos' && $empleados->isEmpty())
                                            No hay empleados registrados.
                                        @elseif ($status_filter == 'alta' && $empleados->isEmpty())
                                            No hay empleados activos registrados.
                                        @else
                                            No hay empleados que coincidan con los filtros.
                                        @endif
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- Modal para Dar de Baja Empleado (Existente) --}}
                <div class="modal fade" id="modalDarBaja" tabindex="-1" aria-labelledby="modalDarBajaLabel" aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="modalDarBajaLabel">Confirmar Baja de Empleado</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <form id="formDarBaja" method="POST" action="">
                                @csrf
                                @method('DELETE')
                                <div class="modal-body">
                                    <p>¿Estás seguro de que quieres dar de baja al empleado <strong id="nombreEmpleadoBaja"></strong>?</p>
                                    <div class="mb-3">
                                        <label for="fecha_baja" class="form-label">Fecha de Baja <span class="text-danger">*</span></label>
                                        <input type="date" class="form-control" id="fecha_baja" name="fecha_baja" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="motivo_baja" class="form-label">Motivo de Baja (Opcional)</label>
                                        <textarea class="form-control" id="motivo_baja" name="motivo_baja" rows="3"></textarea>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                                    <button type="submit" class="btn btn-danger">Confirmar Baja</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                {{-- =====> NUEVO MODAL PARA REACTIVAR EMPLEADO <===== --}}
                <div class="modal fade" id="modalReactivarEmpleado" tabindex="-1" aria-labelledby="modalReactivarLabel" aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="modalReactivarLabel">Reactivar Empleado</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <form id="formReactivarEmpleado" method="POST" action=""> {{-- La action se pondrá con JS --}}
                                @csrf
                                @method('PUT') {{-- Usaremos PUT para la reactivación/actualización --}}
                                <div class="modal-body">
                                    <p>Reactivando a: <strong id="nombreEmpleadoReactivar"></strong></p>
                                    
                                    <div class="mb-3">
                                        <label for="fecha_ingreso_reingreso" class="form-label">Nueva Fecha de Ingreso <span class="text-danger">*</span></label>
                                        <input type="date" class="form-control" id="fecha_ingreso_reingreso" name="fecha_ingreso_reingreso" required>
                                    </div>

                                    <div class="mb-3">
                                        <label for="id_puesto_reingreso" class="form-label">Nuevo Puesto <span class="text-danger">*</span></label>
                                        <select class="form-select" id="id_puesto_reingreso" name="id_puesto_reingreso" required>
                                            <option value="">Seleccione un puesto...</option>
                                            @if(isset($puestos)) {{-- Verificamos que $puestos exista --}}
                                                @foreach ($puestos as $puesto)
                                                    <option value="{{ $puesto->id_puesto }}">
                                                        {{ $puesto->nombre_puesto }} ({{ number_format($puesto->salario_mensual, 2) }})
                                                    </option>
                                                @endforeach
                                            @endif
                                        </select>
                                    </div>

                                    <div class="mb-3">
                                        <label for="id_sucursal_reingreso" class="form-label">Nueva Sucursal <span class="text-danger">*</span></label>
                                        <select class="form-select" id="id_sucursal_reingreso" name="id_sucursal_reingreso" required>
                                            <option value="">Seleccione una sucursal...</option>
                                             @if(isset($sucursales)) {{-- Verificamos que $sucursales exista --}}
                                                @foreach ($sucursales as $sucursal)
                                                    <option value="{{ $sucursal->id_sucursal }}">
                                                        {{ $sucursal->nombre_sucursal }}
                                                    </option>
                                                @endforeach
                                            @endif
                                        </select>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                                    <button type="submit" class="btn btn-success">Confirmar Reactivación</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                {{-- ================================================ --}}

            </div> {{-- Fin card-body --}}
        </div> {{-- Fin card --}}
    </div> {{-- Fin container-fluid --}}

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Script para el modal de Dar de Baja (existente)
        var modalDarBaja = document.getElementById('modalDarBaja');
        if (modalDarBaja) { 
            var formDarBaja = document.getElementById('formDarBaja');
            var nombreEmpleadoBajaSpan = document.getElementById('nombreEmpleadoBaja');
            var fechaBajaInput = document.getElementById('fecha_baja');

            modalDarBaja.addEventListener('show.bs.modal', function (event) {
                var button = event.relatedTarget;
                if (!button || typeof button.getAttribute !== 'function') { 
                    console.error('ERROR (modalDarBaja): event.relatedTarget no es un botón válido.');
                    if(formDarBaja) formDarBaja.action = "#ERROR_NO_BUTTON_BAJA";
                    return;
                }
                var idEmpleado = button.getAttribute('data-id_empleado');
                var nombreEmpleado = button.getAttribute('data-nombre_empleado');
                
                if (nombreEmpleadoBajaSpan) nombreEmpleadoBajaSpan.textContent = nombreEmpleado;
                if (formDarBaja && idEmpleado) {
                    let baseActionUrl = "{{ route('empleados.destroy', ['empleado' => 'ID_PLACEHOLDER']) }}";
                    formDarBaja.action = baseActionUrl.replace('ID_PLACEHOLDER', idEmpleado);
                } else if (formDarBaja) {
                     formDarBaja.action = "#ERROR_ID_EMPLEADO_INVALIDO_BAJA";
                }
                if (fechaBajaInput) fechaBajaInput.value = new Date().toISOString().slice(0, 10);
            });
        } else {
            console.error('Modal con ID "modalDarBaja" NO encontrado.');
        }

        // =====> NUEVO SCRIPT PARA MODAL REACTIVAR <=====
        var modalReactivarEmpleado = document.getElementById('modalReactivarEmpleado');
        if (modalReactivarEmpleado) {
            var formReactivarEmpleado = document.getElementById('formReactivarEmpleado');
            var nombreEmpleadoReactivarSpan = document.getElementById('nombreEmpleadoReactivar');
            var fechaIngresoReingresoInput = document.getElementById('fecha_ingreso_reingreso');
            var selectPuestoReingreso = document.getElementById('id_puesto_reingreso');
            var selectSucursalReingreso = document.getElementById('id_sucursal_reingreso');

            modalReactivarEmpleado.addEventListener('show.bs.modal', function(event) {
                var button = event.relatedTarget;
                if (!button || typeof button.getAttribute !== 'function') {
                    console.error('ERROR (modalReactivarEmpleado): event.relatedTarget no es un botón válido.');
                    if(formReactivarEmpleado) formReactivarEmpleado.action = "#ERROR_NO_BUTTON_REACTIVAR";
                    return;
                }

                var idEmpleado = button.getAttribute('data-id_empleado');
                var nombreEmpleado = button.getAttribute('data-nombre_empleado');
                var idPuestoActual = button.getAttribute('data-id_puesto_actual');
                var idSucursalActual = button.getAttribute('data-id_sucursal_actual');

                if (nombreEmpleadoReactivarSpan) {
                    nombreEmpleadoReactivarSpan.textContent = nombreEmpleado;
                }

                if (formReactivarEmpleado && idEmpleado) {
                    // Usaremos la ruta que crearemos llamada 'empleados.reactivar'
                    let reactivarBaseUrl = "{{ route('empleados.reactivar', ['empleado' => 'ID_PLACEHOLDER']) }}";
                    formReactivarEmpleado.action = reactivarBaseUrl.replace('ID_PLACEHOLDER', idEmpleado);
                } else if (formReactivarEmpleado) {
                    formReactivarEmpleado.action = "#ERROR_ID_EMPLEADO_INVALIDO_REACTIVAR";
                }

                if (fechaIngresoReingresoInput) {
                    fechaIngresoReingresoInput.value = new Date().toISOString().slice(0, 10);
                }
                if (selectPuestoReingreso) {
                    selectPuestoReingreso.value = idPuestoActual; // Intenta preseleccionar el puesto actual
                }
                if (selectSucursalReingreso) {
                    selectSucursalReingreso.value = idSucursalActual; // Intenta preseleccionar la sucursal actual
                }
            });
        } else {
            console.error('Modal con ID "modalReactivarEmpleado" NO encontrado.');
        }
        // ================================================
    });
</script>
@endpush

</x-app-layout>