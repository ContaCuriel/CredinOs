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
                    <input type="hidden" name="id_empleado_alta_imss" id="id_empleado_alta_imss_modal">

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
                        @error('id_patron_imss') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
<div class="mb-3">
    <label for="sdi_modal" class="form-label">Salario Diario Integrado (SDI) <span class="text-danger">*</span></label>
    <div class="input-group input-group-sm">
        <span class="input-group-text">$</span>
        <input type="number" step="0.01" min="0" class="form-control @error('sdi') is-invalid @enderror" id="sdi_modal" name="sdi" required placeholder="Ej: 250.50">
    </div>
    @error('sdi') <div class="invalid-feedback">{{ $message }}</div> @enderror
</div>


                    <div class="mb-3">
                        <label for="fecha_alta_imss_modal" class="form-label">Fecha de Alta IMSS <span class="text-danger">*</span></label>
                        <input type="date" class="form-control form-control-sm @error('fecha_alta_imss') is-invalid @enderror" id="fecha_alta_imss_modal" name="fecha_alta_imss" required>
                        @error('fecha_alta_imss') <div class="invalid-feedback">{{ $message }}</div> @enderror
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
    // ... (tu script existente para tooltips y envío de filterForm si lo tienes) ...

    // =====> SCRIPT PARA MODAL ALTA IMSS <=====
    var modalAltaImss = document.getElementById('modalAltaImss');
    if (modalAltaImss) {
        var formAltaImss = document.getElementById('formAltaImss');
        var nombreEmpleadoSpanModal = document.getElementById('nombreEmpleadoAltaImss');
        var inputIdEmpleadoModal = document.getElementById('id_empleado_alta_imss_modal');
        var selectPatronModal = document.getElementById('id_patron_imss_modal');
        var inputFechaAltaModal = document.getElementById('fecha_alta_imss_modal');

        modalAltaImss.addEventListener('show.bs.modal', function(event) {
            var button = event.relatedTarget;
            var idEmpleado = button.dataset.id_empleado;
            var nombreEmpleado = button.dataset.nombre_empleado;
            var fechaAltaActual = button.dataset.fecha_alta_actual;
            var idPatronActual = button.dataset.id_patron_imss_actual;

            if (nombreEmpleadoSpanModal) nombreEmpleadoSpanModal.textContent = nombreEmpleado;
            if (inputIdEmpleadoModal) inputIdEmpleadoModal.value = idEmpleado;
            
            if (selectPatronModal) selectPatronModal.value = idPatronActual || ""; // Preseleccionar si existe
            if (inputFechaAltaModal) inputFechaAltaModal.value = fechaAltaActual || new Date().toISOString().slice(0,10); // Preseleccionar o hoy

            if (formAltaImss && idEmpleado) {
                // La ruta necesitará el ID del empleado
                let actionUrl = "{{ route('imss.registrarAlta', ['empleado' => ':id_empleado']) }}";
                formAltaImss.action = actionUrl.replace(':id_empleado', idEmpleado);
            }
        });
    }
});
</script>

<script>
document.addEventListener('DOMContentLoaded', function () {
    // ... (tu script existente para tooltips y modalAltaImss) ...

    // =====> SCRIPT PARA MODAL BAJA IMSS <=====
    var modalBajaImss = document.getElementById('modalBajaImss');
    if (modalBajaImss) {
        var formBajaImss = document.getElementById('formBajaImss');
        var nombreEmpleadoSpanBajaModal = document.getElementById('nombreEmpleadoBajaImss');
        var inputIdEmpleadoBajaModal = document.getElementById('id_empleado_baja_imss_modal');
        var inputFechaBajaModal = document.getElementById('fecha_baja_imss_modal');

        modalBajaImss.addEventListener('show.bs.modal', function(event) {
            var button = event.relatedTarget;
            var idEmpleado = button.dataset.id_empleado;
            var nombreEmpleado = button.dataset.nombre_empleado;
            // Podrías también pasar y usar fecha_alta_imss para validaciones o pre-llenados

            if (nombreEmpleadoSpanBajaModal) nombreEmpleadoSpanBajaModal.textContent = nombreEmpleado;
            if (inputIdEmpleadoBajaModal) inputIdEmpleadoBajaModal.value = idEmpleado;

            // Pre-llenar la fecha de baja con hoy por defecto, o dejar vacío
            if (inputFechaBajaModal) inputFechaBajaModal.value = new Date().toISOString().slice(0,10); 

            if (formBajaImss && idEmpleado) {
                let actionUrl = "{{ route('imss.registrarBaja', ['empleado' => ':id_empleado']) }}";
                formBajaImss.action = actionUrl.replace(':id_empleado', idEmpleado);
            }
        });
    }
});
</script>

</x-app-layout>