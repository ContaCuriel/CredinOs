<x-app-layout>
    <div class="container-fluid py-4">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Gestión de Horarios</h5>
                <a href="{{ route('horarios.create') }}" class="btn btn-success">
                    <i class="bi bi-plus-lg"></i> Nuevo Horario
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
                                <th>Nombre del Horario</th>
                                <th>Descripción</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($horarios as $horario)
                                <tr>
                                    <td>{{ $horario->id_horario }}</td>
                                    <td>{{ $horario->nombre_horario }}</td>
                                    <td>{{ $horario->descripcion ?: 'N/A' }}</td>
                                    <td>
                                        <a href="{{ route('horarios.edit', $horario->id_horario) }}" class="btn btn-sm btn-info" title="Editar Horario">
                                            <i class="bi bi-pencil-square"></i>
                                        </a>
                                        <form action="{{ route('horarios.destroy', $horario->id_horario) }}" method="POST" style="display:inline;">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger" title="Eliminar Horario" onclick="return confirm('¿Estás seguro de que quieres eliminar este horario? Asegúrate de que ningún empleado lo tenga asignado si tienes restricciones.')">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center">No hay horarios registrados.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="mt-3">
                    {{ $horarios->links() }}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>