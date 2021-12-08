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
            <button type="button" onclick="modalAgregar()" class="btn btn-success btn-sm">
                <i class="fas fa-pencil-alt"></i>
                Nuevo Equipo
            </button>
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

    <div class="modal fade" id="modalAgregar">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Nuevo Equipo</h4>
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
                                        <label>Equipo</label>
                                        <input type="text" maxlength="200" class="form-control" id="tipo-nuevo">
                                    </div>

                                    <div class="form-group">
                                        <label>Descripción</label>
                                        <input type="text" maxlength="400" class="form-control" id="descripcion-nuevo">
                                    </div>

                                    <div class="form-group">
                                        <label>Placa</label>
                                        <input type="text" maxlength="100" class="form-control" id="placa-nuevo">
                                    </div>

                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer justify-content-between">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
                    <button type="button" class="btn btn-primary" onclick="nuevo()">Guardar</button>
                </div>
            </div>
        </div>
    </div>

    <!-- modal editar -->
    <div class="modal fade" id="modalEditar">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Editar Equipo</h4>
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
                                        <label>Equipo</label>
                                        <input type="hidden" id="id-editar">
                                        <input type="text" maxlength="200" class="form-control" id="tipo-editar">
                                    </div>

                                    <div class="form-group">
                                        <label>Descripción</label>
                                        <input type="text" maxlength="400" class="form-control" id="descripcion-editar">
                                    </div>

                                    <div class="form-group">
                                        <label>Placa</label>
                                        <input type="text" maxlength="100" class="form-control" id="placa-editar">
                                    </div>

                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer justify-content-between">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
                    <button type="button" class="btn btn-primary" onclick="editar()">Guardar</button>
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
            var ruta = "{{ URL::to('/admin/equipo/tabla') }}";
            $('#tablaDatatable').load(ruta);
            document.getElementById("divcontenedor").style.display = "block";
        });
    </script>

    <script>

        function recargar(){
            var ruta = "{{ url('/admin/equipo/tabla') }}";
            $('#tablaDatatable').load(ruta);
        }

        function modalAgregar(){
            document.getElementById("formulario-nuevo").reset();
            $('#modalAgregar').modal('show');
        }

        function nuevo(){
            var tipo = document.getElementById('tipo-nuevo').value;
            var descripcion = document.getElementById('descripcion-nuevo').value;
            var placa = document.getElementById('placa-nuevo').value;

            if(tipo === ''){
                toastr.error('nombre de equipo es requerido');
                return;
            }

            if(tipo.length > 200){
                toastr.error('nombre de equipo máximo 200 caracteres');
                return;
            }

            if(descripcion.length > 0){

                if(descripcion.length > 400){
                    toastr.error('descripción máximo 400 caracteres');
                    return;
                }
            }

            if(placa.length > 0){

                if(placa.length > 100){
                    toastr.error('placa máximo 100 caracteres');
                    return;
                }
            }

            openLoading();
            var formData = new FormData();
            formData.append('tipo', tipo);
            formData.append('descripcion', descripcion);
            formData.append('placa', placa);

            axios.post(url+'/equipo/nuevo', formData, {
            })
                .then((response) => {
                    closeLoading();
                    if(response.data.success === 1){
                        toastr.success('registrado correctamente');
                        $('#modalAgregar').modal('hide');
                        recargar();
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

        function informacion(id){
            openLoading();
            document.getElementById("formulario-editar").reset();

            axios.post(url+'/equipo/informacion',{
                'id': id
            })
                .then((response) => {
                    closeLoading();
                    if(response.data.success === 1){
                        $('#modalEditar').modal('show');
                        $('#id-editar').val(response.data.equipo.id);
                        $('#tipo-editar').val(response.data.equipo.tipo);
                        $('#descripcion-editar').val(response.data.equipo.descripcion);
                        $('#placa-editar').val(response.data.equipo.placa);
                    }else{
                        toastr.error('información no encontrada');
                    }

                })
                .catch((error) => {
                    closeLoading();
                    toastr.error('información no encontrada');
                });
        }

        function editar(){
            var id = document.getElementById('id-editar').value;
            var tipo = document.getElementById('tipo-editar').value;
            var descripcion = document.getElementById('descripcion-editar').value;
            var placa = document.getElementById('placa-editar').value;

            if(tipo === ''){
                toastr.error('nombre de equipo es requerido');
                return;
            }

            if(tipo.length > 200){
                toastr.error('nombre de equipo máximo 200 caracteres');
                return;
            }

            if(descripcion.length > 0){

                if(descripcion.length > 400){
                    toastr.error('descripción máximo 400 caracteres');
                    return;
                }
            }

            if(placa.length > 0){

                if(placa.length > 100){
                    toastr.error('placa máximo 100 caracteres');
                    return;
                }
            }

            openLoading();
            var formData = new FormData();
            formData.append('id', id);
            formData.append('tipo', tipo);
            formData.append('descripcion', descripcion);
            formData.append('placa', placa);

            axios.post(url+'/equipo/editar', formData, {
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
