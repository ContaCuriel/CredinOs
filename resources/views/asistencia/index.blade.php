<x-app-layout>
    <div class="container-fluid py-4">
        <div class="card shadow-sm">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0 text-dark fw-bold">Control de Asistencias</h5>
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
                    <div class="row align-items-end g-2 justify-content-center">
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
                        <div class="col-md-2 d-flex align-items-end gap-1">
                            <button type="submit" class="btn btn-primary btn-sm flex-fill">Ver</button>
                            @if(isset($id_sucursal_seleccionada) && $id_sucursal_seleccionada)
                                @php
                                    $params = ['id_sucursal_seleccionada' => $id_sucursal_seleccionada, 'tipo_periodo' => $tipoPeriodo];
                                    $prevDate = $fechaReferencia->copy(); $nextDate = $fechaReferencia->copy();
                                    if($tipoPeriodo == 'semana') { $prevDate->subWeek(); $nextDate->addWeek(); }
                                    elseif($tipoPeriodo == 'quincena') { $prevDate->subDays(15); $nextDate->addDays(15); }
                                    elseif($tipoPeriodo == 'mes') { $prevDate->subMonthNoOverflow(); $nextDate->addMonthNoOverflow(); }
                                    elseif($tipoPeriodo == 'dia') { $prevDate->subDay(); $nextDate->addDay(); }
                                @endphp
                                <a href="{{ route('asistencia.index', array_merge($params, ['fecha_ref' => $prevDate->toDateString()])) }}" class="btn btn-outline-secondary btn-sm" title="Anterior"><i class="bi bi-chevron-left"></i></a>
                                <a href="{{ route('asistencia.index', array_merge($params, ['fecha_ref' => $nextDate->toDateString()])) }}" class="btn btn-outline-secondary btn-sm" title="Siguiente"><i class="bi bi-chevron-right"></i></a>
                            @endif
                        </div>
                    </div>
                </form>
                <hr>

                @if(isset($id_sucursal_seleccionada) && $id_sucursal_seleccionada)
                    <div class="text-center mb-3">
                        <h5 class="mb-1">Sucursal: <span class="text-primary fw-bold">{{ $sucursalSeleccionadaNombre ?? '' }}</span></h5>
                        <span class="badge bg-light text-dark border p-2">
                            <i class="bi bi-calendar3"></i> Rango: <strong>{{ $fechaReferencia->translatedFormat('d \d\e F') }}</strong>
                        </span>
                    </div>

                    @if(isset($empleadosDeSucursal) && $empleadosDeSucursal->isNotEmpty() && isset($fechasDelPeriodo) && $fechasDelPeriodo->isNotEmpty())
                        
                        <div class="table-responsive shadow-sm mx-auto" style="border-radius: 8px; max-width: {{ $tipoPeriodo == 'dia' ? '900px' : '100%' }};">
                            <table class="table table-bordered table-sm text-center align-middle mb-0 bg-white">
                                <thead class="table-dark" style="position: sticky; top: 0; z-index: 3;">
                                    <tr>
                                        <th style="min-width: 200px; text-align: left; position: sticky; left: 0; z-index: 4; background-color: #343a40;">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <span>Empleado</span>
                                                <button id="btn-mostrar-ocultos" class="btn btn-outline-info btn-sm py-0 d-none" onclick="mostrarOcultos()" title="Restaurar empleados ocultos" style="font-size: 0.7rem;">
                                                    <i class="bi bi-eye"></i> Mostrar ocultos
                                                </button>
                                            </div>
                                        </th>
                                        @foreach ($fechasDelPeriodo as $fecha)
                                            <th class="{{ $fecha->isToday() ? 'bg-primary' : '' }}" style="min-width: 120px;">
                                                {{ $fecha->translatedFormat('D d') }}
                                            </th>
                                        @endforeach
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($empleadosDeSucursal as $empleado)
                                        <tr id="row_emp_{{ $empleado->id_empleado }}" class="empleado-row" data-id="{{ $empleado->id_empleado }}">
                                            <td class="align-middle" style="text-align: left; position: sticky; left: 0; background-color: #f8f9fa; z-index: 1; border-right: 2px solid #dee2e6;">
                                                <div class="d-flex align-items-center">
                                                    {{-- EL OJITO HA VUELTO --}}
                                                    <i class="bi bi-eye-slash text-muted me-2" style="cursor: pointer; font-size: 0.9rem;" onclick="ocultarEmpleado({{ $empleado->id_empleado }})" title="Ocultar de esta lista"></i>
                                                    
                                                    <span class="fw-bold text-dark me-2">{{ $empleado->nombre_completo }}</span>
                                                    
                                                    @if($tipoPeriodo == 'dia')
                                                        @if($id_sucursal_seleccionada === 'todas')
                                                            <span class="badge bg-secondary me-1" style="font-size: 0.65em; white-space: nowrap;">
                                                                <i class="bi bi-shop"></i> {{ $empleado->sucursal->nombre_sucursal ?? 'Sin Sucursal' }}
                                                            </span>
                                                        @endif
                                                        <span class="badge bg-light text-secondary border" style="font-size: 0.65em; white-space: nowrap;">
                                                            {{ $empleado->puesto->nombre_puesto ?? 'Sin Puesto' }}
                                                        </span>
                                                    @else
                                                        @if($id_sucursal_seleccionada === 'todas')
                                                            <span class="badge bg-secondary" style="font-size: 0.65em; white-space: nowrap;">
                                                                <i class="bi bi-shop"></i> {{ $empleado->sucursal->nombre_sucursal ?? 'Sin Sucursal' }}
                                                            </span>
                                                        @endif
                                                    @endif
                                                </div>
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
                                                            case 'Baja_Dia': $claseFondo = 'bg-dark bg-opacity-10'; break;
                                                            case 'Incidencia': $claseFondo = 'bg-info bg-opacity-25'; break;
                                                        }
                                                    }
                                                    $estadoActualForm = $asistenciaDia ? ($asistenciaDia->status_asistencia == 'Retardo' ? 'Presente' : $asistenciaDia->status_asistencia) : 'Presente';
                                                @endphp
                                                <td class="p-2 {{ $claseFondo }}">
                                                    
                                                    {{-- MODO VISTA --}}
                                                    <div class="display-mode w-100 h-100 d-flex align-items-center justify-content-center" style="cursor: pointer;" onclick="activarEdicion(this)">
                                                        @if ($asistenciaDia)
                                                            <div class="w-100 fw-bold text-center" style="font-size: 0.9rem;">
                                                                @if (in_array($asistenciaDia->status_asistencia, ['Presente', 'Retardo']))
                                                                    <span class="text-{{ $asistenciaDia->status_asistencia == 'Retardo' ? 'warning-emphasis' : 'success' }}">
                                                                        {{ \Carbon\Carbon::parse($asistenciaDia->hora_llegada)->format('H:i') }}
                                                                    </span>
                                                                    @if($asistenciaDia->status_asistencia == 'Retardo') 
                                                                        <span class="text-warning-emphasis">/ Retardo</span> 
                                                                    @endif
                                                                @elseif ($asistenciaDia->status_asistencia == 'Falta')
                                                                    <span class="text-danger">FALTA</span>
                                                                @elseif ($asistenciaDia->status_asistencia == 'Baja_Dia')
                                                                    <span class="text-muted">BAJA</span>
                                                                @elseif ($asistenciaDia->status_asistencia == 'Incidencia')
                                                                    {{-- 🔥 INCIDENCIA CLARA Y HORA --}}
                                                                    <span class="text-info-emphasis d-block" style="font-size: 0.75rem; line-height: 1.2;">
                                                                        @if($asistenciaDia->hora_llegada) 
                                                                            <strong>{{ \Carbon\Carbon::parse($asistenciaDia->hora_llegada)->format('H:i') }}</strong> <br>
                                                                        @endif 
                                                                        {{ strtoupper($asistenciaDia->notas_incidencia ?: 'INCIDENCIA') }}
                                                                    </span>
                                                                @endif
                                                            </div>
                                                        @else
                                                            <span class="text-muted small border border-dashed rounded px-2"><i class="bi bi-plus"></i></span>
                                                        @endif
                                                    </div>

                                                    {{-- MODO EDICIÓN DIRECTO EN CELDA --}}
                                                    <div class="edit-mode d-none">
                                                        <form method="POST" action="{{ route('asistencia.registrarEntrada') }}" class="d-flex flex-column" onsubmit="prepararHoraAntesDeEnviar(this)">
                                                            @csrf
                                                            <input type="hidden" name="id_empleado" value="{{ $empleado->id_empleado }}">
                                                            <input type="hidden" name="fecha_registro" value="{{ $fechaString }}">
                                                            <input type="hidden" name="id_sucursal_seleccionada" value="{{ $id_sucursal_seleccionada }}">

                                                            <select name="status_asistencia" class="form-select form-select-sm mb-1 text-center fw-bold bg-white" style="font-size: 0.75rem; padding: 0px 2px;" data-periodo="{{ $tipoPeriodo }}" onchange="manejarCambioEstado(this)">
                                                                <option value="Presente" {{ $estadoActualForm == 'Presente' ? 'selected' : '' }}>Asistencia</option>
                                                                <option value="Falta" {{ $estadoActualForm == 'Falta' ? 'selected' : '' }}>Falta</option>
                                                                <option value="Baja_Dia" {{ $estadoActualForm == 'Baja_Dia' ? 'selected' : '' }}>Baja Día</option>
                                                                <option value="Incidencia" {{ $estadoActualForm == 'Incidencia' ? 'selected' : '' }}>Incidencia</option>
                                                            </select>

                                                            <input type="text" name="hora_llegada_manual" class="form-control form-control-sm mb-1 text-center input-hora" style="font-size: 0.8rem; padding: 0px;" placeholder="HH:MM" maxlength="5" oninput="formatearHoraAuto(this)" value="{{ $asistenciaDia && $asistenciaDia->hora_llegada ? \Carbon\Carbon::parse($asistenciaDia->hora_llegada)->format('H:i') : '' }}">

                                                            <input type="text" name="notas_incidencia" class="form-control form-control-sm mb-1 input-notas text-center" style="font-size: 0.75rem; padding: 1px;" placeholder="¿Qué pasó? (Ej. Accidente)" value="{{ $asistenciaDia && $asistenciaDia->status_asistencia == 'Incidencia' ? $asistenciaDia->notas_incidencia : '' }}">

                                                            <div class="d-flex gap-1 justify-content-center">
                                                                <button type="submit" class="btn btn-success btn-sm py-0 px-2" title="Guardar"><i class="bi bi-check-lg" style="font-size: 0.8rem;"></i></button>
                                                                <button type="button" class="btn btn-secondary btn-sm py-0 px-2" title="Cancelar" onclick="cancelarEdicion(this)"><i class="bi bi-x-lg" style="font-size: 0.8rem;"></i></button>
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
                        <div class="alert alert-warning mt-3 text-center">No hay datos de asistencia para mostrar.</div>
                    @endif
                @else
                    <div class="alert alert-info mt-4 text-center">Por favor, seleccione una sucursal para visualizar el control de asistencia.</div>
                @endif
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
        function activarEdicion(divVista) {
            document.querySelectorAll('.edit-mode').forEach(el => el.classList.add('d-none'));
            document.querySelectorAll('.display-mode').forEach(el => el.classList.remove('d-none'));

            let celda = divVista.closest('td');
            celda.querySelector('.display-mode').classList.add('d-none');
            let editMode = celda.querySelector('.edit-mode');
            editMode.classList.remove('d-none');
            
            let select = editMode.querySelector('select');
            manejarCambioEstado(select);
            
            setTimeout(() => editMode.querySelector('.input-hora').focus(), 50);
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
            let periodo = selectElement.getAttribute('data-periodo');

            if (selectElement.value === 'Presente') {
                inputHora.style.setProperty('display', 'block', 'important');
                inputHora.required = true;
                inputNotas.style.setProperty('display', 'none', 'important');
                inputNotas.required = false;
            } else if (selectElement.value === 'Incidencia') {
                // 🔥 AHORA SIEMPRE SE MUESTRAN AMBOS PARA INCIDENCIA
                inputHora.style.setProperty('display', 'block', 'important');
                inputHora.required = false; 
                inputNotas.style.setProperty('display', 'block', 'important');
                inputNotas.required = true;
            } else {
                inputHora.style.setProperty('display', 'none', 'important');
                inputHora.required = false;
                inputNotas.style.setProperty('display', 'none', 'important');
                inputNotas.required = false;
            }
        }

        function formatearHoraAuto(input) {
            let valor = input.value.replace(/\D/g, '');
            if (valor.length > 2) {
                valor = valor.substring(0, 2) + ':' + valor.substring(2, 4);
            }
            input.value = valor;
        }

        function prepararHoraAntesDeEnviar(form) {
            let inputHora = form.querySelector('.input-hora');
            if (inputHora.style.display !== 'none' && inputHora.value && inputHora.value.length < 5) {
                let numericos = inputHora.value.replace(':', '');
                if(numericos.length === 3) {
                    inputHora.value = '0' + numericos.substring(0,1) + ':' + numericos.substring(1,3);
                }
            }
        }

        const storageKey = 'empleados_ocultos_asistencia';

        document.addEventListener('DOMContentLoaded', function () {
            initOcultos();
        });

        function initOcultos() {
            let ocultos = JSON.parse(localStorage.getItem(storageKey)) || [];
            let count = 0;
            document.querySelectorAll('.empleado-row').forEach(row => {
                let id = parseInt(row.getAttribute('data-id'));
                if (ocultos.includes(id)) {
                    row.classList.add('d-none');
                    count++;
                }
            });
            actualizarBotonOcultos(count);
        }

        function ocultarEmpleado(id) {
            let ocultos = JSON.parse(localStorage.getItem(storageKey)) || [];
            if (!ocultos.includes(id)) {
                ocultos.push(id);
                localStorage.setItem(storageKey, JSON.stringify(ocultos));
            }
            let row = document.getElementById('row_emp_' + id);
            if (row) {
                row.classList.add('d-none');
            }
            
            let count = document.querySelectorAll('.empleado-row.d-none').length;
            actualizarBotonOcultos(count);
        }

        function mostrarOcultos() {
            localStorage.removeItem(storageKey);
            document.querySelectorAll('.empleado-row').forEach(row => {
                row.classList.remove('d-none');
            });
            actualizarBotonOcultos(0);
        }

        function actualizarBotonOcultos(count) {
            let btn = document.getElementById('btn-mostrar-ocultos');
            if (count > 0) {
                btn.classList.remove('d-none');
                btn.innerHTML = `<i class="bi bi-eye"></i> Mostrar ${count} ocultos`;
            } else {
                btn.classList.add('d-none');
            }
        }
        </script>
    @endpush
</x-app-layout>