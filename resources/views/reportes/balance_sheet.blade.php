<x-app-layout>
    <div class="card shadow-sm">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Balance General</h5>
            <div class="btn-group" role="group">
                <button id="excel-export-btn" class="btn btn-sm btn-outline-success"><i class="bi bi-file-earmark-excel-fill"></i> Excel</button>
                <button id="pdf-export-btn" class="btn btn-sm btn-outline-danger"><i class="bi bi-file-earmark-pdf-fill"></i> PDF</button>
                <button id="analisis-ia-btn" class="btn btn-sm btn-outline-primary"><i class="bi bi-robot"></i> Analizar</button>
            </div>
        </div>
        <div class="card-body">
            {{-- Formulario --}}
            <form method="GET" action="{{ route('reportes.balance_sheet') }}" id="report-form" class="mb-4 p-3 border rounded bg-light" onsubmit="let btn = document.getElementById('btn-generar'); btn.disabled = true; btn.innerHTML = '<span class=\'spinner-border spinner-border-sm\' role=\'status\' aria-hidden=\'true\'></span> Cargando...';">
    <div class="row g-3 align-items-end">
        <div class="col-md-5">
            <label for="company_name_input" class="form-label">Nombre de Empresa para PDF</label>
            <input type="text" class="form-control" id="company_name_input" name="company_name" value="{{ request('company_name', 'Credintegra SA de CV') }}">
        </div>
        <div class="col-md-5">
            <label for="end_date" class="form-label">Balance al día:</label>
            <input type="date" class="form-control" id="end_date" name="end_date" value="{{ $endDate }}">
        </div>
        <div class="col-md-2">
            <button type="submit" id="btn-generar" class="btn btn-primary w-100">Generar</button>
        </div>
    </div>
</form>

            {{-- Cuerpo del Reporte --}}
            @php
                $totalAssets = $assetAccount ? $assetAccount->getInitialBalance($endDate) : 0;
                $totalLiabilities = $liabilityAccount ? $liabilityAccount->getInitialBalance($endDate) : 0;
                $incomeMovements = $incomeAccount ? $incomeAccount->getMovements('2000-01-01', $endDate) : ['debits' => 0, 'credits' => 0];
                $totalIncome = $incomeMovements['credits'] - $incomeMovements['debits'];
                $totalExpenses = 0;
                if ($expenseAccounts) {
                    foreach($expenseAccounts as $expenseAccount) {
                        $expenseMovements = $expenseAccount->getMovements('2000-01-01', $endDate);
                        $totalExpenses += $expenseMovements['debits'] - $expenseMovements['credits'];
                    }
                }
                $netIncomeForPeriod = $totalIncome - $totalExpenses;
                $equityBalance = $equityAccount ? $equityAccount->getInitialBalance($endDate) : 0;
                $totalEquity = $equityBalance + $netIncomeForPeriod;
                $totalLiabilitiesAndEquity = $totalLiabilities + $totalEquity;
            @endphp
            <div class="mt-4" id="report-data" data-activos="{{ $totalAssets }}" data-pasivos="{{ $totalLiabilities }}" data-capital="{{ $totalEquity }}">
                 <div class="row">
                    {{-- COLUMNA DE ACTIVOS --}}
                    <div class="col-md-6">
                        <h5>Activos</h5>
                        <table class="table table-sm">
                            @if($assetAccount)
                                @include('reportes.partials._balance_sheet_row', ['account' => $assetAccount, 'endDate' => $endDate, 'level' => 0])
                            @endif
                            <tr class="table-group-divider fw-bold bg-light">
                                <td>Total Activos</td>
                                <td class="text-end">${{ number_format($totalAssets, 2) }}</td>
                            </tr>
                        </table>
                    </div>

                    {{-- COLUMNA DE PASIVOS Y CAPITAL --}}
                    <div class="col-md-6">
                        <h5>Pasivos</h5>
                        <table class="table table-sm">
                             @if($liabilityAccount)
                                @include('reportes.partials._balance_sheet_row', ['account' => $liabilityAccount, 'endDate' => $endDate, 'level' => 0])
                             @endif
                             <tr class="table-group-divider fw-bold bg-light">
                                <td>Total Pasivos</td>
                                <td class="text-end">${{ number_format($totalLiabilities, 2) }}</td>
                            </tr>
                        </table>

                        <h5 class="mt-4">Capital Contable</h5>
                        <table class="table table-sm">
                            @if($equityAccount)
                                @include('reportes.partials._balance_sheet_row', ['account' => $equityAccount, 'endDate' => $endDate, 'level' => 0])
                            @endif
                            <tr>
                                <td style="padding-left: 25px;">Utilidad (o Pérdida) del Ejercicio</td>
                                <td class="text-end fw-bold">${{ number_format($netIncomeForPeriod, 2) }}</td>
                            </tr>
                            <tr class="table-group-divider fw-bold bg-light">
                                <td>Total Capital Contable</td>
                                <td class="text-end">${{ number_format($totalEquity, 2) }}</td>
                            </tr>
                        </table>

                        <table class="table table-sm mt-4">
                            <tr class="table-group-divider fw-bold fs-5 bg-secondary text-white">
                                <td>Total Pasivo + Capital</td>
                                <td class="text-end">${{ number_format($totalLiabilitiesAndEquity, 2) }}</td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    {{-- Modal para Análisis de IA --}}
    <div class="modal fade" id="analisisIaModal" tabindex="-1"><div class="modal-dialog modal-lg"><div class="modal-content">
        <div class="modal-header"><h5 class="modal-title"><i class="bi bi-robot"></i> Análisis de Balance General</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
        <div class="modal-body" id="analisis-ia-content"></div>
        <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button></div>
    </div></div></div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Lógica para los botones de exportación
        const excelBtn = document.getElementById('excel-export-btn');
        const pdfBtn = document.getElementById('pdf-export-btn');
        
        function handleExport(e, type) {
            e.preventDefault();
            const companyName = document.getElementById('company_name_input').value;
            const endDate = document.getElementById('end_date').value;
            const baseUrl = (type === 'pdf') 
                ? "{{ route('reportes.export_balance_sheet_pdf') }}"
                : "{{ route('reportes.export_balance_sheet') }}";
            
            const queryParams = new URLSearchParams({ company_name: companyName, end_date: endDate }).toString();
            const finalUrl = `${baseUrl}?${queryParams}`;
            window.open(finalUrl, '_blank');
        }
        
        pdfBtn.addEventListener('click', (e) => handleExport(e, 'pdf'));
        excelBtn.addEventListener('click', (e) => handleExport(e, 'excel'));

        // Lógica para el análisis con IA
        const analisisBtn = document.getElementById('analisis-ia-btn');
        const modalElement = document.getElementById('analisisIaModal');
        const modalContent = document.getElementById('analisis-ia-content');
        const reportDataEl = document.getElementById('report-data');
        const analysisModal = new bootstrap.Modal(modalElement);
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        analisisBtn.addEventListener('click', async function () {
            modalContent.innerHTML = `<div class="text-center"><div class="spinner-border text-primary"></div><p class="mt-2">Analizando...</p></div>`;
            analysisModal.show();
            const data = reportDataEl.dataset;
            
            try {
                const apiUrl = "{{ route('reportes.generate_balance_sheet_analysis') }}";
                const response = await fetch(apiUrl, {
                    method: 'POST',
                    headers: {'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken},
                    body: JSON.stringify({activos: data.activos, pasivos: data.pasivos, capital: data.capital})
                });
                if (!response.ok) throw new Error(`Error en el servidor`);
                const result = await response.json();
                if (result.analysis) {
                    modalContent.innerHTML = `<p>${result.analysis.replace(/\n/g, '<br>')}</p>`;
                } else { throw new Error(result.error || 'Respuesta inesperada.'); }
            } catch (error) {
                modalContent.innerHTML = `<div class="alert alert-danger"><strong>Error:</strong> ${error.message}</div>`;
            }
        });
    });
    </script>
</x-app-layout>
