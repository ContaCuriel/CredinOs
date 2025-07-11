<x-app-layout>
    <div class="container-fluid py-4">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Historial de Créditos</h5>
                <a href="{{ route('creditos.create') }}" class="btn btn-success">
                    <i class="bi bi-plus-lg"></i> Registrar Nuevo Crédito
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
                                <th>Titular (Cliente/Grupo)</th>
                                <th>Tipo</th>
                                <th>Monto Solicitado</th>
                                <th>Estatus</th>
                                <th>Fecha de Solicitud</th>
                                <th class="text-end">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($creditos as $credito)
                                <tr>
                                    <td>
                                        {{-- Aquí verificamos si el dueño es un Cliente o un Grupo para mostrar el nombre correcto --}}
                                        @if ($credito->loanable_type == 'App\\Models\\Cliente')
                                            {{ $credito->loanable->nombre ?? '' }} {{ $credito->loanable->apellido_paterno ?? '' }}
                                        @elseif ($credito->loanable_type == 'App\\Models\\Group')
                                            {{ $credito->loanable->nombre_grupo ?? 'Grupo no encontrado' }}
                                        @endif
                                    </td>
                                    <td>
                                        @if ($credito->loanable_type == 'App\\Models\\Cliente')
                                            <span class="badge bg-info">Individual</span>
                                        @elseif ($credito->loanable_type == 'App\\Models\\Group')
                                            <span class="badge bg-secondary">Grupal</span>
                                        @endif
                                    </td>
                                    <td>${{ number_format($credito->monto_solicitado, 2) }}</td>
                                    <td><span class="badge bg-primary">{{ $credito->status }}</span></td>
                                    <td>{{ \Carbon\Carbon::parse($credito->fecha_solicitud)->format('d/m/Y') }}</td>
                                    <td class="text-end">
                                        <a href="#" class="btn btn-primary btn-sm">Ver Detalles</a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center">No hay créditos registrados.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                
                <div class="mt-3">
                    {{ $creditos->links() }}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>