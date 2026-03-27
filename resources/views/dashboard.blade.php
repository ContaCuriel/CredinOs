<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-4">
        <div class="container-fluid">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Dashboard</h1>
            </div>

            {{-- ===== SECCIÓN DE SALUDO PERSONALIZADO ===== --}}
            <div class="mb-4">
                <h3 class="fw-normal">{{ $saludo ?? 'Bienvenido(a)' }}, {{ $nombreUsuario ?? 'Usuario' }}!</h3>
                @if(isset($mensajeEspecial))
                    <div class="alert alert-info" role="alert">
                        <i class="bi bi-stars"></i> {{ $mensajeEspecial }}
                    </div>
                @endif
            </div>
            {{-- ================================================= --}}

            {{-- ===== NUEVO: DASHBOARD GERENCIAL FINANCIERO ===== --}}
            @can('ver-widget-rentabilidad-sucursales')
                <div class="mb-5 bg-white p-4 rounded shadow-sm border">
                    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap">
    <div class="d-flex align-items-center gap-3">
    <h4 class="fw-bold text-primary mb-0"><i class="bi bi-graph-up-arrow me-2"></i>Visión General Financiera</h4>
    
    @can('descargar-reporte-ejecutivo-ia')
        <a href="{{ route('reportes.ejecutivo.pdf', ['start_date' => $startDate, 'end_date' => $endDate]) }}" 
           class="btn btn-sm btn-danger shadow-sm" 
           target="_blank">
            <i class="bi bi-file-earmark-pdf-fill me-1"></i> Descargar Reporte Ejecutivo (IA)
        </a>
    @endcan
</div>
    
    <form method="GET" action="{{ route('dashboard') }}" class="d-flex gap-2 align-items-end mt-3 mt-md-0">
        <div>
            <label for="start_date" class="form-label mb-0 text-muted" style="font-size: 0.8rem;">Desde:</label>
            <input type="date" name="start_date" id="start_date" class="form-control form-control-sm" value="{{ $startDate ?? now()->startOfMonth()->toDateString() }}">
        </div>
        <div>
            <label for="end_date" class="form-label mb-0 text-muted" style="font-size: 0.8rem;">Hasta:</label>
            <input type="date" name="end_date" id="end_date" class="form-control form-control-sm" value="{{ $endDate ?? now()->endOfMonth()->toDateString() }}">
        </div>
        <button type="submit" class="btn btn-sm btn-outline-secondary"><i class="bi bi-filter"></i> Filtrar</button>
    </form>
</div>

                    <div class="row mb-4 text-center">
        <div class="col mb-3">
            <div class="card bg-info text-white border-0 shadow-sm">
                <div class="card-body py-3">
                    <h6 class="text-uppercase fw-normal mb-1 opacity-75" style="font-size: 0.7rem;">Colocación</h6>
                    <h4 class="fw-bold mb-0">${{ number_format($totalColocacionEmpresa ?? 0, 2) }}</h4>
                </div>
            </div>
        </div>
        <div class="col mb-3">
            <div class="card bg-secondary text-white border-0 shadow-sm">
                <div class="card-body py-3">
                    <h6 class="text-uppercase fw-normal mb-1 opacity-75" style="font-size: 0.7rem;">Cap. Recuperado</h6>
                    <h4 class="fw-bold mb-0">${{ number_format($totalCapitalEmpresa ?? 0, 2) }}</h4>
                </div>
            </div>
        </div>
        <div class="col mb-3">
            <div class="card bg-success text-white border-0 shadow-sm">
                <div class="card-body py-3">
                    <h6 class="text-uppercase fw-normal mb-1 opacity-75" style="font-size: 0.7rem;">Int. Cobrados</h6>
                    <h4 class="fw-bold mb-0">${{ number_format($totalInteresesEmpresa ?? 0, 2) }}</h4>
                </div>
            </div>
        </div>
        <div class="col mb-3">
            <div class="card bg-danger text-white border-0 shadow-sm">
                <div class="card-body py-3">
                    <h6 class="text-uppercase fw-normal mb-1 opacity-75" style="font-size: 0.7rem;">Gastos Oper.</h6>
                    <h4 class="fw-bold mb-0">${{ number_format($totalGastosEmpresa ?? 0, 2) }}</h4>
                </div>
            </div>
        </div>
        <div class="col mb-3">
            @php 
                $utilidadNetaGlobal = ($totalInteresesEmpresa ?? 0) - ($totalGastosEmpresa ?? 0);
            @endphp
            <div class="card {{ $utilidadNetaGlobal >= 0 ? 'bg-primary' : 'bg-dark' }} text-white border-0 shadow-sm">
                <div class="card-body py-3">
                    <h6 class="text-uppercase fw-normal mb-1 opacity-75" style="font-size: 0.7rem;">Utilidad Real</h6>
                    <h4 class="fw-bold mb-0">${{ number_format($utilidadNetaGlobal, 2) }}</h4>
                </div>
            </div>
        </div>
    </div>

                    <div class="row g-4">
                        <div class="col-lg-8">
                            <div class="card border-0 shadow-none h-100 bg-light">
                                <div class="card-body">
                                    <h6 class="fw-bold mb-3 text-center">Rentabilidad por Sucursal (Ingresos vs Gastos vs Utilidad)</h6>
                                    <div style="position: relative; height: 300px; width: 100%;">
                                        <canvas id="sucursalesChart"></canvas>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-4">
                            <div class="card border-0 shadow-none h-100 bg-light">
                                <div class="card-body d-flex flex-column align-items-center justify-content-center">
                                    <h6 class="fw-bold mb-3 text-center">Composición del Gasto General</h6>
                                    <div style="position: relative; height: 250px; width: 100%; display: flex; justify-content: center;">
                                        <canvas id="gastosChart"></canvas>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endcan
            {{-- ================================================= --}}


            {{-- ===== SECCIÓN ORIGINAL (WIDGETS DE MASONRY) ===== --}}
            <div class="row" data-masonry='{"percentPosition": true }'>
                
                @can('ver-widget-contratos-vencer')
                <div class="col-md-6 col-lg-4 mb-4">
                    <div class="card">
                        <div class="card-header"><i class="bi bi-calendar-x"></i> Contratos por Vencer (Próx. 15 días)</div>
                        <div class="card-body">
                            @if(isset($contratosPorVencer) && $contratosPorVencer->isNotEmpty())
                                <ul class="list-group list-group-flush">
                                    @foreach ($contratosPorVencer as $contrato)
                                        <li class="list-group-item">
                                            <strong>{{ $contrato->empleado->nombre_completo }}</strong><br>
                                            <small>
                                                Puesto: {{ $contrato->empleado->puesto ? $contrato->empleado->puesto->nombre_puesto : 'N/A' }} <br>
                                                Sucursal: {{ $contrato->empleado->sucursal ? $contrato->empleado->sucursal->nombre_sucursal : 'N/A' }} <br>
                                                Vence: <strong>{{ \Carbon\Carbon::parse($contrato->fecha_fin)->format('d/m/Y') }}</strong>
                                                ({{ \Carbon\Carbon::parse($contrato->fecha_fin)->diffForHumans(now()->startOfDay(), true, false, 2) }} para vencer)
                                            </small>
                                        </li>
                                    @endforeach
                                </ul>
                            @else
                                <p class="text-muted mb-0">No hay contratos próximos a vencer.</p>
                            @endif
                        </div>
                    </div>
                </div>
                @endcan

                @can('ver-widget-contratos-vencer')
                <div class="col-md-6 col-lg-4 mb-4">
                    <div class="card border-warning">
                        <div class="card-header bg-warning text-dark"><i class="bi bi-exclamation-triangle"></i> Contratos Vencidos No Renovados (Últimos 7 días)</div>
                        <div class="card-body">
                            @if(isset($contratosVencidosRecientemente) && $contratosVencidosRecientemente->isNotEmpty())
                                <ul class="list-group list-group-flush">
                                    @foreach ($contratosVencidosRecientemente as $empleado)
                                        <li class="list-group-item">
                                            <strong>{{ $empleado->nombre_completo }}</strong><br>
                                            <small>
                                                Puesto: {{ $empleado->puesto ? $empleado->puesto->nombre_puesto : 'N/A' }} <br>
                                                Sucursal: {{ $empleado->sucursal ? $empleado->sucursal->nombre_sucursal : 'N/A' }} <br>
                                                Contrato venció: <strong class="text-danger">{{ \Carbon\Carbon::parse($empleado->ultimoContrato->fecha_fin)->format('d/m/Y') }}</strong>
                                                (hace {{ \Carbon\Carbon::parse($empleado->ultimoContrato->fecha_fin)->diffForHumans(null, true) }})
                                            </small>
                                        </li>
                                    @endforeach
                                </ul>
                            @else
                                <p class="text-muted mb-0">No hay contratos vencidos recientemente.</p>
                            @endif
                        </div>
                    </div>
                </div>
                @endcan
                
                @can('ver-widget-cumpleanos')
                <div class="col-md-6 col-lg-4 mb-4">
                    <div class="card">
                        <div class="card-header"><i class="bi bi-cake2"></i> Cumpleaños del Mes ({{ ucfirst(\Carbon\Carbon::now()->translatedFormat('F')) }})</div>
                        <div class="card-body">
                            @if(isset($cumpleanerosDelMes) && $cumpleanerosDelMes->isNotEmpty())
                                <ul class="list-group list-group-flush">
                                    @foreach ($cumpleanerosDelMes as $empleado)
                                        <li class="list-group-item">
                                            {{ $empleado->nombre_completo }} 
                                            ({{ \Carbon\Carbon::parse($empleado->fecha_nacimiento)->format('d') }})
                                            
                                            {{-- Indicador de bono de cumpleaños --}}
                                            @if($empleado->esElegibleParaBono)
                                                <i class="bi bi-gift-fill text-primary" title="Elegible para bono de cumpleaños"></i>
                                            @endif
                                            
                                            <br>
                                            <small class="text-muted">
                                                {{ $empleado->sucursal ? $empleado->sucursal->nombre_sucursal : 'Sin Sucursal' }}
                                            </small>
                                        </li>
                                    @endforeach
                                </ul>
                            @else
                                <p class="text-muted mb-0">No hay cumpleaños este mes.</p>
                            @endif
                        </div>
                    </div>
                </div>
                @endcan

                @can('ver-widget-aniversarios')
                <div class="col-md-6 col-lg-4 mb-4">
                    <div class="card">
                        <div class="card-header"><i class="bi bi-award"></i> Aniversarios Laborales del Mes ({{ ucfirst(\Carbon\Carbon::now()->translatedFormat('F')) }})</div>
                        <div class="card-body">
                            @if(isset($aniversariosDelMes) && $aniversariosDelMes->isNotEmpty())
                                <ul class="list-group list-group-flush">
                                    @foreach ($aniversariosDelMes as $empleado)
                                        <li class="list-group-item">
                                            {{ $empleado->nombre_completo }} 
                                            ({{ \Carbon\Carbon::parse($empleado->fecha_ingreso)->translatedFormat('d M') }})
                                            - Cumple <strong>{{ $empleado->anosCelebrando }}</strong>
                                            {{ $empleado->anosCelebrando == 1 ? 'año' : 'años' }}
                                        </li>
                                    @endforeach
                                </ul>
                            @else
                                <p class="text-muted mb-0">No hay aniversarios laborales este mes.</p>
                            @endif
                        </div>
                    </div>
                </div>
                @endcan

                @can('ver-widget-accesos-rapidos')
                <div class="col-md-6 col-lg-4 mb-4">
                    <div class="card">
                        <div class="card-header"><i class="bi bi-lightning-charge"></i> Accesos Rápidos</div>
                        <div class="card-body">
                           <a href="{{ route('empleados.create') }}" class="btn btn-success mb-2 w-100">Nuevo Empleado</a>
                           <a href="{{ route('contratos.create') }}" class="btn btn-info w-100">Nuevo Contrato</a>
                        </div>
                    </div>
                </div>
                @endcan

                @can('ver-widget-imss')
                <div class="col-md-6 col-lg-4 mb-4">
                    <div class="card">
                        <div class="card-header p-3 pt-2">
                            <div class="icon icon-lg icon-shape bg-gradient-info shadow-info text-center border-radius-xl mt-n4 position-absolute">
                                <i class="bi bi-shield-check opacity-10"></i>
                            </div>
                            <div class="text-end pt-1">
                                <p class="text-sm mb-0 text-capitalize">Empleados con IMSS</p>
                                <h4 class="mb-0">Por Patrón</h4>
                            </div>
                        </div>
                        <hr class="dark horizontal my-0">
                        <div class="card-footer p-3" style="max-height: 250px; overflow-y: auto;">
                            @if(isset($patronesConteoImss) && count($patronesConteoImss) > 0)
                                <ul class="list-group list-group-flush">
                                    @foreach($patronesConteoImss as $item)
                                        <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                                            <a href="{{ route('imss.index', ['id_patron_imss_filter' => $item['patron']->id_patron, 'estado_imss_filter' => 'Alta']) }}" class="text-primary">
                                                {{ $item['patron']->razon_social }}
                                            </a>
                                            <span class="badge bg-primary rounded-pill">{{ $item['conteo_imss_alta'] }}</span>
                                        </li>
                                    @endforeach
                                    </ul>
                            @else
                                <p class="text-sm text-muted mb-0">No hay patrones con empleados actualmente de alta en IMSS.</p>
                            @endif
                        </div>
                        <hr class="dark horizontal my-0">
                        <div class="card-footer p-3 text-center">
                            <a href="{{ route('imss.index') }}" class="btn btn-outline-primary btn-sm w-100">
                                Ir a Gestión IMSS
                            </a>
                        </div>
                    </div>
                </div>
                @endcan

                @can('aprobar-gastos')
                <div class="col-md-6 col-lg-4 mb-4">
                    <div class="card h-100">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <span><i class="bi bi-hourglass-split"></i> Gastos Pendientes de Aprobación</span>
                            @if(isset($gastosPendientes) && $gastosPendientes->count() > 0)
                                <span class="badge bg-danger rounded-pill">{{ $gastosPendientes->count() }}</span>
                            @endif
                        </div>
                        <div class="card-body">
                            @if(isset($gastosPendientes) && $gastosPendientes->isNotEmpty())
                                <p class="text-sm text-muted">Mostrando los 5 más recientes.</p>
                                <ul class="list-group list-group-flush">
                                    @foreach ($gastosPendientes as $gasto)
                                        <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                                            <div>
                                                <strong>{{ $gasto->sucursal?->nombre_sucursal ?? 'N/A' }}</strong><br>
                                                <small>
                                                    {{ $gasto->categoria?->nombre ?? 'Sin Categoría' }} - {{ $gasto->fecha_gasto->format('d/m/Y') }}
                                                </small>
                                            </div>
                                            <strong class="text-danger">${{ number_format($gasto->monto_total, 2) }}</strong>
                                        </li>
                                    @endforeach
                                </ul>
                            @else
                                <div class="text-center py-3">
                                    <i class="bi bi-check-circle-fill text-success fs-3"></i>
                                    <p class="text-muted mt-2 mb-0">¡Excelente! No hay gastos pendientes.</p>
                                </div>
                            @endif
                        </div>
                        <div class="card-footer p-3 text-center">
                            <a href="{{ route('gastos.approvals') }}" class="btn btn-outline-primary btn-sm w-100">
                                Ir a Aprobar Gastos
                            </a>
                        </div>
                    </div>
                </div>
                @endcan

                @can('ver-widget-nuevos-ingresos')
                <div class="col-md-6 col-lg-4 mb-4">
                    <div class="card h-100 border-success">
                        <div class="card-header bg-success text-white"><i class="bi bi-person-plus-fill"></i> Nuevos Ingresos ({{ $fortnightTitle ?? 'Quincena' }})</div>
                        <div class="card-body">
                            @if(isset($nuevosIngresos) && $nuevosIngresos->isNotEmpty())
                                <ul class="list-group list-group-flush">
                                    @foreach ($nuevosIngresos as $empleado)
                                        <li class="list-group-item px-0">
                                            <strong>{{ $empleado->nombre_completo }}</strong><br>
                                            <small>
                                                Puesto: {{ $empleado->puesto?->nombre_puesto ?? 'N/A' }} <br>
                                                Sucursal: {{ $empleado->sucursal?->nombre_sucursal ?? 'N/A' }} <br>
                                                Ingreso: <strong>{{ \Carbon\Carbon::parse($empleado->fecha_ingreso)->format('d/m/Y') }}</strong>
                                            </small>
                                        </li>
                                    @endforeach
                                </ul>
                            @else
                                <p class="text-muted mb-0">No hay nuevos ingresos activos en esta quincena.</p>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="col-md-6 col-lg-4 mb-4">
                    <div class="card h-100 border-danger">
                        <div class="card-header bg-danger text-white"><i class="bi bi-person-dash-fill"></i> Bajas de la Quincena</div>
                        <div class="card-body">
                            @if(isset($bajasQuincena) && $bajasQuincena->isNotEmpty())
                                <ul class="list-group list-group-flush">
                                    @foreach ($bajasQuincena as $empleado)
                                        <li class="list-group-item px-0">
                                            <strong>{{ $empleado->nombre_completo }}</strong><br>
                                            <small>
                                                Puesto: {{ $empleado->puesto?->nombre_puesto ?? 'N/A' }} <br>
                                                Sucursal: {{ $empleado->sucursal?->nombre_sucursal ?? 'N/A' }} <br>
                                                Baja: <strong class="text-danger">{{ \Carbon\Carbon::parse($empleado->fecha_baja)->format('d/m/Y') }}</strong>
                                            </small>
                                        </li>
                                    @endforeach
                                </ul>
                            @else
                                <p class="text-muted mb-0">No hay bajas registradas en esta quincena.</p>
                            @endif
                        </div>
                    </div>
                </div>
                @endcan

            </div> {{-- Fin del Contenedor .row Masonry --}}
        </div>
    </div>

    @push('scripts')
    {{-- Script de la librería Masonry --}}
    <script src="https://cdn.jsdelivr.net/npm/masonry-layout@4.2.2/dist/masonry.pkgd.min.js" integrity="sha384-GNFwBvfVxBkLMJpYMOABq3c+d3KnQxudP/mGPkzpZSTYykLBNsZEnG2D9G/X/+7D" crossorigin="anonymous" async></script>
    
    {{-- LIBRERÍA CHART.JS PARA LAS GRÁFICAS FINANCIERAS --}}
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <script>
    document.addEventListener("DOMContentLoaded", function() {
        // Ejecutar las gráficas solo si existe el bloque de rentabilidad
        @if(isset($rentabilidad) && count($rentabilidad) > 0)
            
            // 1. Datos para la Gráfica de Sucursales
            const rentabilidadData = @json($rentabilidad);
            
            const sucursalesLabels = rentabilidadData.map(s => s.nombre);
            const capital = rentabilidadData.map(s => s.capital);
            const intereses = rentabilidadData.map(s => s.ingresos);
            const gastos = rentabilidadData.map(s => s.gastos);
            const utilidades = rentabilidadData.map(s => s.utilidad);

            const ctxSucursales = document.getElementById('sucursalesChart');
            if(ctxSucursales) {
                new Chart(ctxSucursales.getContext('2d'), {
                    type: 'bar',
                    data: {
                        labels: sucursalesLabels,
                        datasets: [
        {
            label: 'Colocación',
            backgroundColor: '#0dcaf0', // Cian
            data: rentabilidadData.map(s => s.colocacion),
            borderRadius: 4,
            barPercentage: 0.5
        },
        {
            label: 'Cap. Recup.',
            backgroundColor: '#adb5bd', // Gris
            data: rentabilidadData.map(s => s.capital),
            borderRadius: 4,
            barPercentage: 0.5
        },
        {
            label: 'Intereses',
            backgroundColor: '#198754', // Verde
            data: rentabilidadData.map(s => s.ingresos),
            borderRadius: 4,
            barPercentage: 0.5
        },
                            {
                                label: 'Gastos',
                                backgroundColor: '#dc3545', // Rojo danger
                                data: gastos,
                                borderRadius: 4
                            },
                            {
                                label: 'Utilidad Neta (Real)',
                                type: 'line',
                                borderColor: '#0d6efd', // Azul primary
                                backgroundColor: '#0d6efd',
                                borderWidth: 3,
                                pointBackgroundColor: '#fff',
                                pointBorderWidth: 2,
                                pointRadius: 4,
                                fill: false,
                                data: utilidades,
                                tension: 0.3
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { position: 'top' }
                        },
                        scales: { 
                            y: { 
                                beginAtZero: true,
                                grid: { color: '#e9ecef' }
                            },
                            x: {
                                grid: { display: false }
                            }
                        }
                    }
                });
            }

            // 2. Datos para la Gráfica de Pastel (Gastos)
            const gastosData = @json($gastosPorCategoria);
            const categoriasLabels = Object.keys(gastosData);
            const montosGastos = Object.values(gastosData);

            const ctxGastos = document.getElementById('gastosChart');
            if(ctxGastos && categoriasLabels.length > 0) {
                new Chart(ctxGastos.getContext('2d'), {
                    type: 'doughnut',
                    data: {
                        labels: categoriasLabels,
                        datasets: [{
                            data: montosGastos,
                            backgroundColor: [
                                '#0d6efd', '#6610f2', '#d63384', '#fd7e14', '#ffc107', '#20c997', '#0dcaf0'
                            ],
                            borderWidth: 2,
                            borderColor: '#fff'
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        cutout: '65%',
                        plugins: {
                            legend: { 
                                position: 'right',
                                labels: { padding: 15, usePointStyle: true, boxWidth: 10 }
                            }
                        }
                    }
                });
            }
        @endif
    });
    </script>
    @endpush

</x-app-layout>