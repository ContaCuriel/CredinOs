<x-app-layout>
    <div class="card shadow-sm">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Balanza de Comprobación</h5>
            {{-- ===== NUEVO BOTÓN DE EXPORTACIÓN ===== --}}
            <a href="{{ route('reportes.export_trial_balance', ['start_date' => $startDate, 'end_date' => $endDate]) }}" class="btn btn-sm btn-outline-success">
                <i class="bi bi-file-earmark-excel-fill"></i> Exportar a Excel
            </a>
            {{-- ======================================= --}}
        </div>
        <div class="card-body">
            {{-- Formulario de Filtro por Fechas --}}
            {{-- CAMBIO CLAVE: Apuntamos al nuevo nombre de la ruta --}}
            <form method="GET" action="{{ route('reportes.balanza_comprobacion') }}" id="report-form" class="mb-4 p-3 border rounded bg-light" onsubmit="let btn = document.getElementById('btn-generar'); btn.disabled = true; btn.innerHTML = '<span class=\'spinner-border spinner-border-sm\' role=\'status\' aria-hidden=\'true\'></span> Cargando...';">
    <div class="row g-3 align-items-end">
        <div class="col-md-4">
            <label for="start_date" class="form-label">Fecha de Inicio</label>
            <input type="date" class="form-control" id="start_date" name="start_date" value="{{ $startDate }}">
        </div>
        <div class="col-md-4">
            <label for="end_date" class="form-label">Fecha de Fin</label>
            <input type="date" class="form-control" id="end_date" name="end_date" value="{{ $endDate }}">
        </div>
        <div class="col-md-4">
            <button type="submit" id="btn-generar" class="btn btn-primary w-100">Generar Reporte</button>
        </div>
    </div>
</form>

            {{-- Tabla de Resultados --}}
            <div class="table-responsive">
                <table class="table table-sm table-bordered">
                    <thead class="table-light text-center">
                        <tr>
                            <th rowspan="2" class="align-middle">Cuenta</th>
                            <th rowspan="2" class="align-middle">Nombre</th>
                            <th colspan="2">Saldos Iniciales</th>
                            <th colspan="2">Movimientos del Periodo</th>
                            <th colspan="2">Saldos Finales</th>
                        </tr>
                        <tr>
                            <th>Deudor</th>
                            <th>Acreedor</th>
                            <th>Debe</th>
                            <th>Haber</th>
                            <th>Deudor</th>
                            <th>Acreedor</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($accounts as $account)
                            {{-- CAMBIO CLAVE: Apuntamos a la carpeta 'reportes' --}}
                            @include('reportes.partials._trial_balance_row', ['account' => $account, 'level' => 0, 'startDate' => $startDate, 'endDate' => $endDate])
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
