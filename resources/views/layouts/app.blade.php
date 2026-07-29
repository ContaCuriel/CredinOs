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
        .navbar-brand { flex-shrink: 0; }
        .navbar { z-index: 101; }
        .nav-link { color: #333; }
        .nav-link.active { color: #0d6efd; font-weight: bold; }
        .nav-link:hover { background-color: #e9ecef; }
        .nav-link .bi { margin-right: 8px; width: 16px; text-align: center; }
        main { margin-left: 0px; padding: 70px 20px 20px 20px; } 
        .sidebar-heading { font-size: 0.8em; text-transform: uppercase; color: #6c757d; }
        .sidebar .nav-item .nav-link { padding-left: 35px; font-size: 0.9em; }
        .navbar-dark .navbar-nav .nav-link { color: rgba(255,255,255,.75); }
        .navbar-dark .navbar-nav .nav-link:hover { color: #fff; }

        .sidebar, main {
            transition: all 0.3s ease-in-out;
        }
        body.sidebar-collapsed .sidebar {
            transform: translateX(-100%);
        }
        body.sidebar-collapsed main {
            margin-left: 0;
        }
        
        .sidebar-backdrop {
            position: fixed;
            top: 0;
            left: 0;
            width: 100vw;
            height: 100vh;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 99;
            opacity: 0;
            visibility: hidden;
            transition: opacity 0.3s ease-in-out, visibility 0.3s ease-in-out;
        }
        .sidebar-backdrop.show {
            opacity: 1;
            visibility: visible;
        }
    </style>
</head>
<body>
    <header class="navbar navbar-dark sticky-top bg-dark flex-md-nowrap p-0 shadow">
        <a class="navbar-brand col-md-3 col-lg-2 me-0 px-3 fs-6" href="{{ route('dashboard') }}">CredinOs System</a>
        
        {{-- Solo mostramos los toggles de menú si el usuario inició sesión --}}
        @auth
            <button class="btn btn-link d-none d-md-block" type="button" id="sidebarToggle" title="Contraer/Expandir Menú">
                <i class="bi bi-list text-white fs-4"></i>
            </button>
            <button class="navbar-toggler d-md-none" type="button" data-bs-toggle="collapse" data-bs-target="#sidebarMenu" aria-controls="sidebarMenu" aria-expanded="false" aria-label="Toggle navigation" style="z-index: 1031;">
                <span class="navbar-toggler-icon"></span>
            </button>
        @endauth

        <div class="navbar-nav ms-auto">
            <div class="nav-item text-nowrap">
                {{-- 💡 CONTROL BLINDADO CONTRA ERRORES 500 --}}
                @auth
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <a class="nav-link px-3" href="{{ route('logout') }}"
                           onclick="event.preventDefault(); this.closest('form').submit();">
                            Cerrar Sesión ({{ Auth::user()->name }})
                        </a>
                    </form>
                @else
                    <span class="navbar-text px-3 text-info fw-bold">
                        <i class="bi bi-unlock-fill"></i> Módulo de Asistencia Pública
                    </span>
                @endauth
            </div>
        </div>
    </header>

    <div class="container-fluid">
        <div class="row">
            {{-- El menú lateral solo se renderiza si hay un usuario autenticado --}}
            @auth
            <nav id="sidebarMenu" class="col-md-3 col-lg-2 d-md-block sidebar collapse">
                <div class="position-sticky pt-3 sidebar-sticky">
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}">
                                <i class="bi bi-house-door"></i> Home / Inicio
                            </a>
                        </li>
                    </ul>
                    @can('ver-menu-creditos')
                    <h6 class="sidebar-heading d-flex justify-content-between align-items-center px-3 mt-4 mb-1 text-muted">
                        <span>CRÉDITOS Y COBRANZA</span>
                    </h6>
                    <ul class="nav flex-column mb-2">
                        @can('ver-clientes')
                            <li class="nav-item"><a class="nav-link {{ request()->routeIs('clientes.*') ? 'active' : '' }}" href="{{ route('clientes.index') }}"><i class="bi bi-person-badge"></i> Clientes</a></li>
                        @endcan
                        @can('ver-grupos')
                            <li class="nav-item"><a class="nav-link {{ request()->routeIs('grupos.*') ? 'active' : '' }}" href="{{ route('grupos.index') }}"><i class="bi bi-people-fill"></i> Grupos Solidarios</a></li>
                        @endcan
                        @can('registrar-credito')
                            <li class="nav-item"><a class="nav-link {{ request()->routeIs('creditos.create') ? 'active' : '' }}" href="{{ route('creditos.create') }}"><i class="bi bi-plus-circle"></i> Registrar Crédito</a></li>
                        @endcan
                        @can('ver-creditos')
                            <li class="nav-item"><a class="nav-link {{ request()->routeIs(['creditos.index', 'creditos.show']) ? 'active' : '' }}" href="{{ route('creditos.index') }}"><i class="bi bi-card-list"></i> Lista de Créditos</a></li>
                        @endcan
                    </ul>
                    @endcan

                    @can('ver-menu-rh')
                    <h6 class="sidebar-heading d-flex justify-content-between align-items-center px-3 mt-4 mb-1 text-muted">
                        <span>RECURSOS HUMANOS</span>
                    </h6>
                    <ul class="nav flex-column mb-2">
                        @can('ver-empleados')<li class="nav-item"><a class="nav-link {{ request()->routeIs('empleados.*') ? 'active' : '' }}" href="{{ route('empleados.index') }}"><i class="bi bi-people"></i> Empleados</a></li>@endcan
                        @can('ver-contratos')<li class="nav-item"><a class="nav-link {{ request()->routeIs('contratos.*') ? 'active' : '' }}" href="{{ route('contratos.index') }}"><i class="bi bi-file-earmark-text"></i> Contratos</a></li>@endcan
                        @can('ver-aguinaldo')
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('aguinaldo.*') ? 'active' : '' }}" href="{{ route('aguinaldo.index') }}">
                                <i class="bi bi-gift-fill"></i> Cálculo de Aguinaldo
                            </a>
                        </li>
                        @endcan
                        @can('ver-asistencias')<li class="nav-item"><a class="nav-link {{ request()->routeIs('asistencia.*') ? 'active' : '' }}" href="{{ route('asistencia.index') }}"><i class="bi bi-calendar-check"></i> Asistencias</a></li>@endcan
                        @can('ver-vacaciones')<li class="nav-item"><a class="nav-link {{ request()->routeIs('vacaciones.*') ? 'active' : '' }}" href="{{ route('vacaciones.index') }}"><i class="bi bi-briefcase-fill"></i> Vacaciones</a></li>@endcan
                        @can('ver-deducciones')<li class="nav-item"><a class="nav-link {{ request()->routeIs('deducciones.*') ? 'active' : '' }}" href="{{ route('deducciones.index') }}"><i class="bi bi-wallet2"></i> Deducciones</a></li>@endcan
                        @can('ver-lista-raya')<li class="nav-item"><a class="nav-link {{ request()->routeIs('lista_de_raya.*') ? 'active' : '' }}" href="{{ route('lista_de_raya.index') }}"><i class="bi bi-file-spreadsheet"></i> Lista de Raya</a></li>@endcan
                        {{-- 🔥 MÓDULO: TIMBRADO DE NÓMINA 🔥 --}}
@can('ver-timbrado')
<li class="nav-item">
    <a class="nav-link {{ request()->routeIs('nomina.timbrado.*') ? 'active' : '' }}" href="{{ route('nomina.timbrado.index') }}">
        <i class="bi bi-receipt-cutoff"></i> Timbrado CFDI
    </a>
</li>
@endcan
                        @can('ver-finiquitos')<li class="nav-item"><a class="nav-link {{ request()->routeIs('finiquitos.*') ? 'active' : '' }}" href="{{ route('finiquitos.index') }}"><i class="bi bi-person-x"></i> Finiquitos y Liquidaciones</a></li>@endcan
                        @can('ver-gestion-imss')<li class="nav-item"><a class="nav-link {{ request()->routeIs('imss.*') ? 'active' : '' }}" href="{{ route('imss.index') }}"><i class="bi bi-shield-check"></i> Gestión IMSS</a></li>@endcan
                        @can('ver-renuncias')
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('renuncias.create') ? 'active' : '' }}" href="{{ route('renuncias.create') }}">
                                <i class="bi bi-file-earmark-text"></i> Renuncia Voluntaria
                            </a>
                        </li>
                        @endcan
                    </ul>
                    @endcan

                    @can('ver-menu-contabilidad')
                    <h6 class="sidebar-heading d-flex justify-content-between align-items-center px-3 mt-4 mb-1 text-muted"><span>CONTABILIDAD</span></h6>
                    <ul class="nav flex-column mb-2">
                        @can('ver-colocaciones')
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('placements.*') ? 'active' : '' }}" href="{{ route('placements.index') }}">
                                <i class="bi bi-box-arrow-up-right"></i> Colocaciones Mensuales
                            </a>
                        </li>
                        @endcan

                        @can('ver-recuperaciones')
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('recoveries.*') ? 'active' : '' }}" href="{{ route('recoveries.index') }}">
                                <i class="bi bi-box-arrow-in-down-left"></i> Recuperaciones Mensuales
                            </a>
                        </li>
                        @endcan
                            
                        @can('ver-gastos')
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs(['gastos.index', 'gastos.create', 'gastos.edit']) ? 'active' : '' }}" href="{{ route('gastos.index') }}">
                                    <i class="bi bi-receipt-cutoff"></i> Gestión de Gastos
                                </a>
                            </li>
                        @endcan

                        @can('aprobar-gastos')
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('gastos.approvals') ? 'active' : '' }}" href="{{ route('gastos.approvals') }}">
                                    <i class="bi bi-check2-square"></i> Aprobar Gastos
                                </a>
                            </li>
                        @endcan
                        @can('ver-reportes')
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('reportes.gastos.sucursal') ? 'active' : '' }}" href="{{ route('reportes.gastos.sucursal') }}">
                                <i class="bi bi-file-bar-graph-fill"></i> Reporte de Gastos
                            </a>
                        </li>
                        @endcan
                        @can('ver-cuentas')
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('accounts.*') ? 'active' : '' }}" href="{{ route('accounts.index') }}">
                                <i class="bi bi-journal-bookmark-fill"></i> Catálogo de Cuentas
                            </a>
                        </li>
                        @endcan

                        @can('ver-polizas')
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('journals.*') ? 'active' : '' }}" href="{{ route('journals.index') }}">
                                <i class="bi bi-collection-fill"></i> Libro de Diario (Pólizas)
                            </a>
                        </li>
                        @endcan

                        @can('ver-reportes')
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('reportes.balanza_comprobacion') ? 'active' : '' }}" href="{{ route('reportes.balanza_comprobacion') }}">
                                <i class="bi bi-file-earmark-spreadsheet-fill"></i> Balanza de Comprobación
                            </a>
                        </li>
                        @endcan
                            
                        @can('ver-reportes')
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('reportes.income_statement') ? 'active' : '' }}" href="{{ route('reportes.income_statement') }}">
                                <i class="bi bi-graph-up-arrow"></i> Estado de Resultados
                            </a>
                        </li>
                        @endcan

                        @can('ver-reportes')
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('reportes.balance_sheet') ? 'active' : '' }}" href="{{ route('reportes.balance_sheet') }}">
                                <i class="bi bi-bank2"></i> Balance General
                            </a>
                        </li>
                        @endcan
                    </ul>
                    @endcan
                        
                    @can('ver-menu-administracion')
                    <h6 class="sidebar-heading d-flex justify-content-between align-items-center px-3 mt-4 mb-1 text-muted"><span>ADMINISTRACIÓN</span></h6>
                    <ul class="nav flex-column mb-2">
                        <li class="nav-item"><a class="nav-link {{ request()->routeIs('profile.edit') ? 'active' : '' }}" href="{{ route('profile.edit') }}"><i class="bi bi-person-circle"></i> Mi Perfil</a></li>
                        @can('ver-usuarios')<li class="nav-item"><a class="nav-link {{ request()->routeIs('users.*') ? 'active' : '' }}" href="{{ route('users.index') }}"><i class="bi bi-person-gear"></i> Usuarios del Sistema</a></li>@endcan
                        @can('ver-roles')<li class="nav-item"><a class="nav-link {{ request()->routeIs('roles.*') ? 'active' : '' }}" href="{{ route('roles.index') }}"><i class="bi bi-shield-lock-fill"></i> Roles y Permisos</a></li>@endcan
                        @can('ver-modulo-prueba')
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('prueba.index') ? 'active' : '' }}" href="{{ route('prueba.index') }}">
                                <i class="bi bi-joystick"></i> Módulo de Prueba
                            </a>
                        </li>
                        @endcan
                    </ul>
                    @endcan

                    @can('ver-menu-configuracion')
                    <h6 class="sidebar-heading d-flex justify-content-between align-items-center px-3 mt-4 mb-1 text-muted"><span>CONFIGURACIÓN</span></h6>
                    <ul class="nav flex-column mb-2">
                        @can('ver-sucursales')<li class="nav-item"><a class="nav-link {{ request()->routeIs('sucursales.*') ? 'active' : '' }}" href="{{ route('sucursales.index') }}"><i class="bi bi-building"></i> Sucursales</a></li>@endcan
                        @can('ver-puestos')<li class="nav-item"><a class="nav-link {{ request()->routeIs('puestos.*') ? 'active' : '' }}" href="{{ route('puestos.index') }}"><i class="bi bi-briefcase"></i> Puestos</a></li>@endcan
                        @can('ver-patrones')<li class="nav-item"><a class="nav-link {{ request()->routeIs('patrones.*') ? 'active' : '' }}" href="{{ route('patrones.index') }}"><i class="bi bi-person-badge"></i> Patrones (Empresas)</a></li>@endcan
                        @can('ver-horarios')<li class="nav-item"><a class="nav-link {{ request()->routeIs('horarios.*') ? 'active' : '' }}" href="{{ route('horarios.index') }}"><i class="bi bi-clock-history"></i> Horarios</a></li>@endcan
                        @can('ver-categorias')
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('categorias.*') ? 'active' : '' }}" href="{{ route('categorias.index') }}">
                                <i class="bi bi-tags-fill"></i> Categorías de Gastos
                            </a>
                        </li>
                        @endcan
                    </ul>
                    @endcan
                </div>
            </nav>
            <div class="sidebar-backdrop"></div>
            @endauth

            {{-- Si no hay sesión iniciada, ajustamos el layout ocupando todo el ancho para comodidad de la sucursal --}}
            <main class="{{ Auth::check() ? 'px-md-4' : 'container py-2' }}" style="{{ Auth::check() ? '' : 'margin-left: 0; padding-top: 70px;' }}">
                {{ $slot }}
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>

    <script>
    document.addEventListener('DOMContentLoaded', function () {
        const body = document.body;
        const sidebarMenu = document.getElementById('sidebarMenu');
        const desktopToggle = document.getElementById('sidebarToggle');
        const backdrop = document.querySelector('.sidebar-backdrop');
        const mainContent = document.querySelector('main');
        const menuLinks = document.querySelectorAll('#sidebarMenu .nav-link');
        
        if (sidebarMenu) {
            const bsCollapse = new bootstrap.Collapse(sidebarMenu, { toggle: false });

            const openSidebar = () => {
                body.classList.remove('sidebar-collapsed');
                if(backdrop) backdrop.classList.add('show');
                if(mainContent) mainContent.style.marginLeft = '250px';
                localStorage.setItem('sidebarState', 'expanded');
            };

            const closeSidebar = () => {
                if (window.innerWidth < 768 && sidebarMenu.classList.contains('show')) {
                    bsCollapse.hide();
                } else {
                    body.classList.add('sidebar-collapsed');
                    if(backdrop) backdrop.classList.remove('show');
                    if(mainContent) mainContent.style.marginLeft = '0';
                    localStorage.setItem('sidebarState', 'collapsed');
                }
            };

            if (desktopToggle) {
                desktopToggle.addEventListener('click', () => {
                    if (body.classList.contains('sidebar-collapsed')) {
                        openSidebar();
                    } else {
                        closeSidebar();
                    }
                });
            }

            sidebarMenu.addEventListener('show.bs.collapse', () => {
                body.classList.remove('sidebar-collapsed');
                if(backdrop) backdrop.classList.add('show');
            });
            sidebarMenu.addEventListener('hide.bs.collapse', () => {
                body.classList.add('sidebar-collapsed');
                if(backdrop) backdrop.classList.remove('show');
            });

            if(mainContent) mainContent.addEventListener('click', () => { if (!body.classList.contains('sidebar-collapsed')) closeSidebar(); });
            if(backdrop) backdrop.addEventListener('click', () => { if (!body.classList.contains('sidebar-collapsed')) closeSidebar(); });
            menuLinks.forEach(link => { link.addEventListener('click', () => { if (!body.classList.contains('sidebar-collapsed')) closeSidebar(); }); });
        }

        if (window.innerWidth >= 768) {
            if (localStorage.getItem('sidebarState') === 'collapsed') {
                body.classList.add('sidebar-collapsed');
                if(mainContent) mainContent.style.marginLeft = '0';
            } else {
                body.classList.remove('sidebar-collapsed');
                if(mainContent && sidebarMenu) mainContent.style.marginLeft = '250px';
            }
        } else {
            body.classList.add('sidebar-collapsed');
            if(mainContent) mainContent.style.marginLeft = '0';
        }
    });
    </script>

    @stack('scripts')
</body>
</html>