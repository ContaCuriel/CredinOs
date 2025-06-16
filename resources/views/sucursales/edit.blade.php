<x-app-layout>
    <div class="container-fluid py-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Editar Sucursal: {{ $sucursal->nombre_sucursal }}</h5>
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

               <p>ID de Sucursal: {{ $sucursal->id_sucursal }}</p>
<p>Nombre de Sucursal: {{ $sucursal->nombre_sucursal }}</p>
{{-- Intento de generar la URL directamente para ver si falla aquí --}}
<p>URL Generada (Intento 1): {{ route('sucursales.update', ['sucursale' => $sucursal]) }}</p>
<p>URL Generada (Intento 2): {{ route('sucursales.update', ['sucursale' => $sucursal->id_sucursal]) }}</p>

<form action="{{ route('sucursales.update', ['sucursale' => $sucursal]) }}" method="POST">


                    @csrf
                    @method('PUT') {{-- Método HTTP para actualizar --}}

                    <div class="mb-3">
                        <label for="nombre_sucursal" class="form-label">Nombre de la Sucursal <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('nombre_sucursal') is-invalid @enderror" id="nombre_sucursal" name="nombre_sucursal" value="{{ old('nombre_sucursal', $sucursal->nombre_sucursal) }}" required>
                        @error('nombre_sucursal')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="direccion_sucursal" class="form-label">Dirección (Opcional)</label>
                        <textarea class="form-control @error('direccion_sucursal') is-invalid @enderror" id="direccion_sucursal" name="direccion_sucursal" rows="3">{{ old('direccion_sucursal', $sucursal->direccion_sucursal) }}</textarea>
                        @error('direccion_sucursal')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <hr>
                    <div class="text-end">
                        <a href="{{ route('sucursales.index') }}" class="btn btn-secondary">Cancelar</a>
                        <button type="submit" class="btn btn-primary">Actualizar Sucursal</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>