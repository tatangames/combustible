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
        <div class="container-fluid">

        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <div class="card card-success">
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

    <div class="modal fade" id="modalEditar">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Editar Factura</h4>
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
                                        <label>Factura Nº</label>
                                        <input type="hidden" id="id-editar">
                                        <input type="text" class="form-control" id="factura-editar">
                                    </div>

                                    <div class="form-group">
                                        <label class="control-label">Equipo: </label>
                                        <select id="select-equipo" class="form-control">
                                        </select>
                                    </div>

                                    <div class="form-group">
                                        <label class="control-label">Producto: </label>
                                        <select id="select-producto" class="form-control">
                                            <option value="D">DIESEL</option>
                                            <option value="R">REGULAR</option>
                                            <option value="E">ESPECIAL</option>
                                        </select>
                                    </div>

                                    <div class="form-group">
                                        <label>Fecha</label>
                                        <input type="date" class="form-control" id="fecha-editar">
                                    </div>

                                    <div class="form-group">
                                        <label>Galones</label>
                                        <input type="number" class="form-control" id="galones-editar">
                                    </div>

                                    <div class="form-group">
                                        <label>Precio Unitario</label>
                                        <input type="number" class="form-control" id="precio-editar">
                                    </div>

                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer justify-content-between">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
                    <button type="button" class="btn btn-primary" onclick="editar()">Actualizar</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalBorrar">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Borrar Registro?</h4>
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
                                        <input type="hidden" id="id-borrar">
                                    </div>

                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer justify-content-between">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
                    <button type="button" class="btn btn-danger" onclick="borrar()">Borrar</button>
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
            var ruta = "{{ URL::to('/admin/factura/editar/tabla') }}";
            $('#tablaDatatable').load(ruta);
            document.getElementById("divcontenedor").style.display = "block";
        });
    </script>

    <script>

        function recargar(){
            var ruta = "{{ url('/admin/factura/editar/tabla') }}";
            $('#tablaDatatable').load(ruta);
        }

        function modalBorrar(id){
            $('#id-borrar').val(id);
            $('#modalBorrar').modal('show');
        }

        function borrar(){
            var idborrar = document.getElementById('id-borrar').value;

            openLoading();
            var formData = new FormData();
            formData.append('id', idborrar);

            axios.post(url+'/factura/borrar', formData, {
            })
                .then((response) => {
                    closeLoading();
                    if(response.data.success === 1){
                        toastr.success('eliminado correctamente');
                        $('#modalBorrar').modal('hide');
                        recargar();
                    }
                    else {
                        toastr.error('Error al eliminar');
                    }
                })
                .catch((error) => {
                    toastr.error('Error al eliminar');
                    closeLoading();
                });
        }

        function modalInfo(id){
            openLoading();
            document.getElementById("formulario-editar").reset();

            axios.post(url+'/factura/informacion',{
                'id': id
            })
                .then((response) => {
                    closeLoading();
                    if(response.data.success === 1){
                        $('#modalEditar').modal('show');
                        $('#id-editar').val(response.data.factura.id);

                        $('#factura-editar').val(response.data.factura.factura);
                        $('#fecha-editar').val(response.data.factura.fecha);
                        $('#galones-editar').val(response.data.factura.cantidad);
                        $('#precio-editar').val(response.data.factura.unitario);

                        document.getElementById("select-equipo").options.length = 0;

                        $.each(response.data.equipo, function( key, val ){
                            if(response.data.factura.id_equipo == val.id){
                                $('#select-equipo').append('<option value="' +val.id +'" selected="selected">'+val.tipo+'</option>');
                            }else{
                                $('#select-equipo').append('<option value="' +val.id +'">'+val.tipo+'</option>');
                            }
                        });

                        if(response.data.factura.producto == 'D'){
                            document.getElementById("select-producto").selectedIndex = "0";
                        }else if(response.data.factura.producto == 'R'){
                            document.getElementById("select-producto").selectedIndex = "1";
                        }else{
                            // especial
                            document.getElementById("select-producto").selectedIndex = "2";
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

        function editar(){
            var id = document.getElementById('id-editar').value;

            var factura = document.getElementById('factura-editar').value;
            var equipo = document.getElementById('select-equipo').value;
            var producto = document.getElementById('select-producto').value;
            var fecha = document.getElementById('fecha-editar').value;
            var galones = document.getElementById('galones-editar').value;
            var precio = document.getElementById('precio-editar').value;

            var reglaNumeroEntero = /^[0-9]\d*$/;
            var reglaNumeroDecimal = /^[0-9]\d*(\.\d+)?$/;

            if(factura === ''){
                toastr.error('factura es requerido');
                return;
            }

            if(!factura.match(reglaNumeroEntero)) {
                toastr.error('factura debe ser número Entero');
                return;
            }

            if(factura < 0){
                toastr.error('factura no debe tener números negativos');
                return;
            }

            if(factura > 10000000){
                toastr.error('factura no debe superar 10 millones');
                return;
            }

            if(equipo === ''){
                toastr.error('equipo es requerido');
                return;
            }

            if(producto === ''){
                toastr.error('producto es requerido');
                return;
            }

            if(fecha === ''){
                toastr.error('fecha es requerida');
                return;
            }

            // ----- galones ------

            if(galones === ''){
                toastr.error('galones es requerido');
                return;
            }

            if(!galones.match(reglaNumeroDecimal)) {
                toastr.error('galones debe ser número');
                return;
            }

            if(galones < 0){
                toastr.error('galones no debe tener números negativos');
                return;
            }

            if(galones > 10000000){
                toastr.error('galones no debe superar 10 millones');
                return;
            }

            // ----- precio unitario ------

            if(precio === ''){
                toastr.error('precio unitario es requerido');
                return;
            }

            if(!precio.match(reglaNumeroDecimal)) {
                toastr.error('precio unitario debe ser número');
                return;
            }

            if(precio < 0){
                toastr.error('precio unitario no debe tener números negativos');
                return;
            }

            if(precio > 10000000){
                toastr.error('precio unitario no debe superar 10 millones');
                return;
            }

            openLoading();
            var formData = new FormData();
            formData.append('id', id);
            formData.append('factura', factura);
            formData.append('equipo', equipo);
            formData.append('producto', producto);
            formData.append('fecha', fecha);
            formData.append('galones', galones);
            formData.append('precio', precio);

            axios.post(url+'/factura/editar', formData, {
            })
                .then((response) => {
                    closeLoading();

                    if(response.data.success === 1){
                        toastr.success('actualizado correctamente');
                        $('#modalEditar').modal('hide');
                        recargar();
                    }
                    else {
                        toastr.error('error al actualizar');
                    }

                })
                .catch((error) => {
                    toastr.error('error al actualizar');
                    closeLoading();
                });
        }


    </script>


@endsection
