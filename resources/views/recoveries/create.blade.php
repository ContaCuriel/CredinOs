<x-app-layout>
    <div class="card shadow-sm">
        <div class="card-header">
            <h5 class="mb-0">Nuevo Registro de Recuperación Mensual</h5>
        </div>
        <div class="card-body">
            <form action="{{ route('recoveries.store') }}" method="POST">
                @csrf
                @if ($errors->any())
                    <div class="alert alert-danger">
                        <ul>@foreach ($errors->all() as $error) <li>{{ $error }}</li> @endforeach</ul>
                    </div>
                @endif
                <div class="row g-3">
                    <div class="col-md-4">
                        <label for="sucursal_id" class="form-label">Sucursal</label>
                        <select class="form-select" id="sucursal_id" name="sucursal_id" required>
                            <option value="">Seleccione una sucursal...</option>
                            @foreach ($sucursales as $sucursal)
                                <option value="{{ $sucursal->id_sucursal }}" {{ old('sucursal_id') == $sucursal->id_sucursal ? 'selected' : '' }}>
                                    {{ $sucursal->nombre_sucursal }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label for="month" class="form-label">Mes</label>
                        <select class="form-select" id="month" name="month" required>
                            @for ($m = 1; $m <= 12; $m++)
                                <option value="{{ $m }}" {{ old('month', date('m')) == $m ? 'selected' : '' }}>
                                    {{ \Carbon\Carbon::create(null, $m)->translatedFormat('F') }}
                                </option>
                            @endfor
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label for="year" class="form-label">Año</label>
                        <input type="number" class="form-control" id="year" name="year" value="{{ old('year', date('Y')) }}" required>
                    </div>
                    
                    <hr class="mt-4 mb-2">
                    <h6 class="text-primary mb-3">Datos de Cobranza (Calculadora Automática)</h6>
                    
                    <div class="col-md-3">
                        <label for="cobro_proyectado" class="form-label">1. Cobro Proyectado</label>
                        <div class="input-group">
                            <span class="input-group-text">$</span>
                            <input type="number" step="0.01" class="form-control calc-input" id="cobro_proyectado" name="cobro_proyectado" value="{{ old('cobro_proyectado', 0) }}" required>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <label for="capital_recovered" class="form-label">2. Capital Recuperado</label>
                        <div class="input-group">
                            <span class="input-group-text">$</span>
                            <input type="number" step="0.01" class="form-control calc-input" id="capital_recovered" name="capital_recovered" value="{{ old('capital_recovered', 0) }}" required>
                        </div>
                    </div>
                    
                    <div class="col-md-3">
                        <label for="interest_collected" class="form-label">3. Intereses Cobrados</label>
                        <div class="input-group">
                            <span class="input-group-text">$</span>
                            <input type="number" step="0.01" class="form-control calc-input" id="interest_collected" name="interest_collected" value="{{ old('interest_collected', 0) }}" required>
                        </div>
                    </div>
                    
                    <div class="col-md-3">
                        <label class="form-label text-danger fw-bold">Mora Generada (Auto)</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light text-danger border-danger">$</span>
                            <input type="text" class="form-control bg-light text-danger fw-bold border-danger" id="mora_visual" value="0.00" readonly tabindex="-1">
                        </div>
                    </div>

                    <div class="col-md-4 mt-4">
                        <label for="unrecoverable_amount" class="form-label text-muted">Préstamos Castigados (Pérdida)</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light">$</span>
                            <input type="number" step="0.01" class="form-control" id="unrecoverable_amount" name="unrecoverable_amount" value="{{ old('unrecoverable_amount', 0) }}" required>
                        </div>
                    </div>

                     <div class="col-md-8 mt-4">
                        <label for="notes" class="form-label text-muted">Notas / Observaciones de la Sucursal</label>
                        <input type="text" class="form-control" id="notes" name="notes" value="{{ old('notes') }}">
                    </div>
                </div>

                <div class="d-flex justify-content-end mt-4">
                    <a href="{{ route('recoveries.index') }}" class="btn btn-secondary me-2">Cancelar</a>
                    <button type="submit" class="btn btn-success">Guardar Registro Definitivo</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Agarramos los 3 inputs donde la cajera escribe
            const inputs = document.querySelectorAll('.calc-input');
            const visualMora = document.getElementById('mora_visual');

            function calcularMoraEnVivo() {
                // Obtenemos los valores, si están vacíos los tratamos como 0
                let proyectado = parseFloat(document.getElementById('cobro_proyectado').value) || 0;
                let capital = parseFloat(document.getElementById('capital_recovered').value) || 0;
                let interes = parseFloat(document.getElementById('interest_collected').value) || 0;

                // Matemáticas: Lo que esperaba - Lo que me dieron
                let mora = proyectado - (capital + interes);
                
                // Si la mora es negativa (cobraron de más), la mostramos en 0
                if (mora < 0) mora = 0;

                // Ponemos el resultado en la cajita roja con formato de comas y decimales
                visualMora.value = mora.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
            }

            // Cada que la cajera presione una tecla en esas 3 cajas, recalculamos
            inputs.forEach(input => {
                input.addEventListener('input', calcularMoraEnVivo);
            });
        });
    </script>
</x-app-layout>