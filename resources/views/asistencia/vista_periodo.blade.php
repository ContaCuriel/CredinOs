<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vista de Asistencia por Periodo</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        body { background-color: #f4f6f9; }
        .attendance-container { max-width: 95%; margin: 20px auto; }
        .table th, .table td { vertical-align: middle; font-size: 0.8rem; padding: 0.4rem; }
        .table th:first-child, .table td:first-child { min-width: 180px; text-align: left; position: sticky; left: 0; background-color: #f8f9fa; z-index:2;}
        .table thead th { position: sticky; top: 0; z-index: 1; background-color: #e9ecef;}
        .table-responsive { max-height: 75vh; }
        .celda-asistencia-editable { cursor: pointer; }
        .celda-asistencia-editable:hover { background-color: #e0e0e0; }
    </style>
</head>
<body>
    <div class="container-fluid attendance-container">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h2>Vista de Asistencia por Periodo</h2>
            <a href="{{ route('asistencia.index') }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-calendar-check"></i> Ir a Registro Diario</a>
        </div>

        <div class="card">
            <div class="card-body">
                @if (session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
                @endif
                @if (session('error'))
                     <div class="alert alert-danger alert-dismissible fade show" role="alert">{{ session('error') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
                @endif

                {{-- Formulario de Filtros --}}
                <form id="filterForm" method="GET" action="{{ route('asistencia.vistaPeriodo') }}" class="mb-3">
                    <div class="row align-items-end g-2">
                        <div class="col-md-3"><label for="id_sucursal_seleccionada" class="form-label mb-1">Sucursal:</label><select class="form-select form-select-sm" id="id_sucursal_seleccionada" name="id_sucursal_seleccionada">
                            <option value="">-- Seleccione Sucursal --</option>
                            @foreach ($sucursales as $sucursal)<option value="{{ $sucursal->id_sucursal }}" {{ ($id_sucursal_seleccionada ?? '') == $sucursal->id_sucursal ? 'selected' : '' }}>{{ $sucursal->nombre_sucursal }}</option>@endforeach</select></div>
                        <div class="col-md-3"><label for="tipo_periodo" class="form-label mb-1">Ver por:</label><select class="form-select form-select-sm" name="tipo_periodo" id="tipo_periodo"><option value="semana" {{ ($tipoPeriodo ?? 'semana') == 'semana' ? 'selected' : '' }}>Semana</option><option value="quincena" {{ ($tipoPeriodo ?? '') == 'quincena' ? 'selected' : '' }}>Quincena</option><option value="mes" {{ ($tipoPeriodo ?? '') == 'mes' ? 'selected' : '' }}>Mes</option></select></div>
                        <div class="col-md-3"><label for="fecha_ref" class="form-label mb-1">Fecha de Referencia:</label><input type="date" name="fecha_ref" id="fecha_ref" class="form-control form-control-sm" value="{{ $fechaReferencia->toDateString() }}"></div>
                        <div class="col-md-1 d-flex align-items-end"><button type="submit" class="btn btn-primary btn-sm w-100">Ver</button></div>
                        <div class="col-md-2 d-flex align-items-end">
                            @if(isset($id_sucursal_seleccionada))
                                @php
                                    $params = ['id_sucursal_seleccionada' => $id_sucursal_seleccionada, 'tipo_periodo' => $tipoPeriodo];
                                    $prevDate = $fechaReferencia->copy();
                                    $nextDate = $fechaReferencia->copy();
                                    if($tipoPeriodo == 'semana') { $prevDate->subWeek(); $nextDate->addWeek(); }
                                    elseif($tipoPeriodo == 'quincena') { $prevDate->subDays(15); $nextDate->addDays(15); }
                                    elseif($tipoPeriodo == 'mes') { $prevDate->subMonthNoOverflow(); $nextDate->addMonthNoOverflow(); }
                                @endphp
                                <a href="{{ route('asistencia.vistaPeriodo', array_merge($params, ['fecha_ref' => $prevDate->toDateString()])) }}" class="btn btn-outline-secondary btn-sm me-1"><i class="bi bi-chevron-left"></i></a>
                                <a href="{{ route('asistencia.vistaPeriodo', array_merge($params, ['fecha_ref' => $nextDate->toDateString()])) }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-chevron-right"></i></a>
                            @endif
                        </div>
                    </div>
                </form>
                <hr>

                @if(isset($id_sucursal_seleccionada) && $id_sucursal_seleccionada)
                    <h5 class="mt-3 mb-3">Asistencia para: <span class="text-primary">{{ $sucursalSeleccionadaNombre ?? '' }}</span> - Periodo: <span class="text-primary">{{ $nombrePeriodo ?? '' }}</span></h5>
                    @if(isset($empleadosDeSucursal) && $empleadosDeSucursal->isNotEmpty() && isset($fechasDelPeriodo) && $fechasDelPeriodo->isNotEmpty())
                        <div class="table-responsive">
                            <table class="table table-bordered table-sm text-center">
                                <thead class="table-light">
                                    <tr>
                                        <th style="min-width: 180px; text-align: left; position: sticky; left: 0; z-index: 3;">Empleado</th>
                                        @foreach ($fechasDelPeriodo as $fecha)
                                            <th>{{ $fecha->translatedFormat('D') }}<br>{{ $fecha->format('d') }}</th>
                                        @endforeach
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($empleadosDeSucursal as $empleado)
                                        <tr>
                                            <td style="text-align: left; position: sticky; left: 0; background-color: #f8f9fa; z-index: 1;">{{ $empleado->nombre_completo }}</td>
                                            @foreach ($fechasDelPeriodo as $fecha)
                                                @php
                                                    $fechaString = $fecha->toDateString();
                                                    $asistenciaDia = $asistenciaProcesada->get($empleado->id_empleado, collect())->get($fechaString);
                                                    
                                                    // Lógica para el resaltado de celdas
                                                    $claseFondo = '';
                                                    if ($asistenciaDia) {
                                                        switch ($asistenciaDia->status_asistencia) {
                                                            case 'Retardo': $claseFondo = 'table-warning'; break;
                                                            case 'Falta': $claseFondo = 'table-danger'; break;
                                                            case 'Baja_Dia': $claseFondo = 'table-dark text-white'; break;
                                                        }
                                                    }
                                                @endphp
                                                <td class="celda-asistencia-editable {{ $claseFondo }}"
                                                    data-id_empleado="{{ $empleado->id_empleado }}"
                                                    data-nombre_empleado="{{ $empleado->nombre_completo }}"
                                                    data-fecha="{{ $fechaString }}"
                                                    data-fecha_formateada="{{ $fecha->translatedFormat('d \d\e F \d\e Y') }}"
                                                    data-status_actual="{{ $asistenciaDia->status_asistencia ?? '' }}"
                                                    data-hora_actual="{{ $asistenciaDia && $asistenciaDia->hora_llegada ? \Carbon\Carbon::parse($asistenciaDia->hora_llegada)->format('H:i') : '' }}"
                                                    data-notas_actuales="{{ $asistenciaDia->notas_incidencia ?? '' }}">
                                                    
                                                    @if ($asistenciaDia)
                                                        @if ($asistenciaDia->status_asistencia == 'Presente')
                                                            <span class="badge bg-success">{{ \Carbon\Carbon::parse($asistenciaDia->hora_llegada)->format('h:iA') }}</span>
                                                        @elseif ($asistenciaDia->status_asistencia == 'Retardo')
                                                            <span class="badge bg-warning text-dark">{{ \Carbon\Carbon::parse($asistenciaDia->hora_llegada)->format('h:iA') }}</span>
                                                        @elseif ($asistenciaDia->status_asistencia == 'Falta')
                                                            <span class="badge bg-danger">F</span>
                                                        @elseif ($asistenciaDia->status_asistencia == 'Baja_Dia')
                                                            <span class="badge bg-dark">B</span>
                                                        @elseif ($asistenciaDia->status_asistencia == 'Incidencia')
                                                            <span class="badge bg-info" data-bs-toggle="tooltip" title="{{ $asistenciaDia->notas_incidencia }}">I</span>
                                                        @else
                                                            <span class="badge bg-light text-dark">?</span>
                                                        @endif
                                                    @else
                                                        <span class="text-muted">-</span>
                                                    @endif
                                                </td>
                                            @endforeach
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="alert alert-warning mt-3 text-center">No hay datos de asistencia para mostrar.</div>
                    @endif
                @else
                    <div class="alert alert-info mt-4 text-center">Por favor, seleccione una sucursal.</div>
                @endif
            </div>
        </div>
    </div>

    {{-- Modal para Registrar/Editar Asistencia --}}
    <div class="modal fade" id="modalEditarAsistenciaDia" tabindex="-1" aria-labelledby="modalEditarAsistenciaLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalEditarAsistenciaLabel">Registrar/Editar Asistencia</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="formEditarAsistenciaDia" method="POST" action="{{ route('asistencia.guardarDia') }}">
                    @csrf
                    <div class="modal-body">
                        <input type="hidden" name="id_empleado_asistencia_dia" id="id_empleado_asistencia_dia_modal">
                        <input type="hidden" name="fecha_asistencia_dia" id="fecha_asistencia_dia_modal">
                        {{-- Campos ocultos para mantener el estado de la vista al redireccionar --}}
                        <input type="hidden" name="id_sucursal_seleccionada" value="{{ $id_sucursal_seleccionada ?? '' }}">
                        <input type="hidden" name="tipo_periodo" value="{{ $tipoPeriodo ?? 'semana' }}">
                        <input type="hidden" name="fecha_ref" value="{{ isset($fechaReferencia) ? $fechaReferencia->toDateString() : today()->toDateString() }}">

                        <p>Empleado: <strong id="nombreEmpleadoAsistenciaDia"></strong></p>
                        <p>Fecha: <strong id="fechaMostradaAsistenciaDia"></strong></p>
                        
                        <div class="mb-3">
                            <label for="status_asistencia_dia" class="form-label">Estado <span class="text-danger">*</span></label>
                            <select class="form-select form-select-sm" id="status_asistencia_dia" name="status_asistencia_dia" required>
                                <option value="Presente">Presente (Llegada)</option>
                                <option value="Falta">Falta</option>
                                <option value="Baja_Dia">Baja del Día</option>
                                <option value="Incidencia">Incidencia</option>
                            </select>
                        </div>

                        <div class="mb-3" id="campoHoraLlegadaDia" style="display: none;">
                            <label for="hora_llegada_dia" class="form-label">Hora de Llegada</label>
                            <input type="time" class="form-control form-control-sm" id="hora_llegada_dia" name="hora_llegada_dia">
                        </div>

                        <div class="mb-3" id="campoNotasIncidenciaDia" style="display: none;">
                            <label for="notas_incidencia_dia" class="form-label">Notas de la Incidencia</label>
                            <textarea class="form-control form-control-sm" id="notas_incidencia_dia" name="notas_incidencia_dia" rows="3"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Guardar Cambios</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    
    <script>
    document.addEventListener('DOMContentLoaded', function () {
        // Inicializar Tooltips de Bootstrap
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });

        // Si se cambia la sucursal, tipo de periodo o fecha_ref, enviar el formulario principal de filtros
        const filterForm = document.getElementById('filterForm');
        const sucursalSelect = document.getElementById('id_sucursal_seleccionada');
        const tipoPeriodoSelect = document.getElementById('tipo_periodo');
        const fechaRefInput = document.getElementById('fecha_ref');

        if (sucursalSelect) {
            sucursalSelect.addEventListener('change', function() { if(this.value) filterForm.submit(); });
        }
        if (tipoPeriodoSelect) {
            tipoPeriodoSelect.addEventListener('change', function() { if(sucursalSelect.value) filterForm.submit(); });
        }
        if (fechaRefInput) {
            fechaRefInput.addEventListener('change', function() { if(sucursalSelect.value) filterForm.submit(); });
        }

        // Script para el modal de Editar/Registrar Asistencia Día Específico
        var modalEditarAsistencia = document.getElementById('modalEditarAsistenciaDia');
        if (modalEditarAsistencia) {
            var formEditarAsistencia = document.getElementById('formEditarAsistenciaDia');
            var nombreEmpleadoSpanModal = document.getElementById('nombreEmpleadoAsistenciaDia');
            var fechaMostradaSpanModal = document.getElementById('fechaMostradaAsistenciaDia');
            var inputIdEmpleadoModal = document.getElementById('id_empleado_asistencia_dia_modal');
            var inputFechaModal = document.getElementById('fecha_asistencia_dia_modal');
            
            var selectStatusModal = document.getElementById('status_asistencia_dia');
            var campoHoraLlegadaModal = document.getElementById('campoHoraLlegadaDia');
            var inputHoraLlegadaModal = document.getElementById('hora_llegada_dia');
            var campoNotasIncidenciaModal = document.getElementById('campoNotasIncidenciaDia');
            var inputNotasIncidenciaModal = document.getElementById('notas_incidencia_dia');

            function toggleModalFields() {
                if (!selectStatusModal || !campoHoraLlegadaModal || !inputHoraLlegadaModal || !campoNotasIncidenciaModal || !inputNotasIncidenciaModal) return;

                let statusSelected = selectStatusModal.value;
                campoHoraLlegadaModal.style.display = 'none';
                inputHoraLlegadaModal.removeAttribute('required');
                inputHoraLlegadaModal.value = '';
                
                campoNotasIncidenciaModal.style.display = 'none';
                inputNotasIncidenciaModal.removeAttribute('required');
                inputNotasIncidenciaModal.value = '';

                if (statusSelected === 'Presente') {
                    campoHoraLlegadaModal.style.display = 'block';
                    inputHoraLlegadaModal.setAttribute('required', 'required');
                } else if (statusSelected === 'Incidencia') {
                    campoNotasIncidenciaModal.style.display = 'block';
                    inputNotasIncidenciaModal.setAttribute('required', 'required');
                }
            }

            if(selectStatusModal) {
                selectStatusModal.addEventListener('change', toggleModalFields);
            }

            document.querySelectorAll('.celda-asistencia-editable').forEach(function(celda) {
                celda.addEventListener('click', function() {
                    var idEmpleado = this.dataset.id_empleado;
                    var nombreEmpleado = this.dataset.nombre_empleado;
                    var fecha = this.dataset.fecha; 
                    var fechaFormateada = this.dataset.fecha_formateada;
                    var statusActual = this.dataset.status_actual || 'Presente'; 
                    var horaActual = this.dataset.hora_actual || '';
                    var notasActuales = this.dataset.notas_actuales || '';

                    if (nombreEmpleadoSpanModal) nombreEmpleadoSpanModal.textContent = nombreEmpleado;
                    if (fechaMostradaSpanModal) fechaMostradaSpanModal.textContent = fechaFormateada;
                    if (inputIdEmpleadoModal) inputIdEmpleadoModal.value = idEmpleado;
                    if (inputFechaModal) inputFechaModal.value = fecha;
                    
                    if (selectStatusModal) selectStatusModal.value = statusActual;
                    if (inputHoraLlegadaModal) inputHoraLlegadaModal.value = horaActual;
                    if (inputNotasIncidenciaModal) inputNotasIncidenciaModal.value = notasActuales;

                    toggleModalFields(); 

                    if (formEditarAsistencia) {
                        // Esta ruta la crearemos en el siguiente paso
                        formEditarAsistencia.action = "{{ route('asistencia.guardarDia') }}"; // Ruta placeholder, la crearemos
                    }
                    var bootstrapModal = new bootstrap.Modal(modalEditarAsistencia);
                    bootstrapModal.show();
                });
            });
        } else {
            console.error('Modal con ID "modalEditarAsistenciaDia" NO encontrado.');
        }
    });
    </script>
</body>
</html>