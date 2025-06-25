<x-app-layout>
    <div class="container-fluid py-4">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                {{-- CAMBIO 1: Título de la página --}}
                <h5 class="mb-0">Editar Rol: {{ $role->name }}</h5>
                <a href="{{ route('roles.index') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-arrow-left"></i> Volver a la Lista
                </a>
            </div>
            <div class="card-body">
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

                {{-- CAMBIO 2: La acción ahora apunta a 'roles.update' y usamos el método PUT --}}
                <form action="{{ route('roles.update', $role->id) }}" method="POST">
                    @csrf
                    @method('PUT') {{-- Directiva para indicar que es una actualización --}}

                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <label for="name" class="form-label">Nombre del Rol:</label>
                            {{-- CAMBIO 3: Precargamos el nombre actual del rol --}}
                            <input type="text" name="name" id="name" class="form-control" value="{{ old('name', $role->name) }}" required>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label">Asignar Permisos:</label>
                            <div class="row">
                                @php $group = ''; @endphp
                                @foreach($permissions as $permission)
                                    @php
                                        $currentGroup = explode('-', $permission->name)[2] ?? explode('-', $permission->name)[1] ?? 'general';
                                        if ($group !== $currentGroup) {
                                            $group = $currentGroup;
                                            echo '<h6 class="mt-3 text-primary text-capitalize col-12">' . str_replace('_', ' ', $group) . '</h6>';
                                        }
                                    @endphp
                                    <div class="col-md-3">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="permissions[]" value="{{ $permission->name }}" id="perm_{{ $permission->id }}"
                                                {{-- CAMBIO 4: Marcamos el checkbox si el rol ya tiene este permiso --}}
                                                {{ in_array($permission->name, $rolePermissions) ? 'checked' : '' }}
                                            >
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
                        {{-- CAMBIO 5: Cambiamos el texto del botón --}}
                        <button type="submit" class="btn btn-info">Actualizar Rol</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>