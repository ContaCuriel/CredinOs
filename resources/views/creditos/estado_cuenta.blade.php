<x-app-layout>
    <div class="container-fluid py-4">

        {{-- ENCABEZADO --}}
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                @php
                    $lider = $credito->integrantes->where('pivot.es_lider', true)->first();
                    $nombreTitular = $lider ? ($lider->nombre_completo ?? $lider->nombre . ' ' . $lider->apellido_paterno) : ($credito->cliente->nombre_completo ?? $credito->cliente->nombre ?? 'SIN ASIGNAR');
                    $nombrePrincipal = $credito->nombre_credito ?? ($credito->grupo->nombre_grupo ?? $nombreTitular);
                @endphp
                <h4 class="fw-bold mb-1 text-dark">
                    <i class="bi bi-receipt me-2 text-primary"></i>Estado de Cuenta: {{ mb_strtoupper($nombrePrincipal) }}
                </h4>
                <p class="text-muted font-monospace mb-0">Folio del Crédito: <b>{{ $credito->folio }}</b> | Fondeado el: {{ \Carbon\Carbon::parse($credito->fecha_desembolso)->format('d/m/Y') }}</p>
            </div>
            <div>
                <a href="{{ route('cartera.index') }}" class="btn btn-outline-secondary shadow-sm me-2">
                    <i class="bi bi-arrow-left me-1"></i> Regresar
                </a>
                <button class="btn btn-success fw-bold shadow-sm" data-bs-toggle="modal" data-bs-target="#modalRegistrarPago">
                    <i class="bi bi-cash-coin me-1"></i> Registrar Pago
                </button>
            </div>
        </div>

        {{-- TARJETAS DE RESUMEN --}}
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card border-0 shadow-sm border-bottom border-primary border-4 h-100">
                    <div class="card-body">
                        <div class="text-muted small fw-bold text-uppercase mb-1">Monto Desembolsado</div>
                        <h3 class="fw-bold text-dark mb-0">${{ number_format($credito->monto_aprobado, 2) }}</h3>
                        <div class="small text-muted mt-1">{{ $credito->plazo_aprobado }} Cuotas {{ ucfirst($credito->producto->frecuencia_pago) }}s</div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm border-bottom border-success border-4 h-100">
                    <div class="card-body">
                        <div class="text-muted small fw-bold text-uppercase mb-1">Total Pagado</div>
                        @php
                            $totalPagado = $credito->amortizaciones->sum('monto_pagado') ?? 0;
                            $porcentaje = $credito->monto_aprobado > 0 ? ($totalPagado / ($credito->amortizaciones->sum('total_cuota'))) * 100 : 0;
                        @endphp
                        <h3 class="fw-bold text-success mb-0">${{ number_format($totalPagado, 2) }}</h3>
                        <div class="progress mt-2" style="height: 6px;">
                            <div class="progress-bar bg-success" role="progressbar" style="width: {{ $porcentaje }}%;"></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                @php
                    $cuotaProxima = $credito->amortizaciones->where('estatus', 'pendiente')->first();
                    $esAtrasado = $cuotaProxima && \Carbon\Carbon::parse($cuotaProxima->fecha_pago)->isPast();
                @endphp
                <div class="card border-0 shadow-sm border-bottom border-{{ $esAtrasado ? 'danger' : 'info' }} border-4 h-100">
                    <div class="card-body">
                        <div class="text-muted small fw-bold text-uppercase mb-1">Próximo Vencimiento</div>
                        <h3 class="fw-bold text-{{ $esAtrasado ? 'danger' : 'dark' }} mb-0">
                            ${{ number_format($cuotaProxima->total_cuota ?? 0, 2) }}
                        </h3>
                        <div class="small fw-bold text-{{ $esAtrasado ? 'danger' : 'muted' }} mt-1">
                            @if($cuotaProxima)
                                <i class="bi bi-calendar-event me-1"></i> {{ \Carbon\Carbon::parse($cuotaProxima->fecha_pago)->format('d/m/Y') }}
                                @if($esAtrasado) (ATRASADO) @endif
                            @else
                                CRÉDITO LIQUIDADO
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm border-bottom border-warning border-4 h-100 bg-light">
                    <div class="card-body">
                        <div class="text-muted small fw-bold text-uppercase mb-1">Estatus del Crédito</div>
                        @if($esAtrasado)
                            <h4 class="fw-bold text-danger mb-0"><i class="bi bi-exclamation-triangle-fill me-1"></i> EN MORA</h4>
                            <div class="small text-danger mt-1">Requiere gestión de cobranza</div>
                        @else
                            <h4 class="fw-bold text-success mb-0"><i class="bi bi-shield-check me-1"></i> AL CORRIENTE</h4>
                            <div class="small text-muted mt-1">Pagos al día</div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            {{-- LISTA DE INTEGRANTES Y MONTOS (Lado Izquierdo) --}}
            <div class="col-lg-4 mb-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-white py-3">
                        <h6 class="fw-bold text-dark mb-0"><i class="bi bi-people-fill me-2 text-primary"></i>Integrantes y Montos</h6>
                    </div>
                    <div class="card-body p-0">
                        <ul class="list-group list-group-flush">
                            @foreach($credito->integrantes as $integrante)
                            <li class="list-group-item d-flex justify-content-between align-items-center py-3">
                                <div>
                                    <div class="fw-bold" style="font-size: 0.9rem;">{{ mb_strtoupper($integrante->nombre_completo ?? $integrante->nombre . ' ' . $integrante->apellido_paterno) }}</div>
                                    <div class="small text-muted">
                                        @if($integrante->pivot->es_lider)
                                            <span class="badge bg-warning text-dark px-1 py-0 me-1">Líder</span>
                                        @endif
                                        {{ $integrante->telefono_celular ?? 'Sin Teléfono' }}
                                    </div>
                                </div>
                                <div class="fw-bold text-success">
                                    ${{ number_format($integrante->pivot->monto_individual, 2) }}
                                </div>
                            </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>

            {{-- SISTEMA DE PESTAÑAS DERECHO --}}
            <div class="col-lg-8">
                
                {{-- Navegación de Pestañas --}}
                <ul class="nav nav-tabs fw-bold mb-3 border-bottom-0" id="estadoCuentaTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active text-primary border border-bottom-0 bg-white" id="amortizacion-tab" data-bs-toggle="tab" data-bs-target="#amortizacion" type="button" role="tab">
                            <i class="bi bi-calendar3 me-1"></i> Control Semanal (Amortización)
                        </button>
                    </li>
                    <li class="nav-item ms-1" role="presentation">
                        <button class="nav-link text-success bg-light border border-bottom-0" id="historial-tab" data-bs-toggle="tab" data-bs-target="#historial" type="button" role="tab">
                            <i class="bi bi-clock-history me-1"></i> Radiografía Forense (Tickets)
                        </button>
                    </li>
                </ul>

                <div class="tab-content bg-white border border-top-0 rounded-bottom shadow-sm" id="estadoCuentaTabsContent">
                    
                    {{-- ========================================================================= --}}
                    {{-- PESTAÑA 1: TABLA DE AMORTIZACIÓN --}}
                    {{-- ========================================================================= --}}
                    <div class="tab-pane fade show active p-0" id="amortizacion" role="tabpanel">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0" style="font-size: 0.85rem;">
                                <thead class="bg-light">
                                    <tr>
                                        <th class="ps-3 text-center">No.</th>
                                        <th>Vencimiento</th>
                                        <th class="text-end">Cuota Fija</th>
                                        <th class="text-end">Moratorios</th>
                                        <th class="text-end">Pagó / Abono</th>
                                        <th class="text-center">Diferencia</th>
                                        <th class="text-center">Fecha Pago</th>
                                        <th class="text-center pe-3">Estatus</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php
                                        // Variables para los totales al final de la tabla
                                        $sumaFaltantes = 0;
                                        $sumaMoratorios = 0;
                                        $sumaAbonos = 0;
                                        $sumaDiferencias = 0;
                                    @endphp
                                    
                                    @foreach($credito->amortizaciones as $cuota)
                                        @php
                                            $fechaVencimiento = \Carbon\Carbon::parse($cuota->fecha_pago);
                                            $estaAtrasada = $fechaVencimiento->isPast() && $cuota->estatus != 'pagado';
                                            
                                            $montoPagado = $cuota->monto_pagado ?? 0;
                                            $moratorios = $cuota->moratorios_generados ?? 0; 
                                            
                                            // 🔥 CORRECCIÓN: La diferencia ahora es SOLO sobre la cuota normal
                                            $diferencia = $cuota->estatus === 'pendiente' ? 0 : ($montoPagado - $cuota->total_cuota);

                                            // Sumatorias
                                            if ($cuota->estatus != 'pagado') {
                                                $sumaFaltantes += ($cuota->total_cuota - $montoPagado);
                                            }
                                            $sumaMoratorios += $moratorios;
                                            $sumaAbonos += $montoPagado;
                                            $sumaDiferencias += $diferencia;
                                        @endphp
                                        <tr class="{{ $estaAtrasada ? 'bg-danger bg-opacity-10' : '' }}">
                                            <td class="ps-3 text-center fw-bold">{{ $cuota->numero_cuota }}</td>
                                            <td class="fw-bold {{ $estaAtrasada ? 'text-danger' : 'text-dark' }}">
                                                {{ $fechaVencimiento->format('d/m/Y') }}
                                            </td>
                                            <td class="text-end text-dark fw-bold">${{ number_format($cuota->total_cuota, 2) }}</td>
                                            
                                            <td class="text-end text-danger fw-bold">
                                                ${{ number_format($moratorios, 2) }}
                                            </td>

                                            <td class="text-end fw-bold {{ $montoPagado > 0 ? 'text-success' : 'text-muted' }}">
                                                ${{ number_format($montoPagado, 2) }}
                                            </td>

                                            <td class="text-center">
                                                @if($cuota->estatus == 'pagado' || $cuota->estatus == 'parcial')
                                                    @if($diferencia > 0)
                                                        <span class="text-success small fw-bold">+${{ number_format($diferencia, 2) }}</span>
                                                    @elseif($diferencia < 0)
                                                        <span class="text-danger small fw-bold">-${{ number_format(abs($diferencia), 2) }}</span>
                                                    @else
                                                        <span class="text-muted small"><i class="bi bi-check2"></i> Exacto</span>
                                                    @endif
                                                @else
                                                    -
                                                @endif
                                            </td>
                                            
                                            {{-- 🔥 CORRECCIÓN: Columna de fecha devuelta con hora --}}
                                            <td class="text-center text-muted small">
                                                @if($cuota->fecha_pago_real)
                                                    {{ \Carbon\Carbon::parse($cuota->fecha_pago_real)->format('d/m/y h:i A') }}
                                                @else
                                                    --/--
                                                @endif
                                            </td>

                                            <td class="text-center pe-3">
                                                @if($cuota->estatus == 'pagado')
                                                    <span class="badge bg-success w-100 mb-1">Pagado</span>
                                                    <a href="{{ route('cajas.ticket_cuota', $cuota->id) }}" target="_blank" class="btn btn-sm btn-outline-dark w-100" style="font-size: 0.7rem; padding: 2px;">
                                                        <i class="bi bi-printer-fill"></i> Ticket
                                                    </a>
                                                @elseif($cuota->estatus == 'parcial')
                                                    <span class="badge bg-warning text-dark w-100 mb-1">Incompleto</span>
                                                    <a href="{{ route('cajas.ticket_cuota', $cuota->id) }}" target="_blank" class="btn btn-sm btn-outline-dark w-100" style="font-size: 0.7rem; padding: 2px;">
                                                        <i class="bi bi-printer-fill"></i> Ticket
                                                    </a>
                                                @elseif($estaAtrasada)
                                                    <span class="badge bg-danger w-100">Atrasado</span>
                                                @else
                                                    <span class="badge bg-secondary w-100">Pendiente</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                                
                                {{-- 🔥 NUEVO: FILA DE TOTALES --}}
                                <tfoot class="bg-dark text-white">
                                    <tr>
                                        <td colspan="2" class="text-end fw-bold">TOTALES:</td>
                                        <td class="text-end fw-bold text-warning">
                                            <span class="d-block small text-white-50" style="font-size: 0.7rem;">Deuda Restante:</span>
                                            ${{ number_format($sumaFaltantes, 2) }}
                                        </td>
                                        <td class="text-end fw-bold text-danger">
                                            <span class="d-block small text-white-50" style="font-size: 0.7rem;">Suma Multas:</span>
                                            ${{ number_format($sumaMoratorios, 2) }}
                                        </td>
                                        <td class="text-end fw-bold text-success">
                                            <span class="d-block small text-white-50" style="font-size: 0.7rem;">Suma Abonos:</span>
                                            ${{ number_format($sumaAbonos, 2) }}
                                        </td>
                                        <td class="text-center fw-bold">
                                            @if($sumaDiferencias < 0)
                                                <span class="text-danger">-${{ number_format(abs($sumaDiferencias), 2) }}</span>
                                            @else
                                                <span class="text-success">+${{ number_format($sumaDiferencias, 2) }}</span>
                                            @endif
                                        </td>
                                        <td colspan="2"></td>
                                    </tr>
                                </tfoot>
                                
                            </table>
                        </div>
                    </div>

                    {{-- ========================================================================= --}}
                    {{-- PESTAÑA 2: RADIOGRAFÍA FORENSE (LOS TICKETS FÍSICOS) --}}
                    {{-- ========================================================================= --}}
                    <div class="tab-pane fade p-3" id="historial" role="tabpanel">
                        <div class="alert alert-light border shadow-sm mb-3">
                            <i class="bi bi-info-circle-fill text-primary me-2"></i> 
                            Aquí se muestra <strong>cada movimiento o billete ingresado</strong> a la caja asociado a este crédito, con su hora exacta, monto y botón para reimprimir el ticket original entregado al cliente.
                        </div>

                        @if($transacciones && $transacciones->count() > 0)
                            <div class="table-responsive">
                                <table class="table table-bordered table-hover align-middle" style="font-size: 0.85rem;">
                                    <thead class="table-dark">
                                        <tr>
                                            <th class="text-center">Fecha y Hora Exacta</th>
                                            <th>Cajero</th>
                                            <th>Concepto (Semana / Multa)</th>
                                            <th class="text-center">Método</th>
                                            <th class="text-end">Monto Real</th>
                                            <th class="text-center">Acción</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($transacciones as $tx)
                                            <tr>
                                                <td class="text-center fw-bold">
                                                    {{ \Carbon\Carbon::parse($tx->created_at)->format('d/m/Y') }}<br>
                                                    <span class="text-muted small">{{ \Carbon\Carbon::parse($tx->created_at)->format('h:i:s A') }}</span>
                                                </td>
                                                <td class="text-muted small">{{ mb_strtoupper($tx->nombre_cajero) }}</td>
                                                <td class="fw-bold text-dark">{{ mb_strtoupper($tx->concepto) }}</td>
                                                <td class="text-center"><span class="badge bg-secondary">{{ mb_strtoupper($tx->metodo_pago) }}</span></td>
                                                <td class="text-end fw-bold text-success">${{ number_format($tx->monto, 2) }}</td>
                                                <td class="text-center">
                                                    {{-- Usamos la ruta original del ticket físico que creaste hace rato --}}
                                                    <a href="{{ route('cajas.ticket', $tx->id) }}" target="_blank" class="btn btn-sm btn-outline-dark fw-bold" style="padding: 2px 8px;">
                                                        <i class="bi bi-printer-fill me-1"></i> Reimprimir
                                                    </a>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="text-center py-5">
                                <i class="bi bi-inbox fs-1 text-muted"></i>
                                <h5 class="text-muted mt-3">Aún no hay tickets registrados</h5>
                                <p class="small text-muted">Cuando el cliente realice su primer pago, los recibos aparecerán aquí.</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

    </div>

    {{-- SCRIPTS PARA CONTROLAR LOS COLORES DE LAS PESTAÑAS --}}
    @push('scripts')
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const tabs = document.querySelectorAll('button[data-bs-toggle="tab"]');
            tabs.forEach(tab => {
                tab.addEventListener('shown.bs.tab', function (event) {
                    tabs.forEach(t => t.classList.remove('bg-white'));
                    tabs.forEach(t => t.classList.add('bg-light'));
                    event.target.classList.remove('bg-light');
                    event.target.classList.add('bg-white');
                });
            });
        });
    </script>
    @endpush
</x-app-layout>