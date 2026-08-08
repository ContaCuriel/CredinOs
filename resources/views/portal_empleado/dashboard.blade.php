<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mi Portal | Comprobantes</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <style>
        body { background-color: #f8f9fa; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        .navbar-custom { background: linear-gradient(135deg, #0d6efd 0%, #0a58ca 100%); }
        .card-desglose { border-radius: 1rem; }
        .table-desglose th { font-size: 0.85rem; text-transform: uppercase; letter-spacing: 0.5px; }
    </style>
</head>
<body>
    <nav class="navbar navbar-dark navbar-custom shadow-sm mb-4">
        <div class="container">
            <span class="navbar-brand mb-0 h1 fw-bold"><i class="bi bi-wallet2 me-2"></i>Mi Portal</span>
            <div class="d-flex align-items-center text-white">
                <span class="me-3 d-none d-md-inline fw-semibold">{{ $empleado->nombre_completo }}</span>
                <form action="{{ route('portal.salir') }}" method="POST" class="m-0">
                    @csrf
                    <button type="submit" class="btn btn-sm btn-outline-light"><i class="bi bi-box-arrow-right"></i> Salir</button>
                </form>
            </div>
        </div>
    </nav>

    <div class="container pb-5">
        @if(session('error'))
            <div class="alert alert-danger shadow-sm"><i class="bi bi-exclamation-triangle-fill me-2"></i>{{ session('error') }}</div>
        @endif

        {{-- COMPROBANTE DEL PERIODO ACTUAL --}}
        <h5 class="fw-bold mb-3 text-secondary"><i class="bi bi-star-fill text-warning me-2"></i>Comprobante del Periodo Actual</h5>
        
        @if($quincenaActual)
            @php
                $esTimbrado = $quincenaActual->nominaTimbrada && $quincenaActual->nominaTimbrada->estado_timbrado === 'timbrado';
            @endphp

            <div class="card shadow-sm border-0 border-top border-primary border-4 mb-5 card-desglose">
                <div class="card-body p-4">
                    <div class="d-flex flex-wrap justify-content-between align-items-center mb-3">
                        <div>
                            @if($esTimbrado)
                                <span class="badge bg-success mb-1"><i class="bi bi-check-circle-fill me-1"></i> Disponible / Timbrado</span>
                            @else
                                <span class="badge bg-warning text-dark mb-1"><i class="bi bi-clock-history me-1"></i> Generado / Pendiente de Timbrado</span>
                            @endif
                            <h3 class="fw-bold text-dark m-0">{{ $quincenaActual->periodo_formateado }}</h3>
                        </div>
                        <div class="mt-2 mt-md-0">
                            <span class="text-muted small d-block text-md-end">Neto a Pagar</span>
                            <span class="fw-bold fs-3 text-primary">${{ number_format($quincenaActual->val_neto, 2) }}</span>
                        </div>
                    </div>

                    {{-- TABLA DE DESGLOSE COMPLETO --}}
                    <div class="table-responsive my-4 border rounded-3">
                        <table class="table table-bordered align-middle text-center mb-0 table-desglose">
                            <thead class="table-light text-secondary">
                                <tr>
                                    <th class="bg-light">Percepciones</th>
                                    <th colspan="4" class="bg-light">Deducciones</th>
                                    <th class="bg-primary text-white">Neto a Pagar</th>
                                </tr>
                                <tr>
                                    <th>Sueldo Quinc.</th>
                                    <th>Caja Ahorro</th>
                                    <th>Infonavit</th>
                                    <th>ISR</th>
                                    <th>IMSS</th>
                                    <th class="bg-primary text-white">Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td class="fw-bold text-success">${{ number_format($quincenaActual->val_sueldo, 2) }}</td>
                                    <td class="text-danger">({{ number_format($quincenaActual->val_caja, 2) }})</td>
                                    <td class="text-danger">({{ number_format($quincenaActual->val_infonavit, 2) }})</td>
                                    <td class="text-danger">({{ number_format($quincenaActual->val_isr, 2) }})</td>
                                    <td class="text-danger">({{ number_format($quincenaActual->val_imss, 2) }})</td>
                                    <td class="fw-bold fs-6 text-primary bg-light">${{ number_format($quincenaActual->val_neto, 2) }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    {{-- BOTONES DE DESCARGA --}}
                    <div class="d-flex justify-content-center gap-3 mt-3">
                        @if($esTimbrado)
                            <a href="{{ route('portal.descargar.pdf', $quincenaActual->id_detalle_lista) }}" target="_blank" class="btn btn-danger px-4 rounded-pill shadow-sm">
                                <i class="bi bi-file-earmark-pdf-fill me-1"></i> Ver PDF Recibo
                            </a>
                            <a href="{{ route('portal.descargar.xml', $quincenaActual->id_detalle_lista) }}" class="btn btn-outline-secondary px-4 rounded-pill shadow-sm">
                                <i class="bi bi-filetype-xml me-1"></i> Descargar XML
                            </a>
                        @else
                            <button class="btn btn-secondary px-4 rounded-pill shadow-sm" disabled>
                                <i class="bi bi-hourglass-split me-1"></i> PDF en proceso de timbrado
                            </button>
                        @endif
                    </div>
                </div>
            </div>
        @else
            <div class="alert alert-info shadow-sm rounded-3">Aún no tienes comprobantes generados.</div>
        @endif

        {{-- HISTORIAL DE COMPROBANTES --}}
        @if($historial->isNotEmpty())
            <h5 class="fw-bold mb-3 text-secondary"><i class="bi bi-clock-history me-2"></i>Historial de Comprobantes Pasados</h5>
            <div class="card shadow-sm border-0 rounded-3">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light text-secondary">
                            <tr>
                                <th>Periodo (Quincena)</th>
                                <th class="text-end">Monto Neto</th>
                                <th class="text-center">Estado / Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($historial as $nomina)
                                @php
                                    $esTimbradoH = $nomina->nominaTimbrada && $nomina->nominaTimbrada->estado_timbrado === 'timbrado';
                                @endphp
                                <tr>
                                    <td class="fw-bold text-dark">
                                        <i class="bi bi-calendar-check text-primary me-2"></i>{{ $nomina->periodo_formateado }}
                                    </td>
                                    <td class="text-end fw-bold text-secondary">
                                        ${{ number_format($nomina->val_neto, 2) }}
                                    </td>
                                    <td class="text-center">
                                        @if($esTimbradoH)
                                            <div class="btn-group btn-group-sm">
                                                <a href="{{ route('portal.descargar.pdf', $nomina->id_detalle_lista) }}" target="_blank" class="btn btn-outline-danger"><i class="bi bi-file-earmark-pdf"></i> PDF</a>
                                                <a href="{{ route('portal.descargar.xml', $nomina->id_detalle_lista) }}" class="btn btn-outline-secondary"><i class="bi bi-filetype-xml"></i> XML</a>
                                            </div>
                                        @else
                                            <span class="badge bg-light text-muted border">No Timbrado</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>