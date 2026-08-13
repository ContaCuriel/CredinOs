<x-app-layout>
    {{-- Librerías para el Buscador Inteligente de Clientes --}}
    @push('styles')
        <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
        <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
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

                <form action="{{ route('creditos.store') }}" method="POST" id="formCredito">
                    @csrf
                    
                    {{-- SECCIÓN 1: DATOS GENERALES --}}
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-body p-4">
                            <h6 class="fw-bold text-primary border-bottom pb-2 mb-3">1. Parámetros del Crédito</h6>
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label class="form-label fw-bold">Producto de Crédito <span class="text-danger">*</span></label>
                                    <select class="form-select" name="producto_id" id="producto_id" required>
                                        <option value="">Seleccione un producto...</option>
                                        @foreach($productos as $producto)
                                            <option value="{{ $producto->id }}" data-tipo="{{ $producto->tipo_credito }}" @selected(old('producto_id') == $producto->id)>
                                                {{ $producto->nombre }} ({{ ucfirst($producto->tipo_credito) }})
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label fw-bold">Asesor Responsable <span class="text-danger">*</span></label>
                                    <select class="form-select" name="asesor_id" required>
                                        <option value="">Seleccione al asesor...</option>
                                        @foreach($asesores as $asesor)
                                            <option value="{{ $asesor->id_empleado }}" @selected(old('asesor_id') == $asesor->id_empleado)>
                                                {{ $asesor->nombre_completo }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label fw-bold">Monto Total Solicitado ($) <span class="text-danger">*</span></label>
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
                                
                                {{-- SE OCULTA/MUESTRA CON JS SI EL PRODUCTO ES GRUPAL --}}
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
                            
                            <div class="row align-items-end mb-4 bg-light p-3 rounded">
                                <div class="col-md-8">
                                    <label class="form-label fw-bold">Buscar Cliente en el Sistema</label>
                                    <select class="form-select" id="buscador_clientes"></select>
                                </div>
                                <div class="col-md-4 text-end">
                                    <button type="button" class="btn btn-success w-100 fw-bold" id="btnAgregarCliente">
                                        <i class="bi bi-person-plus-fill me-1"></i> Agregar al Crédito
                                    </button>
                                </div>
                            </div>

                            <div class="table-responsive">
                                <table class="table table-hover align-middle border" id="tabla_clientes">
                                    <thead class="table-light">
                                        <tr>
                                            <th width="40%">Nombre del Cliente</th>
                                            <th width="30%">Monto a Recibir ($)</th>
                                            <th width="15%" class="text-center">¿Es la Líder?</th>
                                            <th width="15%" class="text-center">Quitar</th>
                                        </tr>
                                    </thead>
                                    <tbody id="tbody_clientes">
                                        {{-- Aquí se insertarán las filas dinámicamente --}}
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
                                {{-- Fila inicial por defecto --}}
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
    {{-- Dependencias de jQuery y Select2 (Necesarias para el buscador) --}}
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            
            // --- 1. LÓGICA DE PRODUCTO GRUPAL VS INDIVIDUAL ---
            const productoSelect = document.getElementById('producto_id');
            const divNombreGrupo = document.getElementById('div_nombre_grupo');
            const inputNombreGrupo = document.getElementById('nombre_grupo');

            function toggleGrupo() {
                const option = productoSelect.options[productoSelect.selectedIndex];
                const tipo = option.getAttribute('data-tipo');
                
                if (tipo === 'grupal') {
                    divNombreGrupo.style.display = 'block';
                    inputNombreGrupo.required = true;
                } else {
                    divNombreGrupo.style.display = 'none';
                    inputNombreGrupo.required = false;
                    inputNombreGrupo.value = ''; // Limpiamos
                }
            }

            productoSelect.addEventListener('change', toggleGrupo);
            toggleGrupo(); // Ejecutar al inicio

            // --- 2. LÓGICA DEL BUSCADOR DE CLIENTES (SELECT2) ---
            $('#buscador_clientes').select2({
                theme: 'bootstrap-5',
                placeholder: 'Escribe nombre, apellido o ID...',
                allowClear: true,
                ajax: {
                    url: '{{ route("web.clientes.search") }}', // Esta es la ruta que ya tienes en tu sistema
                    dataType: 'json',
                    delay: 250,
                    data: function (params) {
                        return {
                            term: params.term // El texto que el usuario escribe
                        };
                    },
                    processResults: function (data) {
                        return { results: data };
                    },
                    cache: true
                }
            });

            // --- 3. LÓGICA PARA AGREGAR/QUITAR CLIENTES A LA TABLA ---
            let indiceCliente = 0;
            const btnAgregarCliente = document.getElementById('btnAgregarCliente');
            const tbodyClientes = document.getElementById('tbody_clientes');
            const filaVacia = document.getElementById('fila_vacia_clientes');

            btnAgregarCliente.addEventListener('click', function() {
                const select = $('#buscador_clientes').select2('data');
                if (!select.length) {
                    alert('Por favor, busca y selecciona un cliente primero.');
                    return;
                }

                const clienteId = select[0].id;
                const clienteTexto = select[0].text;

                // Verificar que no se agregue dos veces
                if (document.getElementById('fila_cliente_' + clienteId)) {
                    alert('Este cliente ya está en la lista del crédito.');
                    return;
                }

                // Ocultar mensaje de tabla vacía
                if (filaVacia) filaVacia.style.display = 'none';

                // Determinar si es el primer líder
                const checkLider = (indiceCliente === 0) ? 'checked' : '';

                const tr = document.createElement('tr');
                tr.id = 'fila_cliente_' + clienteId;
                tr.innerHTML = `
                    <td class="fw-bold">
                        <i class="bi bi-person-circle text-muted me-2"></i> ${clienteTexto}
                        <input type="hidden" name="clientes[${indiceCliente}][id]" value="${clienteId}">
                    </td>
                    <td>
                        <div class="input-group input-group-sm">
                            <span class="input-group-text bg-white">$</span>
                            <input type="number" step="0.01" min="0" class="form-control" name="clientes[${indiceCliente}][monto]" required placeholder="0.00">
                        </div>
                    </td>
                    <td class="text-center">
                        <input class="form-check-input" type="radio" name="lider_id" value="${clienteId}" ${checkLider} required style="transform: scale(1.3);">
                    </td>
                    <td class="text-center">
                        <button type="button" class="btn btn-outline-danger btn-sm btn-quitar-cliente" data-id="${clienteId}">
                            <i class="bi bi-x-lg"></i>
                        </button>
                    </td>
                `;

                tbodyClientes.appendChild(tr);
                indiceCliente++;
                
                // Limpiar el buscador para el siguiente
                $('#buscador_clientes').val(null).trigger('change');
            });

            // Delegación de eventos para botón Quitar Cliente
            tbodyClientes.addEventListener('click', function(e) {
                if (e.target.closest('.btn-quitar-cliente')) {
                    const btn = e.target.closest('.btn-quitar-cliente');
                    const fila = document.getElementById('fila_cliente_' + btn.getAttribute('data-id'));
                    fila.remove();

                    if (tbodyClientes.querySelectorAll('tr[id^="fila_cliente_"]').length === 0) {
                        if (filaVacia) filaVacia.style.display = 'table-row';
                    }
                }
            });

            // --- 4. LÓGICA PARA CUENTAS BANCARIAS DINÁMICAS ---
            let indiceCuenta = 1; // Ya hay una (la 0)
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
                if (botones.length === 1) {
                    botones[0].disabled = true; // No permitir borrar la única cuenta
                } else {
                    botones.forEach(btn => btn.disabled = false);
                }
            }
            actualizarBotonesQuitarCuenta(); // Validar estado inicial

            // --- 5. VALIDAR AL ENVIAR QUE HAYA AL MENOS UN CLIENTE ---
            document.getElementById('formCredito').addEventListener('submit', function(e) {
                const clientesCount = tbodyClientes.querySelectorAll('tr[id^="fila_cliente_"]').length;
                if (clientesCount === 0) {
                    e.preventDefault();
                    alert('Debes agregar al menos un integrante al crédito antes de enviar la solicitud.');
                }
            });
        });
    </script>
    @endpush
</x-app-layout>