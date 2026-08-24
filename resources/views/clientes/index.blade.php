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
                <h4 class="fw-bold mb-0 text-dark"><i class="bi bi-person-lines-fill me-2 text-primary"></i>Directorio de Clientes</h4>
                <p class="text-muted mb-0">Gestión de prospectos y clientes activos</p>
            </div>
            <div>
                <a href="{{ route('clientes.create') }}" class="btn btn-primary fw-bold shadow-sm">
                    <i class="bi bi-plus-lg me-1"></i> Nuevo Cliente
                </a>
            </div>
        </div>

        {{-- BARRA DE BÚSQUEDA Y FILTROS COMPACTA --}}
        <div class="bg-white p-3 rounded shadow-sm mb-4 border">
            <form action="{{ route('clientes.index') }}" method="GET" class="row g-2 align-items-end">
                <div class="col-md-5">
                    <label class="form-label small fw-bold text-muted mb-1"><i class="bi bi-search me-1"></i> Buscar por Nombre o CURP</label>
                    <input type="text" class="form-control form-control-sm" name="nombre" placeholder="Ej. Juan Pérez..." value="{{ request('nombre') }}">
                </div>
                
                <div class="col-md-5">
                    <label class="form-label small fw-bold text-muted mb-1"><i class="bi bi-building me-1"></i> Filtrar por Sucursal</label>
                    <select class="form-select form-select-sm" name="sucursal_id">
                        <option value="">Todas las Sucursales</option>
                        @foreach($sucursales ?? [] as $sucursal)
                            <option value="{{ $sucursal->id_sucursal ?? $sucursal->id }}" {{ request('sucursal_id') == ($sucursal->id_sucursal ?? $sucursal->id) ? 'selected' : '' }}>
                                {{ $sucursal->nombre_sucursal }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-2 d-flex gap-1">
                    <button type="submit" class="btn btn-sm btn-dark fw-bold w-100 shadow-sm">
                        Filtrar
                    </button>
                    @if(request()->hasAny(['nombre', 'sucursal_id']))
                        <a href="{{ route('clientes.index') }}" class="btn btn-sm btn-outline-danger shadow-sm" title="Limpiar Filtros">
                            <i class="bi bi-x-lg"></i>
                        </a>
                    @endif
                </div>
            </form>
        </div>

        {{-- TABLA DE CLIENTES (LISTADO LIMPIO Y SIN AVATARES) --}}
        <div class="bg-white rounded shadow-sm border overflow-hidden">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 text-sm">
                    <thead class="table-light text-secondary">
                        <tr>
                            <th class="ps-4 py-3">Nombre Completo</th>
                            <th class="py-3">Sucursal</th>
                            <th class="py-3">Grupo Solidario</th>
                            <th class="text-center py-3">Teléfono</th>
                            <th class="text-center py-3">Estatus</th>
                            <th class="text-end pe-4 py-3">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="border-top-0">
                        @forelse($clientes as $cliente)
                            @php
                                // ¿Tiene créditos desembolsados?
                                $esActivo = false;
                                if($cliente->creditos) {
                                    $esActivo = $cliente->creditos->where('estatus', 'desembolsado')->count() > 0;
                                }

                                // ¿A qué grupo pertenece?
                                $nombreGrupo = null;
                                if($cliente->grupos && $cliente->grupos->count() > 0) {
                                    $nombreGrupo = $cliente->grupos->last()->nombre_grupo;
                                }
                            @endphp
                            <tr>
                                <td class="ps-4">
                                    <div class="fw-bold text-dark">{{ mb_strtoupper($cliente->nombre . ' ' . $cliente->apellido_paterno . ' ' . $cliente->apellido_materno) }}</div>
                                    <div class="text-muted" style="font-size: 0.8rem;">CURP: {{ $cliente->curp ?? 'NO REGISTRADA' }}</div>
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark border">{{ $cliente->sucursal->nombre_sucursal ?? 'Sin Asignar' }}</span>
                                </td>
                                <td>
                                    @if($nombreGrupo)
                                        <span class="fw-bold text-primary" style="font-size: 0.85rem;">{{ mb_strtoupper($nombreGrupo) }}</span>
                                    @else
                                        <span class="text-muted small fst-italic">Individual / Sin Grupo</span>
                                    @endif
                                </td>
                                <td class="text-center font-monospace text-dark">
                                    {{ $cliente->telefono_celular ?? 'N/A' }}
                                </td>
                                <td class="text-center">
                                    @if($esActivo)
                                        <span class="badge bg-success bg-opacity-10 text-success border border-success px-2 py-1">ACTIVO</span>
                                    @else
                                        <span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary px-2 py-1">PROSPECTO</span>
                                    @endif
                                </td>
                                <td class="text-end pe-4">
                                    <div class="btn-group">
                                        <a href="{{ route('clientes.edit', $cliente->id_cliente ?? $cliente->id) }}" class="btn btn-sm btn-light border text-primary" title="Editar Datos">
                                            <i class="bi bi-pencil-square"></i>
                                        </a>
                                        <form action="{{ route('clientes.destroy', $cliente->id_cliente ?? $cliente->id) }}" method="POST" onsubmit="return confirm('¿Confirma eliminar este registro?');" style="display:inline;">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-light border text-danger" title="Eliminar Cliente">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-5 text-muted">
                                    <i class="bi bi-person-x fs-2 d-block mb-2"></i> No se encontraron clientes registrados.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            @if($clientes->hasPages())
                <div class="card-footer bg-white border-top py-2">
                    {{ $clientes->links() }}
                </div>
            @endif
        </div>

    </div>
</x-app-layout>