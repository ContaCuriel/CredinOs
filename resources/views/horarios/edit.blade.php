<x-app-layout>
    <div class="container py-4">
        <div class="card shadow-sm">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0 fw-bold text-dark"><i class="bi bi-pencil-square text-info"></i> Editar Horario: {{ $horario->nombre_horario }}</h5>
                <a href="{{ route('horarios.index') }}" class="btn btn-sm btn-outline-secondary">
                    <i class="bi bi-arrow-left"></i> Regresar
                </a>
            </div>
            <div class="card-body">
                @if ($errors->any())
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                <form action="{{ route('horarios.update', $horario->id_horario) }}" method="POST">
                    @csrf
                    @method('PUT')
                    
                    {{-- Datos Generales --}}
                    <div class="row mb-4">
                        <div class="col-md-6 mb-3">
                            <label for="nombre_horario" class="form-label fw-bold">Nombre del Horario <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="nombre_horario" name="nombre_horario" value="{{ old('nombre_horario', $horario->nombre_horario) }}" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="descripcion" class="form-label fw-bold">Descripción (Opcional)</label>
                            <input type="text" class="form-control" id="descripcion" name="descripcion" value="{{ old('descripcion', $horario->descripcion) }}">
                        </div>
                    </div>

                    <hr>
                    <h6 class="fw-bold text-primary mb-3"><i class="bi bi-calendar-week"></i> 1. Configuración de Días y Horas de Entrada/Salida</h6>
                    <p class="text-muted small">Marca la casilla del día para indicar que es **laborable** y define sus horas.</p>

                    @php
                        $diasSemana = [
                            'lunes' => 'Lunes', 'martes' => 'Martes', 'miercoles' => 'Miércoles',
                            'jueves' => 'Jueves', 'viernes' => 'Viernes', 'sabado' => 'Sábado', 'domingo' => 'Domingo'
                        ];
                    @endphp

                    <div class="row row-cols-1 row-cols-md-2 row-cols-lg-4 g-3 mb-4">
                        @foreach($diasSemana as $key => $label)
                            @php 
                                $diaLaborable = old($key, $horario->{$key}); 
                                $entradaValue = old($key.'_entrada', $horario->{$key.'_entrada'} ? \Carbon\Carbon::parse($horario->{$key.'_entrada'})->format('H:i') : '');
                                $salidaValue = old($key.'_salida', $horario->{$key.'_salida'} ? \Carbon\Carbon::parse($horario->{$key.'_salida'})->format('H:i') : '');
                            @endphp
                            <div class="col">
                                <div class="card h-100 border bg-light p-3">
                                    <div class="form-check form-switch mb-2">
                                        <input class="form-check-switch form-check-input switch-dia" type="checkbox" id="check_{{ $key }}" name="{{ $key }}" value="1" {{ $diaLaborable ? 'checked' : '' }}>
                                        <label class="form-check-label fw-bold text-dark" for="check_{{ $key }}">{{ $label }}</label>
                                    </div>
                                    <div id="inputs_{{ $key }}" class="container-inputs-dia" style="display: {{ $diaLaborable ? 'block' : 'none' }};">
                                        <div class="mb-2">
                                            <label class="small text-muted mb-0">Entrada:</label>
                                            <input type="time" class="form-control form-control-sm" name="{{ $key }}_entrada" value="{{ $entradaValue }}" {{ $diaLaborable ? 'required' : '' }}>
                                        </div>
                                        <div>
                                            <label class="small text-muted mb-0">Salida:</label>
                                            <input type="time" class="form-control form-control-sm" name="{{ $key }}_salida" value="{{ $salidaValue }}" {{ $diaLaborable ? 'required' : '' }}>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <hr>
                    <h6 class="fw-bold text-primary mb-3"><i class="bi bi-shield-check"></i> 2. Reglas de Disciplina y Castigos (Flexibles)</h6>
                    
                    {{-- BLOQUE: TOLERANCIA Y RETARDOS --}}
                    @php
                        // Evaluamos si tiene tolerancia_minutos guardada > 0
                        $tieneToleranciaCheck = old('tiene_tolerancia', ($horario->tolerancia_minutos > 0));
                    @endphp
                    <div class="card border-warning bg-warning bg-opacity-10 p-3 mb-3">
                        <div class="row">
                            <div class="col-md-4 border-end border-warning">
                                <div class="form-check form-switch mb-2">
                                    <input class="form-check-input" type="checkbox" id="tiene_tolerancia" name="tiene_tolerancia" value="1" {{ $tieneToleranciaCheck ? 'checked' : '' }}>
                                    <label class="form-check-label fw-bold text-warning-emphasis" for="tiene_tolerancia">Aplicar Tolerancia</label>
                                </div>
                                <div id="container_tolerancia" style="display: {{ $tieneToleranciaCheck ? 'block' : 'none' }};">
                                    <label for="tolerancia_minutos" class="form-label small fw-bold text-dark">Minutos de tolerancia:</label>
                                    <div class="input-group input-group-sm mb-2">
                                        <input type="number" class="form-control" id="tolerancia_minutos" name="tolerancia_minutos" value="{{ old('tolerancia_minutos', $horario->tolerancia_minutos ?? 0) }}" min="0">
                                        <span class="input-group-text">min</span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4 border-end border-warning">
                                <label class="form-label small fw-bold text-dark">Límite para ser RETARDO:</label>
                                <div class="input-group input-group-sm mb-2">
                                    <input type="number" class="form-control" name="minutos_limite_retardo" value="{{ old('minutos_limite_retardo', $horario->minutos_limite_retardo ?? 15) }}" min="0">
                                    <span class="input-group-text">min</span>
                                </div>
                                <small class="text-muted" style="font-size: 0.75rem;">Después de la tolerancia hasta este minuto.</small>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-bold text-dark">Castigo por retardos:</label>
                                <div class="input-group input-group-sm mb-2">
                                    <input type="number" class="form-control" name="retardos_por_falta" value="{{ old('retardos_por_falta', $horario->retardos_por_falta ?? 3) }}" min="0">
                                    <span class="input-group-text">retardos = 1 falta</span>
                                </div>
                                <small class="text-muted" style="font-size: 0.75rem;">Pon 0 para no castigar acumulados.</small>
                            </div>
                        </div>
                    </div>

                    {{-- BLOQUE: MEDIO DÍA --}}
                    @php
                        // Corrección de lectura booleana
                        $aplicaMedioDiaCheck = old('aplica_medio_dia', $horario->aplica_medio_dia == 1 || $horario->aplica_medio_dia === true || $horario->aplica_medio_dia === 't');
                    @endphp
                    <div class="card border-info bg-info bg-opacity-10 p-3 mb-3">
                        <div class="row align-items-center">
                            <div class="col-md-4 border-end border-info">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="aplica_medio_dia" name="aplica_medio_dia" value="1" {{ $aplicaMedioDiaCheck ? 'checked' : '' }}>
                                    <label class="form-check-label fw-bold text-info-emphasis" for="aplica_medio_dia">Descuento Medio Día (0.5)</label>
                                </div>
                            </div>
                            <div class="col-md-8">
                                <div id="container_medio_dia" style="display: {{ $aplicaMedioDiaCheck ? 'flex' : 'none' }}; align-items: center; gap: 15px;">
                                    <label class="form-label small fw-bold text-dark mb-0">Límite para Medio Día:</label>
                                    <div class="input-group input-group-sm" style="width: 150px;">
                                        <input type="number" class="form-control" id="minutos_limite_medio_dia" name="minutos_limite_medio_dia" value="{{ old('minutos_limite_medio_dia', $horario->minutos_limite_medio_dia ?? 30) }}" min="0">
                                        <span class="input-group-text">min</span>
                                    </div>
                                    <small class="text-muted" style="font-size: 0.75rem;">Exceder este límite será Falta Directa.</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- BLOQUE: MULTIPLICADOR DE FALTAS --}}
                    @php
                        // Corrección de lectura booleana
                        $aplicaMultiplicadorCheck = old('aplica_castigo_multiplicador', $horario->aplica_castigo_multiplicador == 1 || $horario->aplica_castigo_multiplicador === true || $horario->aplica_castigo_multiplicador === 't');
                    @endphp
                    <div class="card border-danger bg-danger bg-opacity-10 p-3 mb-4">
                        <div class="row align-items-center">
                            <div class="col-md-4 border-end border-danger">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="aplica_castigo_multiplicador" name="aplica_castigo_multiplicador" value="1" {{ $aplicaMultiplicadorCheck ? 'checked' : '' }}>
                                    <label class="form-check-label fw-bold text-danger-emphasis" for="aplica_castigo_multiplicador">Multiplicar Días x Falta</label>
                                </div>
                            </div>
                            <div class="col-md-8">
                                <div id="container_multiplicadores" style="display: {{ $aplicaMultiplicadorCheck ? 'flex' : 'none' }}; gap: 15px;">
                                    <div>
                                        <label class="form-label small fw-bold text-dark mb-1">Lunes/Viernes:</label>
                                        <div class="input-group input-group-sm">
                                            <input type="number" class="form-control" id="multiplicador_lunes_viernes" name="multiplicador_lunes_viernes" value="{{ old('multiplicador_lunes_viernes', $horario->multiplicador_lunes_viernes ?? 3) }}" min="1">
                                            <span class="input-group-text">días</span>
                                        </div>
                                    </div>
                                    <div>
                                        <label class="form-label small fw-bold text-dark mb-1">Días Regulares:</label>
                                        <div class="input-group input-group-sm">
                                            <input type="number" class="form-control" id="multiplicador_dias_regulares" name="multiplicador_dias_regulares" value="{{ old('multiplicador_dias_regulares', $horario->multiplicador_dias_regulares ?? 2) }}" min="1">
                                            <span class="input-group-text">días</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="text-end mt-4">
                        <button type="submit" class="btn btn-info text-white px-4 fw-bold shadow-sm">
                            <i class="bi bi-arrow-clockwise"></i> Actualizar Horario
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Activar/Desactivar campos de horas por día
            document.querySelectorAll('.switch-dia').forEach(function(checkbox) {
                checkbox.addEventListener('change', function() {
                    const diaKey = this.id.replace('check_', '');
                    const container = document.getElementById('inputs_' + diaKey);
                    if (this.checked) {
                        container.style.display = 'block';
                        container.querySelectorAll('input').forEach(i => i.required = true);
                    } else {
                        container.style.display = 'none';
                        container.querySelectorAll('input').forEach(i => { i.required = false; i.value = ''; });
                    }
                });
            });

            // Lógica Toggle: Tolerancia
            const checkTolerancia = document.getElementById('tiene_tolerancia');
            const containerTolerancia = document.getElementById('container_tolerancia');
            const inputTolerancia = document.getElementById('tolerancia_minutos');

            if (checkTolerancia) {
                checkTolerancia.addEventListener('change', function() {
                    if (this.checked) {
                        containerTolerancia.style.display = 'block';
                        inputTolerancia.required = true;
                    } else {
                        containerTolerancia.style.display = 'none';
                        inputTolerancia.required = false;
                        inputTolerancia.value = 0;
                    }
                });
            }

            // Lógica Toggle: Medio Día
            const checkMedioDia = document.getElementById('aplica_medio_dia');
            const containerMedioDia = document.getElementById('container_medio_dia');
            const inputMedioDia = document.getElementById('minutos_limite_medio_dia');

            if (checkMedioDia) {
                checkMedioDia.addEventListener('change', function() {
                    if (this.checked) {
                        containerMedioDia.style.display = 'flex';
                        inputMedioDia.required = true;
                    } else {
                        containerMedioDia.style.display = 'none';
                        inputMedioDia.required = false;
                    }
                });
            }

            // Lógica Toggle: Multiplicadores
            const checkMulti = document.getElementById('aplica_castigo_multiplicador');
            const containerMulti = document.getElementById('container_multiplicadores');
            const inputLV = document.getElementById('multiplicador_lunes_viernes');
            const inputReg = document.getElementById('multiplicador_dias_regulares');

            if (checkMulti) {
                checkMulti.addEventListener('change', function() {
                    if (this.checked) {
                        containerMulti.style.display = 'flex';
                        inputLV.required = true;
                        inputReg.required = true;
                    } else {
                        containerMulti.style.display = 'none';
                        inputLV.required = false;
                        inputReg.required = false;
                    }
                });
            }
        });
    </script>
    @endpush
</x-app-layout>