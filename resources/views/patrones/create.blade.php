<x-app-layout>
    <div class="container-fluid py-4">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white border-bottom py-3">
                <h5 class="mb-0 fw-bold text-primary">
                    <i class="bi bi-building-add me-2"></i>Registrar Nuevo Patrón
                </h5>
            </div>
            <div class="card-body">
                @if ($errors->any())
                    <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
                        <h6 class="alert-heading fw-bold"><i class="bi bi-exclamation-triangle-fill me-2"></i>¡Por favor corrige los siguientes errores!</h6>
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                <form action="{{ route('patrones.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf

                    <h6 class="fw-bold mb-3 text-secondary border-bottom pb-2">Datos Generales</h6>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="nombre_comercial" class="form-label fw-bold small">Nombre Comercial <span class="text-danger">*</span></label>
                            <input type="text" class="form-control form-control-sm @error('nombre_comercial') is-invalid @enderror" id="nombre_comercial" name="nombre_comercial" value="{{ old('nombre_comercial') }}" required>
                            @error('nombre_comercial') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="razon_social" class="form-label fw-bold small">Razón Social (Nombre Fiscal) <span class="text-danger">*</span></label>
                            <input type="text" class="form-control form-control-sm @error('razon_social') is-invalid @enderror" id="razon_social" name="razon_social" value="{{ old('razon_social') }}" required>
                            @error('razon_social') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="tipo_persona" class="form-label fw-bold small">Tipo de Persona <span class="text-danger">*</span></label>
                            <select class="form-select form-select-sm @error('tipo_persona') is-invalid @enderror" id="tipo_persona" name="tipo_persona" required>
                                <option value="">Seleccione un tipo...</option>
                                @foreach (['fisica' => 'Persona Física', 'moral' => 'Persona Moral'] as $valor => $texto)
                                    <option value="{{ $valor }}" {{ old('tipo_persona') == $valor ? 'selected' : '' }}>{{ $texto }}</option>
                                @endforeach
                            </select>
                            @error('tipo_persona') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="rfc" class="form-label fw-bold small">RFC <span class="text-danger">*</span></label>
                            <input type="text" class="form-control form-control-sm @error('rfc') is-invalid @enderror" id="rfc" name="rfc" value="{{ old('rfc') }}" required>
                            @error('rfc') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    {{-- 🔥 SECCIÓN NUEVA: DATOS FISCALES CFDI 4.0 🔥 --}}
                    <h6 class="fw-bold mt-4 mb-3 text-secondary border-bottom pb-2"><i class="bi bi-bank me-1"></i> Datos Fiscales (CFDI 4.0)</h6>
                    <div class="row bg-light p-3 rounded mb-3 border">
                        <div class="col-md-4 mb-3">
                            <label for="registro_patronal" class="form-label fw-bold small">Registro Patronal (IMSS)</label>
                            <input type="text" class="form-control form-control-sm @error('registro_patronal') is-invalid @enderror" id="registro_patronal" name="registro_patronal" value="{{ old('registro_patronal') }}" maxlength="20" placeholder="Opcional, ej. B5510768108">
                            <small class="text-muted" style="font-size: 0.75rem;">Requerido para Sueldos y Salarios.</small>
                            @error('registro_patronal') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="codigo_postal" class="form-label fw-bold small">C.P. (Lugar Expedición) <span class="text-danger">*</span></label>
                            <input type="text" class="form-control form-control-sm @error('codigo_postal') is-invalid @enderror" id="codigo_postal" name="codigo_postal" value="{{ old('codigo_postal') }}" maxlength="5" required placeholder="Ej. 56100">
                            @error('codigo_postal') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="regimen_fiscal" class="form-label fw-bold small">Régimen Fiscal (SAT) <span class="text-danger">*</span></label>
                            <select class="form-select form-select-sm @error('regimen_fiscal') is-invalid @enderror" id="regimen_fiscal" name="regimen_fiscal" required>
                                <option value="">Seleccione...</option>
                                <option value="601" {{ old('regimen_fiscal') == '601' ? 'selected' : '' }}>601 - General de Ley Personas Morales</option>
                                <option value="612" {{ old('regimen_fiscal') == '612' ? 'selected' : '' }}>612 - Personas Físicas con Actividades Emp. y Prof.</option>
                                <option value="626" {{ old('regimen_fiscal') == '626' ? 'selected' : '' }}>626 - Régimen Simplificado de Confianza (RESICO)</option>
                                {{-- Agrega otros si es necesario --}}
                            </select>
                            @error('regimen_fiscal') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <h6 class="fw-bold mt-4 mb-3 text-secondary border-bottom pb-2">Información Adicional</h6>
                    <div class="mb-3">
                        <label for="direccion_fiscal" class="form-label fw-bold small">Dirección Fiscal (Opcional)</label>
                        <textarea class="form-control form-control-sm @error('direccion_fiscal') is-invalid @enderror" id="direccion_fiscal" name="direccion_fiscal" rows="2">{{ old('direccion_fiscal') }}</textarea>
                        @error('direccion_fiscal') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="mb-3">
                        <label for="actividad_principal" class="form-label fw-bold small">Actividad Principal (Opcional)</label>
                        <textarea class="form-control form-control-sm @error('actividad_principal') is-invalid @enderror" id="actividad_principal" name="actividad_principal" rows="2">{{ old('actividad_principal') }}</textarea>
                        @error('actividad_principal') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="mb-3">
                        <label for="representante_legal" class="form-label fw-bold small">Representante Legal (Si aplica, opcional)</label>
                        <input type="text" class="form-control form-control-sm @error('representante_legal') is-invalid @enderror" id="representante_legal" name="representante_legal" value="{{ old('representante_legal') }}">
                        @error('representante_legal') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        <div class="form-text" style="font-size: 0.75rem;">Requerido principalmente para Personas Morales.</div>
                    </div>

                    <div class="mb-4">
                        <label for="logo_path" class="form-label fw-bold small">Logo del Patrón (Opcional)</label>
                        <input class="form-control form-control-sm @error('logo_path') is-invalid @enderror" type="file" id="logo_path" name="logo_path" accept="image/png, image/jpeg, image/gif">
                        @error('logo_path') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        <div class="form-text" style="font-size: 0.75rem;">Sube una imagen para el logo (Formatos: PNG, JPG, GIF. Tamaño máx: 2MB).</div>
                    </div>

                    <hr class="mt-4 mb-3">
                    <div class="text-end">
                        <a href="{{ route('patrones.index') }}" class="btn btn-secondary fw-bold px-4 me-2">Cancelar</a>
                        <button type="submit" class="btn btn-primary fw-bold px-4"><i class="bi bi-save me-1"></i> Guardar Patrón</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>