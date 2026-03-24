<x-app-layout>
    <div class="container-fluid py-4">
        <div class="card">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0 fw-bold">Calculadora de Finiquitos y Liquidaciones</h5>
            </div>
            <div class="card-body">
                {{-- Alertas de éxito o error --}}
                @if (session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif
                @if ($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div class="row border p-3 rounded bg-light">
                    {{-- Columna Izquierda: Calculadora --}}
                    <div class="col-md-7">
                        <h6 class="mb-3 fw-bold text-primary">1. Seleccione Empleado y Fechas</h6>
                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <label for="id_empleado" class="form-label fw-bold">Empleado <span class="text-danger">*</span></label>
                                <select class="form-select select2" id="id_empleado" required>
                                    <option value="">Seleccione un empleado...</option>
                                    @foreach ($empleados as $empleado)
                                        <option value="{{ $empleado->id_empleado }}" 
                                                data-fecha_ingreso="{{ $empleado->fecha_ingreso?->format('Y-m-d') }}" 
                                                data-fecha_baja="{{ $empleado->fecha_baja?->format('Y-m-d') }}"
                                                data-finiquito-path="{{ $empleado->finiquito_firmado_path }}"
                                                data-finiquito-view-url="{{ route('finiquitos.viewSigned', $empleado) }}"
                                                data-finiquito-upload-url="{{ route('finiquitos.uploadSigned', $empleado) }}">
                                            {{ $empleado->nombre_completo }} - ({{ $empleado->status }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label for="fecha_ingreso" class="form-label small fw-bold">Fecha de Ingreso</label>
                                <input type="date" class="form-control form-control-sm" id="fecha_ingreso" readonly>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="fecha_final" class="form-label small fw-bold">Fecha Final (Baja) <span class="text-danger">*</span></label>
                                <input type="date" class="form-control form-control-sm" id="fecha_final" required>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="dias_vacaciones_manuales" class="form-label small fw-bold">Días Vacaciones</label>
                                <input type="number" class="form-control form-control-sm" id="dias_vacaciones_manuales" step="0.01" placeholder="Ej: 19.54">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="id_patron_manual" class="form-label small fw-bold">Patrón para Documentos <span class="text-danger">*</span></label>
                                <select class="form-select form-select-sm" id="id_patron_manual" required>
                                    <option value="">Seleccione un patrón...</option>
                                    @foreach($patrones as $patron)
                                        <option value="{{ $patron->id_patron }}">{{ $patron->nombre_comercial }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="gratificacion_monto_inicial" class="form-label small fw-bold">Gratificación (Opcional)</label>
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text">$</span>
                                    <input type="number" class="form-control" id="gratificacion_monto_inicial" step="0.01" placeholder="0.00">
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Columna Derecha: Botones de Cálculo y Gestión de Documentos --}}
                    <div class="col-md-5 border-start">
                        <h6 class="mb-3 fw-bold text-primary">2. Elija el Tipo de Cálculo</h6>
                        <div class="d-grid gap-2">
                            <button type="button" id="btn_calc_dias_laborados" class="btn btn-outline-info fw-bold" disabled>Calcular Días Laborados</button>
                            <button type="button" id="btn_calc_finiquito" class="btn btn-outline-primary fw-bold" disabled>Calcular Finiquito</button>
                            <button type="button" id="btn_calc_liquidacion" class="btn btn-outline-danger fw-bold" disabled>Calcular Liquidación</button>
                        </div>
                        
                        {{-- GESTIÓN DE DOCUMENTO FIRMADO --}}
                        <div id="documento_firmado_container" class="mt-4 p-3 border rounded bg-white" style="display: none;">
                            <h6 class="mb-3 fw-bold"><i class="bi bi-cloud-arrow-up"></i> 3. Documento Firmado</h6>
                            <div id="documento_firmado_content"></div>
                        </div>
                    </div>
                </div>

                {{-- Resultados del Cálculo --}}
                <div id="resultados_finiquito_container" class="mt-4" style="display: none;">
                    <hr>
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="mb-0 fw-bold text-success">Resultados del Cálculo (Editable)</h5>
                        <span class="badge bg-secondary" id="badge_tipo_calculo"></span>
                    </div>
                    
                    <div id="tabla_resultados"></div>
                    
                    <div class="text-end mt-4 d-flex justify-content-end gap-2 flex-wrap">
                        <form id="form_export" method="POST" target="_blank">
                            @csrf
                            {{-- Campos ocultos para enviar al controlador --}}
                            <input type="hidden" name="id_empleado" id="export_id_empleado">
                            <input type="hidden" name="fecha_final" id="export_fecha_final">
                            <input type="hidden" name="tipo_calculo" id="export_tipo_calculo">
                            <input type="hidden" name="dias_vacaciones_manuales" id="export_dias_vacaciones_manuales">
                            <input type="hidden" name="id_patron" id="export_id_patron">
                            <input type="hidden" name="dias_laborados_monto" id="export_dias_laborados_monto">
                            <input type="hidden" name="aguinaldo_monto" id="export_aguinaldo_monto">
                            <input type="hidden" name="vacaciones_monto" id="export_vacaciones_monto">
                            <input type="hidden" name="prima_vacacional_monto" id="export_prima_vacacional_monto">
                            <input type="hidden" name="monto_3_meses" id="export_monto_3_meses">
                            <input type="hidden" name="monto_prima_antiguedad" id="export_monto_prima_antiguedad">
                            <input type="hidden" name="caja_ahorro_monto" id="export_caja_ahorro_monto">
                            <input type="hidden" name="prestamo_saldo" id="export_prestamo_saldo">
                            <input type="hidden" name="gratificacion_monto" id="export_gratificacion_monto">
                            
                            {{-- BOTÓN NUEVO: AVISO DE TERMINACIÓN --}}
                            <button type="button" id="btn_export_aviso_terminacion" class="btn btn-dark fw-bold shadow-sm">
                                <i class="bi bi-file-earmark-text"></i> Aviso de Terminación
                            </button>

                            <button type="button" id="btn_export_pdf" class="btn btn-danger fw-bold shadow-sm">
                                <i class="bi bi-file-earmark-pdf"></i> Finiquito PDF
                            </button>
                            
                            <button type="button" id="btn_export_renuncia" class="btn btn-secondary fw-bold shadow-sm">
                                <i class="bi bi-journal-text"></i> Carta Renuncia
                            </button>
                            
                            <button type="button" id="btn_export_excel" class="btn btn-success fw-bold shadow-sm">
                                <i class="bi bi-file-earmark-excel"></i> Excel
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
    document.addEventListener('DOMContentLoaded', function () {
        const empleadoSelect = document.getElementById('id_empleado');
        const fechaIngresoInput = document.getElementById('fecha_ingreso');
        const fechaFinalInput = document.getElementById('fecha_final');
        const diasManualesInput = document.getElementById('dias_vacaciones_manuales');
        const patronManualSelect = document.getElementById('id_patron_manual');
        const gratificacionInput = document.getElementById('gratificacion_monto_inicial');
        const resultadosContainer = document.getElementById('resultados_finiquito_container');
        const tablaResultadosDiv = document.getElementById('tabla_resultados');
        const botonesCalculo = document.querySelectorAll('#btn_calc_dias_laborados, #btn_calc_finiquito, #btn_calc_liquidacion');

        // GESTIÓN DE DOCUMENTOS
        const docContainer = document.getElementById('documento_firmado_container');
        const docContent = document.getElementById('documento_firmado_content');

        function toggleButtons() {
            const habilitar = empleadoSelect.value && fechaFinalInput.value && patronManualSelect.value;
            botonesCalculo.forEach(btn => btn.disabled = !habilitar);
        }

        // Evento al cambiar de empleado
        empleadoSelect.addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            fechaIngresoInput.value = selectedOption.dataset.fecha_ingreso || '';
            fechaFinalInput.value = selectedOption.dataset.fecha_baja || '';
            resultadosContainer.style.display = 'none';
            toggleButtons();

            if (this.value) {
                const finiquitoPath = selectedOption.dataset.finiquitoPath;
                const viewUrl = selectedOption.dataset.finiquitoViewUrl;
                const uploadUrl = selectedOption.dataset.finiquitoUploadUrl;
                let html = '';

                if (finiquitoPath) {
                    html = `<div class="alert alert-success p-2 small d-flex justify-content-between align-items-center">
                                <span><i class="bi bi-check-circle-fill"></i> Documento subido.</span>
                                <a href="${viewUrl}" class="btn btn-xs btn-info py-0 px-2" target="_blank">Ver</a>
                            </div>`;
                } else {
                    html = `<p class="text-muted small">Sin documento firmado.</p>`;
                }

                html += `<form action="${uploadUrl}" method="POST" enctype="multipart/form-data">
                            @csrf
                            <div class="input-group input-group-sm">
                                <input type="file" class="form-control" name="documento_firmado" required>
                                <button class="btn btn-primary" type="submit">Subir</button>
                            </div>
                        </form>`;
                
                docContent.innerHTML = html;
                docContainer.style.display = 'block';
            } else {
                docContainer.style.display = 'none';
            }
        });

        // Eventos de botones de cálculo
        botonesCalculo.forEach(btn => {
            btn.addEventListener('click', function(e) {
                botonesCalculo.forEach(b => b.classList.remove('active', 'btn-info', 'btn-primary', 'btn-danger'));
                botonesCalculo.forEach(b => b.classList.add('btn-outline-' + b.id.split('_')[2]));
                
                this.classList.remove('btn-outline-' + this.id.split('_')[2]);
                this.classList.add('active', 'btn-' + this.id.split('_')[2]);
                
                handleCalculation.call(this, e);
            });
        });

        function handleCalculation(e) {
            const tipoCalculo = this.id.replace('btn_calc_', '');
            document.getElementById('badge_tipo_calculo').textContent = tipoCalculo.toUpperCase();
            
            const payload = {
                id_empleado: empleadoSelect.value,
                fecha_final: fechaFinalInput.value,
                tipo_calculo: tipoCalculo,
                dias_vacaciones_manuales: diasManualesInput.value,
                gratificacion_monto: gratificacionInput.value || 0
            };

            const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            tablaResultadosDiv.innerHTML = `<div class="text-center py-4"><div class="spinner-border text-primary"></div><p>Procesando...</p></div>`;
            resultadosContainer.style.display = 'block';

            fetch("{{ route('finiquitos.calcular') }}", {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': token },
                body: JSON.stringify(payload)
            })
            .then(res => res.ok ? res.json() : Promise.reject(res))
            .then(data => construirTablaEditable(data))
            .catch(() => tablaResultadosDiv.innerHTML = `<div class="alert alert-danger">Error en el servidor.</div>`);
        }

        function construirTablaEditable(data) {
            let p = ''; 
            const conceptos = [
                {l: `Días Laborados (${data.dias_laborados_dias || 0})`, i: 'dias_laborados_monto', v: data.dias_laborados_monto},
                {l: 'Aguinaldo Proporcional', i: 'aguinaldo_monto', v: data.aguinaldo_monto},
                {l: 'Vacaciones', i: 'vacaciones_monto', v: data.vacaciones_monto},
                {l: 'Prima Vacacional', i: 'prima_vacacional_monto', v: data.prima_vacacional_monto},
                {l: 'Indemnización 90 días', i: 'monto_3_meses', v: data.monto_3_meses},
                {l: 'Prima Antigüedad', i: 'monto_prima_antiguedad', v: data.monto_prima_antiguedad},
                {l: 'Gratificación', i: 'gratificacion_monto', v: data.gratificacion_monto || 0},
                {l: 'Caja Ahorro', i: 'caja_ahorro_monto', v: data.caja_ahorro_monto || 0}
            ];
            
            conceptos.forEach(c => {
                if(c.v > 0 || c.i === 'gratificacion_monto') {
                    p += `<tr><td>${c.l}</td><td><input type="number" step="0.01" id="${c.i}" class="form-control form-control-sm text-end monto-p" value="${parseFloat(c.v).toFixed(2)}"></td></tr>`;
                }
            });

            let d = `<tr><td>Saldo de Préstamo</td><td><input type="number" step="0.01" id="prestamo_saldo" class="form-control form-control-sm text-end monto-d text-danger" value="${parseFloat(data.prestamo_saldo || 0).toFixed(2)}"></td></tr>`;

            tablaResultadosDiv.innerHTML = `
                <table class="table table-sm table-bordered bg-white">
                    <thead class="table-light text-center"><tr><th>Concepto</th><th style="width:180px">Monto ($)</th></tr></thead>
                    <tbody>
                        <tr class="table-secondary small fw-bold"><td colspan="2">PERCEPCIONES</td></tr>${p}
                        <tr class="table-secondary small fw-bold"><td colspan="2">DEDUCCIONES</td></tr>${d}
                        <tr class="fs-5 fw-bold table-primary"><td class="text-end">NETO A PAGAR:</td><td class="text-end" id="neto_p">$0.00</td></tr>
                    </tbody>
                </table>`;
            
            recalcularTotales();
            document.querySelectorAll('.monto-p, .monto-d').forEach(i => i.addEventListener('input', recalcularTotales));
        }
        
        function recalcularTotales() {
            let tp = 0; document.querySelectorAll('.monto-p').forEach(i => tp += parseFloat(i.value) || 0);
            let td = 0; document.querySelectorAll('.monto-d').forEach(i => td += parseFloat(i.value) || 0);
            document.getElementById('neto_p').textContent = `$${(tp - td).toLocaleString('es-MX', {minimumFractionDigits:2})}`;
        }

        function prepararEnvio(format) {
    const form = document.getElementById('form_export');
    const idEmp = empleadoSelect.value;

    if (!idEmp) {
        alert('Por favor, seleccione un empleado primero.');
        return;
    }

    if (format === 'aviso') {
        const urlBase = "{{ url('finiquitos/aviso-terminacion') }}";
        form.action = urlBase + "/" + idEmp;
        form.method = "GET";
        form.submit();
        return;
    }

    // Definir la ruta según el botón
    if (format === 'pdf') form.action = "{{ route('finiquitos.export.pdf') }}";
    else if (format === 'renuncia') form.action = "{{ route('finiquitos.export.renuncia.pdf') }}";
    else if (format === 'excel') form.action = "{{ route('finiquitos.export.excel') }}";
    
    form.method = "POST";

    // SINCRONIZACIÓN DE DATOS (AQUÍ ESTABA EL ERROR)
    document.getElementById('export_id_empleado').value = idEmp;
    document.getElementById('export_fecha_final').value = fechaFinalInput.value;
    document.getElementById('export_id_patron').value = patronManualSelect.value;
    
    // IMPORTANTE: Tomar el valor del input de la calculadora y pasarlo al oculto
    const valVacaciones = document.getElementById('dias_vacaciones_manuales').value;
    document.getElementById('export_dias_vacaciones_manuales').value = valVacaciones || 0;

    // Sincronizar el tipo de cálculo (el que esté activo en los botones azules/rojos)
    const btnActivo = document.querySelector('.btn-group .active') || document.querySelector('button.active');
    document.getElementById('export_tipo_calculo').value = btnActivo ? btnActivo.id.replace('btn_calc_', '') : 'finiquito';

    // Sincronizar montos editables de la tabla
    const campos = ['dias_laborados_monto', 'aguinaldo_monto', 'vacaciones_monto', 'prima_vacacional_monto', 'monto_3_meses', 'monto_prima_antiguedad', 'caja_ahorro_monto', 'prestamo_saldo', 'gratificacion_monto'];
    
    campos.forEach(id => {
        const inputTabla = document.getElementById(id);
        const inputOculto = document.getElementById('export_' + id);
        if (inputOculto) {
            inputOculto.value = inputTabla ? inputTabla.value : 0;
        }
    });

    form.submit();
}
        document.getElementById('btn_export_aviso_terminacion').addEventListener('click', () => prepararEnvio('aviso'));
        document.getElementById('btn_export_pdf').addEventListener('click', () => prepararEnvio('pdf'));
        document.getElementById('btn_export_renuncia').addEventListener('click', () => prepararEnvio('renuncia'));
        document.getElementById('btn_export_excel').addEventListener('click', () => prepararEnvio('excel'));

        [fechaFinalInput, patronManualSelect].forEach(i => i.addEventListener('change', toggleButtons));
    });
    </script>
    @endpush
</x-app-layout>