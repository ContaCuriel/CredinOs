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
                                        <!-- Botón de Editar -->
                                        <a href="{{ route('patrones.edit', $patron->id_patron) }}" class="btn btn-sm btn-info" title="Editar">
                                            <i class="bi bi-pencil-square"></i>
                                        </a>

                                        <!-- Formulario para el botón de Eliminar -->
                                        <form action="{{ route('patrones.destroy', $patron->id_patron) }}" method="POST" class="d-inline" onsubmit="return confirm('¿Estás seguro de que deseas eliminar este patrón? Esta acción no se puede deshacer.');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger" title="Eliminar">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
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
