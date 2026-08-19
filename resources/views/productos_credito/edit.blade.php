<x-app-layout>
    <div class="container-fluid py-4">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <h5 class="mb-0 fw-bold text-dark">
                    <i class="bi bi-pencil-square me-2 text-warning"></i>Editar Producto: {{ $producto->nombre }}
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

                <form action="{{ route('productos_credito.update', $producto->id) }}" method="POST">
                    @csrf
                    @method('PUT')
                    
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
                                        <div class="col-md-8 mb-3">
                                            <label class="form-label fw-bold">Nombre del Producto <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control" name="nombre" value="{{ old('nombre', $producto->nombre) }}" required>
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label class="form-label fw-bold text-danger"><i class="bi bi-shield-lock-fill me-1"></i>¿Requiere Garantía? <span class="text-danger">*</span></label>
                                            <select class="form-select border-danger" name="requiere_garantia" required>
                                                <option value="0" @selected(old('requiere_garantia', $producto->requiere_garantia) == '0')>No</option>
                                                <option value="1" @selected(old('requiere_garantia', $producto->requiere_garantia) == '1')>Sí (Vehículo o Terreno)</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-3 mb-3">
                                            <label class="form-label fw-bold">Tipo <span class="text-danger">*</span></label>
                                            <select class="form-select" name="tipo_credito" required>
                                                <option value="individual" @selected(old('tipo_credito', $producto->tipo_credito) == 'individual')>Individual</option>
                                                <option value="grupal" @selected(old('tipo_credito', $producto->tipo_credito) == 'grupal')>Grupal</option>
                                            </select>
                                        </div>
                                        <div class="col-md-3 mb-3">
                                            <label class="form-label fw-bold">Frecuencia de Pago <span class="text-danger">*</span></label>
                                            <select class="form-select" name="frecuencia_pago" required>
                                                <option value="diario" @selected(old('frecuencia_pago', $producto->frecuencia_pago) == 'diario')>Diario</option>
                                                <option value="semanal" @selected(old('frecuencia_pago', $producto->frecuencia_pago) == 'semanal')>Semanal</option>
                                                <option value="catorcenal" @selected(old('frecuencia_pago', $producto->frecuencia_pago) == 'catorcenal')>Catorcenal</option>
                                                <option value="quincenal" @selected(old('frecuencia_pago', $producto->frecuencia_pago) == 'quincenal')>Quincenal</option>
                                                <option value="mensual" @selected(old('frecuencia_pago', $producto->frecuencia_pago) == 'mensual')>Mensual</option>
                                                <option value="pago_al_final" @selected(old('frecuencia_pago', $producto->frecuencia_pago) == 'pago_al_final')>Pago al Final</option>
                                            </select>
                                        </div>
                                        <div class="col-md-3 mb-3">
                                            <label class="form-label fw-bold">Comisión Apertura (%) <span class="text-danger">*</span></label>
                                            <div class="input-group">
                                                <input type="number" step="0.01" min="0" class="form-control" name="cobro_comision_apertura" value="{{ old('cobro_comision_apertura', $producto->cobro_comision_apertura) }}" required>
                                                <span class="input-group-text">%</span>
                                            </div>
                                        </div>
                                        <div class="col-md-3 mb-3">
                                            <label class="form-label fw-bold">Tasa de Interés (%) <span class="text-danger">*</span></label>
                                            <div class="input-group">
                                                <input type="number" step="0.01" class="form-control" name="tasa_interes" value="{{ old('tasa_interes', $producto->tasa_interes) }}" required>
                                                <span class="input-group-text">%</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label fw-bold">Aplicación de Tasa <span class="text-danger">*</span></label>
                                            <select class="form-select" name="tipo_tasa" required>
                                                <option value="global" @selected(old('tipo_tasa', $producto->tipo_tasa) == 'global')>Global (Fija al Capital)</option>
                                                <option value="saldo_insoluto" @selected(old('tipo_tasa', $producto->tipo_tasa) == 'saldo_insoluto')>Sobre Saldo Insoluto</option>
                                            </select>
                                        </div>
                                        {{-- NUEVO CAMPO: REQUIERE SEGURO --}}
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label fw-bold text-info"><i class="bi bi-car-front-fill me-1"></i>¿Requiere Seguro? <span class="text-danger">*</span></label>
                                            <select class="form-select border-info" name="requiere_seguro" required>
                                                <option value="0" @selected(old('requiere_seguro', $producto->requiere_seguro) == '0')>No</option>
                                                <option value="1" @selected(old('requiere_seguro', $producto->requiere_seguro) == '1')>Sí (Aplica a Vehículos)</option>
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
                                            <input type="number" class="form-control" name="plazo_minimo" value="{{ old('plazo_minimo', $producto->plazo_minimo) }}" required>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label fw-bold">Plazo Máximo <span class="text-danger">*</span></label>
                                            <input type="number" class="form-control" name="plazo_maximo" value="{{ old('plazo_maximo', $producto->plazo_maximo) }}" required>
                                        </div>
                                    </div>
                                    <h6 class="fw-bold mb-3">Rango de Montos ($)</h6>
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label fw-bold">Monto Mínimo a Prestar <span class="text-danger">*</span></label>
                                            <input type="number" step="0.01" class="form-control" name="monto_minimo" value="{{ old('monto_minimo', $producto->monto_minimo) }}" required>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label fw-bold">Monto Máximo a Prestar <span class="text-danger">*</span></label>
                                            <input type="number" step="0.01" class="form-control" name="monto_maximo" value="{{ old('monto_maximo', $producto->monto_maximo) }}" required>
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
                                            <label class="form-label fw-bold">Hora Límite de Pago</label>
                                            <input type="time" class="form-control" name="hora_maxima_pago" value="{{ old('hora_maxima_pago', $producto->hora_maxima_pago ? \Carbon\Carbon::parse($producto->hora_maxima_pago)->format('H:i') : '') }}">
                                            <small class="text-muted">Déjalo vacío si tienen hasta las 23:59.</small>
                                        </div>
                                        <div class="col-md-8">
                                            <label class="form-label fw-bold">Si rompe Multa y Mora, ¿qué cobramos?</label>
                                            <select class="form-select text-danger fw-bold" name="politica_acumulacion">
                                                <option value="acumular" @selected(old('politica_acumulacion', $producto->politica_acumulacion) == 'acumular')>Sumar ambas (Cobrar Multa + Mora)</option>
                                                <option value="reemplazar" @selected(old('politica_acumulacion', $producto->politica_acumulacion) == 'reemplazar')>Jerarquía (Día sig, solo cobrar Mora)</option>
                                                <option value="solo_mayor" @selected(old('politica_acumulacion', $producto->politica_acumulacion) == 'solo_mayor')>Inteligente (Cobrar solo la más alta)</option>
                                            </select>
                                        </div>
                                    </div>

                                    <h6 class="fw-bold mb-3 text-dark"><i class="bi bi-clock-history me-1"></i> Configuración de MULTA</h6>
                                    <div class="row mb-4">
                                        <div class="col-md-4 mb-3">
                                            <label class="form-label">¿Cuándo se dispara?</label>
                                            <select class="form-select" name="multa_trigger">
                                                <option value="despues_de_hora" @selected(old('multa_trigger', $producto->multa_trigger) == 'despues_de_hora')>Inmediato (Hora Límite)</option>
                                                <option value="despues_de_dia" @selected(old('multa_trigger', $producto->multa_trigger) == 'despues_de_dia')>Al día siguiente</option>
                                                <option value="no_aplica" @selected(old('multa_trigger', $producto->multa_trigger) == 'no_aplica')>No Aplica</option>
                                            </select>
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label class="form-label">Valor del Castigo</label>
                                            <input type="number" step="0.01" class="form-control" name="multa_valor" value="{{ old('multa_valor', $producto->multa_valor) }}">
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label class="form-label">Tipo de Cálculo</label>
                                            <select class="form-select" name="multa_calculo">
                                                <option value="fijo" @selected(old('multa_calculo', $producto->multa_calculo) == 'fijo')>Monto Fijo ($)</option>
                                                <option value="porcentaje_pago" @selected(old('multa_calculo', $producto->multa_calculo) == 'porcentaje_pago')>% del Pago Vencido</option>
                                                <option value="porcentaje_saldo" @selected(old('multa_calculo', $producto->multa_calculo) == 'porcentaje_saldo')>% del Saldo Restante</option>
                                                <option value="porcentaje_credito" @selected(old('multa_calculo', $producto->multa_calculo) == 'porcentaje_credito')>% del Crédito Original</option>
                                            </select>
                                        </div>
                                    </div>

                                    <h6 class="fw-bold mb-3 text-dark"><i class="bi bi-calendar-x me-1"></i> Configuración de MORA</h6>
                                    <div class="row mb-4">
                                        <div class="col-md-4 mb-3">
                                            <label class="form-label">¿Cuándo se dispara?</label>
                                            <select class="form-select" name="mora_trigger">
                                                <option value="despues_de_dia" @selected(old('mora_trigger', $producto->mora_trigger) == 'despues_de_dia')>Al día siguiente</option>
                                                <option value="despues_de_hora" @selected(old('mora_trigger', $producto->mora_trigger) == 'despues_de_hora')>Inmediato (Hora Límite)</option>
                                                <option value="no_aplica" @selected(old('mora_trigger', $producto->mora_trigger) == 'no_aplica')>No Aplica</option>
                                            </select>
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label class="form-label">Valor del Castigo</label>
                                            <input type="number" step="0.01" class="form-control" name="mora_valor" value="{{ old('mora_valor', $producto->mora_valor) }}">
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label class="form-label">Tipo de Cálculo</label>
                                            <select class="form-select" name="mora_calculo">
                                                <option value="porcentaje_credito" @selected(old('mora_calculo', $producto->mora_calculo) == 'porcentaje_credito')>% del Crédito Original</option>
                                                <option value="porcentaje_pago" @selected(old('mora_calculo', $producto->mora_calculo) == 'porcentaje_pago')>% del Pago Vencido</option>
                                                <option value="porcentaje_saldo" @selected(old('mora_calculo', $producto->mora_calculo) == 'porcentaje_saldo')>% del Saldo Restante</option>
                                                <option value="fijo" @selected(old('mora_calculo', $producto->mora_calculo) == 'fijo')>Monto Fijo ($)</option>
                                            </select>
                                        </div>
                                    </div>

                                    {{-- NUEVO BLOQUE: MULTA DE SEGURO --}}
                                    <h6 class="fw-bold mb-3 text-info mt-4"><i class="bi bi-shield-x me-1"></i> Configuración de SEGURO (Prendarios)</h6>
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Penalización / Retención por falta de seguro</label>
                                            <div class="input-group">
                                                <span class="input-group-text">$</span>
                                                <input type="number" step="0.01" class="form-control" name="penalizacion_seguro" value="{{ old('penalizacion_seguro', $producto->penalizacion_seguro) }}">
                                            </div>
                                            <small class="text-muted">Si no tiene seguro, este monto se le descontará automáticamente de su fondeo para cubrir la póliza.</small>
                                        </div>
                                    </div>

                                </div>
                            </div>
                        </div>

                    </div>

                    <div class="text-end mt-4">
                        <button type="submit" class="btn btn-warning px-4 shadow-sm text-dark fw-bold"><i class="bi bi-arrow-clockwise me-1"></i> Actualizar Parámetros</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>