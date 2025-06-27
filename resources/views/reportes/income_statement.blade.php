<x-app-layout>
    <div class="card shadow-sm">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Estado de Resultados</h5>
            <div class="btn-group" role="group">
                <a href="{{ route('reportes.export_income_statement', request()->query()) }}" class="btn btn-sm btn-outline-success"><i class="bi bi-file-earmark-excel-fill"></i> Excel</a>
                <a href="#" id="pdf-export-btn" data-base-url="{{ route('reportes.export_income_statement_pdf') }}" class="btn btn-sm btn-outline-danger">
                    <i class="bi bi-file-earmark-pdf-fill"></i> PDF
                </a>
                <button id="analisis-ia-btn" class="btn btn-sm btn-outline-primary"><i class="bi bi-robot"></i> Generar Análisis</button>
            </div>
        </div>
        <div class="card-body">
            {{-- Formulario de Filtros con todos los campos --}}
            <form method="GET" action="{{ route('reportes.income_statement') }}" id="report-form" class="mb-4 p-3 border rounded bg-light">
                <div class="row g-3 align-items-end">
                    <div class="col-md-6">
                        <label for="company_name_input" class="form-label">Nombre de la Empresa para Reporte</label>
                        <input type="text" class="form-control" id="company_name_input" name="company_name" value="{{ request('company_name', 'Credintegra SA de CV') }}">
                    </div>
                    <div class="col-md-6">
                        <label for="legal_rep_input" class="form-label">Nombre del Representante Legal</label>
                        <input type="text" class="form-control" id="legal_rep_input" name="legal_representative" value="{{ request('legal_representative', 'Socio Accionista') }}">
                    </div>
                    <div class="col-md-4">
                        <label for="sucursal_id" class="form-label">Sucursal</label>
                        <select class="form-select" id="sucursal_id" name="sucursal_id">
                            <option value="">Todas las Sucursales</option>
                            @foreach ($sucursales as $sucursal)
                                <option value="{{ $sucursal->id_sucursal }}" {{ $selectedSucursalId == $sucursal->id_sucursal ? 'selected' : '' }}>
                                    {{ $sucursal->nombre_sucursal }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3"><label for="start_date" class="form-label">Fecha de Inicio</label><input type="date" class="form-control" id="start_date" name="start_date" value="{{ $startDate }}"></div>
                    <div class="col-md-3"><label for="end_date" class="form-label">Fecha de Fin</label><input type="date" class="form-control" id="end_date" name="end_date" value="{{ $endDate }}"></div>
                    <div class="col-md-2"><button type="submit" class="btn btn-primary w-100">Generar</button></div>
                </div>
            </form>

            {{-- Cuerpo del reporte --}}
            <div class="mt-4" id="report-data" data-ingresos="{{ $totalInterest }}" data-gastos="{{ $totalOperationalExpenses }}" data-castigos="{{ $totalUnrecoverable }}" data-utilidad="{{ $netIncome }}" data-inicio="{{ $startDate }}" data-fin="{{ $endDate }}">
                <h4 class="text-center">Estado de Resultados</h4>
                <p class="text-center text-muted">Del {{ \Carbon\Carbon::parse($startDate)->format('d/m/Y') }} al {{ \Carbon\Carbon::parse($endDate)->format('d/m/Y') }}</p>
                <ul class="list-group list-group-flush fs-5">
                    <li class="list-group-item d-flex justify-content-between align-items-center"><strong>(+) Ingresos por Intereses</strong><span class="text-success">${{ number_format($totalInterest, 2) }}</span></li>
                    <li class="list-group-item d-flex justify-content-between align-items-center">(-) Gastos de Operación<span class="text-danger">(${{ number_format($totalOperationalExpenses, 2) }})</span></li>
                    <li class="list-group-item d-flex justify-content-between align-items-center list-group-item-secondary"><strong>= Utilidad Operativa</strong><strong>${{ number_format($operatingProfit, 2) }}</strong></li>
                    <li class="list-group-item d-flex justify-content-between align-items-center">(-) Castigo por Cuentas Incobrables<span class="text-danger">(${{ number_format($totalUnrecoverable, 2) }})</span></li>
                    <li class="list-group-item d-flex justify-content-between align-items-center {{ $netIncome >= 0 ? 'list-group-item-success' : 'list-group-item-danger' }}"><h5>= Utilidad (o Pérdida) Neta</h5><h5 class="fw-bold">${{ number_format($netIncome, 2) }}</h5></li>
                </ul>
            </div>

            {{-- Sección de Firmas --}}
            <div class="row mt-5 pt-5">
                <div class="col-6 text-center"><div style="border-bottom: 1px solid #ccc; margin: 40px 20% 10px;"></div><p class="mb-0"><strong>Representante Legal</strong></p></div>
                <div class="col-6 text-center"><div style="border-bottom: 1px solid #ccc; margin: 40px 20% 10px;"></div><p class="mb-0"><strong>C.P. CARLOS ALBERTO MARTÍNEZ CURIEL</strong></p><p class="text-muted" style="font-size: 0.9em;">Céd. Prof. 14713550</p></div>
            </div>
        </div>
    </div>

    <!-- Modal para el Análisis de IA -->
    <div class="modal fade" id="analisisIaModal" tabindex="-1" aria-labelledby="analisisIaModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg"><div class="modal-content">
            <div class="modal-header"><h5 class="modal-title" id="analisisIaModalLabel"><i class="bi bi-robot"></i> Análisis Financiero con IA</h5><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button></div>
            <div class="modal-body" id="analisis-ia-content"></div>
            <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button></div>
        </div></div>
    </div>
    
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Script para el botón de PDF
            const pdfBtn = document.getElementById('pdf-export-btn');
            
            pdfBtn.addEventListener('click', function(e) {
                e.preventDefault();
                
                // Leemos los valores de los campos directamente al hacer clic
                const companyName = document.getElementById('company_name_input').value;
                const legalRep = document.getElementById('legal_rep_input').value;
                const sucursalId = document.getElementById('sucursal_id').value;
                const startDate = document.getElementById('start_date').value;
                const endDate = document.getElementById('end_date').value;

                // Construimos los parámetros de la URL
                const queryParams = new URLSearchParams({
                    company_name: companyName,
                    legal_representative: legalRep,
                    sucursal_id: sucursalId,
                    start_date: startDate,
                    end_date: endDate
                }).toString();
                
                const finalUrl = `${this.dataset.baseUrl}?${queryParams}`;
                window.open(finalUrl, '_blank');
            });

            // Script para el botón de Análisis con IA (se mantiene igual)
            const analisisBtn = document.getElementById('analisis-ia-btn');
            const modalElement = document.getElementById('analisisIaModal');
            const modalContent = document.getElementById('analisis-ia-content');
            const reportDataEl = document.getElementById('report-data');
            const analysisModal = new bootstrap.Modal(modalElement);
            const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

            analisisBtn.addEventListener('click', async function () {
                modalContent.innerHTML = `<div class="text-center"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Analizando...</span></div><p class="mt-2">Analizando los datos, por favor espere...</p></div>`;
                analysisModal.show();
                const data = reportDataEl.dataset;
                
                try {
                    const apiUrl = "{{ route('reports.generate_analysis') }}";
                    const response = await fetch(apiUrl, {
                        method: 'POST',
                        headers: {'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken},
                        body: JSON.stringify({ingresos: data.ingresos, gastos: data.gastos, castigos: data.castigos, utilidad: data.utilidad, inicio: data.inicio, fin: data.fin})
                    });
                    if (!response.ok) { throw new Error(`Error en el servidor: ${response.statusText}`); }
                    const result = await response.json();
                    if (result.analysis) {
                        modalContent.innerHTML = `<p>${result.analysis.replace(/\n/g, '<br>')}</p>`;
                    } else { throw new Error(result.error || 'La respuesta del servidor no tuvo el formato esperado.'); }
                } catch (error) {
                    modalContent.innerHTML = `<div class="alert alert-danger"><strong>Error:</strong> No se pudo generar el análisis. ${error.message}</div>`;
                }
            });
        });
    </script>
</x-app-layout>