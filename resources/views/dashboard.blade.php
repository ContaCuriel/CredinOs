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
                
                {{-- --- INICIO DE LA CORRECCIÓN DEL WIDGET DE CUMPLEAÑOS --- --}}
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
                {{-- --- FIN DE LA CORRECCIÓN --- --}}

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
                    <div class="card h-100">
                        <div class="card-header"><i class="bi bi-person-plus-fill"></i> Nuevos Ingresos ({{ $fortnightTitle ?? 'Quincena Actual' }})</div>
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
                                <p class="text-muted mb-0">No hay nuevos ingresos en esta quincena.</p>
                            @endif
                        </div>
                    </div>
                </div>
                @endcan

            </div> {{-- Fin del Contenedor .row --}}
        </div>
    </div>

    @push('scripts')
    {{-- Script de la librería Masonry --}}
    <script src="https://cdn.jsdelivr.net/npm/masonry-layout@4.2.2/dist/masonry.pkgd.min.js" xintegrity="sha384-GNFwBvfVxBkLMJpYMOABq3c+d3KnQxudP/mGPkzpZSTYykLBNsZEnG2D9G/X/+7D" crossorigin="anonymous" async></script>
    @endpush

</x-app-layout>

