<x-app-layout>
    <div class="container-fluid py-4">
        <div class="card shadow-sm">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0 text-dark fw-bold"><i class="bi bi-shield-check text-primary"></i> Pre-Cierre de Asistencias (Interactivo)</h5>
                <button id="btn-restaurar-ocultos" class="btn btn-outline-info btn-sm d-none" onclick="restaurarFilas()">
                    <i class="bi bi-arrow-counterclockwise"></i> Restaurar <span id="contador-ocultos">0</span> ocultos
                </button>
            </div>
            
            <div class="card-body">
                <div class="alert alert-light border border-info border-start-5 mb-4 shadow-sm text-secondary" style="border-left-width: 5px !important;">
                    <strong>¿Cómo funciona?</strong> Aquí verás el cálculo puro de incidencias de la quincena. 
                    <br>1. Desmarca la casilla de una incidencia para "perdonarla" y ver cómo se recalcula el total de días a descontar en tiempo real. 
                    <br>2. Usa el bote de basura <i class="bi bi-trash text-danger"></i> para ocultar temporalmente a empleados exentos y limpiar tu pantalla (ideal para capturas de pantalla).
                </div>

                {{-- Formulario de Filtros --}}
                <form method="GET" action="{{ route('asistencia.pre_cierre') }}" class="mb-4 p-3 bg-light rounded border">
                    <div class="row align-items-end g-3 justify-content-center">
                        <div class="col-md-4">
                            <label for="periodo" class="form-label mb-1 fw-bold">Periodo (Quincena):</label>
                            <select name="periodo" id="periodo" class="form-select" required>
                                <option value="">Seleccione una quincena...</option>
                                @foreach ($opcionesPeriodo as $opcion)
                                    <option value="{{ $opcion['valor'] }}" {{ request('periodo') == $opcion['valor'] ? 'selected' : '' }}>
                                        {{ $opcion['texto'] }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label for="id_sucursal" class="form-label mb-1 fw-bold">Sucursal:</label>
                            <select name="id_sucursal" id="id_sucursal" class="form-select" required>
                                <option value="">Seleccione una sucursal...</option>
                                @foreach ($sucursales as $sucursal)
                                    <option value="{{ $sucursal->id_sucursal }}" {{ request('id_sucursal') == $sucursal->id_sucursal ? 'selected' : '' }}>
                                        {{ $sucursal->nombre_sucursal }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="bi bi-calculator"></i> Analizar
                            </button>
                        </div>
                    </div>
                </form>

                {{-- Resultados del Pre-Cierre --}}
                @if(isset($empleadosData) && $empleadosData->isNotEmpty())
                    <h6 class="mb-3">Pre-Cierre para: <span class="text-primary fw-bold">{{ $sucursalSeleccionada->nombre_sucursal ?? '' }}</span></h6>
                    
                    <div class="table-responsive shadow-sm" style="border-radius: 8px;">
                        <table class="table table-hover table-bordered table-sm align-middle mb-0 bg-white">
                            <thead class="table-dark text-center">
                                <tr>
                                    <th style="width: 5%;"></th>
                                    <th style="width: 30%; text-align: left;">Empleado</th>
                                    <th style="width: 45%; text-align: left;">Detalle de Incidencias (Desmarca para perdonar)</th>
                                    <th style="width: 20%;">Total a Descontar</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($empleadosData as $emp)
                                    <tr id="row_{{ $emp['id_empleado'] }}" class="empleado-row">
                                        <td class="text-center">
                                            <button type="button" class="btn btn-sm btn-outline-danger border-0" onclick="ocultarFila({{ $emp['id_empleado'] }})" title="Ocultar de la vista">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </td>
                                        <td style="text-align: left;">
                                            <span class="fw-bold d-block">{{ $emp['nombre'] }}</span>
                                            <span class="badge bg-light text-secondary border mt-1" style="font-size: 0.7em;">
                                                Regla: {{ $emp['regla_retardos'] > 0 ? $emp['regla_retardos'] . ' Retardos = 1 Falta' : 'Sin castigo acum.' }}
                                            </span>
                                        </td>
                                        <td style="text-align: left; background-color: #fafbfc;">
                                            @if(count($emp['detalles']) > 0)
                                                <div class="d-flex flex-wrap gap-2">
                                                    @foreach($emp['detalles'] as $idx => $incidencia)
                                                        @php
                                                            $fechaFormateada = \Carbon\Carbon::parse($incidencia['fecha'])->format('d/m');
                                                            $etiqueta = '';
                                                            $colorClass = '';
                                                            if ($incidencia['tipo'] == 'falta') {
                                                                $etiqueta = "Falta ({$incidencia['penalizacion']}d)";
                                                                $colorClass = "danger";
                                                            } elseif ($incidencia['tipo'] == 'falta_por_retardo_extremo') {
                                                                $etiqueta = "Retardo Extremo ({$incidencia['penalizacion']}d)";
                                                                $colorClass = "danger";
                                                            } elseif ($incidencia['tipo'] == 'medio_dia') {
                                                                $etiqueta = "Medio Día (0.5d)";
                                                                $colorClass = "warning";
                                                            } elseif ($incidencia['tipo'] == 'retardo') {
                                                                $etiqueta = "Retardo";
                                                                $colorClass = "warning-emphasis";
                                                            }
                                                        @endphp
                                                        <div class="form-check form-switch bg-white border rounded px-2 py-1 shadow-sm d-flex align-items-center me-2" style="font-size: 0.85rem;">
                                                            <input class="form-check-input mt-0 me-2 check-incidencia" type="checkbox" checked
                                                                id="chk_{{ $emp['id_empleado'] }}_{{ $idx }}"
                                                                data-tipo="{{ $incidencia['tipo'] }}"
                                                                data-penalizacion="{{ $incidencia['penalizacion'] }}"
                                                                onchange="recalcularFila({{ $emp['id_empleado'] }}, {{ $emp['regla_retardos'] }}, this)">
                                                            <label class="form-check-label text-{{ $colorClass }} fw-bold mb-0 pt-1" for="chk_{{ $emp['id_empleado'] }}_{{ $idx }}" style="cursor: pointer;">
                                                                {{ $fechaFormateada }} - {{ $etiqueta }}
                                                            </label>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            @else
                                                <span class="text-success fw-bold" style="font-size: 0.85rem;"><i class="bi bi-check-circle"></i> Asistencia Perfecta</span>
                                            @endif
                                        </td>
                                        <td class="text-center align-middle">
                                            @if($emp['total_dias_descuento_inicial'] > 0)
                                                <h4 class="mb-0 fw-bold text-danger"><span id="txt_total_{{ $emp['id_empleado'] }}">{{ $emp['total_dias_descuento_inicial'] }}</span></h4>
                                                <small class="text-muted d-block" style="line-height: 1;">Días a descontar</small>
                                            @else
                                                <h4 class="mb-0 fw-bold text-success"><span id="txt_total_{{ $emp['id_empleado'] }}">0</span></h4>
                                                <small class="text-muted d-block" style="line-height: 1;">Días a descontar</small>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @elseif(request()->filled('periodo'))
                    <div class="alert alert-info text-center mt-3">
                        No se encontraron empleados activos para los parámetros seleccionados.
                    </div>
                @endif
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
        function recalcularFila(idEmpleado, reglaRetardos, checkboxToggled) {
            // Efecto visual: Tachado si está desmarcado (perdonado)
            let label = checkboxToggled.nextElementSibling;
            if (checkboxToggled.checked) {
                label.style.textDecoration = 'none';
                label.classList.remove('text-muted');
            } else {
                label.style.textDecoration = 'line-through';
                label.classList.add('text-muted');
            }

            let fila = document.getElementById('row_' + idEmpleado);
            let checkboxes = fila.querySelectorAll('.check-incidencia:checked'); // Solo los que siguen activos (no perdonados)
            
            let totalDias = 0;
            let conteoRetardosNormales = 0;

            checkboxes.forEach(chk => {
                let tipo = chk.getAttribute('data-tipo');
                let penalizacion = parseFloat(chk.getAttribute('data-penalizacion'));

                if (tipo === 'falta' || tipo === 'falta_por_retardo_extremo') {
                    totalDias += penalizacion;
                } else if (tipo === 'medio_dia') {
                    totalDias += 0.5;
                } else if (tipo === 'retardo') {
                    conteoRetardosNormales++;
                }
            });

            // Aplicar regla de retardos a los que quedaron vivos
            if (reglaRetardos > 0) {
                totalDias += Math.floor(conteoRetardosNormales / reglaRetardos);
            }

            // Actualizar la interfaz
            let txtTotal = document.getElementById('txt_total_' + idEmpleado);
            txtTotal.innerText = totalDias % 1 === 0 ? totalDias : totalDias.toFixed(1); // Muestra 1 en lugar de 1.0, pero 1.5 normal

            // Cambiar color si bajó a cero
            let parentH4 = txtTotal.parentElement;
            if (totalDias > 0) {
                parentH4.classList.remove('text-success');
                parentH4.classList.add('text-danger');
            } else {
                parentH4.classList.remove('text-danger');
                parentH4.classList.add('text-success');
            }
        }

        let ocultosCount = 0;

        function ocultarFila(idEmpleado) {
            let fila = document.getElementById('row_' + idEmpleado);
            fila.style.display = 'none';
            
            ocultosCount++;
            document.getElementById('contador-ocultos').innerText = ocultosCount;
            document.getElementById('btn-restaurar-ocultos').classList.remove('d-none');
        }

        function restaurarFilas() {
            document.querySelectorAll('.empleado-row').forEach(row => row.style.display = '');
            ocultosCount = 0;
            document.getElementById('contador-ocultos').innerText = ocultosCount;
            document.getElementById('btn-restaurar-ocultos').classList.add('d-none');
        }
        </script>
    @endpush
</x-app-layout>