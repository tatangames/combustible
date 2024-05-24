@extends('backend.menus.superior')

@section('content-admin-css')
    <link href="{{ asset('css/adminlte.min.css') }}" type="text/css" rel="stylesheet" />
    <link href="{{ asset('css/dataTables.bootstrap4.css') }}" type="text/css" rel="stylesheet" />
    <link href="{{ asset('css/toastr.min.css') }}" type="text/css" rel="stylesheet" />
@stop

<section class="content-header">
    <div class="container-fluid">


    </div>
</section>

<section class="content">
    <div class="container-fluid" style="margin-left: 15px">
        <div class="row">
            <div class="col-md-6">
                <div class="card card-gray-dark">
                    <div class="card-header">
                        <h3 class="card-title">Formulario</h3>
                    </div>
                    <form>
                        <div class="card-body">

                            <h3>Columna 1</h3>

                            <div class="form-group">

                                <input type="text" id="nombre1" class="form-control" maxlength="200" value="{{ $infoExtra->nombre1 }}">
                            </div>

                            <div class="form-group">

                                <input type="text" id="nombre2" class="form-control" maxlength="200" value="{{ $infoExtra->nombre2 }}">
                            </div>

                            <hr>


                            <h3>Columna 2</h3>

                            <div class="form-group">

                                <input type="text" id="nombre3" class="form-control" maxlength="200" value="{{ $infoExtra->nombre3 }}">
                            </div>

                            <div class="form-group">

                                <input type="text" id="nombre4" class="form-control" maxlength="200" value="{{ $infoExtra->nombre4 }}">
                            </div>

                        </div>

                        <div class="card-footer" style="float: right;">
                            <button type="button" class="btn btn-primary" onclick="actualizar()">Actualizar</button>
                        </div>
                    </form>
                </div>

            </div>

        </div>
    </div>
</section>

@extends('backend.menus.footerjs')
@section('archivos-js')

    <script src="{{ asset('js/jquery.dataTables.js') }}" type="text/javascript"></script>
    <script src="{{ asset('js/dataTables.bootstrap4.js') }}" type="text/javascript"></script>

    <script src="{{ asset('js/toastr.min.js') }}" type="text/javascript"></script>
    <script src="{{ asset('js/axios.min.js') }}" type="text/javascript"></script>
    <script src="{{ asset('js/sweetalert2.all.min.js') }}"></script>
    <script src="{{ asset('js/alertaPersonalizada.js') }}"></script>

    <script>

        function abrirModalAgregar(){
            document.getElementById("formulario-nuevo").reset();
            $('#modalAgregar').modal('show');
        }

        function actualizar(){
            var nombre1 = document.getElementById('nombre1').value;
            var nombre2 = document.getElementById('nombre2').value;
            var nombre3 = document.getElementById('nombre3').value;
            var nombre4 = document.getElementById('nombre4').value;


            openLoading()
            var formData = new FormData();
            formData.append('nombre1', nombre1);
            formData.append('nombre2', nombre2);
            formData.append('nombre3', nombre3);
            formData.append('nombre4', nombre4);

            axios.post(url+'/cambio/nombres/actualizar', formData, {
            })
                .then((response) => {
                    closeLoading()

                    if (response.data.success === 1) {
                        toastr.success('Actualizado');
                    }
                    else {
                        toastr.error('error al actualizar');
                    }
                })
                .catch((error) => {
                    closeLoading();
                    toastr.error('error al actualizar');
                });
        }


    </script>



@stop
