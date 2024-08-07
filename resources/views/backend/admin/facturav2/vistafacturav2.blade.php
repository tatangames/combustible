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

    html, body {
        overflow-x: hidden;
    }
</style>

<section class="content-header">
    <div class="container-fluid">

        <div class="form-group">

            <div class="form-group" style="width: 30%">
                <label>Filtro por Equipo</label>
                <select class="form-control" id="select-equipo">
                    <option value="0" selected>TODOS</option>
                    @foreach($arrayEquipos as $dato)
                        <option value="{{ $dato->id }}">{{ $dato->nombre }}</option>
                    @endforeach
                </select>
            </div>

            <button type="button" class="btn btn-primary" onclick="filtrarDatos()">Filtrar</button>

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
    <div class="modal-dialog modal-xl">
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
                                            <select class="form-control" id="equipo-editar">
                                            </select>
                                        </div>


                                        <div class="form-group">
                                            <label>Producto</label>
                                            <select class="form-control" id="producto-editar">
                                            </select>
                                        </div>

                                        <div class="form-group">
                                            <label>KM (Opcional)</label>
                                            <input type="text" id="km-editar" maxlength="15" class="form-control">
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
                                            <label>Descripción</label>
                                            <input type="text" id="descripcion-editar" class="form-control">
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

            $('#select-equipo').select2({
                theme: "bootstrap-5",
                "language": {
                    "noResults": function(){
                        return "Búsqueda no encontrada";
                    }
                },
            });

            var ruta = "{{ URL::to('/admin/facturav2/listado/tabla') }}";
            $('#tablaDatatable').load(ruta);

        });
    </script>

    <script>

        function recargar(){
            $('#select-equipo').val(0).trigger('change');

            var ruta = "{{ URL::to('/admin/facturav2/listado/tabla') }}";
            $('#tablaDatatable').load(ruta);
        }


        function informacion(id){
            openLoading();
            document.getElementById("formulario-editar").reset();

            axios.post(url+'/facturav2/informacion', {
                'id': id
            })
                .then((response) => {
                    closeLoading();
                    if(response.data.success === 1){
                        $('#modalEditar').modal('show');
                        $('#id-editar').val(id);

                        $('#numfactura-editar').val(response.data.info.numero_factura);
                        $('#fecha-editar').val(response.data.info.fecha);
                        $('#galones-editar').val(response.data.info.cantidad);
                        $('#precio-editar').val(response.data.info.unitario);
                        $('#km-editar').val(response.data.info.km);
                        $('#descripcion-editar').val(response.data.info.descripcion);

                        document.getElementById("producto-editar").options.length = 0;
                        document.getElementById("equipo-editar").options.length = 0;

                        $.each(response.data.arrayproducto, function( key, val ){
                            if(response.data.info.id_tipocombustible == val.id){
                                $('#producto-editar').append('<option value="' +val.id +'" selected="selected">'+ val.nombre +'</option>');
                            }else{
                                $('#producto-editar').append('<option value="' +val.id +'">'+ val.nombre +'</option>');
                            }
                        });


                        $.each(response.data.arrayequipo, function( key, val ){
                            if(response.data.info.id_equipo == val.id){
                                $('#equipo-editar').append('<option value="' +val.id +'" selected="selected">'+ val.nombre +'</option>');
                            }else{
                                $('#equipo-editar').append('<option value="' +val.id +'">'+ val.nombre +'</option>');
                            }
                        });

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
            var producto = document.getElementById('producto-editar').value;
            var fecha = document.getElementById('fecha-editar').value;
            var galones = document.getElementById('galones-editar').value;
            var unitario = document.getElementById('precio-editar').value;
            var km = document.getElementById('km-editar').value;
            var descripcion = document.getElementById('descripcion-editar').value;

            if(numfactura === ''){
                toastr.error('# Factura es requerido');
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
            formData.append('producto', producto);
            formData.append('galones', galones);
            formData.append('unitario', unitario);
            formData.append('km', km);
            formData.append('descripcion', descripcion);

            axios.post(url+'/facturav2/actualizar', formData, {
            })
                .then((response) => {
                    closeLoading();

                    if(response.data.success === 0) {
                        toastr.error('Faltan campos para Registrar');
                    }
                    else if(response.data.success === 1){
                        toastr.success('Actualizado');
                        $('#modalEditar').modal('hide');
                        recargar()
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

            axios.post(url+'/facturav2/borrar', {
                'id': idfila
            })
                .then((response) => {
                    closeLoading();
                    if(response.data.success === 1){
                        toastr.success('Borrado');
                        recargar();
                    }else{
                        toastr.error('Error al borrar');
                    }
                })
                .catch((error) => {
                    toastr.error('Error al borrar');
                    closeLoading();
                });
        }


        function filtrarDatos(){
            var id = document.getElementById('select-equipo').value;

            openLoading();

            var ruta = "{{ URL::to('/admin/facturav2/listado/tabla') }}/" + id;
            $('#tablaDatatable').load(ruta);
        }


    </script>



@stop
