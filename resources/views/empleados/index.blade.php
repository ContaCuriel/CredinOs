<x-app-layout>
    <style>
        /* Fondo Animado iOS Mesh Gradient Sutil */
        .bg-system-islands {
            background: linear-gradient(-45deg, #f0f4fd, #f4f0fa, #f0faf5, #f5f8fc);
            background-size: 400% 400%;
            animation: subtleMesh 20s ease infinite;
            min-height: calc(100vh - 60px);
            padding-bottom: 3rem;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
        }

        @keyframes subtleMesh {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }

        /* Tarjetas Estilo Isla (Glassmorphism) */
        .ios-island {
            background: rgba(255, 255, 255, 0.85);
            backdrop-filter: blur(24px);
            -webkit-backdrop-filter: blur(24px);
            border: 1px solid rgba(255, 255, 255, 0.9);
            border-radius: 1.5rem;
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.04);
            padding: 1.5rem;
        }

        .ios-table-container {
            background: rgba(255, 255, 255, 0.85);
            backdrop-filter: blur(24px);
            -webkit-backdrop-filter: blur(24px);
            border-radius: 1.5rem;
            overflow: hidden;
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.04);
            border: 1px solid rgba(255, 255, 255, 0.9);
        }

        /* Tablas estilo iOS */
        .table-ios { margin-bottom: 0; }
        .table-ios thead th {
            background-color: rgba(245, 245, 247, 0.6) !important;
            color: #6e6e73 !important;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.75rem;
            letter-spacing: 0.5px;
            border-bottom: 1px solid rgba(0,0,0,0.05);
            padding: 1rem 1.5rem;
        }
        .table-ios tbody td {
            border-bottom: 1px solid rgba(0,0,0,0.03);
            padding: 1rem 1.5rem;
            vertical-align: middle;
            color: #1d1d1f;
        }
        .table-ios tbody tr:hover td {
            background-color: rgba(0,0,0,0.015);
        }

        /* Botones estilo iOS */
        .btn-ios-primary {
            background: linear-gradient(135deg, #0071e3, #005bb5);
            color: white;
            border-radius: 2rem;
            font-weight: 500;
            padding: 0.5rem 1.2rem;
            border: none;
            box-shadow: 0 4px 10px rgba(0, 113, 227, 0.2);
            transition: all 0.2s;
        }
        .btn-ios-primary:hover {
            background: linear-gradient(135deg, #0077ED, #0062c4);
            transform: translateY(-1px);
            box-shadow: 0 6px 15px rgba(0, 113, 227, 0.35);
            color: white;
        }
        
        .btn-ios-success {
            background: linear-gradient(135deg, #34c759, #28a745);
            color: white;
            border-radius: 2rem;
            font-weight: 500;
            padding: 0.5rem 1.2rem;
            border: none;
            box-shadow: 0 4px 10px rgba(40, 167, 69, 0.2);
            transition: all 0.2s;
        }
        .btn-ios-success:hover {
            background: linear-gradient(135deg, #3dd262, #2eb34c);
            transform: translateY(-1px);
            box-shadow: 0 6px 15px rgba(40, 167, 69, 0.35);
            color: white;
        }

        .btn-ios-outline {
            background-color: rgba(255,255,255,0.6);
            color: #6e6e73;
            border-radius: 2rem;
            font-weight: 600;
            padding: 0.5rem 1.2rem;
            border: 1px solid rgba(0,0,0,0.1);
            transition: all 0.2s;
        }
        .btn-ios-outline:hover {
            background-color: rgba(0,0,0,0.03);
            color: #1d1d1f;
        }

        /* Controles de Formulario */
        .form-control-ios, .form-select-ios {
            border-radius: 0.75rem;
            border: 1px solid rgba(0,0,0,0.1);
            background-color: rgba(255,255,255,0.8);
            padding: 0.5rem 1rem;
            color: #1d1d1f;
            transition: all 0.3s ease;
        }
        .form-control-ios:focus, .form-select-ios:focus {
            background-color: #ffffff;
            border-color: #0071e3;
            box-shadow: 0 0 0 3px rgba(0, 113, 227, 0.15);
            outline: none;
        }
    </style>

    <div class="container-fluid pt-4 px-4 bg-system-islands">
        
        {{-- HEADER PRINCIPAL TRANSPARENTE --}}
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h3 class="mb-0 fw-bold" style="color: #1d1d1f; letter-spacing: -0.5px;">Directorio de Empleados</h3>
                <p class="text-muted mb-0" style="font-size: 0.85rem;">
                    Mostrando empleados 
                    @if ($status_filter == 'alta') <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 rounded-pill px-2">Activos</span>
                    @elseif ($status_filter == 'baja') <span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25 rounded-pill px-2">Dados de Baja</span>
                    @else <span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary border-opacity-25 rounded-pill px-2">Todos (Histórico)</span>
                    @endif
                </p>
            </div>
            <div class="d-flex gap-2">
                {{-- BOTÓN: EXPORTAR A EXCEL --}}
                <a href="{{ route('empleados.exportar.excel', request()->all()) }}" class="btn-ios-success text-decoration-none shadow-sm">
                    <i class="bi bi-file-earmark-excel-fill me-1"></i> Exportar a Excel
                </a>
                
                <a href="{{ route('empleados.create') }}" class="btn-ios-primary text-decoration-none shadow-sm">
                    <i class="bi bi-person-plus-fill me-1"></i> Nuevo Empleado
                </a>
            </div>
        </div>

        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show ios-island mb-4 py-3 border-0 d-flex align-items-center" role="alert" style="background-color: rgba(229, 249, 231, 0.85); color: #198754;">
                <i class="bi bi-check-circle-fill me-2 fs-5"></i>{{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif
        @if (session('error'))
            <div class="alert alert-danger alert-dismissible fade show ios-island mb-4 py-3 border-0 d-flex align-items-center" role="alert" style="background-color: rgba(252, 232, 230, 0.85); color: #dc3545;">
                <i class="bi bi-exclamation-circle-fill me-2 fs-5"></i>{{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        {{-- ISLA 1: FORMULARIO DE FILTROS --}}
        <form method="GET" action="{{ route('empleados.index') }}" class="ios-island mb-4">
            <div class="row align-items-end g-3 justify-content-center">
                <div class="col-md-3">
                    <label for="status_filter" class="form-label fw-bold mb-1 ms-1" style="font-size: 0.8rem; color: #86868b;">Estatus</label>
                    <select name="status_filter" id="status_filter" class="form-select form-select-ios fw-semibold">
                        <option value="alta" {{ request('status_filter', 'alta') == 'alta' ? 'selected' : '' }}>Activos</option>
                        <option value="baja" {{ request('status_filter') == 'baja' ? 'selected' : '' }}>Bajas</option>
                        <option value="todos" {{ request('status_filter') == 'todos' ? 'selected' : '' }}>Todos</option>
                    </select>
                </div>

                <div class="col-md-3">
                    <label for="id_sucursal_filter" class="form-label fw-bold mb-1 ms-1" style="font-size: 0.8rem; color: #86868b;">Sucursal</label>
                    <select name="id_sucursal_filter" id="id_sucursal_filter" class="form-select form-select-ios fw-semibold">
                        <option value="">-- TODAS LAS SUCURSALES --</option>
                        @if(isset($sucursales))
                            @foreach ($sucursales as $sucursal)
                                <option value="{{ $sucursal->id_sucursal }}" {{ request('id_sucursal_filter') == $sucursal->id_sucursal ? 'selected' : '' }}>
                                    {{ $sucursal->nombre_sucursal }}
                                </option>
                            @endforeach
                        @endif
                    </select>
                </div>

                <div class="col-md-3">
                    <label for="search_term" class="form-label fw-bold mb-1 ms-1" style="font-size: 0.8rem; color: #86868b;">Búsqueda</label>
                    <input type="text" name="search_term" id="search_term" class="form-control form-control-ios fw-semibold" value="{{ old('search_term', request('search_term')) }}" placeholder="Nombre, CURP o RFC...">
                </div>

                <div class="col-md-3 d-flex gap-2">
                    <button type="submit" class="btn-ios-primary flex-fill shadow-sm py-2">
                        <i class="bi bi-search me-1"></i> Buscar
                    </button>
                    @if(request('status_filter') !== 'alta' || request('id_sucursal_filter') || request('search_term'))
                        <a href="{{ route('empleados.index') }}" class="btn-ios-outline text-decoration-none shadow-sm py-2" title="Limpiar Filtros">
                            <i class="bi bi-eraser-fill"></i>
                        </a>
                    @endif
                </div>
            </div>
        </form>

        {{-- ISLA 2: TABLA DE DATOS --}}
        <div class="ios-table-container mx-auto mb-5">
            <div class="table-responsive">
                <table class="table table-ios table-hover align-middle">
                    <thead>
                        <tr>
                            <th>Nombre Completo</th>
                            <th>Puesto</th>
                            <th>Sueldo</th>
                            <th>Sucursal</th>
                            <th>Ingreso</th>
                            <th>RFC</th>
                            <th>CURP</th>
                            @if ($status_filter == 'baja' || $status_filter == 'todos')
                                <th>Fecha Baja</th>
                                <th>Motivo Baja</th>
                            @endif
                            <th class="text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($empleados as $empleado)
                            <tr>
                                <td>
                                    <span class="fw-bold" style="color: #1d1d1f;">{{ $empleado->nombre_completo }}</span>
                                </td>
                                <td>
                                    <span class="badge rounded-pill fw-medium" style="font-size: 0.75rem; color: #0071e3; background: rgba(0, 113, 227, 0.1);">
                                        {{ $empleado->puesto ? $empleado->puesto->nombre_puesto : 'N/A' }}
                                    </span>
                                </td>
                                <td class="fw-semibold" style="font-size: 0.9rem; color: #155724;">
                                    ${{ number_format($empleado->puesto->salario_mensual ?? 0, 2) }}
                                </td>
                                <td>
                                    <span class="text-muted fw-medium" style="font-size: 0.85rem;">
                                        <i class="bi bi-shop me-1 opacity-50"></i>{{ $empleado->sucursal ? $empleado->sucursal->nombre_sucursal : 'S/S' }}
                                    </span>
                                </td>
                                <td class="fw-semibold" style="font-size: 0.9rem;">
                                    {{ \Carbon\Carbon::parse($empleado->fecha_ingreso)->format('d/m/Y') }}
                                </td>
                                <td style="font-family: monospace; font-size: 0.9rem; color: #6e6e73;">{{ $empleado->rfc ?: 'N/A' }}</td>
                                <td style="font-family: monospace; font-size: 0.9rem; color: #6e6e73;">{{ $empleado->curp }}</td>
                                
                                @if ($status_filter == 'baja' || $status_filter == 'todos')
                                    <td>
                                        @if ($empleado->fecha_baja)
                                            <span class="text-danger fw-bold" style="font-size: 0.9rem;">{{ \Carbon\Carbon::parse($empleado->fecha_baja)->format('d/m/Y') }}</span>
                                        @else
                                            <span class="text-muted opacity-50">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="text-truncate d-inline-block text-muted" style="max-width: 150px; font-size: 0.85rem;" title="{{ $empleado->motivo_baja }}">
                                            {{ $empleado->motivo_baja ?: 'N/A' }}
                                        </span>
                                    </td>
                                @endif
                                
                                <td class="text-center">
                                    @if ($empleado->status == 'Alta')
                                        <div class="d-flex justify-content-center gap-1">
                                            <a href="{{ route('empleados.edit', $empleado->id_empleado) }}" class="btn btn-sm shadow-sm" style="background: rgba(13,202,240,0.1); color: #0dcaf0; border-radius: 0.5rem;" title="Editar Empleado">
                                                <i class="bi bi-pencil-square"></i>
                                            </a>
                                            <button type="button" class="btn btn-sm shadow-sm" style="background: rgba(220,53,69,0.1); color: #dc3545; border-radius: 0.5rem;" 
                                                    data-bs-toggle="modal" data-bs-target="#modalDarBaja"
                                                    data-id_empleado="{{ $empleado->id_empleado }}"
                                                    data-nombre_empleado="{{ $empleado->nombre_completo }}"
                                                    title="Dar de Baja">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </div>
                                    @else
                                        <div class="d-flex justify-content-center gap-2 align-items-center">
                                            <span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary border-opacity-25 rounded-pill px-2">Baja</span>
                                            <button type="button" class="btn btn-sm shadow-sm fw-bold px-3 py-1" style="background: rgba(25,135,84,0.1); color: #198754; border-radius: 1rem;"
                                                    data-bs-toggle="modal" data-bs-target="#modalReactivarEmpleado"
                                                    data-id_empleado="{{ $empleado->id_empleado }}"
                                                    data-nombre_empleado="{{ $empleado->nombre_completo }}"
                                                    data-id_puesto_actual="{{ $empleado->id_puesto }}"
                                                    data-id_sucursal_actual="{{ $empleado->id_sucursal }}"
                                                    title="Reactivar Empleado">
                                                <i class="bi bi-person-check-fill me-1"></i> Reactivar
                                            </button>
                                        </div>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ ($status_filter == 'baja' || $status_filter == 'todos') ? '10' : '8' }}" class="text-center py-5">
                                    <i class="bi bi-inbox text-muted opacity-25" style="font-size: 3rem;"></i>
                                    <h5 class="fw-bold mt-3 text-dark">Sin registros</h5>
                                    <p class="text-muted mb-0">
                                        @if ($status_filter == 'baja') No hay empleados de baja registrados.
                                        @elseif ($status_filter == 'todos' && $empleados->isEmpty()) No hay empleados registrados en el sistema.
                                        @elseif ($status_filter == 'alta' && $empleados->isEmpty()) No hay empleados activos registrados.
                                        @else No hay empleados que coincidan con los filtros de búsqueda.
                                        @endif
                                    </p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- MODAL PARA DAR DE BAJA EMPLEADO --}}
    <div class="modal fade" id="modalDarBaja" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 1.5rem; background: rgba(255, 255, 255, 0.95); backdrop-filter: blur(24px); -webkit-backdrop-filter: blur(24px);">
                <div class="modal-header border-0 px-4 pt-4 pb-2">
                    <h5 class="modal-title fw-bold text-danger"><i class="bi bi-person-x-fill me-2"></i> Confirmar Baja</h5>
                    <button type="button" class="btn-close bg-light rounded-circle shadow-sm" data-bs-dismiss="modal" aria-label="Close" style="padding: 0.5rem;"></button>
                </div>
                <form id="formDarBaja" method="POST" action="">
                    @csrf
                    @method('DELETE')
                    <div class="modal-body px-4">
                        <p class="text-muted mb-4">Vas a dar de baja a: <strong id="nombreEmpleadoBaja" class="text-dark fs-5 d-block mt-1"></strong></p>
                        
                        <div class="mb-3">
                            <label for="fecha_baja" class="form-label fw-bold mb-1" style="font-size: 0.85rem; color: #86868b;">Fecha de Baja <span class="text-danger">*</span></label>
                            <input type="date" class="form-control form-control-ios bg-light" id="fecha_baja" name="fecha_baja" required>
                        </div>
                        <div class="mb-3">
                            <label for="motivo_baja" class="form-label fw-bold mb-1" style="font-size: 0.85rem; color: #86868b;">Motivo de Baja (Opcional)</label>
                            <textarea class="form-control form-control-ios bg-light" id="motivo_baja" name="motivo_baja" rows="3" placeholder="Razón de salida..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer border-0 px-4 pb-4 pt-0">
                        <button type="button" class="btn fw-semibold border-0" style="color: #86868b;" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn-ios-primary fw-bold px-4 shadow-sm" style="background-color: #dc3545;">Confirmar Baja</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- MODAL PARA REACTIVAR EMPLEADO --}}
    <div class="modal fade" id="modalReactivarEmpleado" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 1.5rem; background: rgba(255, 255, 255, 0.95); backdrop-filter: blur(24px); -webkit-backdrop-filter: blur(24px);">
                <div class="modal-header border-0 px-4 pt-4 pb-2">
                    <h5 class="modal-title fw-bold text-success"><i class="bi bi-person-check-fill me-2"></i> Reactivar Empleado</h5>
                    <button type="button" class="btn-close bg-light rounded-circle shadow-sm" data-bs-dismiss="modal" aria-label="Close" style="padding: 0.5rem;"></button>
                </div>
                <form id="formReactivarEmpleado" method="POST" action=""> 
                    @csrf
                    @method('PUT')
                    <div class="modal-body px-4">
                        <p class="text-muted mb-4">Reactivando a: <strong id="nombreEmpleadoReactivar" class="text-dark fs-5 d-block mt-1"></strong></p>
                        
                        <div class="mb-3">
                            <label for="fecha_ingreso_reingreso" class="form-label fw-bold mb-1" style="font-size: 0.85rem; color: #86868b;">Nueva Fecha de Ingreso <span class="text-danger">*</span></label>
                            <input type="date" class="form-control form-control-ios bg-light" id="fecha_ingreso_reingreso" name="fecha_ingreso_reingreso" required>
                        </div>

                        <div class="mb-3">
                            <label for="id_puesto_reingreso" class="form-label fw-bold mb-1" style="font-size: 0.85rem; color: #86868b;">Nuevo Puesto <span class="text-danger">*</span></label>
                            <select class="form-select form-select-ios bg-light" id="id_puesto_reingreso" name="id_puesto_reingreso" required>
                                <option value="">Seleccione un puesto...</option>
                                @if(isset($puestos))
                                    @foreach ($puestos as $puesto)
                                        <option value="{{ $puesto->id_puesto }}">
                                            {{ $puesto->nombre_puesto }} (${{ number_format($puesto->salario_mensual, 2) }})
                                        </option>
                                    @endforeach
                                @endif
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="id_sucursal_reingreso" class="form-label fw-bold mb-1" style="font-size: 0.85rem; color: #86868b;">Nueva Sucursal <span class="text-danger">*</span></label>
                            <select class="form-select form-select-ios bg-light" id="id_sucursal_reingreso" name="id_sucursal_reingreso" required>
                                <option value="">Seleccione una sucursal...</option>
                                 @if(isset($sucursales))
                                    @foreach ($sucursales as $sucursal)
                                        <option value="{{ $sucursal->id_sucursal }}">
                                            {{ $sucursal->nombre_sucursal }}
                                        </option>
                                    @endforeach
                                @endif
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer border-0 px-4 pb-4 pt-0">
                        <button type="button" class="btn fw-semibold border-0" style="color: #86868b;" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn-ios-primary fw-bold px-4 shadow-sm" style="background-color: #198754;">Confirmar Reactivación</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Lógica Modal Dar de Baja
        var modalDarBaja = document.getElementById('modalDarBaja');
        if (modalDarBaja) { 
            var formDarBaja = document.getElementById('formDarBaja');
            var nombreEmpleadoBajaSpan = document.getElementById('nombreEmpleadoBaja');
            var fechaBajaInput = document.getElementById('fecha_baja');

            modalDarBaja.addEventListener('show.bs.modal', function (event) {
                var button = event.relatedTarget;
                var idEmpleado = button.getAttribute('data-id_empleado');
                var nombreEmpleado = button.getAttribute('data-nombre_empleado');
                
                if (nombreEmpleadoBajaSpan) nombreEmpleadoBajaSpan.textContent = nombreEmpleado;
                if (formDarBaja && idEmpleado) {
                    let baseActionUrl = "{{ route('empleados.destroy', ['empleado' => 'ID_PLACEHOLDER']) }}";
                    formDarBaja.action = baseActionUrl.replace('ID_PLACEHOLDER', idEmpleado);
                }
                if (fechaBajaInput) fechaBajaInput.value = new Date().toISOString().slice(0, 10);
            });
        }

        // Lógica Modal Reactivar
        var modalReactivarEmpleado = document.getElementById('modalReactivarEmpleado');
        if (modalReactivarEmpleado) {
            var formReactivarEmpleado = document.getElementById('formReactivarEmpleado');
            var nombreEmpleadoReactivarSpan = document.getElementById('nombreEmpleadoReactivar');
            var fechaIngresoReingresoInput = document.getElementById('fecha_ingreso_reingreso');
            var selectPuestoReingreso = document.getElementById('id_puesto_reingreso');
            var selectSucursalReingreso = document.getElementById('id_sucursal_reingreso');

            modalReactivarEmpleado.addEventListener('show.bs.modal', function(event) {
                var button = event.relatedTarget;
                var idEmpleado = button.getAttribute('data-id_empleado');
                var nombreEmpleado = button.getAttribute('data-nombre_empleado');
                var idPuestoActual = button.getAttribute('data-id_puesto_actual');
                var idSucursalActual = button.getAttribute('data-id_sucursal_actual');

                if (nombreEmpleadoReactivarSpan) {
                    nombreEmpleadoReactivarSpan.textContent = nombreEmpleado;
                }

                if (formReactivarEmpleado && idEmpleado) {
                    let reactivarBaseUrl = "{{ route('empleados.reactivar', ['empleado' => 'ID_PLACEHOLDER']) }}";
                    formReactivarEmpleado.action = reactivarBaseUrl.replace('ID_PLACEHOLDER', idEmpleado);
                }

                if (fechaIngresoReingresoInput) {
                    fechaIngresoReingresoInput.value = new Date().toISOString().slice(0, 10);
                }
                if (selectPuestoReingreso) {
                    selectPuestoReingreso.value = idPuestoActual; 
                }
                if (selectSucursalReingreso) {
                    selectSucursalReingreso.value = idSucursalActual;
                }
            });
        }
    });
</script>
@endpush

</x-app-layout>