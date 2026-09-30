<x-app-layout>
    <style>
        /* Paleta Anti-Deslumbramiento (Estilo macOS / iOS) */
        .bg-system {
            background-color: #f2f2f7 !important; /* Gris base del sistema operativo para descansar la vista */
            min-height: calc(100vh - 60px);
        }

        .ios-card {
            background: #ffffff; /* Blanco solo donde importa */
            border: 1px solid rgba(0,0,0,0.05);
            border-radius: 1.2rem;
            box-shadow: 0 10px 30px rgba(0,0,0,0.04);
        }
        
        .ios-filter-box {
            background-color: #fbfbfd; /* Off-white para matar el brillo */
            border: 1px solid #e5e5ea;
            border-radius: 1rem;
        }

        .ios-table-container {
            border-radius: 1rem;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0,0,0,0.02);
            border: 1px solid #e5e5ea;
            background-color: #ffffff;
        }

        .table-ios {
            margin-bottom: 0;
            border-collapse: separate;
            border-spacing: 0;
        }

        .table-ios thead th {
            background-color: #f5f5f7 !important; /* Gris ultra claro para cabeceras */
            color: #6e6e73 !important; /* Gris en lugar de negro para no fatigar */
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.75rem;
            letter-spacing: 0.5px;
            border-bottom: 1px solid #e5e5ea;
            border-right: 1px solid #f0f0f2;
            padding: 1rem 0.5rem;
        }

        .table-ios tbody td {
            border-bottom: 1px solid #f0f0f2;
            border-right: 1px solid #f0f0f2;
            padding: 0.5rem;
            transition: background-color 0.2s ease;
        }

        .table-ios tbody tr:hover td {
            background-color: #fafafc; /* Hover súper sutil */
        }

        /* Insignias Flotantes (Glassmorphism) */
        .ios-badge-float {
            width: 24px; 
            height: 24px; 
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 4px 8px rgba(0,0,0,0.08);
            border: 1.5px solid rgba(255,255,255,0.8);
            backdrop-filter: blur(4px);
        }

        .badge-birthday {
            background: linear-gradient(135deg, #32d74b 0%, #28cd41 100%);
        }

        .badge-anniversary {
            height: 24px;
            padding: 0 8px;
            background: linear-gradient(135deg, #ff9f0a 0%, #ff8c00 100%);
        }
        
        .badge-newcomer {
            background: linear-gradient(135deg, #0a84ff 0%, #0066cc 100%);
        }

        /* Celdas Interactivas */
        .cell-interactive {
            border-radius: 0.5rem;
            margin: 2px;
            height: calc(100% - 4px);
            transition: all 0.2s cubic-bezier(0.25, 1, 0.5, 1);
        }
        
        .display-mode:hover .cell-interactive:not(.disabled-cell) {
            transform: scale(1.03);
            box-shadow: 0 2px 8px rgba(0,0,0,0.06);
            background-color: #ffffff;
        }

        /* Botones estilo iOS */
        .btn-ios-primary {
            background-color: #0071e3;
            color: white;
            border-radius: 2rem;
            font-weight: 500;
            padding: 0.4rem 1.2rem;
            border: none;
            transition: all 0.2s;
        }
        .btn-ios-primary:hover {
            background-color: #0077ED;
            transform: translateY(-1px);
            box-shadow: 0 4px 10px rgba(0, 113, 227, 0.3);
            color: white;
        }

        .btn-ios-outline {
            background-color: transparent;
            color: #0071e3;
            border-radius: 2rem;
            font-weight: 500;
            padding: 0.4rem 1.2rem;
            border: 1.5px solid #0071e3;
            transition: all 0.2s;
        }
        .btn-ios-outline:hover {
            background-color: rgba(0, 113, 227, 0.05);
            color: #0071e3;
        }

        /* Formularios suaves */
        .form-control-ios, .form-select-ios {
            border-radius: 0.75rem;
            border: 1px solid #d2d2d7;
            background-color: #ffffff;
            padding: 0.5rem 1rem;
            color: #1d1d1f;
        }
        .form-control-ios:focus, .form-select-ios:focus {
            border-color: #0071e3;
            box-shadow: 0 0 0 3px rgba(0, 113, 227, 0.1);
            outline: none;
        }
    </style>

    <div class="container-fluid py-4 bg-system">
        <div class="card ios-card">
            <div class="card-header bg-transparent border-0 d-flex justify-content-between align-items-center pt-4 pb-2 px-4">
                <h4 class="mb-0 fw-bold" style="color: #1d1d1f; letter-spacing: -0.5px;">Control de Asistencias</h4>
                <div class="d-flex gap-2">
                    <button type="button" class="btn-ios-outline shadow-sm" data-bs-toggle="modal" data-bs-target="#modalAsueto">
                        <i class="bi bi-calendar-heart me-1"></i> Asueto
                    </button>
                    <a href="{{ route('asistencia.pre_cierre') }}" class="btn-ios-primary text-decoration-none shadow-sm">
                        <i class="bi bi-shield-check me-1"></i> Pre-Cierre
                    </a>
                    <a href="{{ route('asistencia.resumenIncidencias') }}" class="btn-ios-primary text-decoration-none shadow-sm" style="background-color: #5e5ce6;">
                        <i class="bi bi-file-pdf me-1"></i> PDF
                    </a>
                </div>
            </div>         
            <div class="card-body px-4 pb-4">
                @if (session('success'))
                    <div class="alert alert-success alert-dismissible fade show rounded-4 shadow-sm border-0" role="alert" style="background-color: #e5f9e7; color: #198754;">
                        <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif
                @if (session('error'))
                    <div class="alert alert-danger alert-dismissible fade show rounded-4 shadow-sm border-0" role="alert" style="background-color: #fce8e6; color: #dc3545;">
                        <i class="bi bi-exclamation-circle-fill me-2"></i>{{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                {{-- Formulario de Filtros Suavizado --}}
                <form id="filterForm" method="GET" action="{{ route('asistencia.index') }}" class="mb-4 ios-filter-box p-3 shadow-sm">
                    <div class="row align-items-end g-3 justify-content-center">
                        <div class="col-md-3">
                            <label for="id_sucursal_seleccionada" class="form-label fw-bold mb-1" style="font-size: 0.8rem; color: #86868b;">Sucursal</label>
                            <select class="form-select form-select-ios fw-semibold" id="id_sucursal_seleccionada" name="id_sucursal_seleccionada">
                                <option value="">-- Seleccione --</option>
                                <option value="todas" {{ request('id_sucursal_seleccionada') == 'todas' ? 'selected' : '' }} class="fw-bold text-primary">-- TODAS --</option>
                                @foreach ($sucursales as $sucursal)
                                    <option value="{{ $sucursal->id_sucursal }}" {{ ($id_sucursal_seleccionada ?? '') == $sucursal->id_sucursal ? 'selected' : '' }}>
                                        {{ $sucursal->nombre_sucursal }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label for="tipo_periodo" class="form-label fw-bold mb-1" style="font-size: 0.8rem; color: #86868b;">Visualización</label>
                            <select class="form-select form-select-ios fw-semibold" name="tipo_periodo" id="tipo_periodo">
                                <option value="dia" {{ $tipoPeriodo == 'dia' ? 'selected' : '' }}>Día</option>
                                <option value="semana" {{ $tipoPeriodo == 'semana' ? 'selected' : '' }}>Semana</option>
                                <option value="quincena" {{ $tipoPeriodo == 'quincena' ? 'selected' : '' }}>Quincena</option>
                                <option value="mes" {{ $tipoPeriodo == 'mes' ? 'selected' : '' }}>Mes</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="fecha_ref" class="form-label fw-bold mb-1" style="font-size: 0.8rem; color: #86868b;">Fecha Base</label>
                            <input type="date" name="fecha_ref" id="fecha_ref" class="form-control form-control-ios fw-semibold" value="{{ $fechaReferencia->toDateString() }}">
                        </div>
                        <div class="col-md-2 d-flex align-items-end gap-2">
                            <button type="submit" class="btn-ios-primary flex-fill border-0 py-2 shadow-sm"><i class="bi bi-search"></i> Ver</button>
                            @if(isset($id_sucursal_seleccionada) && $id_sucursal_seleccionada)
                                @php
                                    $params = ['id_sucursal_seleccionada' => $id_sucursal_seleccionada, 'tipo_periodo' => $tipoPeriodo];
                                    $prevDate = $fechaReferencia->copy(); $nextDate = $fechaReferencia->copy();
                                    if($tipoPeriodo == 'semana') { $prevDate->subWeek(); $nextDate->addWeek(); }
                                    elseif($tipoPeriodo == 'quincena') { $prevDate->subDays(15); $nextDate->addDays(15); }
                                    elseif($tipoPeriodo == 'mes') { $prevDate->subMonthNoOverflow(); $nextDate->addMonthNoOverflow(); }
                                    elseif($tipoPeriodo == 'dia') { $prevDate->subDay(); $nextDate->addDay(); }
                                @endphp
                                <div class="btn-group shadow-sm rounded-pill bg-white border border-light">
                                    <a href="{{ route('asistencia.index', array_merge($params, ['fecha_ref' => $prevDate->toDateString()])) }}" class="btn btn-sm btn-light border-0 px-3 rounded-start-pill text-secondary hover-bg-light" title="Anterior"><i class="bi bi-chevron-left"></i></a>
                                    <div class="border-end"></div>
                                    <a href="{{ route('asistencia.index', array_merge($params, ['fecha_ref' => $nextDate->toDateString()])) }}" class="btn btn-sm btn-light border-0 px-3 rounded-end-pill text-secondary hover-bg-light" title="Siguiente"><i class="bi bi-chevron-right"></i></a>
                                </div>
                            @endif
                        </div>
                    </div>
                </form>

                @if(isset($id_sucursal_seleccionada) && $id_sucursal_seleccionada)
                    <div class="d-flex justify-content-between align-items-center mb-3 px-2">
                        <div>
                            <span class="fw-bold" style="color: #86868b; font-size: 0.80rem; text-transform: uppercase; letter-spacing: 0.5px;">Sucursal</span><br>
                            <h5 class="mb-0 fw-bold" style="color: #1d1d1f;">{{ $sucursalSeleccionadaNombre ?? '' }}</h5>
                        </div>
                        <div class="text-end">
                            <span class="badge text-dark rounded-pill px-3 py-2 shadow-sm fw-semibold" style="background-color: #ffffff; border: 1px solid #e5e5ea; font-size: 0.85rem;">
                                <i class="bi bi-calendar3 text-primary me-1"></i> {{ $fechaReferencia->translatedFormat('d \d\e F') }}
                            </span>
                        </div>
                    </div>

                    @if(isset($empleadosDeSucursal) && $empleadosDeSucursal->isNotEmpty() && isset($fechasDelPeriodo) && $fechasDelPeriodo->isNotEmpty())
                        
                        <div class="ios-table-container mx-auto" style="max-width: {{ $tipoPeriodo == 'dia' ? '900px' : '100%' }};">
                            <table class="table table-ios text-center align-middle bg-white">
                                <thead style="position: sticky; top: 0; z-index: 10;">
                                    <tr>
                                        <th style="min-width: 250px; text-align: left; position: sticky; left: 0; z-index: 11; background-color: #fcfcfd;">
                                            <div class="d-flex justify-content-between align-items-center px-2">
                                                <span style="color: #86868b;">Empleado</span>
                                                <button id="btn-mostrar-ocultos" class="btn btn-outline-secondary btn-sm py-0 d-none rounded-pill" onclick="mostrarOcultos()" title="Restaurar ocultos" style="font-size: 0.7rem; border-color: #d2d2d7;">
                                                    <i class="bi bi-eye"></i> <span id="span-contador-ocultos"></span>
                                                </button>
                                            </div>
                                        </th>
                                        @foreach ($fechasDelPeriodo as $fecha)
                                            <th style="min-width: 130px; {{ $fecha->isToday() ? 'background-color: #e5f0ff !important; color: #0071e3 !important;' : '' }}">
                                                <div class="d-flex flex-column align-items-center">
                                                    <span style="font-size: 0.7rem; opacity: 0.7;">{{ strtoupper($fecha->translatedFormat('l')) }}</span>
                                                    <span class="fw-bold" style="font-size: 0.9rem;">{{ $fecha->format('d') }}</span>
                                                </div>
                                            </th>
                                        @endforeach
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($empleadosDeSucursal as $empleado)
                                        <tr id="row_emp_{{ $empleado->id_empleado }}" class="empleado-row" data-id="{{ $empleado->id_empleado }}">
                                            
                                            {{-- CELDA EMPLEADO --}}
                                            <td class="align-middle" style="text-align: left; position: sticky; left: 0; background-color: #fdfdfd; z-index: 1; border-right: 1px solid #e5e5ea;">
                                                <div class="d-flex align-items-center px-2 py-1">
                                                    <i class="bi bi-eye-slash text-muted me-3" style="cursor: pointer; font-size: 1rem; opacity: 0.3; transition: opacity 0.2s;" onmouseover="this.style.opacity=1" onmouseout="this.style.opacity=0.3" onclick="ocultarEmpleado({{ $empleado->id_empleado }})" title="Ocultar empleado"></i>
                                                    
                                                    <div>
                                                        <span class="fw-bold d-block" style="color: #1d1d1f; font-size: 0.9rem; letter-spacing: -0.2px;">{{ $empleado->nombre_completo }}</span>
                                                        
                                                        @if($tipoPeriodo == 'dia')
                                                            @if($id_sucursal_seleccionada === 'todas')
                                                                <span class="text-muted me-2" style="font-size: 0.7em; font-weight: 500;">
                                                                    <i class="bi bi-shop opacity-50"></i> {{ $empleado->sucursal->nombre_sucursal ?? 'S/S' }}
                                                                </span>
                                                            @endif
                                                            <span class="fw-semibold" style="color: #86868b; font-size: 0.7em;">
                                                                {{ $empleado->puesto->nombre_puesto ?? 'General' }}
                                                            </span>
                                                        @else
                                                            @if($id_sucursal_seleccionada === 'todas')
                                                                <span class="text-muted" style="font-size: 0.7em; font-weight: 500;">
                                                                    <i class="bi bi-shop opacity-50"></i> {{ $empleado->sucursal->nombre_sucursal ?? 'S/S' }}
                                                                </span>
                                                            @else
                                                                <span class="fw-semibold" style="color: #86868b; font-size: 0.7em;">
                                                                    {{ $empleado->puesto->nombre_puesto ?? 'General' }}
                                                                </span>
                                                            @endif
                                                        @endif
                                                    </div>
                                                </div>
                                            </td>

                                            {{-- CELDAS DE DÍAS --}}
                                            @foreach ($fechasDelPeriodo as $fecha)
                                                @php
                                                    $fechaString = $fecha->toDateString();
                                                    $asistenciaDia = $asistenciaProcesada->get($empleado->id_empleado, collect())->get($fechaString);
                                                    
                                                    $mapaDias = [1 => 'lunes', 2 => 'martes', 3 => 'miercoles', 4 => 'jueves', 5 => 'viernes', 6 => 'sabado', 7 => 'domingo'];
                                                    $nombreDia = $mapaDias[$fecha->dayOfWeekIso];
                                                    $esLaborable = $empleado->horario ? $empleado->horario->{$nombreDia} : true;
                                                    
                                                    $asuetoDia = isset($asuetos) ? $asuetos->first(function($a) use ($fecha, $empleado) {
                                                        $aplicaSucursal = is_null($a->id_sucursal) || $a->id_sucursal == $empleado->id_sucursal;
                                                        $inicio = \Carbon\Carbon::parse($a->fecha_inicio)->startOfDay();
                                                        $fin = \Carbon\Carbon::parse($a->fecha_fin)->endOfDay();
                                                        return $aplicaSucursal && $fecha->between($inicio, $fin);
                                                    }) : null;

                                                    $vacsEmpleado = isset($vacaciones) ? $vacaciones->get($empleado->id_empleado, collect()) : collect();
                                                    $vacacionDia = $vacsEmpleado->first(function($v) use ($fecha) {
                                                        $inicio = \Carbon\Carbon::parse($v->fecha_inicio)->startOfDay();
                                                        $fin = \Carbon\Carbon::parse($v->fecha_fin)->endOfDay();
                                                        return $fecha->between($inicio, $fin);
                                                    });

                                                    $esCumple = false; $esAniversario = false; $esIngreso = false; $esAntesDeIngreso = false; $aniosAniversario = 0;

                                                    if (!empty($empleado->fecha_nacimiento)) {
                                                        $nac = \Carbon\Carbon::parse($empleado->fecha_nacimiento);
                                                        $esCumple = ($nac->month == $fecha->month && $nac->day == $fecha->day);
                                                    }

                                                    if (!empty($empleado->fecha_ingreso)) {
                                                        $ing = \Carbon\Carbon::parse($empleado->fecha_ingreso)->startOfDay();
                                                        $fechaDiaActual = $fecha->copy()->startOfDay();
                                                        if ($fechaDiaActual->lessThan($ing)) $esAntesDeIngreso = true;
                                                        elseif ($fechaDiaActual->equalTo($ing)) $esIngreso = true;
                                                        if ($ing->month == $fecha->month && $ing->day == $fecha->day) {
                                                            $aniosAniversario = $fecha->year - $ing->year;
                                                            $esAniversario = ($aniosAniversario > 0);
                                                        }
                                                    }

                                                    if ($asistenciaDia && $asistenciaDia->status_asistencia === 'Falta' && ($asuetoDia || $vacacionDia)) {
                                                        $asistenciaDia = null; 
                                                    }

                                                    // Colores de Fondo Suaves y Mate
                                                    $bgStyle = '';
                                                    $textClass = 'text-dark';
                                                    if ($esAntesDeIngreso) {
                                                        $bgStyle = 'background-color: #f2f2f7; opacity: 0.6;'; // Gris sistema
                                                    } elseif ($asistenciaDia) {
                                                        switch ($asistenciaDia->status_asistencia) {
                                                            case 'Retardo': $bgStyle = 'background-color: #fff8e6;'; $textClass = 'text-warning-emphasis'; break;
                                                            case 'Falta': $bgStyle = 'background-color: #ffeef0;'; $textClass = 'text-danger'; break;
                                                            case 'Baja_Dia': $bgStyle = 'background-color: #f2f2f7;'; $textClass = 'text-muted'; break;
                                                            case 'Incidencia': $bgStyle = 'background-color: #e5f0ff;'; $textClass = 'text-primary'; break;
                                                        }
                                                    } elseif ($asuetoDia) {
                                                        $bgStyle = 'background-color: #f0ebf8;'; $textClass = 'text-purple'; 
                                                    } elseif ($vacacionDia) {
                                                        $bgStyle = 'background-color: #e6f6eb;'; $textClass = 'text-success';
                                                    } elseif (!$esLaborable) {
                                                        $bgStyle = 'background-color: #f5f5f7;'; $textClass = 'text-secondary';
                                                    }
                                                    
                                                    $estadoActualForm = $asistenciaDia ? ($asistenciaDia->status_asistencia == 'Retardo' ? 'Presente' : $asistenciaDia->status_asistencia) : 'Presente';
                                                @endphp
                                                
                                                <td class="p-1 position-relative" style="height: 50px;">
                                                    <div class="cell-interactive w-100 h-100 position-relative {{ $esAntesDeIngreso ? 'disabled-cell' : '' }}" style="{{ $bgStyle }}">
                                                    
                                                        {{-- PINES FLOTANTES --}}
                                                        @if($esIngreso)
                                                            <div class="position-absolute top-0 end-0 mt-1 me-1" style="z-index: 5; pointer-events: none;" title="Día de Ingreso">
                                                                <div class="ios-badge-float badge-newcomer rounded-circle">
                                                                    <i class="bi bi-briefcase-fill text-white" style="font-size: 0.75rem;"></i>
                                                                </div>
                                                            </div>
                                                        @endif

                                                        @if($esCumple)
                                                            <div class="position-absolute top-0 start-0 mt-1 ms-1" style="z-index: 5; pointer-events: none;" title="¡Feliz Cumpleaños!">
                                                                <div class="ios-badge-float badge-birthday rounded-circle">
                                                                    <i class="bi bi-balloon-fill text-white" style="font-size: 0.80rem; margin-top: 1px;"></i>
                                                                </div>
                                                            </div>
                                                        @endif
                                                        
                                                        @if($esAniversario)
                                                            <div class="position-absolute bottom-0 start-0 mb-1 ms-1" style="z-index: 5; pointer-events: none;" title="¡{{ $aniosAniversario }}º Aniversario!">
                                                                <div class="ios-badge-float badge-anniversary rounded-pill">
                                                                    <i class="bi bi-star-fill text-white me-1" style="font-size: 0.65rem;"></i>
                                                                    <span class="fw-bold text-white" style="font-size: 0.7rem; font-family: -apple-system, BlinkMacSystemFont, sans-serif;">{{ $aniosAniversario }}</span>
                                                                </div>
                                                            </div>
                                                        @endif
                                                        
                                                        {{-- CONTENIDO CENTRAL --}}
                                                        <div class="display-mode w-100 h-100 d-flex flex-column align-items-center justify-content-center" style="{{ $esAntesDeIngreso ? 'cursor: not-allowed;' : 'cursor: pointer;' }}" {!! !$esAntesDeIngreso ? 'onclick="activarEdicion(this)"' : '' !!}>
                                                            
                                                            @if ($esAntesDeIngreso)
                                                                <i class="bi bi-dash text-secondary opacity-25" style="font-size: 1.2rem;"></i>
                                                            
                                                            @elseif ($asistenciaDia)
                                                                @if (in_array($asistenciaDia->status_asistencia, ['Presente', 'Retardo']))
                                                                    <span class="fw-bold {{ $textClass }}" style="font-size: 0.9rem; font-family: -apple-system, sans-serif;">
                                                                        {{ \Carbon\Carbon::parse($asistenciaDia->hora_llegada)->format('H:i') }}
                                                                    </span>
                                                                    @if($asistenciaDia->status_asistencia == 'Retardo') 
                                                                        <span class="{{ $textClass }}" style="font-size: 0.65rem; font-weight: 600;">Retardo</span> 
                                                                    @endif
                                                                @elseif ($asistenciaDia->status_asistencia == 'Falta')
                                                                    <span class="fw-bold {{ $textClass }}" style="font-size: 0.85rem; letter-spacing: 0.5px;">FALTA</span>
                                                                @elseif ($asistenciaDia->status_asistencia == 'Baja_Dia')
                                                                    <span class="fw-semibold {{ $textClass }}" style="font-size: 0.8rem;">BAJA</span>
                                                                @elseif ($asistenciaDia->status_asistencia == 'Incidencia')
                                                                    @if($asistenciaDia->hora_llegada) 
                                                                        <strong class="{{ $textClass }}" style="font-size: 0.85rem;">{{ \Carbon\Carbon::parse($asistenciaDia->hora_llegada)->format('H:i') }}</strong>
                                                                    @endif 
                                                                    <span class="{{ $textClass }} fw-semibold text-truncate w-100 px-1 text-center" style="font-size: 0.65rem; max-width: 100px;" title="{{ $asistenciaDia->notas_incidencia }}">
                                                                        {{ $asistenciaDia->notas_incidencia ?: 'INCID.' }}
                                                                    </span>
                                                                @endif
                                                            
                                                            @else
                                                                @if($asuetoDia)
                                                                    <span class="badge rounded-pill fw-bold" style="background-color: #ab8ed8; color: white; font-size: 0.65rem; padding: 0.35em 0.6em;" title="{{ $asuetoDia->nombre }}">
                                                                        <i class="bi bi-cup-hot-fill me-1"></i> ASUETO
                                                                    </span>
                                                                @elseif($vacacionDia)
                                                                    <span class="badge rounded-pill fw-bold" style="background-color: #34c759; color: white; font-size: 0.65rem; padding: 0.35em 0.6em;" title="Periodo Vacacional">
                                                                        <i class="bi bi-airplane-fill me-1"></i> VACAC.
                                                                    </span>
                                                                @elseif(!$esLaborable)
                                                                    <span class="fw-bold" style="color: #c7c7cc; font-size: 0.7rem; letter-spacing: 0.5px;">DESC</span>
                                                                @else
                                                                    <i class="bi bi-plus-circle text-primary opacity-25" style="font-size: 1.1rem; transition: opacity 0.2s;" onmouseover="this.style.opacity=0.8" onmouseout="this.style.opacity=0.25"></i>
                                                                @endif
                                                            @endif
                                                        </div>

                                                        {{-- MODO EDICIÓN --}}
                                                        @if(!$esAntesDeIngreso)
                                                            <div class="edit-mode d-none position-absolute top-0 start-0 w-100 h-100 bg-white shadow rounded-3 z-3 d-flex align-items-center justify-content-center p-1" style="transform: scale(1.1); border: 1px solid #e5e5ea;">
                                                                <form method="POST" action="{{ route('asistencia.registrarEntrada') }}" class="w-100 d-flex flex-column align-items-center" onsubmit="prepararHoraAntesDeEnviar(this)">
                                                                    @csrf
                                                                    <input type="hidden" name="id_empleado" value="{{ $empleado->id_empleado }}">
                                                                    <input type="hidden" name="fecha_registro" value="{{ $fechaString }}">
                                                                    <input type="hidden" name="id_sucursal_seleccionada" value="{{ $id_sucursal_seleccionada }}">

                                                                    <select name="status_asistencia" class="form-select border-0 bg-light fw-bold text-center mb-1" style="font-size: 0.7rem; padding: 2px 10px; height: auto; border-radius: 4px; color: #1d1d1f;" data-periodo="{{ $tipoPeriodo }}" onchange="manejarCambioEstado(this)">
                                                                        <option value="Presente" {{ $estadoActualForm == 'Presente' ? 'selected' : '' }}>ASIST</option>
                                                                        <option value="Falta" {{ $estadoActualForm == 'Falta' ? 'selected' : '' }}>FALTA</option>
                                                                        <option value="Incidencia" {{ $estadoActualForm == 'Incidencia' ? 'selected' : '' }}>INCID</option>
                                                                        <option value="Baja_Dia" {{ $estadoActualForm == 'Baja_Dia' ? 'selected' : '' }}>BAJA</option>
                                                                    </select>

                                                                    <input type="text" name="hora_llegada_manual" class="form-control border-0 bg-light text-center fw-bold mb-1" style="font-size: 0.8rem; padding: 2px; height: auto; border-radius: 4px; color: #1d1d1f;" placeholder="HH:MM" maxlength="5" oninput="formatearHoraAuto(this)" value="{{ $asistenciaDia && $asistenciaDia->hora_llegada ? \Carbon\Carbon::parse($asistenciaDia->hora_llegada)->format('H:i') : '' }}">
                                                                    <input type="text" name="notas_incidencia" class="form-control border-0 bg-light text-center mb-1" style="font-size: 0.7rem; padding: 2px; height: auto; border-radius: 4px;" placeholder="Nota" value="{{ $asistenciaDia && $asistenciaDia->status_asistencia == 'Incidencia' ? $asistenciaDia->notas_incidencia : '' }}">

                                                                    <div class="d-flex w-100 px-1 gap-1">
                                                                        <button type="submit" class="btn btn-primary flex-fill rounded-1 py-0 border-0" style="height: 20px; background-color: #0071e3;"><i class="bi bi-check" style="font-size: 0.8rem; line-height: 0;"></i></button>
                                                                        <button type="button" class="btn btn-light flex-fill rounded-1 py-0 border" style="height: 20px;" onclick="cancelarEdicion(this)"><i class="bi bi-x" style="font-size: 0.8rem; line-height: 0;"></i></button>
                                                                    </div>
                                                                </form>
                                                            </div>
                                                        @endif

                                                    </div>
                                                </td>
                                            @endforeach
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="bi bi-inbox text-muted opacity-25" style="font-size: 3rem;"></i>
                            <p class="text-muted mt-2 fw-semibold" style="color: #86868b;">No hay datos de asistencia para mostrar.</p>
                        </div>
                    @endif
                @else
                    <div class="text-center py-5">
                        <i class="bi bi-shop text-primary opacity-25" style="font-size: 3rem;"></i>
                        <p class="text-muted mt-2 fw-semibold" style="color: #86868b;">Seleccione una sucursal para visualizar la asistencia.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Modal Asueto -->
    <div class="modal fade" id="modalAsueto" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <form method="POST" action="{{ route('asistencia.guardar_asueto') }}" class="modal-content border-0 shadow-lg" style="border-radius: 1.2rem; background-color: rgba(255,255,255,0.95); backdrop-filter: blur(10px);">
                @csrf
                <div class="modal-header border-0 px-4 pt-4 pb-3">
                    <h5 class="modal-title fw-bold" style="color: #1d1d1f;"><i class="bi bi-calendar-heart text-primary me-2"></i> Registrar Asueto</h5>
                    <button type="button" class="btn-close bg-light rounded-circle shadow-sm" data-bs-dismiss="modal" aria-label="Close" style="padding: 0.5rem;"></button>
                </div>
                <div class="modal-body px-4 pb-4">
                    <div class="mb-3">
                        <label class="form-label fw-bold mb-1" style="font-size: 0.85rem; color: #86868b;">Motivo del Asueto</label>
                        <input type="text" name="nombre" class="form-control form-control-ios bg-light" placeholder="Ej. Día de la Independencia" required>
                    </div>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold mb-1" style="font-size: 0.85rem; color: #86868b;">Fecha Inicio</label>
                            <input type="date" name="fecha_inicio" class="form-control form-control-ios bg-light" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold mb-1" style="font-size: 0.85rem; color: #86868b;">Fecha Fin</label>
                            <input type="date" name="fecha_fin" class="form-control form-control-ios bg-light" required>
                            <small class="text-muted" style="font-size: 0.7em;">(Misma fecha si es un solo día)</small>
                        </div>
                    </div>
                    <div class="mt-3">
                        <label class="form-label fw-bold mb-1" style="font-size: 0.85rem; color: #86868b;">Aplica para Sucursal</label>
                        <select name="id_sucursal" class="form-select form-select-ios bg-light" required>
                            <option value="todas">-- TODAS LAS SUCURSALES --</option>
                            @foreach ($sucursales as $sucursal)
                                <option value="{{ $sucursal->id_sucursal }}">{{ $sucursal->nombre_sucursal }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="modal-footer border-0 px-4 pb-4 pt-0">
                    <button type="button" class="btn fw-semibold border-0" style="color: #86868b;" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn-ios-primary fw-bold px-4 shadow-sm">Guardar Asueto</button>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
        <script>
        function activarEdicion(divVista) {
            document.querySelectorAll('.edit-mode').forEach(el => el.classList.add('d-none'));
            document.querySelectorAll('.display-mode').forEach(el => el.classList.remove('d-none'));

            let celda = divVista.closest('td');
            celda.querySelector('.display-mode').classList.add('d-none');
            let editMode = celda.querySelector('.edit-mode');
            if (editMode) {
                editMode.classList.remove('d-none');
                let select = editMode.querySelector('select');
                manejarCambioEstado(select);
                setTimeout(() => editMode.querySelector('.input-hora').focus(), 50);
            }
        }

        function cancelarEdicion(btnCancelar) {
            let celda = btnCancelar.closest('td');
            celda.querySelector('.edit-mode').classList.add('d-none');
            celda.querySelector('.display-mode').classList.remove('d-none');
        }

        function manejarCambioEstado(selectElement) {
            let form = selectElement.closest('form');
            let inputHora = form.querySelector('.input-hora');
            let inputNotas = form.querySelector('.input-notas');

            if (selectElement.value === 'Presente') {
                inputHora.style.setProperty('display', 'block', 'important');
                inputHora.required = true;
                inputNotas.style.setProperty('display', 'none', 'important');
                inputNotas.required = false;
            } else if (selectElement.value === 'Incidencia') {
                inputHora.style.setProperty('display', 'block', 'important');
                inputHora.required = false; 
                inputNotas.style.setProperty('display', 'block', 'important');
                inputNotas.required = true;
            } else {
                inputHora.style.setProperty('display', 'none', 'important');
                inputHora.required = false;
                inputNotas.style.setProperty('display', 'none', 'important');
                inputNotas.required = false;
            }
        }

        function formatearHoraAuto(input) {
            let valor = input.value.replace(/\D/g, '');
            if (valor.length > 2) {
                valor = valor.substring(0, 2) + ':' + valor.substring(2, 4);
            }
            input.value = valor;
        }

        function prepararHoraAntesDeEnviar(form) {
            let inputHora = form.querySelector('.input-hora');
            if (inputHora.style.display !== 'none' && inputHora.value && inputHora.value.length < 5) {
                let numericos = inputHora.value.replace(':', '');
                if(numericos.length === 3) {
                    inputHora.value = '0' + numericos.substring(0,1) + ':' + numericos.substring(1,3);
                }
            }
        }

        const storageKey = 'empleados_ocultos_asistencia';

        document.addEventListener('DOMContentLoaded', function () {
            initOcultos();
        });

        function initOcultos() {
            let ocultos = JSON.parse(localStorage.getItem(storageKey)) || [];
            let count = 0;
            document.querySelectorAll('.empleado-row').forEach(row => {
                let id = parseInt(row.getAttribute('data-id'));
                if (ocultos.includes(id)) {
                    row.classList.add('d-none');
                    count++;
                }
            });
            actualizarBotonOcultos(count);
        }

        function ocultarEmpleado(id) {
            let ocultos = JSON.parse(localStorage.getItem(storageKey)) || [];
            if (!ocultos.includes(id)) {
                ocultos.push(id);
                localStorage.setItem(storageKey, JSON.stringify(ocultos));
            }
            let row = document.getElementById('row_emp_' + id);
            if (row) {
                row.classList.add('d-none');
            }
            
            let count = document.querySelectorAll('.empleado-row.d-none').length;
            actualizarBotonOcultos(count);
        }

        function mostrarOcultos() {
            localStorage.removeItem(storageKey);
            document.querySelectorAll('.empleado-row').forEach(row => {
                row.classList.remove('d-none');
            });
            actualizarBotonOcultos(0);
        }

        function actualizarBotonOcultos(count) {
            let btn = document.getElementById('btn-mostrar-ocultos');
            let span = document.getElementById('span-contador-ocultos');
            if (count > 0) {
                btn.classList.remove('d-none');
                span.innerText = count;
            } else {
                btn.classList.add('d-none');
            }
        }
        </script>
    @endpush
</x-app-layout>