<x-app-layout>
    <div class="container-fluid py-4">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="mb-0">Historial de Vacaciones: {{ $empleado->nombre_completo }}</h5>
                    <p class="text-sm mb-0">
                        Antigüedad: 
                        @if ($empleado->fecha_ingreso)
                            {{ \Carbon\Carbon::parse($empleado->fecha_ingreso)->diffForHumans(null, true, false, 2) }}
                        @else
                            N/A
                        @endif
                    </p>
                </div>
                <div>
                    <a href="{{ route('vacaciones.create', ['id_empleado' => $empleado->id_empleado]) }}" class="btn btn-success me-2">
                        <i class="bi bi-plus-lg"></i> Registrar Vacaciones
                    </a>
                    <a href="{{ route('vacaciones.index') }}" class="btn btn-secondary">
                        <i class="bi bi-arrow-left"></i> Volver a Resumen
                    </a>
                </div>
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

                {{-- Sección de Resumen por Año de Servicio --}}
                <h6 class="mt-3">Resumen por Año de Servicio</h6>
                @if (isset($historialVacacional) && !empty($historialVacacional))
                    <div class="table-responsive mb-4">
                        <table class="table table-sm table-bordered table-striped">
                            <thead class="table-light">
                                <tr>
                                    <th class="text-center">Año de Servicio</th>
                                    <th>Periodo del Año de Servicio</th>
                                    <th class="text-center">Días Devengados (LFT)</th>
                                    <th class="text-center">Días Tomados</th>
                                    <th class="text-center">Días Restantes del Periodo</th>
                                    <th class="text-center">Estado</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($historialVacacional as $item)
                                    <tr>
                                        <td class="text-center">{{ $item['ano_servicio'] }}</td>
                                        <td>{{ $item['periodo_servicio_label'] }}</td>
                                        <td class="text-center">{{ $item['dias_correspondientes'] }}</td>
                                        <td class="text-center">{{ $item['dias_tomados'] }}</td>
                                        <td class="text-center fw-bold {{ $item['dias_restantes'] < 0 ? 'text-danger' : '' }}">
                                            {{ $item['dias_restantes'] }}
                                        </td>
                                        <td class="text-center">
                                            @if ($item['estado'] == 'Completado')
                                                <span class="badge bg-secondary">Completado</span>
                                            @else
                                                <span class="badge bg-primary">En Curso</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr class="table-light">
                                    <td colspan="4" class="text-end fw-bold">TOTAL DE DÍAS RESTANTES A LA FECHA:</td>
                                    <td class="text-center fw-bold fs-5 text-primary">{{ round($totalDiasRestantesGeneral, 2) }}</td>
                                    <td></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                @else
                    <div class="alert alert-info">El empleado aún no tiene historial vacacional para mostrar o no tiene fecha de ingreso registrada.</div>
                @endif

                {{-- Sección de Detalle de Periodos Tomados --}}
                <h6 class="mt-4">Detalle de Periodos Vacacionales Tomados</h6>
                @if (isset($periodosTomados) && $periodosTomados->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered table-striped">
                            <thead class="table-light">
                                <tr>
                                    <th>Fecha Inicio</th>
                                    <th>Fecha Fin</th>
                                    <th class="text-center">Días Tomados</th>
                                    <th class="text-center">Aplicado al Año de Serv. Nº</th>
                                    <th>Comentarios</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($periodosTomados as $periodo)
                                    <tr>
                                        <td>{{ $periodo->fecha_inicio->format('d/m/Y') }}</td>
                                        <td>{{ $periodo->fecha_fin->format('d/m/Y') }}</td>
                                        <td class="text-center">{{ $periodo->dias_tomados }}</td>
                                        <td class="text-center">{{ $periodo->ano_servicio_correspondiente }}</td>
                                        <td>{{ $periodo->comentarios ?: 'N/A' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="alert alert-secondary">
                        Este empleado no tiene periodos vacacionales registrados.
                        <a href="{{ route('vacaciones.create', ['id_empleado' => $empleado->id_empleado]) }}" class="btn btn-sm btn-success ms-2">Registrar Nuevo Periodo</a>
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>