<x-app-layout>
    <div class="container-fluid py-4">
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Administrar Grupo: <span class="text-primary">{{ $group->nombre_grupo }}</span></h5>
            </div>
        </div>

        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <div class="row">
            {{-- Columna para la lista de miembros actuales --}}
            <div class="col-lg-7">
                <div class="card">
                    <div class="card-header">
                        <h6 class="mb-0">Miembros Actuales ({{ $group->clients->count() }})</h6>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-striped mb-0">
                                <thead>
                                    <tr>
                                        <th>Nombre del Cliente</th>
                                        <th class="text-end">Acción</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($group->clients as $client)
                                        <tr>
                                            <td>{{ $client->nombre }} {{ $client->apellido_paterno }}</td>
                                            <td class="text-end">
                                                <form action="{{ route('groups.members.remove', ['group' => $group->id_group, 'client' => $client->id_cliente]) }}" method="POST">
                                                    @csrf
                                                    <button type="submit" class="btn btn-danger btn-sm">Quitar</button>
                                                </form>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="2" class="text-center">Este grupo aún no tiene miembros.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Columna para añadir nuevos miembros --}}
            <div class="col-lg-5">
                <div class="card">
                    <div class="card-header">
                        <h6 class="mb-0">Añadir Miembro al Grupo</h6>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('groups.members.add', $group->id_group) }}" method="POST">
                            @csrf
                            <div class="mb-3">
                                <label for="client_id" class="form-label">Selecciona un Cliente</label>
                                <select class="form-select" name="client_id" required>
                                    <option value="" disabled selected>-- Clientes disponibles --</option>
                                    @foreach ($clientsToAdd as $client)
                                        <option value="{{ $client->id_cliente }}">
                                            {{ $client->nombre }} {{ $client->apellido_paterno }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <button type="submit" class="btn btn-success w-100">Añadir Miembro</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <div class="mt-4">
            <a href="{{ route('groups.index') }}" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Volver a la Lista de Grupos
            </a>
        </div>
    </div>
</x-app-layout>