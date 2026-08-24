<x-app-layout>
    <div class="container-fluid py-4">
        
        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm mb-4" role="alert">
                <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="fw-bold mb-0 text-dark"><i class="bi bi-wallet-fill me-2 text-success"></i>Cartera Activa</h4>
                <p class="text-muted mb-0">Créditos vigentes y en proceso de cobro</p>
            </div>
        </div>

        {{-- BARRA DE BÚSQUEDA Y FILTROS --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body p-3 bg-white">
                <form action="{{ route('cartera.index') }}" method="GET" class="row g-2 align-items-end">
                    <div class="col-md-5">
                        <label class="form-label small fw-bold text-muted mb-1"><i class="bi bi-search me-1"></i> Nombre Cliente / Grupo</label>
                        <input type="text" class="form-control form-control-sm" name="nombre" placeholder="Buscar por cliente o grupo..." value="{{ request('nombre') }}">
                    </div>
                    
                    <div class="col-md-5">
                        <label class="form-label small fw-bold text-muted mb-1"><i class="bi bi-building me-1"></i> Sucursal</label>
                        <select class="form-select form-select-sm" name="sucursal_id">
                            <option value="">Todas las Sucursales</option>
                            @foreach($sucursales ?? [] as $sucursal)
                                <option value="{{ $sucursal->id_sucursal }}" {{ request('sucursal_id') == $sucursal->id_sucursal ? 'selected' : '' }}>
                                    {{ $sucursal->nombre_sucursal }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-2 d-flex gap-1">
                        <button type="submit" class="btn btn-sm btn-success fw-bold w-100 shadow-sm">
                            <i class="bi bi-filter me-1"></i> Filtrar
                        </button>
                        @if(request()->hasAny(['nombre', 'sucursal_id']))
                            <a href="{{ route('cartera.index') }}" class="btn btn-sm btn-outline-secondary shadow-sm" title="Limpiar Filtros">
                                <i class="bi bi-x-circle"></i>
                            </a>
                        @endif
                    </div>
                </form>
            </div>
        </div>

        {{-- TABLA DE CRÉDITOS ACTIVOS --}}
        <div class="card border-0 shadow-sm">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th class="ps-4">Grupo / Cliente</th>
                                <th>Sucursal</th>
                                <th>Monto Aprobado</th>
                                <th>Pago a Realizar</th>
                                <th class="text-center">Tipo</th>
                                <th>Desembolso</th>
                                <th class="text-center">Estatus</th>
                                <th class="text-end pe-4">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($creditos as $credito)
                                @php
                                    $lider = $credito->integrantes->where('pivot.es_lider', true)->first();
                                    $nombreTitular = $lider ? ($lider->nombre_completo ?? $lider->nombre . ' ' . $lider->apellido_paterno) : ($credito->cliente->nombre_completo ?? $credito->cliente->nombre ?? 'SIN ASIGNAR');
                                    $nombrePrincipal = $credito->nombre_credito ?? ($credito->grupo->nombre_grupo ?? $nombreTitular);
                                    
                                    // Obtenemos la cuota regular de la primera amortización
                                    $cuota = $credito->amortizaciones->first() ? $credito->amortizaciones->first()->total_cuota : 0;
                                    $frecuencia = $credito->producto ? ucfirst($credito->producto->frecuencia_pago) : 'N/A';
                                @endphp
                                <tr>
                                    <td class="ps-4">
                                        <div class="fw-bold text-dark">{{ mb_strtoupper($nombrePrincipal) }}</div>
                                        <div class="small text-muted">Titular: {{ mb_strtoupper($nombreTitular) }}</div>
                                    </td>
                                    <td>{{ $credito->sucursal->nombre_sucursal ?? 'N/A' }}</td>
                                    <td class="fw-bold text-success">${{ number_format($credito->monto_aprobado, 2) }}</td>
                                    <td>
                                        <div class="fw-bold text-danger">${{ number_format($cuota, 2) }}</div>
                                        <div class="small text-muted">{{ $frecuencia }}</div>
                                    </td>
                                    <td class="text-center">
                                        @if($credito->grupo_id)
                                            <span class="badge bg-purple bg-opacity-10 text-purple">Grupal</span>
                                        @else
                                            <span class="badge bg-info bg-opacity-10 text-info text-dark">Individual</span>
                                        @endif
                                    </td>
                                    <td>{{ \Carbon\Carbon::parse($credito->fecha_desembolso)->format('d/m/Y') }}</td>
                                    <td class="text-center">
                                        <span class="badge bg-success"><i class="bi bi-check-circle-fill me-1"></i> Activo</span>
                                    </td>
                                    <td class="text-end pe-4">
                                        <a href="{{ route('creditos.show', $credito->id) }}" class="btn btn-sm btn-outline-primary shadow-sm" title="Ver Expediente / Pagos">
                                            <i class="bi bi-eye-fill"></i> Detalle
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center py-5 text-muted">
                                        <i class="bi bi-wallet2 fs-1 d-block mb-2"></i> No hay créditos activos registrados en la cartera.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            @if($creditos->hasPages())
                <div class="card-footer bg-white py-3">
                    {{ $creditos->links() }}
                </div>
            @endif
        </div>

    </div>
</x-app-layout>