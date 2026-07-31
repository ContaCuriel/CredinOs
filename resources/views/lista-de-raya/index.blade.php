<x-app-layout>
    <style>
        .switch-panel { background-color: #f8f9fa; border: 1px solid #dee2e6; border-radius: 0.5rem; padding: 1rem; }
        .table td { vertical-align: middle !important; }
        .row-disabled { background-color: #fdfdfe; opacity: 0.65; }
    </style>

    <div class="container-fluid py-4">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Generar Lista de Raya (Pre-Nómina)</h5>
                
                {{-- BOTÓN DE CONFIGURACIÓN (ENGRANE) --}}
                <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-toggle="modal" data-bs-target="#configuracionNominaModal">
                    <i class="bi bi-gear-fill"></i> Configuración de Nómina
                </button>
            </div>
            <div class="card-body">
                @if (session('success'))
                    <div class="alert alert-success alert-dismissible fade show shadow-sm border-0" role="alert">
                        <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif
                
                @if (session('error'))
                    <div class="alert alert-danger alert-dismissible fade show shadow-sm border-0" role="alert">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i> {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                {{-- Formulario de Filtros --}}
                <form method="GET" action="{{ route('lista_de_raya.index') }}" class="mb-4 border p-3 rounded">
                    <h6 class="mb-3">Seleccione los Parámetros</h6>
                    <div class="row align-items-end g-3">
                        <div class="col-md-5">
                            <label for="periodo" class="form-label mb-1">Periodo (Quincena): <span class="text-danger">*</span></label>
                            <select name="periodo" id="periodo" class="form-select" required>
                                <option value="">Seleccione una quincena...</option>
                                @foreach ($opcionesPeriodo as $opcion)
                                    <option value="{{ $opcion['valor'] }}" {{ request('periodo') == $opcion['valor'] ? 'selected' : '' }}>
                                        {{ $opcion['texto'] }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-5">
                            <label for="id_sucursal" class="form-label mb-1">Sucursal: <span class="text-danger">*</span></label>
                            <select name="id_sucursal" id="id_sucursal" class="form-select" required>
                                <option value="">Seleccione una sucursal...</option>
                                <option value="todas" {{ request('id_sucursal') == 'todas' ? 'selected' : '' }}>-- Todas las Sucursales (Solo para Exportar) --</option>
                                @foreach ($sucursales as $sucursal)
                                    <option value="{{ $sucursal->id_sucursal }}" {{ request('id_sucursal') == $sucursal->id_sucursal ? 'selected' : '' }}>
                                        {{ $sucursal->nombre_sucursal }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="bi bi-calculator"></i> Generar Reporte
                            </button>
                        </div>
                    </div>
                </form>
                {{-- Fin Filtros --}}

                @if ($resultados)
                    <hr>
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        
                        {{-- AQUÍ ESTÁ EL NUEVO LETRERO DE HISTÓRICO --}}
                        <h6 class="mb-0">
                            Resultados para: <span class="text-primary">{{ $sucursalSeleccionada->nombre_sucursal ?? 'Ninguna sucursal seleccionada' }}</span>
                            @if ($esHistorico ?? false)
                                <span class="badge bg-warning text-dark ms-2"><i class="bi bi-archive"></i> Viendo Histórico Guardado</span>
                            @else
                                <span class="badge bg-info text-dark ms-2"><i class="bi bi-lightning"></i> Cálculo en Vivo</span>
                            @endif
                        </h6>
                        
                        <div class="d-flex gap-2">
                            @if(request('id_sucursal') != 'todas')
                                {{-- Si estamos viendo la foto guardada (Histórico), mostramos el botón para eliminar/recalcular --}}
                                @if($esHistorico ?? false)
                                    <form method="POST" action="{{ route('lista_raya.eliminar_borrador') }}">
                                        @csrf
                                        <input type="hidden" name="periodo" value="{{ request('periodo') }}">
                                        <input type="hidden" name="id_sucursal" value="{{ request('id_sucursal') }}">
                                        <button type="submit" class="btn btn-danger" onclick="return confirm('¿Estás seguro de eliminar este borrador? El sistema volverá a calcular los sueldos en vivo basándose en los datos actuales de los empleados.');">
                                            <i class="bi bi-trash"></i> Eliminar Borrador (Recalcular)
                                        </button>
                                    </form>
                                @else
                                    {{-- Si NO hay foto, mostramos el botón para Guardar --}}
                                    <form method="POST" action="{{ route('lista_raya.guardar_historico') }}">
                                        @csrf
                                        <input type="hidden" name="periodo" value="{{ request('periodo') }}">
                                        <input type="hidden" name="id_sucursal" value="{{ request('id_sucursal') }}">
                                        <button type="submit" class="btn btn-primary" onclick="return confirm('¿Estás seguro de guardar esta nómina como histórico?');">
                                            <i class="bi bi-save"></i> Guardar Histórico
                                        </button>
                                    </form>
                                @endif
                            @endif

                            {{-- BOTÓN: EXPORTAR A EXCEL --}}
                            <form method="GET" action="{{ route('lista_de_raya.exportar') }}">
                                <input type="hidden" name="periodo" value="{{ request('periodo') }}">
                                <input type="hidden" name="id_sucursal" value="{{ request('id_sucursal') }}">
                                <button type="submit" class="btn btn-success" {{ !request()->filled('periodo') || !request()->filled('id_sucursal') ? 'disabled' : '' }}>
                                    <i class="bi bi-file-earmark-excel"></i> Exportar a Excel
                                </button>
                            </form>
                        </div>
                    </div>

                    @if (request('id_sucursal') == 'todas')
                        <div class="alert alert-info text-center">
                            Ha seleccionado "Todas las Sucursales". La vista previa no se muestra para esta opción. <br>
                            Haga clic en <strong>"Exportar a Excel"</strong> para descargar el reporte completo con una pestaña por sucursal.
                        </div>
                    @else
                        
                        {{-- 🔥 LÓGICA DINÁMICA DE OCULTAMIENTO DE COLUMNAS --}}
                        @php
                            $showRetardos = $resultados->sum('retardos_reporte') > 0;
                            $showFaltas = $resultados->sum('faltas_reporte') > 0;
                            
                            $showBonoPerm = $resultados->sum('bono_permanencia') > 0;
                            $showBonoCump = $resultados->sum('bono_cumpleanos') > 0;
                            $showPrimaVac = $resultados->sum('prima_vacacional') > 0;
                            
                            $showDedFaltas = $resultados->sum('deduccion_faltas') > 0;
                            $showPrestamo = $resultados->sum('deduccion_prestamo') > 0;
                            $showPrevision = $resultados->sum('deduccion_prevision') > 0; // 🔥 SE AGREGÓ PREVISIÓN
                            $showCajaAhorro = $resultados->sum('deduccion_caja_ahorro') > 0;
                            $showInfonavit = $resultados->sum('deduccion_infonavit') > 0;
                            $showIsr = $resultados->sum('deduccion_isr') > 0;
                            $showImss = $resultados->sum('deduccion_imss') > 0;
                            $showOtro = $resultados->sum('deduccion_otro') > 0;
                            
                            $colspanPercepciones = 1 + ($showBonoPerm ? 1 : 0) + ($showBonoCump ? 1 : 0) + ($showPrimaVac ? 1 : 0);
                            $colspanDeducciones = ($showDedFaltas ? 1 : 0) + ($showPrestamo ? 1 : 0) + ($showPrevision ? 1 : 0) + ($showCajaAhorro ? 1 : 0) + ($showInfonavit ? 1 : 0) + ($showIsr ? 1 : 0) + ($showImss ? 1 : 0) + ($showOtro ? 1 : 0);
                        @endphp

                        <div class="table-responsive">
                            <table class="table table-bordered table-sm align-middle">
                                <thead class="table-light">
                                    <tr class="text-center">
                                        <th rowspan="2" class="align-middle">Empleado</th>
                                        
                                        @if($showRetardos) <th rowspan="2" class="align-middle text-secondary" style="width: 50px;">R</th> @endif
                                        @if($showFaltas) <th rowspan="2" class="align-middle text-danger" style="width: 50px;">F</th> @endif
                                        
                                        <th colspan="{{ $colspanPercepciones }}" class="align-middle">Percepciones</th>
                                        
                                        @if($colspanDeducciones > 0)
                                            <th colspan="{{ $colspanDeducciones }}" class="align-middle">Deducciones</th>
                                        @endif
                                        
                                        <th rowspan="2" class="align-middle">Neto a Pagar</th>
                                    </tr>
                                    <tr class="text-center">
                                        {{-- Percepciones --}}
                                        <th>Sueldo Quinc.</th>
                                        @if($showBonoPerm) <th>Bono Permanencia</th> @endif
                                        @if($showBonoCump) <th>Bono Cumpleaños</th> @endif
                                        @if($showPrimaVac) <th>Prima Vacacional</th> @endif
                                        
                                        {{-- Deducciones --}}
                                        @if($showDedFaltas) <th>Faltas</th> @endif
                                        @if($showPrestamo) <th>Préstamo</th> @endif
                                        @if($showPrevision) <th>Previsión</th> @endif {{-- 🔥 COLUMNA PREVISIÓN --}}
                                        @if($showCajaAhorro) <th>Caja Ahorro</th> @endif
                                        @if($showInfonavit) <th>Infonavit</th> @endif
                                        @if($showIsr) <th>ISR</th> @endif
                                        @if($showImss) <th>IMSS</th> @endif
                                        @if($showOtro) <th>Otro</th> @endif
                                    </tr>
                                </thead>
                                <tbody>
                                    @if($resultados->isNotEmpty())
                                        @foreach($resultados as $resultado)
                                            <tr>
                                                <td>
                                                    {{ strtoupper($resultado['empleado_nombre']) }}
                                                    <br>
                                                    <small class="text-muted">
                                                        {{ $resultado['puesto'] }}
                                                        @if(isset($resultado['fecha_ingreso']))
                                                            | Ingreso: {{ \Carbon\Carbon::parse($resultado['fecha_ingreso'])->format('d/m/Y') }}
                                                        @endif
                                                    </small>
                                                </td>
                                                
                                                @if($showRetardos)
                                                    <td class="text-center fw-bold text-secondary bg-light">{{ $resultado['retardos_reporte'] ?? 0 }}</td>
                                                @endif
                                                @if($showFaltas)
                                                    <td class="text-center fw-bold text-danger bg-light">{{ $resultado['faltas_reporte'] ?? 0 }}</td>
                                                @endif
                                                
                                                <td class="text-end">$ {{ number_format($resultado['sueldo_quincenal'], 2) }}</td>
                                                @if($showBonoPerm) <td class="text-end">$ {{ number_format($resultado['bono_permanencia'], 2) }}</td> @endif
                                                @if($showBonoCump) <td class="text-end">$ {{ number_format($resultado['bono_cumpleanos'], 2) }}</td> @endif
                                                @if($showPrimaVac) <td class="text-end text-success">$ {{ number_format($resultado['prima_vacacional'], 2) }}</td> @endif
                                                
                                                @if($showDedFaltas) <td class="text-end text-danger">($ {{ number_format($resultado['deduccion_faltas'], 2) }})</td> @endif
                                                @if($showPrestamo) <td class="text-end text-danger">($ {{ number_format($resultado['deduccion_prestamo'], 2) }})</td> @endif
                                                @if($showPrevision) <td class="text-end text-danger">($ {{ number_format($resultado['deduccion_prevision'], 2) }})</td> @endif {{-- 🔥 DATO PREVISIÓN --}}
                                                @if($showCajaAhorro) <td class="text-end text-danger">($ {{ number_format($resultado['deduccion_caja_ahorro'], 2) }})</td> @endif
                                                @if($showInfonavit) <td class="text-end text-danger">($ {{ number_format($resultado['deduccion_infonavit'], 2) }})</td> @endif
                                                @if($showIsr) <td class="text-end text-danger">($ {{ number_format($resultado['deduccion_isr'], 2) }})</td> @endif
                                                @if($showImss) <td class="text-end text-danger">($ {{ number_format($resultado['deduccion_imss'], 2) }})</td> @endif
                                                @if($showOtro) <td class="text-end text-danger">($ {{ number_format($resultado['deduccion_otro'], 2) }})</td> @endif
                                                
                                                <td class="text-end fw-bold fs-6">$ {{ number_format($resultado['neto_a_pagar'], 2) }}</td>
                                            </tr>
                                        @endforeach
                                    @else
                                        <tr>
                                            <td colspan="15" class="text-center text-muted py-4">No se encontraron empleados activos en la sucursal seleccionada para este periodo.</td>
                                        </tr>
                                    @endif
                                </tbody>
                            </table>
                        </div>
                    @endif
                @endif
            </div>
        </div>
    </div>

    {{-- MODAL DE CONFIGURACIÓN BOOTSTRAP --}}
    <div class="modal fade" id="configuracionNominaModal" tabindex="-1" aria-labelledby="configuracionNominaModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="configuracionNominaModalLabel">Configuración del Motor de Nómina</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('lista_raya.configuracion') }}" method="POST">
                    @csrf
                    <div class="modal-body">

                        <div class="mb-3">
                            <label class="form-label">Cálculo de Días de Periodo</label>
                            <select name="metodo_calculo_dias" class="form-select" required>
                                <option value="fijos_15">Fijos 15 Días (Sin importar mes de 28/31)</option>
                                <option value="exactos">Días Exactos del Calendario</option>
                                <option value="factor">Por Factor (Ej. 15.20)</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Regla: Pago del Día 31</label>
                            <select name="pagar_dia_31" class="form-select" required>
                                <option value="nadie">A Nadie (Se absorbe en los 15 días)</option>
                                <option value="todos">A Todos (Se paga el día extra)</option>
                                <option value="nuevos">Solo a Ingresos Nuevos de la Quincena</option>
                            </select>
                        </div>

                        <div class="row">
                            <div class="col-6">
                                <label class="form-label">Descontar 7mo Día</label>
                                <select name="descontar_septimo_dia" class="form-select" required>
                                    <option value="1">Sí, proporcional</option>
                                    <option value="0">No</option>
                                </select>
                            </div>
                            <div class="col-6">
                                <label class="form-label">Redondear Neto</label>
                                <select name="redondear_neto" class="form-select" required>
                                    <option value="1">Sí (Cerrar)</option>
                                    <option value="0">No (Centavos)</option>
                                </select>
                            </div>
                        </div>

                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Guardar Configuración</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>