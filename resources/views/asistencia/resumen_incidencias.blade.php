<x-app-layout>
    <div class="container-fluid py-4">
        <div class="card">
            <div class="card-header d-flex justify-content-between">
                <h5 class="mb-0">Resumen de Incidencias para Nómina</h5>
                <form action="{{ route('asistencia.resumenIncidencias') }}" method="GET" class="d-flex gap-2">
                    <input type="date" name="fecha_inicio" class="form-control form-control-sm" value="{{ $fechaInicio }}">
                    <input type="date" name="fecha_fin" class="form-control form-control-sm" value="{{ $fechaFin }}">
                    <a href="{{ route('asistencia.exportarPDF', ['fecha_inicio' => $fechaInicio, 'fecha_fin' => $fechaFin]) }}" class="btn btn-danger btn-sm">
    <i class="bi bi-file-earmark-pdf"></i> Exportar PDF
</a>
                    <button type="submit" class="btn btn-primary btn-sm">Filtrar</button>
                </form>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover">
                        <thead class="table-dark">
                            <tr>
                                <th>Empleado</th>
                                <th>Sucursal</th>
                                <th class="text-center">Retardos</th>
                                <th class="text-center">Faltas (Directas)</th>
                                <th class="text-center">F. por Retardos</th>
                                <th class="text-center">Medios Días</th>
                                <th class="text-center bg-danger">Total Faltas a Descontar</th>
                                <th>Acción</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($resumen as $item)
                            <tr>
                                <td>{{ $item['empleado'] }}</td>
                                <td>{{ $item['sucursal'] }}</td>
                                <td class="text-center">{{ $item['retardos_acumulados'] }}</td>
                                <td class="text-center">{{ $item['faltas_directas'] }}</td>
                                <td class="text-center">{{ $item['faltas_por_retardos'] }}</td>
                                <td class="text-center">{{ $item['medios_dias'] }}</td>
                                <td class="text-center fw-bold text-danger">{{ $item['total_faltas_final'] }}</td>
                                <td>
                                    <button class="btn btn-sm btn-outline-info">Justificar</button>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>