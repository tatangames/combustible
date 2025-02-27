@extends('backend.menus.superior')

@section('content-admin-css')
    <link href="{{ asset('css/adminlte.min.css') }}" type="text/css" rel="stylesheet" />
    <link href="{{ asset('css/dataTables.bootstrap4.css') }}" type="text/css" rel="stylesheet" />
    <link href="{{ asset('css/toastr.min.css') }}" type="text/css" rel="stylesheet" />
    <link href="{{ asset('css/estiloToggle.css') }}" type="text/css" rel="stylesheet" />
@stop

<style>
    table{
        /*Ajustar tablas*/
        table-layout:fixed;
    }
</style>


<div id="divcontenedor" style="display: none">

    <section class="content-header">
        <div class="row mb-2">
            <div class="col-sm-6">
                <button type="button" onclick="modalAgregar()" class="btn btn-primary btn-sm">
                    <i class="fas fa-plus-square"></i>
                    Nuevo registro
                </button>
            </div>

            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item">Contratos</li>
                    <li class="breadcrumb-item active">Listado</li>
                </ol>
            </div>

        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <div class="card card-gray-dark">
                <div class="card-header">
                    <h3 class="card-title">Listado</h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-12">
                            <div id="tablaDatatable">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <div class="modal fade" id="modalAgregar">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Registro</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="formulario-nuevo">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-12">

                                    <div class="form-group">
                                        <label>Proveedor</label>
                                        <input type="text" id="proveedor-nuevo" autocomplete="off" class="form-control" maxlength="100" />
                                    </div>

                                    <div class="form-group">
                                        <label>Proceso Referencia</label>
                                        <input type="text" id="proceso-referencia-nuevo" autocomplete="off" class="form-control" maxlength="100" />
                                    </div>

                                    <div class="form-group">
                                        <label>Nombre Proceso</label>
                                        <input type="text" id="proceso-nuevo" autocomplete="off" class="form-control" maxlength="600" />
                                    </div>

                                    <div class="form-group col-md-5">
                                        <label>Fecha Desde</label>
                                        <input type="date" id="fechadesde-nuevo" autocomplete="off" class="form-control" />
                                    </div>

                                    <div class="form-group col-md-5">
                                        <label>Fecha Hasta</label>
                                        <input type="date" id="fechahasta-nuevo" autocomplete="off" class="form-control" />
                                    </div>

                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer justify-content-between">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
                    <button type="button" class="btn btn-primary btn-sm" onclick="nuevoRegistro()">Guardar</button>
                </div>
            </div>
        </div>
    </div>

    <!-- modal editar -->
    <div class="modal fade" id="modalEditar">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Editar</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="formulario-editar">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-12">

                                    <div class="form-group">
                                        <input type="hidden" id="id-editar"/>
                                    </div>

                                    <div class="form-group">
                                        <label>Proveedor</label>
                                        <input type="text" id="proveedor-editar" autocomplete="off" class="form-control" maxlength="100" />
                                    </div>

                                    <div class="form-group">
                                        <label>Proceso Referencia</label>
                                        <input type="text" id="proceso-referencia-editar" autocomplete="off" class="form-control" maxlength="100" />
                                    </div>

                                    <div class="form-group">
                                        <label>Nombre Proceso</label>
                                        <input type="text" id="proceso-editar" autocomplete="off" class="form-control" maxlength="600" />
                                    </div>

                                    <div class="form-group col-md-5">
                                        <label>Fecha Desde</label>
                                        <input type="date" id="fechadesde-editar" autocomplete="off" class="form-control" />
                                    </div>

                                    <div class="form-group col-md-5">
                                        <label>Fecha Hasta</label>
                                        <input type="date" id="fechahasta-editar" autocomplete="off" class="form-control" />
                                    </div>

                                </div>
                            </div>
                        </div>
                    </form>
                </div>

                <div class="modal-footer justify-content-between">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
                    <button type="button" class="btn btn-primary btn-sm" onclick="editarRegistro()">Actualizar</button>
                </div>
            </div>
        </div>
    </div>

</div>


@extends('backend.menus.footerjs')
@section('archivos-js')

    <script src="{{ asset('js/jquery.dataTables.js') }}" type="text/javascript"></script>
    <script src="{{ asset('js/dataTables.bootstrap4.js') }}" type="text/javascript"></script>

    <script src="{{ asset('js/toastr.min.js') }}" type="text/javascript"></script>
    <script src="{{ asset('js/axios.min.js') }}" type="text/javascript"></script>
    <script src="{{ asset('js/sweetalert2.all.min.js') }}"></script>
    <script src="{{ asset('js/alertaPersonalizada.js') }}"></script>

    <script type="text/javascript">
        $(document).ready(function(){

            var ruta = "{{ URL::to('/admin/contratos/tabla') }}";
            $('#tablaDatatable').load(ruta);

            document.getElementById("divcontenedor").style.display = "block";
        });
    </script>

    <script>

        function recargar(){
            var ruta = "{{ URL::to('/admin/contratos/tabla') }}";
            $('#tablaDatatable').load(ruta);
        }

        function modalAgregar(){
            document.getElementById("formulario-nuevo").reset();
            $('#modalAgregar').modal('show');
        }

        function nuevoRegistro(){

            var proveedor = document.getElementById('proveedor-nuevo').value;
            var procesoReferencia = document.getElementById('proceso-referencia-nuevo').value;
            var proceso = document.getElementById('proceso-nuevo').value;
            var fechaDesde = document.getElementById('fechadesde-nuevo').value;
            var fechaHasta = document.getElementById('fechahasta-nuevo').value;

            if(fechaDesde === ''){
                toastr.error('Fecha Desde es requerido');
                return;
            }

            if(fechaHasta === ''){
                toastr.error('Fecha Hasta es requerido');
                return;
            }

            openLoading();
            var formData = new FormData();
            formData.append('proveedor', proveedor);
            formData.append('procesoReferencia', procesoReferencia);
            formData.append('proceso', proceso);
            formData.append('fechaDesde', fechaDesde);
            formData.append('fechaHasta', fechaHasta);

            axios.post(url+'/contratos/nuevo', formData, {
            })
                .then((response) => {
                    closeLoading();
                    if(response.data.success === 1){
                        toastr.success('Registrado');
                        $('#modalAgregar').modal('hide');
                        recargar();
                    }
                    else {
                        toastr.error('Error al registrar');
                    }
                })
                .catch((error) => {
                    toastr.error('Error al registrar');
                    closeLoading();
                });
        }

        function infoEditar(id){
            openLoading();
            document.getElementById("formulario-editar").reset();

            axios.post(url+'/contratos/informacion',{
                'id': id
            })
                .then((response) => {
                    closeLoading();
                    if(response.data.success === 1){
                        $('#modalEditar').modal('show');
                        $('#id-editar').val(id);

                        $('#proveedor-editar').val(response.data.info.proveedor);
                        $('#proceso-referencia-editar').val(response.data.info.proceso_ref);
                        $('#proceso-editar').val(response.data.info.nombre_proceso);
                        $('#fechadesde-editar').val(response.data.info.fecha_desde);
                        $('#fechahasta-editar').val(response.data.info.fecha_hasta);

                    }else{
                        toastr.error('Información no encontrada');
                    }

                })
                .catch((error) => {
                    closeLoading();
                    toastr.error('Información no encontrada');
                });
        }


        function editarRegistro(){
            var id = document.getElementById('id-editar').value;
            var proveedor = document.getElementById('proveedor-editar').value;
            var procesoReferencia = document.getElementById('proceso-referencia-editar').value;
            var proceso = document.getElementById('proceso-editar').value;
            var fechaDesde = document.getElementById('fechadesde-editar').value;
            var fechaHasta = document.getElementById('fechahasta-editar').value;

            if(fechaDesde === ''){
                toastr.error('Fecha Desde es requerido');
                return;
            }

            if(fechaHasta === ''){
                toastr.error('Fecha Hasta es requerido');
                return;
            }


            openLoading();
            var formData = new FormData();
            formData.append('id', id);
            formData.append('proveedor', proveedor);
            formData.append('procesoReferencia', procesoReferencia);
            formData.append('proceso', proceso);
            formData.append('fechaDesde', fechaDesde);
            formData.append('fechaHasta', fechaHasta);

            axios.post(url+'/contratos/editar', formData, {
            })
                .then((response) => {
                    closeLoading();

                    if(response.data.success === 1){
                        toastr.success('Actualizado');
                        $('#modalEditar').modal('hide');
                        recargar();
                    }
                    else {
                        toastr.error('Error al actualizar');
                    }
                })
                .catch((error) => {
                    toastr.error('Error al actualizar');
                    closeLoading();
                });
        }


        function infoDetalle(id){
            window.location.href="{{ url('/admin/contratos/detalle/index') }}/" + id;
        }


    </script>


@endsection
