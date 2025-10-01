<x-app-layout>
    <div class="container-fluid py-4">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Lista de Clientes</h5>
                <a href="{{ route('clientes.create') }}" class="btn btn-success">
                    <i class="bi bi-plus-lg"></i> Nuevo Cliente
                </a>
            </div>
            <div class="card-body">
                {{-- Alertas de Sesión --}}
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
                    <table class="table table-striped align-middle">
                        <thead>
                            <tr>
                                <th scope="col">Nombre Completo</th>
                                <th scope="col">Contacto</th>
                                <th scope="col">Estatus</th>
                                <th scope="col">Sucursal</th>
                                <th scope="col" class="text-end">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($clientes as $cliente)
                                <tr>
                                    <td>{{ $cliente->nombre_completo }}</td>
                                    <td>
                                        {{ $cliente->telefono_celular ?? 'N/A' }}
                                        <br>
                                        <small class="text-muted">{{ $cliente->email ?? 'Sin correo' }}</small>
                                    </td>
                                    <td>
                                        {{-- Placeholder para el futuro estatus del cliente --}}
                                        <span class="badge bg-secondary">Sin créditos</span>
                                    </td>
                                    <td>{{ $cliente->sucursal->nombre_sucursal ?? 'Sin asignar' }}</td>
                                    <td class="text-end">
                                        {{-- Botón para Ver (opcional, si creas una vista show) --}}
                                        {{-- <a href="{{ route('clientes.show', $cliente->id_cliente) }}" class="btn btn-info btn-sm" title="Ver Detalles">
                                            <i class="bi bi-eye"></i>
                                        </a> --}}
                                        
                                        {{-- Botón para Editar --}}
                                        <a href="{{ route('clientes.edit', $cliente->id_cliente) }}" class="btn btn-warning btn-sm" title="Editar Cliente">
                                            <i class="bi bi-pencil-square"></i> Editar
                                        </a>
                                        
                                        {{-- Formulario para Eliminar --}}
                                        <form action="{{ route('clientes.destroy', $cliente->id_cliente) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger btn-sm" title="Eliminar Cliente" onclick="return confirm('¿Estás seguro de que quieres eliminar a este cliente?')">
                                                <i class="bi bi-trash"></i> Eliminar
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center">No hay clientes registrados.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                
                {{-- Enlaces de Paginación --}}
                <div class="mt-3">
                    {{ $clientes->links() }}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>