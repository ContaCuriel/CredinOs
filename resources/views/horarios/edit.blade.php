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
                    <div class="text-end">
                        <a href="{{ route('horarios.index') }}" class="btn btn-secondary">Cancelar</a>
                        <button type="submit" class="btn btn-primary">Actualizar Horario</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
    {{-- Reutilizamos el mismo script de la vista de creación --}}
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
        });
    </script>
    @endpush
</x-app-layout>