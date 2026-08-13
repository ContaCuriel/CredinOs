<x-app-layout>
    <div class="container-fluid py-4">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <h5 class="mb-0 fw-bold text-dark"><i class="bi bi-person-plus-fill me-2 text-primary"></i>Registrar Nuevo Cliente</h5>
                <a href="{{ route('clientes.index') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-arrow-left me-1"></i> Regresar
                </a>
            </div>
            <div class="card-body">
                @if ($errors->any())
                    <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm" role="alert">
                        <h6 class="alert-heading fw-bold mb-1"><i class="bi bi-exclamation-diamond-fill me-2"></i>Por favor corrige los siguientes errores:</h6>
                        <ul class="mb-0 small">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                        @if (session('error'))
                    <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm mt-3" role="alert">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i> {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                <form action="{{ route('clientes.store') }}" method="POST">
                    @csrf
                    
                    <div class="accordion accordion-flush" id="accordionCliente">

                        {{-- SECCIÓN 1: DATOS PERSONALES --}}
                        <div class="accordion-item border mb-3 rounded-3 shadow-sm">
                            <h2 class="accordion-header">
                                <button class="accordion-button fw-bold text-primary bg-light rounded-3" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
                                    <i class="bi bi-person-vcard me-2 fs-5"></i> Sección 1: Datos Personales y Domicilio
                                </button>
                            </h2>
                            <div id="collapseOne" class="accordion-collapse collapse show" data-bs-parent="#accordionCliente">
                                <div class="accordion-body p-4">
                                    <div class="row">
                                        <div class="col-md-4 mb-3"><label class="form-label fw-bold">Nombre(s) <span class="text-danger">*</span></label><input type="text" class="form-control" name="nombre" value="{{ old('nombre') }}" required></div>
                                        <div class="col-md-4 mb-3"><label class="form-label fw-bold">Apellido Paterno <span class="text-danger">*</span></label><input type="text" class="form-control" name="apellido_paterno" value="{{ old('apellido_paterno') }}" required></div>
                                        <div class="col-md-4 mb-3"><label class="form-label fw-bold">Apellido Materno</label><input type="text" class="form-control" name="apellido_materno" value="{{ old('apellido_materno') }}"></div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-4 mb-3"><label class="form-label fw-bold">Fecha de Nacimiento <span class="text-danger">*</span></label><input type="date" class="form-control" name="fecha_nacimiento" value="{{ old('fecha_nacimiento') }}" required></div>
                                        <div class="col-md-4 mb-3"><label class="form-label fw-bold">CURP</label><input type="text" class="form-control text-uppercase" name="curp" value="{{ old('curp') }}"></div>
                                        <div class="col-md-4 mb-3">
                                            <label class="form-label fw-bold">Año de Vencimiento INE <span class="text-danger">*</span></label>
                                            <input type="number" class="form-control" name="vencimiento_ine" placeholder="YYYY" min="{{ date('Y') }}" value="{{ old('vencimiento_ine') }}" required>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-4 mb-3">
                                            <label class="form-label fw-bold">Estado Civil <span class="text-danger">*</span></label>
                                            <select class="form-select" name="estado_civil" required>
                                                <option value="Soltero(a)" @selected(old('estado_civil') == 'Soltero(a)')>Soltero(a)</option>
                                                <option value="Casado(a)" @selected(old('estado_civil') == 'Casado(a)')>Casado(a)</option>
                                                <option value="Divorciado(a)" @selected(old('estado_civil') == 'Divorciado(a)')>Divorciado(a)</option>
                                                <option value="Viudo(a)" @selected(old('estado_civil') == 'Viudo(a)')>Viudo(a)</option>
                                                <option value="Unión Libre" @selected(old('estado_civil') == 'Unión Libre')>Unión Libre</option>
                                            </select>
                                        </div>
                                        <div class="col-md-4 mb-3"><label class="form-label fw-bold">Teléfono Celular <span class="text-danger">*</span></label><input type="tel" class="form-control" name="telefono_celular" value="{{ old('telefono_celular') }}" required></div>
                                        <div class="col-md-4 mb-3"><label class="form-label fw-bold">Teléfono Fijo</label><input type="tel" class="form-control" name="telefono_fijo" value="{{ old('telefono_fijo') }}"></div>
                                    </div>

                                    <hr class="my-4">
                                    <h6 class="fw-bold text-dark mb-3"><i class="bi bi-house-door-fill me-2 text-primary"></i>Dirección Particular</h6>
                                    
                                    <div class="row">
                                        <div class="col-md-3 mb-3">
                                            <label class="form-label fw-bold">Código Postal <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control" name="codigo_postal" id="codigo_postal" value="{{ old('codigo_postal') }}" required>
                                        </div>
                                        <div class="col-md-5 mb-3">
                                            <label class="form-label fw-bold">Colonia <span class="text-danger">*</span></label>
                                            <div id="colonia_container">
                                                <input type="text" class="form-control" name="colonia" id="colonia" value="{{ old('colonia') }}" required>
                                            </div>
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label class="form-label fw-bold">Fecha Comprobante Domicilio <span class="text-danger">*</span></label>
                                            <input type="date" class="form-control" name="fecha_comprobante_domicilio" value="{{ old('fecha_comprobante_domicilio') }}" required>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6 mb-3"><label class="form-label fw-bold">Municipio <span class="text-danger">*</span></label><input type="text" class="form-control" name="municipio" id="municipio" value="{{ old('municipio') }}" required></div>
                                        <div class="col-md-6 mb-3"><label class="form-label fw-bold">Estado <span class="text-danger">*</span></label><input type="text" class="form-control" name="estado" id="estado" value="{{ old('estado') }}" required></div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-8 mb-3"><label class="form-label fw-bold">Calle <span class="text-danger">*</span></label><input type="text" class="form-control" name="calle" value="{{ old('calle') }}" required></div>
                                        <div class="col-md-4 mb-3"><label class="form-label fw-bold">Número <span class="text-danger">*</span></label><input type="text" class="form-control" name="numero" value="{{ old('numero') }}" required></div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label fw-bold">Años en el domicilio <span class="text-danger">*</span></label>
                                            <input type="number" name="anios_domicilio" class="form-control" value="{{ old('anios_domicilio', '0') }}" min="0" required>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label fw-bold">Tipo de Vivienda <span class="text-danger">*</span></label>
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

                        {{-- SECCIÓN 2: DATOS LABORALES Y FINANCIEROS --}}
                        <div class="accordion-item border mb-3 rounded-3 shadow-sm">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed fw-bold text-primary bg-light rounded-3" type="button" data-bs-toggle="collapse" data-bs-target="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo">
                                    <i class="bi bi-briefcase-fill me-2 fs-5"></i> Sección 2: Datos Laborales y Finanzas
                                </button>
                            </h2>
                            <div id="collapseTwo" class="accordion-collapse collapse" data-bs-parent="#accordionCliente">
                                <div class="accordion-body p-4">
                                    <div class="row">
                                        <div class="col-md-6 mb-3"><label class="form-label fw-bold">Nombre del Negocio / Empresa <span class="text-danger">*</span></label><input type="text" class="form-control" name="nombre_negocio" value="{{ old('nombre_negocio') }}" required></div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label fw-bold">Giro del Negocio <span class="text-danger">*</span></label>
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
                                            <label class="form-label fw-bold">Destino del Crédito <span class="text-danger">*</span></label>
                                            <select class="form-select" name="destino_credito" required>
                                                <option value="Capital de Trabajo" @selected(old('destino_credito') == 'Capital de Trabajo')>Capital de Trabajo</option>
                                                <option value="Activo Fijo" @selected(old('destino_credito') == 'Activo Fijo')>Activo Fijo</option>
                                                <option value="Inversión" @selected(old('destino_credito') == 'Inversión')>Inversión</option>
                                                <option value="Otro" @selected(old('destino_credito') == 'Otro')>Otro</option>
                                            </select>
                                        </div>
                                        <div class="col-md-6 mb-3"><label class="form-label fw-bold">Antigüedad del Negocio (años) <span class="text-danger">*</span></label><input type="number" class="form-control" name="antiguedad_negocio" value="{{ old('antiguedad_negocio') }}" min="0" required></div>
                                    </div>

                                    <hr class="my-4">
                                    <h6 class="fw-bold text-dark mb-3"><i class="bi bi-cash-stack me-2 text-success"></i>Capacidad de Pago Mensual</h6>
                                    <div class="row bg-light p-3 rounded-3 mb-3 border">
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label fw-bold">Ingresos Mensuales ($) <span class="text-danger">*</span></label>
                                            <div class="input-group">
                                                <span class="input-group-text bg-white fw-bold">$</span>
                                                <input type="number" step="0.01" min="0" class="form-control" name="ingresos_mensuales" value="{{ old('ingresos_mensuales', '0.00') }}" required>
                                            </div>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label fw-bold">Gastos Mensuales ($) <span class="text-danger">*</span></label>
                                            <div class="input-group">
                                                <span class="input-group-text bg-white fw-bold">$</span>
                                                <input type="number" step="0.01" min="0" class="form-control" name="gastos_mensuales" value="{{ old('gastos_mensuales', '0.00') }}" required>
                                            </div>
                                        </div>
                                    </div>

                                    <hr class="my-4">
                                    <h6 class="fw-bold text-dark mb-3"><i class="bi bi-geo-alt-fill me-2 text-primary"></i>Domicilio del Negocio / Laboral</h6>
                                    
                                    <div class="form-check form-switch mb-3">
                                        <input class="form-check-input" type="checkbox" role="switch" id="mismo_domicilio" name="mismo_domicilio_laboral" value="1" {{ old('mismo_domicilio_laboral') ? 'checked' : '' }}>
                                        <label class="form-check-label fw-bold text-primary" for="mismo_domicilio">
                                            El domicilio del negocio es el mismo que el domicilio particular
                                        </label>
                                    </div>

                                    <div id="seccion_domicilio_negocio">
                                        <div class="row">
                                            <div class="col-md-3 mb-3"><label class="form-label fw-bold">Código Postal</label><input type="text" class="form-control" name="codigo_postal_negocio" value="{{ old('codigo_postal_negocio') }}"></div>
                                            <div class="col-md-5 mb-3"><label class="form-label fw-bold">Colonia</label><input type="text" class="form-control" name="colonia_negocio" value="{{ old('colonia_negocio') }}"></div>
                                            <div class="col-md-4 mb-3"><label class="form-label fw-bold">Municipio</label><input type="text" class="form-control" name="municipio_negocio" value="{{ old('municipio_negocio') }}"></div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-4 mb-3"><label class="form-label fw-bold">Estado</label><input type="text" class="form-control" name="estado_negocio" value="{{ old('estado_negocio') }}"></div>
                                            <div class="col-md-6 mb-3"><label class="form-label fw-bold">Calle</label><input type="text" class="form-control" name="calle_negocio" value="{{ old('calle_negocio') }}"></div>
                                            <div class="col-md-2 mb-3"><label class="form-label fw-bold">Número</label><input type="text" class="form-control" name="numero_negocio" value="{{ old('numero_negocio') }}"></div>
                                        </div>
                                    </div>

                                </div>
                            </div>
                        </div>

                        {{-- SECCIÓN 3: REFERENCIAS --}}
                        <div class="accordion-item border mb-3 rounded-3 shadow-sm">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed fw-bold text-primary bg-light rounded-3" type="button" data-bs-toggle="collapse" data-bs-target="#collapseThree" aria-expanded="false" aria-controls="collapseThree">
                                    <i class="bi bi-people-fill me-2 fs-5"></i> Sección 3: Referencias Personales (2 Requeridas)
                                </button>
                            </h2>
                            <div id="collapseThree" class="accordion-collapse collapse" data-bs-parent="#accordionCliente">
                                <div class="accordion-body p-4">
                                    @for ($i = 0; $i < 2; $i++)
                                        <div class="p-3 bg-light rounded-3 mb-3 border">
                                            <h6 class="fw-bold text-dark mb-3"><i class="bi bi-person-badge-fill me-2 text-secondary"></i>Referencia {{ $i + 1 }}</h6>
                                            <div class="row">
                                                <div class="col-md-4 mb-3"><label class="form-label fw-bold">Nombre Completo <span class="text-danger">*</span></label><input type="text" class="form-control" name="referencias[{{ $i }}][nombre_referencia]" value="{{ old('referencias.'.$i.'.nombre_referencia') }}" required></div>
                                                <div class="col-md-4 mb-3"><label class="form-label fw-bold">Parentesco <span class="text-danger">*</span></label><input type="text" class="form-control" name="referencias[{{ $i }}][parentesco]" value="{{ old('referencias.'.$i.'.parentesco') }}" required></div>
                                                <div class="col-md-4 mb-3"><label class="form-label fw-bold">Teléfono <span class="text-danger">*</span></label><input type="tel" class="form-control" name="referencias[{{ $i }}][telefono]" value="{{ old('referencias.'.$i.'.telefono') }}" required></div>
                                            </div>
                                        </div>
                                    @endfor
                                </div>
                            </div>
                        </div>

                    </div>

                    <div class="text-end mt-4">
                        <a href="{{ route('clientes.index') }}" class="btn btn-light me-2 border">Cancelar</a>
                        <button type="submit" class="btn btn-primary px-4 shadow-sm"><i class="bi bi-floppy-fill me-1"></i> Guardar Cliente</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
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
                toggleDomicilioNegocio();
            }

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
                                if (!data || data.length === 0 || data.error) return;

                                municipioInput.value = data[0].response.municipio;
                                estadoInput.value = data[0].response.estado;

                                const colonias = [...new Set(data.map(item => item.response.asentamiento))];
                                coloniaContainer.innerHTML = ''; 

                                if (colonias.length > 1) {
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
                            .catch(error => console.error('Error en API de CP:', error));
                    }
                });
            }
        });
    </script>
    @endpush
</x-app-layout>