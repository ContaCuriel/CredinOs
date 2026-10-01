<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <style>
        /* Fondo Animado iOS Mesh Gradient Sutil */
        .bg-system-islands {
            background: linear-gradient(-45deg, #f0f4fd, #f4f0fa, #f0faf5, #f5f8fc);
            background-size: 400% 400%;
            animation: subtleMesh 20s ease infinite;
            min-height: calc(100vh - 60px);
            padding-bottom: 3rem;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
        }

        @keyframes subtleMesh {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }

        /* Tarjetas Estilo Isla (Glassmorphism) */
        .ios-card {
            background: rgba(255, 255, 255, 0.85);
            backdrop-filter: blur(24px);
            -webkit-backdrop-filter: blur(24px);
            border: 1px solid rgba(255, 255, 255, 0.9);
            border-radius: 1.5rem;
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.04);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .ios-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 12px 40px rgba(0, 0, 0, 0.08);
        }

        .ios-card-header {
            background-color: transparent;
            border-bottom: 1px solid rgba(0, 0, 0, 0.04);
            padding: 1.25rem 1.5rem;
        }

        .ios-list-item {
            background-color: transparent;
            border-bottom: 1px solid rgba(0, 0, 0, 0.03) !important;
            padding: 1rem 1.5rem;
            transition: background-color 0.2s;
        }

        .ios-list-item:hover {
            background-color: rgba(0, 0, 0, 0.015);
        }

        .ios-list-item:last-child {
            border-bottom: none !important;
        }

        /* Iconos Gradientes */
        .icon-gradient-primary { background: linear-gradient(135deg, #0a84ff, #0066cc); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
        .icon-gradient-warning { background: linear-gradient(135deg, #ff9f0a, #ff8c00); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
        .icon-gradient-danger { background: linear-gradient(135deg, #ff453a, #d70015); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
        .icon-gradient-success { background: linear-gradient(135deg, #32d74b, #248a3d); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
        .icon-gradient-purple { background: linear-gradient(135deg, #bf5af2, #5e5ce6); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }

        /* Botones estilo iOS */
        .btn-ios-primary {
            background: linear-gradient(135deg, #0071e3, #005bb5);
            color: white;
            border-radius: 2rem;
            font-weight: 500;
            padding: 0.6rem 1.2rem;
            border: none;
            box-shadow: 0 4px 10px rgba(0, 113, 227, 0.2);
            transition: all 0.2s;
        }
        .btn-ios-primary:hover {
            background: linear-gradient(135deg, #0077ED, #0062c4);
            transform: translateY(-1px);
            box-shadow: 0 6px 15px rgba(0, 113, 227, 0.35);
            color: white;
        }

        .btn-ios-outline {
            background-color: rgba(0, 113, 227, 0.05);
            color: #0071e3;
            border-radius: 2rem;
            font-weight: 600;
            padding: 0.6rem 1.2rem;
            border: 1px solid rgba(0, 113, 227, 0.2);
            transition: all 0.2s;
        }
        .btn-ios-outline:hover {
            background-color: rgba(0, 113, 227, 0.1);
            color: #0071e3;
        }
        
        .text-ios-muted { color: #86868b; }
        .text-ios-dark { color: #1d1d1f; }
    </style>

    <div class="bg-system-islands pt-4">
        <div class="container-fluid px-4">
            
            {{-- ===== SECCIÓN DE SALUDO PERSONALIZADO ===== --}}
            <div class="mb-5 mt-2">
                <h1 class="fw-bold text-ios-dark mb-1" style="font-size: 2.2rem; letter-spacing: -1px;">
                    {{ $saludo ?? 'Bienvenido(a)' }}, {{ $nombreUsuario ?? 'Usuario' }}.
                </h1>
                <p class="text-ios-muted" style="font-size: 1.1rem;">Aquí tienes el resumen de tu equipo hoy.</p>
                
                @if(isset($mensajeEspecial))
                    <div class="alert mt-3 border-0 shadow-sm d-flex align-items-center" style="background: rgba(255, 255, 255, 0.8); backdrop-filter: blur(10px); border-radius: 1rem; color: #1d1d1f;">
                        <i class="bi bi-stars fs-4 me-3 icon-gradient-purple"></i> 
                        <span class="fw-medium">{{ $mensajeEspecial }}</span>
                    </div>
                @endif
            </div>

            {{-- ===== WIDGETS DE MASONRY (ISLAS) ===== --}}
            <div class="row" data-masonry='{"percentPosition": true }'>
                
                {{-- Contratos por Vencer --}}
                @can('ver-widget-contratos-vencer')
                <div class="col-md-6 col-lg-4 mb-4">
                    <div class="card ios-card border-0">
                        <div class="ios-card-header d-flex align-items-center">
                            <i class="bi bi-calendar-event fs-4 me-2 icon-gradient-warning"></i>
                            <div>
                                <h6 class="mb-0 fw-bold text-ios-dark">Contratos por Vencer</h6>
                                <small class="text-ios-muted" style="font-size: 0.75rem;">Próximos 15 días</small>
                            </div>
                        </div>
                        <div class="card-body p-0">
                            @if(isset($contratosPorVencer) && $contratosPorVencer->isNotEmpty())
                                <ul class="list-group list-group-flush rounded-bottom" style="border-radius: 0 0 1.5rem 1.5rem; overflow: hidden;">
                                    @foreach ($contratosPorVencer as $contrato)
                                        <li class="list-group-item ios-list-item">
                                            <strong class="text-ios-dark" style="font-size: 0.95rem;">{{ $contrato->empleado->nombre_completo }}</strong><br>
                                            <div class="d-flex justify-content-between align-items-center mt-1">
                                                <small class="text-ios-muted">
                                                    {{ $contrato->empleado->puesto ? $contrato->empleado->puesto->nombre_puesto : 'N/A' }} <br>
                                                </small>
                                                <span class="badge bg-warning bg-opacity-25 text-dark rounded-pill border border-warning border-opacity-25 px-2 py-1">
                                                    {{ \Carbon\Carbon::parse($contrato->fecha_fin)->format('d/m/Y') }}
                                                </span>
                                            </div>
                                        </li>
                                    @endforeach
                                </ul>
                            @else
                                <div class="p-4 text-center">
                                    <i class="bi bi-check-circle text-success fs-3 opacity-50"></i>
                                    <p class="text-ios-muted mb-0 mt-2" style="font-size: 0.85rem;">No hay contratos próximos a vencer.</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
                @endcan

                {{-- Contratos Vencidos --}}
                @can('ver-widget-contratos-vencer')
                <div class="col-md-6 col-lg-4 mb-4">
                    <div class="card ios-card border-0">
                        <div class="ios-card-header d-flex align-items-center">
                            <i class="bi bi-exclamation-triangle-fill fs-4 me-2 icon-gradient-danger"></i>
                            <div>
                                <h6 class="mb-0 fw-bold text-ios-dark">Contratos Vencidos</h6>
                                <small class="text-ios-muted" style="font-size: 0.75rem;">Sin renovar (Últ. 7 días)</small>
                            </div>
                        </div>
                        <div class="card-body p-0">
                            @if(isset($contratosVencidosRecientemente) && $contratosVencidosRecientemente->isNotEmpty())
                                <ul class="list-group list-group-flush rounded-bottom" style="border-radius: 0 0 1.5rem 1.5rem; overflow: hidden;">
                                    @foreach ($contratosVencidosRecientemente as $empleado)
                                        <li class="list-group-item ios-list-item">
                                            <strong class="text-ios-dark" style="font-size: 0.95rem;">{{ $empleado->nombre_completo }}</strong><br>
                                            <div class="d-flex justify-content-between align-items-center mt-1">
                                                <small class="text-ios-muted">
                                                    {{ $empleado->puesto ? $empleado->puesto->nombre_puesto : 'N/A' }}
                                                </small>
                                                <span class="badge bg-danger bg-opacity-10 text-danger rounded-pill border border-danger border-opacity-25 px-2 py-1">
                                                    Venció {{ \Carbon\Carbon::parse($empleado->ultimoContrato->fecha_fin)->format('d/m/y') }}
                                                </span>
                                            </div>
                                        </li>
                                    @endforeach
                                </ul>
                            @else
                                <div class="p-4 text-center">
                                    <i class="bi bi-shield-check text-success fs-3 opacity-50"></i>
                                    <p class="text-ios-muted mb-0 mt-2" style="font-size: 0.85rem;">Todo en orden. No hay contratos vencidos.</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
                @endcan
                
                {{-- Cumpleaños --}}
                @can('ver-widget-cumpleanos')
                <div class="col-md-6 col-lg-4 mb-4">
                    <div class="card ios-card border-0">
                        <div class="ios-card-header d-flex align-items-center">
                            <i class="bi bi-balloon-fill fs-4 me-2 icon-gradient-primary"></i>
                            <div>
                                <h6 class="mb-0 fw-bold text-ios-dark">Cumpleaños</h6>
                                <small class="text-ios-muted" style="font-size: 0.75rem;">Mes de {{ ucfirst(\Carbon\Carbon::now()->translatedFormat('F')) }}</small>
                            </div>
                        </div>
                        <div class="card-body p-0">
                            @if(isset($cumpleanerosDelMes) && $cumpleanerosDelMes->isNotEmpty())
                                <ul class="list-group list-group-flush rounded-bottom" style="border-radius: 0 0 1.5rem 1.5rem; overflow: hidden;">
                                    @foreach ($cumpleanerosDelMes as $empleado)
                                        <li class="list-group-item ios-list-item d-flex justify-content-between align-items-center">
                                            <div>
                                                <strong class="text-ios-dark" style="font-size: 0.95rem;">{{ $empleado->nombre_completo }}</strong>
                                                @if($empleado->esElegibleParaBono)
                                                    <i class="bi bi-gift-fill ms-1 icon-gradient-purple" title="Elegible para bono"></i>
                                                @endif
                                                <br>
                                                <small class="text-ios-muted">{{ $empleado->sucursal ? $empleado->sucursal->nombre_sucursal : 'Sin Sucursal' }}</small>
                                            </div>
                                            <div class="text-center bg-light rounded-3 px-2 py-1 border">
                                                <span class="d-block fw-bold text-primary" style="font-size: 1.1rem; line-height: 1;">{{ \Carbon\Carbon::parse($empleado->fecha_nacimiento)->format('d') }}</span>
                                            </div>
                                        </li>
                                    @endforeach
                                </ul>
                            @else
                                <div class="p-4 text-center">
                                    <i class="bi bi-calendar-minus text-secondary fs-3 opacity-50"></i>
                                    <p class="text-ios-muted mb-0 mt-2" style="font-size: 0.85rem;">No hay cumpleaños este mes.</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
                @endcan

                {{-- Aniversarios --}}
                @can('ver-widget-aniversarios')
                <div class="col-md-6 col-lg-4 mb-4">
                    <div class="card ios-card border-0">
                        <div class="ios-card-header d-flex align-items-center">
                            <i class="bi bi-star-fill fs-4 me-2 icon-gradient-warning"></i>
                            <div>
                                <h6 class="mb-0 fw-bold text-ios-dark">Aniversarios Laborales</h6>
                                <small class="text-ios-muted" style="font-size: 0.75rem;">Mes de {{ ucfirst(\Carbon\Carbon::now()->translatedFormat('F')) }}</small>
                            </div>
                        </div>
                        <div class="card-body p-0">
                            @if(isset($aniversariosDelMes) && $aniversariosDelMes->isNotEmpty())
                                <ul class="list-group list-group-flush rounded-bottom" style="border-radius: 0 0 1.5rem 1.5rem; overflow: hidden;">
                                    @foreach ($aniversariosDelMes as $empleado)
                                        <li class="list-group-item ios-list-item d-flex justify-content-between align-items-center">
                                            <div>
                                                <strong class="text-ios-dark" style="font-size: 0.95rem;">{{ $empleado->nombre_completo }}</strong><br>
                                                <small class="text-ios-muted"><i class="bi bi-calendar-check me-1"></i>{{ \Carbon\Carbon::parse($empleado->fecha_ingreso)->translatedFormat('d M, Y') }}</small>
                                            </div>
                                            <span class="badge bg-warning text-dark rounded-pill shadow-sm px-3 py-2 border border-warning" style="font-size: 0.8rem;">
                                                {{ $empleado->anosCelebrando }} {{ $empleado->anosCelebrando == 1 ? 'año' : 'años' }}
                                            </span>
                                        </li>
                                    @endforeach
                                </ul>
                            @else
                                <div class="p-4 text-center">
                                    <i class="bi bi-star text-secondary fs-3 opacity-50"></i>
                                    <p class="text-ios-muted mb-0 mt-2" style="font-size: 0.85rem;">No hay aniversarios laborales este mes.</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
                @endcan

                {{-- Accesos Rápidos --}}
                @can('ver-widget-accesos-rapidos')
                <div class="col-md-6 col-lg-4 mb-4">
                    <div class="card ios-card border-0">
                        <div class="ios-card-header d-flex align-items-center">
                            <i class="bi bi-lightning-charge-fill fs-4 me-2 icon-gradient-primary"></i>
                            <h6 class="mb-0 fw-bold text-ios-dark">Accesos Rápidos</h6>
                        </div>
                        <div class="card-body p-4 d-flex flex-column gap-3">
                           <a href="{{ route('empleados.create') }}" class="btn btn-ios-primary w-100 text-center"><i class="bi bi-person-plus-fill me-2"></i>Nuevo Empleado</a>
                           <a href="{{ route('contratos.create') }}" class="btn btn-ios-outline w-100 text-center"><i class="bi bi-file-earmark-text me-2"></i>Nuevo Contrato</a>
                        </div>
                    </div>
                </div>
                @endcan

                {{-- IMSS --}}
                @can('ver-widget-imss')
                <div class="col-md-6 col-lg-4 mb-4">
                    <div class="card ios-card border-0">
                        <div class="ios-card-header d-flex align-items-center justify-content-between">
                            <div class="d-flex align-items-center">
                                <i class="bi bi-heart-pulse-fill fs-4 me-2 icon-gradient-success"></i>
                                <div>
                                    <h6 class="mb-0 fw-bold text-ios-dark">IMSS por Patrón</h6>
                                    <small class="text-ios-muted" style="font-size: 0.75rem;">Empleados dados de alta</small>
                                </div>
                            </div>
                        </div>
                        <div class="card-body p-0" style="max-height: 250px; overflow-y: auto;">
                            @if(isset($patronesConteoImss) && count($patronesConteoImss) > 0)
                                <ul class="list-group list-group-flush rounded-bottom">
                                    @foreach($patronesConteoImss as $item)
                                        <li class="list-group-item ios-list-item d-flex justify-content-between align-items-center">
                                            <a href="{{ route('imss.index', ['id_patron_imss_filter' => $item['patron']->id_patron, 'estado_imss_filter' => 'Alta']) }}" class="text-decoration-none fw-semibold text-ios-dark" style="font-size: 0.9rem;">
                                                {{ $item['patron']->razon_social }}
                                            </a>
                                            <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 rounded-pill px-3">{{ $item['conteo_imss_alta'] }}</span>
                                        </li>
                                    @endforeach
                                </ul>
                            @else
                                <div class="p-4 text-center">
                                    <i class="bi bi-shield-x text-secondary fs-3 opacity-50"></i>
                                    <p class="text-ios-muted mb-0 mt-2" style="font-size: 0.85rem;">No hay patrones con empleados de alta en IMSS.</p>
                                </div>
                            @endif
                        </div>
                        <div class="p-3 border-top" style="background-color: rgba(255,255,255,0.5); border-radius: 0 0 1.5rem 1.5rem;">
                            <a href="{{ route('imss.index') }}" class="btn btn-ios-outline btn-sm w-100 text-center">
                                Gestión IMSS
                            </a>
                        </div>
                    </div>
                </div>
                @endcan

                {{-- Gastos Pendientes --}}
                @can('aprobar-gastos')
                <div class="col-md-6 col-lg-4 mb-4">
                    <div class="card ios-card border-0">
                        <div class="ios-card-header d-flex justify-content-between align-items-center">
                            <div class="d-flex align-items-center">
                                <i class="bi bi-hourglass-split fs-4 me-2 icon-gradient-danger"></i>
                                <h6 class="mb-0 fw-bold text-ios-dark">Gastos Pendientes</h6>
                            </div>
                            @if(isset($gastosPendientes) && $gastosPendientes->count() > 0)
                                <span class="badge bg-danger rounded-pill shadow-sm">{{ $gastosPendientes->count() }}</span>
                            @endif
                        </div>
                        <div class="card-body p-0">
                            @if(isset($gastosPendientes) && $gastosPendientes->isNotEmpty())
                                <ul class="list-group list-group-flush rounded-bottom">
                                    @foreach ($gastosPendientes as $gasto)
                                        <li class="list-group-item ios-list-item d-flex justify-content-between align-items-center">
                                            <div>
                                                <strong class="text-ios-dark" style="font-size: 0.9rem;">{{ $gasto->sucursal?->nombre_sucursal ?? 'N/A' }}</strong><br>
                                                <small class="text-ios-muted">
                                                    {{ $gasto->categoria?->nombre ?? 'Sin Categoría' }} - {{ $gasto->fecha_gasto->format('d/m/Y') }}
                                                </small>
                                            </div>
                                            <strong class="text-danger">${{ number_format($gasto->monto_total, 2) }}</strong>
                                        </li>
                                    @endforeach
                                </ul>
                            @else
                                <div class="p-4 text-center">
                                    <i class="bi bi-check-circle-fill text-success fs-3 opacity-50"></i>
                                    <p class="text-ios-muted mt-2 mb-0" style="font-size: 0.85rem;">¡Excelente! No hay gastos por aprobar.</p>
                                </div>
                            @endif
                        </div>
                        <div class="p-3 border-top" style="background-color: rgba(255,255,255,0.5); border-radius: 0 0 1.5rem 1.5rem;">
                            <a href="{{ route('gastos.approvals') }}" class="btn btn-ios-outline btn-sm w-100 text-center">
                                Aprobar Gastos
                            </a>
                        </div>
                    </div>
                </div>
                @endcan

                {{-- Nuevos Ingresos --}}
                @can('ver-widget-nuevos-ingresos')
                <div class="col-md-6 col-lg-4 mb-4">
                    <div class="card ios-card border-0">
                        <div class="ios-card-header d-flex align-items-center">
                            <i class="bi bi-person-plus-fill fs-4 me-2 icon-gradient-success"></i>
                            <div>
                                <h6 class="mb-0 fw-bold text-ios-dark">Nuevos Ingresos</h6>
                                <small class="text-ios-muted" style="font-size: 0.75rem;">{{ $fortnightTitle ?? 'Quincena Actual' }}</small>
                            </div>
                        </div>
                        <div class="card-body p-0">
                            @if(isset($nuevosIngresos) && $nuevosIngresos->isNotEmpty())
                                <ul class="list-group list-group-flush rounded-bottom" style="border-radius: 0 0 1.5rem 1.5rem; overflow: hidden;">
                                    @foreach ($nuevosIngresos as $empleado)
                                        <li class="list-group-item ios-list-item">
                                            <strong class="text-ios-dark" style="font-size: 0.95rem;">{{ $empleado->nombre_completo }}</strong><br>
                                            <div class="d-flex justify-content-between align-items-center mt-1">
                                                <small class="text-ios-muted">
                                                    {{ $empleado->puesto?->nombre_puesto ?? 'N/A' }} <br>
                                                    <i class="bi bi-shop me-1"></i>{{ $empleado->sucursal?->nombre_sucursal ?? 'N/A' }}
                                                </small>
                                                <span class="badge bg-success bg-opacity-10 text-success rounded-pill border border-success border-opacity-25 px-2 py-1">
                                                    {{ \Carbon\Carbon::parse($empleado->fecha_ingreso)->format('d/m/y') }}
                                                </span>
                                            </div>
                                        </li>
                                    @endforeach
                                </ul>
                            @else
                                <div class="p-4 text-center">
                                    <i class="bi bi-person-lines-fill text-secondary fs-3 opacity-50"></i>
                                    <p class="text-ios-muted mb-0 mt-2" style="font-size: 0.85rem;">No hay ingresos recientes esta quincena.</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
                @endcan

                {{-- Bajas --}}
                @can('ver-widget-nuevos-ingresos')
                <div class="col-md-6 col-lg-4 mb-4">
                    <div class="card ios-card border-0">
                        <div class="ios-card-header d-flex align-items-center">
                            <i class="bi bi-person-dash-fill fs-4 me-2 icon-gradient-danger"></i>
                            <div>
                                <h6 class="mb-0 fw-bold text-ios-dark">Bajas Registradas</h6>
                                <small class="text-ios-muted" style="font-size: 0.75rem;">{{ $fortnightTitle ?? 'Quincena Actual' }}</small>
                            </div>
                        </div>
                        <div class="card-body p-0">
                            @if(isset($bajasQuincena) && $bajasQuincena->isNotEmpty())
                                <ul class="list-group list-group-flush rounded-bottom" style="border-radius: 0 0 1.5rem 1.5rem; overflow: hidden;">
                                    @foreach ($bajasQuincena as $empleado)
                                        <li class="list-group-item ios-list-item">
                                            <strong class="text-ios-dark" style="font-size: 0.95rem;">{{ $empleado->nombre_completo }}</strong><br>
                                            <div class="d-flex justify-content-between align-items-center mt-1">
                                                <small class="text-ios-muted">
                                                    {{ $empleado->puesto?->nombre_puesto ?? 'N/A' }} <br>
                                                    <i class="bi bi-shop me-1"></i>{{ $empleado->sucursal?->nombre_sucursal ?? 'N/A' }}
                                                </small>
                                                <span class="badge bg-danger bg-opacity-10 text-danger rounded-pill border border-danger border-opacity-25 px-2 py-1">
                                                    {{ \Carbon\Carbon::parse($empleado->fecha_baja)->format('d/m/y') }}
                                                </span>
                                            </div>
                                        </li>
                                    @endforeach
                                </ul>
                            @else
                                <div class="p-4 text-center">
                                    <i class="bi bi-emoji-smile text-secondary fs-3 opacity-50"></i>
                                    <p class="text-ios-muted mb-0 mt-2" style="font-size: 0.85rem;">No hay bajas registradas esta quincena.</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
                @endcan

            </div> {{-- Fin del Contenedor .row Masonry --}}
        </div>
    </div>

    @push('scripts')
    {{-- Script de la librería Masonry para alinear las tarjetas --}}
    <script src="https://cdn.jsdelivr.net/npm/masonry-layout@4.2.2/dist/masonry.pkgd.min.js" integrity="sha384-GNFwBvfVxBkLMJpYMOABq3c+d3KnQxudP/mGPkzpZSTYykLBNsZEnG2D9G/X/+7D" crossorigin="anonymous" async></script>
    @endpush

</x-app-layout>