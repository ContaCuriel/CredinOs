<x-app-layout>
    <div class="container-fluid py-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Editar Cliente: {{ $cliente->nombre_completo }}</h5>
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

                <form action="{{ route('clientes.update', $cliente->id_cliente) }}" method="POST">
                    @csrf
                    @method('PUT')
                    
                    <div class="accordion" id="accordionCliente">

                        {{-- SECCIÓN 1: DATOS PERSONALES --}}
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
                                        <div class="col-md-4 mb-3"><label class="form-label">Nombre(s) <span class="text-danger">*</span></label><input type="text" class="form-control" name="nombre" value="{{ old('nombre', $cliente->nombre) }}" required></div>
                                        <div class="col-md-4 mb-3"><label class="form-label">Apellido Paterno <span class="text-danger">*</span></label><input type="text" class="form-control" name="apellido_paterno" value="{{ old('apellido_paterno', $cliente->apellido_paterno) }}" required></div>
                                        <div class="col-md-4 mb-3"><label class="form-label">Apellido Materno</label><input type="text" class="form-control" name="apellido_materno" value="{{ old('apellido_materno', $cliente->apellido_materno) }}"></div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-4 mb-3"><label class="form-label">Fecha de Nacimiento <span class="text-danger">*</span></label><input type="date" class="form-control" name="fecha_nacimiento" value="{{ old('fecha_nacimiento', $cliente->fecha_nacimiento?->format('Y-m-d')) }}" required></div>
                                        <div class="col-md-4 mb-3"><label class="form-label">CURP</label><input type="text" class="form-control text-uppercase" name="curp" value="{{ old('curp', $cliente->curp) }}"></div>
                                        <div class="col-md-4 mb-3">
                                            <label class="form-label">Año de Vencimiento del INE <span class="text-danger">*</span></label>
                                            <input type="number" class="form-control" name="vencimiento_ine" placeholder="YYYY" min="{{ date('Y') }}" value="{{ old('vencimiento_ine', $cliente->vencimiento_ine) }}" required>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-4 mb-3">
                                            <label class="form-label">Estado Civil <span class="text-danger">*</span></label>
                                            <select class="form-select" name="estado_civil" required>
                                                <option value="Soltero(a)" @selected(old('estado_civil', $cliente->estado_civil) == 'Soltero(a)')>Soltero(a)</option>
                                                <option value="Casado(a)" @selected(old('estado_civil', $cliente->estado_civil) == 'Casado(a)')>Casado(a)</option>
                                                <option value="Divorciado(a)" @selected(old('estado_civil', $cliente->estado_civil) == 'Divorciado(a)')>Divorciado(a)</option>
                                                <option value="Viudo(a)" @selected(old('estado_civil', $cliente->estado_civil) == 'Viudo(a)')>Viudo(a)</option>
                                                <option value="Unión Libre" @selected(old('estado_civil', $cliente->estado_civil) == 'Unión Libre')>Unión Libre</option>
                                            </select>
                                        </div>
                                        <div class="col-md-4 mb-3"><label class="form-label">Teléfono Celular <span class="text-danger">*</span></label><input type="tel" class="form-control" name="telefono_celular" value="{{ old('telefono_celular', $cliente->telefono_celular) }}" required></div>
                                        <div class="col-md-4 mb-3"><label class="form-label">Teléfono Fijo</label><input type="tel" class="form-control" name="telefono_fijo" value="{{ old('telefono_fijo', $cliente->telefono_fijo) }}"></div>
                                    </div>

                                    <hr>
                                    <h6 class="mt-3">Dirección Particular</h6>
                                    
                                    {{-- DIRECCIÓN CON AUTOCOMPLETADO --}}
                                    <div class="row">
                                        <div class="col-md-3 mb-3">
                                            <label class="form-label">Código Postal <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control" name="codigo_postal" id="codigo_postal" value="{{ old('codigo_postal', $cliente->codigo_postal) }}" required>
                                        </div>
                                        <div class="col-md-5 mb-3">
                                            <label class="form-label">Colonia <span class="text-danger">*</span></label>
                                            <div id="colonia_container">
                                                <input type="text" class="form-control" name="colonia" id="colonia" value="{{ old('colonia', $cliente->colonia) }}" required>
                                            </div>
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label class="form-label">Fecha Comprobante Domicilio <span class="text-danger">*</span></label>
                                            <input type="date" class="form-control" name="fecha_comprobante_domicilio" value="{{ old('fecha_comprobante_domicilio', $cliente->fecha_comprobante_domicilio?->format('Y-m-d')) }}" required>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6 mb-3"><label class="form-label">Municipio <span class="text-danger">*</span></label><input type="text" class="form-control" name="municipio" id="municipio" value="{{ old('municipio', $cliente->municipio) }}" required></div>
                                        <div class="col-md-6 mb-3"><label class="form-label">Estado <span class="text-danger">*</span></label><input type="text" class="form-control" name="estado" id="estado" value="{{ old('estado', $cliente->estado) }}" required></div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-8 mb-3"><label class="form-label">Calle <span class="text-danger">*</span></label><input type="text" class="form-control" name="calle" value="{{ old('calle', $cliente->calle) }}" required></div>
                                        <div class="col-md-4 mb-3"><label class="form-label">Número <span class="text-danger">*</span></label><input type="text" class="form-control" name="numero" value="{{ old('numero', $cliente->numero) }}" required></div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Años en el domicilio <span class="text-danger">*</span></label>
                                            <input type="number" name="anios_domicilio" class="form-control" value="{{ old('anios_domicilio', $cliente->anios_domicilio) }}" min="0" required>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Tipo de Vivienda <span class="text-danger">*</span></label>
                                            <select class="form-select" name="tipo_vivienda" required>
                                                <option value="Propia" @selected(old('tipo_vivienda', $cliente->tipo_vivienda) == 'Propia')>Propia</option>
                                                <option value="Rentada" @selected(old('tipo_vivienda', $cliente->tipo_vivienda) == 'Rentada')>Rentada</option>
                                                <option value="Familiar" @selected(old('tipo_vivienda', $cliente->tipo_vivienda) == 'Familiar')>Familiar</option>
                                                <option value="Hipotecada" @selected(old('tipo_vivienda', $cliente->tipo_vivienda) == 'Hipotecada')>Hipotecada</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- SECCIÓN 2: DATOS LABORALES Y FINANCIEROS --}}
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo">
                                    <strong>Sección 2: Datos Laborales y Financieros</strong>
                                </button>
                            </h2>
                            <div id="collapseTwo" class="accordion-collapse collapse" data-bs-parent="#accordionCliente">
                                <div class="accordion-body">
                                    <div class="row">
                                        <div class="col-md-6 mb-3"><label class="form-label">Nombre del Negocio / Empresa <span class="text-danger">*</span></label><input type="text" class="form-control" name="nombre_negocio" value="{{ old('nombre_negocio', $cliente->nombre_negocio) }}" required></div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Giro del Negocio <span class="text-danger">*</span></label>
                                            <select class="form-select" name="giro_negocio" required>
                                                <option value="Comercio" @selected(old('giro_negocio', $cliente->giro_negocio) == 'Comercio')>Comercio</option>
                                                <option value="Servicios" @selected(old('giro_negocio', $cliente->giro_negocio) == 'Servicios')>Servicios</option>
                                                <option value="Industria" @selected(old('giro_negocio', $cliente->giro_negocio) == 'Industria')>Industria</option>
                                                <option value="Agropecuario" @selected(old('giro_negocio', $cliente->giro_negocio) == 'Agropecuario')>Agropecuario</option>
                                                <option value="Otro" @selected(old('giro_negocio', $cliente->giro_negocio) == 'Otro')>Otro</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Destino del Crédito <span class="text-danger">*</span></label>
                                            <select class="form-select" name="destino_credito" required>
                                                <option value="Capital de Trabajo" @selected(old('destino_credito', $cliente->destino_credito) == 'Capital de Trabajo')>Capital de Trabajo</option>
                                                <option value="Activo Fijo" @selected(old('destino_credito', $cliente->destino_credito) == 'Activo Fijo')>Activo Fijo</option>
                                                <option value="Inversión" @selected(old('destino_credito', $cliente->destino_credito) == 'Inversión')>Inversión</option>
                                                <option value="Otro" @selected(old('destino_credito', $cliente->destino_credito) == 'Otro')>Otro</option>
                                            </select>
                                        </div>
                                        <div class="col-md-6 mb-3"><label class="form-label">Antigüedad del Negocio (años) <span class="text-danger">*</span></label><input type="number" class="form-control" name="antiguedad_negocio" value="{{ old('antiguedad_negocio', $cliente->antiguedad_negocio) }}" min="0" required></div>
                                    </div>

                                    <hr>
                                    <h6 class="mt-3">Capacidad de Pago</h6>
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Ingresos Mensuales ($) <span class="text-danger">*</span></label>
                                            <div class="input-group">
                                                <span class="input-group-text">$</span>
                                                <input type="number" step="0.01" min="0" class="form-control" name="ingresos_mensuales" value="{{ old('ingresos_mensuales', $cliente->ingresos_mensuales ?? '0.00') }}" required>
                                            </div>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Gastos Mensuales ($) <span class="text-danger">*</span></label>
                                            <div class="input-group">
                                                <span class="input-group-text">$</span>
                                                <input type="number" step="0.01" min="0" class="form-control" name="gastos_mensuales" value="{{ old('gastos_mensuales', $cliente->gastos_mensuales ?? '0.00') }}" required>
                                            </div>
                                        </div>
                                    </div>

                                    <hr>
                                    <h6 class="mt-3">Domicilio del Negocio / Laboral</h6>
                                    
                                    {{-- CHECKBOX PARA OCULTAR EL DOMICILIO LABORAL --}}
                                    <div class="form-check mb-3">
                                        <input class="form-check-input" type="checkbox" id="mismo_domicilio" name="mismo_domicilio_laboral" value="1" {{ old('mismo_domicilio_laboral', $cliente->mismo_domicilio_laboral) ? 'checked' : '' }}>
                                        <label class="form-check-label fw-bold text-primary" for="mismo_domicilio">
                                            El domicilio del negocio/trabajo es el mismo que el particular
                                        </label>
                                    </div>

                                    {{-- CONTENEDOR DE DOMICILIO LABORAL --}}
                                    <div id="seccion_domicilio_negocio">
                                        <div class="row">
                                            <div class="col-md-3 mb-3">
                                                <label class="form-label">Código Postal</label>
                                                <input type="text" class="form-control" name="codigo_postal_negocio" value="{{ old('codigo_postal_negocio', $cliente->codigo_postal_negocio) }}">
                                            </div>
                                            <div class="col-md-5 mb-3">
                                                <label class="form-label">Colonia</label>
                                                <input type="text" class="form-control" name="colonia_negocio" value="{{ old('colonia_negocio', $cliente->colonia_negocio) }}">
                                            </div>
                                            <div class="col-md-4 mb-3">
                                                <label class="form-label">Municipio</label>
                                                <input type="text" class="form-control" name="municipio_negocio" value="{{ old('municipio_negocio', $cliente->municipio_negocio) }}">
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-4 mb-3">
                                                <label class="form-label">Estado</label>
                                                <input type="text" class="form-control" name="estado_negocio" value="{{ old('estado_negocio', $cliente->estado_negocio) }}">
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label class="form-label">Calle</label>
                                                <input type="text" class="form-control" name="calle_negocio" value="{{ old('calle_negocio', $cliente->calle_negocio) }}">
                                            </div>
                                            <div class="col-md-2 mb-3">
                                                <label class="form-label">Número</label>
                                                <input type="text" class="form-control" name="numero_negocio" value="{{ old('numero_negocio', $cliente->numero_negocio) }}">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- SECCIÓN 3: REFERENCIAS --}}
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
                                            <div class="col-md-4 mb-3"><label class="form-label">Nombre Completo <span class="text-danger">*</span></label><input type="text" class="form-control" name="referencias[{{ $i }}][nombre_referencia]" value="{{ old('referencias.'.$i.'.nombre_referencia', $cliente->referencias[$i]->nombre_referencia ?? '') }}" required></div>
                                            <div class="col-md-4 mb-3"><label class="form-label">Parentesco <span class="text-danger">*</span></label><input type="text" class="form-control" name="referencias[{{ $i }}][parentesco]" value="{{ old('referencias.'.$i.'.parentesco', $cliente->referencias[$i]->parentesco ?? '') }}" required></div>
                                            <div class="col-md-4 mb-3"><label class="form-label">Teléfono <span class="text-danger">*</span></label><input type="tel" class="form-control" name="referencias[{{ $i }}][telefono]" value="{{ old('referencias.'.$i.'.telefono', $cliente->referencias[$i]->telefono ?? '') }}" required></div>
                                        </div>
                                    @endfor
                                </div>
                            </div>
                        </div>

                    </div>

                    <div class="text-end mt-4">
                        <a href="{{ route('clientes.index') }}" class="btn btn-secondary">Cancelar</a>
                        <button type="submit" class="btn btn-primary">Actualizar Cliente</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // LÓGICA PARA OCULTAR/MOSTRAR DOMICILIO LABORAL
            const checkboxMismoDomicilio = document.getElementById('mismo_domicilio');
            const seccionDomicilioNegocio = document.getElementById('seccion_domicilio_negocio');

            function toggleDomicilioNegocio() {
                if (checkboxMismoDomicilio.checked) {
                    seccionDomicilioNegocio.style.display = 'none';
                } else {
                    seccionDomicilioNegocio.style.display = 'block';
                }
            }

            if (checkboxMismoDomicilio) {
                checkboxMismoDomicilio.addEventListener('change', toggleDomicilioNegocio);
                toggleDomicilioNegocio(); // Ejecutar al cargar la página para respetar la BD
            }

            // LÓGICA DE CÓDIGO POSTAL
            const cpInput = document.getElementById('codigo_postal');
            const coloniaContainer = document.getElementById('colonia_container');
            const municipioInput = document.getElementById('municipio');
            const estadoInput = document.getElementById('estado');

            if (cpInput) {
                cpInput.addEventListener('blur', function () {
                    const cp = this.value.trim();
                    if (cp.length === 5 && /^\d+$/.test(cp)) {
                        
                        fetch(`/api/cp/${cp}`)
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