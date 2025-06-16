<x-app-layout>
    <div class="container-fluid py-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Editar Contrato de: {{ $empleadoDelContrato->nombre_completo }}</h5>
                <p class="text-sm mb-0">Contrato ID: {{ $contrato->id_contrato }}</p>
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

                <form action="{{ route('contratos.update', $contrato->id_contrato) }}" method="POST">
                    @csrf
                    @method('PUT') {{-- Método para actualizar --}}

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Empleado</label>
                            <input type="text" class="form-control" value="{{ $empleadoDelContrato->nombre_completo }} ({{ $empleadoDelContrato->rfc }})" readonly>
                            {{-- No permitimos cambiar el empleado de un contrato existente, si se necesita, se crea uno nuevo --}}
                            <input type="hidden" name="id_empleado" value="{{ $contrato->id_empleado }}">
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="patron_tipo" class="form-label">Tipo de Patrón <span class="text-danger">*</span></label>
                            <select class="form-select @error('patron_tipo') is-invalid @enderror" id="patron_tipo" name="patron_tipo" required>
                                <option value="">Seleccione un tipo de patrón...</option>
                                @foreach ($tipos_patron as $valor => $texto)
                                    <option value="{{ $valor }}" {{ old('patron_tipo', $contrato->patron_tipo) == $valor ? 'selected' : '' }}>
                                        {{ $texto }}
                                    </option>
                                @endforeach
                            </select>
                            @error('patron_tipo') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="tipo_contrato" class="form-label">Tipo de Contrato <span class="text-danger">*</span></label>
                            <select class="form-select @error('tipo_contrato') is-invalid @enderror" id="tipo_contrato" name="tipo_contrato" required>
                                <option value="">Seleccione un tipo...</option>
                                @foreach ($tipos_contrato as $valor => $texto)
                                    <option value="{{ $valor }}" {{ old('tipo_contrato', $contrato->tipo_contrato) == $valor ? 'selected' : '' }}>
                                        {{ $texto }}
                                    </option>
                                @endforeach
                            </select>
                            @error('tipo_contrato') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-4 mb-3">
                            <label for="fecha_inicio" class="form-label">Fecha de Inicio <span class="text-danger">*</span></label>
                            <input type="date" class="form-control @error('fecha_inicio') is-invalid @enderror" id="fecha_inicio" name="fecha_inicio" value="{{ old('fecha_inicio', $contrato->fecha_inicio->format('Y-m-d')) }}" required>
                            @error('fecha_inicio') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="col-md-4 mb-3">
                            <label for="fecha_fin" class="form-label">Fecha de Fin <span class="text-danger">*</span></label>
                            <input type="date" class="form-control @error('fecha_fin') is-invalid @enderror" id="fecha_fin" name="fecha_fin" value="{{ old('fecha_fin', $contrato->fecha_fin->format('Y-m-d')) }}" required>
                            @error('fecha_fin') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    {{-- El campo de Cartas de Recomendación es del Empleado, no se edita aquí en el contrato --}}

                    <hr>
                    <div class="text-end">
                        <a href="{{ route('contratos.index') }}" class="btn btn-secondary">Cancelar</a>
                        <button type="submit" class="btn btn-primary">Actualizar Contrato</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>