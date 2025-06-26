{{-- resources/views/categorias/_form.blade.php --}}

@if ($errors->any())
    <div class="alert alert-danger">
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="mb-3">
    <label for="nombre" class="form-label">Nombre de la Categoría</label>
    <input type="text" class="form-control" id="nombre" name="nombre" value="{{ old('nombre', $categoria->nombre ?? '') }}" required>
</div>

<div class="form-check mb-4">
    <input class="form-check-input" type="checkbox" id="default_requiere_aprobacion" name="default_requiere_aprobacion" value="1" 
        {{-- Marcamos el checkbox si es una edición y el valor es true, o si es una creación y el old() es true --}}
        @if(isset($categoria))
            {{ $categoria->default_requiere_aprobacion ? 'checked' : '' }}
        @else
            {{ old('default_requiere_aprobacion') ? 'checked' : '' }}
        @endif
    >
    <label class="form-check-label" for="default_requiere_aprobacion">
        Los gastos de esta categoría requieren aprobación por defecto
    </label>
</div>

<div class="d-flex justify-content-end">
    <a href="{{ route('categorias.index') }}" class="btn btn-secondary me-2">Cancelar</a>
    <button type="submit" class="btn btn-success">
        <i class="bi bi-check-lg"></i> {{ isset($categoria) ? 'Actualizar' : 'Guardar' }} Categoría
    </button>
</div>