<x-app-layout>
    <div class="container-fluid py-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Registrar Nueva Sucursal</h5>
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

                <form action="{{ route('sucursales.store') }}" method="POST">
                    @csrf

                    <div class="mb-3">
                        <label for="nombre_sucursal" class="form-label">Nombre de la Sucursal <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('nombre_sucursal') is-invalid @enderror" id="nombre_sucursal" name="nombre_sucursal" value="{{ old('nombre_sucursal') }}" required>
                        @error('nombre_sucursal')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="direccion_sucursal" class="form-label">Dirección (Opcional)</label>
                        <textarea class="form-control @error('direccion_sucursal') is-invalid @enderror" id="direccion_sucursal" name="direccion_sucursal" rows="3">{{ old('direccion_sucursal') }}</textarea>
                        @error('direccion_sucursal')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Campos de teléfono y gerente eliminados --}}

                    <hr>
                    <div class="text-end">
                        <a href="{{ route('sucursales.index') }}" class="btn btn-secondary">Cancelar</a>
                        <button type="submit" class="btn btn-primary">Guardar Sucursal</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>