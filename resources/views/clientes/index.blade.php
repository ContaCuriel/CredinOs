<x-app-layout>
    <div class="container-fluid py-4">
        
        {{-- TARJETAS DE MÉTRICAS RÁPIDAS (KPIs) --}}
        <div class="row mb-4">
            <div class="col-xl-3 col-sm-6 mb-xl-0 mb-3">
                <div class="card shadow-sm border-0 border-start border-primary border-4">
                    <div class="card-body p-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <p class="text-uppercase text-muted fw-bold mb-0 style-xs">Total Clientes</p>
                                <h4 class="fw-bolder mb-0">{{ $clientes->total() }}</h4>
                            </div>
                            <div class="bg-primary text-white rounded-circle p-3 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                                <i class="bi bi-people-fill fs-5"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-sm-6 mb-xl-0 mb-3">
                <div class="card shadow-sm border-0 border-start border-success border-4">
                    <div class="card-body p-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <p class="text-uppercase text-muted fw-bold mb-0 style-xs">Activos / Al día</p>
                                <h4 class="fw-bolder mb-0 text-success">{{ $clientes->where('estatus', 'activo')->count() }}</h4>
                            </div>
                            <div class="bg-success text-white rounded-circle p-3 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                                <i class="bi bi-check-circle-fill fs-5"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-sm-6 mb-xl-0 mb-3">
                <div class="card shadow-sm border-0 border-start border-warning border-4">
                    <div class="card-body p-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <p class="text-uppercase text-muted fw-bold mb-0 style-xs">Prospectos</p>
                                <h4 class="fw-bolder mb-0 text-warning">{{ $clientes->where('estatus', 'prospecto')->count() }}</h4>
                            </div>
                            <div class="bg-warning text-white rounded-circle p-3 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                                <i class="bi bi-person-plus-fill fs-5"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-sm-6">
                <div class="card shadow-sm border-0 border-start border-danger border-4">
                    <div class="card-body p-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <p class="text-uppercase text-muted fw-bold mb-0 style-xs">Morosos</p>
                                <h4 class="fw-bolder mb-0 text-danger">{{ $clientes->where('estatus', 'moroso')->count() }}</h4>
                            </div>
                            <div class="bg-danger text-white rounded-circle p-3 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                                <i class="bi bi-exclamation-triangle-fill fs-5"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- TABLA PRINCIPAL DE CLIENTES --}}
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <h5 class="mb-0 fw-bold text-dark"><i class="bi bi-person-lines-fill me-2 text-primary"></i>Directorio de Clientes</h5>
                <a href="{{ route('clientes.create') }}" class="btn btn-primary px-3 shadow-sm">
                    <i class="bi bi-person-plus-fill me-1"></i> Nuevo Cliente
                </a>
            </div>
            <div class="card-body">
                
                @if (session('success'))
                    <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm" role="alert">
                        <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif
                @if (session('error'))
                    <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm" role="alert">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i>{{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th scope="col" class="fw-bold">Cliente</th>
                                <th scope="col" class="fw-bold">Contacto</th>
                                <th scope="col" class="fw-bold">Capacidad de Pago</th>
                                <th scope="col" class="fw-bold">Estatus</th>
                                <th scope="col" class="fw-bold">Sucursal</th>
                                <th scope="col" class="text-end fw-bold me-3">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($clientes as $cliente)
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="avatar bg-light text-primary rounded-circle fw-bold d-flex align-items-center justify-content-center me-3" style="width: 40px; height: 40px;">
                                                {{ strtoupper(substr($cliente->nombre, 0, 1) . substr($cliente->apellido_paterno, 0, 1)) }}
                                            </div>
                                            <div>
                                                <h6 class="mb-0 fw-bold text-dark">{{ $cliente->nombre_completo }}</h6>
                                                <small class="text-muted"><i class="bi bi-card-heading me-1"></i>{{ $cliente->curp ?? 'SIN CURP' }}</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div><i class="bi bi-telephone-fill me-1 text-secondary"></i>{{ $cliente->telefono_celular ?? 'N/A' }}</div>
                                        <small class="text-muted"><i class="bi bi-geo-alt-fill me-1"></i>{{ $cliente->municipio }}, {{ $cliente->estado }}</small>
                                    </td>
                                    <td>
                                        @php
                                            $capacidad = ($cliente->ingresos_mensuales ?? 0) - ($cliente->gastos_mensuales ?? 0);
                                        @endphp
                                        <span class="badge bg-light text-dark border">
                                            ${{ number_format($capacidad, 2) }} / mes
                                        </span>
                                    </td>
                                    <td>
                                        @switch($cliente->estatus)
                                            @case('activo')
                                                <span class="badge bg-success-subtle text-success border border-success fw-bold px-2 py-1"><i class="bi bi-circle-fill fs-6 me-1"></i>Activo</span>
                                                @break
                                            @case('moroso')
                                                <span class="badge bg-danger-subtle text-danger border border-danger fw-bold px-2 py-1"><i class="bi bi-exclamation-octagon-fill me-1"></i>Moroso</span>
                                                @break
                                            @case('liquidado')
                                                <span class="badge bg-info-subtle text-info border border-info fw-bold px-2 py-1"><i class="bi bi-check2-all me-1"></i>Liquidado</span>
                                                @break
                                            @default
                                                <span class="badge bg-warning-subtle text-warning border border-warning fw-bold px-2 py-1"><i class="bi bi-clock-history me-1"></i>Prospecto</span>
                                        @endswitch
                                    </td>
                                    <td>
                                        <span class="badge bg-light text-secondary border">
                                            <i class="bi bi-building me-1"></i>{{ $cliente->sucursal->nombre_sucursal ?? 'Sin asignar' }}
                                        </span>
                                    </td>
                                    <td class="text-end">
                                        <div class="btn-group shadow-sm" role="group">
                                            <a href="{{ route('clientes.edit', $cliente->id_cliente) }}" class="btn btn-outline-warning btn-sm" title="Editar Cliente">
                                                <i class="bi bi-pencil-square"></i>
                                            </a>
                                            <form action="{{ route('clientes.destroy', $cliente->id_cliente) }}" method="POST" class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-outline-danger btn-sm" title="Eliminar Cliente" onclick="return confirm('¿Estás seguro de que quieres eliminar a este cliente?')">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-4 text-muted">
                                        <i class="bi bi-people display-6 d-block mb-2 text-secondary"></i>
                                        No hay clientes registrados en el sistema.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                
                @if($clientes->hasPages())
                <div class="mt-3">
                    {{ $clientes->links() }}
                </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>