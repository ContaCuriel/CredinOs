<x-app-layout>
    {{-- Librerías para el Buscador Inteligente de Clientes --}}
    @push('styles')
        <style>
            .select2-resultado-cliente { padding: 4px 0; }
            .select2-resultado-cliente .titulo { font-weight: bold; color: #212529; }
            .select2-resultado-cliente .detalles { font-size: 0.85em; color: #6c757d; display: flex; justify-content: space-between; margin-top: 2px; }
        </style>
    @endpush

    <div class="container-fluid py-4">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <h5 class="mb-0 fw-bold text-dark">
                    <i class="bi bi-file-earmark-plus-fill me-2 text-primary"></i>Registrar Solicitud de Crédito
                </h5>
                <a href="{{ route('creditos.index') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-arrow-left me-1"></i> Regresar
                </a>
            </div>
            <div class="card-body bg-light">
                
                @if ($errors->any())
                    <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm" role="alert">
                        <h6 class="alert-heading fw-bold mb-1"><i class="bi bi-exclamation-diamond-fill me-2"></i>Verifica los datos:</h6>
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

                <form action="{{ route('creditos.store') }}" method="POST" id="formCredito">
                    @csrf
                    
                    {{-- SECCIÓN 1: DATOS GENERALES --}}
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-body p-4">
                            <h6 class="fw-bold text-primary border-bottom pb-2 mb-3">1. Parámetros del Crédito</h6>
                            
                            {{-- FILA 1: Producto y Nombre del Crédito (Fusionado) --}}
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Producto de Crédito <span class="text-danger">*</span></label>
                                    <select class="form-select" name="producto_id" id="producto_id" required>
                                        <option value="">Seleccione...</option>
                                        @foreach($productos as $producto)
                                            <option value="{{ $producto->id }}" data-tipo="{{ $producto->tipo_credito }}" data-garantia="{{ $producto->requiere_garantia ?? 0 }}" @selected(old('producto_id') == $producto->id)>
                                                {{ $producto->nombre }} ({{ ucfirst($producto->tipo_credito) }})
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold text-purple">Nombre del Crédito (Identificador) <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control border-purple" name="nombre_credito" id="nombre_credito" value="{{ old('nombre_credito') }}" required placeholder="Ej. Nitzhe Neza o Norma Perez Ind">
                                    {{-- Campo oculto por si el backend necesita forzosamente la variable "nombre_grupo" --}}
                                    <input type="hidden" name="nombre_grupo" id="nombre_grupo">
                                </div>
                            </div>

                            {{-- FILA 2: Asesor, Sucursal y Monto Total --}}
                            <div class="row mb-3">
                                <div class="col-md-4">
                                    <label class="form-label fw-bold">Asesor Responsable <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="asesor_buscar" list="lista_asesores" placeholder="🔍 Buscar asesor..." required autocomplete="off" value="{{ old('asesor_texto_temporal') }}">
                                    <datalist id="lista_asesores">
                                        @foreach($asesores as $asesor)
                                            <option data-id="{{ $asesor->id_empleado }}" data-sucursal="{{ $asesor->id_sucursal }}" value="{{ $asesor->nombre_completo }} ({{ $asesor->sucursal->nombre_sucursal ?? 'Sin Sucursal' }})"></option>
                                        @endforeach
                                    </datalist>
                                    <input type="hidden" name="asesor_id" id="asesor_id" value="{{ old('asesor_id') }}" required>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-bold">Sucursal <span class="text-danger">*</span></label>
                                    <select class="form-select" name="sucursal_id" id="sucursal_id" required>
                                        <option value="">Seleccione...</option>
                                        @foreach($sucursales as $sucursal)
                                            <option value="{{ $sucursal->id_sucursal }}" @selected(old('sucursal_id') == $sucursal->id_sucursal)>
                                                {{ $sucursal->nombre_sucursal }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-bold">Monto Total Solicitado ($) <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-white fw-bold">$</span>
                                        <input type="text" class="form-control form-control-lg text-success fw-bold monto-formateado" id="monto_solicitado_global" name="monto_solicitado" value="{{ old('monto_solicitado') }}" required placeholder="0.00">
                                    </div>
                                </div>
                            </div>
                            
                            {{-- FILA 3: Fechas (NUEVO) --}}
                            <div class="row mb-3">
                                <div class="col-md-4">
                                    <label class="form-label fw-bold text-info">Fecha de Desembolso Solicitada <span class="text-danger">*</span></label>
                                    <input type="date" class="form-control border-info" name="fecha_desembolso" value="{{ old('fecha_desembolso', date('Y-m-d')) }}" required>
                                    <div class="form-text small">Día en que el cliente espera recibir el dinero.</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- SECCIÓN 2: INTEGRANTES DEL CRÉDITO --}}
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-body p-4">
                            <h6 class="fw-bold text-success border-bottom pb-2 mb-3" id="titulo_seccion_clientes">2. Datos del Cliente</h6>
                            
                            {{-- BUSCADOR NATIVO TIPO GOOGLE --}}
                            <div class="row mb-4 bg-light p-3 rounded border">
                                <div class="col-md-12 position-relative">
                                    <label class="form-label fw-bold">Buscar Cliente en el Sistema</label>
                                    <input type="text" class="form-control form-control-lg border-success shadow-sm" id="buscador_clientes_input" placeholder="🔍 Escribe Nombre, Apellido o CURP (Mín. 3 letras)..." autocomplete="off">
                                    
                                    <div id="resultados_clientes" class="list-group position-absolute shadow-lg w-100 mt-1" style="z-index: 1050; display: none; max-height: 250px; overflow-y: auto;"></div>
                                    
                                    <small class="text-muted mt-2 d-block"><i class="bi bi-info-circle-fill me-1"></i> Haz clic en el cliente de la lista desplegable para agregarlo a la tabla de abajo.</small>
                                </div>
                            </div>

                            <div class="table-responsive">
                                <table class="table table-hover align-middle border" id="tabla_clientes">
                                    <thead class="table-light">
                                        <tr>
                                            <th width="40%">Nombre del Cliente</th>
                                            <th width="30%">Monto a Recibir ($)</th>
                                            <th width="15%" class="text-center" id="columna_lider_head">¿Es la Líder?</th>
                                            <th width="15%" class="text-center">Quitar</th>
                                        </tr>
                                    </thead>
                                    <tbody id="tbody_clientes">
                                        <tr id="fila_vacia_clientes">
                                            <td colspan="4" class="text-center text-muted py-4">Aún no has agregado integrantes a este crédito.</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    {{-- SECCIÓN 3: CUENTAS BANCARIAS DE DESEMBOLSO --}}
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-body p-4">
                            <div class="d-flex justify-content-between align-items-center border-bottom pb-2 mb-3">
                                <h6 class="fw-bold text-warning mb-0 text-dark">3. Cuentas para Desembolso</h6>
                                <button type="button" class="btn btn-sm btn-outline-dark fw-bold" id="btnAgregarCuenta">
                                    <i class="bi bi-plus-circle me-1"></i> Añadir Cuenta
                                </button>
                            </div>

                            <div id="contenedor_cuentas">
                                <div class="row fila-cuenta mb-3 bg-light p-2 rounded border">
                                    <div class="col-md-3">
                                        <label class="form-label small fw-bold">Banco <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" name="cuentas[0][banco]" required placeholder="Ej. Azteca, Inbursa...">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label small fw-bold d-flex justify-content-between align-items-center">
                                            <span>Nombre del Titular <span class="text-danger">*</span></span>
                                            <div class="form-check form-switch mb-0" style="font-size: 0.85em;">
                                                <input class="form-check-input check-usar-titular" type="checkbox" role="switch">
                                                <label class="form-check-label text-muted ms-1">Usar cliente</label>
                                            </div>
                                        </label>
                                        <input type="text" class="form-control input-titular-cuenta" name="cuentas[0][titular]" required placeholder="Nombre completo">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label small fw-bold">Número de Cuenta / CLABE <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" name="cuentas[0][cuenta]" required placeholder="18 dígitos o cuenta">
                                    </div>
                                    <div class="col-md-1 d-flex align-items-end">
                                        <button type="button" class="btn btn-outline-danger w-100 btn-quitar-cuenta" disabled>
                                            <i class="bi bi-trash-fill"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- SECCIÓN 4: GARANTÍAS --}}
                    <div class="card border-0 shadow-sm mb-4 bg-light border-start border-danger border-4" id="seccion_garantia" style="display: none;">
                        <div class="card-body p-4">
                            <h6 class="fw-bold text-danger border-bottom border-danger pb-2 mb-3">
                                <i class="bi bi-shield-lock-fill me-2"></i>4. Garantía Prendaria o Hipotecaria
                            </h6>
                            <p class="text-muted small">Este producto de crédito exige el registro de una garantía formal.</p>
                            
                            <div class="row mb-4">
                                <div class="col-md-4">
                                    <label class="form-label fw-bold">Tipo de Garantía</label>
                                    <select class="form-select border-danger" name="garantia[tipo]" id="tipo_garantia">
                                        <option value="vehiculo">Vehículo (Auto/Moto)</option>
                                        <option value="propiedad">Terreno / Propiedad</option>
                                    </select>
                                </div>
                            </div>

                            {{-- Formulario para Vehículo --}}
                            <div id="form_garantia_vehiculo">
                                <div class="row mb-3">
                                    <div class="col-md-4">
                                        <label class="form-label small fw-bold">Documento Amparador (Ej. FACTURA 18469 D)</label>
                                        <input type="text" class="form-control" name="garantia[vehiculo_documento]" placeholder="Número de factura o documento">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label small fw-bold">Tipo de Vehículo</label>
                                        <select class="form-select" name="garantia[vehiculo_tipo]">
                                            <option value="AUTOMOVIL">Automóvil</option>
                                            <option value="MOTOCICLETA">Motocicleta</option>
                                            <option value="CAMIONETA">Camioneta</option>
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label small fw-bold">Marca</label>
                                        <input type="text" class="form-control" name="garantia[vehiculo_marca]" placeholder="Ej. VOLKSWAGEN, ITALIKA...">
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <div class="col-md-4">
                                        <label class="form-label small fw-bold">Modelo y Versión</label>
                                        <input type="text" class="form-control" name="garantia[vehiculo_modelo]" placeholder="Ej. JETTA VERSION EUROPA">
                                    </div>
                                    <div class="col-md-2">
                                        <label class="form-label small fw-bold">Año</label>
                                        <input type="text" class="form-control" name="garantia[vehiculo_anio]" placeholder="Ej. 2007">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label small fw-bold">Motor</label>
                                        <input type="text" class="form-control" name="garantia[vehiculo_motor]" placeholder="Ej. 2.0 LTS">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label small fw-bold">Color</label>
                                        <input type="text" class="form-control" name="garantia[vehiculo_color]" placeholder="Ej. BLANCO CAMPAN">
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <label class="form-label small fw-bold">Número de Serie (VIN)</label>
                                        <input type="text" class="form-control font-monospace text-uppercase" name="garantia[vehiculo_serie]" placeholder="17 Caracteres">
                                    </div>
                                </div>

                                {{-- NUEVO: SECCIÓN DE SEGURO --}}
                                <hr class="text-danger mt-4 mb-3">
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label class="form-label small fw-bold">¿Cuenta con Seguro Activo?</label>
                                        <select class="form-select border-danger" name="garantia[tiene_seguro]" id="tiene_seguro_vehiculo">
                                            <option value="0">No (Aplica retención si el producto lo requiere)</option>
                                            <option value="1">Sí, seguro vigente</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6" id="div_vigencia_seguro" style="display: none;">
                                        <label class="form-label small fw-bold">Vigencia del Seguro (Fecha de Vencimiento)</label>
                                        <input type="date" class="form-control" name="garantia[vigencia_seguro]" id="vigencia_seguro_input">
                                    </div>
                                </div>

                            </div>

                            {{-- Formulario para Propiedad/Terreno --}}
                            <div id="form_garantia_propiedad" style="display: none;">
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label class="form-label small fw-bold">Documento / Descripción (Ej. PARCELA EJIDAL NUMERO 64 Z-1)</label>
                                        <input type="text" class="form-control" name="garantia[propiedad_documento]" placeholder="Escritura, Constancia Ejidal, etc.">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small fw-bold">Ubicación de la Propiedad</label>
                                        <input type="text" class="form-control" name="garantia[propiedad_ubicacion]" placeholder="Ej. EJIDO DE TLAMINCA DE TEXCOTZINGO...">
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <div class="col-md-12">
                                        <label class="form-label small fw-bold">Medidas y Colindancias</label>
                                        <textarea class="form-control" name="garantia[propiedad_medidas]" rows="3" placeholder="Ej. AL NORESTE 75.280 METROS CON PARCELA 53... AL SURESTE..."></textarea>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <label class="form-label small fw-bold">Superficie Total</label>
                                        <input type="text" class="form-control" name="garantia[propiedad_superficie]" placeholder="Ej. 0-97-57.660 HA. (NOVENTA Y SIETE AREAS...)">
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>

                    <div class="text-end mt-4 mb-5">
                        <a href="{{ route('creditos.index') }}" class="btn btn-light me-2 border">Cancelar</a>
                        <button type="submit" class="btn btn-primary px-5 shadow-sm fw-bold">
                            <i class="bi bi-send-check-fill me-2"></i> Enviar Solicitud de Crédito
                        </button>
                    </div>

                </form>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            
            // --- 0. FORMATEO DE MONEDA CON COMAS ---
            function formatNumberWithCommas(value) {
                if (!value) return '';
                let parts = value.toString().replace(/[^0-9.]/g, '').split('.');
                parts[0] = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, ",");
                return parts.join('.');
            }

            document.addEventListener('input', function(e) {
                if (e.target.classList.contains('monto-formateado')) {
                    let rawVal = e.target.value.replace(/,/g, '');
                    e.target.value = formatNumberWithCommas(rawVal);
                }
            });

            // --- 1. LÓGICA DEL BUSCADOR DE ASESORES Y SUCURSAL AUTO ---
            const asesorInput = document.getElementById('asesor_buscar');
            const asesorHidden = document.getElementById('asesor_id');
            const sucursalSelect = document.getElementById('sucursal_id');
            const montoGlobalInput = document.getElementById('monto_solicitado_global');
            
            asesorInput.addEventListener('input', function(e) {
                const list = document.getElementById('lista_asesores').options;
                asesorHidden.value = ''; 
                for (let i = 0; i < list.length; i++) {
                    if (list[i].value === e.target.value) {
                        asesorHidden.value = list[i].getAttribute('data-id');
                        const sucursalAsesor = list[i].getAttribute('data-sucursal');
                        if(sucursalAsesor) sucursalSelect.value = sucursalAsesor;
                        break;
                    }
                }
            });

            montoGlobalInput.addEventListener('input', function() {
                if (esIndividualGlobal) {
                    const inputsMontoIndividual = document.querySelectorAll('.monto-individual-input');
                    inputsMontoIndividual.forEach(input => {
                        input.value = this.value;
                    });
                }
            });

            // --- 2. LÓGICA DE PRODUCTO GRUPAL VS INDIVIDUAL Y GARANTÍA ---
            const productoSelect = document.getElementById('producto_id');
            
            const columnaLiderHead = document.getElementById('columna_lider_head');
            const tituloSeccionClientes = document.getElementById('titulo_seccion_clientes');
            const seccionGarantia = document.getElementById('seccion_garantia');

            let esIndividualGlobal = false;
            let indiceCliente = 0;
            const tbodyClientes = document.getElementById('tbody_clientes');

            function toggleGrupo() {
                if(productoSelect.selectedIndex === 0) {
                    seccionGarantia.style.display = 'none';
                    return;
                }
                
                const option = productoSelect.options[productoSelect.selectedIndex];
                const tipo = option.getAttribute('data-tipo');
                const requiereGarantia = option.getAttribute('data-garantia');
                
                if(requiereGarantia === "1" || requiereGarantia === "true") {
                    seccionGarantia.style.display = 'block';
                } else {
                    seccionGarantia.style.display = 'none';
                }

                tbodyClientes.innerHTML = '<tr id="fila_vacia_clientes"><td colspan="4" class="text-center text-muted py-4">Aún no has agregado integrantes a este crédito.</td></tr>';
                indiceCliente = 0;

                if (tipo === 'grupal') {
                    columnaLiderHead.style.display = 'table-cell';
                    tituloSeccionClientes.innerHTML = '2. Integrantes del Grupo';
                    esIndividualGlobal = false;
                } else {
                    columnaLiderHead.style.display = 'none';
                    tituloSeccionClientes.innerHTML = '2. Datos del Cliente';
                    esIndividualGlobal = true;
                }
            }

            productoSelect.addEventListener('change', toggleGrupo);
            toggleGrupo();

            // --- 2.5 LÓGICA INTERNA DEL FORMULARIO DE GARANTÍAS ---
            const tipoGarantiaSelect = document.getElementById('tipo_garantia');
            const formVehiculo = document.getElementById('form_garantia_vehiculo');
            const formPropiedad = document.getElementById('form_garantia_propiedad');

            tipoGarantiaSelect.addEventListener('change', function() {
                if(this.value === 'vehiculo') {
                    formVehiculo.style.display = 'block';
                    formPropiedad.style.display = 'none';
                } else {
                    formVehiculo.style.display = 'none';
                    formPropiedad.style.display = 'block';
                }
            });

            // --- 2.6 LÓGICA DE SEGURO DE VEHÍCULO ---
            const selectSeguro = document.getElementById('tiene_seguro_vehiculo');
            const divVigencia = document.getElementById('div_vigencia_seguro');
            const inputVigencia = document.getElementById('vigencia_seguro_input');

            if(selectSeguro) {
                selectSeguro.addEventListener('change', function() {
                    if(this.value === '1') {
                        divVigencia.style.display = 'block';
                        inputVigencia.required = true;
                    } else {
                        divVigencia.style.display = 'none';
                        inputVigencia.required = false;
                        inputVigencia.value = '';
                    }
                });
            }

            // --- 3. NUEVO BUSCADOR DE CLIENTES NATIVO TIPO GOOGLE ---
            const inputBuscador = document.getElementById('buscador_clientes_input');
            const divResultados = document.getElementById('resultados_clientes');
            let timeoutId;

            inputBuscador.addEventListener('input', function() {
                clearTimeout(timeoutId);
                const query = this.value.trim();

                if (query.length < 3) {
                    divResultados.style.display = 'none';
                    return;
                }

                timeoutId = setTimeout(() => {
                    fetch(`{{ route('web.clientes.search') }}?term=${encodeURIComponent(query)}`)
                        .then(response => response.json())
                        .then(data => {
                            divResultados.innerHTML = '';
                            if(data.error) {
                                divResultados.innerHTML = `<div class="list-group-item text-danger">Error: ${data.error}</div>`;
                            } else if(data.length === 0) {
                                divResultados.innerHTML = '<div class="list-group-item text-muted">No se encontraron clientes</div>';
                            } else {
                                data.forEach(cliente => {
                                    const a = document.createElement('a');
                                    a.href = 'javascript:void(0)';
                                    a.className = 'list-group-item list-group-item-action py-2';
                                    a.innerHTML = `
                                        <div class='fw-bold text-dark'><i class='bi bi-person-fill me-1 text-primary'></i>${cliente.text}</div>
                                        <div class='small text-muted d-flex justify-content-between mt-1'>
                                            <span><i class='bi bi-card-text me-1'></i>${cliente.curp || 'Sin CURP'}</span>
                                            <span><i class='bi bi-geo-alt-fill me-1'></i>${cliente.municipio || 'N/A'}</span>
                                        </div>
                                    `;
                                    a.addEventListener('click', () => agregarClienteATabla(cliente));
                                    divResultados.appendChild(a);
                                });
                            }
                            divResultados.style.display = 'block';
                        }).catch(err => console.error(err));
                }, 300);
            });

            document.addEventListener('click', function(e) {
                if (!inputBuscador.contains(e.target) && !divResultados.contains(e.target)) {
                    divResultados.style.display = 'none';
                }
            });

            function agregarClienteATabla(cliente) {
                if (esIndividualGlobal && tbodyClientes.querySelectorAll('tr[id^="fila_cliente_"]').length >= 1) {
                    alert('Un crédito individual solo puede tener un cliente asignado.');
                    return;
                }
                if (document.getElementById('fila_cliente_' + cliente.id)) {
                    alert('Este cliente ya está en la lista de abajo.');
                    return;
                }

                const filaVacia = document.getElementById('fila_vacia_clientes');
                if (filaVacia) filaVacia.style.display = 'none';

                const checkLider = (indiceCliente === 0) ? 'checked' : '';
                const displayLider = esIndividualGlobal ? 'none' : 'table-cell';
                
                const montoValue = esIndividualGlobal ? montoGlobalInput.value : '';
                const readOnlyAtr = esIndividualGlobal ? 'readonly' : '';
                const customClass = esIndividualGlobal ? 'bg-light' : '';

                const tr = document.createElement('tr');
                tr.id = 'fila_cliente_' + cliente.id;
                tr.innerHTML = `
                    <td class="fw-bold nombre-cliente-fila">
                        <i class="bi bi-person-circle text-muted me-2"></i> <span>${cliente.text}</span>
                        <input type="hidden" name="clientes[${indiceCliente}][id]" value="${cliente.id}">
                    </td>
                    <td>
                        <div class="input-group input-group-sm">
                            <span class="input-group-text bg-white">$</span>
                            <input type="text" class="form-control border-success monto-individual-input monto-formateado ${customClass}" name="clientes[${indiceCliente}][monto]" required placeholder="0.00" value="${montoValue}" ${readOnlyAtr}>
                        </div>
                    </td>
                    <td class="text-center col-lider-body" style="display: ${displayLider};">
                        <input class="form-check-input" type="radio" name="lider_id" value="${cliente.id}" ${checkLider} style="transform: scale(1.3);">
                    </td>
                    <td class="text-center">
                        <button type="button" class="btn btn-outline-danger btn-sm btn-quitar-cliente" data-id="${cliente.id}">
                            <i class="bi bi-trash"></i>
                        </button>
                    </td>
                `;

                tbodyClientes.appendChild(tr);
                indiceCliente++;
                
                inputBuscador.value = '';
                divResultados.style.display = 'none';
            }

            tbodyClientes.addEventListener('click', function(e) {
                if (e.target.closest('.btn-quitar-cliente')) {
                    const btn = e.target.closest('.btn-quitar-cliente');
                    document.getElementById('fila_cliente_' + btn.getAttribute('data-id')).remove();
                    if (tbodyClientes.querySelectorAll('tr[id^="fila_cliente_"]').length === 0) {
                        const filaVacia = document.getElementById('fila_vacia_clientes');
                        if (filaVacia) filaVacia.style.display = 'table-row';
                    }
                }
            });

            // --- 4. LÓGICA PARA CUENTAS BANCARIAS Y AUTOCOMPLETADO ---
            let indiceCuenta = 1;
            const contenedorCuentas = document.getElementById('contenedor_cuentas');
            const btnAgregarCuenta = document.getElementById('btnAgregarCuenta');

            contenedorCuentas.addEventListener('change', function(e) {
                if (e.target.classList.contains('check-usar-titular')) {
                    const fila = e.target.closest('.fila-cuenta');
                    const inputTitular = fila.querySelector('.input-titular-cuenta');

                    if (e.target.checked) {
                        const primerTr = document.querySelector('#tbody_clientes tr[id^="fila_cliente_"] .nombre-cliente-fila span');
                        if (primerTr) {
                            inputTitular.value = primerTr.innerText.trim();
                            inputTitular.setAttribute('readonly', true);
                            inputTitular.classList.add('bg-light', 'text-muted');
                        } else {
                            alert("Por favor, busca y selecciona a un cliente primero en el Paso 2.");
                            e.target.checked = false;
                        }
                    } else {
                        inputTitular.value = "";
                        inputTitular.removeAttribute('readonly');
                        inputTitular.classList.remove('bg-light', 'text-muted');
                    }
                }
            });

            btnAgregarCuenta.addEventListener('click', function() {
                const divRow = document.createElement('div');
                divRow.className = 'row fila-cuenta mb-3 bg-light p-2 rounded border mt-3';
                divRow.innerHTML = `
                    <div class="col-md-3">
                        <label class="form-label small fw-bold">Banco <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="cuentas[${indiceCuenta}][banco]" required placeholder="Ej. Azteca, Inbursa...">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-bold d-flex justify-content-between align-items-center">
                            <span>Nombre del Titular <span class="text-danger">*</span></span>
                            <div class="form-check form-switch mb-0" style="font-size: 0.85em;">
                                <input class="form-check-input check-usar-titular" type="checkbox" role="switch">
                                <label class="form-check-label text-muted ms-1">Usar cliente</label>
                            </div>
                        </label>
                        <input type="text" class="form-control input-titular-cuenta" name="cuentas[${indiceCuenta}][titular]" required placeholder="Nombre completo">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-bold">Número de Cuenta / CLABE <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="cuentas[${indiceCuenta}][cuenta]" required placeholder="18 dígitos o cuenta">
                    </div>
                    <div class="col-md-1 d-flex align-items-end">
                        <button type="button" class="btn btn-outline-danger w-100 btn-quitar-cuenta">
                            <i class="bi bi-trash-fill"></i>
                        </button>
                    </div>
                `;
                contenedorCuentas.appendChild(divRow);
                indiceCuenta++;
                actualizarBotonesQuitarCuenta();
            });

            contenedorCuentas.addEventListener('click', function(e) {
                if (e.target.closest('.btn-quitar-cuenta')) {
                    e.target.closest('.fila-cuenta').remove();
                    actualizarBotonesQuitarCuenta();
                }
            });

            function actualizarBotonesQuitarCuenta() {
                const botones = contenedorCuentas.querySelectorAll('.btn-quitar-cuenta');
                if (botones.length === 1) botones[0].disabled = true;
                else botones.forEach(btn => btn.disabled = false);
            }
            actualizarBotonesQuitarCuenta();

            // --- 5. VALIDACIÓN FINAL ANTES DE ENVIAR ---
            document.getElementById('formCredito').addEventListener('submit', function(e) {
                // Clonar el nombre del crédito al campo oculto de grupo por si el backend lo necesita
                document.getElementById('nombre_grupo').value = document.getElementById('nombre_credito').value;

                // Limpiar comas de todos los montos formateaados antes de enviarlos a la BD
                const montos = document.querySelectorAll('.monto-formateado');
                montos.forEach(input => {
                    input.value = input.value.replace(/,/g, '');
                });

                if (asesorHidden.value === '') {
                    e.preventDefault();
                    alert('Por favor, busca y selecciona un Asesor válido de la lista desplegable.');
                    asesorInput.focus();
                    return;
                }
                
                const clientesCount = tbodyClientes.querySelectorAll('tr[id^="fila_cliente_"]').length;
                if (clientesCount === 0) {
                    e.preventDefault();
                    alert('Debes buscar y agregar al menos un integrante al crédito antes de enviar la solicitud.');
                    inputBuscador.focus();
                }
            });
        });
    </script>
    @endpush
</x-app-layout>