<div class="position-sticky pt-3 sidebar-sticky">
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}">
                                <i class="bi bi-house-door"></i> Home / Inicio
                            </a>
                        </li>
                    </ul>

                    @can('ver-menu-rh')
                        <h6 class="sidebar-heading d-flex justify-content-between align-items-center px-3 mt-4 mb-1 text-muted"><span>RECURSOS HUMANOS</span></h6>
                        <ul class="nav flex-column mb-2">
                            @can('ver-empleados')<li class="nav-item"><a class="nav-link {{ request()->routeIs('empleados.*') ? 'active' : '' }}" href="{{ route('empleados.index') }}"><i class="bi bi-people"></i> Empleados</a></li>@endcan
                            @can('ver-contratos')<li class="nav-item"><a class="nav-link {{ request()->routeIs('contratos.*') ? 'active' : '' }}" href="{{ route('contratos.index') }}"><i class="bi bi-file-earmark-text"></i> Contratos</a></li>@endcan
                            @can('ver-asistencias')<li class="nav-item"><a class="nav-link {{ request()->routeIs('asistencia.*') ? 'active' : '' }}" href="{{ route('asistencia.vistaPeriodo') }}"><i class="bi bi-calendar-check"></i> Asistencias</a></li>@endcan
                            @can('ver-vacaciones')<li class="nav-item"><a class="nav-link {{ request()->routeIs('vacaciones.*') ? 'active' : '' }}" href="{{ route('vacaciones.index') }}"><i class="bi bi-briefcase-fill"></i> Vacaciones</a></li>@endcan
                            @can('ver-deducciones')<li class="nav-item"><a class="nav-link {{ request()->routeIs('deducciones.*') ? 'active' : '' }}" href="{{ route('deducciones.index') }}"><i class="bi bi-wallet2"></i> Deducciones</a></li>@endcan
                            @can('ver-lista-raya')<li class="nav-item"><a class="nav-link {{ request()->routeIs('lista_de_raya.*') ? 'active' : '' }}" href="{{ route('lista_de_raya.index') }}"><i class="bi bi-file-spreadsheet"></i> Lista de Raya</a></li>@endcan
                            @can('ver-finiquitos')<li class="nav-item"><a class="nav-link {{ request()->routeIs('finiquitos.*') ? 'active' : '' }}" href="{{ route('finiquitos.index') }}"><i class="bi bi-person-x"></i> Finiquitos y Liquidaciones</a></li>@endcan
                            @can('ver-gestion-imss')<li class="nav-item"><a class="nav-link {{ request()->routeIs('imss.*') ? 'active' : '' }}" href="{{ route('imss.index') }}"><i class="bi bi-shield-check"></i> Gestión IMSS</a></li>@endcan
                        </ul>
                    @endcan

                    @can('ver-menu-contabilidad')
                        <h6 class="sidebar-heading d-flex justify-content-between align-items-center px-3 mt-4 mb-1 text-muted"><span>CONTABILIDAD</span></h6>
                        <ul class="nav flex-column mb-2">
                            @can('ver-aguinaldo')<li class="nav-item"><a class="nav-link {{ request()->routeIs('aguinaldo.*') ? 'active' : '' }}" href="{{ route('aguinaldo.index') }}"><i class="bi bi-gift-fill"></i> Cálculo de Aguinaldo</a></li>@endcan
                        </ul>
                    @endcan
                    
                    @can('ver-menu-administracion')
                        <h6 class="sidebar-heading d-flex justify-content-between align-items-center px-3 mt-4 mb-1 text-muted"><span>ADMINISTRACIÓN</span></h6>
                        <ul class="nav flex-column mb-2">
                            <li class="nav-item"><a class="nav-link {{ request()->routeIs('profile.edit') ? 'active' : '' }}" href="{{ route('profile.edit') }}"><i class="bi bi-person-circle"></i> Mi Perfil</a></li>
                            @can('ver-usuarios')<li class="nav-item"><a class="nav-link {{ request()->routeIs('users.*') ? 'active' : '' }}" href="{{ route('users.index') }}"><i class="bi bi-person-gear"></i> Usuarios del Sistema</a></li>@endcan
                            @can('ver-roles')<li class="nav-item"><a class="nav-link {{ request()->routeIs('roles.*') ? 'active' : '' }}" href="{{ route('roles.index') }}"><i class="bi bi-shield-lock-fill"></i> Roles y Permisos</a></li>@endcan
                        </ul>
                    @endcan

                    @can('ver-menu-configuracion')
                        <h6 class="sidebar-heading d-flex justify-content-between align-items-center px-3 mt-4 mb-1 text-muted"><span>CONFIGURACIÓN</span></h6>
                        <ul class="nav flex-column mb-2">
                            @can('ver-sucursales')<li class="nav-item"><a class="nav-link {{ request()->routeIs('sucursales.*') ? 'active' : '' }}" href="{{ route('sucursales.index') }}"><i class="bi bi-building"></i> Sucursales</a></li>@endcan
                            @can('ver-puestos')<li class="nav-item"><a class="nav-link {{ request()->routeIs('puestos.*') ? 'active' : '' }}" href="{{ route('puestos.index') }}"><i class="bi bi-briefcase"></i> Puestos</a></li>@endcan
                            @can('ver-patrones')<li class="nav-item"><a class="nav-link {{ request()->routeIs('patrones.*') ? 'active' : '' }}" href="{{ route('patrones.index') }}"><i class="bi bi-person-badge"></i> Patrones (Empresas)</a></li>@endcan
                            @can('ver-horarios')<li class="nav-item"><a class="nav-link {{ request()->routeIs('horarios.*') ? 'active' : '' }}" href="{{ route('horarios.index') }}"><i class="bi bi-clock-history"></i> Horarios</a></li>@endcan
                        </ul>
                    @endcan
                </div>
            </nav>

            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                {{-- Esto es lo que lo hace un layout compatible con <x-app-layout> --}}
                @if (isset($slot))
                    {{ $slot }}
                @else
                    @yield('content')
                @endif
            </main>
        </div>
    </div>
    