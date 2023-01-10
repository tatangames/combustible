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
            </div>

        </div>
    </section>

    <section class="content">
        <div class="container-fluid" style="margin-left: 15px">
            <div class="row">
                <div class="col-md-6">
                    <div class="card card-green">
                        <div class="card-header">
                            <h3 class="card-title">Ajuste de Firmas</h3>
                        </div>
                        <form>
                            <div class="card-body">

                                <labe>Firma 1</labe>

                                <div class="form-group">
                                    <input type="text" maxlength="200" class="form-control" value="{{ $lista->nombre1 }}" id="nombre1" placeholder="Nombre">
                                </div>

                                <div class="form-group">
                                    <input type="text" maxlength="200" class="form-control" value="{{ $lista->cargo1 }}" id="cargo1" placeholder="Cargo">
                                </div>


                                <hr>

                                <labe>Firma 2</labe>

                                <div class="form-group">
                                    <input type="text" maxlength="200" class="form-control" value="{{ $lista->nombre2 }}" id="nombre2" placeholder="Nombre">
                                </div>


                                <div class="form-group">
                                    <input type="text" maxlength="200" class="form-control" value="{{ $lista->cargo2 }}" id="cargo2" placeholder="Cargo">
                                </div>

                                <hr>

                            </div>

                            <div class="card-footer" style="float: right;">
                                <button type="button" class="btn btn-success" onclick="actualizar()">Actualizar</button>
                            </div>
                        </form>
                    </div>

                </div>

            </div>
        </div>
    </section>
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
            document.getElementById("divcontenedor").style.display = "block";
        });
    </script>

    <script>

        function actualizar(){

            var nombre1 = document.getElementById('nombre1').value;
            var cargo1 = document.getElementById('cargo1').value;

            var nombre2 = document.getElementById('nombre2').value;
            var cargo2 = document.getElementById('cargo2').value;


            if(nombre1 === ''){
                toastr.error('Firma 1: nombre es requerido');
                return;
            }

            if(cargo1 === ''){
                toastr.error('Cargo 1: cargo es requerido');
                return;
            }

            if(nombre2 === ''){
                toastr.error('Firma 2: nombre es requerido');
                return;
            }

            if(cargo2 === ''){
                toastr.error('Cargo 2: cargo es requerido');
                return;
            }



            if(nombre1.length > 200){
                toastr.error('Firma 1: nombre 200 caracteres máximo');
                return;
            }

            if(cargo1.length > 200){
                toastr.error('Cargo 1: cargo 200 caracteres máximo');
                return;
            }

            if(nombre2.length > 200){
                toastr.error('Firma 2: nombre 200 caracteres máximo');
                return;
            }

            if(cargo2.length > 200){
                toastr.error('Cargo 2: cargo 200 caracteres máximo');
                return;
            }


            openLoading();
            var formData = new FormData();
            formData.append('nombre1', nombre1);
            formData.append('cargo1', cargo1);
            formData.append('nombre2', nombre2);
            formData.append('cargo2', cargo2);

            axios.post(url+'/configurcion/editar/nombres', formData, {
            })
                .then((response) => {
                    closeLoading();

                    if(response.data.success === 1){
                        toastr.success('Actualizado correctamente');
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

    </script>


@endsection
