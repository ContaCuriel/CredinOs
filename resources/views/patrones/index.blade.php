<x-app-layout>
    <div class="container-fluid py-4">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
                <h5 class="mb-0 fw-bold text-primary">
                    <i class="bi bi-buildings me-2"></i>Lista de Patrones (Empresas/Contratantes)
                </h5>
                <a href="{{ route('patrones.create') }}" class="btn btn-success fw-bold shadow-sm">
                    <i class="bi bi-plus-lg me-1"></i> Nuevo Patrón
                </a>
            </div>
            <div class="card-body">
                @if (session('success'))
                    <div class="alert alert-success alert-dismissible fade show shadow-sm border-0" role="alert">
                        <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif
                
                @if (session('error'))
                    <div class="alert alert-danger alert-dismissible fade show shadow-sm border-0" role="alert">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i> {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                <div class="table-responsive">
                    <table class="table table-striped table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>ID</th>
                                <th>Nombre Comercial</th>
                                <th>Razón Social</th>
                                <th>Tipo</th>
                                <th>RFC</th>
                                <th>C.P.</th>
                                <th>Logo</th>
                                <th class="text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($patrones as $patron)
                                <tr>
                                    <td><span class="fw-bold text-secondary">{{ $patron->id_patron }}</span></td>
                                    <td>{{ $patron->nombre_comercial }}</td>
                                    <td>{{ $patron->razon_social }}</td>
                                    <td>{{ ucfirst($patron->tipo_persona) }}</td>
                                    <td><span class="badge bg-light text-dark border">{{ $patron->rfc }}</span></td>
                                    <td>{{ $patron->codigo_postal ?? 'N/A' }}</td>
                                    <td>
                                        @if ($patron->logo_path)
                                            <img src="{{ asset('storage/' . $patron->logo_path) }}" alt="Logo" class="rounded shadow-sm" style="max-height: 40px; max-width: 100px; object-fit: contain;">
                                        @else
                                            <span class="text-muted small">N/A</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <div class="btn-group shadow-sm" role="group" aria-label="Acciones de patrón">
                                            {{-- BOTÓN: Cambiar Logo --}}
                                            <a href="{{ route('patrones.logo.edit', $patron->id_patron) }}" class="btn btn-sm btn-success" title="Cambiar Logo">
                                                <i class="bi bi-image"></i>
                                            </a>
                                            
                                            {{-- NUEVO BOTÓN: Subir CSD --}}
                                            <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#modalCSD_{{ $patron->id_patron }}" title="Subir Certificados SAT">
                                                <i class="bi bi-shield-lock-fill"></i> CSD
                                            </button>

                                            {{-- Botones originales --}}
                                            <a href="#" class="btn btn-sm btn-info disabled" title="Editar Patrón Completo">
                                                <i class="bi bi-pencil-square"></i>
                                            </a>
                                            <button type="button" class="btn btn-sm btn-danger disabled" title="Eliminar Patrón">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </div>

                                        {{-- Indicador visual de si tiene CSD --}}
                                        @if($patron->csd_cer_path && $patron->csd_key_path)
                                            <div class="mt-2">
                                                <span class="badge bg-success" style="font-size: 0.70rem;">
                                                    <i class="bi bi-check-circle me-1"></i> Sellos Activos
                                                </span>
                                            </div>
                                        @else
                                            <div class="mt-2">
                                                <span class="badge bg-warning text-dark" style="font-size: 0.70rem;">
                                                    <i class="bi bi-exclamation-triangle me-1"></i> Faltan Sellos
                                                </span>
                                            </div>
                                        @endif
                                    </td>
                                </tr>

                                {{-- MODAL CSD --}}
                                <div class="modal fade" id="modalCSD_{{ $patron->id_patron }}" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog">
                                        <div class="modal-content border-0 shadow">
                                            <div class="modal-header bg-dark text-white">
                                                <h5 class="modal-title fs-6"><i class="bi bi-shield-lock me-2"></i>Certificados de Sello Digital (CSD)</h5>
                                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <form action="{{ route('patrones.csd.store', $patron->id_patron) }}" method="POST" enctype="multipart/form-data">
                                                @csrf
                                                <div class="modal-body">
                                                    <div class="alert alert-info py-2 small mb-4">
                                                        Sube los archivos <strong>.cer</strong> y <strong>.key</strong> correspondientes al CSD de <strong>{{ $patron->razon_social }}</strong>. Estos se sincronizarán directamente con Facturama para el timbrado de nómina.
                                                    </div>

                                                    <div class="mb-3">
                                                        <label class="form-label fw-bold small text-secondary">Archivo .cer</label>
                                                        <input type="file" name="csd_cer" class="form-control form-control-sm" accept=".cer" required>
                                                    </div>

                                                    <div class="mb-3">
                                                        <label class="form-label fw-bold small text-secondary">Archivo .key</label>
                                                        <input type="file" name="csd_key" class="form-control form-control-sm" accept=".key" required>
                                                    </div>

                                                    <div class="mb-3">
                                                        <label class="form-label fw-bold small text-secondary">Contraseña del CSD</label>
                                                        <input type="password" name="csd_password" class="form-control form-control-sm" required placeholder="Contraseña de la clave privada">
                                                    </div>
                                                </div>
                                                <div class="modal-footer py-2 bg-light">
                                                    <button type="button" class="btn btn-secondary btn-sm fw-bold" data-bs-dismiss="modal">Cancelar</button>
                                                    <button type="submit" class="btn btn-primary btn-sm fw-bold">
                                                        <i class="bi bi-cloud-upload me-1"></i> Subir a Facturama
                                                    </button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                                {{-- FIN MODAL CSD --}}

                            @empty
                                <tr>
                                    <td colspan="8" class="text-center text-muted py-5">
                                        <i class="bi bi-buildings fs-2 d-block mb-2 text-secondary opacity-50"></i>
                                        No hay patrones registrados en el sistema.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                
                @if($patrones->hasPages())
                    <div class="mt-3 border-top pt-3">
                        {{ $patrones->links() }}
                    </div>
                @endif

            </div>
        </div>
    </div>
</x-app-layout>