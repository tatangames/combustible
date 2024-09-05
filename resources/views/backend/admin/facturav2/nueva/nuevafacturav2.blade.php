@extends('backend.menus.superior')

@section('content-admin-css')
    <link href="{{ asset('css/adminlte.min.css') }}" type="text/css" rel="stylesheet" />
    <link href="{{ asset('css/dataTables.bootstrap4.css') }}" type="text/css" rel="stylesheet" />
    <link href="{{ asset('css/toastr.min.css') }}" type="text/css" rel="stylesheet" />
    <link href="{{ asset('css/estiloToggle.css') }}" type="text/css" rel="stylesheet" />
    <link href="{{ asset('css/select2.min.css') }}" type="text/css" rel="stylesheet">
    <link href="{{ asset('css/select2-bootstrap-5-theme.min.css') }}" type="text/css" rel="stylesheet">
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
                                            <input type="text" id="numfactura-nuevo" class="form-control" maxlength="50">
                                        </div>

                                        <div class="form-group">
                                            <label>Distrito De</label>
                                            <select class="form-control" id="distrito-nuevo" >
                                                <option value="0">Seleccionar opción</option>
                                                @foreach($arrayDistritos as $dato)
                                                    <option value="{{ $dato->id }}">{{ $dato->nombre }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="form-group">
                                            <label></label>
                                        </div>
                                        <hr>
                                        <br>

                                        <div class="form-group">
                                            <label>Equipo</label>
                                            <select class="form-control" id="equipo-nuevo" >
                                                <option value="0">Seleccionar opción</option>
                                                @foreach($arrayEquipos as $dato)
                                                    <option value="{{ $dato->id }}">{{ $dato->nombre }}</option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <div class="form-group">
                                            <label>Producto</label>
                                            <select class="form-control" id="producto-nuevo">
                                                @foreach($arrayCombus as $dato)
                                                    <option value="{{ $dato->id }}">{{ $dato->nombre }}</option>
                                                @endforeach
                                            </select>
                                        </div>


                                        <div class="form-group">
                                            <label>KM (Opcional)</label>
                                            <input type="text" id="km-nuevo" maxlength="15" class="form-control">
                                        </div>


                                    </div>


                                    <div class="col-md-3">

                                        <div class="form-group">
                                            <label>Fecha</label>
                                            <input type="date" id="fecha-nuevo" value="{{ $fechaActual->format('Y-m-d') }}" class="form-control">
                                        </div>

                                        <div class="form-group">
                                            <label>Tipo de Fondos</label>
                                            <select class="form-control" id="fondos-nuevo" >
                                                <option value="0">Seleccionar opción</option>
                                                @foreach($arrayTipoFondos as $dato)
                                                    <option value="{{ $dato->id }}">{{ $dato->nombre }}</option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <div class="form-group">
                                            <label>Turno</label>
                                            <select class="form-control" id="select-turno" >
                                                <option value="0">Mañana</option>
                                                <option value="1">Tarde</option>
                                            </select>
                                        </div>

                                        <hr>
                                        <br>

                                        <div class="form-group">
                                            <label># Galones</label>
                                            <input type="number" id="galones-nuevo" class="form-control">
                                        </div>

                                        <div class="form-group">
                                            <label>Precio Unitario</label>
                                            <input type="number" id="precio-nuevo" class="form-control">
                                        </div>


                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">

                                        <div class="form-group">
                                            <label>Descripción</label>
                                            <input type="text" id="descripcion-nuevo" maxlength="800" placeholder="Descripción" class="form-control">
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
    <script src="{{ asset('js/select2.min.js') }}" type="text/javascript"></script>

    <script type="text/javascript">
        $(document).ready(function(){

            $('#equipo-nuevo').select2({
                theme: "bootstrap-5",
                "language": {
                    "noResults": function(){
                        return "Búsqueda no encontrada";
                    }
                },
            });

            document.getElementById("divcontenedor").style.display = "block";
        });
    </script>

    <script>


        function registrar(){

            var numfactura = document.getElementById('numfactura-nuevo').value;
            var equipo = document.getElementById('equipo-nuevo').value;
            var producto = document.getElementById('producto-nuevo').value;
            var fecha = document.getElementById('fecha-nuevo').value;
            var galones = document.getElementById('galones-nuevo').value;
            var unitario = document.getElementById('precio-nuevo').value;
            var km = document.getElementById('km-nuevo').value;
            var descripcion = document.getElementById('descripcion-nuevo').value;

            var selectFondos = document.getElementById('fondos-nuevo').value;
            var selectDistritos = document.getElementById('distrito-nuevo').value;
            var selectTurno = document.getElementById('select-turno').value;


            if(numfactura === ''){
                toastr.error('# Factura es requerido');
                return
            }

            if(equipo == '0'){
                toastr.error('Seleccionar equipo');
                return
            }

            if(selectFondos === '0'){
                toastr.error('Seleccionar Fondos');
                return
            }

            if(selectDistritos === '0'){
                toastr.error('Seleccionar Distrito');
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
            formData.append('producto', producto);
            formData.append('galones', galones);
            formData.append('unitario', unitario);
            formData.append('km', km);
            formData.append('descripcion', descripcion);
            formData.append('fondos', selectFondos);
            formData.append('distrito', selectDistritos);
            formData.append('turno', selectTurno);

            axios.post(url+'/facturav2/nuevo', formData, {
            })
                .then((response) => {
                    closeLoading();

                    if(response.data.success === 0) {
                        toastr.error('Faltan campos para Registrar');
                    }
                    else if(response.data.success === 1){
                        toastr.success('Registrado');
                        resetear();


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


        function resetear(){

            $('#equipo-nuevo').val(0).trigger('change');

            //$('#numfactura-nuevo').val("");

            $('#galones-nuevo').val("");
            $('#precio-nuevo').val("");
            $('#km-nuevo').val("");
            $('#descripcion-nuevo').val("");
        }





    </script>


@endsection
