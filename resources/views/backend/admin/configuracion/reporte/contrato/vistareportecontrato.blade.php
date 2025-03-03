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
                        <h3 class="card-title">Generar Acta de Recepción</h3>
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
                                <label>Proceso Referencia</label>

                                <select class="form-control" id="select-contrato" onchange="infoContrato(this)">
                                    <option value="" selected>Seleccionar Contrato</option>
                                    @foreach($arrayContrato as $dato)
                                        <option value="{{ $dato->id }}">{{ $dato->proceso_ref }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="form-group" style="width: 50%">
                                <label>Distrito</label>

                                <select class="form-control" id="select-distrito">
                                    @foreach($arrayDistrito as $dato)
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

            <div class="col-md-6">
                <div class="card card-gray-dark">
                    <div class="card-header">
                        <h3 class="card-title">Información de Contrato</h3>
                    </div>
                    <form id="formulario-info-contrato">
                        <div class="card-body">

                            <div class="row">
                                <div class="form-group">
                                    <label>Desde</label>
                                    <input type="date" disabled class="form-control" id="fecha-desde-contrato">
                                </div>

                                <div class="form-group" style="margin-left: 15px">
                                    <label>Hasta</label>
                                    <input type="date" disabled class="form-control" id="fecha-hasta-contrato">
                                </div>
                            </div>

                            <div class="form-group">
                                <label>Proveedor</label>
                                <input type="text" disabled class="form-control" id="proveedor-contrato">
                            </div>
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


        });

    </script>

    <script>

        function infoContrato(e){
            let id = $(e).val();
            document.getElementById("formulario-info-contrato").reset();

            if(id === ''){
                return
            }

            openLoading();

            axios.post(url+'/contratos/informacion',{
                'id': id
            })
                .then((response) => {
                    closeLoading();
                    if(response.data.success === 1){

                        $('#fecha-desde-contrato').val(response.data.info.fecha_desde);
                        $('#fecha-hasta-contrato').val(response.data.info.fecha_hasta);
                        $('#proveedor-contrato').val(response.data.info.proveedor);

                    }else{
                        toastr.error('Información no encontrada');
                    }

                })
                .catch((error) => {
                    closeLoading();
                    toastr.error('Información no encontrada');
                });
        }



        function reportePdf(){

            var fechadesde = document.getElementById('fecha-desde').value;
            var fechahasta = document.getElementById('fecha-hasta').value;
            var distrito = document.getElementById('select-distrito').value;
            var contrato = document.getElementById('select-contrato').value;

            if(fechadesde === ''){
                toastr.error('Fecha desde es requerido');
                return;
            }

            if(fechahasta === ''){
                toastr.error('Fecha hasta es requerido');
                return;
            }

            if(contrato === ''){
                toastr.error('Contrato es requerido');
                return
            }

            // Convertir a objetos Date para comparar
            let dateDesde = new Date(fechadesde);
            let dateHasta = new Date(fechahasta);

            if (dateHasta < dateDesde) {
                toastr.error('La Fecha Hasta no puede ser menor que la Fecha Desde');
                return;
            }

            var fechadesdeContrato = document.getElementById('fecha-desde-contrato').value;
            var fechahastaContrato = document.getElementById('fecha-hasta-contrato').value;
            let fechadesdeContratoDate = new Date(fechadesdeContrato);
            let fechahastaContratoDate = new Date(fechahastaContrato);
            // LA FECHA DESDE NO PUEDE SER MENOR A LA DEL CONTRATO
           if(dateDesde < fechadesdeContratoDate){
               toastr.error('Fecha DESDE no puede ser menor a la del Contrato');
               return
           }

            // LA FECHA HASTA NO PUEDE SER MAYOR A LA DEL CONTRATO
            if(dateHasta > fechahastaContratoDate){
                toastr.error('Fecha HASTA no puede ser menor a la del fecha HASTA del Contrato');
                return
            }


            window.open("{{ URL::to('admin/reportev2/contrato/info') }}/" +
                fechadesde + "/" + fechahasta + "/" + contrato + "/" + distrito);
        }







    </script>



@stop
