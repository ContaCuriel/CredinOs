<x-app-layout>
    <div class="card shadow-sm">
        <div class="card-header">
            <h5 class="mb-0">Nuevo Registro de Colocación Mensual</h5>
        </div>
        <div class="card-body">
            <form action="{{ route('placements.store') }}" method="POST">
                @csrf
                @if ($errors->any())
                    <div class="alert alert-danger">
                        <ul>
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
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
                    <div class="col-12">
                        <label for="amount" class="form-label">Monto Total Colocado (Desembolsado)</label>
                        <div class="input-group">
                            <span class="input-group-text">$</span>
                            <input type="number" step="0.01" class="form-control" id="amount" name="amount" value="{{ old('amount') }}" required>
                        </div>
                    </div>
                     <div class="col-12">
                        <label for="notes" class="form-label">Notas (Opcional)</label>
                        <textarea class="form-control" id="notes" name="notes" rows="3">{{ old('notes') }}</textarea>
                    </div>
                </div>
                <div class="d-flex justify-content-end mt-4">
                    <a href="{{ route('placements.index') }}" class="btn btn-secondary me-2">Cancelar</a>
                    <button type="submit" class="btn btn-success">Guardar Registro</button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
