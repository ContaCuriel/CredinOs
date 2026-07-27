<x-app-layout>
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

                            {{-- BOTÓN: EXPORTAR A EXCEL (Ya lo tenías) --}}
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
                        <div class="table-responsive">
                            <table class="table table-bordered table-sm">
                                <thead class="table-light">
                                    <tr class="text-center">
                                        <th rowspan="2" class="align-middle">Empleado</th>
                                        <th colspan="4" class="align-middle">Percepciones</th>
                                        <th colspan="7" class="align-middle">Deducciones</th>
                                        <th rowspan="2" class="align-middle">Neto a Pagar</th>
                                    </tr>
                                    <tr class="text-center">
                                        {{-- Percepciones --}}
                                        <th>Sueldo Quinc.</th>
                                        <th>Bono Permanencia</th>
                                        <th>Bono Cumpleaños</th>
                                        <th>Prima Vacacional</th>
                                        {{-- Deducciones --}}
                                        <th>Faltas</th>
                                        <th>Préstamo</th>
                                        <th>Caja Ahorro</th>
                                        <th>Infonavit</th>
                                        <th>ISR</th>
                                        <th>IMSS</th>
                                        <th>Otro</th>
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
                                                <td class="text-end">$ {{ number_format($resultado['sueldo_quincenal'], 2) }}</td>
                                                <td class="text-end">$ {{ number_format($resultado['bono_permanencia'], 2) }}</td>
                                                <td class="text-end">$ {{ number_format($resultado['bono_cumpleanos'], 2) }}</td>
                                                <td class="text-end text-success">$ {{ number_format($resultado['prima_vacacional'], 2) }}</td>
                                                <td class="text-end text-danger">($ {{ number_format($resultado['deduccion_faltas'], 2) }})</td>
                                                <td class="text-end text-danger">($ {{ number_format($resultado['deduccion_prestamo'], 2) }})</td>
                                                <td class="text-end text-danger">($ {{ number_format($resultado['deduccion_caja_ahorro'], 2) }})</td>
                                                <td class="text-end text-danger">($ {{ number_format($resultado['deduccion_infonavit'], 2) }})</td>
                                                <td class="text-end text-danger">($ {{ number_format($resultado['deduccion_isr'], 2) }})</td>
                                                <td class="text-end text-danger">($ {{ number_format($resultado['deduccion_imss'], 2) }})</td>
                                                <td class="text-end text-danger">($ {{ number_format($resultado['deduccion_otro'], 2) }})</td>
                                                <td class="text-end fw-bold fs-6">$ {{ number_format($resultado['neto_a_pagar'], 2) }}</td>
                                            </tr>
                                        @endforeach
                                    @else
                                        <tr>
                                            <td colspan="13" class="text-center text-muted">No se encontraron empleados activos en la sucursal seleccionada para este periodo.</td>
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
                            <label class="form-label">Retardos para aplicar 1 Falta (0 = No penalizar)</label>
                            <input type="number" name="retardos_para_falta" value="3" min="0" class="form-control" required>
                        </div>

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