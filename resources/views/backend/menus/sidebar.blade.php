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


                    @can('sidebar.combustible')

                        <li class="nav-item">
                            <a href="#" class="nav-link">
                                <i class="far fa-edit"></i>
                                <p>
                                    Factura
                                    <i class="fas fa-angle-left right"></i>
                                </p>
                            </a>

                            <ul class="nav nav-treeview">



                                <li class="nav-item">
                                    <a href="{{ route('admin.facturav2.index') }}" target="frameprincipal" class="nav-link">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>Nueva Factura</p>
                                    </a>
                                </li>


                                <li class="nav-item">
                                    <a href="{{ route('admin.facturav2.listado.index') }}" target="frameprincipal" class="nav-link">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>Listado Factura</p>
                                    </a>
                                </li>


                            </ul>
                        </li>


                        <li class="nav-item">
                            <a href="#" class="nav-link">
                                <i class="far fa-edit"></i>
                                <p>
                                    Configuración
                                    <i class="fas fa-angle-left right"></i>
                                </p>
                            </a>

                            <ul class="nav nav-treeview">



                                <li class="nav-item">
                                    <a href="{{ route('admin.equipos.index') }}" target="frameprincipal" class="nav-link">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>Equipos</p>
                                    </a>
                                </li>


                                <li class="nav-item">
                                    <a href="{{ route('admin.nombres.index') }}" target="frameprincipal" class="nav-link">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>Nombre a Reporte</p>
                                    </a>
                                </li>

                                <li class="nav-item">
                                    <a href="{{ route('admin.fondos.index') }}" target="frameprincipal" class="nav-link">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>Tipo de Fondos</p>
                                    </a>
                                </li>

                                <li class="nav-item">
                                    <a href="{{ route('admin.distritos.index') }}" target="frameprincipal" class="nav-link">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>Distritos</p>
                                    </a>
                                </li>

                                <li class="nav-item">
                                    <a href="{{ route('admin.contratos.index') }}" target="frameprincipal" class="nav-link">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>Contratos</p>
                                    </a>
                                </li>

                            </ul>
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
                                    <a href="{{ route('admin.reporte.facturacion.equipos') }}" target="frameprincipal" class="nav-link">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>Equipos</p>
                                    </a>
                                </li>

                                <li class="nav-item">
                                    <a href="{{ route('admin.reporte.facturacion.factura') }}" target="frameprincipal" class="nav-link">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>Factura</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="{{ route('admin.reporte.equipos.consolidado') }}" target="frameprincipal" class="nav-link">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>Equipos Consolidado</p>
                                    </a>
                                </li>

                                <li class="nav-item">
                                    <a href="{{ route('admin.reporte.contrato') }}" target="frameprincipal" class="nav-link">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>Contrato</p>
                                    </a>
                                </li>

                            </ul>
                        </li>


                        <li class="nav-item">
                            <a href="#" class="nav-link">
                                <i class="far fa-edit"></i>
                                <p>
                                    Sistema Anterior
                                    <i class="fas fa-angle-left right"></i>
                                </p>
                            </a>

                            <ul class="nav nav-treeview">


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
                                    <a href="{{ route('admin.reporte.fechas.index') }}" target="frameprincipal" class="nav-link">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>Reporte Fechas</p>
                                    </a>
                                </li>

                                <li class="nav-item">
                                    <a href="{{ route('admin.reporte.equipos.index') }}" target="frameprincipal" class="nav-link">
                                        <i class="far fa-circle nav-icon"></i>
                                        <p>Reporte Equipos</p>
                                    </a>
                                </li>

                            </ul>
                        </li>

                    @endcan



            </ul>
        </nav>

    </div>
</aside>






