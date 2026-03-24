<x-app-layout>
    {{-- 1. SECCIÓN DE ESTILOS CSS --}}
    <style>
        /* Contenedor centrado para la tabla de resultados */
        #tabla_resultados_wrapper {
            max-width: 750px; /* Ancho ideal para que no se desparrame */
            margin: 0 auto;   /* Centra el bloque */
        }

        /* Estilo para los inputs editables más elegantes */
        .monto-editable {
            border: 1px solid #dee2e6;
            border-radius: 5px;
            padding: 5px 10px;
            background-color: #fff;
            transition: all 0.2s;
            width: 140px; /* Tamaño fijo para alineación perfecta */
            font-weight: 600;
            color: #2c3e50;
        }

        .monto-editable:focus {
            border-color: #0d6efd;
            outline: none;
            box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.15);
            background-color: #f8fbff;
        }

        /* Filas de encabezado de sección (Percepciones/Deducciones) */
        .row-categoria {
            background-color: #f8f9fa !important;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 0.75rem;
            color: #6c757d;
            letter-spacing: 1px;
        }
        
        .table-hover tbody tr:hover {
            background-color: rgba(0,0,0,.02);
        }

        .align-middle-custom {
            vertical-align: middle !important;
        }
    </style>

    <div class="container-fluid py-4">
        <div class="card shadow-sm">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0 fw-bold"><i class="bi bi-calculator-fill text-primary"></i> Calculadora de Finiquitos y Liquidaciones</h5>
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
        <label for="dias_vacaciones_manuales" class="form-label small fw-bold">
            Días Vacaciones 
            <i class="bi bi-info-circle text-primary ms-1" 
   id="info_vacaciones" 
   style="cursor: pointer;" 
   data-bs-toggle="popover" 
   title="Historial de Vacaciones">
</i>
        </label>
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

                    {{-- Columna Derecha: Botones de Cálculo --}}
                    <div class="col-md-5 border-start">
                        <h6 class="mb-3 fw-bold text-primary">2. Elija el Tipo de Cálculo</h6>
                        <div class="d-grid gap-2">
                            <button type="button" id="btn_calc_dias_laborados" class="btn btn-outline-info fw-bold" disabled>Calcular Días Laborados</button>
                            <button type="button" id="btn_calc_finiquito" class="btn btn-outline-primary fw-bold" disabled>Calcular Finiquito</button>
                            <button type="button" id="btn_calc_liquidacion" class="btn btn-outline-danger fw-bold" disabled>Calcular Liquidación</button>
                        </div>
                        
                        <div id="documento_firmado_container" class="mt-4 p-3 border rounded bg-white shadow-sm" style="display: none;">
                            <h6 class="mb-3 fw-bold text-secondary"><i class="bi bi-cloud-arrow-up"></i> 3. Documento Firmado</h6>
                            <div id="documento_firmado_content"></div>
                        </div>
                    </div>
                </div>

                {{-- Resultados del Cálculo --}}
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

    let salarioDiarioGlobal = 0;

    // Limpieza robusta de números (maneja comas y formatos incorrectos)
    const limpiarNumero = (val) => {
        if (!val) return 0;
        const num = parseFloat(String(val).replace(/,/g, ''));
        return isNaN(num) ? 0 : num;
    };

    function toggleButtons() {
        const habilitar = empleadoSelect.value && fechaFinalInput.value && patronManualSelect.value;
        botonesCalculo.forEach(btn => btn.disabled = !habilitar);
    }

    // 1. CARGA DE POPOVER (VACACIONES)
    empleadoSelect.addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        const empId = this.value;
        fechaIngresoInput.value = selectedOption.dataset.fecha_ingreso || '';
        fechaFinalInput.value = selectedOption.dataset.fecha_baja || '';
        toggleButtons();
        
        if (empId) {
            fetch(`/vacaciones/historial-json/${empId}`)
            .then(res => res.json())
            .then(data => {
                let html = '<div style="font-size: 11px; width: 250px;"><table class="table table-sm mb-0"><thead class="table-dark"><tr><th>Año</th><th>Periodo</th><th>Restantes</th></tr></thead><tbody>';
                data.forEach(row => {
                    const statusClass = row.estado === 'En Curso' ? 'text-primary fw-bold' : '';
                    html += `<tr><td class="text-center">${row.ano_servicio}</td><td>${row.periodo}</td><td class="text-end ${statusClass}">${row.dias_restantes}</td></tr>`;
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

    // 2. EJECUCIÓN CÁLCULO
    botonesCalculo.forEach(btn => {
        btn.addEventListener('click', function() {
            const tipoCalculo = this.id.replace('btn_calc_', '');
            document.getElementById('badge_tipo_calculo').textContent = tipoCalculo.replace('_', ' ').toUpperCase();
            fetch("{{ route('finiquitos.calcular') }}", {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                body: JSON.stringify({
                    id_empleado: empleadoSelect.value,
                    fecha_final: fechaFinalInput.value,
                    tipo_calculo: tipoCalculo,
                    dias_vacaciones_manuales: diasManualesInput.value || 0,
                    gratificacion_monto: gratificacionInput.value || 0
                })
            })
            .then(res => res.json())
            .then(data => {
                // Capturamos el salario diario exacto del controlador
                salarioDiarioGlobal = limpiarNumero(data.salario_diario);
                resultadosContainer.style.display = 'block';
                construirTablaEditable(data);
            });
        });
    });

    // 3. CONSTRUCCIÓN TABLA EDITABLE
    function construirTablaEditable(data) {
        let p = ''; 
        const conceptos = [
            {
                l: `Días Laborados <input type="number" id="input_dias_cantidad" class="form-control d-inline-block text-center" style="width: 70px; height: 28px; font-size: 13px; margin: 0 5px;" value="${data.dias_laborados_dias || 0}"> d`, 
                i: 'dias_laborados_monto', 
                v: data.dias_laborados_monto
            },
            {l: 'Aguinaldo Proporcional', i: 'aguinaldo_monto', v: data.aguinaldo_monto},
            {l: 'Vacaciones', i: 'vacaciones_monto', v: data.vacaciones_monto},
            {l: 'Prima Vacacional', i: 'prima_vacacional_monto', v: data.prima_vacacional_monto},
            {l: 'Indemnización (90 días)', i: 'monto_3_meses', v: data.monto_3_meses},
            {l: 'Prima de Antigüedad', i: 'monto_prima_antiguedad', v: data.monto_prima_antiguedad},
            {l: 'Gratificación Especial', i: 'gratificacion_monto', v: data.gratificacion_monto || 0},
            {l: 'Caja de Ahorro', i: 'caja_ahorro_monto', v: data.caja_ahorro_monto || 0}
        ];
        
        conceptos.forEach(c => {
            if(limpiarNumero(c.v) > 0 || c.i === 'dias_laborados_monto' || c.i === 'gratificacion_monto') {
                p += `<tr><td class="ps-4 align-middle-custom">${c.l}</td><td class="text-end pe-4"><input type="number" step="0.01" id="${c.i}" class="monto-editable text-end monto-p" value="${limpiarNumero(c.v).toFixed(2)}"></td></tr>`;
            }
        });

        tablaResultadosDiv.innerHTML = `
            <div id="tabla_resultados_wrapper">
                <table class="table table-hover border bg-white shadow-sm">
                    <thead class="table-dark"><tr><th class="ps-4">Concepto</th><th class="text-center">Monto Editable ($)</th></tr></thead>
                    <tbody>
                        <tr class="row-categoria"><td colspan="2" class="py-2 ps-3">Percepciones</td></tr>${p}
                        <tr class="row-categoria"><td colspan="2" class="py-2 ps-3">Deducciones</td></tr>
                        <tr><td class="ps-4 align-middle-custom">Deducciones / Préstamos</td><td class="text-end pe-4"><input type="number" step="0.01" id="prestamo_saldo" class="monto-editable text-end monto-d text-danger" value="${limpiarNumero(data.prestamo_saldo).toFixed(2)}"></td></tr>
                        <tr class="fs-5 fw-bold table-primary"><td class="text-end pe-4">Total Neto:</td><td class="text-end pe-4" id="neto_p">$0.00</td></tr>
                    </tbody>
                </table>
            </div>`;
        recalcularTotales();
    }

    // 4. DELEGACIÓN DE EVENTOS (TU SOLUCIÓN REACTIVA DIRECTA)
    document.addEventListener('input', function (e) {
        // A. Si cambian los DÍAS
        if (e.target && e.target.id === 'input_dias_cantidad') {
            const montoInput = document.getElementById('dias_laborados_monto');
            if (montoInput && salarioDiarioGlobal > 0) {
                const dias = limpiarNumero(e.target.value);
                montoInput.value = (dias * salarioDiarioGlobal).toFixed(2);
            }
            // 🔥 RECALCULAR DIRECTO
            recalcularTotales();
            return;
        }

        // B. Si cambia cualquier MONTO editable
        if (e.target && e.target.classList.contains('monto-editable')) {
            recalcularTotales();
        }
    });
    
    function recalcularTotales() {
        let tp = 0; 
        document.querySelectorAll('.monto-p').forEach(i => tp += limpiarNumero(i.value));
        let td = 0; 
        document.querySelectorAll('.monto-d').forEach(i => td += limpiarNumero(i.value));
        
        const netoElement = document.getElementById('neto_p');
        if (netoElement) {
            netoElement.textContent = `$${(tp - td).toLocaleString('es-MX', {minimumFractionDigits:2, maximumFractionDigits:2})}`;
        }
    }

    // 5. EXPORTACIÓN
    function prepararEnvio(format) {
    const idEmp = empleadoSelect.value;
    if (!idEmp) {
        alert('Por favor, selecciona un empleado primero.');
        return;
    }

    // --- CORRECCIÓN PARA AVISO DE TERMINACIÓN ---
    if (format === 'aviso') {
        // Asegúrate de que apunte a la ruta que tienes en web.php
        window.open(`/finiquitos/aviso-terminacion/${idEmp}`, '_blank');
        return;
    }

    const form = document.getElementById('form_export');
    form.method = "POST"; // PDF y Excel siempre deben ser POST para enviar los montos editados
    
    // Definir la ruta según el formato
    if (format === 'pdf') {
        form.action = "{{ route('finiquitos.export.pdf') }}";
    } else if (format === 'excel') {
        form.action = "{{ route('finiquitos.export.excel') }}";
    } else if (format === 'renuncia') {
        form.action = "{{ route('finiquitos.export.renuncia.pdf') }}";
    }

    // Llenar los campos ocultos con lo que hay actualmente en la pantalla
    document.getElementById('export_id_empleado').value = idEmp;
    document.getElementById('export_fecha_final').value = fechaFinalInput.value;
    document.getElementById('export_id_patron').value = patronManualSelect.value;
    document.getElementById('export_dias_vacaciones_manuales').value = diasManualesInput.value || 0;
    
    const btnActive = document.querySelector('button.active');
    document.getElementById('export_tipo_calculo').value = btnActive ? btnActive.id.replace('btn_calc_', '') : 'finiquito';

    // Mapear cada monto de la tabla a los campos hidden del formulario de envío
    const campos = ['dias_laborados_monto', 'aguinaldo_monto', 'vacaciones_monto', 'prima_vacacional_monto', 'monto_3_meses', 'monto_prima_antiguedad', 'caja_ahorro_monto', 'prestamo_saldo', 'gratificacion_monto'];
    
    campos.forEach(id => {
        const inputTabla = document.getElementById(id);
        const inputHidden = document.getElementById('export_' + id);
        if (inputHidden) {
            // Usamos la función limpiarNumero para quitar comas antes de enviar al servidor
            inputHidden.value = inputTabla ? limpiarNumero(inputTabla.value) : 0;
        }
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