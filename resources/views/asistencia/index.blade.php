<x-app-layout>
    <div class="container-fluid py-4">
        <div class="card shadow-sm">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0 text-dark fw-bold">Registro de Asistencia</h5>
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
                        <div class="table-responsive shadow-sm" style="max-height: 70vh; border-radius: 8px;">
                            <table class="table table-bordered table-sm text-center align-middle mb-0 bg-white">
                                <thead class="table-dark" style="position: sticky; top: 0; z-index: 3;">
                                    <tr>
                                        <th style="min-width: 220px; text-align: left; position: sticky; left: 0; z-index: 4; background-color: #343a40;">Empleado</th>
                                        @foreach ($fechasDelPeriodo as $fecha)
                                            <th class="{{ $fecha->isToday() ? 'bg-primary' : '' }}" style="min-width: 110px;">
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
                                                    
                                                    // Detectar estado actual para el menú
                                                    $estadoActual = $asistenciaDia ? ($asistenciaDia->status_asistencia == 'Retardo' ? 'Presente' : $asistenciaDia->status_asistencia) : 'Presente';
                                                @endphp
                                                <td class="p-1 celda-interactiva" style="height: 60px;">
                                                    
                                                    {{-- MODO VISTA (Click para editar) --}}
                                                    <div class="display-mode w-100 h-100 d-flex align-items-center justify-content-center" style="cursor: pointer;" onclick="activarEdicion(this)">
                                                        @if ($asistenciaDia)
                                                            @if (in_array($asistenciaDia->status_asistencia, ['Presente', 'Retardo']))
                                                                <span class="badge bg-{{$asistenciaDia->status_asistencia == 'Retardo' ? 'warning text-dark' : 'success'}} w-100 py-2 fs-6 shadow-sm">
                                                                    {{ \Carbon\Carbon::parse($asistenciaDia->hora_llegada)->format('h:i A') }}
                                                                </span>
                                                            @elseif ($asistenciaDia->status_asistencia == 'Falta')
                                                                <span class="badge bg-danger w-100 py-2 fs-6 shadow-sm">FALTA</span>
                                                            @elseif ($asistenciaDia->status_asistencia == 'Baja_Dia')
                                                                <span class="badge bg-dark w-100 py-2 shadow-sm">BAJA DÍA</span>
                                                            @elseif ($asistenciaDia->status_asistencia == 'Incidencia')
                                                                <span class="badge bg-info text-dark w-100 py-2 shadow-sm" title="{{ $asistenciaDia->notas_incidencia }}" data-bs-toggle="tooltip">INCIDENCIA</span>
                                                            @endif
                                                        @else
                                                            <span class="text-muted bg-light border rounded px-3 py-1" style="font-size: 0.8em;"><i class="bi bi-dash"></i></span>
                                                        @endif
                                                    </div>

                                                    {{-- MODO EDICIÓN (Mini formulario oculto) --}}
                                                    <div class="edit-mode d-none">
                                                        <form method="POST" action="{{ route('asistencia.registrarEntrada') }}" class="d-flex flex-column">
                                                            @csrf
                                                            <input type="hidden" name="id_empleado" value="{{ $empleado->id_empleado }}">
                                                            <input type="hidden" name="fecha_registro" value="{{ $fechaString }}">
                                                            <input type="hidden" name="id_sucursal_seleccionada" value="{{ $id_sucursal_seleccionada }}">

                                                            <select name="status_asistencia" class="form-select form-select-sm mb-1 text-center fw-bold bg-light" style="font-size: 0.75rem; padding: 0.1rem;" onchange="manejarCambioEstado(this)">
                                                                <option value="Presente" {{ $estadoActual == 'Presente' ? 'selected' : '' }}>Hora (Asistencia)</option>
                                                                <option value="Falta" {{ $estadoActual == 'Falta' ? 'selected' : '' }}>Falta Manual</option>
                                                                <option value="Baja_Dia" {{ $estadoActual == 'Baja_Dia' ? 'selected' : '' }}>Baja del Día</option>
                                                                <option value="Incidencia" {{ $estadoActual == 'Incidencia' ? 'selected' : '' }}>Incidencia / Permiso</option>
                                                            </select>

                                                            <input type="time" name="hora_llegada_manual" class="form-control form-control-sm mb-1 text-center input-hora" style="font-size: 0.8rem; padding: 0.1rem; display: {{ $estadoActual == 'Presente' ? 'block' : 'none' }};" value="{{ $asistenciaDia && $asistenciaDia->hora_llegada ? \Carbon\Carbon::parse($asistenciaDia->hora_llegada)->format('H:i') : '' }}">

                                                            <input type="text" name="notas_incidencia" class="form-control form-control-sm mb-1 input-notas" style="font-size: 0.75rem; padding: 0.2rem; display: {{ $estadoActual == 'Incidencia' ? 'block' : 'none' }};" placeholder="Motivo breve..." value="{{ $asistenciaDia->notas_incidencia ?? '' }}">

                                                            <div class="d-flex gap-1">
                                                                <button type="submit" class="btn btn-success btn-sm flex-fill p-0 shadow-sm" title="Guardar"><i class="bi bi-check2"></i></button>
                                                                <button type="button" class="btn btn-secondary btn-sm flex-fill p-0 shadow-sm" title="Cancelar" onclick="cancelarEdicion(this)"><i class="bi bi-x"></i></button>
                                                            </div>
                                                        </form>
                                                    </div>

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

    @push('scripts')
        <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Auto-envío de filtros principales
            const submitOnchange = (el) => { if(el) el.addEventListener('change', () => document.getElementById('filterForm').submit()); };
            submitOnchange(document.getElementById('id_sucursal_seleccionada'));
            submitOnchange(document.getElementById('tipo_periodo'));
            submitOnchange(document.getElementById('fecha_ref'));

            // Tooltips (para ver notas de incidencias al pasar el mouse)
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            tooltipTriggerList.map(function (tooltipTriggerEl) { return new bootstrap.Tooltip(tooltipTriggerEl); });
        });

        // Funciones para el "Inline Editing" (Edición en celda sin Modales)
        function activarEdicion(divVista) {
            // Cerramos cualquier otra celda que esté abierta
            document.querySelectorAll('.edit-mode').forEach(el => el.classList.add('d-none'));
            document.querySelectorAll('.display-mode').forEach(el => el.classList.remove('d-none'));

            // Ocultamos la vista actual y mostramos el mini-formulario
            let celda = divVista.closest('td');
            celda.querySelector('.display-mode').classList.add('d-none');
            celda.querySelector('.edit-mode').classList.remove('d-none');
        }

        function cancelarEdicion(btnCancelar) {
            let celda = btnCancelar.closest('td');
            celda.querySelector('.edit-mode').classList.add('d-none');
            celda.querySelector('.display-mode').classList.remove('d-none');
        }

        function manejarCambioEstado(selectElement) {
            let form = selectElement.closest('form');
            let inputHora = form.querySelector('.input-hora');
            let inputNotas = form.querySelector('.input-notas');

            if (selectElement.value === 'Presente') {
                inputHora.style.display = 'block';
                inputHora.required = true;
                inputNotas.style.display = 'none';
                inputNotas.required = false;
            } else if (selectElement.value === 'Incidencia') {
                inputHora.style.display = 'none';
                inputHora.required = false;
                inputNotas.style.display = 'block';
                inputNotas.required = true;
            } else {
                // Si es Falta o Baja del Día
                inputHora.style.display = 'none';
                inputHora.required = false;
                inputNotas.style.display = 'none';
                inputNotas.required = false;
            }
        }
        </script>
    @endpush
</x-app-layout>