@extends('backend.menus.superior')

@section('content-admin-css')
    <link href="{{ asset('css/adminlte.min.css') }}" type="text/css" rel="stylesheet" />
    <link href="{{ asset('css/dataTables.bootstrap4.css') }}" type="text/css" rel="stylesheet" />
    <link href="{{ asset('css/toastr.min.css') }}" type="text/css" rel="stylesheet" />
    <link href="{{ asset('css/select2.min.css') }}" type="text/css" rel="stylesheet">
    <link href="{{ asset('css/select2-bootstrap-5-theme.min.css') }}" type="text/css" rel="stylesheet">
@stop

<style>

    body{
        overflow-x: hidden;
    }

</style>

<section class="content-header">
    <div class="container-fluid">
    </div>
</section>

<section class="content">
    <div class="container-fluid" style="margin-left: 15px">
        <div class="row">


            <div class="col-md-5">
                <div class="card card-green">
                    <div class="card-header">
                        <h3 class="card-title">Formulario</h3>
                    </div>
                    <form id="formulario-nuevo">
                        <div class="card-body">

                            <div class="form-group">
                                <label>Factura Nº *</label>
                                <input type="text" class="form-control" autocomplete="off" id="factura-nuevo">
                            </div>

                            <div class="form-group">
                                <label>Fecha *</label>
                                <input type="date" class="form-control" id="fecha-nuevo" autocomplete="off">
                            </div>

                            <div class="form-group">
                                <label>Galones (3 decimales máximo) *</label>
                                <input type="number" class="form-control" id="galones-nuevo" autocomplete="off">
                            </div>

                            <div class="form-group">
                                <label>Precio Unitario (2 decimales máximo) *</label>
                                <input type="number" class="form-control" id="precio-nuevo" autocomplete="off">
                            </div>

                        </div>
                    </form>
                </div>

            </div>


            <div class="col-md-5">
                <div class="card card-green">
                    <div class="card-header">
                        <h3 class="card-title">Formulario</h3>
                    </div>
                    <form id="formulario-nuevo">
                        <div class="card-body">

                            <div class="form-group">
                                <label class="control-label">Equipo: </label>
                                <select id="select-equipo" class="form-control">
                                    @foreach($equipo as $item)
                                        <option value="{{$item->id}}">{{$item->tipo}}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="form-group">
                                <label class="control-label">Combustible: </label>
                                <select id="select-producto" class="form-control">
                                    @foreach($arraycombustible as $item)
                                        <option value="{{$item->id}}">{{$item->nombre}}</option>
                                    @endforeach
                                </select>
                            </div>

                        </div>

                        <div class="card-footer" style="float: right;">
                            <button type="button" class="btn btn-success" onclick="registrar()">Registrar</button>
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
    <script src="{{ asset('js/select2.min.js') }}" type="text/javascript"></script>

    <script type="text/javascript">
        $(document).ready(function(){

            $('#select-equipo').select2({
                theme: "bootstrap-5",
                "language": {
                    "noResults": function(){
                        return "Búsqueda no encontrada";
                    }
                },
            });

        });
    </script>


    <script>

        function registrar(){

            var factura = document.getElementById('factura-nuevo').value;
            var equipo = document.getElementById('select-equipo').value;
            var tipocombustible = document.getElementById('select-producto').value;
            var fecha = document.getElementById('fecha-nuevo').value;
            var galones = document.getElementById('galones-nuevo').value;
            var precio = document.getElementById('precio-nuevo').value;

            var reglaNumeroEntero = /^[0-9]\d*$/;
            var reglaNumeroDecimal = /^[0-9]\d*(\.\d+)?$/;

            if(factura === ''){
                toastr.error('Factura es requerido');
                return;
            }

            if(!factura.match(reglaNumeroEntero)) {
                toastr.error('Factura debe ser número Entero');
                return;
            }

            if(factura < 0){
                toastr.error('Factura no debe tener números negativos');
                return;
            }

            if(factura.length > 8){
                toastr.error('Factura no debe superar 8 dígitos');
                return;
            }

            if(equipo === ''){
                toastr.error('Equipo es requerido');
                return;
            }

            if(tipocombustible === ''){
                toastr.error('Tipo Combustible es requerido');
                return;
            }

            if(fecha === ''){
                toastr.error('Fecha es requerida');
                return;
            }

            // ----- galones ------

            if(galones === ''){
                toastr.error('Galones es requerido');
                return;
            }

            if(!galones.match(reglaNumeroDecimal)) {
                toastr.error('Galones debe ser número Decimal y no Negativo');
                return;
            }

            if(galones < 0){
                toastr.error('Galones no debe tener números negativos');
                return;
            }

            if(galones.length > 8){
                toastr.error('Galones no debe superar 8 dígitos');
                return;
            }

            // ----- precio unitario ------

            if(precio === ''){
                toastr.error('Precio unitario es requerido');
                return;
            }

            if(!precio.match(reglaNumeroDecimal)) {
                toastr.error('Precio unitario debe se debe ser número Decimal y no Negativo.');
                return;
            }

            if(precio < 0){
                toastr.error('Precio unitario no debe tener números negativos');
                return;
            }

            if(precio > 1000000){
                toastr.error('Precio unitario no debe superar 1 millon');
                return;
            }

            openLoading()
            var formData = new FormData();
            formData.append('factura', factura);
            formData.append('equipo', equipo);
            formData.append('combustible', tipocombustible);
            formData.append('fecha', fecha);
            formData.append('galones', galones);
            formData.append('precio', precio);

            axios.post(url+'/factura/nuevo', formData, {
            })
                .then((response) => {
                    closeLoading();

                    if (response.data.success === 1) {
                        toastr.success('registrado correctamente');
                        document.getElementById("galones-nuevo").value = "";
                        document.getElementById("precio-nuevo").value = "";
                        document.getElementById("factura-nuevo").value = "";
                    }
                    else {
                        toastr.error('error al registrar');
                    }
                })
                .catch((error) => {
                    closeLoading();
                    toastr.error('error al registrar');
                });
        }


    </script>



@stop
