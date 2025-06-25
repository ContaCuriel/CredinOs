<x-app-layout>
    <div class="container-fluid py-4">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Gestión de Roles</h5>
                @can('crear-roles')
                    <a href="{{ route('roles.create') }}" class="btn btn-success">
                        <i class="bi bi-plus-lg"></i> Nuevo Rol
                    </a>
                @endcan
            </div>
            <div class="card-body">
                {{-- Mostramos el mensaje de éxito si existe --}}
                @if (session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                <div class="table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead class="table-light">
                            <tr>
                                <th>ID</th>
                                <th>Nombre del Rol</th>
                                <th style="width: 200px;">Acciones</th> {{-- Aumentamos un poco el ancho --}}
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($roles as $role)
                                <tr>
                                    <td>{{ $role->id }}</td>
                                    <td>{{ $role->name }}</td>
                                    <td>
                                        @can('editar-roles')
                                            <a href="{{ route('roles.edit', $role->id) }}" class="btn btn-sm btn-info" title="Editar">
                                                <i class="bi bi-pencil-square"></i>
                                            </a>
                                        @endcan
                                        
                                        {{-- ▼▼▼ CÓDIGO NUEVO PARA ELIMINAR ▼▼▼ --}}
                                        @can('eliminar-roles')
                                            {{-- Protegemos el rol Super-Admin para que no se pueda borrar --}}
                                            @if ($role->name != 'Super-Admin')
                                                <form action="{{ route('roles.destroy', $role->id) }}" method="POST" style="display:inline;" onsubmit="return confirm('¿Estás seguro de que quieres eliminar este rol? Esta acción no se puede deshacer.');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-danger" title="Eliminar">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </form>
                                            @endif
                                        @endcan
                                        {{-- ▲▲▲ FIN DEL CÓDIGO NUEVO ▲▲▲ --}}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="text-center">No hay roles registrados.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>