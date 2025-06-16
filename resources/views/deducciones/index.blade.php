<x-app-layout>
    <div class="container-fluid py-4">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Gestión de Deducciones y Préstamos</h5>
                <a href="{{ route('deducciones.create') }}" class="btn btn-success">
                    <i class="bi bi-plus-lg"></i> Registrar Nueva Deducción
                </a>
            </div>
            <div class="card-body">
                @if (session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                {{-- Formulario de Filtros (se queda como está) --}}
                <form method="GET" action="{{ route('deducciones.index') }}" class="mb-4">
                    <div class="row align-items-end g-2">
                        {{-- ... tu formulario de filtros completo se queda aquí ... --}}
                        <div class="col-md-3">
                            <label for="search_nombre" class="form-label mb-1">Buscar por Empleado:</label>
                            <input type="text" name="search_nombre" id="search_nombre" class="form-control form-control-sm" value="{{ request('search_nombre') }}" placeholder="Nombre del empleado...">
                        </div>
                        <div class="col-md-3">
                            <label for="id_sucursal_filter" class="form-label mb-1">Filtrar por Sucursal:</label>
                            <select name="id_sucursal_filter" id="id_sucursal_filter" class="form-select form-select-sm">
                                <option value="">Todas las Sucursales</option>
                                @if(isset($sucursales))
                                    @foreach ($sucursales as $sucursal)
                                        <option value="{{ $sucursal->id_sucursal }}" {{ request('id_sucursal_filter') == $sucursal->id_sucursal ? 'selected' : '' }}>{{ $sucursal->nombre_sucursal }}</option>
                                    @endforeach
                                @endif
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="tipo_deduccion_filter" class="form-label mb-1">Filtrar por Tipo:</label>
                            <select name="tipo_deduccion_filter" id="tipo_deduccion_filter" class="form-select form-select-sm">
                                <option value="">Todos los Tipos</option>
                                 @if(isset($tipos_deduccion))
                                    @foreach ($tipos_deduccion as $tipo)
                                        <option value="{{ $tipo }}" {{ request('tipo_deduccion_filter') == $tipo ? 'selected' : '' }}>{{ $tipo }}</option>
                                    @endforeach
                                @endif
                            </select>
                        </div>
                        <div class="col-md-2 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary btn-sm w-100">Buscar/Filtrar</button>
                        </div>
                        <div class="col-md-1 d-flex align-items-end">
                            @if(request('search_nombre') || request('id_sucursal_filter') || request('tipo_deduccion_filter'))
                                <a href="{{ route('deducciones.index') }}" class="btn btn-secondary btn-sm w-100" title="Limpiar Filtros"><i class="bi bi-eraser"></i></a>
                            @endif
                        </div>
                    </div>
                </form>
                {{-- Fin Filtros --}}

                <div class="table-responsive">
                    <table class="table table-striped table-hover table-sm">
                        <thead class="table-light">
                            <tr>
                                <th>Empleado</th>
                                <th>Tipo de Deducción</th>
                                <th class="text-center">Fecha Inicio</th>
                                <th class="text-end">Monto Quincenal</th>
                                {{-- =====> COLUMNA ACTUALIZADA <===== --}}
                                <th class="text-end">Monto Acumulado / Saldo Pendiente</th>
                                <th class="text-center">Status</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($deducciones as $deduccion)
                                <tr>
                                    <td>{{ $deduccion->empleado ? $deduccion->empleado->nombre_completo : 'Empleado no encontrado' }}</td>
                                    <td>{{ $deduccion->tipo_deduccion }}</td>
                                    <td class="text-center">{{ $deduccion->fecha_solicitud->format('d/m/Y') }}</td>
                                    <td class="text-end">$ {{ number_format($deduccion->monto_quincenal, 2) }}</td>
                                    {{-- =====> CELDA ACTUALIZADA <===== --}}
                                    <td class="text-end fw-bold">
                                        @if ($deduccion->tipo_deduccion == 'Préstamo')
                                            <span class="text-danger" title="Saldo Pendiente">$ {{ number_format($deduccion->saldo_pendiente, 2) }}</span>
                                        @elseif ($deduccion->tipo_deduccion == 'Caja de Ahorro')
                                            <span class="text-success" title="Monto Acumulado">$ {{ number_format($deduccion->monto_acumulado, 2) }}</span>
                                        @else
                                            <span class="text-muted">N/A</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        @if ($deduccion->status == 'Activo')
                                            <span class="badge bg-primary">{{ $deduccion->status }}</span>
                                        @elseif ($deduccion->status == 'Pagado')
                                            <span class="badge bg-success">{{ $deduccion->status }}</span>
                                        @else
                                            <span class="badge bg-secondary">{{ $deduccion->status }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        <a href="{{ route('deducciones.edit', $deduccion->id) }}" class="btn btn-sm btn-info" title="Editar Deducción"><i class="bi bi-pencil-square"></i></a>
                                          {{-- =====> ACTIVAR FORMULARIO PARA ELIMINAR <===== --}}
                                        <form action="{{ route('deducciones.destroy', $deduccion->id) }}" method="POST" style="display:inline;">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger" title="Eliminar Deducción" onclick="return confirm('¿Estás seguro de que quieres eliminar este registro?')">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                        {{-- ============================================= --}}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center">No hay deducciones que coincidan con los filtros.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="mt-3">
                    {{ $deducciones->links() }}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>