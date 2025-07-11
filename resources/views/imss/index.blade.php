<x-app-layout>
    <div class="container-fluid py-4">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Gestión de Estado IMSS de Empleados</h5>
                {{-- Podríamos añadir un botón "Registrar Alta Masiva" o similar en el futuro si es necesario --}}
            </div>
            <div class="card-body">
                @if (session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif
                @if (session('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                {{-- Filtros --}}
                <form method="GET" action="{{ route('imss.index') }}" class="mb-4">
                    <div class="row align-items-end g-2">
                        <div class="col-md-3">
                            <label for="search_nombre" class="form-label mb-1">Buscar por Nombre:</label>
                            <input type="text" name="search_nombre" id="search_nombre" class="form-control form-control-sm" 
                                   value="{{ request('search_nombre') }}" placeholder="Nombre del empleado...">
                        </div>
                        <div class="col-md-3">
                            <label for="id_sucursal_filter" class="form-label mb-1">Filtrar por Sucursal:</label>
                            <select name="id_sucursal_filter" id="id_sucursal_filter" class="form-select form-select-sm">
                                <option value="">Todas las Sucursales</option>
                                @if(isset($sucursales))
                                    @foreach ($sucursales as $sucursal)
                                        <option value="{{ $sucursal->id_sucursal }}" {{ request('id_sucursal_filter') == $sucursal->id_sucursal ? 'selected' : '' }}>
                                            {{ $sucursal->nombre_sucursal }}
                                        </option>
                                    @endforeach
                                @endif
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="estado_imss_filter" class="form-label mb-1">Estado IMSS:</label>
                            <select name="estado_imss_filter" id="estado_imss_filter" class="form-select form-select-sm">
                                @if(isset($estados_imss_disponibles))
                                    @foreach ($estados_imss_disponibles as $valor => $texto)
                                        <option value="{{ $valor }}" {{ request('estado_imss_filter', 'Alta') == $valor ? 'selected' : '' }}>
                                            {{ $texto }}
                                        </option>
                                    @endforeach
                                @endif
                            </select>
                        </div>
                        <div class="col-md-2 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary btn-sm w-100">Buscar/Filtrar</button>
                        </div>
                        <div class="col-md-1 d-flex align-items-end">
                            @if(request('search_nombre') || request('id_sucursal_filter') || request('estado_imss_filter') != 'Alta')
                                <a href="{{ route('imss.index') }}" class="btn btn-secondary btn-sm w-100" title="Limpiar Filtros">
                                    <i class="bi bi-eraser"></i>
                                </a>
                            @endif
                        </div>
                    </div>
                </form>
                {{-- Fin Filtros --}}

                <div class="table-responsive">
                    <table class="table table-striped table-hover table-sm">
                        <thead class="table-light">
                            <tr>
                                <th>Empleado</th>
                                <th>Puesto</th>
                                <th>Sucursal</th>
                                <th>Antigüedad</th>
                                <th class="text-center">Estado IMSS</th>
                                <th>Patrón (IMSS)</th>
                                <th class="text-center">Fecha Alta IMSS</th>
                                <th class="text-center">Fecha Baja IMSS</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($empleados as $empleado)
                                <tr>
                                    <td>{{ $empleado->nombre_completo }}</td>
                                    <td>{{ $empleado->puesto ? $empleado->puesto->nombre_puesto : 'N/A' }}</td>
                                    <td>{{ $empleado->sucursal ? $empleado->sucursal->nombre_sucursal : 'N/A' }}</td>
                                    <td>
                                        @if ($empleado->fecha_ingreso)
                                            {{ \Carbon\Carbon::parse($empleado->fecha_ingreso)->diffForHumans(null, true, false, 2) }}
                                        @else
                                            N/A
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        @if ($empleado->estado_imss == 'Alta')
                                            <span class="badge bg-success">Alta</span>
                                        @elseif ($empleado->estado_imss == 'Baja')
                                            <span class="badge bg-danger">Baja</span>
                                        @else
                                            <span class="badge bg-secondary">{{ $empleado->estado_imss ?: 'No Registrado' }}</span>
                                        @endif
                                    </td>
                                    <td>{{ $empleado->patronImss ? $empleado->patronImss->nombre_comercial : 'N/A' }}</td>
                                    <td class="text-center">{{ $empleado->fecha_alta_imss ? $empleado->fecha_alta_imss->format('d/m/Y') : 'N/A' }}</td>
                                    <td class="text-center">{{ $empleado->fecha_baja_imss ? $empleado->fecha_baja_imss->format('d/m/Y') : 'N/A' }}</td>
                                    <td>
                                        @if (!$empleado->estado_imss || $empleado->estado_imss != 'Alta')
                                            <button type="button" class="btn btn-sm btn-success btn-registrar-alta-imss"
    data-bs-toggle="modal" data-bs-target="#modalAltaImss"
    data-id_empleado="{{ $empleado->id_empleado }}"
    data-nombre_empleado="{{ $empleado->nombre_completo }}"
    data-fecha_alta_actual="{{ $empleado->fecha_alta_imss ? $empleado->fecha_alta_imss->toDateString() : '' }}"
    data-id_patron_imss_actual="{{ $empleado->id_patron_imss }}"
    data-sdi_actual="{{ $empleado->sdi ?? '' }}"
    title="Registrar/Actualizar Alta IMSS">
    <i class="bi bi-shield-plus"></i> Alta
</button>
                                        @endif
                                        @if ($empleado->estado_imss == 'Alta')
                                            {{-- Dentro de la celda de Acciones, si el empleado está de Alta en IMSS --}}
@if ($empleado->estado_imss == 'Alta' && $empleado->fecha_alta_imss && $empleado->id_patron_imss)
    <a href="{{ route('imss.acuseAltaPdf', $empleado->id_empleado) }}" class="btn btn-sm btn-outline-primary" target="_blank" title="Generar Acuse de Alta IMSS">
        <i class="bi bi-file-earmark-pdf"></i> 
    </a>
@else
    <button type="button" class="btn btn-sm btn-outline-primary" disabled title="Empleado no dado de alta en IMSS o falta información">
        <i class="bi bi-file-earmark-pdf"></i> 
    </button>
@endif
<a href="{{ route('imss.cartaPatronalPdf', $empleado->id_empleado) }}" class="btn btn-sm btn-outline-info" target="_blank" title="Generar Carta Patronal">
            <i class="bi bi-file-text"></i> 
        </a>



                                            <button type="button" class="btn btn-sm btn-danger btn-registrar-baja-imss"
            data-bs-toggle="modal" data-bs-target="#modalBajaImss"
            data-id_empleado="{{ $empleado->id_empleado }}"
            data-nombre_empleado="{{ $empleado->nombre_completo }}"
            data-fecha_alta_imss="{{ $empleado->fecha_alta_imss ? $empleado->fecha_alta_imss->toDateString() : '' }}" {{-- Para validación --}}
            title="Registrar Baja IMSS">
        <i class="bi bi-shield-minus"></i> 
    </button>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="text-center">No hay empleados que coincidan con los filtros seleccionados.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="mt-3">
                    {{ $empleados->appends(request()->query())->links() }}
                </div>
            </div>
        </div>
    </div>

    {{-- Aquí irán los Modales para Registrar Alta y Registrar Baja IMSS más adelante --}}
{{-- Modal para Registrar/Actualizar Alta IMSS --}}
<div class="modal fade" id="modalAltaImss" tabindex="-1" aria-labelledby="modalAltaImssLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalAltaImssLabel">Registrar/Actualizar Alta IMSS</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="formAltaImss" method="POST" action=""> {{-- La action se pondrá con JS --}}
                @csrf
                {{-- Usaremos POST, el controlador manejará si es crear o actualizar datos del empleado --}}
                <div class="modal-body">
                    <p>Empleado: <strong id="nombreEmpleadoAltaImss"></strong></p>
                   

                     {{-- Campos ocultos para mantener los filtros al redirigir --}}
        <input type="hidden" name="id_sucursal_seleccionada" value="{{ request('id_sucursal_seleccionada') }}">
        <input type="hidden" name="search_nombre" value="{{ request('search_nombre') }}">
        <input type="hidden" name="estado_imss_filter" value="{{ request('estado_imss_filter', 'Alta') }}">




                    <div class="mb-3">
                        <label for="id_patron_imss_modal" class="form-label">Patrón de Alta IMSS <span class="text-danger">*</span></label>
                        <select class="form-select form-select-sm @error('id_patron_imss') is-invalid @enderror" id="id_patron_imss_modal" name="id_patron_imss" required>
                            <option value="">Seleccione un patrón...</option>
                            @if(isset($patrones))
                                @foreach ($patrones as $patron)
                                    <option value="{{ $patron->id_patron }}">{{ $patron->razon_social }} ({{ $patron->rfc }})</option>
                                @endforeach
                            @endif
                        </select>
                       <div class="invalid-feedback"></div>
                    </div>
<div class="mb-3">
    <label for="sdi_modal" class="form-label">Salario Diario Integrado (SDI) <span class="text-danger">*</span></label>
    <div class="input-group input-group-sm">
        <span class="input-group-text">$</span>
        <input type="number" step="0.01" min="0" class="form-control @error('sdi') is-invalid @enderror" id="sdi_modal" name="sdi" required placeholder="Ej: 250.50">
    </div>
<div class="invalid-feedback"></div>
</div>


                    <div class="mb-3">
                        <label for="fecha_alta_imss_modal" class="form-label">Fecha de Alta IMSS <span class="text-danger">*</span></label>
                        <input type="date" class="form-control form-control-sm @error('fecha_alta_imss') is-invalid @enderror" id="fecha_alta_imss_modal" name="fecha_alta_imss" required>
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Guardar Alta IMSS</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Modal para Registrar Baja IMSS --}}
<div class="modal fade" id="modalBajaImss" tabindex="-1" aria-labelledby="modalBajaImssLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalBajaImssLabel">Registrar Baja IMSS</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            
            <form id="formBajaImss" method="POST" action="">
    @csrf
    <div class="modal-body">
        <p>Empleado: <strong id="nombreEmpleadoBajaImss"></strong></p>
        <input type="hidden" name="id_empleado_baja_imss" id="id_empleado_baja_imss_modal"> {{-- Este ID lo usa el JS para poner la action --}}
        
        {{-- Campos ocultos para mantener los filtros al redirigir --}}
        <input type="hidden" name="id_sucursal_seleccionada" value="{{ request('id_sucursal_seleccionada') }}">
        <input type="hidden" name="search_nombre" value="{{ request('search_nombre') }}">
        <input type="hidden" name="estado_imss_filter" value="{{ request('estado_imss_filter', 'todos') }}">

        <div class="mb-3">
            <label for="fecha_baja_imss_modal" class="form-label">Fecha de Baja IMSS <span class="text-danger">*</span></label>
            <input type="date" class="form-control form-control-sm @error('fecha_baja_imss') is-invalid @enderror" id="fecha_baja_imss_modal" name="fecha_baja_imss" required>
            @error('fecha_baja_imss') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>
    </div>
    <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
        <button type="submit" class="btn btn-danger">Confirmar Baja IMSS</button>
    </div>
</form>
        </div>
    </div>
</div>


<script>
document.addEventListener('DOMContentLoaded', function () {
    // =====> SCRIPT PARA MODAL ALTA IMSS (VERSIÓN AJAX) <=====
    const modalAltaImss = document.getElementById('modalAltaImss');
    if (modalAltaImss) {
        const formAltaImss = document.getElementById('formAltaImss');

        // Configura el modal con los datos del empleado cuando se abre
        modalAltaImss.addEventListener('show.bs.modal', function(event) {
            const button = event.relatedTarget;
            const idEmpleado = button.dataset.id_empleado;
            
            // Limpia los errores de validación anteriores
            formAltaImss.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
            formAltaImss.querySelectorAll('.invalid-feedback').forEach(el => el.textContent = '');
            
            // Rellena los campos del modal con los datos del botón
            document.getElementById('nombreEmpleadoAltaImss').textContent = button.dataset.nombre_empleado;
            document.getElementById('id_patron_imss_modal').value = button.dataset.id_patron_imss_actual || "";
            document.getElementById('sdi_modal').value = button.dataset.sdi_actual || "";
            document.getElementById('fecha_alta_imss_modal').value = button.dataset.fecha_alta_actual || new Date().toISOString().slice(0, 10);
            
            // Construye la URL de acción para el formulario
            let actionUrl = "{{ route('imss.registrarAlta', ['empleado' => ':id_empleado']) }}";
            formAltaImss.action = actionUrl.replace(':id_empleado', idEmpleado);
        });

        // Intercepta el evento de envío del formulario para usar AJAX
        formAltaImss.addEventListener('submit', function (event) {
            event.preventDefault(); // ¡Evita que el formulario recargue la página!

            const submitButton = formAltaImss.querySelector('button[type="submit"]');
            const originalButtonText = submitButton.innerHTML;
            submitButton.disabled = true;
            submitButton.innerHTML = `<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Guardando...`;

            const formData = new FormData(formAltaImss);
            const actionUrl = formAltaImss.action;

            fetch(actionUrl, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                }
            })
            .then(response => response.json().then(data => ({ status: response.status, body: data })))
            .then(({ status, body }) => {
                if (status === 422) { // 422: Error de validación del servidor
                    // Limpia errores antiguos antes de mostrar los nuevos
                    formAltaImss.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
                    formAltaImss.querySelectorAll('.invalid-feedback').forEach(el => el.textContent = '');

                    // Muestra los nuevos errores de validación
                    Object.keys(body.errors).forEach(key => {
                        const inputId = key + '_modal'; // ej: 'sdi' -> 'sdi_modal'
                        const input = document.getElementById(inputId);
                        const errorDiv = input ? input.closest('.mb-3').querySelector('.invalid-feedback') : null;
                        
                        if (input && errorDiv) {
                            input.classList.add('is-invalid');
                            errorDiv.textContent = body.errors[key][0];
                        }
                    });
                } else if (status >= 200 && status < 300) { // 2xx: Éxito
                    // Redirige a la URL que nos indicó el controlador
                    window.location.href = body.redirect;
                } else { // Cualquier otro error (ej: 500 Error de servidor)
                    alert(body.message || 'Ocurrió un error inesperado. Revisa la consola para más detalles.');
                    console.error("Error del servidor:", body);
                }
            })
            .catch(error => {
                console.error('Error en la petición fetch:', error);
                alert('No se pudo conectar con el servidor. Revisa tu conexión a internet.');
            })
            .finally(() => {
                // Restaura el botón a su estado original
                submitButton.disabled = false;
                submitButton.innerHTML = originalButtonText;
            });
        });
    }

    // =====> SCRIPT PARA MODAL BAJA IMSS (Funcionalidad original) <=====
    // Nota: Si también quieres que la baja sea por AJAX, se necesita una lógica similar a la de arriba.
    const modalBajaImss = document.getElementById('modalBajaImss');
    if (modalBajaImss) {
        const formBajaImss = document.getElementById('formBajaImss');
        modalBajaImss.addEventListener('show.bs.modal', function(event) {
            const button = event.relatedTarget;
            const idEmpleado = button.dataset.id_empleado;
            document.getElementById('nombreEmpleadoBajaImss').textContent = button.dataset.nombre_empleado;
            document.getElementById('fecha_baja_imss_modal').value = new Date().toISOString().slice(0,10); 
            if (formBajaImss && idEmpleado) {
                let actionUrl = "{{ route('imss.registrarBaja', ['empleado' => ':id_empleado']) }}";
                formBajaImss.action = actionUrl.replace(':id_empleado', idEmpleado);
            }
        });
    }
});
</script>

</x-app-layout>