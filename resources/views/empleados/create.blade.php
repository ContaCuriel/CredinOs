<x-app-layout>
    <div class="container-fluid py-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Registrar Nuevo Empleado</h5>
            </div>
            <div class="card-body">
                @if ($errors->any())
                    <div class="alert alert-danger">
                        <ul>
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('empleados.store') }}" method="POST">
                    @csrf

                    <div class="row">
                        {{-- Columna Izquierda --}}
                        <div class="col-md-6">
                            {{-- ... (Campos de nombre_completo) ... --}}
                            <div class="mb-3">
                                <label for="nombre_completo" class="form-label">Nombre Completo <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="nombre_completo" name="nombre_completo" value="{{ old('nombre_completo') }}" required>
                            </div>
                            <div class="mb-3">
                                <label for="id_puesto" class="form-label">Puesto <span class="text-danger">*</span></label>
                                <select class="form-select" id="id_puesto" name="id_puesto" required>
                                    <option value="">Seleccione un puesto...</option>
                                    @foreach ($puestos as $puesto)
                                        <option value="{{ $puesto->id_puesto }}" {{ old('id_puesto') == $puesto->id_puesto ? 'selected' : '' }}>
                                            {{ $puesto->nombre_puesto }} ({{ number_format($puesto->salario_mensual, 2) }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="id_sucursal" class="form-label">Sucursal <span class="text-danger">*</span></label>
                                <select class="form-select" id="id_sucursal" name="id_sucursal" required>
                                    <option value="">Seleccione una sucursal...</option>
                                    @foreach ($sucursales as $sucursal)
                                        <option value="{{ $sucursal->id_sucursal }}" {{ old('id_sucursal') == $sucursal->id_sucursal ? 'selected' : '' }}>
                                            {{ $sucursal->nombre_sucursal }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="mb-3">
                                <label for="id_horario" class="form-label">Horario Asignado <span class="text-danger">*</span></label>
                                <select class="form-select @error('id_horario') is-invalid @enderror" id="id_horario" name="id_horario" required>
                                    <option value="">Seleccione un horario...</option>
                                    @foreach ($horarios as $horario)
                                        {{-- El `old()` intenta recuperar el valor anterior, si no existe, selecciona el primero como default --}}
                                        <option value="{{ $horario->id_horario }}" {{ old('id_horario', $loop->first ? $horario->id_horario : null) == $horario->id_horario ? 'selected' : '' }}>
                                            {{ $horario->nombre_horario }}
                                        </option>
                                    @endforeach
                                </select>
                                <div class="form-text">Se pre-selecciona el primer horario de la lista como opción por defecto.</div>
                                @error('id_horario') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            
                             <div class="mb-3">
                                <label for="fecha_ingreso" class="form-label">Fecha de Ingreso <span class="text-danger">*</span></label>
                                <input type="date" class="form-control @error('fecha_ingreso') is-invalid @enderror" id="fecha_ingreso" name="fecha_ingreso" value="{{ old('fecha_ingreso') }}" required>
                                @error('fecha_ingreso') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                             <div class="mb-3">
                                <label for="fecha_nacimiento" class="form-label">Fecha de Nacimiento <span class="text-danger">*</span></label>
                                <input type="date" class="form-control @error('fecha_nacimiento') is-invalid @enderror" id="fecha_nacimiento" name="fecha_nacimiento" value="{{ old('fecha_nacimiento') }}" required>
                                @error('fecha_nacimiento') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="mb-3">
                                <label for="nacionalidad" class="form-label">Nacionalidad</label>
                                <input type="text" class="form-control @error('nacionalidad') is-invalid @enderror" id="nacionalidad" name="nacionalidad" value="{{ old('nacionalidad', 'Mexicana') }}">
                                @error('nacionalidad') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="mb-3">
                                <label for="sexo" class="form-label">Sexo</label>
                                <select class="form-select @error('sexo') is-invalid @enderror" id="sexo" name="sexo">
                                    <option value="">Seleccione...</option>
                                    <option value="Masculino" {{ old('sexo') == 'Masculino' ? 'selected' : '' }}>Masculino</option>
                                    <option value="Femenino" {{ old('sexo') == 'Femenino' ? 'selected' : '' }}>Femenino</option>
                                    <option value="Otro" {{ old('sexo') == 'Otro' ? 'selected' : '' }}>Otro</option>
                                </select>
                                @error('sexo') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="mb-3">
                                <label for="estado_civil" class="form-label">Estado Civil</label>
                                <select class="form-select @error('estado_civil') is-invalid @enderror" id="estado_civil" name="estado_civil">
                                    <option value="">Seleccione...</option>
                                    <option value="Soltero(a)" {{ old('estado_civil') == 'Soltero(a)' ? 'selected' : '' }}>Soltero(a)</option>
                                    <option value="Casado(a)" {{ old('estado_civil') == 'Casado(a)' ? 'selected' : '' }}>Casado(a)</option>
                                    <option value="Divorciado(a)" {{ old('estado_civil') == 'Divorciado(a)' ? 'selected' : '' }}>Divorciado(a)</option>
                                    <option value="Viudo(a)" {{ old('estado_civil') == 'Viudo(a)' ? 'selected' : '' }}>Viudo(a)</option>
                                    <option value="Unión Libre" {{ old('estado_civil') == 'Unión Libre' ? 'selected' : '' }}>Unión Libre</option>
                                    <option value="Otro" {{ old('estado_civil') == 'Otro' ? 'selected' : '' }}>Otro</option>
                                </select>
                                @error('estado_civil') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            
                            <div class="mb-3">
                                <label for="direccion" class="form-label">Dirección <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('direccion') is-invalid @enderror" id="direccion" name="direccion" value="{{ old('direccion') }}" required>
                                @error('direccion') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            {{-- =====> CAMBIO AQUÍ: Teléfono ya no es obligatorio <===== --}}
                            <div class="mb-3">
                                <label for="telefono" class="form-label">Teléfono</label>
                                <input type="text" class="form-control @error('telefono') is-invalid @enderror" id="telefono" name="telefono" value="{{ old('telefono') }}">
                                @error('telefono') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            {{-- =================================================== --}}
                        </div>

                        {{-- Columna Derecha --}}
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="curp" class="form-label">CURP <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('curp') is-invalid @enderror" id="curp" name="curp" value="{{ old('curp') }}" required>
                                @error('curp') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            {{-- =====> CAMBIO AQUÍ: RFC ya no es obligatorio <===== --}}
                            <div class="mb-3">
                                <label for="rfc" class="form-label">RFC</label>
                                <input type="text" class="form-control @error('rfc') is-invalid @enderror" id="rfc" name="rfc" value="{{ old('rfc') }}">
                                @error('rfc') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            {{-- ================================================= --}}

                            {{-- =====> CAMBIO AQUÍ: NSS ya no es obligatorio <===== --}}
                            <div class="mb-3">
                                <label for="nss" class="form-label">NSS</label>
                                <input type="text" class="form-control @error('nss') is-invalid @enderror" id="nss" name="nss" value="{{ old('nss') }}">
                                @error('nss') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            {{-- ================================================= --}}

                             <div class="mb-3">
                                <label for="cuenta_bancaria" class="form-label">Cuenta Bancaria</label>
                                <input type="text" class="form-control @error('cuenta_bancaria') is-invalid @enderror" id="cuenta_bancaria" name="cuenta_bancaria" value="{{ old('cuenta_bancaria') }}">
                                @error('cuenta_bancaria') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="mb-3">
                                <label for="banco" class="form-label">Banco</label>
                                <input type="text" class="form-control @error('banco') is-invalid @enderror" id="banco" name="banco" value="{{ old('banco') }}">
                                @error('banco') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="mb-3">
                                <label for="contacto_emerg_nombre" class="form-label">Nombre Contacto Emergencia</label>
                                <input type="text" class="form-control @error('contacto_emerg_nombre') is-invalid @enderror" id="contacto_emerg_nombre" name="contacto_emerg_nombre" value="{{ old('contacto_emerg_nombre') }}">
                                @error('contacto_emerg_nombre') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="mb-3">
                                <label for="contacto_emerg_telefono" class="form-label">Teléfono Contacto Emergencia</label>
                                <input type="text" class="form-control @error('contacto_emerg_telefono') is-invalid @enderror" id="contacto_emerg_telefono" name="contacto_emerg_telefono" value="{{ old('contacto_emerg_telefono') }}">
                                @error('contacto_emerg_telefono') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <label for="info_cartas_recomendacion" class="form-label">Información de Cartas de Recomendación (Opcional)</label>
                            <textarea class="form-control @error('info_cartas_recomendacion') is-invalid @enderror" id="info_cartas_recomendacion" name="info_cartas_recomendacion" rows="3">{{ old('info_cartas_recomendacion') }}</textarea>
                            @error('info_cartas_recomendacion')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    
                    <hr>
                    <div class="text-end">
                        <a href="{{ route('empleados.index') }}" class="btn btn-secondary">Cancelar</a>
                        <button type="submit" class="btn btn-primary">Guardar Empleado</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>