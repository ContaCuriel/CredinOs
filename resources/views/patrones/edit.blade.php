<x-app-layout>
    <div class="container-fluid py-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Editar Patrón: {{ $patron->razon_social }}</h5>
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

                <form action="{{ route('patrones.update', $patron->id_patron) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT') {{-- Método HTTP para actualizar --}}

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="nombre_comercial" class="form-label">Nombre Comercial <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('nombre_comercial') is-invalid @enderror" id="nombre_comercial" name="nombre_comercial" value="{{ old('nombre_comercial', $patron->nombre_comercial) }}" required>
                            @error('nombre_comercial') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="razon_social" class="form-label">Razón Social (Nombre Fiscal) <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('razon_social') is-invalid @enderror" id="razon_social" name="razon_social" value="{{ old('razon_social', $patron->razon_social) }}" required>
                            @error('razon_social') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="tipo_persona" class="form-label">Tipo de Persona <span class="text-danger">*</span></label>
                            <select class="form-select @error('tipo_persona') is-invalid @enderror" id="tipo_persona" name="tipo_persona" required>
                                <option value="">Seleccione un tipo...</option>
                                @foreach ($tipos_persona as $valor => $texto)
                                    <option value="{{ $valor }}" {{ old('tipo_persona', $patron->tipo_persona) == $valor ? 'selected' : '' }}>{{ $texto }}</option>
                                @endforeach
                            </select>
                            @error('tipo_persona') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="rfc" class="form-label">RFC <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('rfc') is-invalid @enderror" id="rfc" name="rfc" value="{{ old('rfc', $patron->rfc) }}" required>
                            @error('rfc') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="direccion_fiscal" class="form-label">Dirección Fiscal (Opcional)</label>
                        <textarea class="form-control @error('direccion_fiscal') is-invalid @enderror" id="direccion_fiscal" name="direccion_fiscal" rows="3">{{ old('direccion_fiscal', $patron->direccion_fiscal) }}</textarea>
                        @error('direccion_fiscal') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="mb-3">
                        <label for="actividad_principal" class="form-label">Actividad Principal (Opcional)</label>
                        <textarea class="form-control @error('actividad_principal') is-invalid @enderror" id="actividad_principal" name="actividad_principal" rows="2">{{ old('actividad_principal', $patron->actividad_principal) }}</textarea>
                        @error('actividad_principal') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="mb-3">
                        <label for="representante_legal" class="form-label">Representante Legal (Si aplica, opcional)</label>
                        <input type="text" class="form-control @error('representante_legal') is-invalid @enderror" id="representante_legal" name="representante_legal" value="{{ old('representante_legal', $patron->representante_legal) }}">
                        @error('representante_legal') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="mb-3">
                        <label for="logo_path" class="form-label">Nuevo Logo del Patrón (Opcional, reemplazará el actual si se sube uno)</label>
                        <input class="form-control @error('logo_path') is-invalid @enderror" type="file" id="logo_path" name="logo_path" accept="image/png, image/jpeg, image/gif">
                        @error('logo_path') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        @if ($patron->logo_path)
                            <div class="mt-2">
                                <p class="mb-1">Logo Actual:</p>
                                <img src="{{ asset('storage/' . $patron->logo_path) }}" alt="Logo Actual" style="max-height: 80px; border: 1px solid #ddd; padding: 5px;">
                            </div>
                        @endif
                    </div>

                    <hr>
                    <div class="text-end">
                        <a href="{{ route('patrones.index') }}" class="btn btn-secondary">Cancelar</a>
                        <button type="submit" class="btn btn-primary">Actualizar Patrón</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>