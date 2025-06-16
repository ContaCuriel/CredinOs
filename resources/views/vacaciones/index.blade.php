<x-app-layout>
    <div class="container-fluid py-4">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Resumen de Saldos de Vacaciones</h5>
                <a href="{{ route('vacaciones.create') }}" class="btn btn-success">
                    <i class="bi bi-plus-lg"></i> Capturar Periodo Vacacional
                </a>
            </div>
            <div class="card-body">
                @if (session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                {{-- Filtros --}}
                <form method="GET" action="{{ route('vacaciones.index') }}" class="mb-4">
                    <div class="row align-items-end g-2">
                        <div class="col-md-4">
                            <label for="search_nombre_empleado" class="form-label mb-1">Buscar por Nombre:</label>
                            <input type="text" name="search_nombre_empleado" id="search_nombre_empleado" class="form-control form-control-sm" value="{{ request('search_nombre_empleado') }}" placeholder="Nombre del empleado...">
                            <div class="form-text">La búsqueda por nombre anula otros filtros.</div>
                        </div>
                        <div class="col-md-3">
                            <label for="id_sucursal_filter" class="form-label mb-1">Filtrar por Sucursal:</label>
                            <select name="id_sucursal_filter" id="id_sucursal_filter" class="form-select form-select-sm">
                                <option value="">Todas las Sucursales</option>
                                @foreach ($todasLasSucursales as $sucursal)
                                    <option value="{{ $sucursal->id_sucursal }}" {{ request('id_sucursal_filter') == $sucursal->id_sucursal ? 'selected' : '' }}>
                                        {{ $sucursal->nombre_sucursal }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        {{-- =====> NUEVO FILTRO DE ESTATUS <===== --}}
                        <div class="col-md-2">
                            <label for="status_filter" class="form-label mb-1">Estatus:</label>
                             <select name="status_filter" id="status_filter" class="form-select form-select-sm">
                                <option value="Alta" {{ request('status_filter', 'Alta') == 'Alta' ? 'selected' : '' }}>Activos</option>
                                <option value="Baja" {{ request('status_filter') == 'Baja' ? 'selected' : '' }}>Bajas</option>
                                <option value="Todos" {{ request('status_filter') == 'Todos' ? 'selected' : '' }}>Todos</option>
                            </select>
                        </div>
                        {{-- ====================================== --}}
                        <div class="col-md-2 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary btn-sm w-100">Buscar/Filtrar</button>
                        </div>
                        <div class="col-md-1 d-flex align-items-end">
                            <a href="{{ route('vacaciones.index') }}" class="btn btn-secondary btn-sm w-100" title="Limpiar todos los filtros">Limpiar</a>
                        </div>
                    </div>
                </form>

                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>Empleado</th>
                                <th>Sucursal</th>
                                <th>Estatus</th>
                                <th class="text-center">Antigüedad</th>
                                <th class="text-center">Total Días Restantes</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($empleados as $empleado)
                                <tr>
                                    <td>{{ $empleado->nombre_completo }}</td>
                                    <td>{{ $empleado->sucursal->nombre_sucursal ?? 'N/A' }}</td>
                                    <td>
                                        <span class="badge {{ $empleado->status == 'Alta' ? 'bg-success' : 'bg-danger' }}">
                                            {{ $empleado->status }}
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        {{ \Carbon\Carbon::parse($empleado->fecha_ingreso)->diffForHumans(null, true, false, 2) }}
                                    </td>
                                    <td class="text-center fw-bold fs-6 {{ $empleado->total_dias_restantes > 0 ? 'text-primary' : '' }}">
                                        {{ number_format($empleado->total_dias_restantes, 2) }}
                                    </td>
                                    <td>
                                        <a href="{{ route('vacaciones.historial', $empleado->id_empleado) }}" class="btn btn-sm btn-secondary" title="Ver Historial Detallado">
                                            <i class="bi bi-list-ul"></i> Historial
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-4">
                                        No se encontraron empleados que coincidan con los filtros seleccionados.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="mt-3">
                    {{ $empleados->appends(request()->query())->links() }}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>