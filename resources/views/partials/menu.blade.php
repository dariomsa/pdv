<div class="sidebar">
    <nav class="sidebar-nav">

        <ul class="nav">
            @can('user_management_access')
                <li class="nav-item nav-dropdown">
                    <a class="nav-link  nav-dropdown-toggle" href="#">
                        <i class="fa-fw fas fa-users nav-icon">

                        </i>
                        {{ trans('cruds.userManagement.title') }}
                    </a>
                    <ul class="nav-dropdown-items">
                        @can('permission_access')
                            <li class="nav-item">
                                <a href="{{ route("admin.permissions.index") }}" class="nav-link {{ request()->is('admin/permissions') || request()->is('admin/permissions/*') ? 'active' : '' }}">
                                    <i class="fa-fw fas fa-unlock-alt nav-icon">

                                    </i>
                                    {{ trans('cruds.permission.title') }}
                                </a>
                            </li>
                        @endcan
                        @can('role_access')
                            <li class="nav-item">
                                <a href="{{ route("admin.roles.index") }}" class="nav-link {{ request()->is('admin/roles') || request()->is('admin/roles/*') ? 'active' : '' }}">
                                    <i class="fa-fw fas fa-briefcase nav-icon">

                                    </i>
                                    {{ trans('cruds.role.title') }}
                                </a>
                            </li>
                        @endcan
                        @can('user_access')
                            <li class="nav-item">
                                <a href="{{ route("admin.users.index") }}" class="nav-link {{ request()->is('admin/users') || request()->is('admin/users/*') ? 'active' : '' }}">
                                    <i class="fa-fw fas fa-user nav-icon">

                                    </i>
                                    {{ trans('cruds.user.title') }}
                                </a>
                            </li>
                        @endcan
                    </ul>
                </li>
            @endcan

            <!--inscripciones -->
            @can('inscripciones_access')
                <li class="nav-item">
                    <a href="{{ route("admin.inscripciones.index") }}" class="nav-link {{ request()->is('admin/inscripciones') || request()->is('admin/inscripciones/*') ? 'active' : '' }}">
                        <i class="fa-fw fas fa fa-pencil nav-icon">

                        </i>
                        INSCRIPCIONES
                    </a>
                </li>
            @endcan
            <!--inscripciones -->
			
			  <!--inscripciones -->
            @can('corporativas_access')
                <li class="nav-item">
                    <a href="{{ route("admin.corporativas.index") }}" class="nav-link {{ request()->is('admin/corporativas') || request()->is('admin/expense-categories/*') ? 'active' : '' }}">
                        <i class="fa-fw fas fa fa-pencil nav-icon">

                        </i>
                        CORPORATIVAS
                    </a>
                </li>
            @endcan
            <!--inscripciones -->

			     <!--inscripciones -->
                          @can('corporativas_access')
                <li class="nav-item">
                    <a href="{{ route("admin.inscripciones_gratuitas.index") }}" class="nav-link {{ request()->is('admin/expense-categories') || request()->is('admin/expense-categories/*') ? 'active' : '' }}">
                        <i class="fa-fw fas fa fa-pencil nav-icon">

                        </i>
                        GRATUITAS
                    </a>
                </li>
            @endcan
            <!--inscripciones -->				 
            <!--ventas-->

            @can('cierre_caja_access')
                <li class="nav-item nav-dropdown">
                    <a class="nav-link  nav-dropdown-toggle" href="#">
                        <i class="fa-fw fas fa-users nav-icon">

                        </i>
                       VENTAS
                    </a>
                    <ul class="nav-dropdown-items">
                        
                        @can('cierre_caja_access')
                            <li class="nav-item">
								<a href="{{ route("admin.cierrecaja.index") }}" class="nav-link {{ request()->is('admin/expense-categories') || request()->is('admin/expense-categories/*') ? 'active' : '' }}">
                                    <i class="fa-fw fas fa-usd nav-icon">

                                    </i>
                                   Cierre de Caja
                                </a>
                            </li>
                        @endcan

                        @can('cierre_caja_access')
                            <li class="nav-item">
								<a href="{{ route("admin.cajadetalle.index") }}" class="nav-link {{ request()->is('admin/expense-categories') || request()->is('admin/expense-categories/*') ? 'active' : '' }}">
                                    <i class="fa-fw fas fa-usd nav-icon">

                                    </i>
                                   Cierre Detallado 
                                </a>
                            </li>
                        @endcan
                    
                    </ul>
                </li>
            @endcan

            <!-- fin ventas -->

            <!-- reportes -->

            @can('inscripciones_access')
                <li class="nav-item nav-dropdown">
                    <a class="nav-link  nav-dropdown-toggle" href="#">
                        <i class="fa-fw fas fa-users nav-icon">

                        </i>
                     REPORTES
                    </a>
                    <ul class="nav-dropdown-items">
                        @can('ventas_diarias_access')
                            <li class="nav-item">
                                <a href="{{ route("admin.reporteventasdiarias.index") }}" class="nav-link {{ request()->is('admin/permissions') || request()->is('admin/permissions/*') ? 'active' : '' }}">
                                    <i class="fa-fw fas fa-unlock-alt nav-icon">

                                    </i>
                                    Ventas Diarias
                                </a>
                            </li>
                        @endcan
                      
                        @can('ventas_generales_access')
                            <li class="nav-item">
                                <a href="{{ route("admin.reporteventasgenerales.index") }}" class="nav-link {{ request()->is('admin/permissions') || request()->is('admin/permissions/*') ? 'active' : '' }}">
                                    <i class="fa-fw fas fa-unlock-alt nav-icon">

                                    </i>
                                    Ventas Generales
                                </a>
                            </li>
                        @endcan			
                     
                                         
                    </ul>
                </li>
            @endcan



              <li class="nav-item">
                    <a href="{{ route("admin.parametros.index") }}" class="nav-link {{ request()->is('admin/expense-categories') || request()->is('admin/expense-categories/*') ? 'active' : '' }}">
                        <i class="fa-fw fas fa fa-pencil nav-icon">

                        </i>
                        PARÁMETROS
                    </a>
                </li>

            <li class="nav-item">
                <a href="#" class="nav-link" onclick="event.preventDefault(); document.getElementById('logoutform').submit();">
                    <i class="nav-icon fas fa-fw fa-sign-out-alt">

                    </i>
                  Salir
                </a>
            </li>
        </ul>

    </nav>
    <button class="sidebar-minimizer brand-minimizer" type="button"></button>
</div>