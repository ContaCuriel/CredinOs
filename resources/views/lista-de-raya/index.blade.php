<x-app-layout>
    <div class="container-fluid py-4">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Generar Lista de Raya (Pre-Nómina)</h5>
            </div>
            <div class="card-body">
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
                        <h6 class="mb-0">Resultados para: <span class="text-primary">{{ $sucursalSeleccionada->nombre_sucursal ?? 'Ninguna sucursal seleccionada' }}</span></h6>
                        {{-- El botón de exportar ahora es un formulario para pasar los mismos filtros --}}
                        <form method="GET" action="{{ route('lista_de_raya.exportar') }}">
                            <input type="hidden" name="periodo" value="{{ request('periodo') }}">
                            <input type="hidden" name="id_sucursal" value="{{ request('id_sucursal') }}">
                            <button type="submit" class="btn btn-success" {{ !request()->filled('periodo') || !request()->filled('id_sucursal') ? 'disabled' : '' }}>
                                <i class="bi bi-file-earmark-excel"></i> Exportar a Excel
                            </button>
                        </form>
                    </div>

                    @if (request('id_sucursal') == 'todas')
                        <div class="alert alert-info text-center">
                            Ha seleccionado "Todas las Sucursales". La vista previa no se muestra para esta opción. <br>
                            Haga clic en **"Exportar a Excel"** para descargar el reporte completo con una pestaña por sucursal.
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
                                                <td>{{ $resultado['empleado_nombre'] }} <br><small class="text-muted">{{ $resultado['puesto'] }}</small></td>
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
</x-app-layout>
