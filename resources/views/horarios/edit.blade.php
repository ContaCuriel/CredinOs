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
                    <h6 class="fw-bold text-primary mb-3"><i class="bi bi-shield-check"></i> 2. Regla Simplificada de Tolerancia</h6>
                    
                    @php
                        $tieneToleranciaCheck = old('tiene_tolerancia', $horario->aplicar_reglas_avanzadas);
                    @endphp
                    <div class="card border-warning bg-warning bg-opacity-10 p-3 mb-4" style="max-width: 500px;">
                        <div class="form-check form-switch mb-2">
                            <input class="form-check-input" type="checkbox" id="tiene_tolerancia" name="tiene_tolerancia" value="1" {{ $tieneToleranciaCheck ? 'checked' : '' }}>
                            <label class="form-check-label fw-bold text-warning-emphasis" for="tiene_tolerancia">¿Este horario aplica tolerancia de retardos?</label>
                        </div>
                        <div id="container_tolerancia" style="display: {{ $tieneToleranciaCheck ? 'block' : 'none' }};">
                            <label for="tolerancia_minutos" class="form-label small fw-bold text-dark">Minutos de tolerancia permitidos:</label>
                            <div class="input-group input-group-sm" style="width: 160px;">
                                <input type="number" class="form-control text-center" id="tolerancia_minutos" name="tolerancia_minutos" value="{{ old('tolerancia_minutos', $horario->tolerancia_minutos ?? 0) }}" min="0" {{ $tieneToleranciaCheck ? 'required' : '' }}>
                                <span class="input-group-text">minutos</span>
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

            // Activar/Desactivar campo de tolerancia quincenal
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
        });
    </script>
    @endpush
</x-app-layout>