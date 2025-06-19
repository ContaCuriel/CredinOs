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
        .navbar-brand { /* Se ajusta para que el ancho sea controlado por el contenedor */ flex-shrink: 0; }
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

        /* ESTILOS CSS PARA LA BARRA LATERAL CONTRAÍBLE */
        .sidebar, main {
            transition: all 0.3s ease-in-out;
        }
        body.sidebar-collapsed .sidebar {
            transform: translateX(-100%);
        }
        body.sidebar-collapsed main {
            margin-left: 0;
        }
   
/* Estilos para el Backdrop */
        .sidebar-backdrop {
            position: fixed;
            top: 0;
            left: 0;
            width: 100vw;
            height: 100vh;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 99; /* Debajo del sidebar (100) pero encima de todo lo demás */
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
        
        <button class="btn btn-link d-none d-md-block" type="button" id="sidebarToggle" title="Contraer/Expandir Menú">
            <i class="bi bi-list text-white fs-4"></i>
        </button>

        <button class="navbar-toggler d-md-none" type="button" data-bs-toggle="collapse" data-bs-target="#sidebarMenu" aria-controls="sidebarMenu" aria-expanded="false" aria-label="Toggle navigation" style="z-index: 1031;">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="navbar-nav ms-auto">
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
            <nav id="sidebarMenu" class="d-md-block sidebar collapse">
                <div class="position-sticky pt-3 sidebar-sticky">
                    {{-- Tu menú completo va aquí --}}
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

             <div class="sidebar-backdrop"></div>
            <main class="px-md-4">
                {{ $slot }}
            </main>
        </div>
    </div>
<script>
document.addEventListener('DOMContentLoaded', function () {
    // --- 1. SELECCIÓN DE ELEMENTOS ---
    const body = document.body;
    const sidebarMenu = document.getElementById('sidebarMenu');
    const desktopToggle = document.getElementById('sidebarToggle');
    const mobileToggler = document.querySelector('.navbar-toggler'); // El botón de hamburguesa
    const backdrop = document.querySelector('.sidebar-backdrop');
    const mainContent = document.querySelector('main');
    const menuLinks = document.querySelectorAll('#sidebarMenu .nav-link');
    
    // Instancia del Collapse de Bootstrap para controlarlo con JS si es necesario
    const bsCollapse = new bootstrap.Collapse(sidebarMenu, { toggle: false });

    // --- 2. FUNCIONES PRINCIPALES ---

    // Función para ABRIR el menú (usada por el botón de escritorio)
    const openSidebar = () => {
        body.classList.remove('sidebar-collapsed');
        backdrop.classList.add('show'); // Mostrar el fondo oscuro
        localStorage.setItem('sidebarState', 'expanded');
    };

    // Función para CERRAR el menú
    const closeSidebar = () => {
        // En móvil, usamos el método de Bootstrap para asegurar transiciones correctas
        if (window.innerWidth < 768 && sidebarMenu.classList.contains('show')) {
            bsCollapse.hide();
        } else {
            // En escritorio, usamos nuestra clase personalizada
            body.classList.add('sidebar-collapsed');
            backdrop.classList.remove('show'); // Ocultar el fondo oscuro
            localStorage.setItem('sidebarState', 'collapsed');
        }
    };

    // --- 3. LÓGICA DE EVENTOS ---

    // A) Botón de ESCRITORIO
    if (desktopToggle) {
        desktopToggle.addEventListener('click', () => {
            if (body.classList.contains('sidebar-collapsed')) {
                openSidebar();
            } else {
                closeSidebar();
            }
        });
    }

    // B) Sincronización con los eventos de Bootstrap (para el menú MÓVIL)
    if (sidebarMenu) {
        // Cuando el menú móvil empieza a mostrarse...
        sidebarMenu.addEventListener('show.bs.collapse', () => {
            body.classList.remove('sidebar-collapsed');
            backdrop.classList.add('show'); // <-- FUNCIONALIDAD RESTAURADA
        });

        // Cuando el menú móvil empieza a ocultarse...
        sidebarMenu.addEventListener('hide.bs.collapse', () => {
            body.classList.add('sidebar-collapsed');
            backdrop.classList.remove('show'); // <-- FUNCIONALIDAD RESTAURADA
        });
    }

    // C) Cerrar el menú al hacer clic fuera o en un enlace
    // Se añade un listener al contenido principal
    mainContent.addEventListener('click', () => {
        if (!body.classList.contains('sidebar-collapsed')) {
             closeSidebar(); // <-- FUNCIONALIDAD RESTAURADA
        }
    });
    
    // Se añade un listener al fondo oscuro
    backdrop.addEventListener('click', () => {
         if (!body.classList.contains('sidebar-collapsed')) {
             closeSidebar(); // <-- FUNCIONALIDAD RESTAURADA
        }
    });
    
    // Se añade un listener a cada enlace del menú
    menuLinks.forEach(link => {
        link.addEventListener('click', () => {
            // Se cierra el menú solo si está visible (principalmente en móvil)
             if (!body.classList.contains('sidebar-collapsed')) {
                closeSidebar(); // <-- FUNCIONALIDAD RESTAURADA
            }
        });
    });

    // D) Estado inicial al cargar la página (para escritorio)
    if (window.innerWidth >= 768) {
        if (localStorage.getItem('sidebarState') === 'collapsed') {
            body.classList.add('sidebar-collapsed');
        }
    } else {
        // Asegurarse de que en móvil siempre inicie colapsado
        body.classList.add('sidebar-collapsed');
    }
});
</script>

{{-- NO OLVIDES MANTENER EL JS DE BOOTSTRAP AL FINAL DEL BODY --}}
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>

@stack('scripts')
</body>
</html>