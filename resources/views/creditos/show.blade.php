<x-app-layout>
    <div class="container-fluid py-4">
        
        {{-- ALERTAS DE ERROR Y ÉXITO (AQUÍ VEREMOS POR QUÉ SE DETIENE LA APROBACIÓN) --}}
        @if ($errors->any())
            <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm" role="alert">
                <h6 class="alert-heading fw-bold mb-1"><i class="bi bi-exclamation-diamond-fill me-2"></i>Faltan datos o son incorrectos:</h6>
                <ul class="mb-0 small">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if (session('error'))
            <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm" role="alert">
                <h6 class="alert-heading fw-bold mb-1"><i class="bi bi-x-circle-fill me-2"></i>Error del Sistema:</h6>
                <p class="mb-0 small">{{ session('error') }}</p>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm" role="alert">
                <h6 class="alert-heading fw-bold mb-1"><i class="bi bi-check-circle-fill me-2"></i>¡Excelente!</h6>
                <p class="mb-0 small">{{ session('success') }}</p>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        {{-- ENCABEZADO Y ESTATUS --}}
        <div class="d-flex justify-content-between align-items-center mb-4 mt-2">
            <div>
                <h4 class="fw-bold mb-1 text-dark">
                    <i class="bi bi-file-earmark-text me-2 text-primary"></i>Solicitud: {{ $credito->folio }}
                </h4>
                <p class="text-muted"><i class="bi bi-person-badge"></i> Asesor Responsable: <b>{{ $credito->asesor->nombre_completo ?? 'Sin asignar' }}</b> | Fecha de solicitud: {{ \Carbon\Carbon::parse($credito->fecha_solicitud)->format('d/m/Y') }}</p>
            </div>
            <div class="text-end">
                @if($credito->estatus == 'solicitado')
                    <span class="badge bg-warning text-dark fs-6 px-3 py-2"><i class="bi bi-hourglass-split me-1"></i> Pendiente de Aprobación</span>
                @elseif($credito->estatus == 'aprobado')
                    <span class="badge bg-info text-dark fs-6 px-3 py-2"><i class="bi bi-check-all me-1"></i> Aprobado (Falta Fondeo)</span>
                @elseif($credito->estatus == 'desembolsado')
                    <span class="badge bg-success fs-6 px-3 py-2"><i class="bi bi-cash me-1"></i> Crédito Activo</span>
                @endif
                <div class="d-flex align-items-center ms-3">
                    @if($credito->estatus == 'solicitado')
                    <form action="{{ route('creditos.destroy', $credito->id) }}" method="POST" onsubmit="return confirm('¿Estás seguro de eliminar completamente esta solicitud? Esta acción no se puede deshacer.');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-outline-danger me-2 shadow-sm">
                            <i class="bi bi-trash3-fill me-1"></i> Eliminar Solicitud
                        </button>
                    </form>
                    @endif
                    <a href="{{ route('creditos.index') }}" class="btn btn-outline-secondary shadow-sm">
                        <i class="bi bi-arrow-left me-1"></i> Regresar
                    </a>
                </div>
            </div>
        </div>

        <div class="row">
            {{-- COLUMNA IZQUIERDA: DATOS GENERALES --}}
            <div class="col-lg-4 mb-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-white py-3">
                        <h6 class="fw-bold text-dark mb-0"><i class="bi bi-info-circle-fill me-2 text-primary"></i>Detalles del Crédito</h6>
                    </div>
                    <div class="card-body">
                        
                        <div class="mb-3">
                            <span class="text-muted d-block small fw-bold text-uppercase">Titular</span>
                            @if($credito->grupo_id)
                                <span class="fs-5 fw-bold text-purple"><i class="bi bi-people-fill me-1"></i> {{ $credito->grupo->nombre_grupo }}</span>
                                <span class="badge bg-purple bg-opacity-10 text-purple ms-2">Grupal</span>
                            @else
                                <span class="fs-5 fw-bold text-info text-dark"><i class="bi bi-person-fill me-1"></i> {{ $credito->cliente->nombre_completo ?? $credito->cliente->nombre }}</span>
                                <span class="badge bg-info bg-opacity-10 text-info text-dark ms-2">Individual</span>
                            @endif
                            @if($credito->nombre_credito)
                                <div class="text-muted mt-1 fst-italic">"{{ $credito->nombre_credito }}"</div>
                            @endif
                        </div>

                        <hr>

                        <div class="mb-3">
                            <span class="text-muted d-block small fw-bold text-uppercase">Producto Aplicado</span>
                            <span class="fw-bold text-dark">{{ $credito->producto->nombre }}</span>
                            <ul class="mb-0 mt-1 small text-muted">
                                <li>Tasa: {{ $credito->tasa_interes_aplicada }}%</li>
                                <li>Comisión Apertura: {{ $credito->comision_apertura_aplicada }}%</li>
                            </ul>
                        </div>

                        <hr>

                        <div class="mb-3">
                            <span class="text-muted d-block small fw-bold text-uppercase">Monto Solicitado</span>
                            <span class="fs-3 fw-bold text-success">${{ number_format($credito->monto_solicitado, 2) }}</span>
                        </div>
                        
                        <div class="mb-3">
                            <span class="text-muted d-block small fw-bold text-uppercase">Plazo Solicitado</span>
                            <span class="fs-5 fw-bold text-dark">{{ $credito->plazo_solicitado }} Cuotas ({{ ucfirst($credito->producto->frecuencia_pago) }}s)</span>
                        </div>

                        @if($credito->fecha_desembolso)
                        <div class="mb-3 mt-3 pt-3 border-top">
                            <span class="text-muted d-block small fw-bold text-uppercase text-info"><i class="bi bi-calendar-event me-1"></i>Fecha de Desembolso Programada</span>
                            <span class="fs-6 fw-bold text-dark">{{ \Carbon\Carbon::parse($credito->fecha_desembolso)->isoFormat('LL') }}</span>
                        </div>
                        @endif

                    </div>
                </div>
            </div>

            {{-- COLUMNA DERECHA: INTEGRANTES Y CUENTAS --}}
            <div class="col-lg-8">
                
                {{-- TABLA DE INTEGRANTES --}}
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white py-3">
                        <h6 class="fw-bold text-dark mb-0"><i class="bi bi-people-fill me-2 text-success"></i>Integrantes del Crédito</h6>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="bg-light">
                                    <tr>
                                        <th class="ps-4">Nombre del Cliente</th>
                                        <th class="text-center">Rol</th>
                                        <th class="text-end pe-4">Monto Individual</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($credito->integrantes as $integrante)
                                    <tr>
                                        <td class="ps-4 fw-bold">
                                            {{ $integrante->nombre }} {{ $integrante->apellido_paterno }} {{ $integrante->apellido_materno }}
                                        </td>
                                        <td class="text-center">
                                            @if($integrante->pivot->es_lider)
                                                <span class="badge bg-warning text-dark"><i class="bi bi-star-fill me-1"></i> Líder</span>
                                            @else
                                                <span class="badge bg-secondary">Integrante</span>
                                            @endif
                                        </td>
                                        <td class="text-end pe-4 text-success fw-bold">
                                            ${{ number_format($integrante->pivot->monto_individual, 2) }}
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                {{-- TABLA DE CUENTAS BANCARIAS --}}
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white py-3">
                        <h6 class="fw-bold text-dark mb-0"><i class="bi bi-bank2 me-2 text-warning"></i>Cuentas de Fondeo (Desembolso)</h6>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="bg-light">
                                    <tr>
                                        <th class="ps-4">Banco</th>
                                        <th>Titular</th>
                                        <th class="pe-4">Cuenta / CLABE</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($credito->cuentasDesembolso as $cuenta)
                                    <tr>
                                        <td class="ps-4 fw-bold">{{ $cuenta->banco }}</td>
                                        <td>{{ $cuenta->titular }}</td>
                                        <td class="pe-4 text-primary font-monospace">{{ $cuenta->numero_cuenta }}</td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="3" class="text-center text-muted py-3">No hay cuentas registradas.</td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                {{-- NUEVA SECCIÓN DE GARANTÍA --}}
                @if($credito->garantia)
                <div class="card border-0 shadow-sm mb-4 border-start border-danger border-4">
                    <div class="card-header bg-white py-3">
                        <h6 class="fw-bold text-dark mb-0"><i class="bi bi-shield-lock-fill me-2 text-danger"></i>Garantía Asociada al Crédito</h6>
                    </div>
                    <div class="card-body bg-light">
                        @if($credito->garantia->tipo_garantia == 'vehiculo')
                            <div class="row">
                                <div class="col-md-4 mb-2">
                                    <span class="small text-muted d-block fw-bold">Tipo de Vehículo</span>
                                    <span class="fw-bold">{{ $credito->garantia->vehiculo_tipo }}</span>
                                </div>
                                <div class="col-md-4 mb-2">
                                    <span class="small text-muted d-block fw-bold">Marca y Modelo</span>
                                    <span class="fw-bold">{{ $credito->garantia->vehiculo_marca }} {{ $credito->garantia->vehiculo_modelo }} ({{ $credito->garantia->vehiculo_anio }})</span>
                                </div>
                                <div class="col-md-4 mb-2">
                                    <span class="small text-muted d-block fw-bold">Color y Motor</span>
                                    <span class="fw-bold">{{ $credito->garantia->vehiculo_color }} / {{ $credito->garantia->vehiculo_motor }}</span>
                                </div>
                                <div class="col-md-6 mt-2">
                                    <span class="small text-muted d-block fw-bold">Número de Serie (VIN)</span>
                                    <span class="fw-bold font-monospace text-primary">{{ $credito->garantia->vehiculo_serie }}</span>
                                </div>
                                <div class="col-md-6 mt-2">
                                    <span class="small text-muted d-block fw-bold">Documento Amparador</span>
                                    <span class="fw-bold">{{ $credito->garantia->vehiculo_documento }}</span>
                                </div>
                            </div>
                            
                            @if($credito->producto->requiere_seguro)
                                <div class="row mt-3 pt-3 border-top">
                                    <div class="col-md-12">
                                        <span class="small text-muted d-block fw-bold mb-2"><i class="bi bi-shield-check me-1"></i>Estatus de Seguro (Requerido)</span>
                                        @if($credito->garantia->tiene_seguro)
                                            <span class="badge bg-success bg-opacity-10 text-success px-3 py-2 border border-success">
                                                <i class="bi bi-check-circle-fill me-1"></i> Seguro Vigente hasta {{ \Carbon\Carbon::parse($credito->garantia->vigencia_seguro)->format('d/m/Y') }}
                                            </span>
                                        @else
                                            <span class="badge bg-danger bg-opacity-10 text-danger px-3 py-2 border border-danger">
                                                <i class="bi bi-x-circle-fill me-1"></i> Sin Seguro (Se sugiere aplicar penalización)
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            @endif

                        @else
                            <div class="row">
                                <div class="col-md-12 mb-2">
                                    <span class="small text-muted d-block fw-bold">Ubicación de la Propiedad</span>
                                    <span class="fw-bold">{{ $credito->garantia->propiedad_ubicacion }}</span>
                                </div>
                                <div class="col-md-6 mt-2">
                                    <span class="small text-muted d-block fw-bold">Superficie Total</span>
                                    <span class="fw-bold">{{ $credito->garantia->propiedad_superficie }}</span>
                                </div>
                                <div class="col-md-6 mt-2">
                                    <span class="small text-muted d-block fw-bold">Documento Amparador</span>
                                    <span class="fw-bold">{{ $credito->garantia->propiedad_documento }}</span>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
                @endif

                {{-- ZONA DE AUTORIZACIÓN --}}
                @if($credito->estatus == 'solicitado')
                <div class="card border-0 shadow-sm border-start border-warning border-4 bg-light mb-4">
                    <div class="card-body p-4 text-center">
                        <h5 class="fw-bold text-dark mb-3">Zona de Autorización de Crédito</h5>
                        <p class="text-muted">Como jefe de administración, revisa los datos capturados. Si todo está correcto, procede a dictaminar el crédito.</p>
                        
                        <button type="button" class="btn btn-warning fw-bold text-dark px-4 shadow-sm" data-bs-toggle="modal" data-bs-target="#modalAprobarCredito">
                            <i class="bi bi-check-circle-fill me-2"></i> Dictaminar / Aprobar Crédito
                        </button>
                    </div>
                </div>
                @endif

                {{-- EXPEDIENTE LEGAL (DOCUMENTOS PDF) --}}
                @if($credito->estatus == 'aprobado' || $credito->estatus == 'desembolsado')
                <div class="card border-0 shadow-sm mb-4 border-start border-primary border-4 bg-light">
                    <div class="card-body p-4 d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="fw-bold text-dark mb-1"><i class="bi bi-folder-check me-2 text-primary"></i>Expediente Legal del Crédito</h6>
                            <small class="text-muted">Descarga o imprime los documentos oficiales para firma.</small>
                        </div>
                        <div>
                            <a href="{{ route('creditos.contrato', $credito->id) }}" target="_blank" class="btn btn-sm btn-outline-danger shadow-sm me-1">
                                <i class="bi bi-file-earmark-pdf-fill"></i> Contrato
                            </a>
                            <a href="{{ route('creditos.carta', $credito->id) }}" target="_blank" class="btn btn-sm btn-outline-danger shadow-sm me-1">
                                <i class="bi bi-file-earmark-text-fill"></i> Compromiso
                            </a>
                            <a href="{{ route('creditos.acta', $credito->id) }}" target="_blank" class="btn btn-sm btn-outline-danger shadow-sm me-1">
                                <i class="bi bi-file-earmark-check-fill"></i> Acta
                            </a>
                            <a href="{{ route('creditos.acuse', $credito->id) }}" target="_blank" class="btn btn-sm btn-outline-danger shadow-sm me-1">
                                <i class="bi bi-file-earmark-ruled-fill"></i> Acuse
                            </a>
                            <a href="{{ route('creditos.tabla', $credito->id) }}" target="_blank" class="btn btn-sm btn-outline-danger shadow-sm">
                                <i class="bi bi-table"></i> Control de Pagos
                            </a>
                        </div>
                    </div>
                </div>
                @endif

    {{-- MODAL DE APROBACIÓN DE CRÉDITO --}}
    @if($credito->estatus == 'solicitado')
    <div class="modal fade" id="modalAprobarCredito" tabindex="-1" aria-labelledby="modalAprobarLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-warning text-dark">
                    <h5 class="modal-title fw-bold" id="modalAprobarLabel"><i class="bi bi-ui-checks me-2"></i>Dictamen de Aprobación</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('creditos.aprobar', $credito->id) }}" method="POST" id="formAprobarCredito">
                    @csrf
                    <div class="modal-body bg-light p-4">
                        
                        @php
                            $penalizacionSugerida = 0;
                            if($credito->producto->requiere_seguro && $credito->garantia && !$credito->garantia->tiene_seguro) {
                                $penalizacionSugerida = $credito->producto->penalizacion_seguro;
                            }
                        @endphp

                        @if($penalizacionSugerida > 0)
                            <div class="alert alert-danger py-2 small mb-3 border-danger">
                                <i class="bi bi-exclamation-triangle-fill me-1"></i> El vehículo no cuenta con seguro. Se agregó una retención de <b>${{ number_format($penalizacionSugerida, 2) }}</b> por defecto.
                            </div>
                        @endif

                        {{-- Parámetros Editables --}}
                        <h6 class="fw-bold text-success border-bottom pb-2 mb-3">Parámetros Financieros (Editables)</h6>
                        <div class="row mb-3">
                            <div class="col-md-3">
                                <label class="form-label small fw-bold">Monto Autorizar ($)</label>
                                <input type="number" step="0.01" class="form-control fw-bold text-primary" name="monto_aprobado" id="modal_monto" value="{{ $credito->monto_solicitado }}" required>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small fw-bold">Comisión (%)</label>
                                <input type="number" step="0.01" class="form-control" name="comision_apertura" id="modal_comision" value="{{ $credito->comision_apertura_aplicada }}" required>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small fw-bold">Retención Seguro ($)</label>
                                <input type="number" step="0.01" class="form-control text-danger" name="retencion_seguro" id="modal_retencion_seguro" value="{{ $penalizacionSugerida }}" required>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small fw-bold text-success">Total a Fondear</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-success text-white fw-bold">$</span>
                                    <input type="text" class="form-control fw-bold bg-white text-success" id="modal_fondeo" readonly>
                                </div>
                            </div>
                        </div>

                        {{-- Documentación y Fechas --}}
                        <h6 class="fw-bold text-primary border-bottom pb-2 mb-3 mt-4">Emisión y Fechas Clave</h6>
                        <div class="row mb-3">
                            <div class="col-md-12 mb-3">
                                <label class="form-label small fw-bold">Empresa Emisora (Contrato a nombre de:) <span class="text-danger">*</span></label>
                                <select class="form-select" name="patron_id" required>
                                    <option value="">Seleccione un Patrón...</option>
                                    @if(isset($patrones))
                                        @foreach($patrones as $patron)
                                            <option value="{{ $patron->id_patron }}">{{ $patron->nombre_comercial }} - {{ $patron->razon_social }}</option>
                                        @endforeach
                                    @endif
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-info">Fecha de Desembolso <span class="text-danger">*</span></label>
                                <input type="date" class="form-control border-info" name="fecha_desembolso" value="{{ $credito->fecha_desembolso ? \Carbon\Carbon::parse($credito->fecha_desembolso)->format('Y-m-d') : date('Y-m-d') }}" required title="¿Cuándo se le entregará el dinero al cliente?">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-success">Fecha de Primer Pago <span class="text-danger">*</span></label>
                                <input type="date" class="form-control border-success" name="fecha_primer_pago" required title="¿Cuándo pagará su primera cuota?">
                            </div>
                        </div>

                        {{-- LUGARES DE PAGO AUTORIZADOS --}}
                        <h6 class="fw-bold text-info border-bottom pb-2 mb-3 mt-4"><i class="bi bi-wallet2 me-1"></i> Lugares de Pago Autorizados (Para el Pagaré)</h6>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label small fw-bold">Cuentas Bancarias Permitidas</label>
                                <div class="border rounded p-2 bg-white shadow-sm" style="max-height: 140px; overflow-y: auto;">
                                    @forelse($cuentasEmpresa ?? [] as $cuenta)
                                        <div class="form-check mb-2 border-bottom pb-2">
                                            {{-- QUITÉ EL ATRIBUTO "checked" AQUÍ --}}
                                            <input class="form-check-input" type="checkbox" name="cuentas_pago[]" value="{{ $cuenta->id }}" id="chk_cuenta_{{ $cuenta->id }}">
                                            <label class="form-check-label small" for="chk_cuenta_{{ $cuenta->id }}">
                                                <b>{{ $cuenta->banco }}</b> - {{ $cuenta->titular }}<br>
                                                <span class="text-muted" style="font-size: 0.8em;">Cta: {{ $cuenta->numero_cuenta }}</span>
                                            </label>
                                        </div>
                                    @empty
                                        <span class="small text-muted">No hay cuentas bancarias registradas.</span>
                                    @endforelse
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold">Cajas físicas (Sucursales) Permitidas</label>
                                <div class="border rounded p-2 bg-white shadow-sm" style="max-height: 140px; overflow-y: auto;">
                                    @forelse($sucursales ?? [] as $sucursal)
                                        <div class="form-check mb-1">
                                            {{-- QUITÉ EL ATRIBUTO "checked" AQUÍ --}}
                                            <input class="form-check-input" type="checkbox" name="sucursales_pago[]" value="{{ $sucursal->id_sucursal }}" id="chk_sucursal_{{ $sucursal->id_sucursal }}">
                                            <label class="form-check-label small" for="chk_sucursal_{{ $sucursal->id_sucursal }}">
                                                Caja Sucursal <b>{{ $sucursal->nombre_sucursal }}</b>
                                            </label>
                                        </div>
                                    @empty
                                        <span class="small text-muted">No hay sucursales registradas.</span>
                                    @endforelse
                                </div>
                            </div>
                        </div>

                    </div>
                    <div class="modal-footer d-flex justify-content-between bg-white">
                        <button type="button" class="btn btn-outline-danger fw-bold"><i class="bi bi-x-circle me-1"></i> Rechazar Crédito</button>
                        <div>
                            <button type="button" class="btn btn-light border me-2" data-bs-dismiss="modal">Cancelar</button>
                            <button type="submit" class="btn btn-success fw-bold px-4"><i class="bi bi-check-lg me-1"></i> Confirmar Aprobación</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const inputMonto = document.getElementById('modal_monto');
            const inputComision = document.getElementById('modal_comision');
            const inputRetencionSeguro = document.getElementById('modal_retencion_seguro');
            const inputFondeo = document.getElementById('modal_fondeo');

            if (inputMonto && inputComision && inputFondeo && inputRetencionSeguro) {
                function calcularFondeo() {
                    let monto = parseFloat(inputMonto.value) || 0;
                    let comisionPct = parseFloat(inputComision.value) || 0;
                    let retencionSeg = parseFloat(inputRetencionSeguro.value) || 0;
                    
                    let comisionEfectiva = monto * (comisionPct / 100);
                    
                    // Restamos la comisión y lo que el gerente haya decidido dejar en la casilla de retención de seguro
                    let aFondear = monto - comisionEfectiva - retencionSeg;
                    
                    inputFondeo.value = aFondear.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                }

                // Escuchamos los cambios en los 3 campos
                inputMonto.addEventListener('input', calcularFondeo);
                inputComision.addEventListener('input', calcularFondeo);
                inputRetencionSeguro.addEventListener('input', calcularFondeo);
                
                calcularFondeo();
            }
        });
    </script>
    @endpush
</x-app-layout>