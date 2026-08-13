<x-app-layout>
    <div class="container-fluid py-4">
        
        {{-- ENCABEZADO Y ESTATUS --}}
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="fw-bold mb-1 text-dark">
                    <i class="bi bi-file-earmark-text me-2 text-primary"></i>Solicitud: {{ $credito->folio }}
                </h4>
                <p class="text-muted mb-0">
                    Capturado por: {{ $credito->asesor->nombre_completo ?? 'N/A' }} el {{ $credito->fecha_solicitud->format('d/m/Y') }}
                </p>
            </div>
            <div class="text-end">
                @if($credito->estatus == 'solicitado')
                    <span class="badge bg-warning text-dark fs-6 px-3 py-2"><i class="bi bi-hourglass-split me-1"></i> Pendiente de Aprobación</span>
                @elseif($credito->estatus == 'aprobado')
                    <span class="badge bg-info text-dark fs-6 px-3 py-2"><i class="bi bi-check-all me-1"></i> Aprobado (Falta Fondeo)</span>
                @elseif($credito->estatus == 'desembolsado')
                    <span class="badge bg-success fs-6 px-3 py-2"><i class="bi bi-cash me-1"></i> Crédito Activo</span>
                @endif
                <a href="{{ route('creditos.index') }}" class="btn btn-outline-secondary ms-3">
                    <i class="bi bi-arrow-left me-1"></i> Regresar
                </a>
            </div>
        </div>

        <div class="row">
            {{-- COLUMNA IZQUIERDA: DATOS GENERALES --}}
            <div class="col-lg-4 mb-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-white py-3">
                        <h6 class="fw-bold text-dark mb-0"><i class="bi bi-info-circle-fill me-2 text-primary"></i>Detalles del Crédito</h6>
                    </div>
                    <div class="card-body">
                        
                        <div class="mb-3">
                            <span class="text-muted d-block small fw-bold text-uppercase">Titular</span>
                            @if($credito->grupo_id)
                                <span class="fs-5 fw-bold text-purple"><i class="bi bi-people-fill me-1"></i> {{ $credito->grupo->nombre_grupo }}</span>
                                <span class="badge bg-purple bg-opacity-10 text-purple ms-2">Grupal</span>
                            @else
                                <span class="fs-5 fw-bold text-info text-dark"><i class="bi bi-person-fill me-1"></i> {{ $credito->cliente->nombre_completo ?? $credito->cliente->nombre }}</span>
                                <span class="badge bg-info bg-opacity-10 text-info text-dark ms-2">Individual</span>
                            @endif
                            @if($credito->nombre_credito)
                                <div class="text-muted mt-1 fst-italic">"{{ $credito->nombre_credito }}"</div>
                            @endif
                        </div>

                        <hr>

                        <div class="mb-3">
                            <span class="text-muted d-block small fw-bold text-uppercase">Producto Aplicado</span>
                            <span class="fw-bold text-dark">{{ $credito->producto->nombre }}</span>
                            <ul class="mb-0 mt-1 small text-muted">
                                <li>Tasa: {{ $credito->tasa_interes_aplicada }}%</li>
                                <li>Comisión Apertura: {{ $credito->comision_apertura_aplicada }}%</li>
                            </ul>
                        </div>

                        <hr>

                        <div class="mb-3">
                            <span class="text-muted d-block small fw-bold text-uppercase">Monto Solicitado</span>
                            <span class="fs-3 fw-bold text-success">${{ number_format($credito->monto_solicitado, 2) }}</span>
                        </div>
                        
                        <div class="mb-3">
                            <span class="text-muted d-block small fw-bold text-uppercase">Plazo Solicitado</span>
                            <span class="fs-5 fw-bold text-dark">{{ $credito->plazo_solicitado }} Cuotas ({{ ucfirst($credito->producto->frecuencia_pago) }}s)</span>
                        </div>

                    </div>
                </div>
            </div>

            {{-- COLUMNA DERECHA: INTEGRANTES Y CUENTAS --}}
            <div class="col-lg-8">
                
                {{-- TABLA DE INTEGRANTES --}}
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white py-3">
                        <h6 class="fw-bold text-dark mb-0"><i class="bi bi-people-fill me-2 text-success"></i>Integrantes del Crédito</h6>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="bg-light">
                                    <tr>
                                        <th class="ps-4">Nombre del Cliente</th>
                                        <th class="text-center">Rol</th>
                                        <th class="text-end pe-4">Monto Individual</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($credito->integrantes as $integrante)
                                    <tr>
                                        <td class="ps-4 fw-bold">
                                            {{ $integrante->nombre }} {{ $integrante->apellido_paterno }} {{ $integrante->apellido_materno }}
                                        </td>
                                        <td class="text-center">
                                            @if($integrante->pivot->es_lider)
                                                <span class="badge bg-warning text-dark"><i class="bi bi-star-fill me-1"></i> Líder</span>
                                            @else
                                                <span class="badge bg-secondary">Integrante</span>
                                            @endif
                                        </td>
                                        <td class="text-end pe-4 text-success fw-bold">
                                            ${{ number_format($integrante->pivot->monto_individual, 2) }}
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                {{-- TABLA DE CUENTAS BANCARIAS --}}
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white py-3">
                        <h6 class="fw-bold text-dark mb-0"><i class="bi bi-bank2 me-2 text-warning"></i>Cuentas de Fondeo (Desembolso)</h6>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="bg-light">
                                    <tr>
                                        <th class="ps-4">Banco</th>
                                        <th>Titular</th>
                                        <th class="pe-4">Cuenta / CLABE</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($credito->cuentasDesembolso as $cuenta)
                                    <tr>
                                        <td class="ps-4 fw-bold">{{ $cuenta->banco }}</td>
                                        <td>{{ $cuenta->titular }}</td>
                                        <td class="pe-4 text-primary font-monospace">{{ $cuenta->numero_cuenta }}</td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="3" class="text-center text-muted py-3">No hay cuentas registradas.</td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                {{-- ZONA DE AUTORIZACIÓN (FASE 2 PREPARACIÓN) --}}
                @if($credito->estatus == 'solicitado')
                <div class="card border-0 shadow-sm border-start border-warning border-4 bg-light">
                    <div class="card-body p-4 text-center">
                        <h5 class="fw-bold text-dark mb-3">Zona de Autorización de Crédito</h5>
                        <p class="text-muted">Como jefe de administración, revisa los datos capturados. Si todo está correcto, procede a dictaminar el crédito.</p>
                        
                        <button class="btn btn-warning fw-bold text-dark px-4 shadow-sm" disabled>
                            <i class="bi bi-shield-check me-2"></i> Aprobar Crédito (Módulo en Construcción)
                        </button>
                    </div>
                </div>
                @endif

            </div>
        </div>
    </div>
</x-app-layout>