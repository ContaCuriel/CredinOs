<x-app-layout>
    <div class="container-fluid py-4">
        <div class="card shadow-sm border-0 rounded-3">
            <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
                <h5 class="mb-0 text-dark fw-bold"><i class="bi bi-shield-check text-primary me-2"></i> Pre-Cierre de Asistencias (Interactivo)</h5>
                
                <div>
                    <button id="btn-restaurar-incidencias" class="btn btn-outline-warning btn-sm d-none fw-bold me-2" onclick="restaurarIncidencias()">
                        <i class="bi bi-arrow-counterclockwise"></i> Restaurar <span id="contador-incidencias-ocultas" class="badge bg-warning text-dark ms-1">0</span> perdonadas
                    </button>
                    <button id="btn-restaurar-ocultos" class="btn btn-outline-info btn-sm d-none fw-bold" onclick="restaurarFilas()">
                        <i class="bi bi-arrow-counterclockwise"></i> Restaurar <span id="contador-ocultos" class="badge bg-info text-white ms-1">0</span> empleados ocultos
                    </button>
                </div>
            </div>
            
            <div class="card-body bg-light">
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show shadow-sm border-0" role="alert">
                        <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif
                @if(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show shadow-sm border-0" role="alert">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i> {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                <div class="alert alert-light border border-info border-start-5 mb-4 shadow-sm text-secondary rounded" style="border-left-width: 5px !important;">
                    <strong><i class="bi bi-info-circle text-info me-1"></i> ¿Cómo funciona?</strong> Aquí verás a los empleados que tuvieron incidencias a descontar.
                    <br><i class="bi bi-check2-square text-success me-1"></i> Desmarca la casilla de una incidencia para "perdonarla". <strong>Ésta se ocultará</strong> para limpiar tu vista y recalculará el total.
                    <br><i class="bi bi-pencil-square text-primary me-1"></i> Puedes modificar el <strong>Descuento Final</strong> manualmente en la caja de texto antes de guardar.
                    <br><i class="bi bi-trash text-danger me-1"></i> Oculta empleados temporalmente (ej. dueños o exentos) para mandarlos en cero al cierre.
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
                                        <th style="width: 55%; text-align: left;">Detalle de Incidencias en la Quincena</th>
                                        <th style="width: 15%;">Descuento Final</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($empleadosData as $emp)
                                        <tr id="row_{{ $emp['id_empleado'] }}" class="empleado-row">
                                            
                                            {{-- INPUTS OCULTOS (Nota: El de faltas ya no tiene name, es solo para JS) --}}
                                            <input type="hidden" id="input_faltas_{{ $emp['id_empleado'] }}" value="{{ $emp['faltas_directas'] + ($emp['medios_dias_crudos'] * 0.5) }}">
                                            <input type="hidden" name="empleados[{{ $emp['id_empleado'] }}][retardos]" id="input_retardos_{{ $emp['id_empleado'] }}" value="{{ $emp['retardos_crudos'] }}">

                                            <td class="text-center bg-light">
                                                <button type="button" class="btn btn-sm btn-outline-danger border-0 shadow-sm rounded-circle" onclick="ocultarFila({{ $emp['id_empleado'] }})" title="Ocultar (Mandar en ceros)">
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
                                            <td style="text-align: left; background-color: #fafbfc; padding: 12px;">
                                                <div class="d-flex flex-column gap-2">
                                                    @foreach($emp['detalles'] as $idx => $incidencia)
                                                        @php
                                                            $nombresDias = ['Sunday' => 'Domingo', 'Monday' => 'Lunes', 'Tuesday' => 'Martes', 'Wednesday' => 'Miércoles', 'Thursday' => 'Jueves', 'Friday' => 'Viernes', 'Saturday' => 'Sábado'];
                                                            $nombresMeses = ['Jan' => 'Ene', 'Feb' => 'Feb', 'Mar' => 'Mar', 'Apr' => 'Abr', 'May' => 'May', 'Jun' => 'Jun', 'Jul' => 'Jul', 'Aug' => 'Ago', 'Sep' => 'Sep', 'Oct' => 'Oct', 'Nov' => 'Nov', 'Dec' => 'Dic'];
                                                            
                                                            $fechaCarbon = \Carbon\Carbon::parse($incidencia['fecha']);
                                                            $diaSemana = $nombresDias[$fechaCarbon->format('l')];
                                                            $diaMes = $fechaCarbon->format('d') . '-' . $nombresMeses[$fechaCarbon->format('M')];
                                                            
                                                            $horaTxt = isset($incidencia['hora']) && $incidencia['hora'] ? " a las " . \Carbon\Carbon::parse($incidencia['hora'])->format('g:i A') : "";
                                                            
                                                            $etiqueta = '';
                                                            $colorClass = '';
                                                            
                                                            if ($incidencia['tipo'] == 'falta') {
                                                                $tipoFaltaTxt = $incidencia['penalizacion'] == 1 ? 'Falta' : ($incidencia['penalizacion'] == 2 ? 'Falta doble' : "Falta ({$incidencia['penalizacion']}d)");
                                                                $etiqueta = "{$tipoFaltaTxt} (Sin registro de asistencia)";
                                                                $colorClass = "danger";
                                                            } elseif ($incidencia['tipo'] == 'falta_por_retardo_extremo') {
                                                                $tipoFaltaTxt = $incidencia['penalizacion'] == 1 ? 'Falta' : ($incidencia['penalizacion'] == 2 ? 'Falta doble' : "Falta ({$incidencia['penalizacion']}d)");
                                                                $etiqueta = "{$tipoFaltaTxt} por retardo extremo al llegar{$horaTxt}";
                                                                $colorClass = "danger";
                                                            } elseif ($incidencia['tipo'] == 'medio_dia') {
                                                                $etiqueta = "Medio día por llegar{$horaTxt}";
                                                                $colorClass = "warning text-dark";
                                                            } elseif ($incidencia['tipo'] == 'retardo') {
                                                                $etiqueta = "Retardo por llegar{$horaTxt}";
                                                                $colorClass = "secondary text-dark";
                                                            } elseif ($incidencia['tipo'] == 'incidencia') {
                                                                $nota = isset($incidencia['notas']) && $incidencia['notas'] != '' ? $incidencia['notas'] : 'Incidencia manual';
                                                                $etiqueta = ucfirst($nota) . $horaTxt;
                                                                $colorClass = "info text-dark";
                                                            }
                                                        @endphp
                                                        
                                                        <div class="form-check form-switch d-flex align-items-center mb-0 incidencia-container" style="border-bottom: 1px dashed #e9ecef; padding-bottom: 5px;">
                                                            <input class="form-check-input mt-0 me-3 check-incidencia flex-shrink-0" type="checkbox" checked
                                                                style="cursor: pointer;"
                                                                id="chk_{{ $emp['id_empleado'] }}_{{ $idx }}"
                                                                data-empleado="{{ $emp['id_empleado'] }}"
                                                                data-regla="{{ $emp['regla_retardos'] }}"
                                                                data-tipo="{{ $incidencia['tipo'] }}"
                                                                data-penalizacion="{{ $incidencia['penalizacion'] }}"
                                                                onchange="recalcularFila(this)">
                                                            
                                                            <label class="form-check-label flex-grow-1 text-{{ $colorClass }}" for="chk_{{ $emp['id_empleado'] }}_{{ $idx }}" style="cursor: pointer; font-size: 0.85rem;">
                                                                <i>{{ $diaSemana }} {{ $diaMes }}:</i> <strong>{{ $etiqueta }}</strong>
                                                            </label>

                                                            @if($incidencia['tipo'] == 'incidencia')
                                                                <select class="form-select form-select-sm border-info text-info fw-bold py-0 ms-2 bg-info bg-opacity-10" style="width: auto; font-size: 0.75rem; cursor: pointer;" onchange="actualizarPenalizacionIncidencia(this, 'chk_{{ $emp['id_empleado'] }}_{{ $idx }}')">
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
                                            
                                            {{-- 🔥 ESTA ES LA CAJA DE TEXTO EDITABLE QUE MANDA EL RESULTADO --}}
                                            <td class="text-center align-middle bg-light border-start-0" style="min-width: 120px;">
                                                <div class="d-flex justify-content-center mb-1">
                                                    <input type="number" 
                                                           step="0.5" 
                                                           min="0"
                                                           name="empleados[{{ $emp['id_empleado'] }}][faltas]" 
                                                           id="txt_total_{{ $emp['id_empleado'] }}" 
                                                           value="{{ $emp['total_dias_descuento_inicial'] }}" 
                                                           class="form-control text-center fw-bold shadow-sm {{ $emp['total_dias_descuento_inicial'] > 0 ? 'text-danger border-danger' : 'text-success border-success' }}" 
                                                           style="max-width: 90px; font-size: 1.5rem;">
                                                </div>
                                                <span id="label_total_{{ $emp['id_empleado'] }}" class="fw-bold text-uppercase {{ $emp['total_dias_descuento_inicial'] > 0 ? 'text-danger' : 'text-success' }}" style="font-size: 0.7rem; letter-spacing: 0.5px;">
                                                    Días a Descontar
                                                </span>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        
                        {{-- BARRA FLOTANTE DE BOTÓN DE ACCIÓN --}}
                        <div class="position-sticky bottom-0 bg-white p-3 border-top shadow-lg rounded-top" style="z-index: 1000;">
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="text-muted small"><i class="bi bi-info-circle"></i> Los empleados ocultos se mandarán con CERO faltas al cierre.</span>
                                <div>
                                    <button type="button" class="btn btn-outline-primary btn-lg fw-bold shadow-sm me-2" onclick="guardarBorrador()">
                                        <i class="bi bi-floppy"></i> Guardar Borrador
                                    </button>
                                    <button type="submit" class="btn btn-success btn-lg fw-bold shadow" onclick="return confirm('¿Estás seguro de cerrar este periodo? Los datos se guardarán y estarán listos para la Lista de Raya.')">
                                        <i class="bi bi-check2-all"></i> Guardar Cierre de Asistencias
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>

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
        let incidenciasOcultasCount = 0;
        let ocultosCount = 0;

        function actualizarPenalizacionIncidencia(selectElement, checkboxId) {
            let checkbox = document.getElementById(checkboxId);
            checkbox.setAttribute('data-penalizacion', selectElement.value);
            
            if (parseFloat(selectElement.value) > 0 && !checkbox.checked) {
                checkbox.checked = true;
                checkbox.closest('.incidencia-container').classList.remove('d-none');
            }
            recalcularFila(checkbox);
        }

        function recalcularFila(checkboxToggled) {
            let idEmpleado = checkboxToggled.getAttribute('data-empleado');
            let reglaRetardos = parseInt(checkboxToggled.getAttribute('data-regla'));
            let container = checkboxToggled.closest('.incidencia-container');
            
            if (!checkboxToggled.checked) {
                container.classList.add('d-none');
                incidenciasOcultasCount++;
                document.getElementById('contador-incidencias-ocultas').innerText = incidenciasOcultasCount;
                document.getElementById('btn-restaurar-incidencias').classList.remove('d-none');
            }

            procesarCalculo(idEmpleado, reglaRetardos);
        }

        function restaurarIncidencias() {
            document.querySelectorAll('.check-incidencia:not(:checked)').forEach(chk => {
                chk.checked = true;
                chk.closest('.incidencia-container').classList.remove('d-none');
                recalcularFilaRestauracion(chk);
            });
            
            incidenciasOcultasCount = 0;
            document.getElementById('contador-incidencias-ocultas').innerText = '0';
            document.getElementById('btn-restaurar-incidencias').classList.add('d-none');
        }

        function recalcularFilaRestauracion(checkboxToggled) {
            let idEmpleado = checkboxToggled.getAttribute('data-empleado');
            let reglaRetardos = parseInt(checkboxToggled.getAttribute('data-regla'));
            procesarCalculo(idEmpleado, reglaRetardos);
        }

        // Función unificada para hacer el cálculo y actualizar la caja editable
        function procesarCalculo(idEmpleado, reglaRetardos) {
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

            document.getElementById('input_faltas_' + idEmpleado).value = totalFaltas;
            document.getElementById('input_retardos_' + idEmpleado).value = conteoRetardosNormales;

            let faltasPorRetardos = reglaRetardos > 0 ? Math.floor(conteoRetardosNormales / reglaRetardos) : 0;
            let totalDias = totalFaltas + faltasPorRetardos;

            // ACTUALIZAMOS LA CAJA DE TEXTO (Ahora es .value)
            let txtTotal = document.getElementById('txt_total_' + idEmpleado);
            let labelTotal = document.getElementById('label_total_' + idEmpleado);

            txtTotal.value = totalDias % 1 === 0 ? totalDias : totalDias.toFixed(1);
            
            // Cambiamos clases de color
            if (totalDias > 0) {
                txtTotal.classList.replace('text-success', 'text-danger');
                txtTotal.classList.replace('border-success', 'border-danger');
                labelTotal.classList.replace('text-success', 'text-danger');
            } else {
                txtTotal.classList.replace('text-danger', 'text-success');
                txtTotal.classList.replace('border-danger', 'border-success');
                labelTotal.classList.replace('text-danger', 'text-success');
            }
        }

        function ocultarFila(idEmpleado) {
            let fila = document.getElementById('row_' + idEmpleado);
            fila.style.display = 'none';
            
            document.getElementById('input_faltas_' + idEmpleado).value = 0;
            document.getElementById('input_retardos_' + idEmpleado).value = 0;
            
            // Forzamos la caja editable a 0 para que no mande cargos
            let txtTotal = document.getElementById('txt_total_' + idEmpleado);
            txtTotal.value = 0;
            txtTotal.classList.replace('text-danger', 'text-success');
            txtTotal.classList.replace('border-danger', 'border-success');
            document.getElementById('label_total_' + idEmpleado).classList.replace('text-danger', 'text-success');
            
            ocultosCount++;
            document.getElementById('contador-ocultos').innerText = ocultosCount;
            document.getElementById('btn-restaurar-ocultos').classList.remove('d-none');
        }

        function restaurarFilas() {
            document.querySelectorAll('.empleado-row').forEach(row => {
                if(row.style.display === 'none') {
                    row.style.display = '';
                    let checks = row.querySelectorAll('.check-incidencia');
                    if(checks.length > 0) {
                        recalcularFilaRestauracion(checks[0]); 
                    }
                }
            });
            ocultosCount = 0;
            document.getElementById('contador-ocultos').innerText = ocultosCount;
            document.getElementById('btn-restaurar-ocultos').classList.add('d-none');
        }

        // =======================================================
        // 🔥 LÓGICA DE BORRADOR LOCAL (Sin tocar base de datos)
        // =======================================================
        function obtenerClaveBorrador() {
            const periodo = document.querySelector('input[name="periodo_cierre"]')?.value;
            const sucursal = document.querySelector('input[name="id_sucursal_cierre"]')?.value;
            if (!periodo || !sucursal) return null;
            return `borrador_precierre_${periodo}_${sucursal}`;
        }

        function guardarBorrador() {
            const clave = obtenerClaveBorrador();
            if (!clave) return;

            let borrador = {};
            document.querySelectorAll('.empleado-row').forEach(row => {
                let empId = row.id.split('_')[1];
                let oculto = row.style.display === 'none';
                let txtTotal = document.getElementById('txt_total_' + empId).value; // Guarda si lo editaste a mano
                
                let checks = [];
                row.querySelectorAll('.check-incidencia').forEach(chk => {
                    checks.push({
                        id: chk.id,
                        checked: chk.checked,
                        penalizacion: chk.getAttribute('data-penalizacion')
                    });
                });
                
                borrador[empId] = { oculto: oculto, txtTotal: txtTotal, checks: checks };
            });

            localStorage.setItem(clave, JSON.stringify(borrador));
            alert('💾 ¡Borrador guardado con éxito!\nPuedes salir de esta pantalla y cuando regreses a este mismo periodo, tus avances seguirán aquí.');
        }

        function cargarBorrador() {
            const clave = obtenerClaveBorrador();
            if (!clave) return;

            let borradorStr = localStorage.getItem(clave);
            if (borradorStr) {
                let borrador = JSON.parse(borradorStr);
                
                Object.keys(borrador).forEach(empId => {
                    let data = borrador[empId];
                    let row = document.getElementById('row_' + empId);
                    if (!row) return;

                    // 1. Restaurar switches
                    data.checks.forEach(cData => {
                        let chk = document.getElementById(cData.id);
                        if (chk) {
                            chk.checked = cData.checked;
                            chk.setAttribute('data-penalizacion', cData.penalizacion);
                            
                            let select = chk.closest('.incidencia-container').querySelector('select');
                            if (select) select.value = cData.penalizacion;

                            if (!chk.checked) {
                                chk.closest('.incidencia-container').classList.add('d-none');
                                incidenciasOcultasCount++;
                            }
                        }
                    });

                    // 2. Ejecutar cálculos básicos para estabilizar la fila
                    let firstChk = row.querySelector('.check-incidencia');
                    if (firstChk) recalcularFilaRestauracion(firstChk);

                    // 3. SOBRESCRIBIR EL INPUT con el valor exacto que dejaste (por si editaste a mano)
                    let txtTotal = document.getElementById('txt_total_' + empId);
                    txtTotal.value = data.txtTotal;
                    
                    let labelTotal = document.getElementById('label_total_' + empId);
                    if (data.txtTotal > 0) {
                        txtTotal.classList.replace('text-success', 'text-danger');
                        txtTotal.classList.replace('border-success', 'border-danger');
                        labelTotal.classList.replace('text-success', 'text-danger');
                    } else {
                        txtTotal.classList.replace('text-danger', 'text-success');
                        txtTotal.classList.replace('border-danger', 'border-success');
                        labelTotal.classList.replace('text-danger', 'text-success');
                    }

                    // 4. Ocultar si el empleado fue mandado al bote de basura
if (data.oculto) {
    row.style.display = 'none';
    
    // 🔥 CORRECCIÓN: Forzamos los inputs ocultos a 0 para que no manden basura al guardar
    document.getElementById('input_retardos_' + empId).value = 0;
    document.getElementById('input_faltas_' + empId).value = 0;
    
    ocultosCount++;
}
                });

                // Actualizar los globos de los botones de restauración
                if (incidenciasOcultasCount > 0) {
                    document.getElementById('contador-incidencias-ocultas').innerText = incidenciasOcultasCount;
                    document.getElementById('btn-restaurar-incidencias').classList.remove('d-none');
                }
                if (ocultosCount > 0) {
                    document.getElementById('contador-ocultos').innerText = ocultosCount;
                    document.getElementById('btn-restaurar-ocultos').classList.remove('d-none');
                }
                
                // Limpiar la memoria para que, si envían los datos finales, no se atore el borrador viejo la próxima vez
                // localStorage.removeItem(clave); // Opcional: Descomentar si quieres que el borrador se autodestruya al cargar
            }
        }

        // Cargar el borrador automáticamente cuando cargue la página
        document.addEventListener('DOMContentLoaded', cargarBorrador);
        </script>
    @endpush
</x-app-layout>