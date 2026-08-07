<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mis Recibos de Nómina</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <style>
        body { background-color: #f8f9fa; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        .navbar-custom { background: linear-gradient(135deg, #0d6efd 0%, #0a58ca 100%); }
    </style>
</head>
<body>
    <nav class="navbar navbar-dark navbar-custom shadow-sm mb-4">
        <div class="container">
            <span class="navbar-brand mb-0 h1 fw-bold"><i class="bi bi-wallet2 me-2"></i>Portal de Nómina</span>
            <div class="d-flex align-items-center text-white">
                <span class="me-3 d-none d-md-inline">{{ $empleado->nombre_completo }}</span>
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

        {{-- LA FOTO DE LA QUINCENA ACTUAL --}}
        <h5 class="fw-bold mb-3 text-secondary">Recibo más reciente</h5>
        @if($quincenaActual)
            <div class="card shadow-sm border-0 border-top border-primary border-4 mb-5 rounded-3">
                <div class="card-body p-4 text-center">
                    <span class="badge bg-success mb-2">Timbrado y Listo</span>
                    <h4 class="fw-bold text-dark">{{ $quincenaActual->detalle->periodo->periodo_rango ?? 'Periodo Actual' }}</h4>
                    <p class="text-muted mb-4">Sueldo Neto a Pagar: <span class="fw-bold fs-5 text-primary">${{ number_format($quincenaActual->detalle->total_neto ?? 0, 2) }}</span></p>
                    
                    <div class="d-flex justify-content-center gap-2">
                        <a href="{{ route('portal.descargar.pdf', $quincenaActual->id_detalle_lista) }}" target="_blank" class="btn btn-danger px-4 rounded-pill shadow-sm">
                            <i class="bi bi-file-earmark-pdf-fill me-1"></i> Ver PDF
                        </a>
                        <a href="{{ route('portal.descargar.xml', $quincenaActual->id_detalle_lista) }}" class="btn btn-outline-secondary px-4 rounded-pill shadow-sm">
                            <i class="bi bi-filetype-xml me-1"></i> Bajar XML
                        </a>
                    </div>
                </div>
            </div>
        @else
            <div class="alert alert-info shadow-sm">Aún no tienes recibos de nómina timbrados recientes.</div>
        @endif

        {{-- TABLA DEL HISTORIAL --}}
        @if($historial->isNotEmpty())
            <h5 class="fw-bold mb-3 text-secondary">Historial de Recibos Pasados</h5>
            <div class="card shadow-sm border-0 rounded-3">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light text-secondary">
                            <tr>
                                <th>Periodo</th>
                                <th>Fecha de Timbrado</th>
                                <th class="text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($historial as $nomina)
                                <tr>
                                    <td class="fw-bold text-dark">{{ $nomina->detalle->periodo->periodo_rango ?? 'N/A' }}</td>
                                    <td class="text-muted small">{{ \Carbon\Carbon::parse($nomina->fecha_timbrado)->translatedFormat('d \d\e F Y') }}</td>
                                    <td class="text-center">
                                        <div class="btn-group btn-group-sm">
                                            <a href="{{ route('portal.descargar.pdf', $nomina->id_detalle_lista) }}" target="_blank" class="btn btn-outline-danger"><i class="bi bi-file-earmark-pdf"></i> PDF</a>
                                            <a href="{{ route('portal.descargar.xml', $nomina->id_detalle_lista) }}" class="btn btn-outline-secondary"><i class="bi bi-filetype-xml"></i> XML</a>
                                        </div>
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