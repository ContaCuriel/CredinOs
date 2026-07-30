<x-app-layout>
    <style>
        .switch-panel { background-color: #f8f9fa; border: 1px solid #dee2e6; border-radius: 0.5rem; padding: 1rem; }
        .table td { vertical-align: middle !important; }
        .row-disabled { background-color: #fdfdfe; opacity: 0.65; }
    </style>

    <div class="container-fluid py-4">
        <div class="card shadow-sm">
            <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
                <h5 class="mb-0 fw-bold text-primary">
                    <i class="bi bi-receipt-cutoff me-2"></i>Módulo de Timbrado de Nómina (CFDI 4.0)
                </h5>
                
                {{-- Botón de Configuración --}}
                <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-toggle="modal" data-bs-target="#configuracionNominaModal">
                    <i class="bi bi-gear-fill me-1"></i> Configuración de Impuestos
                </button>
            </div>
            <div class="card-body">
                @if (session('success'))
                    <div class="alert alert-success alert-dismissible fade show shadow-sm border-0 mb-4" role="alert">
                        <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif
                
                @if (session('error'))
                    <div class="alert alert-danger alert-dismissible fade show shadow-sm border-0 mb-4" role="alert">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i> {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                {{-- Formulario de Filtros y Switches --}}
                <form method="GET" action="{{ route('nomina.timbrado.index') }}" id="form_filtros" class="mb-4 border p-3 rounded bg-light shadow-sm">
                    <h6 class="mb-3 fw-bold text-secondary"><i class="bi bi-funnel-fill me-1"></i> Parámetros de Consulta</h6>
                    <div class="row align-items-end g-3 mb-3">
                        <div class="col-md-5">
                            <label for="periodo" class="form-label mb-1 small fw-bold">Periodo (Quincena): <span class="text-danger">*</span></label>
                            <select name="periodo" id="periodo" class="form-select form-select-sm" required>
                                <option value="">Seleccione una quincena...</option>
                                @foreach ($opcionesPeriodo as $opcion)
                                    <option value="{{ $opcion['valor'] }}" {{ request('periodo') == $opcion['valor'] ? 'selected' : '' }}>
                                        {{ $opcion['texto'] }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-5">
                            <label for="id_sucursal" class="form-label mb-1 small fw-bold">Sucursal: <span class="text-danger">*</span></label>
                            <select name="id_sucursal" id="id_sucursal" class="form-select form-select-sm" required>
                                <option value="">Seleccione una sucursal...</option>
                                <option value="todas" {{ request('id_sucursal') == 'todas' ? 'selected' : '' }}>-- Todas las Sucursales (Masivo) --</option>
                                @foreach ($sucursales as $sucursal)
                                    <option value="{{ $sucursal->id_sucursal }}" {{ request('id_sucursal') == $sucursal->id_sucursal ? 'selected' : '' }}>
                                        {{ $sucursal->nombre_sucursal }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary btn-sm w-100 fw-bold">
                                <i class="bi bi-search me-1"></i> Cargar Datos
                            </button>
                        </div>
                    </div>

                    {{-- Switches de Control Dinámico --}}
                    @if ($resultados && $resultados->isNotEmpty())
                    <hr class="my-3">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-bold mb-2">1. Modo de Trabajo:</label>
                            <div class="btn-group w-100 shadow-sm" role="group">
                                <input type="radio" class="btn-check" name="modo_trabajo" id="modo_interna" value="interna" {{ request('modo_trabajo', 'interna') == 'interna' ? 'checked' : '' }} onchange="document.getElementById('form_filtros').submit();">
                                <label class="btn btn-outline-secondary btn-sm fw-bold" for="modo_interna">
                                    <i class="bi bi-clipboard-data me-1"></i> Lista de Raya (Interna)
                                </label>

                                <input type="radio" class="btn-check" name="modo_trabajo" id="modo_fiscal" value="fiscal" {{ request('modo_trabajo') == 'fiscal' ? 'checked' : '' }} onchange="document.getElementById('form_filtros').submit();">
                                <label class="btn btn-outline-primary btn-sm fw-bold" for="modo_fiscal">
                                    <i class="bi bi-bank me-1"></i> Nómina Fiscal (SAT)
                                </label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold mb-2">2. Base de Cálculo (Impuestos):</label>
                            <div class="btn-group w-100 shadow-sm" role="group">
                                <input type="radio" class="btn-check" name="base_calculo" id="base_bruto" value="bruto" {{ request('base_calculo', 'bruto') == 'bruto' ? 'checked' : '' }} onchange="document.getElementById('form_filtros').submit();" {{ request('modo_trabajo', 'interna') == 'interna' ? 'disabled' : '' }}>
                                <label class="btn btn-outline-info btn-sm fw-bold" for="base_bruto">
                                    <i class="bi bi-sort-down me-1"></i> Sueldo Bruto
                                </label>

                                <input type="radio" class="btn-check" name="base_calculo" id="base_neto" value="neto" {{ request('base_calculo') == 'neto' ? 'checked' : '' }} onchange="document.getElementById('form_filtros').submit();" {{ request('modo_trabajo', 'interna') == 'interna' ? 'disabled' : '' }}>
                                <label class="btn btn-outline-info btn-sm fw-bold" for="base_neto">
                                    <i class="bi bi-sort-up me-1"></i> Sueldo Neto (Gross-Up)
                                </label>
                            </div>
                        </div>
                    </div>
                    @endif
                </form>
                {{-- Fin Filtros --}}

                @if ($resultados)
                    @php 
                        $isFiscal = request('modo_trabajo') == 'fiscal'; 
                        
                        // 🔥 LÓGICA DINÁMICA DE COLUMNAS 🔥
                        $showBonoPerm = $resultados->sum('bono_permanencia') > 0;
                        $showBonoCump = $resultados->sum('bono_cumpleanos') > 0;
                        $showPrimaVac = $resultados->sum('prima_vacacional') > 0;
                        
                        $showDedFaltas = $resultados->sum('deduccion_faltas') > 0;
                        $showPrestamo = $resultados->sum('deduccion_prestamo') > 0;
                        $showCajaAhorro = $resultados->sum('deduccion_caja_ahorro') > 0;
                        $showInfonavit = $resultados->sum('deduccion_infonavit') > 0;
                        
                        // En modo fiscal SIEMPRE mostramos ISR e IMSS, en modo interno solo si hay
                        $showIsr = $isFiscal || $resultados->sum('deduccion_isr') > 0;
                        $showImss = $isFiscal || $resultados->sum('deduccion_imss') > 0;

                        $colspanPercepciones = 1 + ($showBonoPerm ? 1 : 0) + ($showBonoCump ? 1 : 0) + ($showPrimaVac ? 1 : 0);
                        $colspanDeducciones = ($showDedFaltas ? 1 : 0) + ($showPrestamo ? 1 : 0) + ($showCajaAhorro ? 1 : 0) + ($showInfonavit ? 1 : 0) + ($showIsr ? 1 : 0) + ($showImss ? 1 : 0);
                    @endphp

                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h6 class="mb-0">
                            Resultados para: <span class="text-primary fw-bold">{{ $sucursalSeleccionada->nombre_sucursal ?? 'Todas las Sucursales' }}</span>
                            @if ($isFiscal)
                                <span class="badge bg-success text-white ms-2"><i class="bi bi-shield-check me-1"></i> Modo Fiscal (CFDI 4.0)</span>
                            @else
                                <span class="badge bg-secondary text-white ms-2"><i class="bi bi-eye-slash me-1"></i> Vista de Control Interno</span>
                            @endif
                        </h6>
                    </div>

                    <form action="#" method="POST" id="form_timbrado">
                        @csrf
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover table-sm align-middle shadow-sm" style="font-size: 0.85rem;">
                                <thead class="{{ $isFiscal ? 'table-dark' : 'table-light' }}">
                                    <tr class="text-center">
                                        @if($isFiscal)
                                            <th rowspan="2" class="align-middle" style="width: 40px;">
                                                <input class="form-check-input" type="checkbox" id="checkAll">
                                            </th>
                                            <th rowspan="2" class="align-middle" style="width: 110px;">Estatus SAT</th>
                                        @endif
                                        <th rowspan="2" class="align-middle text-start ps-3">Empleado</th>
                                        <th rowspan="2" class="align-middle text-secondary" style="width: 40px;">R</th>
                                        <th rowspan="2" class="align-middle text-danger" style="width: 40px;">F</th>
                                        
                                        <th colspan="{{ $colspanPercepciones }}" class="align-middle">Percepciones</th>
                                        
                                        @if($colspanDeducciones > 0)
                                            <th colspan="{{ $colspanDeducciones }}" class="align-middle">Deducciones</th>
                                        @endif
                                        
                                        <th rowspan="2" class="align-middle bg-primary text-white" style="width: 110px;">Neto a Pagar</th>
                                        
                                        @if($isFiscal)
                                            <th rowspan="2" class="align-middle" style="width: 90px;">Comprobante</th>
                                        @endif
                                    </tr>
                                    <tr class="text-center">
                                        {{-- Percepciones --}}
                                        <th>{{ $isFiscal ? 'Sueldo Bruto' : 'Sueldo Quinc.' }}</th>
                                        @if($showBonoPerm) <th>Bono Perm.</th> @endif
                                        @if($showBonoCump) <th>Bono Cump.</th> @endif
                                        @if($showPrimaVac) <th>Prima Vac.</th> @endif
                                        
                                        {{-- Deducciones --}}
                                        @if($showDedFaltas) <th>Faltas</th> @endif
                                        @if($showPrestamo) <th>Préstamo</th> @endif
                                        @if($showCajaAhorro) <th>Caja Ahorro</th> @endif
                                        @if($showInfonavit) <th>Infonavit</th> @endif
                                        @if($showIsr) <th class="{{ $isFiscal ? 'text-warning' : '' }}">ISR</th> @endif
                                        @if($showImss) <th class="{{ $isFiscal ? 'text-warning' : '' }}">IMSS</th> @endif
                                    </tr>
                                </thead>
                                <tbody>
                                    @if($resultados->isNotEmpty())
                                        @foreach($resultados as $resultado)
                                            @php
                                                $hasRfcCp = !empty($resultado['rfc']) && !empty($resultado['cp_fiscal']);
                                                $alreadyTimbrado = isset($resultado['estado_timbrado']) && $resultado['estado_timbrado'] === 'timbrado';
                                            @endphp
                                            <tr class="{{ $isFiscal && (!$hasRfcCp || $alreadyTimbrado) ? 'row-disabled' : '' }}">
                                                @if($isFiscal)
                                                    <td class="text-center">
                                                        <input class="form-check-input chk-empleado" type="checkbox" name="empleados_timbrar[]" value="{{ $resultado['id_detalle_lista'] }}" {{ (!$hasRfcCp || $alreadyTimbrado) ? 'disabled' : '' }}>
                                                    </td>
                                                    <td class="text-center">
                                                        @if($alreadyTimbrado)
                                                            <span class="badge bg-primary" data-bs-toggle="tooltip" title="UUID: {{ $resultado['uuid_cfdi'] ?? '' }}"><i class="bi bi-patch-check-fill"></i> Timbrado</span>
                                                        @elseif($hasRfcCp)
                                                            <span class="badge bg-success"><i class="bi bi-check-circle"></i> Listo</span>
                                                        @else
                                                            <span class="badge bg-danger" data-bs-toggle="tooltip" title="Falta RFC o CP Fiscal"><i class="bi bi-exclamation-octagon"></i> Sin Datos</span>
                                                        @endif
                                                    </td>
                                                @endif

                                                <td class="ps-3">
                                                    <span class="fw-bold">{{ strtoupper($resultado['empleado_nombre']) }}</span>
                                                    <br>
                                                    <small class="text-muted">
                                                        {{ $resultado['puesto'] }} | 
                                                        <span class="text-primary">{{ $resultado['tipo_contrato'] ?? 'N/A' }}</span>
                                                    </small>
                                                    
                                                    {{-- BOTÓN DEL PANEL FISCAL --}}
                                                    @if($isFiscal && !$alreadyTimbrado)
                                                        <br>
                                                        <button type="button" class="btn btn-sm btn-outline-danger mt-1 py-0 px-2 btn-editar-fiscal" 
                                                            data-bs-toggle="modal" 
                                                            data-bs-target="#modalDatosFiscales"
                                                            data-id="{{ $resultado['id_empleado'] }}"
                                                            data-nombre="{{ $resultado['empleado_nombre'] }}"
                                                            data-nombrefiscal="{{ $resultado['nombre_fiscal'] ?? '' }}"
                                                            data-rfc="{{ $resultado['rfc'] ?? '' }}"
                                                            data-curp="{{ $resultado['curp'] ?? '' }}"
                                                            data-nss="{{ $resultado['nss'] ?? '' }}"
                                                            data-cp="{{ $resultado['cp_fiscal'] ?? '' }}"
                                                            data-regimen="{{ $resultado['regimen_fiscal'] ?? '605' }}">
                                                            <i class="bi bi-person-lines-fill"></i> Completar Datos Fiscales
                                                        </button>
                                                    @endif
                                                    
                                                    @if(!empty($resultado['mensaje_error_sat']))
                                                        <br><small class="text-danger fw-bold"><i class="bi bi-x-circle me-1"></i>{{ $resultado['mensaje_error_sat'] }}</small>
                                                    @endif
                                                </td>
                                                
                                                <td class="text-center fw-bold text-secondary bg-light">{{ $resultado['retardos_reporte'] ?? 0 }}</td>
                                                <td class="text-center fw-bold text-danger bg-light">{{ $resultado['faltas_reporte'] ?? 0 }}</td>
                                                
                                                {{-- Percepciones --}}
                                                <td class="text-end fw-bold">$ {{ number_format($isFiscal ? ($resultado['sueldo_bruto'] ?? 0) : ($resultado['sueldo_quincenal'] ?? 0), 2) }}</td>
                                                @if($showBonoPerm) <td class="text-end text-success">$ {{ number_format($resultado['bono_permanencia'] ?? 0, 2) }}</td> @endif
                                                @if($showBonoCump) <td class="text-end text-success">$ {{ number_format($resultado['bono_cumpleanos'] ?? 0, 2) }}</td> @endif
                                                @if($showPrimaVac) <td class="text-end text-success">$ {{ number_format($resultado['prima_vacacional'] ?? 0, 2) }}</td> @endif
                                                
                                                {{-- Deducciones --}}
                                                @if($showDedFaltas) <td class="text-end text-danger">($ {{ number_format($resultado['deduccion_faltas'] ?? 0, 2) }})</td> @endif
                                                @if($showPrestamo) <td class="text-end text-danger">($ {{ number_format($resultado['deduccion_prestamo'] ?? 0, 2) }})</td> @endif
                                                @if($showCajaAhorro) <td class="text-end text-danger">($ {{ number_format($resultado['deduccion_caja_ahorro'] ?? 0, 2) }})</td> @endif
                                                @if($showInfonavit) <td class="text-end text-danger">($ {{ number_format($resultado['deduccion_infonavit'] ?? 0, 2) }})</td> @endif
                                                @if($showIsr) <td class="text-end text-danger fw-bold">($ {{ number_format($resultado['deduccion_isr'] ?? 0, 2) }})</td> @endif
                                                @if($showImss) <td class="text-end text-danger fw-bold">($ {{ number_format($resultado['deduccion_imss'] ?? 0, 2) }})</td> @endif
                                                
                                                {{-- Neto --}}
                                                <td class="text-end fw-bold fs-6 text-primary">$ {{ number_format($resultado['neto_a_pagar'] ?? 0, 2) }}</td>

                                                @if($isFiscal)
                                                    <td class="text-center">
                                                        @if($alreadyTimbrado)
                                                            <div class="btn-group btn-group-sm" role="group">
                                                                @if(!empty($resultado['xml_path']))
                                                                    <a href="{{ Storage::url($resultado['xml_path']) }}" class="btn btn-outline-secondary" target="_blank" title="Descargar XML"><i class="bi bi-filetype-xml"></i></a>
                                                                @endif
                                                                @if(!empty($resultado['pdf_path']))
                                                                    <a href="{{ Storage::url($resultado['pdf_path']) }}" class="btn btn-outline-danger" target="_blank" title="Descargar PDF"><i class="bi bi-filetype-pdf"></i></a>
                                                                @endif
                                                            </div>
                                                        @else
                                                            <span class="text-muted small">-</span>
                                                        @endif
                                                    </td>
                                                @endif
                                            </tr>
                                        @endforeach
                                    @else
                                        <tr>
                                            <td colspan="20" class="text-center text-muted py-4">No se encontraron registros para los filtros seleccionados.</td>
                                        </tr>
                                    @endif
                                </tbody>
                            </table>
                        </div>

                        {{-- Botonera de Acción Masiva (Solo visible en Modo Fiscal) --}}
                        @if($isFiscal && $resultados->isNotEmpty())
                            <div class="d-flex justify-content-between align-items-center mt-3 bg-light p-3 border rounded shadow-sm">
                                <span class="small text-muted">
                                    <i class="bi bi-info-circle me-1"></i> Seleccione únicamente los empleados cuyos datos fiscales estén validados.
                                </span>
                                <div class="d-flex gap-2">
                                    <button type="submit" class="btn btn-success fw-bold px-4" id="btn_timbrar">
                                        <i class="bi bi-lightning-fill me-1"></i> Timbrar Seleccionados
                                    </button>
                                </div>
                            </div>
                        @endif
                    </form>
                @endif
            </div>
        </div>
    </div>

    {{-- Modal de Datos Fiscales del Empleado --}}
    <div class="modal fade" id="modalDatosFiscales" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white py-2">
                    <h5 class="modal-title fs-6"><i class="bi bi-person-badge me-2"></i>Datos Fiscales (CFDI 4.0)</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="formDatosFiscales">
                    @csrf
                    <div class="modal-body">
                        <div class="alert alert-info py-2 small">
                            <i class="bi bi-info-circle-fill me-1"></i> Estos datos deben coincidir exactamente con la Constancia de Situación Fiscal (CSF) del trabajador.
                        </div>
                        
                        <input type="hidden" id="fiscal_empleado_id" name="empleado_id">
                        
                        <div class="mb-2">
                            <label class="form-label fw-bold small mb-1">Nombre Exacto del SAT (Sin Régimen Capital)</label>
                            <input type="text" class="form-control form-control-sm text-uppercase" id="fiscal_nombre" name="nombre_fiscal" required placeholder="EJ: JUAN PEREZ LOPEZ">
                        </div>
                        
                        <div class="row g-2 mb-2">
                            <div class="col-6">
                                <label class="form-label fw-bold small mb-1">RFC</label>
                                <input type="text" class="form-control form-control-sm text-uppercase" id="fiscal_rfc" name="rfc" maxlength="13" required>
                            </div>
                            <div class="col-6">
                                <label class="form-label fw-bold small mb-1">CURP</label>
                                <input type="text" class="form-control form-control-sm text-uppercase" id="fiscal_curp" name="curp" maxlength="18" required>
                            </div>
                        </div>

                        <div class="row g-2 mb-2">
                            <div class="col-6">
                                <label class="form-label fw-bold small mb-1">Número de Seguro Social</label>
                                <input type="text" class="form-control form-control-sm" id="fiscal_nss" name="nss" maxlength="11" required>
                            </div>
                            <div class="col-6">
                                <label class="form-label fw-bold small mb-1">C.P. Fiscal (SAT)</label>
                                <input type="text" class="form-control form-control-sm" id="fiscal_cp" name="cp_fiscal" maxlength="5" required>
                            </div>
                        </div>

                        <div class="mb-2">
                            <label class="form-label fw-bold small mb-1">Régimen Fiscal</label>
                            <select class="form-select form-select-sm" id="fiscal_regimen" name="regimen_fiscal" required>
                                <option value="605">605 - Sueldos y Salarios e Ingresos Asimilados</option>
                                <option value="612">612 - Personas Físicas con Actividades Empresariales y Profesionales</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer py-1">
                        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary btn-sm" id="btnGuardarFiscal">Guardar y Validar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Modal de Configuración --}}
    <div class="modal fade" id="configuracionNominaModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-dark text-white py-2">
                    <h5 class="modal-title fs-6">Configuración de Impuestos</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p class="text-muted small mb-3">Ajustes generales para la emisión de CFDI de Nómina.</p>
                    <div class="mb-3">
                        <label class="form-label fw-bold small">Régimen para Honorarios / Asimilados</label>
                        <select class="form-select form-select-sm">
                            <option value="10">Retención Fija 10% ISR</option>
                            <option value="tablas">Aplicar Tablas de Asimilados (Art. 96)</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer py-1">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cerrar</button>
                    <button type="button" class="btn btn-primary btn-sm">Guardar Cambios</button>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Checkbox seleccionar todos
            const checkAll = document.getElementById('checkAll');
            const checkboxes = document.querySelectorAll('.chk-empleado:not([disabled])');
            
            if(checkAll) {
                checkAll.addEventListener('change', function() {
                    checkboxes.forEach(chk => chk.checked = this.checked);
                });
            }

            // Tooltips Bootstrap
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });

            // --- LÓGICA DEL PANEL FISCAL ---
            
            // 1. Llenar el modal con los datos del empleado al abrirlo
            const modalFiscal = document.getElementById('modalDatosFiscales');
            if (modalFiscal) {
                modalFiscal.addEventListener('show.bs.modal', function (event) {
                    const button = event.relatedTarget; // Botón que abrió el modal
                    
                    // Extraemos la información de los data-attributes
                    const id = button.getAttribute('data-id');
                    const nombreRh = button.getAttribute('data-nombre');
                    const nombreFiscal = button.getAttribute('data-nombrefiscal');
                    
                    // Si no tiene nombre fiscal aún, le sugerimos el de RH
                    document.getElementById('fiscal_empleado_id').value = id;
                    document.getElementById('fiscal_nombre').value = nombreFiscal ? nombreFiscal : nombreRh.toUpperCase();
                    document.getElementById('fiscal_rfc').value = button.getAttribute('data-rfc');
                    document.getElementById('fiscal_curp').value = button.getAttribute('data-curp');
                    document.getElementById('fiscal_nss').value = button.getAttribute('data-nss');
                    document.getElementById('fiscal_cp').value = button.getAttribute('data-cp');
                    document.getElementById('fiscal_regimen').value = button.getAttribute('data-regimen') || '605';
                });
            }

            // 2. Enviar los datos por AJAX
            const formDatosFiscales = document.getElementById('formDatosFiscales');
            if(formDatosFiscales) {
                formDatosFiscales.addEventListener('submit', function(e) {
                    e.preventDefault();
                    
                    const empleadoId = document.getElementById('fiscal_empleado_id').value;
                    const btnSubmit = document.getElementById('btnGuardarFiscal');
                    const formData = new FormData(this);
                    
                    // Cambiar estado del botón a cargando
                    btnSubmit.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Guardando...';
                    btnSubmit.disabled = true;

                    // Petición AJAX (Fetch API) a la ruta que creamos
                    fetch(`/empleados/${empleadoId}/datos-fiscales`, {
                        method: 'POST',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value
                        },
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            // Recargamos la página para que Laravel evalúe de nuevo el status y quite el botón rojo
                            window.location.reload();
                        } else {
                            alert('Ocurrió un error al guardar. Verifique los datos.');
                            btnSubmit.innerHTML = 'Guardar y Validar';
                            btnSubmit.disabled = false;
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('Error de conexión. Intente nuevamente.');
                        btnSubmit.innerHTML = 'Guardar y Validar';
                        btnSubmit.disabled = false;
                    });
                });
            }
        });
    </script>
    @endpush
</x-app-layout>