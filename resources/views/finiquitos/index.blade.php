<x-app-layout>
    {{-- 1. SECCIÓN DE ESTILOS CSS --}}
    <style>
        #tabla_resultados_wrapper {
            max-width: 750px;
            margin: 0 auto;
        }

        /* Colores para diferenciar percepciones y deducciones */
        .row-percepcion { background-color: #f0fff4 !important; }
        .row-deduccion { background-color: #fff5f5 !important; }

        /* Estilo para los inputs con formato de moneda */
        .input-moneda-wrapper { 
            position: relative; 
            display: inline-block;
        }

        .input-moneda-wrapper::before {
            content: "$";
            position: absolute;
            left: 12px;
            top: 50%;
            transform: translateY(-50%);
            color: #4a5568;
            font-weight: bold;
            z-index: 10;
            pointer-events: none;
        }

        .spinner-border-sm { margin-right: 8px; display: none; }

        .monto-editable {
            border: 1px solid #dee2e6;
            border-radius: 5px;
            padding: 5px 10px 5px 25px !important;
            background-color: #fff;
            transition: all 0.2s;
            width: 160px;
            font-weight: 600;
            color: #2c3e50;
            text-align: right;
        }

        .monto-editable:focus {
            border-color: #0d6efd;
            outline: none;
            box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.15);
            background-color: #f8fbff;
        }

        .row-categoria {
            background-color: #f8f9fa !important;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 0.75rem;
            color: #6c757d;
            letter-spacing: 1px;
        }
        
        .table td { vertical-align: middle !important; }
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
                                                data-salario="{{ $sueldoMensual }}">
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

            let salarioDiarioGlobal = 0;

            const limpiarNumero = (val) => {
                if (!val) return 0;
                const num = parseFloat(String(val).replace(/,/g, ''));
                return isNaN(num) ? 0 : num;
            };

            function toggleButtons() {
                const habilitar = empleadoSelect.value && fechaFinalInput.value && patronManualSelect.value;
                botonesCalculo.forEach(btn => btn.disabled = !habilitar);
            }

            empleadoSelect.addEventListener('change', function() {
                const opt = this.options[this.selectedIndex];
                fechaIngresoInput.value = opt.dataset.fecha_ingreso || '';
                fechaFinalInput.value = opt.dataset.fecha_baja || '';
                toggleButtons();
                
                if (this.value) {
                    fetch(`/vacaciones/historial-json/${this.value}`)
                    .then(res => res.json())
                    .then(data => {
                        let html = '<div style="font-size: 11px; width: 250px;"><table class="table table-sm mb-0"><thead class="table-dark"><tr><th>Año</th><th>Periodo</th><th>Restantes</th></tr></thead><tbody>';
                        data.forEach(row => {
                            html += `<tr><td>${row.ano_servicio}</td><td>${row.periodo}</td><td class="text-end">${row.dias_restantes}</td></tr>`;
                        });
                        const total = data.reduce((acc, curr) => acc + limpiarNumero(curr.dias_restantes), 0).toFixed(2);
                        html += `</tbody><tfoot class="table-light fw-bold"><tr><td colspan="2">TOTAL:</td><td class="text-end text-danger">${total}</td></tr></tfoot></table></div>`;
                        const el = document.getElementById('info_vacaciones');
                        const existingPopover = bootstrap.Popover.getInstance(el);
                        if (existingPopover) existingPopover.dispose();
                        new bootstrap.Popover(el, { content: html, html: true, trigger: 'hover focus', container: 'body', placement: 'right', sanitize: false });
                    });
                }
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
                        salarioDiarioGlobal = limpiarNumero(data.salario_diario);
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
                    if(limpiarNumero(c.v) > 0 || c.i === 'dias_laborados_monto') {
                        p += `<tr class="row-percepcion">
                                <td class="ps-4">${c.l}</td>
                                <td class="text-end pe-4">
                                    <div class="input-moneda-wrapper">
                                        <input type="number" step="0.01" id="${c.i}" class="monto-editable monto-p" value="${limpiarNumero(c.v).toFixed(2)}">
                                    </div>
                                </td>
                              </tr>`;
                    }
                });

                tablaResultadosDiv.innerHTML = `
                    <div id="tabla_resultados_wrapper">
                        <table class="table table-hover border bg-white shadow-sm">
                            <thead class="table-dark"><tr><th class="ps-4 py-3">Concepto</th><th class="text-center py-3">Monto Editable ($)</th></tr></thead>
                            <tbody>
                                <tr class="row-categoria"><td colspan="2" class="py-2 ps-3">Percepciones (+)</td></tr>${p}
                                <tr class="row-categoria"><td colspan="2" class="py-2 ps-3">Deducciones (-)</td></tr>
                                <tr class="row-deduccion">
                                    <td class="ps-4">Deducciones / Préstamos</td>
                                    <td class="text-end pe-4">
                                        <div class="input-moneda-wrapper">
                                            <input type="number" step="0.01" id="prestamo_saldo" class="monto-editable monto-d text-danger" value="${limpiarNumero(data.prestamo_saldo).toFixed(2)}">
                                        </div>
                                    </td>
                                </tr>
                                <tr class="fs-5 fw-bold table-primary"><td class="text-end pe-4">Total Neto a Pagar:</td><td class="text-end pe-4" id="neto_p" style="font-size: 1.5rem; color: #0d6efd;">$0.00</td></tr>
                            </tbody>
                        </table>
                    </div>`;
                recalcularTotales();
            }

            document.addEventListener('input', function (e) {
                if (e.target.id === 'input_dias_cantidad') {
                    const montoInput = document.getElementById('dias_laborados_monto');
                    if (montoInput && salarioDiarioGlobal > 0) {
                        montoInput.value = (limpiarNumero(e.target.value) * salarioDiarioGlobal).toFixed(2);
                    }
                    recalcularTotales();
                } else if (e.target.classList.contains('monto-editable')) {
                    recalcularTotales();
                }
            });
            
            function recalcularTotales() {
                let tp = 0; document.querySelectorAll('.monto-p').forEach(i => tp += limpiarNumero(i.value));
                let td = 0; document.querySelectorAll('.monto-d').forEach(i => td += limpiarNumero(i.value));
                const neto = document.getElementById('neto_p');
                if (neto) neto.textContent = `$${(tp - td).toLocaleString('es-MX', {minimumFractionDigits:2, maximumFractionDigits:2})}`;
            }

            function prepararEnvio(format) {
                const idEmp = empleadoSelect.value;
                if (!idEmp) return;
                
                // --- CORRECCIÓN AVISO (Usando URL absoluta) ---
                if (format === 'aviso') { 
                    window.open("{{ url('finiquitos/aviso-terminacion') }}/" + idEmp, '_blank'); 
                    return; 
                }

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
                    if (hidden) hidden.value = val ? limpiarNumero(val.value) : 0;
                });
                form.submit();
            }

            document.getElementById('btn_export_pdf').addEventListener('click', () => prepararEnvio('pdf'));
            document.getElementById('btn_export_renuncia').addEventListener('click', () => prepararEnvio('renuncia'));
            document.getElementById('btn_export_excel').addEventListener('click', () => prepararEnvio('excel'));
            document.getElementById('btn_export_aviso_terminacion').addEventListener('click', () => prepararEnvio('aviso'));

            [fechaFinalInput, patronManualSelect].forEach(i => i.addEventListener('change', toggleButtons));
        });
    </script>
    @endpush
</x-app-layout>