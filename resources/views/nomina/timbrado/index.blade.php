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
                    @php $isFiscal = request('modo_trabajo') == 'fiscal'; @endphp

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
                            <table class="table table-bordered table-hover table-sm align-middle shadow-sm">
                                <thead class="{{ $isFiscal ? 'table-dark' : 'table-light' }}">
                                    <tr class="text-center">
                                        @if($isFiscal)
                                            <th rowspan="2" class="align-middle" style="width: 40px;">
                                                <input class="form-check-input" type="checkbox" id="checkAll">
                                            </th>
                                            <th rowspan="2" class="align-middle" style="width: 110px;">Estatus SAT</th>
                                        @endif
                                        <th rowspan="2" class="align-middle text-start ps-3">Empleado</th>
                                        <th rowspan="2" class="align-middle text-secondary" style="width: 45px;">R</th>
                                        <th rowspan="2" class="align-middle text-danger" style="width: 45px;">F</th>
                                        <th colspan="2" class="align-middle">Percepciones</th>
                                        <th colspan="{{ $isFiscal ? 3 : 1 }}" class="align-middle">Deducciones</th>
                                        <th rowspan="2" class="align-middle bg-primary text-white" style="width: 120px;">Neto a Pagar</th>
                                        @if($isFiscal)
                                            <th rowspan="2" class="align-middle" style="width: 100px;">Comprobante</th>
                                        @endif
                                    </tr>
                                    <tr class="text-center">
                                        {{-- Percepciones --}}
                                        <th>{{ $isFiscal ? 'Sueldo Bruto' : 'Sueldo Quinc.' }}</th>
                                        <th>Otras Percep.</th>
                                        
                                        {{-- Deducciones --}}
                                        <th>Faltas/Otros</th>
                                        @if($isFiscal)
                                            <th class="text-warning">ISR</th>
                                            <th class="text-warning">IMSS</th>
                                        @endif
                                    </tr>
                                </thead>
                                <tbody>
                                    @if($resultados->isNotEmpty())
                                        @foreach($resultados as $resultado)
                                            @php
                                                $hasRfcCp = !empty($resultado['rfc']) && !empty($resultado['cp_fiscal']);
                                                $alreadyTimbrado = $resultado['estado_timbrado'] === 'timbrado';
                                            @endphp
                                            <tr class="{{ $isFiscal && (!$hasRfcCp || $alreadyTimbrado) ? 'row-disabled' : '' }}">
                                                @if($isFiscal)
                                                    <td class="text-center">
                                                        <input class="form-check-input chk-empleado" type="checkbox" name="empleados_timbrar[]" value="{{ $resultado['id_detalle_lista'] }}" {{ (!$hasRfcCp || $alreadyTimbrado) ? 'disabled' : '' }}>
                                                    </td>
                                                    <td class="text-center">
                                                        @if($alreadyTimbrado)
                                                            <span class="badge bg-primary" data-bs-toggle="tooltip" title="UUID: {{ $resultado['uuid_cfdi'] }}"><i class="bi bi-patch-check-fill"></i> Timbrado</span>
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
                                                        <span class="text-primary">{{ $resultado['tipo_contrato'] }}</span>
                                                    </small>
                                                    @if(!empty($resultado['mensaje_error_sat']))
                                                        <br><small class="text-danger fw-bold"><i class="bi bi-x-circle me-1"></i>{{ $resultado['mensaje_error_sat'] }}</small>
                                                    @endif
                                                </td>
                                                
                                                <td class="text-center fw-bold text-secondary bg-light">{{ $resultado['retardos_reporte'] }}</td>
                                                <td class="text-center fw-bold text-danger bg-light">{{ $resultado['faltas_reporte'] }}</td>
                                                
                                                <td class="text-end">$ {{ number_format($isFiscal ? $resultado['sueldo_bruto'] : $resultado['sueldo_quincenal'], 2) }}</td>
                                                <td class="text-end">$ 0.00</td>
                                                
                                                <td class="text-end text-danger">($ {{ number_format($resultado['deduccion_faltas'], 2) }})</td>
                                                @if($isFiscal)
                                                    <td class="text-end text-danger fw-bold">($ {{ number_format($resultado['deduccion_isr'], 2) }})</td>
                                                    <td class="text-end text-danger fw-bold">($ {{ number_format($resultado['deduccion_imss'], 2) }})</td>
                                                @endif
                                                
                                                <td class="text-end fw-bold fs-6 text-primary">$ {{ number_format($resultado['neto_a_pagar'], 2) }}</td>

                                                @if($isFiscal)
                                                    <td class="text-center">
                                                        @if($alreadyTimbrado)
                                                            <div class="btn-group btn-group-sm" role="group">
                                                                @if($resultado['xml_path'])
                                                                    <a href="{{ Storage::url($resultado['xml_path']) }}" class="btn btn-outline-secondary" target="_blank" title="Descargar XML"><i class="bi bi-filetype-xml"></i></a>
                                                                @endif
                                                                @if($resultado['pdf_path'])
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
                                            <td colspan="12" class="text-center text-muted py-4">No se encontraron registros de lista de raya para los filtros seleccionados.</td>
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
            tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });
        });
    </script>
    @endpush
</x-app-layout>