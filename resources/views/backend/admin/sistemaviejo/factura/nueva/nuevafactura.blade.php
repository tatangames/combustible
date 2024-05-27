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

    html, body {
        overflow-x: hidden;
    }
</style>

<div id="divcontenedor" style="display: none">
    <section class="content-header">
        <div class="container-fluid">

        </div>
    </section>

    <section class="content">
        <div class="container-fluid" style="margin-left: 15px">
            <div class="row">

                <div class="col-md-12">
                    <div class="card card-gray-dark">
                        <div class="card-header">
                            <h3 class="card-title">Formulario</h3>
                        </div>
                        <form id="formulario-nuevo">
                            <div class="card-body">

                                <div class="row">

                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label># de Factura</label>
                                            <input type="text" id="numfactura-nuevo" class="form-control">
                                        </div>

                                        <div class="form-group">
                                            <label>Equipo</label>
                                            <input type="text" id="equipo-nuevo" maxlength="350" class="form-control">
                                        </div>

                                        <div class="form-group">
                                            <label>Placa (Opcional)</label>
                                            <input type="text" id="placa-nuevo" maxlength="15" class="form-control">
                                        </div>

                                        <div class="form-group">
                                            <label>Producto</label>
                                            <select class="form-control" id="producto-nuevo">
                                                <option value="D">DIESEL</option>
                                                <option value="R">REGULAR</option>
                                                <option value="E">ESPECIAL</option>
                                            </select>
                                        </div>

                                        <div class="form-group">
                                            <label>Línea</label>
                                            <select class="form-control" id="linea-nuevo">
                                                <option value="0101">0101</option>
                                            </select>
                                        </div>

                                    </div>


                                    <div class="col-md-3">


                                        <div class="form-group">
                                            <label>Fecha</label>
                                            <input type="date" id="fecha-nuevo" value="{{ $fechaActual->format('Y-m-d') }}" class="form-control">
                                        </div>

                                        <div class="form-group">
                                            <label># Galones</label>
                                            <input type="number" id="galones-nuevo" class="form-control">
                                        </div>

                                        <div class="form-group">
                                            <label>Precio Unitario</label>
                                            <input type="number" id="precio-nuevo" class="form-control">
                                        </div>


                                        <div class="form-group">
                                            <label>KM (Opcional)</label>
                                            <input type="text" id="km-nuevo" maxlength="15" class="form-control">
                                        </div>

                                        <div class="card-footer" style="float: right;">
                                            <button type="button" class="btn btn-primary" onclick="registrar()">Registrar</button>
                                        </div>
                                    </div>
                                </div>


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


        function registrar(){
            var numfactura = document.getElementById('numfactura-nuevo').value;
            var equipo = document.getElementById('equipo-nuevo').value;
            var placa = document.getElementById('placa-nuevo').value;
            var producto = document.getElementById('producto-nuevo').value;
            var linea = document.getElementById('linea-nuevo').value;
            var fecha = document.getElementById('fecha-nuevo').value;
            var galones = document.getElementById('galones-nuevo').value;
            var unitario = document.getElementById('precio-nuevo').value;
            var km = document.getElementById('km-nuevo').value;

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
            formData.append('numfactura', numfactura);
            formData.append('fecha', fecha);
            formData.append('equipo', equipo);
            formData.append('placa', placa);
            formData.append('producto', producto);
            formData.append('linea', linea);
            formData.append('galones', galones);
            formData.append('unitario', unitario);
            formData.append('km', km);

            axios.post(url+'/nuevafactura/nuevo', formData, {
            })
                .then((response) => {
                    closeLoading();

                    if(response.data.success === 0) {
                        toastr.error('Faltan campos para Registrar');
                    }
                    else if(response.data.success === 1){
                        toastr.success('Registrado');
                        document.getElementById("formulario-nuevo").reset();
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





    </script>


@endsection
