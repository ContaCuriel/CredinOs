<x-app-layout>
    <div class="container-fluid py-4">

        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm mb-4" role="alert">
                <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if (session('error'))
            <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm mb-4" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-2"></i> {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        {{-- ENCABEZADO DEL TURNO --}}
        <div class="card border-0 shadow-sm mb-4 bg-dark text-white">
            <div class="card-body p-4 d-flex justify-content-between align-items-center">
                <div>
                    <h4 class="fw-bold mb-1"><i class="bi bi-safe2-fill text-warning me-2"></i> {{ $turnoActivo->caja->nombre }} (Turno Abierto)</h4>
                    <p class="mb-0 text-white-50">Cajero: {{ auth()->user()->name }} | Apertura: {{ \Carbon\Carbon::parse($turnoActivo->fecha_apertura)->format('d/m/Y h:i A') }}</p>
                </div>
                <div class="text-end">
                    <span class="d-block text-white-50 small text-uppercase fw-bold">Saldo Teórico Actual</span>
                    <h2 class="fw-bold mb-0 text-success">${{ number_format($turnoActivo->saldo_teorico, 2) }}</h2>
                </div>
            </div>
        </div>

        {{-- PESTAÑAS DE OPERACIÓN --}}
        <ul class="nav nav-tabs fw-bold mb-4" id="cajaTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active text-success" id="cobro-tab" data-bs-toggle="tab" data-bs-target="#cobro" type="button" role="tab">
                    <i class="bi bi-cash-coin me-1"></i> 1. Recibir Pago (Cobranza)
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link text-danger" id="gastos-tab" data-bs-toggle="tab" data-bs-target="#gastos" type="button" role="tab">
                    <i class="bi bi-cart-dash me-1"></i> 2. Registrar Gasto (Egreso)
                </button>
            </li>
            <li class="nav-item ms-auto" role="presentation">
                <button class="nav-link text-dark bg-light border" id="cierre-tab" data-bs-toggle="tab" data-bs-target="#cierre" type="button" role="tab">
                    <i class="bi bi-lock-fill me-1"></i> Cerrar Turno
                </button>
            </li>
        </ul>

        <div class="tab-content" id="cajaTabsContent">
            
            {{-- TAB 1: COBRANZA --}}
            <div class="tab-pane fade show active" id="cobro" role="tabpanel" tabindex="0">
                <div class="row">
                    <div class="col-md-5">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-header bg-white py-3">
                                <h6 class="fw-bold mb-0 text-dark"><i class="bi bi-search me-2 text-primary"></i>Buscar Crédito</h6>
                            </div>
                            <div class="card-body">
                                <label class="form-label small fw-bold">Seleccione el Cliente o Grupo:</label>
                                <select class="form-select form-select-lg mb-3" id="select_credito" autofocus>
                                    <option value="" selected disabled>Buscar por nombre o folio...</option>
                                    @foreach($creditos as $cred)
                                        @php
                                            $nombre = $cred->nombre_credito ?? ($cred->grupo->nombre_grupo ?? ($cred->cliente->nombre_completo ?? 'Sin Nombre'));
                                        @endphp
                                        <option value="{{ $cred->id }}">{{ mb_strtoupper($nombre) }} (Folio: {{ $cred->folio }})</option>
                                    @endforeach
                                </select>

                                {{-- ÁREA DINÁMICA: SE LLENA CON JS AL SELECCIONAR --}}
                                <div id="info_credito" class="d-none">
                                    
                                    {{-- ALERTA DE MORATORIOS (Se muestra solo si hay multas) --}}
                                    <div id="alerta_moratorios" class="alert alert-danger border-danger border-2 shadow-sm d-none mb-3">
                                        <i class="bi bi-exclamation-triangle-fill fs-5 me-2"></i>
                                        <strong>¡ATENCIÓN!</strong> Este crédito presenta atrasos.
                                    </div>

                                    <div class="p-3 bg-light rounded border mb-3">
                                        <div class="text-muted small fw-bold text-uppercase" id="txt_cuota_titulo">Próxima Cuota a Pagar</div>
                                        <h3 class="fw-bold text-dark mb-0" id="txt_cuota_monto">$0.00</h3>
                                        <p class="mb-0 text-muted small" id="txt_cuota_fecha">Vencimiento: --/--/----</p>
                                    </div>

                                    <button type="button" class="btn btn-outline-primary w-100 fw-bold" onclick="abrirEstadoCuenta()">
                                        <i class="bi bi-receipt-cutoff me-1"></i> Ver Estado de Cuenta Completo
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-7">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-header bg-white py-3">
                                <h6 class="fw-bold mb-0 text-dark"><i class="bi bi-calculator me-2 text-success"></i>Procesar Pago</h6>
                            </div>
                            <div class="card-body text-center" id="panel_cobro_vacio">
                                <i class="bi bi-arrow-left-circle fs-1 text-muted d-block mt-5 mb-3"></i>
                                <h5 class="text-muted">Selecciona un crédito primero</h5>
                            </div>

                            {{-- FORMULARIO DE COBRO (Oculto hasta seleccionar crédito) --}}
                            <div class="card-body d-none" id="panel_cobro_activo">
                                <form action="{{ route('cajas.cobrar') }}" method="POST" id="form_cobro">
                                    @csrf
                                    <input type="hidden" name="credito_id" id="input_credito_id">

                                    {{-- SWITCH Y LISTA DE DESGLOSE (Ocultos por defecto) --}}
                                    <div id="div_toggle_desglose" class="d-none mb-3 text-start">
                                        <div class="form-check form-switch p-3 bg-light border border-warning rounded shadow-sm">
                                            <input class="form-check-input ms-1 me-2" type="checkbox" role="switch" id="chk_desglose" style="transform: scale(1.3);">
                                            <label class="form-check-label fw-bold ms-2 text-dark" for="chk_desglose">
                                                Desglosar Pago por Integrante <span class="badge bg-warning text-dark ms-1">Pago Incompleto</span>
                                            </label>
                                        </div>
                                    </div>

                                    <div id="div_lista_integrantes" class="d-none mb-4 text-start">
                                        <label class="form-label small fw-bold text-primary"><i class="bi bi-people-fill me-1"></i>Aportación Individual Esperada</label>
                                        <div class="border rounded p-2 bg-light shadow-sm" id="contenedor_integrantes" style="max-height: 250px; overflow-y: auto;">
                                            <!-- Aquí JS inyectará a las señoras -->
                                        </div>
                                    </div>

                                    <div class="row mb-4 text-start">
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label small fw-bold">Monto Total a Recibir ($)</label>
                                            <input type="number" step="0.01" class="form-control form-control-lg text-success fw-bold" name="monto_recibido" id="input_monto_recibido" required>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label small fw-bold">Método de Pago</label>
                                            <select class="form-select form-select-lg" name="metodo_pago" id="select_metodo_pago" onchange="toggleReferenciaTerminal()" required>
                                                <option value="efectivo" selected>💵 Efectivo (Suma a Caja)</option>
                                                <option value="transferencia">🏦 Transferencia / SPEI</option>
                                                <option value="terminal">💳 Terminal Bancaria (Tarjeta)</option>
                                            </select>
                                        </div>
                                        <div class="col-12 mb-3 d-none" id="div_referencia_terminal">
                                            <label class="form-label small fw-bold text-primary">Folio / No. de Autorización de la Terminal</label>
                                            <input type="text" class="form-control border-primary" name="referencia_pago" placeholder="Ej. AUT-987654">
                                        </div>
                                    </div>

                                    <hr>

                                    <button type="submit" class="btn btn-success btn-lg w-100 fw-bold shadow">
                                        <i class="bi bi-check2-circle me-1"></i> Aplicar Pago y Generar Ticket
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- TAB 2: EGRESOS (GASTOS) --}}
            <div class="tab-pane fade" id="gastos" role="tabpanel" tabindex="0">
                <div class="row justify-content-center">
                    <div class="col-md-6">
                        <div class="card border-0 shadow-sm mt-3">
                            <div class="card-body p-4">
                                <h5 class="fw-bold mb-4 text-danger"><i class="bi bi-box-arrow-up-right me-2"></i>Salida de Efectivo</h5>
                                <form action="{{ route('cajas.gasto') }}" method="POST">
                                    @csrf
                                    <div class="mb-3">
                                        <label class="form-label small fw-bold">Concepto del Gasto</label>
                                        <input type="text" class="form-control" name="concepto" placeholder="Ej. Compra de hojas blancas, garrafón de agua..." required>
                                    </div>
                                    <div class="mb-4">
                                        <label class="form-label small fw-bold">Monto a Retirar ($)</label>
                                        <input type="number" step="0.01" class="form-control text-danger fw-bold" name="monto" required>
                                    </div>
                                    <button type="submit" class="btn btn-danger w-100 fw-bold shadow-sm">
                                        Registrar Salida y Restar de Caja
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- TAB 3: CIERRE DE CAJA --}}
            <div class="tab-pane fade" id="cierre" role="tabpanel" tabindex="0">
                <div class="row justify-content-center">
                    <div class="col-md-6">
                        <div class="card border-0 shadow-sm mt-3 border-top border-dark border-4">
                            <div class="card-body p-4 text-center">
                                <h4 class="fw-bold mb-2">Corte de Caja (Fin de Turno)</h4>
                                <p class="text-muted mb-4">El sistema espera que tengas esta cantidad en tu cajón.</p>
                                
                                <h1 class="display-5 fw-bold text-success mb-4">${{ number_format($turnoActivo->saldo_teorico, 2) }}</h1>
                                
                                <form action="{{ route('cajas.cerrar') }}" method="POST">
                                    @csrf
                                    <div class="mb-4 text-start">
                                        <label class="form-label small fw-bold">Efectivo Físico Real (Cuéntalo)</label>
                                        <input type="number" step="0.01" class="form-control form-control-lg text-center fw-bold font-monospace" name="saldo_fisico" placeholder="0.00" required>
                                        <div class="form-text small">Si la cantidad es diferente al saldo teórico, se registrará un faltante o sobrante.</div>
                                    </div>
                                    <button type="submit" class="btn btn-dark btn-lg w-100 fw-bold shadow-sm" onclick="return confirm('¿Estás seguro de cerrar tu turno? Esta acción no se puede deshacer.')">
                                        <i class="bi bi-lock-fill me-2"></i> Cerrar Caja Definitivamente
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>

    {{-- MODAL DEL ESTADO DE CUENTA (IFRAME) --}}
    <div class="modal fade" id="modalEstadoCuenta" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content shadow-lg border-0">
                <div class="modal-header bg-dark text-white">
                    <h5 class="modal-title fw-bold"><i class="bi bi-receipt me-2"></i>Estado de Cuenta</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-0" style="height: 75vh;">
                    {{-- Aquí cargaremos la ruta creditos.show dinámicamente --}}
                    <iframe id="iframe_estado_cuenta" src="" style="width: 100%; height: 100%; border: none;"></iframe>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        const creditosActivos = @json($creditos);

        document.getElementById('select_credito').addEventListener('change', function() {
            const creditoId = this.value;
            const credito = creditosActivos.find(c => c.id == creditoId);
            
            if(credito) {
                document.getElementById('panel_cobro_vacio').classList.add('d-none');
                document.getElementById('panel_cobro_activo').classList.remove('d-none');
                document.getElementById('info_credito').classList.remove('d-none');
                
                document.getElementById('input_credito_id').value = credito.id;
                const proximaCuota = credito.amortizaciones[0];

                if(proximaCuota) {
                    // 1. Mostrar Monto y Número de Semana
                    document.getElementById('txt_cuota_titulo').innerText = 'Próxima Cuota (Pago #' + proximaCuota.numero_cuota + ')';
                    document.getElementById('txt_cuota_monto').innerText = '$' + parseFloat(proximaCuota.total_cuota).toLocaleString('en-US', {minimumFractionDigits: 2});
                    
                    let inputMonto = document.getElementById('input_monto_recibido');
                    inputMonto.value = proximaCuota.total_cuota;
                    
                    // =========================================================
                    // 2. PARSEO DE FECHA BLINDADO EXTREMO (Adiós Invalid Date)
                    // =========================================================
                    let fpStr = String(proximaCuota.fecha_pago);
                    let anio, mes, dia;
                    let isAtrasado = false;
                    let textoFecha = '--/--/----';

                    // Intentar formato YYYY-MM-DD
                    let matchYMD = fpStr.match(/^(\d{4})-(\d{2})-(\d{2})/);
                    // Intentar formato DD/MM/YYYY
                    let matchDMY = fpStr.match(/^(\d{2})\/(\d{2})\/(\d{4})/);

                    if (matchYMD) {
                        anio = parseInt(matchYMD[1]);
                        mes = parseInt(matchYMD[2]) - 1; // JS cuenta meses del 0 al 11
                        dia = parseInt(matchYMD[3]);
                    } else if (matchDMY) {
                        dia = parseInt(matchDMY[1]);
                        mes = parseInt(matchDMY[2]) - 1;
                        anio = parseInt(matchDMY[3]);
                    }

                    if (anio !== undefined) {
                        const fechaObj = new Date(anio, mes, dia);
                        let d = String(fechaObj.getDate()).padStart(2, '0');
                        let m = String(fechaObj.getMonth() + 1).padStart(2, '0');
                        let y = fechaObj.getFullYear();
                        textoFecha = d + '/' + m + '/' + y;

                        const hoy = new Date();
                        hoy.setHours(0,0,0,0);
                        if (fechaObj < hoy) {
                            isAtrasado = true;
                        }
                    } else {
                        textoFecha = fpStr; // Por si llega algo ultra raro
                    }

                    document.getElementById('txt_cuota_fecha').innerText = 'Vencimiento: ' + textoFecha;

                    if (isAtrasado) {
                        document.getElementById('alerta_moratorios').classList.remove('d-none');
                    } else {
                        document.getElementById('alerta_moratorios').classList.add('d-none');
                    }
                    // =========================================================

                    // 3. LÓGICA DE DESGLOSE INDIVIDUAL (Grupos)
                    const chkDesglose = document.getElementById('chk_desglose');
                    const divToggle = document.getElementById('div_toggle_desglose');
                    const divLista = document.getElementById('div_lista_integrantes');
                    const contenedorIntegrantes = document.getElementById('contenedor_integrantes');

                    chkDesglose.checked = false;
                    divLista.classList.add('d-none');
                    contenedorIntegrantes.innerHTML = '';
                    inputMonto.readOnly = false;

                    if (credito.integrantes && credito.integrantes.length > 1) {
                        divToggle.classList.remove('d-none');
                        
                        let totalAprobado = parseFloat(credito.monto_aprobado) || parseFloat(credito.monto_solicitado) || 1;
                        let cuotaGlobal = parseFloat(proximaCuota.total_cuota);

                        credito.integrantes.forEach(integrante => {
                            let montoInd = parseFloat(integrante.pivot.monto_individual) || 0;
                            let cuotaInd = (montoInd / totalAprobado) * cuotaGlobal;
                            let idCliente = integrante.id_cliente || integrante.id;

                            contenedorIntegrantes.innerHTML += `
                                <div class="d-flex justify-content-between align-items-center mb-2 border-bottom pb-2">
                                    <div class="pe-2">
                                        <span class="d-block fw-bold small text-dark">${integrante.nombre} ${integrante.apellido_paterno}</span>
                                        <span class="text-muted" style="font-size: 0.75em;">Cuota esperada: $${cuotaInd.toFixed(2)}</span>
                                    </div>
                                    <div class="input-group input-group-sm" style="width: 130px;">
                                        <span class="input-group-text bg-white">$</span>
                                        <input type="number" step="0.01" class="form-control fw-bold text-success input-cuota-ind" name="pagos_individuales[${idCliente}]" value="${cuotaInd.toFixed(2)}" disabled>
                                    </div>
                                </div>
                            `;
                        });

                        chkDesglose.onchange = function() {
                            const inputs = document.querySelectorAll('.input-cuota-ind');
                            if (this.checked) {
                                divLista.classList.remove('d-none');
                                inputMonto.readOnly = true; 
                                inputs.forEach(inp => {
                                    inp.disabled = false; 
                                    inp.addEventListener('input', sumarCantidades);
                                });
                                sumarCantidades();
                            } else {
                                divLista.classList.add('d-none');
                                inputMonto.readOnly = false;
                                inputs.forEach(inp => inp.disabled = true); 
                                inputMonto.value = proximaCuota.total_cuota;
                            }
                        };

                        function sumarCantidades() {
                            let totalAcumulado = 0;
                            document.querySelectorAll('.input-cuota-ind').forEach(i => {
                                totalAcumulado += parseFloat(i.value) || 0;
                            });
                            inputMonto.value = totalAcumulado.toFixed(2);
                        }
                    } else {
                        divToggle.classList.add('d-none');
                    }
                }
            }
        });

        function toggleReferenciaTerminal() {
            const metodo = document.getElementById('select_metodo_pago').value;
            const divRef = document.getElementById('div_referencia_terminal');
            
            if(metodo === 'terminal') {
                divRef.classList.remove('d-none');
                divRef.querySelector('input').setAttribute('required', 'true');
            } else {
                divRef.classList.add('d-none');
                divRef.querySelector('input').removeAttribute('required');
            }
        }

        function abrirEstadoCuenta() {
            const creditoId = document.getElementById('input_credito_id').value;
            if(!creditoId) return;

            const urlBase = "{{ route('creditos.show', ':id') }}";
            const urlFinal = urlBase.replace(':id', creditoId);

            document.getElementById('iframe_estado_cuenta').src = urlFinal;
            const modal = new bootstrap.Modal(document.getElementById('modalEstadoCuenta'));
            modal.show();
        }
    </script>
    @endpush
</x-app-layout>