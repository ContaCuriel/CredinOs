<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro de Asistencia - Credinos System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        body { background-color: #f4f6f9; }
        .attendance-container { max-width: 1000px; margin-top: 30px;}
        .table th, .table td { vertical-align: middle; }
        .estado-display { cursor: pointer; display: block; padding: 0.5rem; }
        .estado-display:hover { background-color: #e9ecef; }
        .inline-edit-form input[type="time"] {
            padding: 0.25rem 0.5rem;
            font-size: 0.875rem;
            width: auto;
            display: inline-block;
        }
        .inline-edit-form .btn-sm {
            padding: 0.25rem 0.5rem;
            font-size: 0.75rem;
            line-height: 1.5;
        }
    </style>
</head>
<body>
    <div class="container attendance-container">
        <div class="text-center mb-4">
            <div class="text-end mb-3">
                <a href="{{ route('asistencia.vistaPeriodo') }}" class="btn btn-outline-primary btn-sm">
                    <i class="bi bi-calendar3-week"></i> Ver Asistencia por Periodo
                </a>
            </div>
            <h2>Registro de Asistencia</h2>
        </div>
        @auth
        <div class="text-end mb-2">
            <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-arrow-left-circle"></i> Volver al Sistema Principal
            </a>
        </div>
        @endauth

        <div class="card">
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

                <form id="selectSucursalForm" method="GET" action="{{ route('asistencia.index') }}" class="mb-3">
                    <div class="row justify-content-center">
                        <div class="col-md-6">
                            <label for="id_sucursal_seleccionada" class="form-label">Seleccione la Sucursal:</label>
                            <div class="input-group">
                                <select class="form-select" id="id_sucursal_seleccionada" name="id_sucursal_seleccionada" onchange="this.form.submit()">
                                    <option value="">-- Seleccione una Sucursal --</option>
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
                    <h4 class="mt-4 mb-3">Registrar Asistencia para: <span class="text-primary">{{ $sucursalSeleccionadaNombre ?? '' }}</span> - Fecha: <span id="currentDateDisplay">{{ \Carbon\Carbon::today()->translatedFormat('d \d\e F \d\e Y') }}</span></h4>
                    
                    @if(isset($empleadosDeSucursal) && $empleadosDeSucursal->isNotEmpty())
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped">
                                <thead class="table-light">
                                    <tr>
                                        <th>Empleado</th>
                                        <th class="text-center" style="width: 25%;">Estado Hoy / Hora Llegada</th>
                                        <th class="text-center" style="width: 35%;">Acciones Rápidas</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($empleadosDeSucursal as $empleado)
                                        <tr>
                                            <td>{{ $empleado->nombre_completo }}</td>
                                            
                                            @php
                                                $asistenciaDelDia = $asistenciasHoy->get($empleado->id_empleado);
                                                $claseFondo = '';
                                                if ($asistenciaDelDia) {
                                                    switch ($asistenciaDelDia->status_asistencia) {
                                                        case 'Retardo':
                                                            $claseFondo = 'table-warning'; // Fondo amarillo para retardos
                                                            break;
                                                        case 'Falta':
                                                            $claseFondo = 'table-danger';
                                                            break;
                                                    }
                                                }
                                            @endphp
                                            <td class="text-center {{ $claseFondo }}">
                                                <div class="estado-display" data-empleado-id="{{ $empleado->id_empleado }}">
                                                    @if ($asistenciaDelDia)
                                                        @if ($asistenciaDelDia->status_asistencia == 'Presente' && $asistenciaDelDia->hora_llegada)
                                                            <span class="badge bg-success fs-6 me-1">{{ \Carbon\Carbon::parse($asistenciaDelDia->hora_llegada)->format('h:i A') }}</span>
                                                            <i class="bi bi-pencil-fill text-primary edit-hora-icon" style="cursor:pointer;" title="Editar Hora"></i>
                                                        @elseif ($asistenciaDelDia->status_asistencia == 'Retardo' && $asistenciaDelDia->hora_llegada)
                                                            <span class="badge bg-warning text-dark fs-6 me-1">{{ \Carbon\Carbon::parse($asistenciaDelDia->hora_llegada)->format('h:i A') }}</span>
                                                            <i class="bi bi-pencil-fill text-primary edit-hora-icon" style="cursor:pointer;" title="Editar Hora"></i>
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
                                                        <i class="bi bi-pencil-fill text-primary edit-hora-icon" style="cursor:pointer;" title="Ingresar Hora"></i>
                                                    @endif
                                                </div>
                                                <div class="inline-edit-form" style="display: none;">
                                                    <form method="POST" action="{{ route('asistencia.registrarEntrada') }}" class="d-inline-flex align-items-center">
                                                        @csrf
                                                        <input type="hidden" name="id_empleado" value="{{ $empleado->id_empleado }}">
                                                        <input type="hidden" name="id_sucursal_seleccionada" value="{{ $id_sucursal_seleccionada }}">
                                                        <input type="time" name="hora_llegada_manual" class="form-control form-control-sm me-1" style="width: 100px;" required>
                                                        <button type="submit" class="btn btn-sm btn-success me-1" title="Guardar Hora"><i class="bi bi-check-lg"></i></button>
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
                                                        <button type="submit" class="btn btn-sm btn-primary" title="Registrar Entrada (Hora Actual)"><i class="bi bi-alarm-fill"></i></button>
                                                    </form>
                                                @endif
                                                <form method="POST" action="{{ route('asistencia.registrarFalta') }}" class="d-inline-block ms-1">
                                                    @csrf
                                                    <input type="hidden" name="id_empleado" value="{{ $empleado->id_empleado }}">
                                                    <input type="hidden" name="id_sucursal_seleccionada" value="{{ $id_sucursal_seleccionada }}">
                                                    <button type="submit" class="btn btn-sm btn-warning" title="Marcar Falta">F</button>
                                                </form>
                                                <form method="POST" action="{{ route('asistencia.registrarBajaDia') }}" class="d-inline-block ms-1">
                                                    @csrf
                                                    <input type="hidden" name="id_empleado" value="{{ $empleado->id_empleado }}">
                                                    <input type="hidden" name="id_sucursal_seleccionada" value="{{ $id_sucursal_seleccionada }}">
                                                    <button type="submit" class="btn btn-sm btn-dark" title="Marcar Baja del Día">B</button>
                                                </form>
                                                <button type="button" class="btn btn-sm btn-info btn-registrar-incidencia ms-1" data-bs-toggle="modal" data-bs-target="#modalRegistrarIncidencia" data-id_empleado="{{ $empleado->id_empleado }}" data-nombre_empleado="{{ $empleado->nombre_completo }}" title="Registrar Incidencia">I</button>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="alert alert-warning mt-3 text-center">No hay empleados activos asignados a esta sucursal.</div>
                    @endif
                @else
                    <div class="alert alert-info mt-4 text-center">Por favor, seleccione una sucursal para registrar asistencia.</div>
                @endif
            </div>
        </div>
    </div>

    {{-- Modal para Registrar Incidencia --}}
    <div class="modal fade" id="modalRegistrarIncidencia" tabindex="-1" aria-labelledby="modalIncidenciaLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalIncidenciaLabel">Registrar Incidencia para Empleado</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="formRegistrarIncidencia" method="POST" action="{{ route('asistencia.registrarIncidencia') }}">
                    @csrf
                    <div class="modal-body">
                        <p>Empleado: <strong id="nombreEmpleadoIncidencia"></strong></p>
                        <input type="hidden" name="id_empleado" id="id_empleado_incidencia_modal">
                        <input type="hidden" name="id_sucursal_seleccionada" value="{{ $id_sucursal_seleccionada ?? '' }}">
                        <div class="mb-3">
                            <label for="notas_incidencia_modal" class="form-label">Notas de la Incidencia <span class="text-danger">*</span></label>
                            <textarea class="form-control" id="notas_incidencia_modal" name="notas_incidencia_modal" rows="4" required></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-info">Guardar Incidencia</button>
                    </div>
                </form>
            </div>
        </div>
    </div>


    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    
    <script>
    document.addEventListener('DOMContentLoaded', function () {
        console.log('Asistencia JS: DOMContentLoaded');

        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });

        // Manejar edición en línea para la hora de llegada
        document.querySelectorAll('.estado-display').forEach(function(displayElement) {
            displayElement.addEventListener('click', function() {
                // Ocultar todos los otros formularios de edición en línea abiertos
                document.querySelectorAll('.inline-edit-form').forEach(f => f.style.display = 'none');
                document.querySelectorAll('.estado-display').forEach(d => d.style.display = 'block');

                // Mostrar este formulario de edición
                this.style.display = 'none';
                var editForm = this.nextElementSibling; // El .inline-edit-form debe estar justo después
                if (editForm && editForm.classList.contains('inline-edit-form')) {
                    editForm.style.display = 'block';
                    var timeInput = editForm.querySelector('input[name="hora_llegada_manual"]');
                    if (timeInput) {
                        // Si había una hora registrada, pre-llenarla. Si no, dejarlo vacío.
                        var existingTimeBadge = this.querySelector('.badge.bg-success');
                        if (existingTimeBadge) {
                            // Convertir '08:15 AM' a '08:15'
                            let timeText = existingTimeBadge.textContent; // "08:15 AM"
                            let parts = timeText.match(/(\d+):(\d+)\s*(AM|PM)/i);
                            if (parts) {
                                let hours = parseInt(parts[1], 10);
                                let minutes = parts[2];
                                let ampm = parts[3].toUpperCase();
                                if (ampm === 'PM' && hours < 12) hours += 12;
                                if (ampm === 'AM' && hours === 12) hours = 0; // medianoche
                                timeInput.value = String(hours).padStart(2, '0') + ':' + minutes;
                            } else {
                                timeInput.value = ''; // Si no se puede parsear, dejar vacío
                            }
                        } else {
                            timeInput.value = ''; // Si era '--:--', dejar vacío
                        }
                        timeInput.focus();
                    }
                }
            });
        });

        // Manejar cancelación de edición en línea
        document.querySelectorAll('.btn-cancel-edit-hora').forEach(function(cancelButton) {
            cancelButton.addEventListener('click', function() {
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
        
        // Manejar Enter para guardar en edición en línea
        document.querySelectorAll('input[name="hora_llegada_manual"]').forEach(function(timeInput) {
            timeInput.addEventListener('keypress', function(event) {
                if (event.key === 'Enter') {
                    event.preventDefault(); // Prevenir envío normal si está en un form más grande
                    this.closest('form').submit();
                }
            });
        });

        // Script para el modal de Registrar Incidencia (existente)
        var modalRegistrarIncidencia = document.getElementById('modalRegistrarIncidencia');
        if (modalRegistrarIncidencia) {
            // ... (resto de tu script para modalRegistrarIncidencia como lo tenías) ...
            var formRegistrarIncidencia = document.getElementById('formRegistrarIncidencia');
            var nombreEmpleadoIncidenciaSpan = document.getElementById('nombreEmpleadoIncidencia');
            var idEmpleadoIncidenciaModalInput = document.getElementById('id_empleado_incidencia_modal');
            var notasTextarea = document.getElementById('notas_incidencia_modal');

            modalRegistrarIncidencia.addEventListener('show.bs.modal', function(event) {
                var button = event.relatedTarget; 
                if (!button || typeof button.getAttribute !== 'function') {
                    console.error('ERROR (modalRegistrarIncidencia): event.relatedTarget no es un botón válido.');
                    if(formRegistrarIncidencia) formRegistrarIncidencia.action = "#ERROR_NO_BUTTON_INCIDENCIA";
                    return;
                }
                var idEmpleado = button.getAttribute('data-id_empleado');
                var nombreEmpleado = button.getAttribute('data-nombre_empleado');
                
                if (nombreEmpleadoIncidenciaSpan) nombreEmpleadoIncidenciaSpan.textContent = nombreEmpleado;
                if (idEmpleadoIncidenciaModalInput) idEmpleadoIncidenciaModalInput.value = idEmpleado;
                
                if (formRegistrarIncidencia) {
                    formRegistrarIncidencia.action = "{{ route('asistencia.registrarIncidencia') }}"; 
                }
                if(notasTextarea) notasTextarea.value = '';
            });
        } else {
            console.error('Asistencia JS: Modal con ID "modalRegistrarIncidencia" NO encontrado.');
        }
    });
    </script>
</body>
</html>