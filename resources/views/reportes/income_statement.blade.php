<x-app-layout>
    <div class="card shadow-sm">
        <div class="card-header">
            <h5 class="mb-0">Estado de Resultados</h5>
        </div>
        <div class="card-body">
            {{-- Formulario de Filtro por Fechas --}}
            <form method="GET" action="{{ route('reportes.income_statement') }}" class="mb-4 p-3 border rounded bg-light">
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
                        <button type="submit" class="btn btn-primary w-100">Generar Reporte</button>
                    </div>
                </div>
            </form>

            {{-- Cuerpo del Reporte --}}
            <div class="mt-4">
                <h4 class="text-center">Estado de Resultados</h4>
                <p class="text-center text-muted">Del {{ \Carbon\Carbon::parse($startDate)->format('d/m/Y') }} al {{ \Carbon\Carbon::parse($endDate)->format('d/m/Y') }}</p>

                <ul class="list-group list-group-flush fs-5">
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <strong>Ingresos Totales</strong>
                        <span>${{ number_format($totalIncome, 2) }}</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        Gastos de Operación
                        <span class="text-danger">(${{ number_format($totalExpenses, 2) }})</span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center list-group-item-secondary">
                        <strong>Utilidad (o Pérdida) Neta</strong>
                        <strong class="{{ $netIncome >= 0 ? 'text-success' : 'text-danger' }}">
                            ${{ number_format($netIncome, 2) }}
                        </strong>
                    </li>
                </ul>
            </div>

            {{-- Sección de Firmas --}}
            <div class="row mt-5 pt-5">
    {{-- Firma del Representante Legal (sin cambios) --}}
    <div class="col-6 text-center">
        <div style="border-bottom: 1px solid #ccc; margin: 40px 20% 10px;"></div>
        <p class="mb-0"><strong>Nombre del Representante Legal</strong></p>
        <p class="text-muted" style="font-size: 0.9em;">Representante Legal</p>
    </div>
                <div class="col-6 text-center">
        {{-- Línea para la firma --}}
        <div style="border-bottom: 1px solid #ccc; margin: 40px 20% 10px;"></div>
        
        {{-- Nombre del Contador --}}
        <p class="mb-0"><strong>C.P. CARLOS ALBERTO MARTÍNEZ CURIEL</strong></p>
        
        {{-- Título --}}
        <p class="mb-2 text-muted" style="font-size: 0.9em;">Contador Público</p>
        
        {{-- Cédula Profesional --}}
        <p class="text-muted" style="font-size: 0.8em;">Céd. Prof. 14713550</p>
    </div>
    
            </div>
        </div>
    </div>
</x-app-layout>
