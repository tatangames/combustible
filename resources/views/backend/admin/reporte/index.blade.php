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
                        <h3 class="card-title">Reporte</h3>
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
                                <label>Fecha Desde</label>
                                <input type="date" class="form-control" id="fecha-desde">
                            </div>

                            <div class="form-group">
                                <label>Fecha Hasta</label>
                                <input type="date" class="form-control" id="fecha-hasta">
                            </div>

                        </div>

                        <div class="card-footer" style="float: right;">
                            <button type="button" class="btn btn-success" onclick="buscar()">PDF</button>
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

        function buscar(){

            var equipo = document.getElementById('select-equipo').value;
            var fechadesde = document.getElementById('fecha-desde').value;
            var fechahasta = document.getElementById('fecha-hasta').value;

            if(fechadesde === ''){
                toastr.error('fecha desde es requerido');
                return;
            }

            if(fechahasta === ''){
                toastr.error('fecha hasta es requerido');
                return;
            }

            window.open("{{ URL::to('admin/factura/reporte-equipo') }}/" + fechadesde + "/" + fechahasta + "/" + equipo);
        }


    </script>



@stop
