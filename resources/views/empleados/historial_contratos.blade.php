<x-app-layout>
    <div class="container-fluid py-4">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Historial de Contratos de: {{ $empleado->nombre_completo }}</h5>
                <a href="{{ route('contratos.index') }}" class="btn btn-secondary">
                    <i class="bi bi-arrow-left"></i> Volver al Panorama Contractual
                </a>
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

                @if ($contratos->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-striped table-hover align-middle">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Tipo Patrón</th>
                                    <th>Tipo Contrato</th>
                                    <th>Fecha Inicio</th>
                                    <th>Fecha Fin</th>
                                    <th>Duración</th>
                                    {{-- ================== NUEVA COLUMNA AÑADIDA ================== --}}
                                    <th class="text-center">Contrato Firmado</th>
                                    {{-- ========================================================== --}}
                                    <th class="text-end">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($contratos as $contrato)
                                    <tr>
                                        <td>{{ $contrato->id_contrato }}</td>
                                        <td>
                                            @if ($contrato->patron_tipo == 'fisica')
                                                Persona Física
                                            @elseif ($contrato->patron_tipo == 'moral')
                                                Persona Moral
                                            @else
                                                {{ $contrato->patron_tipo ?: 'N/A' }}
                                            @endif
                                        </td>
                                        <td>{{ $contrato->tipo_contrato ?: 'N/A' }}</td>
                                        <td>{{ $contrato->fecha_inicio ? $contrato->fecha_inicio->format('d/m/Y') : 'N/A' }}</td>
                                        <td>{{ $contrato->fecha_fin ? $contrato->fecha_fin->format('d/m/Y') : 'N/A' }}</td>
                                        <td>
                                            @if ($contrato->fecha_inicio && $contrato->fecha_fin)
                                                {{ $contrato->fecha_inicio->diffForHumans($contrato->fecha_fin, true, false, 2) }}
                                            @else
                                                N/A
                                            @endif
                                        </td>
                                        
                                        {{-- ============ LÓGICA DE LA NUEVA COLUMNA ============ --}}
                                        <td class="text-center">
                                            @if ($contrato->ruta_contrato_firmado)
                                                <a href="{{ route('contratos.verFirmado', $contrato->id_contrato) }}" class="btn btn-sm btn-success" target="_blank" title="Ver Contrato Firmado">
                                                    <i class="bi bi-check-circle-fill"></i> Ver
                                                </a>
                                            @else
                                                <a href="{{ route('contratos.edit', $contrato->id_contrato) }}" class="btn btn-sm btn-warning" title="Subir Contrato Firmado">
                                                    <i class="bi bi-upload"></i> Subir
                                                </a>
                                            @endif
                                        </td>
                                        {{-- ==================================================== --}}

                                        <td class="text-end">
                                            {{-- ============ BOTONES DE ACCIÓN MEJORADOS ============ --}}
                                            <div class="btn-group" role="group">
                                                <a href="{{ route('contratos.pdf', $contrato->id_contrato) }}" class="btn btn-sm btn-primary" target="_blank" title="Generar PDF del Contrato">
                                                    <i class="bi bi-file-pdf"></i>
                                                </a>
                                                <a href="{{ route('contratos.edit', $contrato->id_contrato) }}" class="btn btn-sm btn-info" title="Editar Contrato">
                                                    <i class="bi bi-pencil-square"></i>
                                                </a>
                                                <form action="{{ route('contratos.destroy', $contrato->id_contrato) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-danger" title="Eliminar Contrato" onclick="return confirm('¿Estás seguro de que quieres eliminar este contrato del historial? Esta acción no se puede deshacer.')" style="border-top-left-radius: 0; border-bottom-left-radius: 0;">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    {{-- Enlaces de Paginación para los contratos del historial --}}
                    <div class="mt-3">
                        {{ $contratos->links() }}
                    </div>
                @else
                    <div class="alert alert-info">
                        Este empleado no tiene contratos registrados en su historial.
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>