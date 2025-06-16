<x-app-layout>
    <div class="container-fluid py-4">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Lista de Sucursales</h5>
                <a href="{{ route('sucursales.create') }}" class="btn btn-success">
                    <i class="bi bi-plus-lg"></i> Nueva Sucursal
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
                                <th>Nombre de la Sucursal</th>
                                <th>Dirección</th>
                                {{-- Columna Teléfono Eliminada --}}
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($sucursales as $sucursal)
                                <tr>
                                    <td>{{ $sucursal->id_sucursal }}</td>
                                    <td>{{ $sucursal->nombre_sucursal }}</td>
                                    <td>{{ $sucursal->direccion_sucursal ?: 'N/A' }}</td>
                                    {{-- Celda Teléfono Eliminada --}}
                                    <td>
    {{-- Acciones de Editar y Eliminar deshabilitadas temporalmente --}}
    <button class="btn btn-sm btn-info disabled" title="Editar (Próximamente)"><i class="bi bi-pencil-square"></i></button>
    <button class="btn btn-sm btn-danger disabled" title="Eliminar (Próximamente)"><i class="bi bi-trash"></i></button>
</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center">No hay sucursales registradas.</td> {{-- Colspan ajustado a 4 --}}
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                {{-- Enlaces de Paginación --}}
                <div class="mt-3">
                    {{ $sucursales->links() }}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>