<x-app-layout>
    <div class="container-fluid py-4">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Crear Nuevo Rol</h5>
                <a href="{{ route('roles.index') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-arrow-left"></i> Volver a la Lista
                </a>
            </div>
            <div class="card-body">
                {{-- Mostramos errores de validación si existen --}}
                @if ($errors->any())
                    <div class="alert alert-danger">
                        <strong>¡Ups!</strong> Hubo algunos problemas con los datos introducidos.<br><br>
                        <ul>
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('roles.store') }}" method="POST">
                    @csrf
                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <label for="name" class="form-label">Nombre del Rol:</label>
                            <input type="text" name="name" id="name" class="form-control" placeholder="Ej: Recursos Humanos" value="{{ old('name') }}" required>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label">Asignar Permisos:</label>
                            <div class="row">
                                @php
                                    $group = '';
                                @endphp
                                @foreach($permissions as $permission)
                                    @php
                                        // Extraemos el nombre del grupo del permiso (ej: 'ver-modulo-aguinaldo' -> 'aguinaldo')
                                        $currentGroup = explode('-', $permission->name)[2] ?? explode('-', $permission->name)[1] ?? 'general';
                                        if ($group !== $currentGroup) {
                                            $group = $currentGroup;
                                            echo '<h6 class="mt-3 text-primary text-capitalize col-12">' . str_replace('_', ' ', $group) . '</h6>';
                                        }
                                    @endphp
                                    <div class="col-md-3">
                                        <div class="form-check">
                                            {{-- El nombre del input es un array para poder recibir múltiples valores --}}
                                            <input class="form-check-input" type="checkbox" name="permissions[]" value="{{ $permission->name }}" id="perm_{{ $permission->id }}">
                                            <label class="form-check-label" for="perm_{{ $permission->id }}">
                                                {{ $permission->name }}
                                            </label>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                    <div class="text-center mt-4">
                        <button type="submit" class="btn btn-success">Guardar Rol</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>