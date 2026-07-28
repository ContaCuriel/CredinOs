<x-app-layout>
    <div class="container-fluid py-4">
        <div class="card shadow-sm border-0 rounded-3">
            <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
                <h5 class="mb-0 text-dark fw-bold"><i class="bi bi-shield-check text-primary me-2"></i> Pre-Cierre de Asistencias (Interactivo)</h5>
                <button id="btn-restaurar-ocultos" class="btn btn-outline-info btn-sm d-none fw-bold" onclick="restaurarFilas()">
                    <i class="bi bi-arrow-counterclockwise"></i> Restaurar <span id="contador-ocultos" class="badge bg-info text-white ms-1">0</span> ocultos
                </button>
            </div>
            
            <div class="card-body bg-light">
                <div class="alert alert-light border border-info border-start-5 mb-4 shadow-sm text-secondary rounded" style="border-left-width: 5px !important;">
                    <strong><i class="bi bi-info-circle text-info me-1"></i> ¿Cómo funciona?</strong> Aquí verás a los empleados que tuvieron incidencias a descontar.
                    <br><i class="bi bi-check2-square text-success me-1"></i> Desmarca la casilla de una incidencia para "perdonarla" y ver cómo se recalcula el total al instante.
                    <br><i class="bi bi-exclamation-circle text-warning me-1"></i> Las <strong>Incidencias/Permisos</strong> valen 0 días por defecto, pero puedes asignarles una penalización manual.
                    <br><i class="bi bi-trash text-danger me-1"></i> Oculta empleados temporalmente (ej. dueños o exentos) para no mandarlos al cierre.
                </div>

                {{-- Formulario de Filtros --}}
                <form method="GET" action="{{ route('asistencia.pre_cierre') }}" class="mb-4 p-4 bg-white rounded border shadow-sm">
                    <div class="row align-items-end g-3 justify-content-center">
                        <div class="col-md-4">
                            <label for="periodo" class="form-label mb-1 fw-bold text-secondary">Periodo (Quincena):</label>
                            <select name="periodo" id="periodo" class="form-select border-primary bg-primary bg-opacity-10 fw-bold" required>
                                <option value="">Seleccione una quincena...</option>
                                @foreach ($opcionesPeriodo as $opcion)
                                    <option value="{{ $opcion['valor'] }}" {{ request('periodo') == $opcion['valor'] ? 'selected' : '' }}>
                                        {{ $opcion['texto'] }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label for="id_sucursal" class="form-label mb-1 fw-bold text-secondary">Sucursal:</label>
                            <select name="id_sucursal" id="id_sucursal" class="form-select border-primary bg-primary bg-opacity-10 fw-bold" required>
                                <option value="">Seleccione una sucursal...</option>
                                <option value="todas" {{ request('id_sucursal') == 'todas' ? 'selected' : '' }} class="fw-bold text-primary">-- TODAS LAS SUCURSALES --</option>
                                @foreach ($sucursales as $sucursal)
                                    <option value="{{ $sucursal->id_sucursal }}" {{ request('id_sucursal') == $sucursal->id_sucursal ? 'selected' : '' }}>
                                        {{ $sucursal->nombre_sucursal }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary w-100 fw-bold shadow-sm">
                                <i class="bi bi-calculator"></i> Analizar
                            </button>
                        </div>
                    </div>
                </form>

                {{-- Resultados del Pre-Cierre --}}
                @if(isset($empleadosData) && $empleadosData->isNotEmpty())
                    <h5 class="mb-3">Resultados para: <span class="text-primary fw-bold">{{ $sucursalSeleccionada->nombre_sucursal ?? '' }}</span></h5>
                    
                    {{-- 🔥 INICIO DEL FORMULARIO HACIA LA BASE DE DATOS --}}
                    <form method="POST" action="{{ route('asistencia.procesar_cierre') }}">
                        @csrf
                        <input type="hidden" name="periodo_cierre" value="{{ request('periodo') }}">
                        <input type="hidden" name="id_sucursal_cierre" value="{{ request('id_sucursal') }}">

                        <div class="table-responsive shadow-sm mb-5" style="border-radius: 8px;">
                            <table class="table table-hover table-bordered align-middle mb-0 bg-white">
                                <thead class="table-dark text-center">
                                    <tr>
                                        <th style="width: 5%;"></th>
                                        <th style="width: 25%; text-align: left;">Empleado</th>
                                        <th style="width: 55%; text-align: left;">Detalle de Incidencias</th>
                                        <th style="width: 15%;">Descuento Final</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($empleadosData as $emp)
                                        <tr id="row_{{ $emp['id_empleado'] }}" class="empleado-row">
                                            
                                            {{-- 🔥 INPUTS OCULTOS (Mandan datos crudos al backend) --}}
                                            <input type="hidden" name="empleados[{{ $emp['id_empleado'] }}][faltas]" id="input_faltas_{{ $emp['id_empleado'] }}" value="{{ $emp['faltas_directas'] + ($emp['medios_dias_crudos'] * 0.5) }}">
                                            <input type="hidden" name="empleados[{{ $emp['id_empleado'] }}][retardos]" id="input_retardos_{{ $emp['id_empleado'] }}" value="{{ $emp['retardos_crudos'] }}">

                                            <td class="text-center bg-light">
                                                <button type="button" class="btn btn-sm btn-outline-danger border-0 shadow-sm rounded-circle" onclick="ocultarFila({{ $emp['id_empleado'] }})" title="Ocultar (No enviar al cierre)">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </td>
                                            <td style="text-align: left;" class="border-end-0">
                                                <span class="fw-bold text-dark d-block" style="font-size: 0.95rem;">{{ $emp['nombre'] }}</span>
                                                
                                                @if(request('id_sucursal') === 'todas')
                                                    <span class="badge bg-secondary mb-1 mt-1" style="font-size: 0.65em;"><i class="bi bi-shop"></i> {{ $emp['sucursal'] }}</span><br>
                                                @endif
                                                
                                                <span class="badge bg-light text-secondary border mt-1" style="font-size: 0.7em;">
                                                    <i class="bi bi-person-badge"></i> {{ $emp['puesto'] }}
                                                </span><br>
                                                
                                                <span class="text-muted" style="font-size: 0.7em;">
                                                    <strong>Regla:</strong> {{ $emp['regla_retardos'] > 0 ? $emp['regla_retardos'] . ' Retardos = 1 Falta' : 'Sin castigo acum.' }}
                                                </span>
                                            </td>
                                            <td style="text-align: left; background-color: #fafbfc;">
                                                <div class="d-flex flex-wrap gap-2">
                                                    @foreach($emp['detalles'] as $idx => $incidencia)
                                                        @php
                                                            $fechaFormateada = \Carbon\Carbon::parse($incidencia['fecha'])->format('d/m');
                                                            $etiqueta = '';
                                                            $colorClass = '';
                                                            
                                                            $horaTxt = isset($incidencia['hora']) && $incidencia['hora'] ? " ({$incidencia['hora']})" : "";
                                                            
                                                            if ($incidencia['tipo'] == 'falta') {
                                                                $etiqueta = "Falta ({$incidencia['penalizacion']}d)";
                                                                $colorClass = "danger";
                                                            } elseif ($incidencia['tipo'] == 'falta_por_retardo_extremo') {
                                                                $etiqueta = "R. Extremo{$horaTxt} ({$incidencia['penalizacion']}d)";
                                                                $colorClass = "danger";
                                                            } elseif ($incidencia['tipo'] == 'medio_dia') {
                                                                $etiqueta = "Medio Día{$horaTxt} (0.5d)";
                                                                $colorClass = "warning text-dark";
                                                            } elseif ($incidencia['tipo'] == 'retardo') {
                                                                $etiqueta = "Retardo{$horaTxt}";
                                                                $colorClass = "secondary text-dark";
                                                            } elseif ($incidencia['tipo'] == 'incidencia') {
                                                                $nota = isset($incidencia['notas']) && $incidencia['notas'] != '' ? $incidencia['notas'] : 'Incidencia';
                                                                $etiqueta = strtoupper($nota) . $horaTxt;
                                                                $colorClass = "info text-dark";
                                                            }
                                                        @endphp
                                                        
                                                        <div class="form-check form-switch bg-white border border-{{ str_replace(' text-dark', '', $colorClass) }} rounded-pill px-3 py-1 shadow-sm d-flex align-items-center mb-1" style="font-size: 0.8rem; border-width: 2px !important;">
                                                            <input class="form-check-input mt-0 me-2 check-incidencia" type="checkbox" checked
                                                                id="chk_{{ $emp['id_empleado'] }}_{{ $idx }}"
                                                                data-tipo="{{ $incidencia['tipo'] }}"
                                                                data-penalizacion="{{ $incidencia['penalizacion'] }}"
                                                                onchange="recalcularFila({{ $emp['id_empleado'] }}, {{ $emp['regla_retardos'] }}, this)">
                                                            
                                                            <label class="form-check-label text-{{ $colorClass }} fw-bold mb-0 pt-1 me-2" for="chk_{{ $emp['id_empleado'] }}_{{ $idx }}" style="cursor: pointer; user-select: none;">
                                                                {{ $fechaFormateada }} - {{ $etiqueta }}
                                                            </label>

                                                            @if($incidencia['tipo'] == 'incidencia')
                                                                <select class="form-select form-select-sm border-info text-info fw-bold py-0 bg-info bg-opacity-10" style="width: auto; font-size: 0.75rem; cursor: pointer;" onchange="actualizarPenalizacionIncidencia(this, 'chk_{{ $emp['id_empleado'] }}_{{ $idx }}', {{ $emp['id_empleado'] }}, {{ $emp['regla_retardos'] }})">
                                                                    <option value="0" selected>Pena: 0d</option>
                                                                    <option value="0.5">Pena: 0.5d</option>
                                                                    <option value="1">Pena: 1d</option>
                                                                    <option value="2">Pena: 2d</option>
                                                                    <option value="3">Pena: 3d</option>
                                                                </select>
                                                            @endif
                                                        </div>
                                                    @endforeach
                                                </div>
                                            </td>
                                            <td class="text-center align-middle bg-light border-start-0">
                                                @if($emp['total_dias_descuento_inicial'] > 0)
                                                    <h3 class="mb-0 fw-bold text-danger"><span id="txt_total_{{ $emp['id_empleado'] }}">{{ $emp['total_dias_descuento_inicial'] }}</span></h3>
                                                    <span class="text-danger fw-bold text-uppercase" style="font-size: 0.7rem; letter-spacing: 0.5px;">Días a Descontar</span>
                                                @else
                                                    <h3 class="mb-0 fw-bold text-success"><span id="txt_total_{{ $emp['id_empleado'] }}">0</span></h3>
                                                    <span class="text-success fw-bold text-uppercase" style="font-size: 0.7rem; letter-spacing: 0.5px;">Días a Descontar</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        
                        {{-- 🔥 BARRA FLOTANTE DE BOTÓN DE ACCIÓN --}}
                        <div class="position-sticky bottom-0 bg-white p-3 border-top shadow-lg rounded-top" style="z-index: 1000;">
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="text-muted small"><i class="bi bi-info-circle"></i> Los empleados ocultos no serán enviados.</span>
                                <button type="submit" class="btn btn-success btn-lg fw-bold shadow" onclick="return confirm('¿Estás seguro de cerrar este periodo? Los datos se guardarán y estarán listos para la Lista de Raya.')">
                                    <i class="bi bi-check2-all"></i> Guardar Cierre de Asistencias
                                </button>
                            </div>
                        </div>
                    </form>
                    {{-- FIN DEL FORMULARIO --}}

                @elseif(request()->filled('periodo'))
                    <div class="alert alert-success text-center mt-3 shadow-sm border-0">
                        <i class="bi bi-emoji-sunglasses text-success" style="font-size: 1.5rem;"></i><br>
                        ¡Excelente! Todos los empleados tuvieron <strong>Asistencia Perfecta</strong> o no se encontraron datos para estos parámetros.
                    </div>
                @endif
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
        function actualizarPenalizacionIncidencia(selectElement, checkboxId, idEmpleado, reglaRetardos) {
            let checkbox = document.getElementById(checkboxId);
            checkbox.setAttribute('data-penalizacion', selectElement.value);
            
            if (parseFloat(selectElement.value) > 0 && !checkbox.checked) {
                checkbox.checked = true;
            }
            recalcularFila(idEmpleado, reglaRetardos, checkbox);
        }

        function recalcularFila(idEmpleado, reglaRetardos, checkboxToggled) {
            let label = checkboxToggled.nextElementSibling;
            let container = checkboxToggled.closest('.form-check');
            
            if (checkboxToggled.checked) {
                label.style.textDecoration = 'none';
                container.style.opacity = '1';
                container.classList.remove('bg-light');
            } else {
                label.style.textDecoration = 'line-through';
                container.style.opacity = '0.5';
                container.classList.add('bg-light');
            }

            let fila = document.getElementById('row_' + idEmpleado);
            let checkboxes = fila.querySelectorAll('.check-incidencia:checked'); 
            
            let totalFaltas = 0;
            let conteoRetardosNormales = 0;

            checkboxes.forEach(chk => {
                let tipo = chk.getAttribute('data-tipo');
                let penalizacion = parseFloat(chk.getAttribute('data-penalizacion'));

                if (tipo === 'falta' || tipo === 'falta_por_retardo_extremo' || tipo === 'incidencia' || tipo === 'medio_dia') {
                    totalFaltas += penalizacion;
                } else if (tipo === 'retardo') {
                    conteoRetardosNormales++;
                }
            });

            // Actualizamos los inputs ocultos
            document.getElementById('input_faltas_' + idEmpleado).value = totalFaltas;
            document.getElementById('input_retardos_' + idEmpleado).value = conteoRetardosNormales;

            // Calculamos visualmente el total
            let faltasPorRetardos = reglaRetardos > 0 ? Math.floor(conteoRetardosNormales / reglaRetardos) : 0;
            let totalDias = totalFaltas + faltasPorRetardos;

            let txtTotal = document.getElementById('txt_total_' + idEmpleado);
            txtTotal.innerText = totalDias % 1 === 0 ? totalDias : totalDias.toFixed(1);

            let parentTd = txtTotal.closest('td');
            let smallLabel = parentTd.querySelector('span');
            
            if (totalDias > 0) {
                txtTotal.parentElement.classList.remove('text-success');
                txtTotal.parentElement.classList.add('text-danger');
                smallLabel.classList.remove('text-success');
                smallLabel.classList.add('text-danger');
            } else {
                txtTotal.parentElement.classList.remove('text-danger');
                txtTotal.parentElement.classList.add('text-success');
                smallLabel.classList.remove('text-danger');
                smallLabel.classList.add('text-success');
            }
        }

        let ocultosCount = 0;

        function ocultarFila(idEmpleado) {
            let fila = document.getElementById('row_' + idEmpleado);
            fila.style.display = 'none';
            
            // Si lo ocultamos, mandamos 0 para no afectarlo
            document.getElementById('input_faltas_' + idEmpleado).value = 0;
            document.getElementById('input_retardos_' + idEmpleado).value = 0;
            
            ocultosCount++;
            document.getElementById('contador-ocultos').innerText = ocultosCount;
            document.getElementById('btn-restaurar-ocultos').classList.remove('d-none');
        }

        function restaurarFilas() {
            document.querySelectorAll('.empleado-row').forEach(row => {
                row.style.display = '';
                // Simulamos un evento change para recalcular a su estado actual de checkboxes
                let checks = row.querySelectorAll('.check-incidencia');
                if(checks.length > 0) {
                    checks[0].dispatchEvent(new Event('change'));
                }
            });
            ocultosCount = 0;
            document.getElementById('contador-ocultos').innerText = ocultosCount;
            document.getElementById('btn-restaurar-ocultos').classList.add('d-none');
        }
        </script>
    @endpush
</x-app-layout>