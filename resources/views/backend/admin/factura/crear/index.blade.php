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
                <div class="card card-green">
                    <div class="card-header">
                        <h3 class="card-title">Formulario</h3>
                    </div>
                    <form id="formulario-nuevo">
                        <div class="card-body">

                            <div class="form-group">
                                <label>Factura Nº</label>
                                <input type="text" class="form-control" id="factura-nuevo">
                            </div>

                            <div class="form-group">
                                <label class="control-label">Equipo: </label>
                                <select id="select-equipo" class="form-control">
                                    @foreach($equipo as $item)
                                        <option value="{{$item->id}}">{{$item->tipo}}</option>
                                    @endforeach
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
                                <input type="date" class="form-control" id="fecha-nuevo">
                            </div>

                            <div class="form-group">
                                <label>Galones</label>
                                <input type="number" class="form-control" id="galones-nuevo">
                            </div>

                            <div class="form-group">
                                <label>Precio Unitario</label>
                                <input type="number" class="form-control" id="precio-nuevo">
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

    <script>

        function registrar(){

            var factura = document.getElementById('factura-nuevo').value;
            var equipo = document.getElementById('select-equipo').value;
            var producto = document.getElementById('select-producto').value;
            var fecha = document.getElementById('fecha-nuevo').value;
            var galones = document.getElementById('galones-nuevo').value;
            var precio = document.getElementById('precio-nuevo').value;

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

            openLoading()
            var formData = new FormData();
            formData.append('factura', factura);
            formData.append('equipo', equipo);
            formData.append('producto', producto);
            formData.append('fecha', fecha);
            formData.append('galones', galones);
            formData.append('precio', precio);

            axios.post(url+'/factura/nuevo', formData, {
            })
                .then((response) => {
                    closeLoading();

                    if (response.data.success === 1) {
                        toastr.success('registrado correctamente');
                        document.getElementById("formulario-nuevo").reset();
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
