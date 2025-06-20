<x-app-layout>
    <div class="container-fluid py-4">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Panorama Contractual de Empleados Activos</h5>
                <div>
                    <a href="{{ route('contratos.exportarExcel', request()->query()) }}" class="btn btn-outline-success me-2">
                        <i class="bi bi-file-earmark-excel"></i> Exportar a Excel
                    </a>
                    <a href="{{ route('contratos.create') }}" class="btn btn-success">
                        <i class="bi bi-plus-lg"></i> Registrar Nuevo Contrato
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
                @if (session('error'))
                    <div class="alert alert-danger alert-dismissible fade show mb-3" role="alert">
                        {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif
                
                {{-- Filtros y Búsqueda para Panorama Contractual --}}
                <form method="GET" action="{{ route('contratos.index') }}" class="mb-4">
                    <div class="row align-items-end g-2">
                        <div class="col-md-4">
                            <label for="search_nombre_empleado" class="form-label mb-1">Buscar por Nombre Empleado:</label>
                            <input type="text" name="search_nombre_empleado" id="search_nombre_empleado" class="form-control form-control-sm"
                                value="{{ request('search_nombre_empleado') }}" placeholder="Nombre del empleado...">
                        </div>
                        <div class="col-md-4">
                            <label for="id_sucursal_filter" class="form-label mb-1">Filtrar por Sucursal del Empleado:</label>
                            <select name="id_sucursal_filter" id="id_sucursal_filter" class="form-select form-select-sm">
                                <option value="">Todas las Sucursales</option>
                                @if(isset($todasLasSucursales) && $todasLasSucursales->isNotEmpty())
                                    @foreach ($todasLasSucursales as $sucursal)
                                        <option value="{{ $sucursal->id_sucursal }}" {{ request('id_sucursal_filter') == $sucursal->id_sucursal ? 'selected' : '' }}>
                                            {{ $sucursal->nombre_sucursal }}
                                        </option>
                                    @endforeach
                                @else
                                    <option value="" disabled>No hay sucursales para filtrar</option>
                                @endif
                            </select>
                        </div>
                        <div class="col-md-2 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary btn-sm w-100">Buscar/Filtrar</button>
                        </div>
                        <div class="col-md-2 d-flex align-items-end">
                            @if(request('search_nombre_empleado') || request('id_sucursal_filter'))
                                <a href="{{ route('contratos.index') }}" class="btn btn-secondary btn-sm w-100">Limpiar</a>
                            @endif
                        </div>
                    </div>
                </form>
                {{-- Fin Filtros y Búsqueda --}}

                <div class="table-responsive">
                    <table class="table table-striped table-hover align-middle">
                        <thead>
                            <tr>
                                <th>Empleado</th>
                                <th>Puesto</th>
                                <th>Sucursal</th>
                                <th>Antigüedad</th>
                                <th>Tipo Últ. Contrato</th>
                                <th>Inicio Últ. Contrato</th>
                                <th>Fin Últ. Contrato</th>
                                <th>Duración</th>
                                <th class="text-center">Nº Contratos</th>
                                {{-- ================= NUEVA COLUMNA ================= --}}
                                <th class="text-center">Contrato Firmado</th>
                                {{-- ================================================= --}}
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($empleados as $empleado)
                                <tr>
                                    <td>{{ $empleado->nombre_completo }}</td>
                                    <td>{{ $empleado->puesto ? $empleado->puesto->nombre_puesto : 'N/A' }}</td>
                                    <td>{{ $empleado->sucursal ? $empleado->sucursal->nombre_sucursal : 'N/A' }}</td>
                                    <td>
                                        @if ($empleado->fecha_ingreso)
                                            {{ \Carbon\Carbon::parse($empleado->fecha_ingreso)->diffForHumans(null, true, false, 2) }}
                                        @else
                                            N/A
                                        @endif
                                    </td>
                                    <td>{{ $empleado->ultimoContrato ? $empleado->ultimoContrato->tipo_contrato : 'N/A' }}</td>
                                    <td>{{ $empleado->ultimoContrato?->fecha_inicio?->format('d/m/Y') ?? 'N/A' }}</td>
                                    <td>{{ $empleado->ultimoContrato?->fecha_fin?->format('d/m/Y') ?? 'N/A' }}</td>
                                    <td>
                                        @if ($empleado->ultimoContrato?->fecha_inicio && $empleado->ultimoContrato?->fecha_fin)
                                            {{ $empleado->ultimoContrato->fecha_inicio->diffForHumans($empleado->ultimoContrato->fecha_fin, true, false, 2) }}
                                        @else
                                            N/A
                                        @endif
                                    </td>
                                    <td class="text-center">{{ $empleado->contratos_count }}</td>
                                    
                                    {{-- ============ LÓGICA DE LA NUEVA COLUMNA ============ --}}
                                    <td class="text-center">
                                        @if ($empleado->ultimoContrato && $empleado->ultimoContrato->ruta_contrato_firmado)
                                            <a href="{{ route('contratos.verFirmado', $empleado->ultimoContrato->id_contrato) }}" class="btn btn-sm btn-success" target="_blank" title="Ver Contrato Firmado">
                                                <i class="bi bi-check-circle-fill"></i> Ver
                                            </a>
                                        @elseif ($empleado->ultimoContrato)
                                            <a href="{{ route('contratos.edit', $empleado->ultimoContrato->id_contrato) }}" class="btn btn-sm btn-warning" title="Subir Contrato Firmado">
                                                <i class="bi bi-upload"></i>
                                            </a>
                                        @else
                                            <span class="badge bg-secondary">N/A</span>
                                        @endif
                                    </td>
                                    {{-- ==================================================== --}}

                                    <td>
                                        <div class="btn-group" role="group">
                                            @if ($empleado->ultimoContrato)
                                                <a href="{{ route('contratos.pdf', $empleado->ultimoContrato->id_contrato) }}" class="btn btn-sm btn-primary" target="_blank" title="Generar PDF del Último Contrato">
                                                    <i class="bi bi-file-pdf"></i>
                                                </a>
                                                <a href="{{ route('contratos.edit', $empleado->ultimoContrato->id_contrato) }}" class="btn btn-sm btn-info" title="Editar Último Contrato">
                                                    <i class="bi bi-pencil-square"></i>
                                                </a>
                                                <form action="{{ route('contratos.destroy', $empleado->ultimoContrato->id_contrato) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-danger" title="Eliminar Último Contrato" onclick="return confirm('¿Estás seguro de que quieres eliminar este contrato? Esta acción no se puede deshacer.')" style="border-top-left-radius: 0; border-bottom-left-radius: 0;">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </form>
                                            @endif
                                            <a href="{{ route('contratos.create', ['id_empleado' => $empleado->id_empleado]) }}" class="btn btn-sm btn-dark" title="Nuevo Contrato para este Empleado">
                                                <i class="bi bi-file-earmark-plus"></i>
                                            </a>
                                            <a href="{{ route('empleados.contratos.historial', $empleado->id_empleado) }}" class="btn btn-sm btn-secondary" title="Ver Historial de Contratos de {{ $empleado->nombre_completo }}">
                                                <i class="bi bi-list-ul"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    {{-- El colspan debe ser igual al número total de columnas --}}
                                    <td colspan="11" class="text-center">No hay empleados activos registrados o que coincidan con la búsqueda.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                
                {{-- Enlaces de Paginación para los empleados --}}
                <div class="mt-3">
                    {{ $empleados->appends(request()->query())->links() }}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>