<x-app-layout>
    <div class="container-fluid py-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Registrar Nuevo Contrato</h5>
            </div>
            <div class="card-body">
                @if ($errors->any())
                    <div class="alert alert-danger">
                        <h6 class="alert-heading">¡Por favor corrige los siguientes errores!</h6>
                        <ul>
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('contratos.store') }}" method="POST">
                    @csrf

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="id_empleado" class="form-label">Empleado <span class="text-danger">*</span></label>
                            <select class="form-select @error('id_empleado') is-invalid @enderror" id="id_empleado" name="id_empleado" required>
                                <option value="">Seleccione un empleado...</option>
                                @foreach ($empleados as $empleado)
                                    {{-- Se usa $prefill_empleado_id que ya considera old('id_empleado') --}}
                                    <option value="{{ $empleado->id_empleado }}" {{ $prefill_empleado_id == $empleado->id_empleado ? 'selected' : '' }}>
                                        {{ $empleado->nombre_completo }} ({{ $empleado->rfc }})
                                    </option>
                                @endforeach
                            </select>
                            @error('id_empleado') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-6 mb-3"> {{-- O ajusta el tamaño de columna como necesites --}}
    <label for="id_patron" class="form-label">Patrón (Empresa/Contratante) <span class="text-danger">*</span></label>
    <select class="form-select @error('id_patron') is-invalid @enderror" id="id_patron" name="id_patron" required>
        <option value="">Seleccione un Patrón...</option>
        @if(isset($patrones))
            @foreach ($patrones as $patron)
                <option value="{{ $patron->id_patron }}" 
                        data-tipo_persona="{{ $patron->tipo_persona }}" {{-- Para posible JS futuro --}}
                        {{ (old('id_patron', $prefill_patron_id ?? null) == $patron->id_patron) ? 'selected' : '' }}>
                    {{ $patron->razon_social }} ({{ $patron->rfc }}) - {{ ucfirst($patron->tipo_persona) }}
                </option>
            @endforeach
        @endif
    </select>
    @error('id_patron') <div class="invalid-feedback">{{ $message }}</div> @enderror
</div>
                    </div>

                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="tipo_contrato" class="form-label">Tipo de Contrato <span class="text-danger">*</span></label>
                            <select class="form-select @error('tipo_contrato') is-invalid @enderror" id="tipo_contrato" name="tipo_contrato" required>
                                <option value="">Seleccione un tipo...</option>
                                @foreach ($tipos_contrato as $valor => $texto)
                                    {{-- Se usa $prefill_tipo_contrato que ya considera old('tipo_contrato') --}}
                                    <option value="{{ $valor }}" {{ $prefill_tipo_contrato == $valor ? 'selected' : '' }}>
                                        {{ $texto }}
                                    </option>
                                @endforeach
                            </select>
                            @error('tipo_contrato') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-4 mb-3">
                            <label for="fecha_inicio" class="form-label">Fecha de Inicio <span class="text-danger">*</span></label>
                            <input type="date" class="form-control @error('fecha_inicio') is-invalid @enderror" id="fecha_inicio" name="fecha_inicio" value="{{ old('fecha_inicio') }}" required>
                            @error('fecha_inicio') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-4 mb-3">
                            <label for="fecha_fin" class="form-label">Fecha de Fin <span class="text-danger">*</span></label>
                            <input type="date" class="form-control @error('fecha_fin') is-invalid @enderror" id="fecha_fin" name="fecha_fin" value="{{ old('fecha_fin') }}" required>
                            @error('fecha_fin') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>
                    
                    
                    <hr>
                    <div class="text-end">
                        <a href="{{ route('contratos.index') }}" class="btn btn-secondary">Cancelar</a>
                        <button type="submit" class="btn btn-primary">Guardar Contrato</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>