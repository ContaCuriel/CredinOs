<x-app-layout>
    <div class="container-fluid py-4">
        <div class="card shadow-sm">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0 text-dark fw-bold">Registro de Asistencia Diario</h5>
                <div>
                    {{-- Botón para ir al resumen de incidencias (Pre-Nómina) --}}
                    <a href="{{ route('asistencia.resumenIncidencias') }}" class="btn btn-info btn-sm me-2 text-white">
                        <i class="bi bi-file-earmark-text"></i> Ver Resumen de Incidencias
                    </a>
                    {{-- Botón para ir a la vista de periodo --}}
                    <a href="{{ route('asistencia.vistaPeriodo') }}" class="btn btn-outline-primary btn-sm">
                        <i class="bi bi-calendar3-week"></i> Ver Asistencia por Periodo
                    </a>
                </div>
            </div>
            <div class="card-body">
                @if (session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif
                @if (session('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                {{-- Formulario de Selección de Sucursal --}}
                <form id="selectSucursalForm" method="GET" action="{{ route('asistencia.index') }}" class="mb-3">
                    <div class="row justify-content-center">
                        <div class="col-md-6">
                            <label for="id_sucursal_seleccionada" class="form-label fw-bold">Seleccione la Sucursal:</label>
                            <div class="input-group shadow-sm">
                                <select class="form-select" id="id_sucursal_seleccionada" name="id_sucursal_seleccionada" onchange="this.form.submit()">
                                    <option value="">-- Seleccione una Sucursal --</option>
                                    <option value="todas" {{ request('id_sucursal_seleccionada') == 'todas' ? 'selected' : '' }} class="fw-bold text-primary">-- TODAS LAS SUCURSALES --</option>
                                    @foreach ($sucursales as $sucursal)
                                        <option value="{{ $sucursal->id_sucursal }}" {{ request('id_sucursal_seleccionada') == $sucursal->id_sucursal ? 'selected' : '' }}>
                                            {{ $sucursal->nombre_sucursal }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                </form>
                <hr class="mt-4">

                @if(isset($id_sucursal_seleccionada) && $id_sucursal_seleccionada)
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h4 class="mb-0">Registrar Asistencia para: <span class="text-primary fw-bold">{{ $sucursalSeleccionadaNombre ?? '' }}</span></h4>
                        <span class="badge bg-light text-dark border p-2 fs-6">
                            <i class="bi bi-calendar-event"></i> Fecha: <strong>{{ \Carbon\Carbon::today()->translatedFormat('d \d\e F \d\e Y') }}</strong>
                        </span>
                    </div>
                    
                    @if(isset($empleadosDeSucursal) && $empleadosDeSucursal->isNotEmpty())
                        <div class="table-responsive shadow-sm" style="border-radius: 8px;">
                            <table class="table table-bordered table-hover align-middle mb-0">
                                <thead class="table-dark">
                                    <tr>
                                        <th>Empleado</th>
                                        <th class="text-center" style="width: 25%;">Estado Hoy / Hora Llegada</th>
                                        <th class="text-center" style="width: 35%;">Acciones Rápidas</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($empleadosDeSucursal as $empleado)
                                        <tr>
                                            <td class="ps-3">
                                                <div class="fw-bold text-dark fs-6">{{ $empleado->nombre_completo }}</div>
                                                @if($id_sucursal_seleccionada === 'todas')
                                                    <span class="badge bg-secondary" style="font-size: 0.7em;">
                                                        <i class="bi bi-shop"></i> {{ $empleado->sucursal->nombre_sucursal ?? 'Sin sucursal' }}
                                                    </span>
                                                @endif
                                            </td>
                                            
                                            @php
                                                $asistenciaDelDia = $asistenciasHoy->get($empleado->id_empleado);
                                                $claseFondo = '';
                                                if ($asistenciaDelDia) {
                                                    switch ($asistenciaDelDia->status_asistencia) {
                                                        case 'Retardo': $claseFondo = 'bg-warning bg-opacity-10'; break;
                                                        case 'Falta': $claseFondo = 'bg-danger bg-opacity-10'; break;
                                                    }
                                                }
                                            @endphp
                                            <td class="text-center {{ $claseFondo }}">
                                                <div class="estado-display" data-empleado-id="{{ $empleado->id_empleado }}" style="cursor: pointer;">
                                                    @if ($asistenciaDelDia)
                                                        @if (in_array($asistenciaDelDia->status_asistencia, ['Presente', 'Retardo']) && $asistenciaDelDia->hora_llegada)
                                                          <span class="badge bg-{{$asistenciaDelDia->status_asistencia == 'Retardo' ? 'warning text-dark' : 'success'}} fs-6 me-1">{{ \Carbon\Carbon::parse($asistenciaDelDia->hora_llegada)->format('h:i A') }}</span>
                                                          <i class="bi bi-pencil-fill text-primary edit-hora-icon" title="Editar Hora"></i>
                                                        @elseif ($asistenciaDelDia->status_asistencia == 'Falta')
                                                            <span class="badge bg-danger fs-6">FALTA</span>
                                                        @elseif ($asistenciaDelDia->status_asistencia == 'Baja_Dia')
                                                            <span class="badge bg-dark fs-6">BAJA DEL DÍA</span>
                                                        @elseif ($asistenciaDelDia->status_asistencia == 'Incidencia')
                                                            <span class="badge bg-info fs-6">INCIDENCIA</span>
                                                            @if($asistenciaDelDia->notas_incidencia)
                                                                <i class="bi bi-info-circle ms-1" title="{{ $asistenciaDelDia->notas_incidencia }}" data-bs-toggle="tooltip"></i>
                                                            @endif
                                                        @else
                                                            <span class="badge bg-light text-dark fs-6">{{ $asistenciaDelDia->status_asistencia }}</span>
                                                        @endif
                                                    @else
                                                        <span class="text-muted hora-placeholder">--:--</span>
                                                        <i class="bi bi-alarm text-primary ms-1" title="Click para ingresar hora manual"></i>
                                                    @endif
                                                </div>
                                                <div class="inline-edit-form" style="display: none;">
                                                    <form method="POST" action="{{ route('asistencia.registrarEntrada') }}" class="d-inline-flex align-items-center">
                                                        @csrf
                                                        <input type="hidden" name="id_empleado" value="{{ $empleado->id_empleado }}">
                                                        <input type="hidden" name="id_sucursal_seleccionada" value="{{ $id_sucursal_seleccionada }}">
                                                        <input type="time" name="hora_llegada_manual" class="form-control form-control-sm me-1" style="width: 100px;" required>
                                                        <button type="submit" class="btn btn-sm btn-success me-1" title="Guardar"><i class="bi bi-check-lg"></i></button>
                                                        <button type="button" class="btn btn-sm btn-secondary btn-cancel-edit-hora" title="Cancelar"><i class="bi bi-x-lg"></i></button>
                                                    </form>
                                                </div>
                                            </td>
                                            <td class="text-center">
                                                @if (!$asistenciasHoy->has($empleado->id_empleado) || !$asistenciasHoy->get($empleado->id_empleado)->hora_llegada)
                                                    <form method="POST" action="{{ route('asistencia.registrarEntrada') }}" class="d-inline-block">
                                                        @csrf
                                                        <input type="hidden" name="id_empleado" value="{{ $empleado->id_empleado }}">
                                                        <input type="hidden" name="id_sucursal_seleccionada" value="{{ $id_sucursal_seleccionada }}">
                                                        <button type="submit" class="btn btn-sm btn-primary px-3" title="Registrar Entrada (Hora Actual)"><i class="bi bi-stopwatch"></i> Entrada</button>
                                                    </form>
                                                @endif
                                                <form method="POST" action="{{ route('asistencia.registrarFalta') }}" class="d-inline-block ms-1">
                                                    @csrf
                                                    <input type="hidden" name="id_empleado" value="{{ $empleado->id_empleado }}">
                                                    <input type="hidden" name="id_sucursal_seleccionada" value="{{ $id_sucursal_seleccionada }}">
                                                    <button type="submit" class="btn btn-sm btn-warning fw-bold px-2" title="Marcar Falta">F</button>
                                                </form>
                                                <form method="POST" action="{{ route('asistencia.registrarBajaDia') }}" class="d-inline-block ms-1">
                                                    @csrf
                                                    <input type="hidden" name="id_empleado" value="{{ $empleado->id_empleado }}">
                                                    <input type="hidden" name="id_sucursal_seleccionada" value="{{ $id_sucursal_seleccionada }}">
                                                    <button type="submit" class="btn btn-sm btn-dark fw-bold px-2" title="Marcar Baja del Día">B</button>
                                                </form>
                                                <button type="button" class="btn btn-sm btn-info btn-registrar-incidencia ms-1 fw-bold px-2" data-bs-toggle="modal" data-bs-target="#modalRegistrarIncidencia" data-id_empleado="{{ $empleado->id_empleado }}" data-nombre_empleado="{{ $empleado->nombre_completo }}" title="Registrar Incidencia">I</button>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="alert alert-warning mt-3 text-center">No hay empleados activos asignados para esta selección.</div>
                    @endif
                @else
                    <div class="alert alert-info mt-4 text-center">
                        <i class="bi bi-info-circle me-2"></i>Por favor, seleccione una sucursal o elija "Todas las Sucursales" para gestionar la asistencia.
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Modal para Registrar Incidencia --}}
    <div class="modal fade" id="modalRegistrarIncidencia" tabindex="-1" aria-labelledby="modalIncidenciaLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-info text-white">
                    <h5 class="modal-title" id="modalIncidenciaLabel">Registrar Incidencia para Empleado</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="formRegistrarIncidencia" method="POST" action="{{ route('asistencia.registrarIncidencia') }}">
                    @csrf
                    <div class="modal-body">
                        <div class="p-2 mb-3 bg-light border rounded">
                            <span class="text-muted small">Empleado:</span><br>
                            <strong id="nombreEmpleadoIncidencia" class="fs-5"></strong>
                        </div>
                        <input type="hidden" name="id_empleado" id="id_empleado_incidencia_modal">
                        <input type="hidden" name="id_sucursal_seleccionada" value="{{ $id_sucursal_seleccionada ?? '' }}">
                        <div class="mb-3">
                            <label for="notas_incidencia_modal" class="form-label fw-bold">Notas de la Incidencia <span class="text-danger">*</span></label>
                            <textarea class="form-control" id="notas_incidencia_modal" name="notas_incidencia_modal" rows="4" required placeholder="Explique brevemente la razón de la incidencia..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-info px-4">Guardar Incidencia</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Iniciar tooltips de Bootstrap
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });

            // Manejo de edición de hora en línea
            document.querySelectorAll('.estado-display').forEach(function(displayElement) {
                displayElement.addEventListener('click', function() {
                    document.querySelectorAll('.inline-edit-form').forEach(f => f.style.display = 'none');
                    document.querySelectorAll('.estado-display').forEach(d => d.style.display = 'block');
                    
                    this.style.display = 'none';
                    var editForm = this.nextElementSibling;
                    if (editForm && editForm.classList.contains('inline-edit-form')) {
                        editForm.style.display = 'block';
                        var timeInput = editForm.querySelector('input[name="hora_llegada_manual"]');
                        if (timeInput) {
                            var existingTimeBadge = this.querySelector('.badge');
                            if (existingTimeBadge && existingTimeBadge.textContent.includes(':')) {
                                let timeText = existingTimeBadge.textContent.trim();
                                let parts = timeText.match(/(\d+):(\d+)\s*(AM|PM)/i);
                                if (parts) {
                                    let hours = parseInt(parts[1], 10);
                                    let minutes = parts[2];
                                    let ampm = parts[3].toUpperCase();
                                    if (ampm === 'PM' && hours < 12) hours += 12;
                                    if (ampm === 'AM' && hours === 12) hours = 0;
                                    timeInput.value = String(hours).padStart(2, '0') + ':' + minutes;
                                }
                            }
                            timeInput.focus();
                        }
                    }
                });
            });

            document.querySelectorAll('.btn-cancel-edit-hora').forEach(function(cancelButton) {
                cancelButton.addEventListener('click', function(e) {
                    e.stopPropagation(); // Evitar que el click se propague al div padre
                    var editForm = this.closest('.inline-edit-form');
                    if (editForm) {
                        editForm.style.display = 'none';
                        var displayElement = editForm.previousElementSibling;
                        if (displayElement && displayElement.classList.contains('estado-display')) {
                            displayElement.style.display = 'block';
                        }
                    }
                });
            });
            
            // Llenado de datos del modal de incidencia
            var modalRegistrarIncidencia = document.getElementById('modalRegistrarIncidencia');
            if (modalRegistrarIncidencia) {
                modalRegistrarIncidencia.addEventListener('show.bs.modal', function(event) {
                    var button = event.relatedTarget;
                    var idEmpleado = button.getAttribute('data-id_empleado');
                    var nombreEmpleado = button.getAttribute('data-nombre_empleado');
                    document.getElementById('nombreEmpleadoIncidencia').textContent = nombreEmpleado;
                    document.getElementById('id_empleado_incidencia_modal').value = idEmpleado;
                    document.getElementById('notas_incidencia_modal').value = '';
                });
            }
        });
        </script>
    @endpush
</x-app-layout>