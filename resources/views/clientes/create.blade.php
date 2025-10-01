<x-app-layout>
    <div class="container-fluid py-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Registrar Nuevo Cliente</h5>
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

                <form action="{{ route('clientes.store') }}" method="POST">
                    @csrf
                    
                    <div class="accordion" id="accordionCliente">

                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
                                    <strong>Sección 1: Datos Personales</strong>
                                </button>
                            </h2>
                            <div id="collapseOne" class="accordion-collapse collapse show" data-bs-parent="#accordionCliente">
                                <div class="accordion-body">
                                    <div class="row">
                                        <div class="col-md-4 mb-3"><label class="form-label">Nombre(s) <span class="text-danger">*</span></label><input type="text" class="form-control" name="nombre" value="{{ old('nombre') }}" required></div>
                                        <div class="col-md-4 mb-3"><label class="form-label">Apellido Paterno <span class="text-danger">*</span></label><input type="text" class="form-control" name="apellido_paterno" value="{{ old('apellido_paterno') }}" required></div>
                                        <div class="col-md-4 mb-3"><label class="form-label">Apellido Materno</label><input type="text" class="form-control" name="apellido_materno" value="{{ old('apellido_materno') }}" required></div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-4 mb-3"><label class="form-label">Fecha de Nacimiento <span class="text-danger">*</span></label><input type="date" class="form-control" name="fecha_nacimiento" value="{{ old('fecha_nacimiento') }}" required></div>
                                        <div class="col-md-4 mb-3">
                                            <label class="form-label">Género <span class="text-danger">*</span></label>
                                            <select name="genero" class="form-select" required>
                                                <option value="" disabled {{ old('genero') ? '' : 'selected' }}>Seleccione...</option>
                                                <option value="Masculino" {{ old('genero') == 'Masculino' ? 'selected' : '' }}>Masculino</option>
                                                <option value="Femenino" {{ old('genero') == 'Femenino' ? 'selected' : '' }}>Femenino</option>
                                                <option value="Otro" {{ old('genero') == 'Otro' ? 'selected' : '' }}>Otro</option>
                                            </select>
                                        </div>
                                        <div class="col-md-4 mb-3"><label class="form-label">CURP</label><input type="text" class="form-control text-uppercase" name="curp" value="{{ old('curp') }}"></div>
                                    </div>
                                    <div class="row">
    <div class="col-md-4 mb-3"><label class="form-label">Vencimiento del INE <span class="text-danger">*</span></label><input type="date" class="form-control" name="vencimiento_ine" value="{{ old('vencimiento_ine', $cliente->vencimiento_ine ?? '') }}" required></div>
    <div class="col-md-4 mb-3">
        <label class="form-label">Estado de Nacimiento <span class="text-danger">*</span></label>
        {{-- CAMBIO A SELECT --}}
        <select class="form-select" name="estado_nacimiento" required>
            <option value="" disabled {{ old('estado_nacimiento', $cliente->estado_nacimiento ?? '') ? '' : 'selected' }}>Seleccione un estado...</option>
            <option value="AGUASCALIENTES">AGUASCALIENTES</option>
            <option value="BAJA CALIFORNIA">BAJA CALIFORNIA</option>
            <option value="BAJA CALIFORNIA SUR">BAJA CALIFORNIA SUR</option>
            <option value="CAMPECHE">CAMPECHE</option>
            <option value="CHIAPAS">CHIAPAS</option>
            <option value="CHIHUAHUA">CHIHUAHUA</option>
            <option value="CIUDAD DE MEXICO">CIUDAD DE MEXICO</option>
            <option value="COAHUILA">COAHUILA</option>
            <option value="COLIMA">COLIMA</option>
            <option value="DURANGO">DURANGO</option>
            <option value="GUANAJUATO">GUANAJUATO</option>
            <option value="GUERRERO">GUERRERO</option>
            <option value="HIDALGO">HIDALGO</option>
            <option value="JALISCO">JALISCO</option>
            <option value="MEXICO">MEXICO</option>
            <option value="MICHOACAN">MICHOACAN</option>
            <option value="MORELOS">MORELOS</option>
            <option value="NAYARIT">NAYARIT</option>
            <option value="NUEVO LEON">NUEVO LEON</option>
            <option value="OAXACA">OAXACA</option>
            <option value="PUEBLA">PUEBLA</option>
            <option value="QUERETARO">QUERETARO</option>
            <option value="QUINTANA ROO">QUINTANA ROO</option>
            <option value="SAN LUIS POTOSI">SAN LUIS POTOSI</option>
            <option value="SINALOA">SINALOA</option>
            <option value="SONORA">SONORA</option>
            <option value="TABASCO">TABASCO</option>
            <option value="TAMAULIPAS">TAMAULIPAS</option>
            <option value="TLAXCALA">TLAXCALA</option>
            <option value="VERACRUZ">VERACRUZ</option>
            <option value="YUCATAN">YUCATAN</option>
            <option value="ZACATECAS">ZACATECAS</option>
        </select>
    </div>
    <div class="col-md-4 mb-3"><label class="form-label">Nacionalidad <span class="text-danger">*</span></label><input type="text" class="form-control" name="nacionalidad" value="{{ old('nacionalidad', $cliente->nacionalidad ?? 'Mexicana') }}" required></div>
</div>
                                    <div class="row">
                                         <div class="col-md-4 mb-3"><label class="form-label">Estado Civil <span class="text-danger">*</span></label><input type="text" class="form-control" name="estado_civil" value="{{ old('estado_civil') }}" required></div>
                                         <div class="col-md-4 mb-3"><label class="form-label">Número de Hijos <span class="text-danger">*</span></label><input type="number" class="form-control" name="numero_hijos" value="{{ old('numero_hijos', 0) }}" min="0" required></div>
                                         <div class="col-md-4 mb-3"><label class="form-label">Dependientes Económicos <span class="text-danger">*</span></label><input type="number" class="form-control" name="dependientes_economicos" value="{{ old('dependientes_economicos', 0) }}" min="0" required></div>
                                    </div>
                                    <hr>
                                    <h6 class="mt-3">Dirección</h6>
                                    <div class="row">
                                        <div class="col-md-8 mb-3"><label class="form-label">Calle <span class="text-danger">*</span></label><input type="text" class="form-control" name="calle" value="{{ old('calle') }}" required></div>
                                        <div class="col-md-4 mb-3"><label class="form-label">Número <span class="text-danger">*</span></label><input type="text" class="form-control" name="numero" value="{{ old('numero') }}" required></div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-4 mb-3"><label class="form-label">Colonia <span class="text-danger">*</span></label><input type="text" class="form-control" name="colonia" value="{{ old('colonia') }}" required></div>
                                        <div class="col-md-4 mb-3"><label class="form-label">Código Postal <span class="text-danger">*</span></label><input type="text" class="form-control" name="codigo_postal" value="{{ old('codigo_postal') }}" required></div>
                                        <div class="col-md-4 mb-3"><label class="form-label">Fecha Comprobante Domicilio <span class="text-danger">*</span></label><input type="date" class="form-control" name="fecha_comprobante_domicilio" value="{{ old('fecha_comprobante_domicilio') }}" required></div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6 mb-3"><label class="form-label">Municipio <span class="text-danger">*</span></label><input type="text" class="form-control" name="municipio" value="{{ old('municipio') }}" required></div>
                                        <div class="col-md-6 mb-3"><label class="form-label">Estado <span class="text-danger">*</span></label><input type="text" class="form-control" name="estado" value="{{ old('estado') }}" required></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="accordion-item">
    <h2 class="accordion-header" id="headingTwo">
        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo">
            <strong>Sección 2: Datos Laborales</strong>
        </button>
    </h2>
    <div id="collapseTwo" class="accordion-collapse collapse" aria-labelledby="headingTwo" data-bs-parent="#accordionCliente">
        <div class="accordion-body">
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Nombre del Negocio <span class="text-danger">*</span></label>
                    {{-- NAME CORREGIDO: nombre_negocio --}}
                    <input type="text" class="form-control" name="nombre_negocio" value="{{ old('nombre_negocio', $cliente->nombre_negocio ?? '') }}" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Giro del Negocio <span class="text-danger">*</span></label>
                     {{-- NAME CORREGIDO: giro_negocio --}}
                    <input type="text" class="form-control" name="giro_negocio" value="{{ old('giro_negocio', $cliente->giro_negocio ?? '') }}" required>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Destino del Crédito <span class="text-danger">*</span></label>
                     {{-- NAME CORREGIDO: destino_credito --}}
                    <input type="text" class="form-control" name="destino_credito" value="{{ old('destino_credito', $cliente->destino_credito ?? '') }}" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Antigüedad del Negocio (años) <span class="text-danger">*</span></label>
                     {{-- NAME CORREGIDO Y TIPO CORRECTO: antiguedad_negocio, tipo number --}}
                    <input type="number" class="form-control" name="antiguedad_negocio" value="{{ old('antiguedad_negocio', $cliente->antiguedad_negocio ?? '') }}" min="0" required>
                </div>
            </div>
            {{-- Puedes agregar aquí la dirección del negocio si es necesario --}}
        </div>
    </div>
</div>

                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseThree" aria-expanded="false" aria-controls="collapseThree">
                                    <strong>Sección 3: Referencias (se requieren 2)</strong>
                                </button>
                            </h2>
                            <div id="collapseThree" class="accordion-collapse collapse" data-bs-parent="#accordionCliente">
                                <div class="accordion-body">
                                    @for ($i = 0; $i < 2; $i++)
                                        <h6 class="mt-3">Referencia {{ $i + 1 }}</h6>
                                        <div class="row">
                                            <div class="col-md-4 mb-3"><label class="form-label">Nombre Completo <span class="text-danger">*</span></label><input type="text" class="form-control" name="referencias[{{ $i }}][nombre_referencia]" value="{{ old('referencias.'.$i.'.nombre_referencia') }}" required></div>
                                            <div class="col-md-4 mb-3"><label class="form-label">Parentesco <span class="text-danger">*</span></label><input type="text" class="form-control" name="referencias[{{ $i }}][parentesco]" value="{{ old('referencias.'.$i.'.parentesco') }}" required></div>
                                            <div class="col-md-4 mb-3"><label class="form-label">Teléfono <span class="text-danger">*</span></label><input type="tel" class="form-control" name="referencias[{{ $i }}][telefono]" value="{{ old('referencias.'.$i.'.telefono') }}" required></div>
                                        </div>
                                    @endfor
                                </div>
                            </div>
                        </div>

                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseFour" aria-expanded="false" aria-controls="collapseFour">
                                    <strong>Sección 4: Asignación</strong>
                                </button>
                            </h2>
                            <div id="collapseFour" class="accordion-collapse collapse" data-bs-parent="#accordionCliente">
                                <div class="accordion-body">
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label for="id_sucursal" class="form-label">Sucursal <span class="text-danger">*</span></label>
                                            <select class="form-select" name="id_sucursal" required>
                                                <option value="" disabled selected>Selecciona una sucursal</option>
                                                @foreach ($sucursales as $sucursal)
                                                    <option value="{{ $sucursal->id_sucursal }}" {{ old('id_sucursal') == $sucursal->id_sucursal ? 'selected' : '' }}>
                                                        {{ $sucursal->nombre_sucursal }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="text-end mt-4">
                        <a href="{{ route('clientes.index') }}" class="btn btn-secondary">Cancelar</a>
                        <button type="submit" class="btn btn-primary">Guardar Cliente</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>