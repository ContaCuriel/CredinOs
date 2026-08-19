<x-app-layout>
    <div class="container-fluid py-4">
        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm" role="alert">
                <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif
        @if (session('error'))
            <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm" role="alert">
                <i class="bi bi-x-circle-fill me-2"></i>{{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <div class="card shadow-sm border-0">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <h5 class="mb-0 fw-bold text-dark">
                    <i class="bi bi-bank me-2 text-primary"></i>Catálogo de Cuentas Receptores (Empresa)
                </h5>
                @can('crear-cuentas-bancarias')
                    <a href="{{ route('cuentas_bancarias.create') }}" class="btn btn-primary btn-sm shadow-sm">
                        <i class="bi bi-plus-lg me-1"></i> Nueva Cuenta
                    </a>
                @endcan
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light border-bottom">
                            <tr>
                                <th class="ps-4">Banco</th>
                                <th>Nombre del Titular</th>
                                <th>Número de Cuenta / CLABE</th>
                                <th>Estatus</th>
                                <th class="text-end pe-4">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($cuentas as $cuenta)
                                <tr>
                                    <td class="ps-4 fw-bold text-primary">{{ $cuenta->banco }}</td>
                                    <td>{{ $cuenta->titular }}</td>
                                    <td class="font-monospace text-muted">
                                        Cta: {{ $cuenta->numero_cuenta ?? 'N/A' }}<br>
                                        CLABE: {{ $cuenta->clabe ?? 'N/A' }}
                                    </td>
                                    <td>
                                        @if($cuenta->activa)
                                            <span class="badge bg-success bg-opacity-10 text-success rounded-pill px-3">Activa</span>
                                        @else
                                            <span class="badge bg-danger bg-opacity-10 text-danger rounded-pill px-3">Inactiva</span>
                                        @endif
                                    </td>
                                    <td class="text-end pe-4">
                                        @can('editar-cuentas-bancarias')
                                            <a href="{{ route('cuentas_bancarias.edit', $cuenta->id) }}" class="btn btn-outline-warning btn-sm border-0" title="Editar">
                                                <i class="bi bi-pencil-fill"></i>
                                            </a>
                                        @endcan
                                        @can('eliminar-cuentas-bancarias')
                                            <form action="{{ route('cuentas_bancarias.destroy', $cuenta->id) }}" method="POST" class="d-inline" onsubmit="return confirm('¿Estás seguro de eliminar esta cuenta?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-outline-danger btn-sm border-0" title="Eliminar">
                                                    <i class="bi bi-trash-fill"></i>
                                                </button>
                                            </form>
                                        @endcan
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-4 text-muted">No hay cuentas bancarias registradas.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>