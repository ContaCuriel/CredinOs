<x-app-layout>
    <div class="container-fluid py-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Editar Horario: {{ $horario->nombre_horario }}</h5>
            </div>
            <div class="card-body">
                @if ($errors->any())
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <h6 class="alert-heading fw-bold">¡Por favor corrige los siguientes errores!</h6>
                        <ul>
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                <form action="{{ route('horarios.update', $horario->id_horario) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="nombre_horario" class="form-label">Nombre del Horario <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('nombre_horario') is-invalid @enderror" id="nombre_horario" name="nombre_horario" value="{{ old('nombre_horario', $horario->nombre_horario) }}" required>
                            @error('nombre_horario') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="descripcion" class="form-label">Descripción (Opcional)</label>
                            <textarea class="form-control @error('descripcion') is-invalid @enderror" id="descripcion" name="descripcion" rows="1">{{ old('descripcion', $horario->descripcion) }}</textarea>
                            @error('descripcion') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <hr>
                    <h6 class="mb-3">Días Laborables y Horas</h6>

                    @php
                        $dias = ['lunes', 'martes', 'miercoles', 'jueves', 'viernes', 'sabado', 'domingo'];
                    @endphp

                    @foreach ($dias as $dia)
                        <div class="row align-items-center mb-2 p-2" style="background-color: #f8f9fa; border-radius: 5px;">
                            <div class="col-md-2">
                                <div class="form-check form-switch">
                                    <input class="form-check-input dia-toggle" type="checkbox" role="switch" 
                                           id="{{ $dia }}" name="{{ $dia }}" 
                                           {{ old($dia, $horario->$dia) ? 'checked' : '' }}
                                           data-dia="{{ $dia }}">
                                    <label class="form-check-label" for="{{ $dia }}">{{ ucfirst($dia) }}</label>
                                </div>
                            </div>
                            <div class="col-md-5">
                                <label for="{{ $dia }}_entrada" class="form-label mb-0 small">Entrada:</label>
                                <input type="time" class="form-control form-control-sm hora-input" 
                                       id="{{ $dia }}_entrada" name="{{ $dia }}_entrada" 
                                       value="{{ old($dia.'_entrada', $horario->{$dia.'_entrada'}) }}"
                                       {{ old($dia, $horario->$dia) ? '' : 'disabled' }}>
                            </div>
                            <div class="col-md-5">
                                <label for="{{ $dia }}_salida" class="form-label mb-0 small">Salida:</label>
                                <input type="time" class="form-control form-control-sm hora-input" 
                                       id="{{ $dia }}_salida" name="{{ $dia }}_salida"
                                       value="{{ old($dia.'_salida', $horario->{$dia.'_salida'}) }}" 
                                       {{ old($dia, $horario->$dia) ? '' : 'disabled' }}>
                            </div>
                        </div>
                    @endforeach

                    <hr class="mt-4">
                    <h6 class="mb-3"><i class="bi bi-exclamation-triangle-fill text-warning"></i> Reglas de Asistencia y Penalizaciones (Opcional)</h6>
                    
                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" role="switch" id="aplicar_reglas_avanzadas" name="aplicar_reglas_avanzadas" value="1" {{ old('aplicar_reglas_avanzadas', $horario->aplicar_reglas_avanzadas) ? 'checked' : '' }}>
                        <label class="form-check-label fw-bold" for="aplicar_reglas_avanzadas">Habilitar penalizaciones por retardos y faltas para este horario</label>
                    </div>

                    <div id="seccion_reglas_avanzadas" style="display: {{ old('aplicar_reglas_avanzadas', $horario->aplicar_reglas_avanzadas) ? 'block' : 'none' }}; background-color: #f8f9fa; padding: 20px; border-radius: 8px; border: 1px solid #dee2e6;">
                        
                        <h6 class="text-primary fw-bold mb-3">1. Tolerancia y Retardos Menores</h6>
                        <div class="row mb-4">
                            <div class="col-md-3 mb-2">
                                <label for="tolerancia_minutos" class="form-label small">Tolerancia (Minutos)</label>
                                <input type="number" class="form-control form-control-sm" id="tolerancia_minutos" name="tolerancia_minutos" value="{{ old('tolerancia_minutos', $horario->tolerancia_minutos ?? 10) }}">
                                <small class="text-muted" style="font-size: 0.75rem;">Ej: 10 (Sin castigo)</small>
                            </div>
                            <div class="col-md-3 mb-2">
                                <label for="retardo_menor_minutos_inicio" class="form-label small">Inicio Retardo Menor</label>
                                <input type="number" class="form-control form-control-sm" id="retardo_menor_minutos_inicio" name="retardo_menor_minutos_inicio" value="{{ old('retardo_menor_minutos_inicio', $horario->retardo_menor_minutos_inicio ?? 11) }}">
                            </div>
                            <div class="col-md-3 mb-2">
                                <label for="retardo_menor_minutos_fin" class="form-label small">Fin Retardo Menor</label>
                                <input type="number" class="form-control form-control-sm" id="retardo_menor_minutos_fin" name="retardo_menor_minutos_fin" value="{{ old('retardo_menor_minutos_fin', $horario->retardo_menor_minutos_fin ?? 15) }}">
                            </div>
                            <div class="col-md-3 mb-2">
                                <label for="retardos_para_falta" class="form-label small">¿Cuántos Retardos = Falta?</label>
                                <input type="number" class="form-control form-control-sm" id="retardos_para_falta" name="retardos_para_falta" value="{{ old('retardos_para_falta', $horario->retardos_para_falta ?? 3) }}">
                                <small class="text-muted" style="font-size: 0.75rem;">Ej: 3</small>
                            </div>
                        </div>

                        <h6 class="text-warning fw-bold mb-3">2. Medio Día y Faltas por Retraso</h6>
                        <div class="row mb-4">
                            <div class="col-md-4 mb-2">
                                <label for="medio_dia_minutos_inicio" class="form-label small">Inicio Medio Día (Min)</label>
                                <input type="number" class="form-control form-control-sm" id="medio_dia_minutos_inicio" name="medio_dia_minutos_inicio" value="{{ old('medio_dia_minutos_inicio', $horario->medio_dia_minutos_inicio ?? 16) }}">
                            </div>
                            <div class="col-md-4 mb-2">
                                <label for="medio_dia_minutos_fin" class="form-label small">Fin Medio Día (Min)</label>
                                <input type="number" class="form-control form-control-sm" id="medio_dia_minutos_fin" name="medio_dia_minutos_fin" value="{{ old('medio_dia_minutos_fin', $horario->medio_dia_minutos_fin ?? 30) }}">
                                <small class="text-muted" style="font-size: 0.75rem;">Se descuenta medio día</small>
                            </div>
                            <div class="col-md-4 mb-2">
                                <label for="falta_minutos_inicio" class="form-label small">Considerar FALTA desde (Min)</label>
                                <input type="number" class="form-control form-control-sm" id="falta_minutos_inicio" name="falta_minutos_inicio" value="{{ old('falta_minutos_inicio', $horario->falta_minutos_inicio ?? 31) }}">
                                <small class="text-muted" style="font-size: 0.75rem;">Se regresa al trabajador</small>
                            </div>
                        </div>

                        <h6 class="text-danger fw-bold mb-3">3. Descuentos por Faltas Injustificadas</h6>
                        <div class="row">
                            <div class="col-md-6 mb-2">
                                <label for="castigo_falta_lun_vie" class="form-label small">Días a descontar (Lunes o Viernes)</label>
                                <input type="number" step="0.5" class="form-control form-control-sm" id="castigo_falta_lun_vie" name="castigo_falta_lun_vie" value="{{ old('castigo_falta_lun_vie', $horario->castigo_falta_lun_vie ?? 3) }}">
                                <small class="text-muted" style="font-size: 0.75rem;">Ej: 3 (Día de falta + 2 de castigo)</small>
                            </div>
                            <div class="col-md-6 mb-2">
                                <label for="castigo_falta_mar_jue_sab" class="form-label small">Días a descontar (Mar/Mié/Jue/Sáb)</label>
                                <input type="number" step="0.5" class="form-control form-control-sm" id="castigo_falta_mar_jue_sab" name="castigo_falta_mar_jue_sab" value="{{ old('castigo_falta_mar_jue_sab', $horario->castigo_falta_mar_jue_sab ?? 2) }}">
                                <small class="text-muted" style="font-size: 0.75rem;">Ej: 2 (Día de falta + 1 de castigo)</small>
                            </div>
                        </div>
                    </div>
                    <hr class="mt-4">
                    <div class="text-end">
                        <a href="{{ route('horarios.index') }}" class="btn btn-secondary">Cancelar</a>
                        <button type="submit" class="btn btn-primary">Actualizar Horario</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            document.querySelectorAll('.dia-toggle').forEach(function(checkbox) {
                checkbox.addEventListener('change', function() {
                    const dia = this.dataset.dia;
                    const entradaInput = document.getElementById(dia + '_entrada');
                    const salidaInput = document.getElementById(dia + '_salida');

                    if (this.checked) {
                        entradaInput.removeAttribute('disabled');
                        salidaInput.removeAttribute('disabled');
                    } else {
                        entradaInput.setAttribute('disabled', 'disabled');
                        salidaInput.setAttribute('disabled', 'disabled');
                        entradaInput.value = '';
                        salidaInput.value = '';
                    }
                });
            });

            const toggleReglas = document.getElementById('aplicar_reglas_avanzadas');
            const seccionReglas = document.getElementById('seccion_reglas_avanzadas');
            if(toggleReglas) {
                toggleReglas.addEventListener('change', function() {
                    seccionReglas.style.display = this.checked ? 'block' : 'none';
                });
            }
        });
    </script>
    @endpush
</x-app-layout>