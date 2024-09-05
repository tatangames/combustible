@extends('backend.menus.superior')

@section('content-admin-css')
    <link href="{{ asset('css/adminlte.min.css') }}" type="text/css" rel="stylesheet" />
    <link href="{{ asset('css/dataTables.bootstrap4.css') }}" type="text/css" rel="stylesheet" />
    <link href="{{ asset('css/toastr.min.css') }}" type="text/css" rel="stylesheet" />
    <link href="{{ asset('css/select2.min.css') }}" type="text/css" rel="stylesheet">
    <link href="{{ asset('css/select2-bootstrap-5-theme.min.css') }}" type="text/css" rel="stylesheet">
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
                        <h3 class="card-title">Reporte por Equipo</h3>
                    </div>
                    <form id="formulario-nuevo">
                        <div class="card-body">

                            <div class="row">
                                <div class="form-group">
                                    <label>Desde</label>
                                    <input type="date" class="form-control" id="fecha-desde">
                                </div>

                                <div class="form-group" style="margin-left: 15px">
                                    <label>Hasta</label>
                                    <input type="date" class="form-control" id="fecha-hasta">
                                </div>
                            </div>

                            <div class="form-group" style="width: 50%">
                                <label>Equipo</label>
                                <select class="form-control" id="select-equipos">
                                    <option value="0">TODOS</option>
                                    @foreach($arrayEquipos as $dato)
                                        <option value="{{ $dato->id }}">{{ $dato->nombre }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="form-group" style="width: 50%">
                                <label>Distrito</label>
                                <select class="form-control" id="select-distrito">
                                    <option value="0">TODOS</option>
                                    @foreach($arrayDistrito as $dato)
                                        <option value="{{ $dato->id }}">{{ $dato->nombre }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="form-group" style="width: 50%">
                                <label>Fondos</label>
                                <select class="form-control" id="select-fondos">
                                    <option value="0">TODOS</option>
                                    @foreach($arrayFondos as $dato)
                                        <option value="{{ $dato->id }}">{{ $dato->nombre }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="form-group" style="width: 50%">
                                <label>Turno</label>
                                <select class="form-control" id="select-turno">
                                    <option value="10">TODOS</option>
                                    <option value="0">Mañana</option>
                                    <option value="1">Tarde</option>
                                </select>
                            </div>

                        </div>


                        <div class="card-footer" style="float: right;">
                            <button type="button" onclick="reportePdf()" class="btn" style="margin-left: 15px; border-color: black; border-radius: 0.1px;">
                                <img src="{{ asset('images/logopdf.png') }}" width="55px" height="55px">
                                Generar PDF
                            </button>
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

    <script>
        $(document).ready(function() {

            $('#select-equipos').select2({
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

        function reportePdf(){

            var fechadesde = document.getElementById('fecha-desde').value;
            var fechahasta = document.getElementById('fecha-hasta').value;
            var equipo = document.getElementById('select-equipos').value;
            var distrito = document.getElementById('select-distrito').value;
            var fondos = document.getElementById('select-fondos').value;
            var turno = document.getElementById('select-turno').value;

            if(fechadesde === ''){
                toastr.error('Fecha desde es requerido');
                return;
            }

            if(fechahasta === ''){
                toastr.error('Fecha hasta es requerido');
                return;
            }

            window.open("{{ URL::to('admin/reportev2/generar/equipos') }}/" +
                fechadesde + "/" + fechahasta + "/" + equipo + "/" + distrito + "/" + fondos + "/" + turno);
        }







    </script>



@stop
