<x-app-layout>
    <div class="container-fluid py-4">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Lista de Puestos</h5>
                <a href="{{ route('puestos.create') }}" class="btn btn-success">
                    <i class="bi bi-plus-lg"></i> Nuevo Puesto
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
                                <th>Nombre del Puesto</th>
                                <th class="text-end">Salario Mensual</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($puestos as $puesto)
                                <tr>
                                    <td>{{ $puesto->id_puesto }}</td>
                                    <td>{{ $puesto->nombre_puesto }}</td>
                                    <td class="text-end">$ {{ number_format($puesto->salario_mensual, 2) }}</td>
                                    <td>
                                        <a href="{{ route('puestos.edit', $puesto->id_puesto) }}" class="btn btn-sm btn-info" title="Editar Puesto">
                                            <i class="bi bi-pencil-square"></i>
                                        </a>
                                        <form action="{{ route('puestos.destroy', $puesto->id_puesto) }}" method="POST" style="display:inline;">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger" title="Eliminar Puesto" onclick="return confirm('¿Estás seguro de que quieres eliminar este puesto? Esta acción podría afectar a los empleados asignados a él.')">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center">No hay puestos registrados.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                {{-- Enlaces de Paginación --}}
                <div class="mt-3">
                    {{ $puestos->links() }}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>