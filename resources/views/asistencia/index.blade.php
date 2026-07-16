<x-app-layout>
    <div class="container-fluid py-4">
        <div class="card shadow-sm">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0 text-dark fw-bold">Vista de Asistencia por Periodo</h5>
                <div>
                    <a href="{{ route('asistencia.resumenIncidencias') }}" class="btn btn-info btn-sm me-2 text-white">
                        <i class="bi bi-file-earmark-text"></i> Ver Resumen de Incidencias
                    </a>
                    <a href="{{ route('asistencia.index') }}" class="btn btn-outline-secondary btn-sm">
                        <i class="bi bi-calendar-check"></i> Ir a Registro Diario
                    </a>
                </div>
            </div>
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
                        <div class="col-md-3">
                            <label for="id_sucursal_seleccionada" class="form-label mb-1 fw-bold">Sucursal:</label>
                            <select class="form-select form-select-sm" id="id_sucursal_seleccionada" name="id_sucursal_seleccionada">
                                <option value="">-- Seleccione Sucursal --</option>
                                <option value="todas" {{ request('id_sucursal_seleccionada') == 'todas' ? 'selected' : '' }} class="fw-bold text-primary">-- TODAS LAS SUCURSALES --</option>
                                @foreach ($sucursales as $sucursal)
                                    <option value="{{ $sucursal->id_sucursal }}" {{ ($id_sucursal_seleccionada ?? '') == $sucursal->id_sucursal ? 'selected' : '' }}>
                                        {{ $sucursal->nombre_sucursal }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label for="tipo_periodo" class="form-label mb-1 fw-bold">Ver por:</label>
                            <select class="form-select form-select-sm" name="tipo_periodo" id="tipo_periodo">
                                <option value="dia" {{ $tipoPeriodo == 'dia' ? 'selected' : '' }}>Día</option>
    <option value="semana" {{ $tipoPeriodo == 'semana' ? 'selected' : '' }}>Semana</option>
    <option value="quincena" {{ $tipoPeriodo == 'quincena' ? 'selected' : '' }}>Quincena</option>
    <option value="mes" {{ $tipoPeriodo == 'mes' ? 'selected' : '' }}>Mes</option>
</select>
                        </div>
                        <div class="col-md-3">
                            <label for="fecha_ref" class="form-label mb-1 fw-bold">Fecha de Referencia:</label>
                            <input type="date" name="fecha_ref" id="fecha_ref" class="form-control form-control-sm" value="{{ $fechaReferencia->toDateString() }}">
                        </div>
                        <div class="col-md-1 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary btn-sm w-100">Ver</button>
                        </div>
                        <div class="col-md-2 d-flex align-items-end">
                            @if(isset($id_sucursal_seleccionada) && $id_sucursal_seleccionada)
                                @php
                                    $params = ['id_sucursal_seleccionada' => $id_sucursal_seleccionada, 'tipo_periodo' => $tipoPeriodo];
                                    $prevDate = $fechaReferencia->copy();
                                    $nextDate = $fechaReferencia->copy();
                                    if($tipoPeriodo == 'semana') { $prevDate->subWeek(); $nextDate->addWeek(); }
                                    elseif($tipoPeriodo == 'quincena') { $prevDate->subDays(15); $nextDate->addDays(15); }
                                    elseif($tipoPeriodo == 'mes') { $prevDate->subMonthNoOverflow(); $nextDate->addMonthNoOverflow(); }
                                @endphp
                                <a href="{{ route('asistencia.vistaPeriodo', array_merge($params, ['fecha_ref' => $prevDate->toDateString()])) }}" class="btn btn-outline-secondary btn-sm me-1" title="Anterior"><i class="bi bi-chevron-left"></i></a>
                                <a href="{{ route('asistencia.vistaPeriodo', array_merge($params, ['fecha_ref' => $nextDate->toDateString()])) }}" class="btn btn-outline-secondary btn-sm" title="Siguiente"><i class="bi bi-chevron-right"></i></a>
                            @endif
                        </div>
                    </div>
                </form>
                <hr>

                @if(isset($id_sucursal_seleccionada) && $id_sucursal_seleccionada)
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="mb-0">
                            Asistencia para: <span class="text-primary fw-bold">{{ $sucursalSeleccionadaNombre ?? '' }}</span>
                        </h5>
                        <span class="badge bg-light text-dark border p-2">
                            <i class="bi bi-calendar3"></i> Periodo: <strong>{{ $nombrePeriodo ?? '' }}</strong>
                        </span>
                    </div>

                    @if(isset($empleadosDeSucursal) && $empleadosDeSucursal->isNotEmpty() && isset($fechasDelPeriodo) && $fechasDelPeriodo->isNotEmpty())
                        <div class="table-responsive" style="max-height: 70vh; border-radius: 8px;">
                            <table class="table table-bordered table-sm text-center align-middle mb-0">
                                <thead class="table-dark" style="position: sticky; top: 0; z-index: 3;">
                                    <tr>
                                        <th style="min-width: 220px; text-align: left; position: sticky; left: 0; z-index: 4; background-color: #343a40;">Empleado</th>
                                        @foreach ($fechasDelPeriodo as $fecha)
                                            <th class="{{ $fecha->isToday() ? 'bg-primary' : '' }}">
                                                {{ $fecha->translatedFormat('D') }}<br>{{ $fecha->format('d') }}
                                            </th>
                                        @endforeach
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($empleadosDeSucursal as $empleado)
                                        <tr>
                                            <td style="text-align: left; position: sticky; left: 0; background-color: #f8f9fa; z-index: 1; border-right: 2px solid #dee2e6;">
                                                <div class="fw-bold text-dark">{{ $empleado->nombre_completo }}</div>
                                                @if($id_sucursal_seleccionada === 'todas')
                                                    <span class="badge bg-secondary" style="font-size: 0.65em;">
                                                        <i class="bi bi-shop"></i> {{ $empleado->sucursal->nombre_sucursal ?? 'Sin Sucursal' }}
                                                    </span>
                                                @endif
                                            </td>
                                            @foreach ($fechasDelPeriodo as $fecha)
                                                @php
                                                    $fechaString = $fecha->toDateString();
                                                    $asistenciaDia = $asistenciaProcesada->get($empleado->id_empleado, collect())->get($fechaString);
                                                    $claseFondo = '';
                                                    $colorTexto = '';
                                                    if ($asistenciaDia) {
                                                        switch ($asistenciaDia->status_asistencia) {
                                                            case 'Retardo': $claseFondo = 'bg-warning bg-opacity-25'; break;
                                                            case 'Falta': $claseFondo = 'bg-danger bg-opacity-25'; break;
                                                            case 'Baja_Dia': $claseFondo = 'bg-dark bg-opacity-10 text-muted'; break;
                                                            case 'Incidencia': $claseFondo = 'bg-info bg-opacity-10'; break;
                                                        }
                                                    }
                                                @endphp
                                                <td class="celda-asistencia-editable {{ $claseFondo }}" 
                                                    style="cursor: pointer; height: 45px;"
                                                    data-id_empleado="{{ $empleado->id_empleado }}"
                                                    data-nombre_empleado="{{ $empleado->nombre_completo }}"
                                                    data-fecha="{{ $fechaString }}"
                                                    data-fecha_formateada="{{ $fecha->translatedFormat('d \d\e F \d\e Y') }}"
                                                    data-status_actual="{{ $asistenciaDia->status_asistencia ?? '' }}"
                                                    data-hora_actual="{{ $asistenciaDia && $asistenciaDia->hora_llegada ? \Carbon\Carbon::parse($asistenciaDia->hora_llegada)->format('H:i') : '' }}"
                                                    data-notas_actuales="{{ $asistenciaDia->notas_incidencia ?? '' }}">
                                                    
                                                    @if ($asistenciaDia)
                                                        @if ($asistenciaDia->status_asistencia == 'Presente')
                                                            <span class="text-success fw-bold" style="font-size: 0.85em;">{{ \Carbon\Carbon::parse($asistenciaDia->hora_llegada)->format('h:i') }}</span>
                                                        @elseif ($asistenciaDia->status_asistencia == 'Retardo')
                                                            <span class="text-warning-emphasis fw-bold" style="font-size: 0.85em;">{{ \Carbon\Carbon::parse($asistenciaDia->hora_llegada)->format('h:i') }}</span>
                                                        @elseif ($asistenciaDia->status_asistencia == 'Falta')
                                                            <span class="text-danger fw-bold">F</span>
                                                        @elseif ($asistenciaDia->status_asistencia == 'Baja_Dia')
                                                            <span class="text-dark fw-bold">B</span>
                                                        @elseif ($asistenciaDia->status_asistencia == 'Incidencia')
                                                            <span class="text-info fw-bold" data-bs-toggle="tooltip" title="{{ $asistenciaDia->notas_incidencia }}">I</span>
                                                        @endif
                                                    @else
                                                        <span class="text-muted" style="font-size: 0.8em;">-</span>
                                                    @endif
                                                </td>
                                            @endforeach
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="alert alert-warning mt-3 text-center">No hay datos de asistencia para mostrar en este rango de fechas.</div>
                    @endif
                @else
                    <div class="alert alert-info mt-4 text-center">Por favor, seleccione una sucursal y un periodo para visualizar la cuadrícula de asistencia.</div>
                @endif
            </div>
        </div>
    </div>

    {{-- Modal para Registrar/Editar Asistencia --}}
    <div class="modal fade" id="modalEditarAsistenciaDia" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">Actualizar Asistencia</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form id="formEditarAsistenciaDia" method="POST" action="{{ route('asistencia.guardarDia') }}">
                    @csrf
                    <div class="modal-body">
                        <input type="hidden" name="id_empleado_asistencia_dia" id="id_empleado_asistencia_dia_modal">
                        <input type="hidden" name="fecha_asistencia_dia" id="fecha_asistencia_dia_modal">
                        <input type="hidden" name="id_sucursal_seleccionada" value="{{ $id_sucursal_seleccionada ?? '' }}">
                        <input type="hidden" name="tipo_periodo" value="{{ $tipoPeriodo ?? 'semana' }}">
                        <input type="hidden" name="fecha_ref" value="{{ isset($fechaReferencia) ? $fechaReferencia->toDateString() : today()->toDateString() }}">

                        <div class="mb-2 p-2 bg-light border rounded">
                            <div class="small text-muted">Empleado:</div>
                            <div class="fw-bold" id="nombreEmpleadoAsistenciaDia"></div>
                            <div class="small text-muted mt-1">Fecha:</div>
                            <div class="fw-bold" id="fechaMostradaAsistenciaDia"></div>
                        </div>
                        
                        <div class="mt-3 mb-3">
                            <label for="status_asistencia_dia" class="form-label fw-bold">Estado del Día:</label>
                            <select class="form-select" id="status_asistencia_dia" name="status_asistencia_dia" required>
                                <option value="Presente">Presente (Registrar Hora)</option>
                                <option value="Falta">Falta Injustificada</option>
                                <option value="Baja_Dia">Baja del Día</option>
                                <option value="Incidencia">Incidencia / Permiso</option>
                            </select>
                        </div>
                        <div class="mb-3" id="campoHoraLlegadaDia">
                            <label for="hora_llegada_dia" class="form-label fw-bold">Hora de Entrada:</label>
                            <input type="time" class="form-control" id="hora_llegada_dia" name="hora_llegada_dia">
                        </div>
                        <div class="mb-3" id="campoNotasIncidenciaDia" style="display: none;">
                            <label for="notas_incidencia_dia" class="form-label fw-bold">Motivo de Incidencia:</label>
                            <textarea class="form-control" id="notas_incidencia_dia" name="notas_incidencia_dia" rows="3" placeholder="Ej. Retraso por transporte, cita médica, etc."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light border" data-bs-dismiss="modal">Cerrar</button>
                        <button type="submit" class="btn btn-primary px-4">Guardar Asistencia</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Inicializar tooltips
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            tooltipTriggerList.map(function (tooltipTriggerEl) { return new bootstrap.Tooltip(tooltipTriggerEl); });

            // Auto-envío de filtros
            const submitOnchange = (el) => { if(el) el.addEventListener('change', () => document.getElementById('filterForm').submit()); };
            submitOnchange(document.getElementById('id_sucursal_seleccionada'));
            submitOnchange(document.getElementById('tipo_periodo'));
            submitOnchange(document.getElementById('fecha_ref'));

            // Manejo del Modal
            const modalEl = document.getElementById('modalEditarAsistenciaDia');
            if (modalEl) {
                const selectStatus = document.getElementById('status_asistencia_dia');
                const campoHora = document.getElementById('campoHoraLlegadaDia');
                const inputHora = document.getElementById('hora_llegada_dia');
                const campoNotas = document.getElementById('campoNotasIncidenciaDia');
                const inputNotas = document.getElementById('notas_incidencia_dia');

                const toggleFields = () => {
                    const status = selectStatus.value;
                    campoHora.style.display = (status === 'Presente') ? 'block' : 'none';
                    inputHora.required = (status === 'Presente');
                    campoNotas.style.display = (status === 'Incidencia') ? 'block' : 'none';
                    inputNotas.required = (status === 'Incidencia');
                };

                selectStatus.addEventListener('change', toggleFields);

                document.querySelectorAll('.celda-asistencia-editable').forEach(function(celda) {
                    celda.addEventListener('click', function() {
                        document.getElementById('nombreEmpleadoAsistenciaDia').textContent = this.dataset.nombre_empleado;
                        document.getElementById('fechaMostradaAsistenciaDia').textContent = this.dataset.fecha_formateada;
                        document.getElementById('id_empleado_asistencia_dia_modal').value = this.dataset.id_empleado;
                        document.getElementById('fecha_asistencia_dia_modal').value = this.dataset.fecha;
                        
                        selectStatus.value = this.dataset.status_actual || 'Presente';
                        inputHora.value = this.dataset.hora_actual || '';
                        inputNotas.value = this.dataset.notas_actuales || '';
                        
                        toggleFields();
                        new bootstrap.Modal(modalEl).show();
                    });
                });
            }
        });
        </script>
    @endpush
</x-app-layout>