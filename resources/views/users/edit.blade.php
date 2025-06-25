<x-app-layout>
    <div class="container-fluid py-4">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Editar Usuario: {{ $user->name }}</h5>
                <a href="{{ route('users.index') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-arrow-left"></i> Volver a la Lista
                </a>
            </div>
            <div class="card-body">
                @if ($errors->any())
                    <div class="alert alert-danger">
                        <ul>
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('users.update', $user->id) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="row">
                        {{-- Información Básica (No editable por ahora) --}}
                        <div class="col-md-6 mb-3">
                            <label for="name" class="form-label">Nombre</label>
                            <input type="text" id="name" class="form-control" value="{{ $user->name }}" disabled>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" id="email" class="form-control" value="{{ $user->email }}" disabled>
                        </div>
                    </div>

                    <hr>

                    {{-- Asignación de Roles --}}
                    <div class="mb-3">
                        <label class="form-label">Roles Asignados</label>
                        <div class="row">
                            @foreach($roles as $role)
                                <div class="col-md-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="roles[]" value="{{ $role->name }}" id="role_{{ $role->id }}"
                                            {{-- Marcamos el checkbox si el usuario ya tiene este rol --}}
                                            {{ in_array($role->name, $userRoles) ? 'checked' : '' }}
                                        >
                                        <label class="form-check-label" for="role_{{ $role->id }}">
                                            {{ $role->name }}
                                        </label>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <hr>

                    {{-- Permisos de Sucursal --}}
                    <div class="mb-3">
                         <label class="form-label">Acceso a Sucursales</label>
                         <div class="form-check">
                            {{-- Marcamos el checkbox si el usuario tiene el permiso de ver todo --}}
                            <input class="form-check-input" type="checkbox" name="ver_todas_sucursales" value="1" id="ver_todas_sucursales"
                                {{ $user->ver_todas_sucursales ? 'checked' : '' }}
                            >
                            <label class="form-check-label" for="ver_todas_sucursales">
                                Permitir ver todas las sucursales (Acceso Global)
                            </label>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="id_sucursal" class="form-label">O asignar a una sucursal específica:</label>
                        <select name="id_sucursal" id="id_sucursal" class="form-select">
                            <option value="">-- Ninguna --</option>
                            @foreach($sucursales as $sucursal)
                                {{-- Seleccionamos la sucursal que el usuario ya tiene asignada --}}
                                <option value="{{ $sucursal->id_sucursal }}" {{ $user->id_sucursal == $sucursal->id_sucursal ? 'selected' : '' }}>
                                    {{ $sucursal->nombre_sucursal }}
                                </option>
                            @endforeach
                        </select>
                        <small class="form-text text-muted">Esta opción se ignora si "Permitir ver todas las sucursales" está marcado.</small>
                    </div>

                    <div class="text-center mt-4">
                        <button type="submit" class="btn btn-info">Actualizar Usuario</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>