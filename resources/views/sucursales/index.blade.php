<x-app-layout>
    <div class="container-fluid py-4">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Gestión de Sucursales</h5>
                <a href="{{ route('sucursales.create') }}" class="btn btn-primary btn-sm">
                    <i class="bi bi-plus-lg me-1"></i> Nueva Sucursal
                </a>
            </div>
            <div class="card-body">
                {{-- Alertas de éxito o error --}}
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
                    <table class="table table-striped table-hover table-sm">
                        <thead class="table-light">
                            <tr>
                                <th>Nombre</th>
                                <th>Dirección</th>
                                <th class="text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($sucursales as $sucursal)
                                <tr>
                                    <td>{{ $sucursal->nombre_sucursal }}</td>
                                    <td>
                                        {{-- CORREGIDO: Se usa 'numero' y se elimina 'numero_interior' --}}
                                        {{ $sucursal->calle }} {{ $sucursal->numero }},
                                        Col. {{ $sucursal->colonia }}, {{ $sucursal->municipio }}, {{ $sucursal->estado }}.
                                    </td>
                                    <td class="text-center">
                                        {{-- CORREGIDO: Se usa 'sucursale' como nombre del parámetro --}}
                                        <a href="{{ route('sucursales.edit', ['sucursale' => $sucursal]) }}" class="btn btn-sm btn-outline-primary" title="Editar">
                                            <i class="bi bi-pencil-square"></i>
                                        </a>
                                        <button type="button" class="btn btn-sm btn-outline-danger btn-delete" 
                                                data-bs-toggle="modal" 
                                                data-bs-target="#deleteModal"
                                                data-id="{{ $sucursal->id_sucursal }}"
                                                data-name="{{ $sucursal->nombre_sucursal }}"
                                                title="Eliminar">
                                            <i class="bi bi-trash3"></i>
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="text-center">No hay sucursales registradas.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="mt-3">
                    {{ $sucursales->links() }}
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de Confirmación de Eliminación -->
    <div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteModalLabel">Confirmar Eliminación</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>¿Estás seguro de que deseas eliminar la sucursal <strong id="sucursalName"></strong>?</p>
                    <p class="text-danger small">Esta acción no se puede deshacer.</p>
                </div>
                <div class="modal-footer">
                    <form id="deleteForm" method="POST" action="">
                        @csrf
                        @method('DELETE')
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-danger">Sí, Eliminar</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const deleteModal = document.getElementById('deleteModal');
            if (deleteModal) {
                deleteModal.addEventListener('show.bs.modal', function (event) {
                    const button = event.relatedTarget;
                    const sucursalId = button.getAttribute('data-id');
                    const sucursalName = button.getAttribute('data-name');

                    const form = deleteModal.querySelector('#deleteForm');
                    // CORREGIDO: Se usa 'sucursale' como nombre del parámetro
                    let action = "{{ route('sucursales.destroy', ['sucursale' => ':id']) }}";
                    action = action.replace(':id', sucursalId);
                    
                    form.setAttribute('action', action);
                    deleteModal.querySelector('#sucursalName').textContent = sucursalName;
                });
            }
        });
    </script>
    @endpush
</x-app-layout>
