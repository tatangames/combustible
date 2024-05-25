@extends('backend.menus.superior')

@section('content-admin-css')
    <link href="{{ asset('css/adminlte.min.css') }}" type="text/css" rel="stylesheet" />
    <link href="{{ asset('css/dataTables.bootstrap4.css') }}" type="text/css" rel="stylesheet" />
    <link href="{{ asset('css/toastr.min.css') }}" type="text/css" rel="stylesheet" />
    <link href="{{ asset('css/select2.min.css') }}" type="text/css" rel="stylesheet">
    <link href="{{ asset('css/select2-bootstrap-5-theme.min.css') }}" type="text/css" rel="stylesheet">

    <link href="{{ asset('css/estiloToggle.css') }}" type="text/css" rel="stylesheet" />

@stop

<style>


</style>

<section class="content-header">
    <div class="container-fluid">


        <div class="form-group">
            <label>Tabla de Registros</label>

        </div>

    </div>
</section>

<section class="content">
    <div class="container-fluid" style="margin-left: 15px">
        <div class="row">

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



                                <div class="row">

                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label># de Factura</label>
                                            <input type="text" id="numfactura-editar" class="form-control">
                                        </div>

                                        <div class="form-group">
                                            <label>Equipo</label>
                                            <input type="text" id="equipo-editar" maxlength="350" class="form-control">
                                        </div>

                                        <div class="form-group">
                                            <label>Placa (Opcional)</label>
                                            <input type="text" id="placa-editar" maxlength="15" class="form-control">
                                        </div>

                                        <div class="form-group">
                                            <label>Producto</label>
                                            <select class="form-control" id="producto-editar">
                                                <option value="D">DIESEL</option>
                                                <option value="R">REGULAR</option>
                                                <option value="E">ESPECIAL</option>
                                            </select>
                                        </div>

                                        <div class="form-group">
                                            <label>Línea</label>
                                            <select class="form-control" id="linea-editar">
                                                <option value="0101">0101</option>
                                            </select>
                                        </div>

                                    </div>


                                    <div class="col-md-4">

                                        <div class="form-group">
                                            <label>Fecha</label>
                                            <input type="date" id="fecha-editar"  class="form-control">
                                        </div>

                                        <div class="form-group">
                                            <label># Galones</label>
                                            <input type="number" id="galones-editar" class="form-control">
                                        </div>

                                        <div class="form-group">
                                            <label>Precio Unitario</label>
                                            <input type="number" id="precio-editar" class="form-control">
                                        </div>


                                        <div class="form-group">
                                            <label>KM (Opcional)</label>
                                            <input type="text" id="km-editar" maxlength="15" class="form-control">
                                        </div>

                                    </div>
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



@extends('backend.menus.footerjs')
@section('archivos-js')

    <script src="{{ asset('js/jquery.dataTables.js') }}" type="text/javascript"></script>
    <script src="{{ asset('js/dataTables.bootstrap4.js') }}" type="text/javascript"></script>

    <script src="{{ asset('js/toastr.min.js') }}" type="text/javascript"></script>
    <script src="{{ asset('js/axios.min.js') }}" type="text/javascript"></script>
    <script src="{{ asset('js/sweetalert2.all.min.js') }}"></script>
    <script src="{{ asset('js/alertaPersonalizada.js') }}"></script>
    <script src="{{ asset('js/select2.min.js') }}" type="text/javascript"></script>

    <script type="text/javascript">
        $(document).ready(function(){

            openLoading();

            var ruta = "{{ URL::to('/admin/facturav2/listado/tabla') }}";
            $('#tablaDatatable').load(ruta);

        });
    </script>

    <script>

        function solicitarDatos(){

            let t = document.getElementById('toggle').checked;
            let toggle = t ? 1 : 0;

            document.getElementById('tablaDatatable').innerHTML = '';
            document.getElementById('toggle').disabled = true;

            openLoading();

            var ruta = "{{ URL::to('/admin/factura/tabla/tipo') }}/" + toggle;
            $('#tablaDatatable').load(ruta, function(response, status, xhr) {
                /* if (status == "success") {

                 } else if (status == "error") {

                 }*/
                closeLoading();
                document.getElementById('toggle').disabled = false;
            });
        }


        function informacion(id){
            openLoading();
            document.getElementById("formulario-editar").reset();

            axios.post(url+'/factura/informacion', {
                'id': id
            })
                .then((response) => {
                    closeLoading();
                    if(response.data.success === 1){
                        $('#modalEditar').modal('show');
                        $('#id-editar').val(id);

                        $('#numfactura-editar').val(response.data.info.idfactura);
                        $('#equipo-editar').val(response.data.info.equipo);
                        $('#placa-editar').val(response.data.info.placa);
                        $('#fecha-editar').val(response.data.info.fecha);
                        $('#galones-editar').val(response.data.info.cantidad);
                        $('#precio-editar').val(response.data.info.unitario);
                        $('#km-editar').val(response.data.info.km);

                        if(response.data.info.producto == 'D'){
                            document.getElementById('producto-editar').options.selectedIndex = 0;
                        }else if(response.data.info.producto == 'R'){
                            document.getElementById('producto-editar').options.selectedIndex = 1;
                        }else if(response.data.info.producto == 'E'){
                            document.getElementById('producto-editar').options.selectedIndex = 2;
                        }

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
            var numfactura = document.getElementById('numfactura-editar').value;
            var equipo = document.getElementById('equipo-editar').value;
            var placa = document.getElementById('placa-editar').value;
            var producto = document.getElementById('producto-editar').value;
            var fecha = document.getElementById('fecha-editar').value;
            var galones = document.getElementById('galones-editar').value;
            var unitario = document.getElementById('precio-editar').value;
            var km = document.getElementById('km-editar').value;

            if(numfactura === ''){
                toastr.error('# Factura es requerido');
                return
            }

            if(equipo === ''){
                toastr.error('Equipo es requerido');
                return
            }

            if(fecha === ''){
                toastr.error('Fecha es requerido');
                return
            }

            if(galones === ''){
                toastr.error('# de Galones es requerido');
                return
            }

            if(unitario === ''){
                toastr.error('Precio Unitario es requerido');
                return
            }

            openLoading();
            var formData = new FormData();
            formData.append('id', id);
            formData.append('numfactura', numfactura);
            formData.append('fecha', fecha);
            formData.append('equipo', equipo);
            formData.append('placa', placa);
            formData.append('producto', producto);
            formData.append('galones', galones);
            formData.append('unitario', unitario);
            formData.append('km', km);

            axios.post(url+'/factura/actualizar', formData, {
            })
                .then((response) => {
                    closeLoading();

                    if(response.data.success === 0) {
                        toastr.error('Faltan campos para Registrar');
                    }
                    else if(response.data.success === 1){
                        toastr.success('Actualizado');
                        $('#modalEditar').modal('hide');
                        solicitarDatos();
                    }
                    else {
                        toastr.error('error al registrar');
                    }
                })
                .catch((error) => {
                    toastr.error('error al registrar');
                    closeLoading();
                });
        }


        function modalBorrar(id){
            Swal.fire({
                title: 'Borrar?',
                text: "",
                icon: 'info',
                showCancelButton: true,
                confirmButtonColor: '#28a745',
                cancelButtonColor: '#d33',
                cancelButtonText: 'Cancelar',
                confirmButtonText: 'Si'
            }).then((result) => {
                if (result.isConfirmed) {
                    solicitarBorrar(id);
                }
            })
        }


        function solicitarBorrar(idfila){

            openLoading();

            axios.post(url+'/factura/borrar', {
                'id': idfila
            })
                .then((response) => {
                    closeLoading();
                    if(response.data.success === 1){
                        toastr.success('Borrado');
                        solicitarDatos();
                    }else{
                        toastr.error('Error al borrar');
                    }
                })
                .catch((error) => {
                    toastr.error('Error al borrar');
                    closeLoading();
                });
        }


    </script>



@stop
