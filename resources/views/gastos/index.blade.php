<x-app-layout>
    <form method="GET" action="{{ route('gastos.index') }}" class="mb-4">
    <div class="card shadow-sm">
        <div class="card-body">
            <div class="row g-3 align-items-end">
                <div class="col-md-4">
                    <label for="search_term" class="form-label">Buscar por Proveedor/Descripción</label>
                    <input type="text" name="search_term" id="search_term" class="form-control form-control-sm" value="{{ request('search_term') }}" placeholder="Ej: papelería, office depot...">
                </div>

                <div class="col-md-3">
                    <label for="sucursal_id" class="form-label">Sucursal</label>
                    <select name="sucursal_id" id="sucursal_id" class="form-select form-select-sm">
                        <option value="">Todas</option>
                        @foreach($sucursales as $sucursal)
                            <option value="{{ $sucursal->id_sucursal }}" {{ request('sucursal_id') == $sucursal->id_sucursal ? 'selected' : '' }}>
                                {{ $sucursal->nombre_sucursal }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-3">
                    <label for="categoria_id" class="form-label">Categoría</label>
                    <select name="categoria_id" id="categoria_id" class="form-select form-select-sm">
                        <option value="">Todas</option>
                         @foreach($categorias as $categoria)
                            <option value="{{ $categoria->id }}" {{ request('categoria_id') == $categoria->id ? 'selected' : '' }}>
                                {{ $categoria->nombre }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-2">
                    <label for="estado" class="form-label">Estado</label>
                    <select name="estado" id="estado" class="form-select form-select-sm">
                        <option value="">Todos</option>
                        <option value="En Aprobación" {{ request('estado') == 'En Aprobación' ? 'selected' : '' }}>En Aprobación</option>
                        <option value="Aprobado" {{ request('estado') == 'Aprobado' ? 'selected' : '' }}>Aprobado</option>
                        <option value="Aprobado (Automático)" {{ request('estado') == 'Aprobado (Automático)' ? 'selected' : '' }}>Aprobado (Automático)</option>
                        <option value="Rechazado" {{ request('estado') == 'Rechazado' ? 'selected' : '' }}>Rechazado</option>
                    </select>
                </div>

                <div class="col-md-3">
                    <label for="fecha_inicio" class="form-label">Desde</label>
                    <input type="date" name="fecha_inicio" id="fecha_inicio" class="form-control form-control-sm" value="{{ request('fecha_inicio') }}">
                </div>
                <div class="col-md-3">
                    <label for="fecha_fin" class="form-label">Hasta</label>
                    <input type="date" name="fecha_fin" id="fecha_fin" class="form-control form-control-sm" value="{{ request('fecha_fin') }}">
                </div>

                <div class="col-md-3">
                    <button type="submit" class="btn btn-primary btn-sm w-100">Buscar / Filtrar</button>
                </div>
                <div class="col-md-3">
                    <a href="{{ route('gastos.index') }}" class="btn btn-secondary btn-sm w-100">Limpiar Filtros</a>
                </div>
            </div>
        </div>
    </div>
</form>
    <div class="container-fluid py-4">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Historial de Gastos Registrados</h5>
                <div>
                    {{-- Puedes añadir un botón para exportar en el futuro --}}
                    {{-- <a href="#" class="btn btn-outline-success me-2">
                        <i class="bi bi-file-earmark-excel"></i> Exportar a Excel
                    </a> --}}
                    <a href="{{ route('gastos.create') }}" class="btn btn-success">
                        <i class="bi bi-plus-lg"></i> Registrar Nuevo Gasto
                    </a>
                </div>
            </div>
            <div class="card-body">
                @if (session('success'))
                    <div class="alert alert-success alert-dismissible fade show mb-3" role="alert">
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif
                
                {{-- Aquí irán los filtros en el futuro --}}

                <div class="table-responsive">
                    <table class="table table-striped table-hover align-middle">
                        <thead>
                            <tr>
                                <th>Fecha</th>
                                <th>Sucursal</th>
                                <th>Categoría</th>
                                <th>Proveedor</th>
                                <th>Registrado por</th>
                                <th class="text-end">Monto Total</th>
                                <th class="text-center">Estado</th>
                                <th class="text-center">Comprobante</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($gastos as $gasto)
                                <tr>
                                    <td>{{ $gasto->fecha_gasto->format('d/m/Y') }}</td>
                                    <td>{{ $gasto->sucursal?->nombre_sucursal ?? 'N/A' }}</td>
                                    <td>{{ $gasto->categoria?->nombre ?? 'N/A' }}</td>
                                    <td>{{ $gasto->proveedor?->nombre ?? 'N/A' }}</td>
                                    <td>{{ $gasto->usuario?->name ?? 'N/A' }}</td>
                                    <td class="text-end">${{ number_format($gasto->monto_total, 2) }}</td>
                                    <td class="text-center">
                                        {{-- Lógica para mostrar el estado con colores --}}
                                        @php
                                            $estadoClass = '';
                                            switch ($gasto->estado) {
                                                case 'Aprobado':
                                                case 'Aprobado (Automático)':
                                                    $estadoClass = 'text-bg-success';
                                                    break;
                                                case 'En Aprobación':
                                                    $estadoClass = 'text-bg-warning';
                                                    break;
                                                case 'Rechazado':
                                                    $estadoClass = 'text-bg-danger';
                                                    break;
                                                default:
                                                    $estadoClass = 'text-bg-secondary';
                                            }
                                        @endphp
                                        <span class="badge {{ $estadoClass }}">{{ $gasto->estado }}</span>
                                    </td>
                                    <td class="text-center">
                                        @if ($gasto->nombre_archivo_comprobante)
                                            <a href="{{ route('gastos.verComprobante', $gasto) }}" class="btn btn-sm btn-outline-primary" target="_blank" title="Ver Comprobante">
                                                <i class="bi bi-eye-fill"></i> Ver
                                            </a>
                                        @else
                                            <span class="text-muted">N/A</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
    <a href="{{ route('gastos.edit', $gasto) }}" class="btn btn-sm btn-info" title="Editar Gasto">
        <i class="bi bi-pencil-square"></i>
    </a>
    <form action="{{ route('gastos.destroy', $gasto) }}" method="POST" onsubmit="return confirm('¿Estás seguro de que quieres eliminar este gasto?');">
        @csrf
        @method('DELETE')
        <button type="submit" class="btn btn-sm btn-danger" title="Eliminar Gasto" style="border-top-left-radius: 0; border-bottom-left-radius: 0;">
            <i class="bi bi-trash"></i>
        </button>
    </form>
</div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="text-center">No hay gastos registrados todavía.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                
                {{-- Enlaces de Paginación --}}
                <div class="mt-3">
                    {{ $gastos->appends(request()->query())->links() }}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>