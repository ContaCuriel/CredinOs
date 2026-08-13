<x-app-layout>
    <div class="container-fluid py-4">

        {{-- ALERTA DE ÉXITO --}}
        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm mb-4" role="alert">
                <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        {{-- TARJETAS KPI RESUMEN --}}
        <div class="row g-3 mb-4">
            <div class="col-md-4">
                <div class="card border-0 shadow-sm border-start border-warning border-4 rounded-3 p-3 bg-white">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <span class="text-uppercase text-muted fw-bold small">Solicitudes Pendientes</span>
                            <h3 class="mb-0 fw-bold text-dark mt-1">{{ $creditos->where('estatus', 'solicitado')->count() }}</h3>
                        </div>
                        <div class="rounded-circle bg-warning bg-opacity-10 p-3 text-warning">
                            <i class="bi bi-hourglass-split fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-0 shadow-sm border-start border-success border-4 rounded-3 p-3 bg-white">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <span class="text-uppercase text-muted fw-bold small">Créditos Activos</span>
                            <h3 class="mb-0 fw-bold text-success mt-1">{{ $creditos->where('estatus', 'desembolsado')->count() }}</h3>
                        </div>
                        <div class="rounded-circle bg-success bg-opacity-10 p-3 text-success">
                            <i class="bi bi-cash-coin fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-0 shadow-sm border-start border-primary border-4 rounded-3 p-3 bg-white">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <span class="text-uppercase text-muted fw-bold small">Total Colocado Solicitado</span>
                            <h3 class="mb-0 fw-bold text-primary mt-1">${{ number_format($creditos->sum('monto_solicitado'), 2) }}</h3>
                        </div>
                        <div class="rounded-circle bg-primary bg-opacity-10 p-3 text-primary">
                            <i class="bi bi-graph-up-arrow fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- TABLA DE CRÉDITOS --}}
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <h5 class="mb-0 fw-bold text-dark">
                    <i class="bi bi-card-list me-2 text-primary"></i>Listado de Solicitudes y Créditos
                </h5>
                @can('registrar-credito')
                    <a href="{{ route('creditos.create') }}" class="btn btn-primary btn-sm shadow-sm fw-bold">
                        <i class="bi bi-plus-lg me-1"></i> Nueva Solicitud
                    </a>
                @endcan
            </div>
            
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light border-bottom">
                            <tr>
                                <th class="ps-4 text-secondary text-uppercase small fw-bold">Folio / Fecha</th>
                                <th class="text-secondary text-uppercase small fw-bold">Titular (Grupo o Cliente)</th>
                                <th class="text-secondary text-uppercase small fw-bold">Producto</th>
                                <th class="text-secondary text-uppercase small fw-bold text-end">Monto Solicitado</th>
                                <th class="text-secondary text-uppercase small fw-bold text-center">Estatus</th>
                                <th class="pe-4 text-end text-secondary text-uppercase small fw-bold">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($creditos as $credito)
                                <tr>
                                    <td class="ps-4">
                                        <span class="fw-bold text-dark">{{ $credito->folio }}</span>
                                        <div class="small text-muted">
                                            {{ $credito->fecha_solicitud->format('d/m/Y') }}
                                        </div>
                                    </td>
                                    <td>
                                        @if($credito->grupo_id)
                                            <div class="fw-bold text-purple">
                                                <i class="bi bi-people-fill me-1"></i> {{ $credito->grupo->nombre_grupo }}
                                            </div>
                                            <div class="small text-muted">
                                                {{ $credito->integrantes->count() }} integrantes
                                            </div>
                                        @elseif($credito->cliente_id)
                                            <div class="fw-bold text-info text-dark">
                                                <i class="bi bi-person-fill me-1"></i> {{ $credito->cliente->nombre_completo ?? $credito->cliente->nombre }}
                                            </div>
                                            <div class="small text-muted">
                                                Crédito Individual
                                            </div>
                                        @endif
                                        
                                        @if($credito->nombre_credito)
                                            <div class="badge bg-light text-dark border mt-1">
                                                {{ $credito->nombre_credito }}
                                            </div>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="fw-bold text-dark">{{ $credito->producto->nombre }}</span>
                                        <div class="small text-muted">
                                            {{ $credito->plazo_solicitado }} cuotas | {{ $credito->tasa_interes_aplicada }}% 
                                        </div>
                                    </td>
                                    <td class="text-end">
                                        <span class="fw-bold text-success fs-6">${{ number_format($credito->monto_solicitado, 2) }}</span>
                                    </td>
                                    <td class="text-center">
                                        @if($credito->estatus == 'solicitado')
                                            <span class="badge bg-warning text-dark rounded-pill px-3 py-2"><i class="bi bi-hourglass-split me-1"></i> Solicitado</span>
                                        @elseif($credito->estatus == 'aprobado')
                                            <span class="badge bg-info text-dark rounded-pill px-3 py-2"><i class="bi bi-check-all me-1"></i> Aprobado</span>
                                        @elseif($credito->estatus == 'desembolsado')
                                            <span class="badge bg-success rounded-pill px-3 py-2"><i class="bi bi-cash me-1"></i> Activo</span>
                                        @elseif($credito->estatus == 'rechazado')
                                            <span class="badge bg-danger rounded-pill px-3 py-2"><i class="bi bi-x-circle me-1"></i> Rechazado</span>
                                        @else
                                            <span class="badge bg-secondary rounded-pill px-3 py-2">{{ ucfirst($credito->estatus) }}</span>
                                        @endif
                                    </td>
                                    <td class="pe-4 text-end">
                                        {{-- AQUÍ ESTÁ EL BOTÓN CORREGIDO --}}
                                        <a href="{{ route('creditos.show', $credito->id) }}" class="btn btn-light btn-sm border text-primary fw-bold shadow-sm">
                                            <i class="bi bi-eye-fill me-1"></i> Revisar
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-5 text-muted">
                                        <i class="bi bi-folder-x fs-1 d-block mb-2 text-secondary"></i>
                                        No hay solicitudes de crédito registradas.
                                        <br>
                                        <a href="{{ route('creditos.create') }}" class="btn btn-link btn-sm fw-bold">Crear la primera solicitud</a>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                
                @if($creditos->hasPages())
                    <div class="card-footer bg-white border-0 pt-3">
                        {{ $creditos->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>