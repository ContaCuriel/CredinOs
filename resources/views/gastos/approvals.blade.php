@php
    use Illuminate\Support\Str;
@endphp
<x-app-layout>
    <div class="container-fluid py-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Gastos Pendientes de Aprobación</h5>
            </div>
            <div class="card-body">
                @if (session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>
                @endif
                 @if (session('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">{{ session('error') }}<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>
                @endif
                
                <div class="table-responsive">
                    <table class="table table-striped table-hover align-middle">
                        <thead>
                            <tr>
                                <th>Fecha</th>
                                <th>Sucursal</th>
                                <th>Categoría</th>
                                <th class="text-end">Monto</th>
                                <th>Descripción</th>
                                <th class="text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($gastosPendientes as $gasto)
                                <tr>
                                    <td>{{ $gasto->fecha_gasto->format('d/m/Y') }}</td>
                                    <td>{{ $gasto->sucursal?->nombre_sucursal ?? 'N/A' }}</td>
                                    <td>{{ $gasto->categoria?->nombre ?? 'N/A' }}</td>
                                    <td class="text-end">${{ number_format($gasto->monto_total, 2) }}</td>
                                    <td>{{ Str::limit($gasto->descripcion, 50) }}</td>
                                    <td class="text-center">
                                        <div class="btn-group" role="group">
                                            {{-- Botón para aprobar --}}
                                            <form action="{{ route('gastos.approve', $gasto) }}" method="POST" class="d-inline">
                                                @csrf
                                                @method('POST')
                                                <button type="submit" class="btn btn-sm btn-success" title="Aprobar Gasto">
                                                    <i class="bi bi-check-circle-fill"></i> Aprobar
                                                </button>
                                            </form>
                                            {{-- Botón para abrir el modal de rechazo --}}
                                            <button type="button" class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#modalRechazar" data-gasto-id="{{ $gasto->id }}" title="Rechazar Gasto">
                                                <i class="bi bi-x-circle-fill"></i> Rechazar
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center">No hay gastos pendientes de aprobación.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="mt-3">{{ $gastosPendientes->links() }}</div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalRechazar" tabindex="-1" aria-labelledby="modalRechazarLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="modalRechazarLabel">Rechazar Gasto</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="formRechazar" action="" method="POST">
                    @csrf
                    @method('POST')
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="comentarios_rechazo" class="form-label">Motivo del rechazo (obligatorio):</label>
                            <textarea class="form-control" id="comentarios_rechazo" name="comentarios_rechazo" rows="3" required></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-danger">Confirmar Rechazo</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
<script>
    const modalRechazar = document.getElementById('modalRechazar');
    const formRechazar = document.getElementById('formRechazar');

    modalRechazar.addEventListener('show.bs.modal', event => {
        // Botón que activó el modal
        const button = event.relatedTarget;
        // Obtenemos el ID del gasto desde el atributo data-gasto-id
        const gastoId = button.getAttribute('data-gasto-id');
        
        // Construimos la URL para la acción del formulario
        const actionUrl = `/gastos/${gastoId}/rechazar`;
        
        // Asignamos la URL al formulario del modal
        formRechazar.setAttribute('action', actionUrl);
    });
</script>
@endpush

</x-app-layout>