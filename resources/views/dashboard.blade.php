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

            <div class="row">
                {{-- Tarjeta Contratos por Vencer (Placeholder) --}}
                <div class="col-md-6 col-lg-4 mb-3">
                <div class="card">
                    <div class="card-header"><i class="bi bi-calendar-x"></i> Contratos por Vencer (Próx. 15 días)</div>
                    <div class="card-body">
                        {{-- Verificamos primero si la variable existe y si tiene datos --}}
                        @if(isset($contratosPorVencer) && $contratosPorVencer->isNotEmpty())
                            <ul class="list-group list-group-flush">
                                @foreach ($contratosPorVencer as $contrato)
                                    <li class="list-group-item">
                                        <strong>{{ $contrato->empleado->nombre_completo }}</strong><br>
                                        <small>
                                            Puesto: {{ $contrato->empleado->puesto ? $contrato->empleado->puesto->nombre_puesto : 'N/A' }} <br>
                                            Sucursal: {{ $contrato->empleado->sucursal ? $contrato->empleado->sucursal->nombre_sucursal : 'N/A' }} <br>
                                            Vence: <strong>{{ $contrato->fecha_fin->format('d/m/Y') }}</strong>
                                            ({{-- Calculamos el tiempo restante de forma legible --}}
                                            ({{ $contrato->fecha_fin->diffForHumans(now()->startOfDay(), true, false, 2) }} para vencer)
                                        </small>
                                    </li>
                                @endforeach
                            </ul>
                        @else
                            <p class="text-muted mb-0">No hay contratos próximos a vencer en los siguientes 15 días.</p>
                        @endif
                    </div>
                </div>
            </div>

                {{-- Tarjeta Cumpleaños del Mes --}}
                <div class="col-md-6 col-lg-4 mb-3">
                    <div class="card">
                        <div class="card-header"><i class="bi bi-cake2"></i> Cumpleaños del Mes ({{ ucfirst(\Carbon\Carbon::now()->translatedFormat('F')) }})</div>
                        <div class="card-body">
                            @if($cumpleanerosDelMes->isNotEmpty())
                                <ul class="list-group list-group-flush">
                                    @foreach ($cumpleanerosDelMes as $empleado)
                                        <li class="list-group-item">
                                            {{ $empleado->nombre_completo }} 
                                            ({{ \Carbon\Carbon::parse($empleado->fecha_nacimiento)->format('d') }})
                                        </li>
                                    @endforeach
                                </ul>
                            @else
                                <p class="text-muted mb-0">No hay cumpleaños este mes.</p>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Tarjeta Aniversarios Laborales del Mes --}}
                <div class="col-md-6 col-lg-4 mb-3">
                    <div class="card">
                        <div class="card-header"><i class="bi bi-award"></i> Aniversarios Laborales del Mes ({{ ucfirst(\Carbon\Carbon::now()->translatedFormat('F')) }})</div>
                        <div class="card-body">
                            @if($aniversariosDelMes->isNotEmpty())
                                <ul class="list-group list-group-flush">
                                    @foreach ($aniversariosDelMes as $empleado)
                                        @php
                                            $fechaIngreso = \Carbon\Carbon::parse($empleado->fecha_ingreso);
                                            // Calculamos el número de aniversario que celebra este año
                                            $anosCelebrando = \Carbon\Carbon::now()->year - $fechaIngreso->year;
                                            // Si el mes y día del aniversario aún no llegan este año, es el aniversario anterior.
                                            // Esto es para el texto "cumple X años", el filtro ya nos da los del mes.
                                            // Si el aniversario ya pasó este mes, $anosCelebrando es correcto.
                                            // Si el aniversario es más adelante en el mes, $anosCelebrando también es correcto para "cumple X años".
                                        @endphp
                                        <li class="list-group-item">
                                            {{ $empleado->nombre_completo }} 
                                            ({{ $fechaIngreso->format('d M') }})
                                            - Cumple {{ $anosCelebrando }}
                                            {{ $anosCelebrando == 1 ? 'año' : 'años' }}
                                        </li>
                                    @endforeach
                                </ul>
                            @else
                                <p class="text-muted mb-0">No hay aniversarios laborales este mes.</p>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Tarjeta Accesos Rápidos (Placeholder) --}}
                <div class="col-md-6 col-lg-4 mb-3">
                     <div class="card">
                        <div class="card-header"><i class="bi bi-lightning-charge"></i> Accesos Rápidos</div>
                        <div class="card-body">
                           <a href="{{ route('empleados.create') }}" class="btn btn-success mb-2 w-100">Nuevo Empleado</a>
                           {{-- Dentro de la tarjeta "Accesos Rápidos" --}}
<a href="{{ route('contratos.create') }}" class="btn btn-info w-100">Nuevo Contrato</a>
                        </div>
                    </div>
                </div>

{{-- Nuevo Widget: Estado de Empleados en IMSS por Patrón --}}
<div class="col-lg-4 mb-xl-0 mb-4"> {{-- Ajusta el tamaño de columna (ej: col-lg-4) como necesites --}}
    <div class="card">
        <div class="card-header p-3 pt-2">
            <div class="icon icon-lg icon-shape bg-gradient-info shadow-info text-center border-radius-xl mt-n4 position-absolute">
                <i class="bi bi-shield-check opacity-10"></i> {{-- Icono de ejemplo --}}
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

            </div> {{-- Fin de la Fila --}}
        </div>
    </div>
</x-app-layout>