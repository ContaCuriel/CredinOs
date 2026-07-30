<x-app-layout>
    <div class="container-fluid py-4">
        <div class="card shadow-sm">
            <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
                <h5 class="mb-0 fw-bold text-primary">Gestión de Deducciones y Préstamos</h5>
                <a href="{{ route('deducciones.create') }}" class="btn btn-success fw-bold">
                    <i class="bi bi-plus-lg me-1"></i> Registrar Nueva Deducción
                </a>
            </div>
            <div class="card-body">
                @if (session('success'))
                    <div class="alert alert-success alert-dismissible fade show shadow-sm border-0 mb-4" role="alert">
                        <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                @if (session('error'))
                    <div class="alert alert-danger alert-dismissible fade show shadow-sm border-0 mb-4" role="alert">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i> {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                {{-- Formulario de Filtros --}}
                <form method="GET" action="{{ route('deducciones.index') }}" class="mb-4 p-3 bg-light rounded border shadow-sm">
                    <div class="row align-items-end g-2">
                        <div class="col-md-2">
                            <label for="search_nombre" class="form-label mb-1 small fw-bold">Empleado:</label>
                            <input type="text" name="search_nombre" id="search_nombre" class="form-control form-control-sm" value="{{ request('search_nombre') }}" placeholder="Nombre...">
                        </div>
                        <div class="col-md-2">
                            <label for="id_sucursal_filter" class="form-label mb-1 small fw-bold">Sucursal:</label>
                            <select name="id_sucursal_filter" id="id_sucursal_filter" class="form-select form-select-sm">
                                <option value="">Todas</option>
                                @if(isset($sucursales))
                                    @foreach ($sucursales as $sucursal)
                                        <option value="{{ $sucursal->id_sucursal }}" {{ request('id_sucursal_filter') == $sucursal->id_sucursal ? 'selected' : '' }}>{{ $sucursal->nombre_sucursal }}</option>
                                    @endforeach
                                @endif
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label for="tipo_deduccion_filter" class="form-label mb-1 small fw-bold">Tipo:</label>
                            <select name="tipo_deduccion_filter" id="tipo_deduccion_filter" class="form-select form-select-sm">
                                <option value="">Todos</option>
                                 @if(isset($tipos_deduccion))
                                    @foreach ($tipos_deduccion as $tipo)
                                        <option value="{{ $tipo }}" {{ request('tipo_deduccion_filter') == $tipo ? 'selected' : '' }}>{{ $tipo }}</option>
                                    @endforeach
                                @endif
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label for="status_filter" class="form-label mb-1 small fw-bold">Estatus:</label>
                            <select name="status_filter" id="status_filter" class="form-select form-select-sm">
                                <option value="Activo" {{ request('status_filter', 'Activo') == 'Activo' ? 'selected' : '' }}>Activos</option>
                                <option value="Finalizado" {{ request('status_filter') == 'Finalizado' ? 'selected' : '' }}>Finalizados</option>
                                <option value="Pagado" {{ request('status_filter') == 'Pagado' ? 'selected' : '' }}>Pagados</option>
                                <option value="Todas" {{ request('status_filter') == 'Todas' ? 'selected' : '' }}>TODAS (Historial)</option>
                            </select>
                        </div>
                        <div class="col-md-2 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary btn-sm w-100 fw-bold"><i class="bi bi-search me-1"></i> Buscar/Filtrar</button>
                        </div>
                        <div class="col-md-1 d-flex align-items-end">
                            @if(request('search_nombre') || request('id_sucursal_filter') || request('tipo_deduccion_filter') || request('status_filter'))
                                <a href="{{ route('deducciones.index') }}" class="btn btn-secondary btn-sm w-100" title="Limpiar Filtros"><i class="bi bi-eraser"></i></a>
                            @endif
                        </div>
                    </div>
                </form>

                <div class="d-flex justify-content-end mb-3">
                    <a href="{{ route('deducciones.exportar', request()->query()) }}" class="btn btn-outline-success btn-sm fw-bold">
                        <i class="bi bi-file-earmark-excel me-1"></i> Exportar a Excel
                    </a>
                </div>

                <div class="table-responsive">
                    <table class="table table-striped table-hover table-sm align-middle shadow-sm">
                        <thead class="table-light">
                            <tr>
                                <th>Empleado</th>
                                <th>Tipo de Deducción</th>
                                <th class="text-center">Fecha Inicio</th>
                                <th class="text-center">Plazo / Pagadas</th>
                                <th class="text-center">Último Descuento</th>
                                <th class="text-end">Monto Quincenal</th>
                                <th class="text-end">Monto Acumulado / Saldo Pendiente</th>
                                <th class="text-center">Status</th>
                                <th class="text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($deducciones as $deduccion)
                                @php
                                    $esPlazo = in_array($deduccion->tipo_deduccion, ['Préstamo', 'Previsión']);
                                @endphp
                                <tr class="{{ $deduccion->status !== 'Activo' ? 'text-muted table-light' : '' }}">
                                    <td>{{ $deduccion->empleado ? $deduccion->empleado->nombre_completo : 'Empleado no encontrado' }}</td>
                                    <td>
                                        <span class="fw-bold">{{ $deduccion->tipo_deduccion }}</span>
                                    </td>
                                    <td class="text-center">{{ \Carbon\Carbon::parse($deduccion->fecha_solicitud)->format('d/m/Y') }}</td>
                                    
                                    {{-- 🔥 CORREGIDO: AHORA MUESTRA PLAZO / PAGADAS EN PRÉSTAMO Y PREVISIÓN 🔥 --}}
                                    <td class="text-center">
                                        @if ($esPlazo)
                                            <span class="fw-bold text-dark" title="Plazo Total / Quincenas Pagadas">
                                                {{ $deduccion->plazo_quincenas ?? 'N/A' }} / {{ $deduccion->quincenas_pagadas ?? 0 }}
                                            </span>
                                        @else
                                            <span class="text-muted">N/A</span>
                                        @endif
                                    </td>
                                    
                                    <td class="text-center">
                                        @if($deduccion->fecha_ultimo_descuento)
                                            {{ \Carbon\Carbon::parse($deduccion->fecha_ultimo_descuento)->format('d/m/Y') }}
                                        @else
                                            <span class="text-muted">N/A</span>
                                        @endif
                                    </td>
                                    <td class="text-end fw-bold">$ {{ number_format($deduccion->monto_quincenal, 2) }}</td>
                                    
                                    {{-- 🔥 CORREGIDO: MUESTRA SALDO PENDIENTE EN ROJO PARA PRÉSTAMO Y PREVISIÓN 🔥 --}}
                                    <td class="text-end fw-bold">
                                        @if ($esPlazo)
                                            <span class="text-danger" title="Saldo Pendiente">$ {{ number_format($deduccion->saldo_pendiente ?? 0, 2) }}</span>
                                        @else
                                            <span class="text-success" title="Monto Acumulado">$ {{ number_format($deduccion->monto_acumulado ?? 0, 2) }}</span>
                                        @endif
                                    </td>
                                    
                                    <td class="text-center">
                                        @php
                                            $badgeClass = match($deduccion->status) {
                                                'Activo' => 'bg-primary',
                                                'Pagado' => 'bg-success',
                                                'Finalizado' => 'bg-secondary',
                                                default => 'bg-dark'
                                            };
                                        @endphp
                                        <span class="badge {{ $badgeClass }}">{{ $deduccion->status }}</span>
                                    </td>
                                    <td class="text-center">
                                        <div class="btn-group btn-group-sm">
                                            <a href="{{ route('deducciones.edit', $deduccion->id_deduccion ?? $deduccion->id) }}" class="btn btn-info text-white" title="Editar Deducción">
                                                <i class="bi bi-pencil-square"></i>
                                            </a>
                                            
                                            @if($deduccion->status === 'Activo')
                                                <form action="{{ route('deducciones.destroy', $deduccion->id_deduccion ?? $deduccion->id) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-danger" title="Finalizar Deducción" onclick="return confirm('¿Estás seguro de finalizar este registro? Se moverá al historial.')">
                                                        <i class="bi bi-check-circle"></i>
                                                    </button>
                                                </form>
                                            @else
                                                <button class="btn btn-light disabled"><i class="bi bi-lock"></i></button>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="text-center text-muted py-4">No hay deducciones que coincidan con los filtros seleccionados.</td>
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