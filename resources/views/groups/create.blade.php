<x-app-layout>
    <div class="container-fluid py-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Registrar Nuevo Grupo</h5>
            </div>
            <div class="card-body">
                @if ($errors->any())
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <h6 class="alert-heading fw-bold">¡Por favor corrige los siguientes errores!</h6>
                        <ul>
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                <form action="{{ route('groups.store') }}" method="POST">
                    @csrf

                    <div class="mb-3">
                        <label for="nombre_grupo" class="form-label">Nombre del Grupo <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="nombre_grupo" value="{{ old('nombre_grupo') }}" required>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="id_sucursal" class="form-label">Sucursal <span class="text-danger">*</span></label>
                            <select class="form-select" name="id_sucursal" required>
                                <option value="" disabled selected>Selecciona una sucursal</option>
                                @foreach ($sucursales as $sucursal)
                                    <option value="{{ $sucursal->id_sucursal }}" {{ old('id_sucursal') == $sucursal->id_sucursal ? 'selected' : '' }}>
                                        {{ $sucursal->nombre_sucursal }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="id_asesor" class="form-label">Asesor Asignado <span class="text-danger">*</span></label>
                            <select class="form-select" name="id_asesor" required>
                                <option value="" disabled selected>Selecciona un asesor</option>
                                @foreach ($asesores as $asesor)
                                    <option value="{{ $asesor->id }}" {{ old('id_asesor') == $asesor->id ? 'selected' : '' }}>
                                        {{ $asesor->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="text-end mt-4">
                        <a href="{{ route('groups.index') }}" class="btn btn-secondary">Cancelar</a>
                        <button type="submit" class="btn btn-primary">Guardar Grupo</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>