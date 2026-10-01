<x-app-layout>
    <style>
        /* Fondo animado MUY sutil para matar el brillo sin distraer */
        .bg-system-islands {
            background: linear-gradient(-45deg, #f0f4fd, #f4f0fa, #f0faf5, #f5f8fc);
            background-size: 400% 400%;
            animation: subtleMesh 20s ease infinite;
            min-height: calc(100vh - 60px);
            padding-bottom: 3rem;
        }

        @keyframes subtleMesh {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }

        /* Contenedores tipo Isla (Island UI) */
        .ios-island {
            background: rgba(255, 255, 255, 0.75);
            backdrop-filter: blur(24px);
            -webkit-backdrop-filter: blur(24px);
            border: 1px solid rgba(255, 255, 255, 0.8);
            border-radius: 1.5rem;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.03);
            padding: 1.5rem;
        }

        /* Tabla flotante */
        .ios-table-container {
            background: rgba(255, 255, 255, 0.85);
            backdrop-filter: blur(24px);
            -webkit-backdrop-filter: blur(24px);
            border-radius: 1.5rem;
            overflow: visible; /* IMPORTANTE: Permitir que el popup salga de la tabla */
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.03);
            border: 1px solid rgba(255, 255, 255, 0.9);
        }

        .table-ios {
            margin-bottom: 0;
            border-collapse: separate;
            border-spacing: 0;
        }

        .table-ios thead th {
            background-color: rgba(245, 245, 247, 0.6) !important;
            color: #6e6e73 !important;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.75rem;
            letter-spacing: 0.5px;
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
            border-right: 1px solid rgba(0, 0, 0, 0.03);
            padding: 1rem 0.5rem;
        }

        .table-ios tbody td {
            border-bottom: 1px solid rgba(0, 0, 0, 0.03);
            border-right: 1px solid rgba(0, 0, 0, 0.03);
            padding: 0.5rem;
            transition: background-color 0.2s ease;
            background-color: transparent;
        }

        .table-ios tbody tr:hover td {
            background-color: rgba(255, 255, 255, 0.5);
        }

        /* Insignias Flotantes */
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

        .badge-birthday { background: linear-gradient(135deg, #32d74b 0%, #28cd41 100%); }
        .badge-anniversary { height: 24px; padding: 0 8px; background: linear-gradient(135deg, #ff9f0a 0%, #ff8c00 100%); }
        .badge-newcomer { background: linear-gradient(135deg, #0a84ff 0%, #0066cc 100%); }

        /* Celdas Interactivas */
        .cell-interactive {
            border-radius: 0.75rem;
            margin: 2px;
            height: calc(100% - 4px);
            transition: all 0.2s cubic-bezier(0.25, 1, 0.5, 1);
        }
        
        .display-mode:hover .cell-interactive:not(.disabled-cell) {
            transform: scale(1.05);
            box-shadow: 0 4px 12px rgba(0,0,0,0.06);
            background-color: #ffffff !important;
            z-index: 10;
        }

        /* CLASE PARA SUPERPONER LA CELDA AL EDITAR */
        .cell-editing-active {
            z-index: 9999 !important;
        }

        /* Botones estilo iOS */
        .btn-ios-primary {
            background-color: #0071e3;
            color: white;
            border-radius: 2rem;
            font-weight: 500;
            padding: 0.5rem 1.2rem;
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
            background-color: rgba(255, 255, 255, 0.5);
            color: #0071e3;
            border-radius: 2rem;
            font-weight: 600;
            padding: 0.5rem 1.2rem;
            border: 1px solid rgba(0, 113, 227, 0.3);
            transition: all 0.2s;
        }
        .btn-ios-outline:hover {
            background-color: rgba(0, 113, 227, 0.1);
            color: #0071e3;
            border-color: #0071e3;
        }

        /* Formularios suaves */
        .form-control-ios, .form-select-ios {
            border-radius: 0.75rem;
            border: 1px solid rgba(0,0,0,0.1);
            background-color: rgba(255, 255, 255, 0.8);
            padding: 0.5rem 1rem;
            color: #1d1d1f;
            transition: all 0.3s ease;
        }
        .form-control-ios:focus, .form-select-ios:focus {
            background-color: #ffffff;
            border-color: #0071e3;
            box-shadow: 0 0 0 3px rgba(0, 113, 227, 0.15);
            outline: none;
        }

        /* Tooltip informativo (Popover) */
        .popover {
            border-radius: 1rem;
            border: 1px solid rgba(0,0,0,0.1);
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }
        .popover-header {
            background-color: #f8f9fa;
            border-bottom: 1px solid rgba(0,0,0,0.05);
            font-weight: 700;
            border-radius: 1rem 1rem 0 0;
        }
    </style>

    <div class="container-fluid pt-4 px-4 bg-system-islands">
        
        {{-- HEADER PRINCIPAL TRANSPARENTE --}}
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h3 class="mb-0 fw-bold" style="color: #1d1d1f; letter-spacing: -0.5px;">Control de Asistencias</h3>
            <div class="d-flex gap-2">
                
                {{-- BOTÓN PARA CAPTURAR LA TABLA COMO IMAGEN --}}
                @if(isset($empleadosDeSucursal) && $empleadosDeSucursal->isNotEmpty())
                    <button type="button" class="btn-ios-outline shadow-sm text-dark border-secondary" id="btn-capturar" onclick="capturarTabla()">
                        <i class="bi bi-camera me-1"></i> Capturar
                    </button>
                @endif

                {{-- BOTÓN DE VACACIONES --}}
                <button type="button" class="btn-ios-outline shadow-sm" data-bs-toggle="modal" data-bs-target="#modalVacaciones" style="color: #198754; border-color: rgba(25,135,84,0.3);">
                    <i class="bi bi-airplane-fill me-1"></i> Vacaciones
                </button>

                {{-- BOTÓN DE ASUETO --}}
                <button type="button" class="btn-ios-outline shadow-sm" data-bs-toggle="modal" data-bs-target="#modalAsueto" style="color: #6f42c1; border-color: rgba(111,66,193,0.3);">
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

        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show ios-island mb-4 py-3 border-0 d-flex align-items-center" role="alert" style="background-color: rgba(229, 249, 231, 0.85); color: #198754;">
                <i class="bi bi-check-circle-fill me-2 fs-5"></i>{{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif
        @if (session('error'))
            <div class="alert alert-danger alert-dismissible fade show ios-island mb-4 py-3 border-0 d-flex align-items-center" role="alert" style="background-color: rgba(252, 232, 230, 0.85); color: #dc3545;">
                <i class="bi bi-exclamation-circle-fill me-2 fs-5"></i>{{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif
        @if ($errors->any())
            <div class="alert alert-danger alert-dismissible fade show ios-island mb-4 py-3 border-0" role="alert" style="background-color: rgba(252, 232, 230, 0.85); color: #dc3545;">
                <div class="d-flex align-items-center mb-2">
                    <i class="bi bi-exclamation-octagon-fill me-2 fs-5"></i>
                    <strong>Por favor corrige los siguientes errores:</strong>
                </div>
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        {{-- ISLA 1: FORMULARIO DE FILTROS --}}
        <form id="filterForm" method="GET" action="{{ route('asistencia.index') }}" class="ios-island mb-4">
            <div class="row align-items-end g-3 justify-content-center">
                <div class="col-md-3">
                    <label for="id_sucursal_seleccionada" class="form-label fw-bold mb-1 ms-1" style="font-size: 0.8rem; color: #86868b;">Sucursal</label>
                    <select class="form-select form-select-ios fw-semibold" id="id_sucursal_seleccionada" name="id_sucursal_seleccionada">
                        <option value="todas" {{ request('id_sucursal_seleccionada', 'todas') == 'todas' ? 'selected' : '' }} class="fw-bold text-primary">-- TODAS LAS SUCURSALES --</option>
                        @foreach ($sucursales as $sucursal)
                            <option value="{{ $sucursal->id_sucursal }}" {{ ($id_sucursal_seleccionada ?? '') == $sucursal->id_sucursal ? 'selected' : '' }}>
                                {{ $sucursal->nombre_sucursal }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label for="tipo_periodo" class="form-label fw-bold mb-1 ms-1" style="font-size: 0.8rem; color: #86868b;">Visualización</label>
                    <select class="form-select form-select-ios fw-semibold" name="tipo_periodo" id="tipo_periodo">
                        <option value="dia" {{ $tipoPeriodo == 'dia' ? 'selected' : '' }}>Día</option>
                        <option value="semana" {{ $tipoPeriodo == 'semana' ? 'selected' : '' }}>Semana</option>
                        <option value="quincena" {{ $tipoPeriodo == 'quincena' ? 'selected' : '' }}>Quincena</option>
                        <option value="mes" {{ $tipoPeriodo == 'mes' ? 'selected' : '' }}>Mes</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="fecha_ref" class="form-label fw-bold mb-1 ms-1" style="font-size: 0.8rem; color: #86868b;">Fecha Base</label>
                    <input type="date" name="fecha_ref" id="fecha_ref" class="form-control form-control-ios fw-semibold" value="{{ $fechaReferencia->toDateString() }}">
                </div>
                <div class="col-md-3 d-flex align-items-end gap-2">
                    <button type="submit" class="btn-ios-primary flex-fill shadow-sm py-2"><i class="bi bi-search me-1"></i> Consultar</button>
                    
                    @php
                        $params = ['id_sucursal_seleccionada' => $id_sucursal_seleccionada ?? 'todas', 'tipo_periodo' => $tipoPeriodo];
                        $prevDate = $fechaReferencia->copy(); $nextDate = $fechaReferencia->copy();
                        if($tipoPeriodo == 'semana') { $prevDate->subWeek(); $nextDate->addWeek(); }
                        elseif($tipoPeriodo == 'quincena') { $prevDate->subDays(15); $nextDate->addDays(15); }
                        elseif($tipoPeriodo == 'mes') { $prevDate->subMonthNoOverflow(); $nextDate->addMonthNoOverflow(); }
                        elseif($tipoPeriodo == 'dia') { $prevDate->subDay(); $nextDate->addDay(); }
                    @endphp
                    <div class="btn-group shadow-sm rounded-pill border border-light" style="background: rgba(255,255,255,0.8);">
                        <a href="{{ route('asistencia.index', array_merge($params, ['fecha_ref' => $prevDate->toDateString()])) }}" class="btn btn-sm border-0 px-3 rounded-start-pill text-secondary hover-bg-light" style="padding-top: 0.6rem; padding-bottom: 0.6rem;" title="Anterior"><i class="bi bi-chevron-left"></i></a>
                        <div class="border-end border-light"></div>
                        <a href="{{ route('asistencia.index', array_merge($params, ['fecha_ref' => $nextDate->toDateString()])) }}" class="btn btn-sm border-0 px-3 rounded-end-pill text-secondary hover-bg-light" style="padding-top: 0.6rem; padding-bottom: 0.6rem;" title="Siguiente"><i class="bi bi-chevron-right"></i></a>
                    </div>
                </div>
            </div>
        </form>

        <div class="d-flex justify-content-between align-items-end mb-3 px-3">
            <div>
                <span class="fw-bold" style="color: #86868b; font-size: 0.75rem; text-transform: uppercase; letter-spacing: 1px;">Sucursal Actual</span><br>
                <h4 class="mb-0 fw-bold" style="color: #1d1d1f; letter-spacing: -0.5px;">{{ $sucursalSeleccionadaNombre ?? 'TODAS LAS SUCURSALES' }}</h4>
            </div>
            <div class="text-end">
                <span class="badge rounded-pill px-3 py-2 shadow-sm fw-semibold" style="background: rgba(255,255,255,0.8); color: #1d1d1f; border: 1px solid rgba(255,255,255,1); backdrop-filter: blur(10px); font-size: 0.85rem;">
                    <i class="bi bi-calendar3 text-primary me-2"></i>{{ $fechaReferencia->translatedFormat('d \d\e F, Y') }}
                </span>
            </div>
        </div>

        @if(isset($empleadosDeSucursal) && $empleadosDeSucursal->isNotEmpty() && isset($fechasDelPeriodo) && $fechasDelPeriodo->isNotEmpty())
            
            {{-- ISLA 2: TABLA DE ASISTENCIA --}}
            <div id="tabla-captura" class="ios-table-container mx-auto" style="max-width: {{ $tipoPeriodo == 'dia' ? '650px' : '100%' }};">
                <table class="table table-ios text-center align-middle">
                    <thead style="position: sticky; top: 0; z-index: 10;">
                        <tr>
                            <th style="min-width: {{ $tipoPeriodo == 'dia' ? '350px' : '250px' }}; text-align: left; position: sticky; left: 0; z-index: 11; background-color: rgba(245, 245, 247, 0.95) !important; backdrop-filter: blur(10px);">
                                <div class="d-flex justify-content-between align-items-center px-2">
                                    <span>Empleado</span>
                                    <button id="btn-mostrar-ocultos" class="btn btn-light btn-sm py-0 d-none rounded-pill shadow-sm text-primary fw-bold" onclick="mostrarOcultos()" title="Restaurar ocultos" style="font-size: 0.7rem;" data-html2canvas-ignore="true">
                                        <i class="bi bi-eye"></i> <span id="span-contador-ocultos"></span>
                                    </button>
                                </div>
                            </th>
                            @foreach ($fechasDelPeriodo as $fecha)
                                <th style="min-width: 130px; {{ $fecha->isToday() ? 'background-color: #e5f0ff !important; color: #0071e3 !important;' : '' }}">
                                    <div class="d-flex flex-column align-items-center">
                                        <span style="font-size: 0.7rem; opacity: 0.8;">{{ strtoupper($fecha->translatedFormat('l')) }}</span>
                                        <span class="fw-bold" style="font-size: 1rem;">{{ $fecha->format('d') }}</span>
                                    </div>
                                </th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($empleadosDeSucursal as $empleado)
                            <tr id="row_emp_{{ $empleado->id_empleado }}" class="empleado-row" data-id="{{ $empleado->id_empleado }}">
                                
                                {{-- CELDA EMPLEADO --}}
                                <td class="align-middle" style="text-align: left; position: sticky; left: 0; background-color: rgba(255,255,255,0.9); backdrop-filter: blur(5px); z-index: 1; border-right: 1px solid rgba(0,0,0,0.05);">
                                    <div class="d-flex align-items-center px-2 py-1">
                                        <i class="bi bi-eye-slash text-muted me-3" style="cursor: pointer; font-size: 1rem; opacity: 0.3; transition: opacity 0.2s;" onmouseover="this.style.opacity=1" onmouseout="this.style.opacity=0.3" onclick="ocultarEmpleado({{ $empleado->id_empleado }})" title="Ocultar empleado" data-html2canvas-ignore="true"></i>
                                        
                                        <div class="{{ $tipoPeriodo == 'dia' ? 'd-flex align-items-center flex-wrap gap-2' : '' }}">
                                            <span class="fw-bold {{ $tipoPeriodo != 'dia' ? 'd-block' : '' }}" style="color: #1d1d1f; font-size: 0.9rem; letter-spacing: -0.2px;">{{ $empleado->nombre_completo }}</span>
                                            
                                            @if($tipoPeriodo == 'dia')
                                                @if(($id_sucursal_seleccionada ?? 'todas') === 'todas')
                                                    <span class="badge rounded-pill border fw-medium" style="font-size: 0.65rem; color: #6e6e73; background: #f5f5f7;">
                                                        <i class="bi bi-shop me-1"></i>{{ $empleado->sucursal->nombre_sucursal ?? 'S/S' }}
                                                    </span>
                                                @endif
                                                <span class="badge rounded-pill fw-medium" style="font-size: 0.65rem; color: #0071e3; background: rgba(0, 113, 227, 0.1);">
                                                    {{ $empleado->puesto->nombre_puesto ?? 'General' }}
                                                </span>
                                            @else
                                                @if(($id_sucursal_seleccionada ?? 'todas') === 'todas')
                                                    <span class="text-muted d-block" style="font-size: 0.7em; font-weight: 500;">
                                                        <i class="bi bi-shop opacity-50"></i> {{ $empleado->sucursal->nombre_sucursal ?? 'S/S' }}
                                                    </span>
                                                @endif
                                                <span class="fw-semibold d-block" style="color: #86868b; font-size: 0.7em;">
                                                    {{ $empleado->puesto->nombre_puesto ?? 'General' }}
                                                </span>
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

                                        // Colores de Fondo Translucidos
                                        $bgStyle = '';
                                        $textClass = 'text-dark';
                                        if ($esAntesDeIngreso) {
                                            $bgStyle = 'background-color: rgba(0,0,0,0.03);';
                                        } elseif ($asistenciaDia) {
                                            switch ($asistenciaDia->status_asistencia) {
                                                case 'Retardo': $bgStyle = 'background-color: rgba(255, 193, 7, 0.15);'; $textClass = 'text-warning-emphasis'; break;
                                                case 'Falta': $bgStyle = 'background-color: rgba(220, 53, 69, 0.08);'; $textClass = 'text-danger'; break;
                                                case 'Baja_Dia': $bgStyle = 'background-color: rgba(0,0,0,0.04);'; $textClass = 'text-muted'; break;
                                                case 'Incidencia': $bgStyle = 'background-color: rgba(13, 110, 253, 0.08);'; $textClass = 'text-primary'; break;
                                            }
                                        } elseif ($asuetoDia) {
                                            $bgStyle = 'background-color: rgba(111, 66, 193, 0.08);'; $textClass = 'text-purple'; 
                                        } elseif ($vacacionDia) {
                                            $bgStyle = 'background-color: rgba(25, 135, 84, 0.08);'; $textClass = 'text-success';
                                        } elseif (!$esLaborable) {
                                            $bgStyle = 'background-color: rgba(0,0,0,0.02);'; $textClass = 'text-secondary';
                                        }
                                        
                                        $estadoActualForm = $asistenciaDia ? ($asistenciaDia->status_asistencia == 'Retardo' ? 'Presente' : $asistenciaDia->status_asistencia) : 'Presente';
                                    @endphp
                                    
                                    <td class="p-1 position-relative" style="height: {{ $tipoPeriodo == 'dia' ? '44px' : '54px' }};">
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
                                                        <i class="bi bi-balloon-fill text-white" style="font-size: 0.75rem; margin-top: 1px;"></i>
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
                                                        <span class="fw-bold {{ $textClass }}" style="font-size: 0.95rem; font-family: -apple-system, sans-serif; letter-spacing: 0.5px;">
                                                            {{ \Carbon\Carbon::parse($asistenciaDia->hora_llegada)->format('H:i') }}
                                                        </span>
                                                        @if($asistenciaDia->status_asistencia == 'Retardo') 
                                                            <span class="{{ $textClass }}" style="font-size: 0.65rem; font-weight: 600; margin-top: -2px;">Retardo</span> 
                                                        @endif
                                                    @elseif ($asistenciaDia->status_asistencia == 'Falta')
                                                        <span class="fw-bold {{ $textClass }}" style="font-size: 0.85rem; letter-spacing: 0.5px;">FALTA</span>
                                                    @elseif ($asistenciaDia->status_asistencia == 'Baja_Dia')
                                                        <span class="fw-semibold {{ $textClass }}" style="font-size: 0.8rem;">BAJA</span>
                                                    @elseif ($asistenciaDia->status_asistencia == 'Incidencia')
                                                        @if($asistenciaDia->hora_llegada) 
                                                            <strong class="{{ $textClass }}" style="font-size: 0.9rem;">{{ \Carbon\Carbon::parse($asistenciaDia->hora_llegada)->format('H:i') }}</strong>
                                                        @endif 
                                                        <span class="{{ $textClass }} fw-semibold text-truncate w-100 px-1 text-center" style="font-size: 0.65rem; max-width: 100px; margin-top: -2px;" title="{{ $asistenciaDia->notas_incidencia }}">
                                                            {{ $asistenciaDia->notas_incidencia ?: 'INCID.' }}
                                                        </span>
                                                    @endif
                                                
                                                @else
                                                    @if($asuetoDia)
                                                        <span class="badge rounded-pill fw-bold shadow-sm" style="background: linear-gradient(135deg, #a280df 0%, #7e57c2 100%); color: white; font-size: 0.65rem; padding: 0.35em 0.7em;" title="{{ $asuetoDia->nombre }}">
                                                            <i class="bi bi-cup-hot-fill me-1"></i> ASUETO
                                                        </span>
                                                    @elseif($vacacionDia)
                                                        <span class="badge rounded-pill fw-bold shadow-sm" style="background: linear-gradient(135deg, #34c759 0%, #28a745 100%); color: white; font-size: 0.65rem; padding: 0.35em 0.7em;" title="Periodo Vacacional">
                                                            <i class="bi bi-airplane-fill me-1"></i> VACAC.
                                                        </span>
                                                    @elseif(!$esLaborable)
                                                        <span class="fw-bold text-secondary opacity-50" style="font-size: 0.7rem; letter-spacing: 0.5px;">DESC</span>
                                                    @else
                                                        <i class="bi bi-plus text-primary opacity-25" style="font-size: 1.5rem; transition: opacity 0.2s;" onmouseover="this.style.opacity=0.8" onmouseout="this.style.opacity=0.25" data-html2canvas-ignore="true"></i>
                                                    @endif
                                                @endif
                                            </div>

                                            {{-- MODO EDICIÓN --}}
                                            @if(!$esAntesDeIngreso)
                                                <div class="edit-mode d-none position-absolute top-0 start-0 w-100 h-100 bg-white shadow-lg rounded-3 z-3 d-flex align-items-center justify-content-center p-1" style="transform: scale(1.15); border: 1px solid #0071e3;" data-html2canvas-ignore="true">
                                                    <form method="POST" action="{{ route('asistencia.registrarEntrada') }}" class="w-100 d-flex flex-column align-items-center" onsubmit="prepararHoraAntesDeEnviar(this)">
                                                        @csrf
                                                        <input type="hidden" name="id_empleado" value="{{ $empleado->id_empleado }}">
                                                        <input type="hidden" name="fecha_registro" value="{{ $fechaString }}">
                                                        <input type="hidden" name="id_sucursal_seleccionada" value="{{ $id_sucursal_seleccionada }}">

                                                        <select name="status_asistencia" class="form-select border-0 bg-light fw-bold text-center mb-1 text-primary" style="font-size: 0.7rem; padding: 2px 5px; height: auto; border-radius: 4px;" data-periodo="{{ $tipoPeriodo }}" onchange="manejarCambioEstado(this)">
                                                            <option value="Presente" {{ $estadoActualForm == 'Presente' ? 'selected' : '' }}>ASIST</option>
                                                            <option value="Falta" {{ $estadoActualForm == 'Falta' ? 'selected' : '' }}>FALTA</option>
                                                            <option value="Incidencia" {{ $estadoActualForm == 'Incidencia' ? 'selected' : '' }}>INCID</option>
                                                            <option value="Baja_Dia" {{ $estadoActualForm == 'Baja_Dia' ? 'selected' : '' }}>BAJA</option>
                                                        </select>

                                                        <input type="text" name="hora_llegada_manual" class="form-control border-0 bg-light text-center fw-bold mb-1 text-dark" style="font-size: 0.85rem; padding: 2px; height: auto; border-radius: 4px;" placeholder="HH:MM" maxlength="5" oninput="formatearHoraAuto(this)" value="{{ $asistenciaDia && $asistenciaDia->hora_llegada ? \Carbon\Carbon::parse($asistenciaDia->hora_llegada)->format('H:i') : '' }}">
                                                        <input type="text" name="notas_incidencia" class="form-control border-0 bg-light text-center mb-1 text-dark" style="font-size: 0.7rem; padding: 2px; height: auto; border-radius: 4px;" placeholder="Nota" value="{{ $asistenciaDia && $asistenciaDia->status_asistencia == 'Incidencia' ? $asistenciaDia->notas_incidencia : '' }}">

                                                        <div class="d-flex w-100 px-1 gap-1 mt-1">
                                                            <button type="submit" class="btn btn-primary flex-fill rounded-1 py-0 border-0 shadow-sm" style="height: 22px; background-color: #0071e3;"><i class="bi bi-check" style="font-size: 0.9rem; line-height: 0;"></i></button>
                                                            <button type="button" class="btn btn-light flex-fill rounded-1 py-0 border shadow-sm" style="height: 22px;" onclick="cancelarEdicion(this)"><i class="bi bi-x" style="font-size: 0.9rem; line-height: 0;"></i></button>
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
            <div class="text-center py-5 my-4 ios-island mx-auto" style="max-width: 500px;">
                <i class="bi bi-inbox text-muted opacity-25" style="font-size: 3.5rem;"></i>
                <h5 class="fw-bold mt-3" style="color: #1d1d1f;">Sin datos</h5>
                <p class="text-muted mb-0" style="color: #86868b;">No hay registros de asistencia para mostrar en este rango.</p>
            </div>
        @endif
    </div>

    <!-- Modal Asueto -->
    <div class="modal fade" id="modalAsueto" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <form method="POST" action="{{ route('asistencia.guardar_asueto') }}" class="modal-content border-0 shadow-lg" style="border-radius: 1.5rem; background: rgba(255, 255, 255, 0.85); backdrop-filter: blur(24px); -webkit-backdrop-filter: blur(24px);">
                @csrf
                <div class="modal-header border-0 px-4 pt-4 pb-3">
                    <h5 class="modal-title fw-bold" style="color: #1d1d1f;"><i class="bi bi-calendar-heart text-primary me-2"></i> Registrar Asueto</h5>
                    <button type="button" class="btn-close bg-light rounded-circle shadow-sm" data-bs-dismiss="modal" aria-label="Close" style="padding: 0.5rem;"></button>
                </div>
                <div class="modal-body px-4 pb-4">
                    <div class="mb-3">
                        <label class="form-label fw-bold mb-1" style="font-size: 0.85rem; color: #86868b;">Motivo del Asueto</label>
                        <input type="text" name="nombre" class="form-control form-control-ios bg-white" placeholder="Ej. Día de la Independencia" required>
                    </div>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold mb-1" style="font-size: 0.85rem; color: #86868b;">Fecha Inicio</label>
                            <input type="date" name="fecha_inicio" class="form-control form-control-ios bg-white" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold mb-1" style="font-size: 0.85rem; color: #86868b;">Fecha Fin</label>
                            <input type="date" name="fecha_fin" class="form-control form-control-ios bg-white" required>
                            <small class="text-muted" style="font-size: 0.7em;">(Misma fecha si es un solo día)</small>
                        </div>
                    </div>
                    <div class="mt-3">
                        <label class="form-label fw-bold mb-1" style="font-size: 0.85rem; color: #86868b;">Aplica para Sucursal</label>
                        <select name="id_sucursal" class="form-select form-select-ios bg-white" required>
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

    <!-- Modal Vacaciones con Formulario AJAX -->
    <div class="modal fade" id="modalVacaciones" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <form id="form_vacaciones_ajax" method="POST" action="{{ route('vacaciones.store') }}" class="modal-content border-0 shadow-lg" style="border-radius: 1.5rem; background: rgba(255, 255, 255, 0.85); backdrop-filter: blur(24px); -webkit-backdrop-filter: blur(24px);">
                @csrf
                <div class="modal-header border-0 px-4 pt-4 pb-3">
                    <h5 class="modal-title fw-bold" style="color: #1d1d1f;"><i class="bi bi-airplane-fill text-success me-2"></i> Registrar Vacaciones</h5>
                    <button type="button" class="btn-close bg-light rounded-circle shadow-sm" data-bs-dismiss="modal" aria-label="Close" style="padding: 0.5rem;"></button>
                </div>
                <div class="modal-body px-4 pb-4">
                    <div class="mb-3">
                        <label class="form-label fw-bold mb-1" style="font-size: 0.85rem; color: #86868b;">Empleado <span class="text-danger">*</span></label>
                        <select class="form-select form-select-ios bg-white" id="id_empleado_vac" name="id_empleado" required>
                            <option value="">Seleccione un empleado...</option>
                            @php $listaEmpleadosVac = isset($empleados) ? $empleados : (isset($empleadosDeSucursal) ? $empleadosDeSucursal : collect()); @endphp
                            @if($listaEmpleadosVac->count() > 0)
                                @foreach ($listaEmpleadosVac as $emp_vac)
                                    <option value="{{ $emp_vac->id_empleado }}" data-fecha_ingreso="{{ $emp_vac->fecha_ingreso ? \Carbon\Carbon::parse($emp_vac->fecha_ingreso)->toDateString() : '' }}">
                                        {{ $emp_vac->nombre_completo }}
                                    </option>
                                @endforeach
                            @endif
                        </select>
                        
                        {{-- PANEL INFORMATIVO (Días Totales Visibles + Popover) --}}
                        <div id="panel_info_vacaciones" class="mt-2 p-2 rounded-3 d-none border" style="background-color: #f8f9fa;">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <span class="text-muted fw-semibold" style="font-size: 0.8rem;">Días Restantes Totales:</span>
                                    <span id="badge_saldo_total" class="badge rounded-pill bg-secondary ms-1" style="font-size: 0.85rem;">Calculando...</span>
                                </div>
                                <i class="bi bi-info-circle-fill text-primary fs-5" id="icono_popover_vac" style="cursor: help;" title="Ver desglose por año"></i>
                            </div>
                            <div id="alerta_exceso" class="text-danger mt-1 fw-bold d-none text-end" style="font-size: 0.75rem;">
                                <i class="bi bi-exclamation-triangle-fill"></i> ¡Excede el saldo disponible!
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold mb-1" style="font-size: 0.85rem; color: #86868b;">Año de Servicio Correspondiente <span class="text-danger">*</span></label>
                        <input type="number" class="form-control form-control-ios bg-white" id="ano_servicio_correspondiente" name="ano_servicio_correspondiente" min="1" placeholder="Ej: 1, 2" required>
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold mb-1" style="font-size: 0.85rem; color: #86868b;">Fecha Inicio <span class="text-danger">*</span></label>
                            <input type="date" name="fecha_inicio" id="fecha_inicio_vac" class="form-control form-control-ios bg-white" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold mb-1" style="font-size: 0.85rem; color: #86868b;">Fecha Fin <span class="text-danger">*</span></label>
                            <input type="date" name="fecha_fin" id="fecha_fin_vac" class="form-control form-control-ios bg-white" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold mb-1" style="font-size: 0.85rem; color: #86868b;">Días Tomados</label>
                        <input type="text" class="form-control form-control-ios bg-light text-muted fw-semibold" id="dias_tomados_display" readonly placeholder="Se calculará automáticamente">
                    </div>
                    <div class="mb-1">
                        <label class="form-label fw-bold mb-1" style="font-size: 0.85rem; color: #86868b;">Comentarios (Opcional)</label>
                        <textarea name="comentarios" class="form-control form-control-ios bg-white" rows="2" placeholder="Nota adicional..."></textarea>
                    </div>
                </div>
                <div class="modal-footer border-0 px-4 pb-4 pt-0">
                    <button type="button" class="btn fw-semibold border-0" style="color: #86868b;" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" id="btn_guardar_vacaciones" class="btn-ios-primary fw-bold px-4 shadow-sm" style="background-color: #198754;">Guardar Vacaciones</button>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
        {{-- LIBRERÍA HTML2CANVAS PARA CAPTURAR LA TABLA --}}
        <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>

        <script>
        // LÓGICA PARA ENVÍO DE VACACIONES VÍA AJAX
        const formVacacionesAjax = document.getElementById('form_vacaciones_ajax');
        if (formVacacionesAjax) {
            formVacacionesAjax.addEventListener('submit', function(e) {
                e.preventDefault(); 
                
                let btn = document.getElementById('btn_guardar_vacaciones');
                let originalText = btn.innerHTML;
                btn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Guardando...';
                btn.disabled = true;

                fetch(this.action, {
                    method: 'POST',
                    body: new FormData(this),
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(async response => {
                    if (response.ok || response.redirected) {
                        window.location.reload();
                    } else if (response.status === 422) {
                        let data = await response.json();
                        let errores = Object.values(data.errors).flat().join('\n');
                        alert("Por favor corrige los siguientes errores:\n" + errores);
                        btn.innerHTML = originalText;
                        btn.disabled = false;
                    } else {
                        alert("Ocurrió un error inesperado al intentar guardar las vacaciones.");
                        btn.innerHTML = originalText;
                        btn.disabled = false;
                    }
                })
                .catch(err => {
                    console.error("Error Fetch:", err);
                    window.location.reload();
                });
            });
        }

        // LÓGICA PARA VACACIONES Y FETCH AJAX DEL POPOVER
        const empleadoSelectVac = document.getElementById('id_empleado_vac');
        const anoServicioInput = document.getElementById('ano_servicio_correspondiente');
        const fechaInicioInput = document.getElementById('fecha_inicio_vac');
        const fechaFinInput = document.getElementById('fecha_fin_vac');
        const diasTomadosDisplay = document.getElementById('dias_tomados_display');
        const panelInfoVac = document.getElementById('panel_info_vacaciones');
        const iconoPopoverVac = document.getElementById('icono_popover_vac');
        const badgeSaldoTotal = document.getElementById('badge_saldo_total');
        const alertaExceso = document.getElementById('alerta_exceso');

        let saldoGlobalCalculado = 0;

        function actualizarAnoServicioYPopover() {
            const selectedOption = empleadoSelectVac.options[empleadoSelectVac.selectedIndex];
            const empleadoId = empleadoSelectVac.value;
            
            if (!empleadoId) {
                if(anoServicioInput) anoServicioInput.value = '';
                if(panelInfoVac) panelInfoVac.classList.add('d-none');
                saldoGlobalCalculado = 0;
                return;
            }

            const fechaIngresoStr = selectedOption.dataset.fecha_ingreso;

            // 1. Mostrar Panel Inmediatamente
            panelInfoVac.classList.remove('d-none');
            badgeSaldoTotal.className = 'badge rounded-pill bg-secondary ms-1';
            badgeSaldoTotal.innerText = 'Consultando...';

            // 2. Calcular año correspondiente
            if (fechaIngresoStr && anoServicioInput) {
                const fechaIngreso = new Date(fechaIngresoStr + 'T00:00:00');
                const hoy = new Date();
                let anosCompletos = hoy.getFullYear() - fechaIngreso.getFullYear();
                const mesActual = hoy.getMonth();
                const diaActual = hoy.getDate();
                const mesIngreso = fechaIngreso.getMonth();
                const diaIngreso = fechaIngreso.getDate();
                
                if (mesActual < mesIngreso || (mesActual === mesIngreso && diaActual < diaIngreso)) {
                    anosCompletos--;
                }
                anosCompletos = Math.max(0, anosCompletos);
                anoServicioInput.value = (anosCompletos >= 1) ? anosCompletos : 1;
            } else if (anoServicioInput) {
                anoServicioInput.value = '';
            }

            // 3. Fetch AJAX Historial
            fetch(`/vacaciones/historial-json/${empleadoId}`)
            .then(res => {
                if(!res.ok) throw new Error("Error HTTP " + res.status);
                return res.json();
            })
            .then(data => {
                let html = '<div style="font-size: 11px; width: 250px;"><table class="table table-sm mb-0"><thead class="table-dark"><tr><th>Año</th><th>Periodo</th><th>Restantes</th></tr></thead><tbody>';
                let saldoAcumulado = 0;
                
                if(data && data.length > 0) {
                    data.forEach(row => {
                        html += `<tr><td>${row.ano_servicio}</td><td>${row.periodo}</td><td class="text-end fw-bold ${row.dias_restantes < 0 ? 'text-danger' : ''}">${row.dias_restantes}</td></tr>`;
                        let restante = parseFloat(String(row.dias_restantes).replace(/,/g, ''));
                        if(!isNaN(restante)) saldoAcumulado += restante;
                    });
                } else {
                    html += `<tr><td colspan="3" class="text-center text-muted">Sin historial registrado</td></tr>`;
                }
                
                html += `</tbody><tfoot class="table-light fw-bold"><tr><td colspan="2">TOTAL:</td><td class="text-end text-primary fs-6">${saldoAcumulado.toFixed(2)}</td></tr></tfoot></table></div>`;
                
                saldoGlobalCalculado = saldoAcumulado;

                // Actualizar el Badge visible con color robusto
                badgeSaldoTotal.innerText = saldoAcumulado.toFixed(2) + ' días';
                if(saldoAcumulado <= 0) {
                    badgeSaldoTotal.className = 'badge rounded-pill bg-danger ms-1';
                } else {
                    badgeSaldoTotal.className = 'badge rounded-pill bg-success ms-1';
                }

                // Destruir el popover viejo si existe y crear uno nuevo
                const existingPopover = bootstrap.Popover.getInstance(iconoPopoverVac);
                if (existingPopover) existingPopover.dispose();
                
                new bootstrap.Popover(iconoPopoverVac, { 
                    content: html, 
                    html: true, 
                    trigger: 'hover focus', 
                    container: 'body', 
                    placement: 'bottom', 
                    sanitize: false 
                });
                
                calcularDiasTomados(); // Re-validar colores si ya había fechas
            })
            .catch(error => {
                console.error("Error al cargar historial vacacional:", error);
                badgeSaldoTotal.className = 'badge rounded-pill bg-warning text-dark ms-1';
                badgeSaldoTotal.innerText = 'Error de conexión';
                saldoGlobalCalculado = 0;
                calcularDiasTomados();
            });
        }

        function calcularDiasTomados() {
            if (fechaInicioInput.value && fechaFinInput.value && diasTomadosDisplay) {
                const inicio = new Date(fechaInicioInput.value + 'T00:00:00');
                const fin = new Date(fechaFinInput.value + 'T00:00:00');

                if (fin >= inicio) {
                    const diffTime = Math.abs(fin - inicio);
                    const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24)) + 1;
                    diasTomadosDisplay.value = diffDays;

                    // Si se pasa del saldo (según la BD), pintarlo de rojo y mostrar alerta
                    if(diffDays > saldoGlobalCalculado && empleadoSelectVac.value) {
                        diasTomadosDisplay.classList.add('text-danger', 'border-danger');
                        diasTomadosDisplay.classList.remove('text-muted');
                        if(alertaExceso) alertaExceso.classList.remove('d-none');
                    } else {
                        diasTomadosDisplay.classList.remove('text-danger', 'border-danger');
                        diasTomadosDisplay.classList.add('text-muted');
                        if(alertaExceso) alertaExceso.classList.add('d-none');
                    }
                } else {
                    diasTomadosDisplay.value = '';
                    if(alertaExceso) alertaExceso.classList.add('d-none');
                }
            } else if (diasTomadosDisplay) {
                diasTomadosDisplay.value = '';
                if(alertaExceso) alertaExceso.classList.add('d-none');
            }
        }

        if (empleadoSelectVac) {
            empleadoSelectVac.addEventListener('change', actualizarAnoServicioYPopover);
            if (empleadoSelectVac.value) actualizarAnoServicioYPopover();
        }

        if (fechaInicioInput && fechaFinInput) {
            fechaInicioInput.addEventListener('change', calcularDiasTomados);
            fechaFinInput.addEventListener('change', calcularDiasTomados);
            if (fechaInicioInput.value && fechaFinInput.value) calcularDiasTomados();
        }

        // Función para Capturar la Tabla
        function capturarTabla() {
            let btn = document.getElementById('btn-capturar');
            let originalText = btn.innerHTML;
            btn.innerHTML = '<i class="bi bi-hourglass-split me-1"></i> Procesando...';
            btn.disabled = true;

            let elemento = document.getElementById('tabla-captura');

            html2canvas(elemento, {
                scale: 2, 
                backgroundColor: '#ffffff', 
                useCORS: true 
            }).then(canvas => {
                let enlace = document.createElement('a');
                let fecha = document.getElementById('fecha_ref').value;
                enlace.download = 'Asistencia_' + fecha + '.png';
                enlace.href = canvas.toDataURL('image/png');
                enlace.click();
                btn.innerHTML = originalText;
                btn.disabled = false;
            }).catch(err => {
                console.error("Error al capturar la tabla: ", err);
                alert("Ocurrió un error al intentar capturar la tabla. Por favor intenta de nuevo.");
                btn.innerHTML = originalText;
                btn.disabled = false;
            });
        }

        function activarEdicion(divVista) {
            document.querySelectorAll('.edit-mode').forEach(el => el.classList.add('d-none'));
            document.querySelectorAll('.display-mode').forEach(el => el.classList.remove('d-none'));
            document.querySelectorAll('.cell-editing-active').forEach(el => el.classList.remove('cell-editing-active')); 

            let celda = divVista.closest('td');
            celda.classList.add('cell-editing-active');

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
            celda.classList.remove('cell-editing-active'); 
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