<aside class="main-sidebar sidebar-dark-primary elevation-4">
    <a href="#" class="brand-link">
        <img src="{{ asset('images/icono-sistema.png') }}" alt="Logo" class="brand-image img-circle elevation-3" >
        <span class="brand-text font-weight" style="color: white">COMBUSTIBLE</span>
    </a>

    <div class="sidebar">

        <nav class="mt-2">
            <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">

                @can('sidebar.roles.y.permisos')
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

                    <li class="nav-item">
                        <a href="{{ route('admin.nuevafactura.index') }}" target="frameprincipal" class="nav-link">
                            <i class="far fa-circle nav-icon"></i>
                            <p>Nueva Factura</p>
                        </a>
                    </li>


                    <li class="nav-item">
                        <a href="{{ route('admin.factura.index') }}" target="frameprincipal" class="nav-link">
                            <i class="far fa-circle nav-icon"></i>
                            <p>Tabla Factura</p>
                        </a>
                    </li>



                    <li class="nav-item">
                        <a href="#" class="nav-link">
                            <i class="far fa-edit"></i>
                            <p>
                                Reportes
                                <i class="fas fa-angle-left right"></i>
                            </p>
                        </a>

                        <ul class="nav nav-treeview">
                            <li class="nav-item">
                                <a href="{{ route('admin.reporte.fechas.index') }}" target="frameprincipal" class="nav-link">
                                    <i class="far fa-circle nav-icon"></i>
                                    <p>Por Fechas</p>
                                </a>
                            </li>

                            <li class="nav-item">
                                <a href="{{ route('admin.permisos.index') }}" target="frameprincipal" class="nav-link">
                                    <i class="far fa-circle nav-icon"></i>
                                    <p>Por Equipo</p>
                                </a>
                            </li>

                            <li class="nav-item">
                                <a href="{{ route('admin.permisos.index') }}" target="frameprincipal" class="nav-link">
                                    <i class="far fa-circle nav-icon"></i>
                                    <p>Por Factura</p>
                                </a>
                            </li>

                        </ul>
                    </li>



            </ul>
        </nav>

    </div>
</aside>






