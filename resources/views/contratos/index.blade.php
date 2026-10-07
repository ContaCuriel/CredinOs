<x-app-layout>
    <style>
        /* Fondo Animado iOS Mesh Gradient Sutil */
        .bg-system-islands {
            background: linear-gradient(-45deg, #f0f4fd, #f4f0fa, #f0faf5, #f5f8fc);
            background-size: 400% 400%;
            animation: subtleMesh 20s ease infinite;
            min-height: calc(100vh - 60px);
            padding-bottom: 3rem;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
        }

        @keyframes subtleMesh {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }

        /* Tarjetas Estilo Isla (Glassmorphism) */
        .ios-island {
            background: rgba(255, 255, 255, 0.85);
            backdrop-filter: blur(24px);
            -webkit-backdrop-filter: blur(24px);
            border: 1px solid rgba(255, 255, 255, 0.9);
            border-radius: 1.5rem;
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.04);
            padding: 1.5rem;
        }

        .ios-table-container {
            background: rgba(255, 255, 255, 0.85);
            backdrop-filter: blur(24px);
            -webkit-backdrop-filter: blur(24px);
            border-radius: 1.5rem;
            overflow: hidden;
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.04);
            border: 1px solid rgba(255, 255, 255, 0.9);
        }

        /* Tablas estilo iOS */
        .table-ios { margin-bottom: 0; }
        .table-ios thead th {
            background-color: rgba(245, 245, 247, 0.6) !important;
            color: #6e6e73 !important;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.75rem;
            letter-spacing: 0.5px;
            border-bottom: 1px solid rgba(0,0,0,0.05);
            padding: 1rem 1rem;
        }
        .table-ios tbody td {
            border-bottom: 1px solid rgba(0,0,0,0.03);
            padding: 1rem;
            vertical-align: middle;
            color: #1d1d1f;
        }
        .table-ios tbody tr:hover td {
            background-color: rgba(0,0,0,0.015);
        }

        /* Botones estilo iOS */
        .btn-ios-primary {
            background: linear-gradient(135deg, #0071e3, #005bb5);
            color: white;
            border-radius: 2rem;
            font-weight: 500;
            padding: 0.5rem 1.2rem;
            border: none;
            box-shadow: 0 4px 10px rgba(0, 113, 227, 0.2);
            transition: all 0.2s;
        }
        .btn-ios-primary:hover {
            background: linear-gradient(135deg, #0077ED, #0062c4);
            transform: translateY(-1px);
            box-shadow: 0 6px 15px rgba(0, 113, 227, 0.35);
            color: white;
        }
        
        .btn-ios-success {
            background: linear-gradient(135deg, #34c759, #28a745);
            color: white;
            border-radius: 2rem;
            font-weight: 500;
            padding: 0.5rem 1.2rem;
            border: none;
            box-shadow: 0 4px 10px rgba(40, 167, 69, 0.2);
            transition: all 0.2s;
        }
        .btn-ios-success:hover {
            background: linear-gradient(135deg, #3dd262, #2eb34c);
            transform: translateY(-1px);
            box-shadow: 0 6px 15px rgba(40, 167, 69, 0.35);
            color: white;
        }

        .btn-ios-outline {
            background-color: rgba(255,255,255,0.6);
            color: #6e6e73;
            border-radius: 2rem;
            font-weight: 600;
            padding: 0.5rem 1.2rem;
            border: 1px solid rgba(0,0,0,0.1);
            transition: all 0.2s;
        }
        .btn-ios-outline:hover {
            background-color: rgba(0,0,0,0.03);
            color: #1d1d1f;
        }

        /* Controles de Formulario */
        .form-control-ios, .form-select-ios {
            border-radius: 0.75rem;
            border: 1px solid rgba(0,0,0,0.1);
            background-color: rgba(255,255,255,0.8);
            padding: 0.5rem 1rem;
            color: #1d1d1f;
            transition: all 0.3s ease;
        }
        .form-control-ios:focus, .form-select-ios:focus {
            background-color: #ffffff;
            border-color: #0071e3;
            box-shadow: 0 0 0 3px rgba(0, 113, 227, 0.15);
            outline: none;
        }

        /* Botones de acción minimalistas */
        .btn-action-ios {
            border-radius: 0.5rem;
            padding: 0.25rem 0.6rem;
            font-size: 0.9rem;
            transition: all 0.2s ease;
            border: none;
        }
        .btn-action-ios:hover { transform: scale(1.05); }
    </style>

    <div class="container-fluid pt-4 px-4 bg-system-islands">
        
        {{-- HEADER PRINCIPAL TRANSPARENTE --}}
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h3 class="mb-0 fw-bold" style="color: #1d1d1f; letter-spacing: -0.5px;">Panorama Contractual</h3>
                <p class="text-muted mb-0" style="font-size: 0.85rem;">Gestión de contratos de empleados activos</p>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('contratos.exportarExcel', request()->query()) }}" class="btn-ios-success text-decoration-none shadow-sm">
                    <i class="bi bi-file-earmark-excel-fill me-1"></i> Exportar a Excel
                </a>
                <a href="{{ route('contratos.create') }}" class="btn-ios-primary text-decoration-none shadow-sm">
                    <i class="bi bi-file-earmark-plus-fill me-1"></i> Nuevo Contrato
                </a>
            </div>
        </div>

        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show ios-island mb-4 py-3 border-0 d-flex align-items-center" role="alert" style="background-color: rgba(229, 249, 231, 0.85); color: #198754;">
                <i class="bi bi-check-circle-fill me-2 fs-5"></i>{{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif
        @if (session('error'))
            <div class="alert alert-danger alert-dismissible fade show ios-island mb-4 py-3 border-0 d-flex align-items-center" role="alert" style="background-color: rgba(252, 232, 230, 0.85); color: #dc3545;">
                <i class="bi bi-exclamation-circle-fill me-2 fs-5"></i>{{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        {{-- ISLA 1: FORMULARIO DE FILTROS --}}
        <form method="GET" action="{{ route('contratos.index') }}" class="ios-island mb-4">
            <div class="row align-items-end g-3 justify-content-center">
                
                <div class="col-md-4">
                    <label for="search_nombre_empleado" class="form-label fw-bold mb-1 ms-1" style="font-size: 0.8rem; color: #86868b;">Buscar Empleado</label>
                    <input type="text" name="search_nombre_empleado" id="search_nombre_empleado" class="form-control form-control-ios fw-semibold" value="{{ request('search_nombre_empleado') }}" placeholder="Nombre del empleado...">
                </div>

                <div class="col-md-4">
                    <label for="id_sucursal_filter" class="form-label fw-bold mb-1 ms-1" style="font-size: 0.8rem; color: #86868b;">Sucursal</label>
                    <select name="id_sucursal_filter" id="id_sucursal_filter" class="form-select form-select-ios fw-semibold">
                        <option value="">-- TODAS LAS SUCURSALES --</option>
                        @if(isset($todasLasSucursales) && $todasLasSucursales->isNotEmpty())
                            @foreach ($todasLasSucursales as $sucursal)
                                <option value="{{ $sucursal->id_sucursal }}" {{ request('id_sucursal_filter') == $sucursal->id_sucursal ? 'selected' : '' }}>
                                    {{ $sucursal->nombre_sucursal }}
                                </option>
                            @endforeach
                        @endif
                    </select>
                </div>

                <div class="col-md-4 d-flex gap-2">
                    <button type="submit" class="btn-ios-primary flex-fill shadow-sm py-2">
                        <i class="bi bi-funnel-fill me-1"></i> Filtrar
                    </button>
                    @if(request('search_nombre_empleado') || request('id_sucursal_filter'))
                        <a href="{{ route('contratos.index') }}" class="btn-ios-outline text-decoration-none shadow-sm py-2" title="Limpiar Filtros">
                            <i class="bi bi-eraser-fill"></i>
                        </a>
                    @endif
                </div>

            </div>
        </form>

        {{-- ISLA 2: TABLA DE DATOS --}}
        <div class="ios-table-container mx-auto mb-5">
            <div class="table-responsive">
                <table class="table table-ios table-hover align-middle">
                    <thead style="position: sticky; top: 0; z-index: 10;">
                        <tr>
                            <th>Empleado</th>
                            <th>Puesto & Sucursal</th>
                            <th>Antigüedad</th>
                            <th>Tipo Últ. Contrato</th>
                            <th>Fechas del Contrato</th>
                            <th class="text-center">Días</th>
                            <th class="text-center">Totales</th>
                            <th class="text-center">Firmado</th>
                            <th class="text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($empleados as $empleado)
                            <tr>
                                <td>
                                    <span class="fw-bold" style="color: #1d1d1f; font-size: 0.95rem;">{{ $empleado->nombre_completo }}</span>
                                </td>
                                <td>
                                    <span class="badge rounded-pill fw-medium d-inline-block mb-1" style="font-size: 0.65rem; color: #0071e3; background: rgba(0, 113, 227, 0.1);">
                                        {{ $empleado->puesto ? $empleado->puesto->nombre_puesto : 'N/A' }}
                                    </span>
                                    <br>
                                    <span class="text-muted fw-medium" style="font-size: 0.75rem;">
                                        <i class="bi bi-shop me-1 opacity-50"></i>{{ $empleado->sucursal ? $empleado->sucursal->nombre_sucursal : 'S/S' }}
                                    </span>
                                </td>
                                <td>
                                    <span class="fw-semibold text-secondary" style="font-size: 0.85rem;">
                                        @if ($empleado->fecha_ingreso)
                                            {{ \Carbon\Carbon::parse($empleado->fecha_ingreso)->diffForHumans(null, true, false, 2) }}
                                        @else
                                            N/A
                                        @endif
                                    </span>
                                </td>
                                <td>
                                    @php
                                        $tipoContrato = $empleado->ultimoContrato ? $empleado->ultimoContrato->tipo_contrato : 'N/A';
                                        
                                        // Estilos seguros en línea en lugar de depender de clases de Bootstrap conflictivas
                                        $iosStyle = 'color: #6e6e73; background: rgba(110, 110, 115, 0.1); border: 1px solid rgba(110, 110, 115, 0.2);'; // Por defecto (N/A)
                                        
                                        if($tipoContrato == 'Indeterminado') {
                                            $iosStyle = 'color: #198754; background: rgba(25, 135, 84, 0.1); border: 1px solid rgba(25, 135, 84, 0.2);';
                                        } elseif($tipoContrato == 'Determinado') {
                                            $iosStyle = 'color: #0071e3; background: rgba(0, 113, 227, 0.1); border: 1px solid rgba(0, 113, 227, 0.2);';
                                        } elseif($tipoContrato == 'Honorarios') {
                                            $iosStyle = 'color: #6f42c1; background: rgba(111, 66, 193, 0.1); border: 1px solid rgba(111, 66, 193, 0.2);';
                                        } elseif($tipoContrato == 'Sueldo Variable') {
                                            $iosStyle = 'color: #d97706; background: rgba(217, 119, 6, 0.1); border: 1px solid rgba(217, 119, 6, 0.2);';
                                        }
                                    @endphp
                                    <span class="badge rounded-pill fw-bold px-3 py-1 shadow-sm" style="font-size: 0.75rem; {{ $iosStyle }}">
                                        {{ $tipoContrato }}
                                    </span>
                                </td>
                                <td>
                                    @if ($empleado->ultimoContrato)
                                        <div style="font-size: 0.8rem;">
                                            <span class="text-muted">Inicio:</span> <span class="fw-semibold text-dark">{{ $empleado->ultimoContrato->fecha_inicio?->format('d/m/Y') ?? 'N/A' }}</span><br>
                                            <span class="text-muted">Fin:</span> <span class="fw-semibold text-dark">{{ $empleado->ultimoContrato->fecha_fin?->format('d/m/Y') ?? 'N/A' }}</span>
                                        </div>
                                    @else
                                        <span class="text-muted opacity-50">-</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <span class="fw-bold" style="font-size: 0.9rem; color: #1d1d1f;">
                                        @if ($empleado->ultimoContrato?->fecha_inicio && $empleado->ultimoContrato?->fecha_fin)
                                            {{ $empleado->ultimoContrato->fecha_inicio->diffInDays($empleado->ultimoContrato->fecha_fin) }}
                                        @else
                                            -
                                        @endif
                                    </span>
                                </td>
                                <td class="text-center">
                                    <span class="badge rounded-circle shadow-sm" style="background: linear-gradient(135deg, #a280df 0%, #7e57c2 100%); width: 28px; height: 28px; display: inline-flex; align-items: center; justify-content: center; font-size: 0.85rem;">
                                        {{ $empleado->contratos_count }}
                                    </span>
                                </td>
                                
                                {{-- CONTRATO FIRMADO --}}
                                <td class="text-center">
                                    @if ($empleado->ultimoContrato && $empleado->ultimoContrato->ruta_contrato_firmado)
                                        <a href="{{ route('contratos.verFirmado', $empleado->ultimoContrato->id_contrato) }}" class="btn-action-ios shadow-sm text-decoration-none" style="background: rgba(25,135,84,0.1); color: #198754;" target="_blank" title="Ver Contrato Firmado">
                                            <i class="bi bi-file-earmark-check-fill"></i>
                                        </a>
                                    @elseif ($empleado->ultimoContrato)
                                        <a href="{{ route('contratos.edit', $empleado->ultimoContrato->id_contrato) }}" class="btn-action-ios shadow-sm text-decoration-none" style="background: rgba(255,193,7,0.15); color: #b78a00;" title="Subir Contrato Firmado">
                                            <i class="bi bi-upload"></i>
                                        </a>
                                    @else
                                        <span class="text-muted opacity-25"><i class="bi bi-dash-lg"></i></span>
                                    @endif
                                </td>

                                {{-- ACCIONES --}}
                                <td>
                                    <div class="d-flex justify-content-center gap-1">
                                        @if ($empleado->ultimoContrato)
                                            <a href="{{ route('contratos.pdf', $empleado->ultimoContrato->id_contrato) }}" class="btn-action-ios shadow-sm text-decoration-none" style="background: rgba(13,110,253,0.1); color: #0d6efd;" target="_blank" title="Generar PDF del Último Contrato">
                                                <i class="bi bi-file-pdf-fill"></i>
                                            </a>
                                            <a href="{{ route('contratos.edit', $empleado->ultimoContrato->id_contrato) }}" class="btn-action-ios shadow-sm text-decoration-none" style="background: rgba(13,202,240,0.1); color: #0dcaf0;" title="Editar Último Contrato">
                                                <i class="bi bi-pencil-square"></i>
                                            </a>
                                            <form action="{{ route('contratos.destroy', $empleado->ultimoContrato->id_contrato) }}" method="POST" class="m-0 p-0 d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn-action-ios shadow-sm" style="background: rgba(220,53,69,0.1); color: #dc3545;" title="Eliminar Último Contrato" onclick="return confirm('¿Estás seguro de que quieres eliminar este contrato? Esta acción no se puede deshacer.')">
                                                    <i class="bi bi-trash-fill"></i>
                                                </button>
                                            </form>
                                        @endif
                                        <a href="{{ route('contratos.create', ['id_empleado' => $empleado->id_empleado]) }}" class="btn-action-ios shadow-sm text-decoration-none" style="background: rgba(33,37,41,0.1); color: #212529;" title="Nuevo Contrato para este Empleado">
                                            <i class="bi bi-plus-lg"></i>
                                        </a>
                                        <a href="{{ route('empleados.contratos.historial', $empleado->id_empleado) }}" class="btn-action-ios shadow-sm text-decoration-none" style="background: rgba(108,117,125,0.1); color: #6c757d;" title="Ver Historial de Contratos">
                                            <i class="bi bi-clock-history"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="11" class="text-center py-5">
                                    <i class="bi bi-folder2-open text-muted opacity-25" style="font-size: 3rem;"></i>
                                    <h5 class="fw-bold mt-3 text-dark">No se encontraron contratos</h5>
                                    <p class="text-muted mb-0">No hay empleados activos registrados o que coincidan con la búsqueda actual.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Paginación (Sutil) --}}
            @if($empleados->hasPages())
                <div class="px-4 py-3 border-top" style="background: rgba(245, 245, 247, 0.5);">
                    {{ $empleados->appends(request()->query())->links('pagination::bootstrap-5') }}
                </div>
            @endif
        </div>
    </div>
</x-app-layout>