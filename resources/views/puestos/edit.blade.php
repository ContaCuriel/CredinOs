<x-app-layout>
    <div class="container-fluid py-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Editar Puesto: {{ $puesto->nombre_puesto }}</h5>
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

                <form action="{{ route('puestos.update', $puesto->id_puesto) }}" method="POST">
                    @csrf
                    @method('PUT') {{-- Método HTTP para actualizar --}}

                    <div class="mb-3">
                        <label for="nombre_puesto" class="form-label">Nombre del Puesto <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('nombre_puesto') is-invalid @enderror" id="nombre_puesto" name="nombre_puesto" value="{{ old('nombre_puesto', $puesto->nombre_puesto) }}" required>
                        @error('nombre_puesto')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Ya no tenemos descripción_puesto --}}

                    <div class="mb-3">
                        <label for="salario_mensual" class="form-label">Salario Mensual Bruto <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text">$</span>
                            <input type="number" class="form-control @error('salario_mensual') is-invalid @enderror" id="salario_mensual" name="salario_mensual" value="{{ old('salario_mensual', $puesto->salario_mensual) }}" step="0.01" min="0" required placeholder="Ej: 10000.00">
                        </div>
                        @error('salario_mensual')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <hr>
                    <div class="text-end">
                        <a href="{{ route('puestos.index') }}" class="btn btn-secondary">Cancelar</a>
                        <button type="submit" class="btn btn-primary">Actualizar Puesto</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>