<x-app-layout>
    {{-- Cabeceras para Select2, si aún no están en el layout principal --}}
    @push('styles')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" />
    
    {{-- ======================================================= --}}
    {{--             CORRECCIÓN DE ESTILO DEFINITIVA             --}}
    {{-- ======================================================= --}}
    <style>
        /* Apunta al contenedor del menú desplegable que contiene el campo de búsqueda */
        .select2-dropdown {
            z-index: 3051; /* Un valor muy alto para asegurar que esté por encima de todo */
        }
    </style>
@endpush

    <div class="container py-4">
        <form action="{{ route('creditos.store') }}" method="POST" id="credit-form">
            @csrf
            <div class="card">
                <div class="card-header"><h5 class="card-title mb-0">Registrar Nueva Solicitud de Crédito</h5></div>
                <div class="card-body">
                    @if(session('error'))
                        <div class="alert alert-danger">{{ session('error') }}</div>
                    @endif
                    
                    {{-- SECCIÓN 1: DATOS GENERALES DEL CRÉDITO (CORREGIDA) --}}
<h6><i class="bi bi-file-earmark-text"></i> 1. Datos Generales del Crédito</h6>
<div class="p-3 border rounded mb-4">

    {{-- Nombre del Crédito (ahora hasta arriba) --}}
    <div class="row">
        <div class="col-md-12 mb-3">
            <label for="credit_name" class="form-label">Nombre del Crédito / Identificador</label>
            <input type="text" name="credit_name" id="credit_name" class="form-control" required placeholder="Ej: Crédito Grupal 'Las Emprendedoras' - Julio 2025">
        </div>
    </div>

    {{-- Fila con datos administrativos --}}
    <div class="row">
        <div class="col-md-4 mb-3"><label class="form-label">Fecha de Solicitud</label><input type="date" name="fecha_solicitud" class="form-control" value="{{ date('Y-m-d') }}" required></div>
        <div class="col-md-4 mb-3"><label class="form-label">Sucursal</label><select name="id_sucursal" class="form-select" required>@foreach($sucursales as $s)<option value="{{ $s->id_sucursal }}">{{ $s->nombre_sucursal }}</option>@endforeach</select></div>
        <div class="col-md-4 mb-3"><label class="form-label">Asesor</label><select name="id_asesor" class="form-select" required><option value="">Seleccione...</option>@foreach($asesores as $a)<option value="{{ $a->id_empleado }}">{{ $a->nombre_completo }}</option>@endforeach</select></div>
    </div>

    {{-- Fila con detalles del crédito --}}
    <div class="row">
        <div class="col-md-4 mb-3"><label class="form-label">Producto de Crédito</label><select name="credit_type_id" id="credit_type_id" class="form-select" required><option value="">Seleccione...</option>@foreach ($creditTypes as $type)<option value="{{ $type->id }}" data-is-group="{{ $type->is_group_loan }}" data-term="{{ $type->default_term }}">{{ $type->name }}</option>@endforeach</select></div>
        <div class="col-md-4 mb-3"><label class="form-label">Tasa de Interés</label><select name="interest_rate_id" class="form-select" required><option value="">Seleccione...</option>@foreach ($interestRates as $rate)<option value="{{ $rate->id }}">{{ $rate->name }} ({{ $rate->rate }}%)</option>@endforeach</select></div>
        <div class="col-md-4 mb-3"><label class="form-label">Plazo (autocompletado)</label><input type="text" name="plazo" id="plazo" class="form-control" readonly></div>
    </div>
    
    {{-- Fila con el Monto Total (reincorporado) --}}
    <div class="row">
        <div class="col-md-6">
             <label for="monto_solicitado" class="form-label fw-bold">Monto Total Solicitado</label>
             <div class="input-group">
                <span class="input-group-text">$</span>
                <input type="number" name="monto_solicitado" id="monto_solicitado" class="form-control" step="100" required>
            </div>
        </div>
    </div>

</div>

                    {{-- SECCIÓN 2: CLIENTES Y DISTRIBUCIÓN --}}
                    <h6><i class="bi bi-people"></i> 2. Clientes y Distribución de Montos</h6>
                    <div class="p-3 border rounded mb-4">
                        <div class="row">
                            {{-- Columna Izquierda: Miembros seleccionados y montos --}}
                            <div class="col-lg-7">
                                <h6 class="mb-3">Clientes Seleccionados</h6>
                                <div id="members-table-wrapper" class="table-responsive">
                                    <table class="table table-striped table-bordered">
                                        <thead>
                                            <tr>
                                                <th>Cliente</th>
                                                <th style="width: 180px;">Monto Individual</th>
                                                <th style="width: 80px;">Acción</th>
                                            </tr>
                                        </thead>
                                        <tbody id="members-table-body">
                                            <tr id="no-members-row"><td colspan="3" class="text-center text-muted">Añada clientes desde la derecha.</td></tr>
                                        </tbody>
                                    </table>
                                </div>
                                <div class="text-end fw-bold mt-2">Suma de Montos: <span id="total-members-amount" class="text-danger">$0.00</span></div>
                            </div>
                            
                            {{-- Columna Derecha: Añadir nuevos miembros --}}
                            <div class="col-lg-5">
                                <div class="card bg-light">
                                    <div class="card-body">
                                        <h6 class="card-title mb-3">Añadir Cliente</h6>
                                        <div class="mb-3">
                                            <label for="client_search" class="form-label">Buscar Cliente</label>
                                            <select class="form-select" id="client_search" style="width: 100%;">
                                                <option></option> {{-- Requerido para el placeholder de Select2 --}}
                                            </select>
                                        </div>
                                        <button type="button" id="add-client-btn" class="btn btn-success w-100">Añadir Cliente Seleccionado</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    {{-- SECCIÓN 3: MONTO Y DESEMBOLSO --}}
                    <h6><i class="bi bi-cash-coin"></i> 3. Monto Total y Desembolso</h6>
                    <div class="p-3 border rounded">
                        <div class="row">
                            <div class="col-md-4 mb-3"><label class="form-label">Monto Total Solicitado</label><input type="number" name="monto_solicitado" id="monto_solicitado" class="form-control" step="100" required></div>
                            <div class="col-md-4 mb-3"><label class="form-label">Plazo (autocompletado)</label><input type="text" name="plazo" id="plazo" class="form-control" readonly></div>
                        </div>
                         <div class="row">
                            <div class="col-md-6 mb-3"><label class="form-label">Banco para Desembolso</label><input type="text" name="disbursement_bank" class="form-control" required></div>
                            <div class="col-md-6 mb-3"><label class="form-label">Cuenta / CLABE de Desembolso</label><input type="text" name="disbursement_account_number" class="form-control" required></div>
                        </div>
                    </div>

                    {{-- El input oculto para los IDs de los clientes --}}
                    <div id="hidden-client-ids"></div>

                    <div class="mt-4 text-end">
                        <a href="{{ route('creditos.index') }}" class="btn btn-secondary">Cancelar</a>
                        <button type="submit" class="btn btn-primary">Registrar Solicitud</button>
                    </div>
                </div>
            </div>
        </form>
    </div>

@push('scripts')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
$(document).ready(function() {
    let selectedClients = new Map();

    // --- CONFIGURACIÓN DEFINITIVA DE SELECT2 ---
    const clientSearch = $('#client_search').select2({
        theme: 'bootstrap-5',
        placeholder: 'Escribe para buscar un cliente...',
        allowClear: true,
        minimumInputLength: 2,
        // La opción clave para evitar conflictos con Bootstrap:
        dropdownParent: $('#client_search').closest('.card-body'),
        ajax: {
            url: "{{ route('clientes.search') }}",
            dataType: 'json',
            delay: 250,
            processResults: function (data) {
                const filteredData = data.filter(item => !selectedClients.has(parseInt(item.id)));
                return {
                    results: filteredData
                };
            },
            cache: true
        }
    });

    // --- MANEJO DE EVENTOS (SIN CAMBIOS) ---
    $('#credit_type_id').on('change', handleCreditTypeChange);
    $('#add-client-btn').on('click', addSelectedClient);
    $(document).on('click', '.remove-client-btn', removeClient);
    $(document).on('input', '.member-amount, #monto_solicitado', updateMemberAmountSum);

    // --- FUNCIONES (SIN CAMBIOS) ---
    function addSelectedClient() {
        const clientId = clientSearch.val();
        if (!clientId) return;
        const clientData = clientSearch.select2('data')[0];
        if (!clientData || selectedClients.has(parseInt(clientId))) return;
        const isIndividual = !$('#credit_type_id').find('option:selected').data('is-group');
        if(isIndividual && selectedClients.size > 0) {
            alert('Para créditos individuales solo puede seleccionar un cliente.');
            return;
        }
        selectedClients.set(parseInt(clientId), { id: clientId, text: clientData.text });
        addClientToTable({ id: clientId, text: clientData.text });
        clientSearch.val(null).trigger('change');
        updateHiddenInputs();
    }

    function removeClient() {
        const clientId = parseInt($(this).data('client-id'));
        selectedClients.delete(clientId);
        $(this).closest('tr').remove();
        if (selectedClients.size === 0) { $('#no-members-row').show(); }
        updateHiddenInputs();
        updateMemberAmountSum();
    }
    
    function addClientToTable(client) {
        $('#no-members-row').hide();
        const row = `<tr id="member-row-${client.id}"><td>${client.text}</td><td><div class="input-group"><span class="input-group-text">$</span><input type="number" name="montos_individuales[${client.id}]" class="form-control member-amount" required step="50" value="0"></div></td><td><button type="button" class="btn btn-danger btn-sm remove-client-btn" data-client-id="${client.id}"><i class="bi bi-trash"></i></button></td></tr>`;
        $('#members-table-body').append(row);
    }
    
    function updateHiddenInputs() {
        $('#hidden-client-ids').empty();
        selectedClients.forEach((client, id) => {
            $('#hidden-client-ids').append(`<input type="hidden" name="cliente_ids[]" value="${id}">`);
        });
    }
    
    function handleCreditTypeChange() {
        const isGroup = $(this).find('option:selected').data('is-group') == 1;
        $('#plazo').val($(this).find('option:selected').data('term') || '');
        selectedClients.clear();
        $('#members-table-body').empty().append('<tr id="no-members-row"><td colspan="3" class="text-center text-muted">Añada clientes desde la derecha.</td></tr>');
        updateHiddenInputs();
        updateMemberAmountSum();
    }

    function updateMemberAmountSum() {
        let total = 0;
        $('.member-amount').each(function() { total += parseFloat($(this).val()) || 0; });
        const totalSolicitado = parseFloat($('#monto_solicitado').val()) || 0;
        $('#total-members-amount').text('$' + total.toFixed(2));
        $('#total-members-amount').toggleClass('text-danger', total !== totalSolicitado || total === 0);
        $('#total-members-amount').toggleClass('text-success', total === totalSolicitado && total > 0);
    }
});
</script>
@endpush
</x-app-layout>