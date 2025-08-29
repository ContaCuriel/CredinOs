<x-app-layout>
    <div class="container-fluid py-4">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Lista de Patrones (Empresas/Contratantes)</h5>
                <a href="{{ route('patrones.create') }}" class="btn btn-success">
                    <i class="bi bi-plus-lg"></i> Nuevo Patrón
                </a>
            </div>
            <div class="card-body">
                @if (session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif
                @if (session('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>ID</th>
                                <th>Nombre Comercial</th>
                                <th>Razón Social</th>
                                <th>Tipo</th>
                                <th>RFC</th>
                                <th>Logo</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($patrones as $patron)
                                <tr>
                                    <td>{{ $patron->id_patron }}</td>
                                    <td>{{ $patron->nombre_comercial }}</td>
                                    <td>{{ $patron->razon_social }}</td>
                                    <td>{{ ucfirst($patron->tipo_persona) }}</td>
                                    <td>{{ $patron->rfc }}</td>
                                    <td>
                                        @if ($patron->logo_path)
                                            <img src="{{ asset('storage/' . $patron->logo_path) }}" alt="Logo" style="max-height: 40px; max-width: 100px;">
                                        @else
                                            N/A
                                        @endif
                                    </td>
                                                                            <td>
                                            <div class="btn-group" role="group" aria-label="Acciones de patrón">
                                                {{-- ESTE ES EL NUEVO BOTÓN PARA CAMBIAR LOGO --}}
                                                <a href="{{ route('patrones.logo.edit', $patron->id_patron) }}" class="btn btn-sm btn-success" title="Cambiar Logo">
                                                    <i class="bi bi-image"></i>
                                                </a>

                                                {{-- Botones originales (aún no funcionan, pero los dejamos) --}}
                                                <a href="#" class="btn btn-sm btn-info disabled" title="Editar Patrón Completo">
                                                    <i class="bi bi-pencil-square"></i>
                                                </a>
                                                <button type="button" class="btn btn-sm btn-danger disabled" title="Eliminar Patrón">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center">No hay patrones registrados.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="mt-3">
                    {{ $patrones->links() }}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
