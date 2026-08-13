<x-app-layout>
    <div class="container-fluid py-4">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <h5 class="mb-0 fw-bold text-dark">
                    <i class="bi bi-box-seam me-2 text-primary"></i>Configurar Nuevo Producto de Crédito
                </h5>
                <a href="{{ route('productos_credito.index') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-arrow-left me-1"></i> Regresar
                </a>
            </div>
            <div class="card-body">
                
                @if ($errors->any())
                    <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm" role="alert">
                        <h6 class="alert-heading fw-bold mb-1"><i class="bi bi-exclamation-diamond-fill me-2"></i>Verifica los datos:</h6>
                        <ul class="mb-0 small">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                @if (session('error'))
                    <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm" role="alert">
                        <h6 class="alert-heading fw-bold mb-1"><i class="bi bi-exclamation-triangle-fill me-2"></i>Error de BD:</h6>
                        <p class="mb-0 small">{{ session('error') }}</p>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                <form action="{{ route('productos_credito.store') }}" method="POST">
                    @csrf
                    
                    <div class="accordion accordion-flush" id="accordionProducto">

                        {{-- SECCIÓN 1: REGLAS GENERALES --}}
                        <div class="accordion-item border mb-3 rounded-3 shadow-sm">
                            <h2 class="accordion-header">
                                <button class="accordion-button fw-bold text-primary bg-light rounded-3" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne" aria-expanded="true">
                                    <i class="bi bi-gear-fill me-2 fs-5"></i> 1. Estructura General
                                </button>
                            </h2>
                            <div id="collapseOne" class="accordion-collapse collapse show" data-bs-parent="#accordionProducto">
                                <div class="accordion-body p-4">
                                    <div class="row">
                                        <div class="col-md-12 mb-3">
                                            <label class="form-label fw-bold">Nombre del Producto <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control" name="nombre" placeholder="Ej. Credi-Impulso Semanal" value="{{ old('nombre') }}" required>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-3 mb-3">
                                            <label class="form-label fw-bold">Tipo <span class="text-danger">*</span></label>
                                            <select class="form-select" name="tipo_credito" required>
                                                <option value="individual" @selected(old('tipo_credito') == 'individual')>Individual</option>
                                                <option value="grupal" @selected(old('tipo_credito') == 'grupal')>Grupal</option>
                                            </select>
                                        </div>
                                        <div class="col-md-3 mb-3">
                                            <label class="form-label fw-bold">Frecuencia de Pago <span class="text-danger">*</span></label>
                                            <select class="form-select" name="frecuencia_pago" required>
                                                <option value="diario" @selected(old('frecuencia_pago') == 'diario')>Diario</option>
                                                <option value="semanal" @selected(old('frecuencia_pago') == 'semanal')>Semanal</option>
                                                <option value="catorcenal" @selected(old('frecuencia_pago') == 'catorcenal')>Catorcenal</option>
                                                <option value="quincenal" @selected(old('frecuencia_pago') == 'quincenal')>Quincenal</option>
                                                <option value="mensual" @selected(old('frecuencia_pago') == 'mensual')>Mensual</option>
                                                <option value="pago_al_final" @selected(old('frecuencia_pago') == 'pago_al_final')>Pago al Final (Capital + Interés)</option>
                                            </select>
                                        </div>
                                        <div class="col-md-3 mb-3">
                                            <label class="form-label fw-bold">Tasa de Interés (%) <span class="text-danger">*</span></label>
                                            <input type="number" step="0.01" class="form-control" name="tasa_interes" value="{{ old('tasa_interes', '0.00') }}" required>
                                        </div>
                                        <div class="col-md-3 mb-3">
                                            <label class="form-label fw-bold">Aplicación de Tasa <span class="text-danger">*</span></label>
                                            <select class="form-select" name="tipo_tasa" required>
                                                <option value="global" @selected(old('tipo_tasa') == 'global')>Global (Fija al Capital)</option>
                                                <option value="saldo_insoluto" @selected(old('tipo_tasa') == 'saldo_insoluto')>Sobre Saldo Insoluto</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- SECCIÓN 2: LÍMITES --}}
                        <div class="accordion-item border mb-3 rounded-3 shadow-sm">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed fw-bold text-success bg-light rounded-3" type="button" data-bs-toggle="collapse" data-bs-target="#collapseTwo" aria-expanded="false">
                                    <i class="bi bi-arrows-collapse me-2 fs-5"></i> 2. Límites Permitidos
                                </button>
                            </h2>
                            <div id="collapseTwo" class="accordion-collapse collapse" data-bs-parent="#accordionProducto">
                                <div class="accordion-body p-4">
                                    <h6 class="fw-bold mb-3">Rango de Plazos (Cuotas)</h6>
                                    <div class="row mb-4">
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label fw-bold">Plazo Mínimo <span class="text-danger">*</span></label>
                                            <input type="number" class="form-control" name="plazo_minimo" value="{{ old('plazo_minimo', 1) }}" required>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label fw-bold">Plazo Máximo <span class="text-danger">*</span></label>
                                            <input type="number" class="form-control" name="plazo_maximo" value="{{ old('plazo_maximo', 12) }}" required>
                                        </div>
                                    </div>
                                    <h6 class="fw-bold mb-3">Rango de Montos ($)</h6>
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label fw-bold">Monto Mínimo a Prestar <span class="text-danger">*</span></label>
                                            <input type="number" step="0.01" class="form-control" name="monto_minimo" value="{{ old('monto_minimo', '1000.00') }}" required>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label fw-bold">Monto Máximo a Prestar <span class="text-danger">*</span></label>
                                            <input type="number" step="0.01" class="form-control" name="monto_maximo" value="{{ old('monto_maximo', '50000.00') }}" required>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- SECCIÓN 3: CASTIGOS --}}
                        <div class="accordion-item border mb-3 rounded-3 shadow-sm border-danger">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed fw-bold text-danger bg-light rounded-3" type="button" data-bs-toggle="collapse" data-bs-target="#collapseThree" aria-expanded="false">
                                    <i class="bi bi-exclamation-triangle-fill me-2 fs-5"></i> 3. Penalizaciones y Castigos
                                </button>
                            </h2>
                            <div id="collapseThree" class="accordion-collapse collapse" data-bs-parent="#accordionProducto">
                                <div class="accordion-body p-4">
                                    
                                    <div class="row mb-4 bg-light p-3 rounded border">
                                        <div class="col-md-4">
                                            <label class="form-label fw-bold">Hora Límite de Pago en su día</label>
                                            <input type="time" class="form-control" name="hora_maxima_pago" value="{{ old('hora_maxima_pago', '10:00') }}">
                                            <small class="text-muted">Déjalo vacío si tienen hasta las 23:59 de su día para pagar.</small>
                                        </div>
                                        <div class="col-md-8">
                                            <label class="form-label fw-bold">Si rompe Multa y Mora, ¿qué cobramos?</label>
                                            <select class="form-select text-danger fw-bold" name="politica_acumulacion">
                                                <option value="acumular" @selected(old('politica_acumulacion') == 'acumular')>Sumar ambas (Cobrar Multa + Mora)</option>
                                                <option value="reemplazar" @selected(old('politica_acumulacion') == 'reemplazar')>Jerarquía (Si pasa al día sig, solo cobrar Mora)</option>
                                                <option value="solo_mayor" @selected(old('politica_acumulacion') == 'solo_mayor')>Inteligente (Cobrar solo la cantidad que resulte más alta)</option>
                                            </select>
                                        </div>
                                    </div>

                                    <h6 class="fw-bold mb-3 text-dark"><i class="bi bi-clock-history me-1"></i> Configuración de MULTA (Cargo Inmediato)</h6>
                                    <div class="row mb-4">
                                        <div class="col-md-4 mb-3">
                                            <label class="form-label">¿Cuándo se dispara?</label>
                                            <select class="form-select" name="multa_trigger">
                                                <option value="despues_de_hora" @selected(old('multa_trigger') == 'despues_de_hora')>Inmediato (Después de la Hora Límite)</option>
                                                <option value="despues_de_dia" @selected(old('multa_trigger') == 'despues_de_dia')>Al día siguiente</option>
                                                <option value="no_aplica" @selected(old('multa_trigger') == 'no_aplica')>No Aplica</option>
                                            </select>
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label class="form-label">Valor del Castigo</label>
                                            <input type="number" step="0.01" class="form-control" name="multa_valor" value="{{ old('multa_valor', '500.00') }}">
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label class="form-label">Tipo de Cálculo</label>
                                            <select class="form-select" name="multa_calculo">
                                                <option value="fijo" @selected(old('multa_calculo') == 'fijo')>Monto Fijo ($)</option>
                                                <option value="porcentaje_pago" @selected(old('multa_calculo') == 'porcentaje_pago')>% del Pago Vencido</option>
                                                <option value="porcentaje_saldo" @selected(old('multa_calculo') == 'porcentaje_saldo')>% del Saldo Restante</option>
                                                <option value="porcentaje_credito" @selected(old('multa_calculo') == 'porcentaje_credito')>% del Crédito Original</option>
                                            </select>
                                        </div>
                                    </div>

                                    <h6 class="fw-bold mb-3 text-dark"><i class="bi bi-calendar-x me-1"></i> Configuración de MORA (Interés por Atraso)</h6>
                                    <div class="row">
                                        <div class="col-md-4 mb-3">
                                            <label class="form-label">¿Cuándo se dispara?</label>
                                            <select class="form-select" name="mora_trigger">
                                                <option value="despues_de_dia" @selected(old('mora_trigger') == 'despues_de_dia')>Al día siguiente</option>
                                                <option value="despues_de_hora" @selected(old('mora_trigger') == 'despues_de_hora')>Inmediato (Después de la Hora Límite)</option>
                                                <option value="no_aplica" @selected(old('mora_trigger') == 'no_aplica')>No Aplica</option>
                                            </select>
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label class="form-label">Valor del Castigo</label>
                                            <input type="number" step="0.01" class="form-control" name="mora_valor" value="{{ old('mora_valor', '10.00') }}">
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label class="form-label">Tipo de Cálculo</label>
                                            <select class="form-select" name="mora_calculo">
                                                <option value="porcentaje_credito" @selected(old('mora_calculo') == 'porcentaje_credito')>% del Crédito Original</option>
                                                <option value="porcentaje_pago" @selected(old('mora_calculo') == 'porcentaje_pago')>% del Pago Vencido</option>
                                                <option value="porcentaje_saldo" @selected(old('mora_calculo') == 'porcentaje_saldo')>% del Saldo Restante</option>
                                                <option value="fijo" @selected(old('mora_calculo') == 'fijo')>Monto Fijo ($)</option>
                                            </select>
                                        </div>
                                    </div>

                                </div>
                            </div>
                        </div>

                    </div>

                    <div class="text-end mt-4">
                        <button type="submit" class="btn btn-primary px-4 shadow-sm"><i class="bi bi-floppy-fill me-1"></i> Guardar Parámetros del Producto</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>