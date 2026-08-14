<x-app-layout>
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
                            <div class="row">
                                <div class="col-md-3 mb-3">
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
                                <div class="col-md-3 mb-3">
                                    <label class="form-label fw-bold">Producto <span class="text-danger">*</span></label>
                                    <select class="form-select" name="producto_id" id="producto_id" required>
                                        <option value="">Seleccione...</option>
                                        @foreach($productos as $producto)
                                            <option value="{{ $producto->id }}" data-tipo="{{ $producto->tipo_credito }}" @selected(old('producto_id') == $producto->id)>
                                                {{ $producto->nombre }} ({{ ucfirst($producto->tipo_credito) }})
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <label class="form-label fw-bold">Asesor <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="asesor_buscar" list="lista_asesores" placeholder="🔍 Buscar asesor..." required autocomplete="off" value="{{ old('asesor_texto_temporal') }}">
                                    <datalist id="lista_asesores">
                                        @foreach($asesores as $asesor)
                                            <option data-id="{{ $asesor->id_empleado }}" data-sucursal="{{ $asesor->id_sucursal }}" value="{{ $asesor->nombre_completo }} ({{ $asesor->sucursal->nombre_sucursal ?? 'Sin Sucursal' }})"></option>
                                        @endforeach
                                    </datalist>
                                    <input type="hidden" name="asesor_id" id="asesor_id" value="{{ old('asesor_id') }}" required>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <label class="form-label fw-bold">Monto ($) <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-white fw-bold">$</span>
                                        <input type="number" step="0.01" min="1" class="form-control form-control-lg text-success fw-bold" name="monto_solicitado" value="{{ old('monto_solicitado') }}" required placeholder="0.00">
                                    </div>
                                </div>
                            </div>

                            <div class="row mt-2">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-bold">Nombre del Crédito (Opcional)</label>
                                    <input type="text" class="form-control" name="nombre_credito" value="{{ old('nombre_credito') }}" placeholder="Ej. Ampliación de Negocio">
                                    <small class="text-muted">Útil para identificar proyectos específicos.</small>
                                </div>
                                
                                <div class="col-md-6 mb-3" id="div_nombre_grupo" style="display: none;">
                                    <label class="form-label fw-bold text-purple">Nombre del Grupo Solidario <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control border-purple" name="nombre_grupo" id="nombre_grupo" value="{{ old('nombre_grupo') }}" placeholder="Ej. Ajoloapan Zum">
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- SECCIÓN 2: INTEGRANTES DEL CRÉDITO --}}
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-body p-4">
                            <h6 class="fw-bold text-success border-bottom pb-2 mb-3">2. Integrantes (Clientes)</h6>
                            
                            {{-- BUSCADOR NATIVO TIPO GOOGLE --}}
                            <div class="row mb-4 bg-light p-3 rounded border">
                                <div class="col-md-12 position-relative">
                                    <label class="form-label fw-bold">Buscar Cliente en el Sistema</label>
                                    <input type="text" class="form-control form-control-lg border-success shadow-sm" id="buscador_clientes_input" placeholder="🔍 Escribe Nombre, Apellido o CURP (Mín. 3 letras)..." autocomplete="off">
                                    
                                    {{-- Contenedor flotante para los resultados --}}
                                    <div id="resultados_clientes" class="list-group position-absolute shadow-lg w-100 mt-1" style="z-index: 1050; display: none; max-height: 250px; overflow-y: auto;">
                                        {{-- Aquí se inyectan los resultados con JS --}}
                                    </div>
                                    
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
                                <div class="row fila-cuenta mb-3">
                                    <div class="col-md-3">
                                        <label class="form-label small fw-bold">Banco <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" name="cuentas[0][banco]" required placeholder="Ej. Azteca, Inbursa...">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label small fw-bold">Nombre del Titular <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" name="cuentas[0][titular]" required placeholder="Nombre completo">
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
            
            // --- 1. LÓGICA DEL BUSCADOR DE ASESORES Y SUCURSAL AUTO ---
            const asesorInput = document.getElementById('asesor_buscar');
            const asesorHidden = document.getElementById('asesor_id');
            const sucursalSelect = document.getElementById('sucursal_id');
            
            asesorInput.addEventListener('input', function(e) {
                const list = document.getElementById('lista_asesores').options;
                asesorHidden.value = ''; // Limpiamos si cambia el texto
                for (let i = 0; i < list.length; i++) {
                    if (list[i].value === e.target.value) {
                        asesorHidden.value = list[i].getAttribute('data-id');
                        
                        // Seleccionamos la sucursal automáticamente
                        const sucursalAsesor = list[i].getAttribute('data-sucursal');
                        if(sucursalAsesor) {
                            sucursalSelect.value = sucursalAsesor;
                        }
                        break;
                    }
                }
            });

            // --- 2. LÓGICA DE PRODUCTO GRUPAL VS INDIVIDUAL ---
            const productoSelect = document.getElementById('producto_id');
            const divNombreGrupo = document.getElementById('div_nombre_grupo');
            const inputNombreGrupo = document.getElementById('nombre_grupo');
            const columnaLiderHead = document.getElementById('columna_lider_head');
            let esIndividualGlobal = false;

            function toggleGrupo() {
                if(productoSelect.selectedIndex === 0) return;
                
                const option = productoSelect.options[productoSelect.selectedIndex];
                const tipo = option.getAttribute('data-tipo');
                
                if (tipo === 'grupal') {
                    divNombreGrupo.style.display = 'block';
                    inputNombreGrupo.required = true;
                    columnaLiderHead.style.display = 'table-cell';
                    esIndividualGlobal = false;
                } else {
                    divNombreGrupo.style.display = 'none';
                    inputNombreGrupo.required = false;
                    inputNombreGrupo.value = ''; 
                    columnaLiderHead.style.display = 'none';
                    esIndividualGlobal = true;
                }
                
                document.querySelectorAll('.col-lider-body').forEach(td => {
                    td.style.display = esIndividualGlobal ? 'none' : 'table-cell';
                });
            }

            productoSelect.addEventListener('change', toggleGrupo);
            toggleGrupo();

            // --- 3. NUEVO BUSCADOR DE CLIENTES NATIVO TIPO GOOGLE ---
            const inputBuscador = document.getElementById('buscador_clientes_input');
            const divResultados = document.getElementById('resultados_clientes');
            let timeoutId;
            let indiceCliente = 0;
            const tbodyClientes = document.getElementById('tbody_clientes');
            const filaVacia = document.getElementById('fila_vacia_clientes');

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
                                    // Al darle clic, ejecuta la función para agregarlo a la tabla
                                    a.addEventListener('click', () => agregarClienteATabla(cliente));
                                    divResultados.appendChild(a);
                                });
                            }
                            divResultados.style.display = 'block';
                        })
                        .catch(err => console.error(err));
                }, 300); // 300ms de retraso para no saturar la base de datos
            });

            // Ocultar resultados si das clic afuera
            document.addEventListener('click', function(e) {
                if (!inputBuscador.contains(e.target) && !divResultados.contains(e.target)) {
                    divResultados.style.display = 'none';
                }
            });

            // --- Función para agregar el cliente a la tabla al darle clic ---
            function agregarClienteATabla(cliente) {
                // Validaciones
                if (esIndividualGlobal && tbodyClientes.querySelectorAll('tr[id^="fila_cliente_"]').length >= 1) {
                    alert('Un crédito individual solo puede tener un cliente asignado.');
                    return;
                }
                if (document.getElementById('fila_cliente_' + cliente.id)) {
                    alert('Este cliente ya está en la lista de abajo.');
                    return;
                }

                if (filaVacia) filaVacia.style.display = 'none';

                const checkLider = (indiceCliente === 0) ? 'checked' : '';
                const displayLider = esIndividualGlobal ? 'none' : 'table-cell';

                const tr = document.createElement('tr');
                tr.id = 'fila_cliente_' + cliente.id;
                tr.innerHTML = `
                    <td class="fw-bold">
                        <i class="bi bi-person-circle text-muted me-2"></i> ${cliente.text}
                        <input type="hidden" name="clientes[${indiceCliente}][id]" value="${cliente.id}">
                    </td>
                    <td>
                        <div class="input-group input-group-sm">
                            <span class="input-group-text bg-white">$</span>
                            <input type="number" step="0.01" min="1" class="form-control border-success" name="clientes[${indiceCliente}][monto]" required placeholder="0.00">
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
                
                // Limpia el buscador y oculta la lista
                inputBuscador.value = '';
                divResultados.style.display = 'none';
            }

            // Eliminar fila de la tabla
            tbodyClientes.addEventListener('click', function(e) {
                if (e.target.closest('.btn-quitar-cliente')) {
                    const btn = e.target.closest('.btn-quitar-cliente');
                    document.getElementById('fila_cliente_' + btn.getAttribute('data-id')).remove();
                    if (tbodyClientes.querySelectorAll('tr[id^="fila_cliente_"]').length === 0) {
                        if (filaVacia) filaVacia.style.display = 'table-row';
                    }
                }
            });

            // --- 4. LÓGICA PARA CUENTAS BANCARIAS DINÁMICAS ---
            let indiceCuenta = 1;
            const contenedorCuentas = document.getElementById('contenedor_cuentas');
            const btnAgregarCuenta = document.getElementById('btnAgregarCuenta');

            btnAgregarCuenta.addEventListener('click', function() {
                const divRow = document.createElement('div');
                divRow.className = 'row fila-cuenta mb-3';
                divRow.innerHTML = `
                    <div class="col-md-3">
                        <input type="text" class="form-control" name="cuentas[${indiceCuenta}][banco]" required placeholder="Ej. Azteca, Inbursa...">
                    </div>
                    <div class="col-md-4">
                        <input type="text" class="form-control" name="cuentas[${indiceCuenta}][titular]" required placeholder="Nombre completo">
                    </div>
                    <div class="col-md-4">
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