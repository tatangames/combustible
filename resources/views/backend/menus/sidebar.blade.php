<aside class="main-sidebar sidebar-dark-primary elevation-4">
    <a href="#" class="brand-link">
        <img src="{{ asset('images/icono-sistema.png') }}" alt="Logo" class="brand-image img-circle elevation-3" >
        <span class="brand-text font-weight" style="color: white">COMBUSTIBLE</span>
    </a>

    <div class="sidebar">

        <nav class="mt-2">
            <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">

                @can('vista.roles.index')
                 <li class="nav-item">
                     <a href="#" class="nav-link">
                        <i class="far fa-edit"></i>
                        <p>
                            Roles y Permisos
                            <i class="fas fa-angle-left right"></i>
                        </p>
                    </a>

                    <ul class="nav nav-treeview">
                        <li class="nav-item">
                            <a href="{{ route('admin.roles.index') }}" target="frameprincipal" class="nav-link">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Roles</p>
                            </a>
                        </li>

                        <li class="nav-item">
                            <a href="{{ route('admin.permisos.index') }}" target="frameprincipal" class="nav-link">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Permisos</p>
                            </a>
                        </li>

                    </ul>
                 </li>
                @endcan

                @can('vista.factura.index')

                    <li class="nav-item">
                        <a href="{{ route('admin.registrar.factura.index') }}" target="frameprincipal" class="nav-link">
                            <i class="far fa-circle nav-icon"></i>
                            <p>Registrar Factura</p>
                        </a>
                    </li>

                    <li class="nav-item">
                        <a href="{{ route('admin.factura.editar.index') }}" target="frameprincipal" class="nav-link">
                            <i class="far fa-circle nav-icon"></i>
                            <p>Editar Factura</p>
                        </a>
                    </li>

                    <li class="nav-item">
                        <a href="{{ route('admin.nuevo.equipo.index') }}" target="frameprincipal" class="nav-link">
                            <i class="far fa-circle nav-icon"></i>
                            <p>Nuevo Equipo</p>
                        </a>
                    </li>

                    <li class="nav-item">
                        <a href="{{ route('admin.factura.reporte.index') }}" target="frameprincipal" class="nav-link">
                            <i class="far fa-circle nav-icon"></i>
                            <p>Reporte</p>
                        </a>
                    </li>

                    <li class="nav-item">
                        <a href="{{ route('admin.configuracion.nombre.index') }}" target="frameprincipal" class="nav-link">
                            <i class="far fa-circle nav-icon"></i>
                            <p>Configuración</p>
                        </a>
                    </li>

                @endcan

            </ul>
        </nav>

    </div>
</aside>






