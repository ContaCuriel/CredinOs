<x-app-layout>
    <style>
        #tabla_resultados_wrapper { max-width: 750px; margin: 0 auto; }
        .row-percepcion { background-color: #f0fff4 !important; }
        .row-deduccion { background-color: #fff5f5 !important; }
        .input-moneda-wrapper { position: relative; display: inline-block; }
        .input-moneda-wrapper::before { content: "$"; position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: #4a5568; font-weight: bold; z-index: 10; pointer-events: none; }
        .monto-editable { border: 1px solid #dee2e6; border-radius: 5px; padding: 5px 10px 5px 25px !important; background-color: #fff; transition: all 0.2s; width: 160px; font-weight: 600; color: #2c3e50; text-align: right; }
        .monto-editable:focus { border-color: #0d6efd; outline: none; box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.15); background-color: #f8fbff; }
        .row-categoria { background-color: #f8f9fa !important; font-weight: bold; text-transform: uppercase; font-size: 0.75rem; color: #6c757d; letter-spacing: 1px; }
        .table td { vertical-align: middle !important; }
        
        /* Estilos para el Editor IA */
        .tox-promotion { display: none !important; } /* Oculta logo de TinyMCE */
    </style>

    <div class="container-fluid py-4">
        <div class="card shadow-sm">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0 fw-bold"><i class="bi bi-calculator-fill text-primary"></i> Calculadora de Finiquitos y Liquidaciones</h5>
            </div>
            <div class="card-body">
                @if (session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
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
                                        @php $sueldoMensual = $empleado->puesto->salario_mensual ?? 0; @endphp
                                        <option value="{{ $empleado->id_empleado }}" 
                                                data-fecha_ingreso="{{ $empleado->fecha_ingreso?->format('Y-m-d') }}" 
                                                data-fecha_baja="{{ $empleado->fecha_baja?->format('Y-m-d') }}"
                                                data-salario="{{ $sueldoMensual }}"
                                                data-id_patron="{{ $empleado->ultimoContrato?->id_patron }}">
                                            {{ $empleado->nombre_completo }} | ${{ number_format($sueldoMensual, 2) }} | ({{ $empleado->status }})
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
                                <label for="dias_vacaciones_manuales" class="form-label small fw-bold">Días Vacaciones <i class="bi bi-info-circle text-primary ms-1" id="info_vacaciones" style="cursor: pointer;"></i></label>
                                <input type="number" class="form-control form-control-sm" id="dias_vacaciones_manuales" step="0.01" placeholder="Ej: 19.54">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="id_patron_manual" class="form-label small fw-bold">Patrón para Documentos <span class="text-danger">*</span></label>
                                <select class="form-select form-select-sm" id="id_patron_manual" required>
                                    <option value="">Seleccione un patrón...</option>
                                    @foreach($patrones as $patron)
                                        <option value="{{ $patron->id_patron }}">{{ $patron->nombre_comercial }} - {{ $patron->razon_social }}</option>
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

                    {{-- Columna Derecha --}}
                    <div class="col-md-5 border-start">
                        <h6 class="mb-3 fw-bold text-primary">2. Elija el Tipo de Cálculo</h6>
                        <div class="d-grid gap-2">
                            <button type="button" id="btn_calc_dias_laborados" class="btn btn-outline-info fw-bold" disabled>Calcular Días Laborados</button>
                            <button type="button" id="btn_calc_finiquito" class="btn btn-outline-primary fw-bold" disabled>Calcular Finiquito</button>
                            <button type="button" id="btn_calc_liquidacion" class="btn btn-outline-danger fw-bold" disabled>Calcular Liquidación</button>
                        </div>
                    </div>
                </div>

                {{-- Resultados --}}
                <div id="resultados_finiquito_container" class="mt-4" style="display: none;">
                    <hr>
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h5 class="mb-0 fw-bold text-success"><i class="bi bi-list-check"></i> Resultados del Cálculo (Editable)</h5>
                        <span class="badge bg-dark px-3 py-2" id="badge_tipo_calculo"></span>
                    </div>
                    
                    <div id="tabla_resultados"></div>
                    
                    <div class="mt-2 text-center" style="max-width: 750px; margin: 0 auto;">
                        <button type="button" class="btn btn-sm btn-outline-success fw-bold shadow-sm" onclick="window.agregarConceptoExtra()">
                            <i class="bi bi-plus-lg"></i> Agregar Concepto Extra
                        </button>
                    </div>

                    <div class="text-end mt-4 d-flex justify-content-end gap-2 flex-wrap">
                        <form id="form_export" method="POST" target="_blank">
                            @csrf
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
                            <input type="hidden" name="conceptos_extras_json" id="export_conceptos_extras">
                            
                            {{-- 🔥 BOTÓN PARA LA IA --}}
                            <button type="button" class="btn btn-info fw-bold text-white shadow-sm" data-bs-toggle="modal" data-bs-target="#modalIA">
                                <i class="bi bi-stars"></i> Redactar con IA
                            </button>

                            <button type="button" id="btn_export_aviso_terminacion" class="btn btn-dark fw-bold">Aviso de Terminación</button>
                            <button type="button" id="btn_export_pdf" class="btn btn-danger fw-bold">Finiquito PDF</button>
                            <button type="button" id="btn_export_renuncia" class="btn btn-secondary fw-bold">Carta Renuncia</button>
                            <button type="button" id="btn_export_excel" class="btn btn-success fw-bold">Excel</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- 🔥 MODAL PARA REDACTAR CON IA 🔥 --}}
    <div class="modal fade" id="modalIA" tabindex="-1" aria-labelledby="modalIALabel" aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog modal-xl modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header bg-info text-white">
                    <h5 class="modal-title fw-bold" id="modalIALabel"><i class="bi bi-stars"></i> Asistente Legal de Inteligencia Artificial</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body bg-light">
                    <div class="row">
                        {{-- PANEL IZQUIERDO: CONTROLES --}}
                        <div class="col-md-4 border-end pe-4">
                            <h6 class="fw-bold text-secondary mb-3">1. Instrucciones a la IA</h6>
                            
                            <label class="form-label small fw-bold">¿Qué documento deseas crear?</label>
                            <select id="ia_tipo_documento" class="form-select form-select-sm mb-3">
                                <option value="Notificación de Rescisión de Contrato">Rescisión de Contrato</option>
                                <option value="Acta Administrativa por Faltas/Abandono">Acta Administrativa (Abandono/Faltas)</option>
                                <option value="Carta de Recomendación Laboral">Carta de Recomendación</option>
                                <option value="Aviso de Término de Contrato (Sin Renovación)">Vencimiento de Contrato</option>
                            </select>

                            <label class="form-label small fw-bold">Contexto / Hechos (Ej. Chat de WhatsApp)</label>
                            <textarea id="ia_contexto_crudo" class="form-control mb-3" rows="8" placeholder="Pega aquí la conversación, los motivos de despido o las instrucciones sueltas. La IA lo transformará en texto legal..."></textarea>

                            <div class="d-grid">
                                <button type="button" id="btn_generar_ia" class="btn btn-primary fw-bold shadow-sm">
                                    <i class="bi bi-magic"></i> Generar Borrador
                                </button>
                            </div>
                        </div>

                        {{-- PANEL DERECHO: EDITOR DE TEXTO --}}
                        <div class="col-md-8 ps-4">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <h6 class="fw-bold text-secondary mb-0">2. Editor Final (Editable)</h6>
                                <span id="ia_status" class="badge bg-secondary">Esperando instrucciones...</span>
                            </div>
                            
                            <!-- Editor TinyMCE -->
                            <textarea id="ia_editor"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer d-flex justify-content-between">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                    
                    <form id="form_export_ia" method="POST" action="{{ route('finiquitos.export.ia.pdf') }}" target="_blank">
                        @csrf
                        <input type="hidden" name="html_content" id="ia_export_html">
                        <input type="hidden" name="id_patron" id="ia_export_patron">
                        <input type="hidden" name="tipo_documento" id="ia_export_tipo">
                        
                        <button type="submit" id="btn_imprimir_ia" class="btn btn-danger fw-bold shadow-sm" disabled>
                            <i class="bi bi-file-earmark-pdf-fill"></i> Descargar PDF Oficial
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <!-- Script de TinyMCE -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/tinymce/6.8.2/tinymce.min.js" referrerpolicy="origin"></script>
    
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Inicializar TinyMCE
            tinymce.init({
                selector: '#ia_editor',
                height: 400,
                menubar: false,
                plugins: 'lists advlist',
                toolbar: 'undo redo | formatselect | bold italic underline | alignleft aligncenter alignright alignjustify | bullist numlist',
                language: 'es_MX'
            });

            // Lógica principal
            const empleadoSelect = document.getElementById('id_empleado');
            const fechaIngresoInput = document.getElementById('fecha_ingreso');
            const fechaFinalInput = document.getElementById('fecha_final');
            const diasManualesInput = document.getElementById('dias_vacaciones_manuales');
            const patronManualSelect = document.getElementById('id_patron_manual');
            const gratificacionInput = document.getElementById('gratificacion_monto_inicial');
            const resultadosContainer = document.getElementById('resultados_finiquito_container');
            const tablaResultadosDiv = document.getElementById('tabla_resultados');
            const botonesCalculo = document.querySelectorAll('#btn_calc_dias_laborados, #btn_calc_finiquito, #btn_calc_liquidacion');

            let salarioDiarioGlobal = 0;

            window.limpiarNumero = (val) => {
                if (!val) return 0;
                const num = parseFloat(String(val).replace(/,/g, ''));
                return isNaN(num) ? 0 : num;
            };

            function toggleButtons() {
                const habilitar = empleadoSelect.value && fechaFinalInput.value && patronManualSelect.value;
                botonesCalculo.forEach(btn => btn.disabled = !habilitar);
            }

            // 🔥 SELECCIÓN DE EMPLEADO Y AUTO-SELECCIÓN DE PATRÓN 🔥
            empleadoSelect.addEventListener('change', function() {
                const opt = this.options[this.selectedIndex];
                fechaIngresoInput.value = opt.dataset.fecha_ingreso || '';
                fechaFinalInput.value = opt.dataset.fecha_baja || '';
                
                // AUTO-SELECCIÓN Y RESALTADO VERDE DEL PATRÓN
                const patronId = opt.dataset.id_patron || '';
                if (patronId) {
                    patronManualSelect.value = patronId;
                    patronManualSelect.style.backgroundColor = '#e8f5e9'; 
                    patronManualSelect.style.borderColor = '#2e7d32';
                    patronManualSelect.style.color = '#1b5e20';
                    patronManualSelect.style.fontWeight = 'bold';
                } else {
                    patronManualSelect.value = '';
                    patronManualSelect.style.backgroundColor = '';
                    patronManualSelect.style.borderColor = '';
                    patronManualSelect.style.color = '';
                    patronManualSelect.style.fontWeight = 'normal';
                }

                toggleButtons();
                
                if (this.value) {
                    fetch(`/vacaciones/historial-json/${this.value}`)
                    .then(res => res.json())
                    .then(data => {
                        let html = '<div style="font-size: 11px; width: 250px;"><table class="table table-sm mb-0"><thead class="table-dark"><tr><th>Año</th><th>Periodo</th><th>Restantes</th></tr></thead><tbody>';
                        data.forEach(row => {
                            html += `<tr><td>${row.ano_servicio}</td><td>${row.periodo}</td><td class="text-end">${row.dias_restantes}</td></tr>`;
                        });
                        const total = data.reduce((acc, curr) => acc + window.limpiarNumero(curr.dias_restantes), 0).toFixed(2);
                        html += `</tbody><tfoot class="table-light fw-bold"><tr><td colspan="2">TOTAL:</td><td class="text-end text-danger">${total}</td></tr></tfoot></table></div>`;
                        const el = document.getElementById('info_vacaciones');
                        const existingPopover = bootstrap.Popover.getInstance(el);
                        if (existingPopover) existingPopover.dispose();
                        new bootstrap.Popover(el, { content: html, html: true, trigger: 'hover focus', container: 'body', placement: 'right', sanitize: false });
                    });
                }
            });

            // Si se cambia el patrón manualmente, se restablece el estilo visual
            patronManualSelect.addEventListener('change', function() {
                this.style.backgroundColor = '';
                this.style.borderColor = '';
                this.style.color = '';
                this.style.fontWeight = 'normal';
                toggleButtons();
            });

            botonesCalculo.forEach(btn => {
                btn.addEventListener('click', function() {
                    const btnOriginalHtml = this.innerHTML;
                    const tipo = this.id.replace('btn_calc_', '');
                    this.disabled = true;
                    this.innerHTML = `<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true" style="display:inline-block"></span> Calculando...`;

                    fetch("{{ route('finiquitos.calcular') }}", {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                        body: JSON.stringify({
                            id_empleado: empleadoSelect.value,
                            fecha_final: fechaFinalInput.value,
                            tipo_calculo: tipo,
                            dias_vacaciones_manuales: diasManualesInput.value || 0,
                            gratificacion_monto: gratificacionInput.value || 0
                        })
                    })
                    .then(res => res.json())
                    .then(data => {
                        salarioDiarioGlobal = window.limpiarNumero(data.salario_diario);
                        document.getElementById('badge_tipo_calculo').textContent = tipo.replace('_', ' ').toUpperCase();
                        resultadosContainer.style.display = 'block';
                        construirTablaEditable(data);
                        this.disabled = false;
                        this.innerHTML = btnOriginalHtml;
                    })
                    .catch(() => { this.disabled = false; this.innerHTML = btnOriginalHtml; });
                });
            });

            function construirTablaEditable(data) {
                let alertHtml = '';
                if (data.info_asistencia && (data.info_asistencia.faltas_directas > 0 || data.info_asistencia.retardos > 0 || data.info_asistencia.medios_dias > 0)) {
                    let info = data.info_asistencia;
                    let botonDescuento = '';
                    if (info.total_dias_descontar > 0) {
                        botonDescuento = `<div class="mt-2"><button type="button" class="btn btn-sm btn-danger fw-bold shadow-sm" onclick="window.aplicarDescuentoFaltas(${info.monto_sugerido_descuento}, this)"><i class="bi bi-scissors"></i> Aplicar descuento por $${window.limpiarNumero(info.monto_sugerido_descuento).toFixed(2)}</button></div>`;
                    } else {
                        botonDescuento = `<div class="mt-2 text-muted small"><i class="bi bi-info-circle"></i> Los retardos registrados no alcanzan a generar descuento según las reglas.</div>`;
                    }
                    alertHtml = `<div class="alert alert-warning alert-dismissible fade show shadow-sm" role="alert" style="max-width: 750px; margin: 0 auto 20px auto;"><strong><i class="bi bi-exclamation-triangle-fill"></i> ¡Atención! Incidencias detectadas</strong><br>El sistema detectó <b>${info.faltas_directas}</b> falta(s), <b>${info.retardos}</b> retardo(s) y <b>${info.medios_dias}</b> medio(s) día(s).<br>Total sugerido a descontar: <b>${info.total_dias_descontar} días</b>.${botonDescuento}<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>`;
                }

                let p = ''; 
                const conceptos = [
                    {l: `Días Laborados <input type="number" id="input_dias_cantidad" class="form-control d-inline-block text-center" style="width: 70px; height: 28px; font-size: 13px; margin: 0 5px;" value="${data.dias_laborados_dias || 0}"> d`, i: 'dias_laborados_monto', v: data.dias_laborados_monto},
                    {l: 'Aguinaldo Proporcional', i: 'aguinaldo_monto', v: data.aguinaldo_monto},
                    {l: 'Vacaciones', i: 'vacaciones_monto', v: data.vacaciones_monto},
                    {l: 'Prima Vacacional', i: 'prima_vacacional_monto', v: data.prima_vacacional_monto},
                    {l: 'Indemnización (90 días)', i: 'monto_3_meses', v: data.monto_3_meses},
                    {l: 'Prima de Antigüedad', i: 'monto_prima_antiguedad', v: data.monto_prima_antiguedad},
                    {l: 'Gratificación Especial', i: 'gratificacion_monto', v: data.gratificacion_monto || 0},
                    {l: 'Caja de Ahorro', i: 'caja_ahorro_monto', v: data.caja_ahorro_monto || 0}
                ];
                
                conceptos.forEach(c => {
                    if(window.limpiarNumero(c.v) > 0 || c.i === 'dias_laborados_monto') {
                        p += `<tr class="row-percepcion"><td class="ps-4">${c.l}</td><td class="text-end pe-4"><div class="input-moneda-wrapper"><input type="number" step="0.01" id="${c.i}" class="monto-editable monto-p" value="${window.limpiarNumero(c.v).toFixed(2)}"></div></td></tr>`;
                    }
                });

                tablaResultadosDiv.innerHTML = alertHtml + `<div id="tabla_resultados_wrapper"><table class="table table-hover border bg-white shadow-sm" id="tabla_calculos_cuerpo"><thead class="table-dark"><tr><th class="ps-4 py-3">Concepto</th><th class="text-center py-3">Monto Editable ($)</th></tr></thead><tbody><tr class="row-categoria"><td colspan="2" class="py-2 ps-3">Percepciones (+)</td></tr>${p}<tr class="row-categoria"><td colspan="2" class="py-2 ps-3">Deducciones (-)</td></tr><tr class="row-deduccion"><td class="ps-4">Deducciones / Préstamos</td><td class="text-end pe-4"><div class="input-moneda-wrapper"><input type="number" step="0.01" id="prestamo_saldo" class="monto-editable monto-d text-danger" value="${window.limpiarNumero(data.prestamo_saldo).toFixed(2)}"></div></td></tr><tr class="fs-5 fw-bold table-primary"><td class="text-end pe-4">Total Neto a Pagar:</td><td class="text-end pe-4" id="neto_p" style="font-size: 1.5rem; color: #0d6efd;">$0.00</td></tr></tbody></table></div>`;
                window.recalcularTotales();
            }

            window.agregarConceptoExtra = function(defaultDesc = '', defaultTipo = 'percepcion', defaultMonto = 0) {
                const tbody = document.querySelector('#tabla_calculos_cuerpo tbody');
                if(!tbody) return;
                const isDeduccion = (defaultTipo === 'deduccion');
                const tr = document.createElement('tr');
                tr.className = 'concepto-extra-row bg-white border-bottom ' + (isDeduccion ? 'row-deduccion' : 'row-percepcion');
                tr.innerHTML = `<td class="ps-4"><input type="text" class="form-control form-control-sm desc-extra shadow-sm mb-1 ${isDeduccion ? 'border-danger text-danger' : 'border-success'}" placeholder="Escribe el concepto" value="${defaultDesc}"></td><td class="text-end pe-4 align-middle"><div class="d-flex justify-content-end align-items-center gap-2"><select class="form-select form-select-sm tipo-extra shadow-sm text-center" style="width: 110px;" onchange="window.recalcularTotales()"><option value="percepcion" ${!isDeduccion ? 'selected' : ''}>Suma (+)</option><option value="deduccion" ${isDeduccion ? 'selected' : ''}>Resta (-)</option></select><div class="input-moneda-wrapper"><input type="number" step="0.01" class="monto-editable monto-extra shadow-sm ${isDeduccion ? 'text-danger' : ''}" value="${defaultMonto > 0 ? defaultMonto.toFixed(2) : '0.00'}" oninput="window.recalcularTotales()"></div><button type="button" class="btn btn-sm btn-outline-danger shadow-sm" onclick="this.closest('tr').remove(); window.recalcularTotales();"><i class="bi bi-trash"></i></button></div></td>`;
                const totalRow = document.getElementById('neto_p').closest('tr');
                tbody.insertBefore(tr, totalRow);
                window.recalcularTotales();
            };

            window.aplicarDescuentoFaltas = function(monto, btnElement) {
                window.agregarConceptoExtra('Descuento por Faltas y Retardos', 'deduccion', monto);
                btnElement.disabled = true;
                btnElement.classList.replace('btn-danger', 'btn-secondary');
                btnElement.innerHTML = '<i class="bi bi-check2-all"></i> Descuento Agregado';
            };

            window.recalcularTotales = function() {
                let tp = 0; document.querySelectorAll('.monto-p').forEach(i => tp += window.limpiarNumero(i.value));
                let td = 0; document.querySelectorAll('.monto-d').forEach(i => td += window.limpiarNumero(i.value));
                document.querySelectorAll('.concepto-extra-row').forEach(row => {
                    let monto = window.limpiarNumero(row.querySelector('.monto-extra').value);
                    let tipo = row.querySelector('.tipo-extra').value;
                    if (tipo === 'percepcion') { tp += monto; row.classList.replace('row-deduccion', 'row-percepcion'); row.querySelector('.monto-extra').classList.remove('text-danger'); } 
                    else { td += monto; row.classList.replace('row-percepcion', 'row-deduccion'); row.querySelector('.monto-extra').classList.add('text-danger'); }
                });
                const neto = document.getElementById('neto_p');
                if (neto) neto.textContent = `$${(tp - td).toLocaleString('es-MX', {minimumFractionDigits:2, maximumFractionDigits:2})}`;
            };

            document.addEventListener('input', function (e) {
                if (e.target.id === 'input_dias_cantidad') {
                    const montoInput = document.getElementById('dias_laborados_monto');
                    if (montoInput && salarioDiarioGlobal > 0) { montoInput.value = (window.limpiarNumero(e.target.value) * salarioDiarioGlobal).toFixed(2); }
                    window.recalcularTotales();
                } else if (e.target.classList.contains('monto-editable')) {
                    window.recalcularTotales();
                }
            });

            function prepararEnvio(format) {
                const idEmp = empleadoSelect.value;
                if (!idEmp) return;
                if (format === 'aviso') { window.open("{{ url('finiquitos/aviso-terminacion') }}/" + idEmp, '_blank'); return; }

                const form = document.getElementById('form_export');
                form.method = "POST";
                if (format === 'pdf') form.action = "{{ route('finiquitos.export.pdf') }}";
                else if (format === 'excel') form.action = "{{ route('finiquitos.export.excel') }}";
                else if (format === 'renuncia') form.action = "{{ route('finiquitos.export.renuncia.pdf') }}";

                document.getElementById('export_id_empleado').value = idEmp;
                document.getElementById('export_fecha_final').value = fechaFinalInput.value;
                document.getElementById('export_id_patron').value = patronManualSelect.value;
                document.getElementById('export_dias_vacaciones_manuales').value = diasManualesInput.value || 0;
                
                const campos = ['dias_laborados_monto', 'aguinaldo_monto', 'vacaciones_monto', 'prima_vacacional_monto', 'monto_3_meses', 'monto_prima_antiguedad', 'caja_ahorro_monto', 'prestamo_saldo', 'gratificacion_monto'];
                campos.forEach(id => {
                    const val = document.getElementById(id);
                    const hidden = document.getElementById('export_' + id);
                    if (hidden) hidden.value = val ? window.limpiarNumero(val.value) : 0;
                });

                const extras = [];
                document.querySelectorAll('.concepto-extra-row').forEach(row => {
                    const desc = row.querySelector('.desc-extra').value;
                    const tipo = row.querySelector('.tipo-extra').value;
                    const monto = window.limpiarNumero(row.querySelector('.monto-extra').value);
                    if(desc.trim() !== '' && monto > 0) { extras.push({ concepto: desc, tipo: tipo, monto: monto }); }
                });
                document.getElementById('export_conceptos_extras').value = JSON.stringify(extras);
                form.submit();
            }

            document.getElementById('btn_export_pdf').addEventListener('click', () => prepararEnvio('pdf'));
            document.getElementById('btn_export_renuncia').addEventListener('click', () => prepararEnvio('renuncia'));
            document.getElementById('btn_export_excel').addEventListener('click', () => prepararEnvio('excel'));
            document.getElementById('btn_export_aviso_terminacion').addEventListener('click', () => prepararEnvio('aviso'));

            [fechaFinalInput, patronManualSelect].forEach(i => i.addEventListener('change', toggleButtons));

            // 🔥 LÓGICA DE LA IA 🔥
            document.getElementById('btn_generar_ia').addEventListener('click', function() {
                const idEmp = empleadoSelect.value;
                const fecFin = fechaFinalInput.value;
                const idPat = patronManualSelect.value;
                const contexto = document.getElementById('ia_contexto_crudo').value;
                const tipoDoc = document.getElementById('ia_tipo_documento').value;

                if (!idEmp || !fecFin || !idPat || contexto.trim().length < 10) {
                    alert("Asegúrate de haber seleccionado Empleado, Fecha Baja, Patrón y escribir un contexto válido (mínimo 10 letras).");
                    return;
                }

                const btn = this;
                const originalHtml = btn.innerHTML;
                btn.innerHTML = `<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true" style="display:inline-block"></span> Pensando...`;
                btn.disabled = true;
                document.getElementById('ia_status').className = 'badge bg-warning text-dark';
                document.getElementById('ia_status').innerText = 'Generando borrador legal...';
                document.getElementById('btn_imprimir_ia').disabled = true;

                fetch("{{ route('finiquitos.redactar.ia') }}", {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                    body: JSON.stringify({
                        id_empleado: idEmp,
                        fecha_final: fecFin,
                        id_patron: idPat,
                        contexto_crudo: contexto,
                        tipo_documento: tipoDoc
                    })
                })
                .then(res => res.json())
                .then(data => {
                    btn.innerHTML = originalHtml;
                    btn.disabled = false;
                    
                    if(data.error) {
                        alert(data.error);
                        document.getElementById('ia_status').className = 'badge bg-danger';
                        document.getElementById('ia_status').innerText = 'Error al generar';
                    } else {
                        // Inyectar el HTML en el editor de TinyMCE
                        tinymce.get('ia_editor').setContent(data.documento_html);
                        
                        document.getElementById('ia_status').className = 'badge bg-success';
                        document.getElementById('ia_status').innerText = '¡Borrador Listo para Revisar!';
                        
                        // Preparar el formulario de impresión
                        document.getElementById('ia_export_patron').value = idPat;
                        document.getElementById('ia_export_tipo').value = tipoDoc;
                        document.getElementById('btn_imprimir_ia').disabled = false;
                    }
                })
                .catch(err => {
                    console.error(err);
                    btn.innerHTML = originalHtml;
                    btn.disabled = false;
                    alert("Ocurrió un error de conexión con la IA.");
                });
            });

            // Antes de enviar el PDF, sacamos el contenido limpio de TinyMCE
            document.getElementById('form_export_ia').addEventListener('submit', function() {
                document.getElementById('ia_export_html').value = tinymce.get('ia_editor').getContent();
            });
        });
    </script>
    @endpush
</x-app-layout>

```