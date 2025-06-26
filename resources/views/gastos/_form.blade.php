{{-- resources/views/gastos/_form.blade.php --}}

{{-- Bloque para mostrar errores de validación --}}
@if ($errors->any())
    <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
        <strong>¡Error de validación!</strong> Por favor, corrige los siguientes problemas:
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

<div class="row">
    <div class="col-md-6">
        <div class="mb-3">
            <label for="fecha_gasto" class="form-label">Fecha del Gasto</label>
            <input type="date" class="form-control" id="fecha_gasto" name="fecha_gasto" value="{{ old('fecha_gasto', isset($gasto) ? $gasto->fecha_gasto->format('Y-m-d') : date('Y-m-d')) }}">
        </div>

        <div class="mb-3">
            <label for="sucursal_id" class="form-label">Sucursal</label>
            <select class="form-select" id="sucursal_id" name="sucursal_id" {{ Auth::user()->ver_todas_sucursales ? '' : 'disabled' }}>
                 @if(Auth::user()->ver_todas_sucursales)
                    <option value="">Seleccione una sucursal...</option>
                    @foreach($sucursales as $sucursal)
                        {{-- Usamos la variable $gasto si existe (en editar), si no, el usuario actual (en crear) --}}
                        <option value="{{ $sucursal->id_sucursal }}" {{ old('sucursal_id', $gasto->sucursal_id ?? Auth::user()->id_sucursal) == $sucursal->id_sucursal ? 'selected' : '' }}>
                            {{ $sucursal->nombre_sucursal }}
                        </option>
                    @endforeach
                @else
                    <option value="{{ Auth::user()->id_sucursal }}" selected>{{ Auth::user()->sucursal?->nombre_sucursal ?? 'Sucursal no asignada' }}</option>
                @endif
            </select>
             @if(!Auth::user()->ver_todas_sucursales)
                <input type="hidden" name="sucursal_id" value="{{ Auth::user()->id_sucursal }}">
            @endif
        </div>

        <div class="mb-3">
            <label for="proveedor_nombre" class="form-label">Proveedor</label>
            <input type="text" class="form-control" id="proveedor_nombre" name="proveedor_nombre" value="{{ old('proveedor_nombre', $gasto->proveedor->nombre ?? '') }}" list="proveedores_list" placeholder="Escriba para buscar o añadir uno nuevo">
            <datalist id="proveedores_list">
                @foreach($proveedores as $proveedor)
                    <option value="{{ $proveedor->nombre }}">
                @endforeach
            </datalist>
        </div>

        <div class="mb-3">
            <label for="categoria_id" class="form-label">Categoría</label>
            <select class="form-select" id="categoria_id" name="categoria_id">
                <option value="">Selecciona una categoría</option>
                @foreach($categorias as $categoria)
                    <option value="{{ $categoria->id }}" {{ old('categoria_id', $gasto->categoria_id ?? '') == $categoria->id ? 'selected' : '' }}>
                        {{ $categoria->nombre }}
                    </option>
                @endforeach
            </select>
        </div>
        
        <div class="form-check mb-3">
            <input class="form-check-input" type="checkbox" id="requiere_aprobacion" name="requiere_aprobacion" value="1" 
                {{ old('requiere_aprobacion', $gasto->requiere_aprobacion ?? true) ? 'checked' : '' }}
            >
            <label class="form-check-label" for="requiere_aprobacion">
                Requiere Aprobación de Tesorería
            </label>
        </div>
    </div>

    <div class="col-md-6">
        <div class="mb-3">
            <label for="descripcion" class="form-label">Descripción</label>
            <textarea class="form-control" id="descripcion" name="descripcion" rows="3">{{ old('descripcion', $gasto->descripcion ?? '') }}</textarea>
        </div>
        <div class="row">
            <div class="col-md-6 mb-3">
                <label for="monto_subtotal" class="form-label">Subtotal</label>
                <div class="input-group">
                    <span class="input-group-text">$</span>
                    <input type="number" step="0.01" class="form-control" id="subtotalInput" name="monto_subtotal" value="{{ old('monto_subtotal', $gasto->monto_subtotal ?? '') }}">
                </div>
            </div>
            <div class="col-md-6 mb-3">
                <label for="monto_iva" class="form-label">IVA (Opcional)</label>
                 <div class="input-group">
                    <span class="input-group-text">$</span>
                    <input type="number" step="0.01" class="form-control" id="ivaInput" name="monto_iva" value="{{ old('monto_iva', $gasto->monto_iva ?? '') }}">
                </div>
            </div>
        </div>
        <div class="mb-3">
            <label for="monto_total_display" class="form-label">Total</label>
            <div class="input-group">
                <span class="input-group-text">$</span>
                <input type="text" class="form-control" id="totalInput" readonly style="background-color: #e9ecef;">
                <input type="hidden" name="monto_total" id="hiddenTotalInput">
            </div>
        </div>
        <div class="mb-3">
            <label for="comprobante" class="form-label">Comprobante (Opcional)</label>
            <input class="form-control" type="file" id="comprobante" name="comprobante">
            @if(isset($gasto) && $gasto->nombre_archivo_comprobante)
                <small class="form-text text-muted">Archivo actual: 
                    <a href="{{ route('gastos.verComprobante', $gasto) }}" target="_blank">Ver Comprobante</a>
                </small>
            @endif
        </div>
    </div>
</div>

<div class="mt-4 d-flex justify-content-end">
    <a href="{{ route('gastos.index') }}" class="btn btn-secondary me-2">Cancelar</a>
    <button type="submit" class="btn btn-success">
        <i class="bi bi-check-lg"></i> {{ isset($gasto) ? 'Actualizar' : 'Guardar' }} Gasto
    </button>
</div>

{{-- Incluimos el script de la suma aquí para que esté disponible en ambos formularios --}}
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const subtotalInput = document.getElementById('subtotalInput');
        const ivaInput = document.getElementById('ivaInput');
        const totalInput = document.getElementById('totalInput');
        const hiddenTotalInput = document.getElementById('hiddenTotalInput');

        function calcularSuma() {
            const sub = parseFloat(subtotalInput.value) || 0;
            const iva = parseFloat(ivaInput.value) || 0;
            const total = sub + iva;
            
            const totalFormateado = total.toFixed(2);
            totalInput.value = totalFormateado;
            hiddenTotalInput.value = totalFormateado;
        }

        subtotalInput.addEventListener('input', calcularSuma);
        ivaInput.addEventListener('input', calcularSuma);
        calcularSuma();
    });
</script>