{{-- resources/views/accounts/partials/_form.blade.php --}}

@csrf
<div class="mb-3">
    <label for="name" class="form-label">Nombre de la Cuenta</label>
    <input type="text" class="form-control" id="name" name="name" value="{{ old('name', $account->name ?? '') }}" required>
</div>

<div class="mb-3">
    <label for="code" class="form-label">Código</label>
    <input type="text" class="form-control" id="code" name="code" value="{{ old('code', $account->code ?? '') }}" required>
</div>

<div class="mb-3">
    <label for="type" class="form-label">Tipo de Cuenta</label>
    <select class="form-select" id="type" name="type" required>
        @php $currentType = old('type', $account->type ?? ''); @endphp
        <option value="activo" @if($currentType == 'activo') selected @endif>Activo</option>
        <option value="pasivo" @if($currentType == 'pasivo') selected @endif>Pasivo</option>
        <option value="capital" @if($currentType == 'capital') selected @endif>Capital</option>
        <option value="ingresos" @if($currentType == 'ingresos') selected @endif>Ingresos</option>
        <option value="costos" @if($currentType == 'costos') selected @endif>Costos</option>
        <option value="gastos" @if($currentType == 'gastos') selected @endif>Gastos</option>
    </select>
</div>

<div class="mb-3">
    <label for="parent_id" class="form-label">Cuenta Padre (Opcional)</label>
    <select class="form-select" id="parent_id" name="parent_id">
        <option value="">-- Ninguna --</option>
        @foreach ($accounts as $parentAccount)
            <option value="{{ $parentAccount->id }}" @if(old('parent_id', $account->parent_id ?? '') == $parentAccount->id) selected @endif>
                {{ $parentAccount->code }} - {{ $parentAccount->name }}
            </option>
        @endforeach
    </select>
</div>

<div class="mb-3">
    <label for="description" class="form-label">Descripción</label>
    <textarea class="form-control" id="description" name="description" rows="3">{{ old('description', $account->description ?? '') }}</textarea>
</div>

<button type="submit" class="btn btn-primary">{{ $submitButtonText ?? 'Guardar' }}</button>
<a href="{{ route('accounts.index') }}" class="btn btn-secondary">Cancelar</a>