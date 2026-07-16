<x-app-layout>
    <div class="container-fluid py-4">
        <div class="card shadow-sm">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0 text-dark fw-bold">Vista de Asistencia por Periodo</h5>
                <div>
                    <a href="{{ route('asistencia.resumenIncidencias') }}" class="btn btn-info btn-sm me-2 text-white">
                        <i class="bi bi-file-earmark-text"></i> Ver Resumen de Incidencias
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
                <form id="filterForm" method="GET" action="{{ route('asistencia.index') }}" class="mb-3">
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
                                    elseif($tipoPeriodo == 'dia') { $prevDate->subDay(); $nextDate->addDay(); }
                                @endphp
                                <a href="{{ route('asistencia.index', array_merge($params, ['fecha_ref' => $prevDate->toDateString()])) }}" class="btn btn-outline-secondary btn-sm me-1" title="Anterior"><i class="bi bi-chevron-left"></i></a>
                                <a href="{{ route('asistencia.index', array_merge($params, ['fecha_ref' => $nextDate->toDateString()])) }}" class="btn btn-outline-secondary btn-sm" title="Siguiente"><i class="bi bi-chevron-right"></i></a>
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
                                                    if ($asistenciaDia) {
                                                        switch ($asistenciaDia->status_asistencia) {
                                                            case 'Retardo': $claseFondo = 'bg-warning bg-opacity-25'; break;
                                                            case 'Falta': $claseFondo = 'bg-danger bg-opacity-25'; break;
                                                        }
                                                    }
                                                @endphp
                                                <td class="celda-asistencia-editable {{ $claseFondo }}" 
                                                    style="cursor: pointer; height: 45px;"
                                                    data-id_empleado="{{ $empleado->id_empleado }}"
                                                    data-nombre_empleado="{{ $empleado->nombre_completo }}"
                                                    data-fecha="{{ $fechaString }}"
                                                    data-fecha_formateada="{{ $fecha->translatedFormat('d \d\e F \d\e Y') }}"
                                                    data-hora_actual="{{ $asistenciaDia && $asistenciaDia->hora_llegada ? \Carbon\Carbon::parse($asistenciaDia->hora_llegada)->format('H:i') : '' }}">
                                                    
                                                    @if ($asistenciaDia)
                                                        @if ($asistenciaDia->status_asistencia == 'Presente')
                                                            <span class="text-success fw-bold" style="font-size: 0.85em;">{{ \Carbon\Carbon::parse($asistenciaDia->hora_llegada)->format('h:i') }}</span>
                                                        @elseif ($asistenciaDia->status_asistencia == 'Retardo')
                                                            <span class="text-warning-emphasis fw-bold" style="font-size: 0.85em;">{{ \Carbon\Carbon::parse($asistenciaDia->hora_llegada)->format('h:i') }}</span>
                                                        @elseif ($asistenciaDia->status_asistencia == 'Falta')
                                                            <span class="text-danger fw-bold">F</span>
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

    {{-- NUEVO MODAL SIMPLIFICADO --}}
    <div class="modal fade" id="modalEditarAsistenciaDia" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">Registrar Entrada</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                {{-- APUNTAMOS A LA RUTA CORRECTA: registrarEntrada --}}
                <form id="formEditarAsistenciaDia" method="POST" action="{{ route('asistencia.registrarEntrada') }}">
                    @csrf
                    <div class="modal-body">
                        {{-- NOMBRES DE INPUTS CORREGIDOS PARA QUE EL CONTROLADOR LOS ENTIENDA --}}
                        <input type="hidden" name="id_empleado" id="id_empleado_modal">
                        <input type="hidden" name="fecha_registro" id="fecha_registro_modal">
                        <input type="hidden" name="id_sucursal_seleccionada" value="{{ $id_sucursal_seleccionada ?? '' }}">

                        <div class="mb-3 p-3 bg-light border rounded text-center">
                            <div class="small text-muted mb-1">Registro para:</div>
                            <h5 class="fw-bold text-primary mb-2" id="nombreEmpleadoAsistenciaDia"></h5>
                            <div class="badge bg-secondary mb-1" id="fechaMostradaAsistenciaDia"></div>
                        </div>
                        
                        <div class="mb-3 text-center px-4">
                            <label for="hora_llegada_manual" class="form-label fw-bold">Hora de llegada:</label>
                            <input type="time" class="form-control form-control-lg text-center fw-bold" id="hora_llegada_manual" name="hora_llegada_manual" required>
                            <div class="form-text mt-2">
                                <i class="bi bi-info-circle"></i> El sistema calculará automáticamente si es <b>Presente</b>, <b>Retardo</b> o <b>Falta</b> basándose en la tolerancia configurada en su horario.
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer justify-content-center bg-light">
                        <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-success px-4 fw-bold"><i class="bi bi-check-circle"></i> Guardar Registro</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Auto-envío de filtros
            const submitOnchange = (el) => { if(el) el.addEventListener('change', () => document.getElementById('filterForm').submit()); };
            submitOnchange(document.getElementById('id_sucursal_seleccionada'));
            submitOnchange(document.getElementById('tipo_periodo'));
            submitOnchange(document.getElementById('fecha_ref'));

            // Manejo del Modal Ultra Simplificado
            const modalEl = document.getElementById('modalEditarAsistenciaDia');
            if (modalEl) {
                const inputHora = document.getElementById('hora_llegada_manual');

                document.querySelectorAll('.celda-asistencia-editable').forEach(function(celda) {
                    celda.addEventListener('click', function() {
                        document.getElementById('nombreEmpleadoAsistenciaDia').textContent = this.dataset.nombre_empleado;
                        document.getElementById('fechaMostradaAsistenciaDia').textContent = this.dataset.fecha_formateada;
                        document.getElementById('id_empleado_modal').value = this.dataset.id_empleado;
                        document.getElementById('fecha_registro_modal').value = this.dataset.fecha;
                        
                        inputHora.value = this.dataset.hora_actual || '';
                        
                        new bootstrap.Modal(modalEl).show();
                    });
                });
            }
        });
        </script>
    @endpush
</x-app-layout>