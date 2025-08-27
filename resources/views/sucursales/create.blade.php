<x-app-layout>
    <div class="container-fluid py-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Registrar Nueva Sucursal</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('sucursales.store') }}" method="POST">
                    @csrf
                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <label for="nombre_sucursal" class="form-label">Nombre de la Sucursal <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('nombre_sucursal') is-invalid @enderror" id="nombre_sucursal" name="nombre_sucursal" value="{{ old('nombre_sucursal') }}" required>
                            @error('nombre_sucursal')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <h6 class="mt-3">Dirección</h6>
                    <hr>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="calle" class="form-label">Calle <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('calle') is-invalid @enderror" id="calle" name="calle" value="{{ old('calle') }}" required>
                            @error('calle')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-3 mb-3">
                            <label for="numero" class="form-label">Número Exterior <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('numero') is-invalid @enderror" id="numero" name="numero" value="{{ old('numero') }}" required>
                            @error('numero')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                    </div>

                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="colonia" class="form-label">Colonia <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('colonia') is-invalid @enderror" id="colonia" name="colonia" value="{{ old('colonia') }}" required>
                            @error('colonia')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="municipio" class="form-label">Municipio <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('municipio') is-invalid @enderror" id="municipio" name="municipio" value="{{ old('municipio') }}" required>
                            @error('municipio')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="estado" class="form-label">Estado <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('estado') is-invalid @enderror" id="estado" name="estado" value="{{ old('estado') }}" required>
                            @error('estado')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="d-flex justify-content-end mt-4">
                        <a href="{{ route('sucursales.index') }}" class="btn btn-secondary me-2">Cancelar</a>
                        <button type="submit" class="btn btn-primary">Guardar Sucursal</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
