<x-app-layout>
    <div class="container-fluid py-4">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Lista de Grupos</h5>
                <a href="{{ route('groups.create') }}" class="btn btn-success">
                    <i class="bi bi-plus-lg"></i> Nuevo Grupo
                </a>
            </div>
            <div class="card-body">
                @if (session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th scope="col">Nombre del Grupo</th>
                                <th scope="col">Sucursal</th>
                                <th scope="col">Asesor</th>
                                <th scope="col">Estatus</th>
                                <th scope="col" class="text-end">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($groups as $group)
                                <tr>
                                    <td>{{ $group->nombre_grupo }}</td>
                                    <td>{{ $group->sucursal->nombre_sucursal ?? 'N/A' }}</td>
                                    <td>{{ $group->asesor->name ?? 'N/A' }}</td>
                                    <td>
                                        <span class="badge bg-success">{{ $group->status }}</span>
                                    </td>
                                    <td class="text-end">
                                        {{-- Botón para Administrar Miembros --}}
                                        <a href="{{ route('groups.show', $group->id_group) }}" class="btn btn-primary btn-sm">
                                            Administrar Miembros
                                        </a>

                                        {{-- Botón para Editar --}}
                                        <a href="{{ route('groups.edit', $group->id_group) }}" class="btn btn-warning btn-sm">
                                            Editar
                                        </a>
                                        
                                        {{-- Formulario para Eliminar --}}
                                        <form action="{{ route('groups.destroy', $group->id_group) }}" method="POST" style="display:inline-block;">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('¿Estás seguro de que quieres eliminar este grupo?')">
                                                Eliminar
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center">No hay grupos registrados.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                
                {{-- Enlaces de Paginación --}}
                <div class="mt-3">
                    {{ $groups->links() }}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>