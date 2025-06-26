{{-- resources/views/reportes/gastos_por_sucursal.blade.php --}}

<x-app-layout>
    <div class="container-fluid py-4">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
    <h5 class="mb-0">Reporte Comparativo de Gastos por Sucursal</h5>
    {{-- Este es el botón funcional --}}
    <a href="{{ route('reportes.gastos.sucursal.exportar', request()->query()) }}" class="btn btn-outline-success">
        <i class="bi bi-file-earmark-excel"></i> Exportar a Excel
    </a>
</div>
            <div class="card-body">
                <form method="GET" action="{{ route('reportes.gastos.sucursal') }}" class="mb-4">
                    <div class="card shadow-sm">
                        <div class="card-body">
                            <div class="row g-3 align-items-end">
                                <div class="col-md-4">
                                    <label for="fecha_inicio" class="form-label">Desde</label>
                                    <input type="date" name="fecha_inicio" id="fecha_inicio" class="form-control form-control-sm" value="{{ $fechaInicio }}">
                                </div>
                                <div class="col-md-4">
                                    <label for="fecha_fin" class="form-label">Hasta</label>
                                    <input type="date" name="fecha_fin" id="fecha_fin" class="form-control form-control-sm" value="{{ $fechaFin }}">
                                </div>
                                <div class="col-md-4">
                                    <button type="submit" class="btn btn-primary btn-sm w-100">Generar Reporte</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>

                <div class="table-responsive">
                    <table class="table table-bordered table-striped table-hover align-middle">
                        <thead class="table-dark">
                            <tr>
                                <th style="min-width: 200px;">Categoría de Gasto</th>
                                @foreach($sucursales as $sucursal)
                                    <th class="text-end" style="min-width: 150px;">{{ $sucursal->nombre_sucursal }}</th>
                                @endforeach
                                <th class="text-end" style="min-width: 160px;">TOTAL POR CATEGORÍA</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($categorias as $categoria)
                                <tr>
                                    <th>{{ $categoria->nombre }}</th>
                                    @php $totalFila = 0; @endphp
                                    @foreach($sucursales as $sucursal)
                                        @php
                                            $monto = $datosPivoteados[$categoria->nombre][$sucursal->nombre_sucursal] ?? 0;
                                            $totalFila += $monto;
                                        @endphp
                                        <td class="text-end">${{ number_format($monto, 2) }}</td>
                                    @endforeach
                                    <th class="text-end bg-light">${{ number_format($totalFila, 2) }}</th>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="{{ count($sucursales) + 2 }}" class="text-center py-4">
                                        No se encontraron gastos para el periodo seleccionado.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                        <tfoot class="table-dark">
                            <tr>
                                <th>TOTAL POR SUCURSAL</th>
                                @php $granTotal = 0; @endphp
                                @foreach($sucursales as $sucursal)
                                    @php
                                        $totalColumna = $datosPivoteados->sum(function($gastosPorCategoria) use ($sucursal) {
                                            return $gastosPorCategoria[$sucursal->nombre_sucursal] ?? 0;
                                        });
                                        $granTotal += $totalColumna;
                                    @endphp
                                    <th class="text-end">${{ number_format($totalColumna, 2) }}</th>
                                @endforeach
                                <th class="text-end">${{ number_format($granTotal, 2) }}</th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>