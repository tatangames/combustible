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
                <div class="card card-gray-dark">
                    <div class="card-header">
                        <h3 class="card-title">Reporte Rendimiento</h3>
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
                                <select class="form-control" id="select-equipos" name="equipos[]" multiple>
                                    @foreach($arrayEquipos as $dato)
                                        <option value="{{ $dato->id }}">{{ $dato->nombre }}</option>
                                    @endforeach
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

            $(document).ready(function() {
                $('#select-equipos').select2({
                    theme: "bootstrap-5",
                    language: {
                        noResults: function(){
                            return "Búsqueda no encontrada";
                        }
                    },
                    width: '100%'
                });
            });
        });

    </script>

    <script>

        function reportePdf(){

            var fechadesde = document.getElementById('fecha-desde').value;
            var fechahasta = document.getElementById('fecha-hasta').value;
            var equipos    = $('#select-equipos').val(); // ← array de IDs seleccionados

            if (fechadesde === '') {
                toastr.error('Fecha desde es requerido');
                return;
            }

            if (fechahasta === '') {
                toastr.error('Fecha hasta es requerido');
                return;
            }

            // Validar que al menos un equipo esté seleccionado
            if (!equipos || equipos.length === 0) {
                toastr.error('Debes seleccionar al menos un equipo');
                return;
            }

            // === BLOQUEO DE FECHAS ===
            var desde = new Date(fechadesde);
            var hasta = new Date(fechahasta);

            if (hasta < desde) {
                toastr.error('La fecha hasta no puede ser menor que la fecha desde');
                return;
            }

            // Convertimos el array [1,3,5] en "1,3,5"
            var equiposStr = equipos.join(',');

            window.open("{{ URL::to('admin/reporte/pdf/rendimiento') }}/" +
                fechadesde + "/" + fechahasta + "/" + equiposStr);
        }

    </script>

@stop
