<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>CredinOs</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <style>
        body { background-color: #f8f9fa; }
        .sidebar { position: fixed; top: 0; bottom: 0; left: 0; z-index: 100; padding: 56px 0 0; box-shadow: inset -1px 0 0 rgba(0, 0, 0, .1); background-color: #fff; width: 250px; }
        .sidebar-sticky { position: relative; top: 0; height: calc(100vh - 56px); padding-top: .5rem; overflow-x: hidden; overflow-y: auto; }
        .navbar-brand { padding-top: .75rem; padding-bottom: .75rem; font-size: 1.1rem; background-color: #343a40; box-shadow: inset -1px 0 0 rgba(0, 0, 0, .25); color: #fff; }
        .navbar { z-index: 101; }
        .nav-link { color: #333; }
        .nav-link.active { color: #0d6efd; font-weight: bold; }
        .nav-link:hover { background-color: #e9ecef; }
        .nav-link .bi { margin-right: 8px; width: 16px; text-align: center; }
        main { margin-left: 250px; padding: 70px 20px 20px 20px; }
        .sidebar-heading { font-size: 0.8em; text-transform: uppercase; color: #6c757d; }
        .sidebar .nav-item .nav-link { padding-left: 35px; font-size: 0.9em; }
        .navbar-dark .navbar-nav .nav-link { color: rgba(255,255,255,.75); }
        .navbar-dark .navbar-nav .nav-link:hover { color: #fff; }
    </style>

</head>
<body>
    <header class="navbar navbar-dark sticky-top bg-dark flex-md-nowrap p-0 shadow">
        <a class="navbar-brand col-md-3 col-lg-2 me-0 px-3 fs-6" href="{{ route('dashboard') }}">CredinOs System</a>
        <button class="navbar-toggler position-absolute d-md-none collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#sidebarMenu" aria-controls="sidebarMenu" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="navbar-nav">
            <div class="nav-item text-nowrap">
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <a class="nav-link px-3" href="{{ route('logout') }}"
                       onclick="event.preventDefault(); this.closest('form').submit();">
                        Cerrar Sesión ({{ Auth::user()->name }})
                    </a>
                </form>
            </div>
        </div>
    </header>

    <div class="container-fluid">
        <div class="row">
            {{-- Menú Lateral --}}
            <nav id="sidebarMenu" class="col-md-3 col-lg-2 d-md-block sidebar collapse">
                <div class="position-sticky pt-3 sidebar-sticky">
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}">
                                <i class="bi bi-house-door"></i>
                                Home / Inicio
                            </a>
                        </li>
                    </ul>

                    <h6 class="sidebar-heading d-flex justify-content-between align-items-center px-3 mt-4 mb-1 text-muted">
                    <span>RECURSOS HUMANOS</span>
                </h6>
                <ul class="nav flex-column mb-2">
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('empleados.*') ? 'active' : '' }}" href="{{ route('empleados.index') }}">
                            <i class="bi bi-people"></i>
                            Empleados
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('contratos.*') ? 'active' : '' }}" href="{{ route('contratos.index') }}">
                            <i class="bi bi-file-earmark-text"></i>
                            Contratos
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('asistencia.*') ? 'active' : '' }}" href="{{ route('asistencia.index') }}">
                            <i class="bi bi-calendar-check"></i>
                            Asistencias
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('vacaciones.*') ? 'active' : '' }}" href="{{ route('vacaciones.index') }}">
                            <i class="bi bi-briefcase-fill"></i> {{-- O un icono de palmera/playa --}}
                            Vacaciones
                        </a>
                    </li>

                    {{-- =====> NUEVO ENLACE PARA DEDUCCIONES <===== --}}
                    <li class="nav-item">
    <a class="nav-link {{ request()->routeIs('deducciones.*') ? 'active' : '' }}" href="{{ route('deducciones.index') }}">
        <i class="bi bi-wallet2"></i> {{-- Icono cambiado --}}
        Deducciones
    </a>
</li>

  {{-- =====> NUEVO ENLACE PARA LISTA DE RAYA <===== --}}
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('lista_de_raya.*') ? 'active' : '' }}" href="{{ route('lista_de_raya.index') }}">
                            <i class="bi bi-file-spreadsheet"></i>
                            Lista de Raya
                        </a>
                    </li>
                    {{-- =============================================== --}}
                    {{-- ====================================== --}}
 {{-- =====> NUEVO ENLACE PARA FINIQUITOS <===== --}}
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('finiquitos.*') ? 'active' : '' }}" href="{{ route('finiquitos.index') }}">
                            <i class="bi bi-person-x"></i>
                            Finiquitos y Liquidaciones
                        </a>
                    </li>
                    {{-- ======================================= --}}



                    {{-- =====> NUEVO ENLACE PARA GESTIÓN IMSS <===== --}}
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('imss.*') ? 'active' : '' }}" href="{{ route('imss.index') }}">
                            <i class="bi bi-shield-check"></i> {{-- Icono de ejemplo para IMSS --}}
                            Gestión IMSS
                        </a>
                    </li>
                    {{-- ====================================== --}}
                </ul>

                     <h6 class="sidebar-heading d-flex justify-content-between align-items-center px-3 mt-4 mb-1 text-muted">
                    <span>ADMINISTRACIÓN</span>
                </h6>
                <ul class="nav flex-column mb-2">
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('profile.edit') ? 'active' : '' }}" href="{{ route('profile.edit') }}">
                            <i class="bi bi-person-circle"></i>
                            Mi Perfil
                        </a>
                    </li>
                    {{-- =====> ENLACE ACTUALIZADO PARA USUARIOS DEL SISTEMA <===== --}}
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('users.*') ? 'active' : '' }}" href="{{ route('users.index') }}">
                            <i class="bi bi-person-gear"></i>
                            Usuarios del Sistema
                        </a>
                    </li>
                    </ul>

                    {{-- NUEVA SECCIÓN: CONFIGURACIÓN --}}
                    <h6 class="sidebar-heading d-flex justify-content-between align-items-center px-3 mt-4 mb-1 text-muted">
                        <span>CONFIGURACIÓN</span>
                    </h6>
                    <ul class="nav flex-column mb-2">
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('sucursales.*') ? 'active' : '' }}" href="{{ route('sucursales.index') }}">
                                <i class="bi bi-building"></i>
                                Sucursales
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('puestos.*') ? 'active' : '' }}" href="{{ route('puestos.index') }}">
                                <i class="bi bi-briefcase"></i>
                                Puestos
                            </a>
                        </li>
                        {{-- =====> NUEVO ENLACE PARA PATRONES <===== --}}
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('patrones.*') ? 'active' : '' }}" href="{{ route('patrones.index') }}">
                            <i class="bi bi-person-badge"></i> {{-- O el icono que prefieras --}}
                            Patrones (Empresas)
                        </a>
                    </li>
                    {{-- ====================================== --}}
                    {{-- =====> NUEVO ENLACE PARA HORARIOS <===== --}}
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('horarios.*') ? 'active' : '' }}" href="{{ route('horarios.index') }}">
                            <i class="bi bi-clock-history"></i>
                            Horarios
                        </a>
                    </li>
                    {{-- ====================================== --}}

                        
                    </ul>
                </div>
            </nav>

            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                {{ $slot }}
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    
    @stack('scripts') {{-- <--- LÍNEA AÑADIDA/ASEGURADA --}}
</body>
</html>