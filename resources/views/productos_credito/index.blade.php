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
                <div class="card border-0 shadow-sm border-start border-primary border-4 rounded-3 p-3 bg-white">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <span class="text-uppercase text-muted fw-bold small">Total Productos</span>
                            <h3 class="mb-0 fw-bold text-dark mt-1">{{ $productos->count() }}</h3>
                        </div>
                        <div class="rounded-circle bg-primary bg-opacity-10 p-3 text-primary">
                            <i class="bi bi-box-seam fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-0 shadow-sm border-start border-success border-4 rounded-3 p-3 bg-white">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <span class="text-uppercase text-muted fw-bold small">Productos Activos</span>
                            <h3 class="mb-0 fw-bold text-success mt-1">{{ $productos->where('activo', true)->count() }}</h3>
                        </div>
                        <div class="rounded-circle bg-success bg-opacity-10 p-3 text-success">
                            <i class="bi bi-check-circle fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-0 shadow-sm border-start border-warning border-4 rounded-3 p-3 bg-white">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <span class="text-uppercase text-muted fw-bold small">Frecuencias Configuradas</span>
                            <h3 class="mb-0 fw-bold text-warning mt-1">{{ $productos->pluck('frecuencia_pago')->unique()->count() }}</h3>
                        </div>
                        <div class="rounded-circle bg-warning bg-opacity-10 p-3 text-warning">
                            <i class="bi bi-calendar3 fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- TABLA DE PRODUCTOS --}}
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <h5 class="mb-0 fw-bold text-dark">
                    <i class="bi bi-sliders me-2 text-primary"></i>Catálogo de Productos de Crédito
                </h5>
                @can('crear-productos-credito')
                    <a href="{{ route('productos_credito.create') }}" class="btn btn-primary btn-sm shadow-sm">
                        <i class="bi bi-plus-lg me-1"></i> Nuevo Producto
                    </a>
                @endcan
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light border-bottom">
                            <tr>
                                <th class="ps-4 text-secondary text-uppercase small fw-bold">Nombre del Producto</th>
                                <th class="text-secondary text-uppercase small fw-bold">Tipo / Frecuencia</th>
                                <th class="text-secondary text-uppercase small fw-bold">Tasa Interés</th>
                                <th class="text-secondary text-uppercase small fw-bold">Montos Permitidos</th>
                                <th class="text-secondary text-uppercase small fw-bold">Multa / Mora</th>
                                <th class="text-secondary text-uppercase small fw-bold">Estatus</th>
                                <th class="pe-4 text-end text-secondary text-uppercase small fw-bold">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($productos as $producto)
                                <tr>
                                    <td class="ps-4 fw-bold text-dark">
                                        {{ $producto->nombre }}
                                        <br>
                                        <span class="badge bg-secondary bg-opacity-10 text-secondary fw-normal">
                                            Plazo: {{ $producto->plazo_minimo }} a {{ $producto->plazo_maximo }} cuotas
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge {{ $producto->tipo_credito == 'grupal' ? 'bg-purple bg-opacity-10 text-purple border border-purple' : 'bg-info bg-opacity-10 text-info border border-info' }} rounded-pill px-3">
                                            {{ ucfirst($producto->tipo_credito) }}
                                        </span>
                                        <div class="small text-muted mt-1">
                                            <i class="bi bi-arrow-repeat me-1"></i>{{ ucfirst($producto->frecuencia_pago) }}
                                        </div>
                                    </td>
                                    <td>
                                        <span class="fw-bold text-dark">{{ number_format($producto->tasa_interes, 2) }}%</span>
                                        <div class="small text-muted">
                                            {{ $producto->tipo_tasa == 'global' ? 'Global Fija' : 'Sobre Insoluto' }}
                                        </div>
                                    </td>
                                    <td>
                                        <span class="text-success fw-bold">${{ number_format($producto->monto_minimo, 2) }}</span>
                                        <span class="text-muted small">a</span>
                                        <span class="text-success fw-bold">${{ number_format($producto->monto_maximo, 2) }}</span>
                                    </td>
                                    <td>
                                        @if($producto->multa_valor > 0)
                                            <div class="small text-danger">
                                                <i class="bi bi-clock me-1"></i>Multa: ${{ number_format($producto->multa_valor, 2) }}
                                            </div>
                                        @endif
                                        @if($producto->mora_valor > 0)
                                            <div class="small text-danger">
                                                <i class="bi bi-exclamation-triangle me-1"></i>Mora: {{ $producto->mora_valor }}%
                                            </div>
                                        @endif
                                        @if($producto->multa_valor == 0 && $producto->mora_valor == 0)
                                            <span class="text-muted small">Sin castigos</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($producto->activo)
                                            <span class="badge bg-success bg-opacity-10 text-success rounded-pill px-3">Activo</span>
                                        @else
                                            <span class="badge bg-danger bg-opacity-10 text-danger rounded-pill px-3">Inactivo</span>
                                        @endif
                                    </td>
                                    <td class="pe-4 text-end">
                                        @can('editar-productos-credito')
                                            <a href="{{ route('productos_credito.edit', $producto->id) }}" class="btn btn-outline-warning btn-sm border-0" title="Editar Parámetros">
                                                <i class="bi bi-pencil-fill"></i>
                                            </a>
                                        @endcan
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center py-5 text-muted">
                                        <i class="bi bi-box-seam fs-1 d-block mb-2 text-secondary"></i>
                                        No hay productos de crédito registrados aún.
                                        <br>
                                        <a href="{{ route('productos_credito.create') }}" class="btn btn-link btn-sm fw-bold">Crear el primer producto</a>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>