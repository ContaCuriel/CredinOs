<x-app-layout>
    <div class="container-fluid py-4">
        
        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm" role="alert">
                <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="fw-bold mb-0 text-dark"><i class="bi bi-safe2-fill me-2 text-warning"></i>Dashboard de Tesorería (Fondeos)</h4>
                <p class="text-muted mb-0">Reporte de Desembolsos Autorizados - {{ \Carbon\Carbon::now()->isoFormat('dddd D \d\e MMMM \d\e YYYY') }}</p>
            </div>
            <div>
                <button class="btn btn-outline-success shadow-sm" onclick="window.print()">
                    <i class="bi bi-file-earmark-excel-fill me-1"></i> Exportar Reporte
                </button>
            </div>
        </div>

        @php
            $granTotalFondeo = 0;
        @endphp

        <div class="card shadow-sm border-0">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-bordered align-middle mb-0" style="font-size: 0.85rem;">
                        <thead class="bg-light text-center align-middle" style="border-bottom: 2px solid #333;">
                            <tr>
                                <th width="10%">Sucursal</th>
                                <th width="15%">Nombre Grupo / Cliente</th>
                                <th width="8%">Asesor</th>
                                <th width="6%">No. Clientes</th>
                                <th width="9%">Monto Autorizado</th>
                                <th width="7%">Moras/Multas</th>
                                <th width="8%">Comisión Nuevo</th>
                                <th width="8%">Pagos Pend.</th>
                                <th width="8%">Dev. Comision</th>
                                <th width="10%" class="bg-warning bg-opacity-10 fw-bold">Total Fondeo</th>
                                <th width="5%">Tasa</th>
                                <th width="6%">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($agrupados as $sucursal => $creditos)
                                @php
                                    $rowspan = $creditos->count();
                                @endphp

                                @foreach($creditos as $index => $credito)
                                    @php
                                        // Matemáticas de deducciones
                                        $esGrupal = $credito->grupo_id ? true : false;
                                        $nombrePrincipal = $credito->nombre_credito ?? ($credito->grupo->nombre_grupo ?? ($credito->cliente->nombre_completo ?? $credito->cliente->nombre));
                                        $numClientes = $esGrupal ? $credito->integrantes->count() : 1;
                                        
                                        $montoAuth = $credito->monto_aprobado;
                                        $comisionMonto = $montoAuth * ($credito->comision_apertura_aplicada / 100);
                                        
                                        // Retención de seguro y primer pago (puedes ajustar si en BD lo guardaste diferente)
                                        $deduccionesExtras = 0; 
                                        if($credito->garantia && !$credito->garantia->tiene_seguro) {
                                            $deduccionesExtras += $credito->producto->penalizacion_seguro;
                                        }
                                        if($credito->descuenta_primer_pago) {
                                            // Lógica simplificada para visualización (En producción podrías guardar el total_deducciones en el store)
                                            $deduccionesExtras += ($montoAuth / $credito->plazo_aprobado); 
                                        }

                                        $totalDeducciones = $comisionMonto + $deduccionesExtras;
                                        $totalFondeo = $montoAuth - $totalDeducciones;

                                        $granTotalFondeo += $totalFondeo;
                                    @endphp
                                    
                                    <tr>
                                        @if($index == 0)
                                            <td rowspan="{{ $rowspan }}" class="text-center fw-bold bg-light border-end-2">{{ mb_strtoupper($sucursal) }}</td>
                                        @endif
                                        
                                        <td class="fw-bold">
                                            {{ mb_strtoupper($nombrePrincipal) }}
                                            @if($credito->cuentasDesembolso->count() > 0)
                                                <div class="text-muted" style="font-size: 0.75rem; font-weight: normal;">Cta: {{ $credito->cuentasDesembolso->first()->numero_cuenta }} ({{ $credito->cuentasDesembolso->first()->banco }})</div>
                                            @else
                                                <div class="text-muted" style="font-size: 0.75rem; font-weight: normal;">*EFECTIVO EN CAJA*</div>
                                            @endif
                                        </td>
                                        <td class="text-center text-muted">{{ mb_strtoupper(explode(' ', $credito->asesor->nombre ?? 'N/A')[0]) }}</td>
                                        <td class="text-center fw-bold">{{ $numClientes }}</td>
                                        <td class="text-end text-dark">${{ number_format($montoAuth, 2) }}</td>
                                        <td class="text-end text-danger">$0.00</td> {{-- Aquí irían las moras de renovación --}}
                                        <td class="text-end text-danger">${{ number_format($comisionMonto, 2) }}</td>
                                        <td class="text-end text-danger">${{ number_format($deduccionesExtras, 2) }}</td> {{-- Seguros o 1er pago retenido --}}
                                        <td class="text-end text-success">$0.00</td>
                                        <td class="text-end fw-bold bg-warning bg-opacity-10 text-dark fs-6">${{ number_format($totalFondeo, 2) }}</td>
                                        <td class="text-center">{{ number_format($credito->tasa_interes_aplicada, 2) }}%</td>
                                        <td class="text-center">
                                            <form action="{{ route('creditos.fondear', $credito->id) }}" method="POST" onsubmit="return confirm('¿Confirmas que el dinero ha sido entregado/transferido al cliente?');">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-primary fw-bold w-100 shadow-sm" style="font-size: 0.75rem;">
                                                    <i class="bi bi-box-arrow-right me-1"></i> FONDEAR
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            @empty
                                <tr>
                                    <td colspan="12" class="text-center py-5 text-muted">
                                        <i class="bi bi-inbox fs-1 d-block mb-2"></i> No hay créditos pendientes de fondeo en este momento.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                        @if($granTotalFondeo > 0)
                        <tfoot class="bg-dark text-white">
                            <tr>
                                <td colspan="9" class="text-end fw-bold text-uppercase py-3">GRAN TOTAL A FONDEAR HOY:</td>
                                <td class="text-end fw-bold fs-5 py-3">${{ number_format($granTotalFondeo, 2) }}</td>
                                <td colspan="2"></td>
                            </tr>
                        </tfoot>
                        @endif
                    </table>
                </div>
            </div>
        </div>
        
    </div>

    @push('styles')
    <style>
        /* Estilos específicos para que al imprimir salga bonito como reporte */
        @media print {
            body { background-color: #fff !important; }
            .navbar, .sidebar, footer, .btn { display: none !important; }
            .card { border: none !important; box-shadow: none !important; }
            @page { size: landscape; margin: 1cm; }
        }
    </style>
    @endpush
</x-app-layout>