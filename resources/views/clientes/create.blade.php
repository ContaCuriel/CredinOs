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
                                    {{-- NOMBRES Y DATOS BÁSICOS --}}
                                    <div class="row">
                                        <div class="col-md-4 mb-3"><label class="form-label">Nombre(s) <span class="text-danger">*</span></label><input type="text" class="form-control" name="nombre" value="{{ old('nombre') }}" required></div>
                                        <div class="col-md-4 mb-3"><label class="form-label">Apellido Paterno <span class="text-danger">*</span></label><input type="text" class="form-control" name="apellido_paterno" value="{{ old('apellido_paterno') }}" required></div>
                                        <div class="col-md-4 mb-3"><label class="form-label">Apellido Materno</label><input type="text" class="form-control" name="apellido_materno" value="{{ old('apellido_materno') }}"></div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-4 mb-3"><label class="form-label">Fecha de Nacimiento <span class="text-danger">*</span></label><input type="date" class="form-control" name="fecha_nacimiento" value="{{ old('fecha_nacimiento') }}" required></div>
                                        <div class="col-md-4 mb-3"><label class="form-label">CURP</label><input type="text" class="form-control text-uppercase" name="curp" value="{{ old('curp') }}"></div>
                                        <div class="col-md-4 mb-3">
                                            <label class="form-label">Año de Vencimiento del INE <span class="text-danger">*</span></label>
                                            <input type="number" class="form-control" name="vencimiento_ine" placeholder="YYYY" min="{{ date('Y') }}" value="{{ old('vencimiento_ine') }}" required>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-4 mb-3">
                                            <label class="form-label">Estado Civil <span class="text-danger">*</span></label>
                                            <select class="form-select" name="estado_civil" required>
                                                <option value="Soltero(a)" @selected(old('estado_civil') == 'Soltero(a)')>Soltero(a)</option>
                                                <option value="Casado(a)" @selected(old('estado_civil') == 'Casado(a)')>Casado(a)</option>
                                                <option value="Divorciado(a)" @selected(old('estado_civil') == 'Divorciado(a)')>Divorciado(a)</option>
                                                <option value="Viudo(a)" @selected(old('estado_civil') == 'Viudo(a)')>Viudo(a)</option>
                                                <option value="Unión Libre" @selected(old('estado_civil') == 'Unión Libre')>Unión Libre</option>
                                            </select>
                                        </div>
                                        <div class="col-md-4 mb-3"><label class="form-label">Teléfono Celular <span class="text-danger">*</span></label><input type="tel" class="form-control" name="telefono_celular" value="{{ old('telefono_celular') }}" required></div>
                                        <div class="col-md-4 mb-3"><label class="form-label">Teléfono Fijo</label><input type="tel" class="form-control" name="telefono_fijo" value="{{ old('telefono_fijo') }}"></div>
                                    </div>

                                    <hr>
                                    <h6 class="mt-3">Dirección</h6>
                                    
                                    {{-- DIRECCIÓN CON AUTOCOMPLETADO --}}
                                    <div class="row">
                                        <div class="col-md-3 mb-3">
                                            <label class="form-label">Código Postal <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control" name="codigo_postal" id="codigo_postal" value="{{ old('codigo_postal') }}" required>
                                        </div>
                                        <div class="col-md-5 mb-3">
                                            <label class="form-label">Colonia <span class="text-danger">*</span></label>
                                            <div id="colonia_container">
                                                <input type="text" class="form-control" name="colonia" id="colonia" value="{{ old('colonia') }}" required>
                                            </div>
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label class="form-label">Fecha Comprobante Domicilio <span class="text-danger">*</span></label>
                                            <input type="date" class="form-control" name="fecha_comprobante_domicilio" value="{{ old('fecha_comprobante_domicilio') }}" required>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6 mb-3"><label class="form-label">Municipio <span class="text-danger">*</span></label><input type="text" class="form-control" name="municipio" id="municipio" value="{{ old('municipio') }}" required></div>
                                        <div class="col-md-6 mb-3"><label class="form-label">Estado <span class="text-danger">*</span></label><input type="text" class="form-control" name="estado" id="estado" value="{{ old('estado') }}" required></div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-8 mb-3"><label class="form-label">Calle <span class="text-danger">*</span></label><input type="text" class="form-control" name="calle" value="{{ old('calle') }}" required></div>
                                        <div class="col-md-4 mb-3"><label class="form-label">Número <span class="text-danger">*</span></label><input type="text" class="form-control" name="numero" value="{{ old('numero') }}" required></div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Años en el domicilio <span class="text-danger">*</span></label>
                                            <input type="number" name="anios_domicilio" class="form-control" value="{{ old('anios_domicilio', '0') }}" min="0" required>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Tipo de Vivienda <span class="text-danger">*</span></label>
                                            <select class="form-select" name="tipo_vivienda" required>
                                                <option value="Propia" @selected(old('tipo_vivienda') == 'Propia')>Propia</option>
                                                <option value="Rentada" @selected(old('tipo_vivienda') == 'Rentada')>Rentada</option>
                                                <option value="Familiar" @selected(old('tipo_vivienda') == 'Familiar')>Familiar</option>
                                                <option value="Hipotecada" @selected(old('tipo_vivienda') == 'Hipotecada')>Hipotecada</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo">
                                    <strong>Sección 2: Datos Laborales</strong>
                                </button>
                            </h2>
                            <div id="collapseTwo" class="accordion-collapse collapse" data-bs-parent="#accordionCliente">
                                <div class="accordion-body">
                                    <div class="row">
                                        <div class="col-md-6 mb-3"><label class="form-label">Nombre del Negocio <span class="text-danger">*</span></label><input type="text" class="form-control" name="nombre_negocio" value="{{ old('nombre_negocio') }}" required></div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Giro del Negocio <span class="text-danger">*</span></label>
                                            <select class="form-select" name="giro_negocio" required>
                                                <option value="Comercio" @selected(old('giro_negocio') == 'Comercio')>Comercio</option>
                                                <option value="Servicios" @selected(old('giro_negocio') == 'Servicios')>Servicios</option>
                                                <option value="Industria" @selected(old('giro_negocio') == 'Industria')>Industria</option>
                                                <option value="Agropecuario" @selected(old('giro_negocio') == 'Agropecuario')>Agropecuario</option>
                                                <option value="Otro" @selected(old('giro_negocio') == 'Otro')>Otro</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Destino del Crédito <span class="text-danger">*</span></label>
                                            <select class="form-select" name="destino_credito" required>
                                                <option value="Capital de Trabajo" @selected(old('destino_credito') == 'Capital de Trabajo')>Capital de Trabajo</option>
                                                <option value="Activo Fijo" @selected(old('destino_credito') == 'Activo Fijo')>Activo Fijo</option>
                                                <option value="Inversión" @selected(old('destino_credito') == 'Inversión')>Inversión</option>
                                                <option value="Otro" @selected(old('destino_credito') == 'Otro')>Otro</option>
                                            </select>
                                        </div>
                                        <div class="col-md-6 mb-3"><label class="form-label">Antigüedad del Negocio (años) <span class="text-danger">*</span></label><input type="number" class="form-control" name="antiguedad_negocio" value="{{ old('antiguedad_negocio') }}" min="0" required></div>
                                    </div>
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
                                                    <option value="{{ $sucursal->id_sucursal }}" @selected(old('id_sucursal') == $sucursal->id_sucursal)>
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

    @push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const cpInput = document.getElementById('codigo_postal');
        const coloniaContainer = document.getElementById('colonia_container');
        const municipioInput = document.getElementById('municipio');
        const estadoInput = document.getElementById('estado');

        if (cpInput) {
            cpInput.addEventListener('blur', function () {
                const cp = this.value.trim();
                if (cp.length === 5 && /^\d+$/.test(cp)) {
                    
                    // --- ¡CAMBIO IMPORTANTE AQUÍ! ---
                    // Apuntamos a nuestra propia API interna en lugar de a una externa.
                    fetch(`/api/cp/${cp}`)
                    // ---------------------------------

                        .then(response => {
                            if (!response.ok) throw new Error('CP no encontrado');
                            return response.json();
                        })
                        .then(data => {
                            if (!data || data.length === 0 || data.error) {
                                 console.error('Respuesta de API inválida o CP no encontrado');
                                 return;
                            }

                            municipioInput.value = data[0].response.municipio;
                            estadoInput.value = data[0].response.estado;

                            const colonias = [...new Set(data.map(item => item.response.asentamiento))]; // Eliminar duplicados
                            
                            coloniaContainer.innerHTML = ''; 

                            if (colonias.length > 1) {
                                let label = document.createElement('label');
                                label.className = 'form-label visually-hidden';
                                label.setAttribute('for', 'colonia');
                                label.textContent = 'Colonia';
                                coloniaContainer.appendChild(label);

                                let select = document.createElement('select');
                                select.className = 'form-select';
                                select.name = 'colonia';
                                select.id = 'colonia';
                                select.required = true;

                                colonias.forEach(nombreColonia => {
                                    let option = document.createElement('option');
                                    option.value = nombreColonia;
                                    option.textContent = nombreColonia;
                                    select.appendChild(option);
                                });
                                coloniaContainer.appendChild(select);
                            } else {
                                let label = document.createElement('label');
                                label.className = 'form-label visually-hidden';
                                label.setAttribute('for', 'colonia');
                                label.textContent = 'Colonia';
                                coloniaContainer.appendChild(label);

                                let input = document.createElement('input');
                                input.type = 'text';
                                input.className = 'form-control';
                                input.name = 'colonia';
                                input.id = 'colonia';
                                input.value = colonias[0] || '';
                                input.required = true;
                                coloniaContainer.appendChild(input);
                            }
                        })
                        .catch(error => console.error('Error en la API de CP:', error));
                }
            });
        }
    });
</script>
@endpush
</x-app-layout>