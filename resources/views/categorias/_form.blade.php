{{-- resources/views/categorias/_form.blade.php --}}

{{-- Muestra los errores de validación si existen --}}
@if ($errors->any())
    <div class="alert alert-danger" role="alert">
        <h4 class="alert-heading">¡Ups! Hubo algunos errores.</h4>
        <ul class="mb-0">
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

{{-- ================= NUEVO CAMPO AÑADIDO ================= --}}
<div class="mb-3">
    <label for="account_id" class="form-label">Cuenta Contable Asociada</label>
    {{-- El controlador pasa la variable $accounts a esta vista --}}
    <select class="form-select" id="account_id" name="account_id">
        <option value="">-- Opcional: Ninguna cuenta por ahora --</option>
        @foreach ($accounts as $account)
            <option value="{{ $account->id }}"
                {{-- Esto pre-selecciona la opción guardada al editar o si hay un error de validación --}}
                @if(old('account_id', $categoria->account_id ?? '') == $account->id) selected @endif
            >
                {{ $account->code }} - {{ $account->name }}
            </option>
        @endforeach
    </select>
    <div class="form-text">
        Selecciona la cuenta de Gasto que se usará al aprobar un gasto de esta categoría.
    </div>
</div>
{{-- ================= FIN DEL NUEVO CAMPO ================= --}}


{{-- Campo de Checkbox Mejorado --}}
<div class="mb-4">
    {{-- Este input oculto asegura que siempre se envíe un valor (0 si está desmarcado) --}}
    <input type="hidden" name="default_requiere_aprobacion" value="0">
    <div class="form-check">
        <input class="form-check-input" type="checkbox" id="default_requiere_aprobacion" name="default_requiere_aprobacion" value="1"
            {{-- Lógica simplificada para marcar el checkbox. old() tiene prioridad --}}
            @if(old('default_requiere_aprobacion', $categoria->default_requiere_aprobacion ?? false)) checked @endif
        >
        <label class="form-check-label" for="default_requiere_aprobacion">
            Los gastos de esta categoría requieren aprobación por defecto
        </label>
    </div>
</div>


<div class="d-flex justify-content-end">
    <a href="{{ route('categorias.index') }}" class="btn btn-secondary me-2">Cancelar</a>
    <button type="submit" class="btn btn-success">
        <i class="bi bi-check-lg"></i> {{ isset($categoria) ? 'Actualizar' : 'Guardar' }} Categoría
    </button>
</div>
