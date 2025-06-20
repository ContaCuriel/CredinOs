<x-app-layout>
    <div class="container-fluid py-4">
        <div class="card">
            <div class="card-header"><h5 class="mb-0">Calculadora de Finiquitos y Liquidaciones</h5></div>
            <div class="card-body">
                <div class="row border p-3 rounded">
                    <div class="col-md-7">
                        <h6 class="mb-3">1. Seleccione Empleado y Fechas</h6>
                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <label for="id_empleado" class="form-label">Empleado <span class="text-danger">*</span></label>
                                <select class="form-select" id="id_empleado" required>
                                    <option value="">Seleccione un empleado...</option>
                                    @foreach ($empleados as $empleado)
                                        <option value="{{ $empleado->id_empleado }}" data-fecha_ingreso="{{ $empleado->fecha_ingreso?->format('Y-m-d') }}" data-fecha_baja="{{ $empleado->fecha_baja?->format('Y-m-d') }}">
                                            {{ $empleado->nombre_completo }} - ({{ $empleado->status }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-4 mb-3"><label for="fecha_ingreso" class="form-label">Fecha de Ingreso</label><input type="date" class="form-control" id="fecha_ingreso" readonly></div>
                            <div class="col-md-4 mb-3"><label for="fecha_final" class="form-label">Fecha Final (Baja) <span class="text-danger">*</span></label><input type="date" class="form-control" id="fecha_final" required></div>
                            <div class="col-md-4"><div class="form-group"><label for="dias_vacaciones_manuales">Días Vacaciones</label><input type="number" class="form-control" id="dias_vacaciones_manuales" step="0.01" placeholder="Ej: 19.54" required></div></div>
                        </div>
                        <div class="row">
                             <div class="col-md-12">
                                 <div class="form-group">
                                     <label for="id_patron_manual">Patrón para Documentos <span class="text-danger">*</span></label>
                                     <select class="form-select" id="id_patron_manual" required>
                                         <option value="">Seleccione un patrón...</option>
                                         @foreach($patrones as $patron)
                                             <option value="{{ $patron->id_patron }}">{{ $patron->nombre_comercial }}</option>
                                         @endforeach
                                     </select>
                                 </div>
                             </div>
                        </div>
                    </div>

                    <div class="col-md-5 border-start">
                        <h6 class="mb-3">2. Elija el Tipo de Cálculo</h6>
                        <div class="d-grid gap-2">
                            <button type="button" id="btn_calc_dias_laborados" class="btn btn-info" disabled>Calcular Días Laborados</button>
                            <button type="button" id="btn_calc_finiquito" class="btn btn-primary" disabled>Calcular Finiquito</button>
                            <button type="button" id="btn_calc_liquidacion" class="btn btn-danger" disabled>Calcular Liquidación</button>
                        </div>
                    </div>
                </div>

                <div id="resultados_finiquito_container" class="mt-4" style="display: none;">
                    <hr>
                    <h5 class="mb-3">Resultados del Cálculo (Editable)</h5>
                    <div id="tabla_resultados"></div>
                    
                    <div class="text-end mt-3 d-flex justify-content-end gap-2">
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
                            
                            <!-- ============== BOTONES DE EXPORTACIÓN MODIFICADOS ============== -->
                            <button type="button" id="btn_export_pdf" class="btn btn-danger"><i class="bi bi-file-earmark-pdf"></i> Generar Finiquito PDF</button>
                            
                            <!-- ============== NUEVO BOTÓN PARA CARTA DE RENUNCIA ============== -->
                            <button type="button" id="btn_export_renuncia" class="btn btn-secondary"><i class="bi bi-journal-text"></i> Generar Carta Renuncia</button>
                            
                            <button type="button" id="btn_export_excel" class="btn btn-success"><i class="bi bi-file-earmark-excel"></i> Exportar a Excel</button>
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
        const resultadosContainer = document.getElementById('resultados_finiquito_container');
        const tablaResultadosDiv = document.getElementById('tabla_resultados');
        const botonesCalculo = document.querySelectorAll('#btn_calc_dias_laborados, #btn_calc_finiquito, #btn_calc_liquidacion');

        function toggleButtons() {
            const habilitar = empleadoSelect.value && fechaFinalInput.value && diasManualesInput.value && patronManualSelect.value;
            botonesCalculo.forEach(btn => btn.disabled = !habilitar);
        }
        
        empleadoSelect.addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            fechaIngresoInput.value = selectedOption.dataset.fecha_ingreso || '';
            fechaFinalInput.value = selectedOption.dataset.fecha_baja || '';
            resultadosContainer.style.display = 'none';
            toggleButtons();
        });

        [fechaFinalInput, diasManualesInput, patronManualSelect].forEach(input => input.addEventListener('change', toggleButtons));

        function handleCalculation(e) {
            e.preventDefault();
            const tipoCalculo = this.id.replace('btn_calc_', '');
            
            const idEmpleado = empleadoSelect.value;
            const fechaFinal = fechaFinalInput.value;
            const diasManuales = diasManualesInput.value;
            const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

            tablaResultadosDiv.innerHTML = `<div class="text-center"><div class="spinner-border text-primary" role="status"></div><p>Calculando...</p></div>`;
            resultadosContainer.style.display = 'block';

            fetch("{{ route('finiquitos.calcular') }}", {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': token },
                body: JSON.stringify({ id_empleado: idEmpleado, fecha_final: fechaFinal, tipo_calculo: tipoCalculo, dias_vacaciones_manuales: diasManuales })
            })
            .then(response => {
                if (!response.ok) return response.json().then(error => Promise.reject(error));
                return response.json();
            })
            .then(data => {
                construirTablaEditable(data);
            })
            .catch(error => {
                let errorMessage = 'Ocurrió un error. Revise la consola para más detalles.';
                if (error && error.errors) {
                    errorMessage = `<ul>${Object.values(error.errors).map(e => `<li>${e[0]}</li>`).join('')}</ul>`;
                } else if (error && error.mensaje) {
                    errorMessage = error.mensaje;
                }
                tablaResultadosDiv.innerHTML = `<div class="alert alert-danger">${errorMessage}</div>`;
            });
        }

        function construirTablaEditable(data) {
            let percepcionesHtml = '';
            let deduccionesHtml = '';
            const percepciones = [
                {label: `Días Laborados (${data.dias_laborados_dias || 0} días)`, id: 'dias_laborados_monto', value: data.dias_laborados_monto},
                {label: 'Aguinaldo Proporcional', id: 'aguinaldo_monto', value: data.aguinaldo_monto},
                {label: 'Vacaciones', id: 'vacaciones_monto', value: data.vacaciones_monto},
                {label: 'Prima Vacacional', id: 'prima_vacacional_monto', value: data.prima_vacacional_monto},
                {label: 'Indemnización (3 Meses)', id: 'monto_3_meses', value: data.monto_3_meses},
                {label: 'Prima de Antigüedad', id: 'monto_prima_antiguedad', value: data.monto_prima_antiguedad},
                {label: 'Fondo de Caja de Ahorro', id: 'caja_ahorro_monto', value: data.caja_ahorro_monto}
            ];
            
            percepciones.forEach(item => {
                if(item.value > 0) {
                    percepcionesHtml += `<tr><td>${item.label}</td><td class="text-end"><input type="number" step="0.01" id="${item.id}" class="form-control form-control-sm text-end monto-percepcion" value="${parseFloat(item.value).toFixed(2)}"></td></tr>`;
                }
            });

            if (data.prestamo_saldo > 0) {
                deduccionesHtml = `<tr><td>Saldo de Préstamo</td><td class="text-end"><input type="number" step="0.01" id="prestamo_saldo" class="form-control form-control-sm text-end monto-deduccion" value="${parseFloat(data.prestamo_saldo).toFixed(2)}"></td></tr>`;
            }

            let tablaHtml = `
                <table class="table table-sm table-bordered">
                    <thead><tr><th>Concepto</th><th class="text-end" style="width: 200px;">Monto (Editable)</th></tr></thead>
                    <tbody>
                        <tr class="table-group-divider"><td colspan="2" class="fw-bold">PERCEPCIONES</td></tr>
                        ${percepcionesHtml}
                        <tr class="fw-bold"><td class="text-end">TOTAL DE PERCEPCIONES</td><td class="text-end" id="total_percepciones">$0.00</td></tr>
                        <tr class="table-group-divider"><td colspan="2" class="fw-bold">DEDUCCIONES</td></tr>
                        ${deduccionesHtml}
                        <tr class="fw-bold"><td class="text-end">TOTAL DE DEDUCCIONES</td><td class="text-end text-danger" id="total_deducciones">($0.00)</td></tr>
                        <tr class="table-group-divider"></tr>
                        <tr class="fw-bold fs-5 table-light"><td class="text-end">NETO A PAGAR</td><td class="text-end" id="neto_a_pagar">$0.00</td></tr>
                    </tbody>
                </table>
            `;
            tablaResultadosDiv.innerHTML = tablaHtml;
            recalcularTotales();
            
            document.querySelectorAll('.monto-percepcion, .monto-deduccion').forEach(input => {
                input.addEventListener('input', recalcularTotales);
            });
        }
        
        function recalcularTotales() {
            let totalPercepciones = 0;
            document.querySelectorAll('.monto-percepcion').forEach(input => {
                totalPercepciones += parseFloat(input.value) || 0;
            });
            
            let totalDeducciones = 0;
            document.querySelectorAll('.monto-deduccion').forEach(input => {
                totalDeducciones += parseFloat(input.value) || 0;
            });

            const netoAPagar = totalPercepciones - totalDeducciones;

            document.getElementById('total_percepciones').textContent = `$${totalPercepciones.toFixed(2)}`;
            document.getElementById('total_deducciones').textContent = `($${totalDeducciones.toFixed(2)})`;
            document.getElementById('neto_a_pagar').textContent = `$${netoAPagar.toFixed(2)}`;
        }

        // ================== FUNCIÓN DE EXPORTACIÓN MODIFICADA ==================
        function prepararYEnviarFormulario(format) {
            const form = document.getElementById('form_export');
            
            // Asignar la URL correcta según el formato solicitado
            if (format === 'pdf_finiquito') {
                form.action = "{{ route('finiquitos.export.pdf') }}";
            } else if (format === 'excel') {
                form.action = "{{ route('finiquitos.export.excel') }}";
            } else if (format === 'pdf_renuncia') {
                form.action = "{{ route('finiquitos.export.renuncia.pdf') }}";
            } else {
                return; // No hacer nada si el formato es desconocido
            }

            // Rellenar los campos ocultos del formulario
            document.getElementById('export_id_empleado').value = empleadoSelect.value;
            document.getElementById('export_fecha_final').value = fechaFinalInput.value;
            document.getElementById('export_dias_vacaciones_manuales').value = diasManualesInput.value;
            document.getElementById('export_tipo_calculo').value = document.querySelector('.btn-info.active, .btn-primary.active, .btn-danger.active')?.id.replace('btn_calc_', '') || '';
            document.getElementById('export_id_patron').value = patronManualSelect.value;

            const campos = ['dias_laborados_monto', 'aguinaldo_monto', 'vacaciones_monto', 'prima_vacacional_monto', 'monto_3_meses', 'monto_prima_antiguedad', 'caja_ahorro_monto', 'prestamo_saldo'];
            campos.forEach(id => {
                const inputElement = document.getElementById(id);
                const hiddenInputElement = document.getElementById(`export_${id}`);
                if (inputElement && hiddenInputElement) {
                    hiddenInputElement.value = inputElement.value;
                } else if(hiddenInputElement) {
                    hiddenInputElement.value = 0;
                }
            });
            
            form.submit();
        }
        
        // ================== NUEVOS EVENT LISTENERS ==================
        document.getElementById('btn_export_pdf').addEventListener('click', () => prepararYEnviarFormulario('pdf_finiquito'));
        document.getElementById('btn_export_renuncia').addEventListener('click', () => prepararYEnviarFormulario('pdf_renuncia'));
        document.getElementById('btn_export_excel').addEventListener('click', () => prepararYEnviarFormulario('excel'));
        
        botonesCalculo.forEach(btn => btn.addEventListener('click', function(e) {
            botonesCalculo.forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            handleCalculation.call(this, e);
        }));

        toggleButtons();
    });
    </script>
    @endpush
</x-app-layout>
