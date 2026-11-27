@extends('backend.menus.superior')

@section('content-admin-css')
    <link href="{{ asset('css/adminlte.min.css') }}" type="text/css" rel="stylesheet" />
    <link href="{{ asset('css/toastr.min.css') }}" type="text/css" rel="stylesheet" />
@stop

<section class="content-header">
    <div class="container-fluid"></div>
</section>

<section class="content">
    <div class="container-fluid" style="margin-left: 15px">

        <div class="row">
            <div class="col-md-6">
                <div class="card card-gray-dark">
                    <div class="card-header">
                        <h3 class="card-title">Consumo de Contrato</h3>
                    </div>
                    <div class="card-body">
                        <label>Contrato</label>
                        <select class="form-control" id="select-contrato" onchange="cargarContrato()">
                            <option value="">Seleccionar...</option>
                            @foreach($arrayContratos as $item)
                                <option value="{{ $item->id }}">
                                    {{ $item->proceso_ref }} - {{ $item->proveedor }}
                                    ({{ date('d-m-Y', strtotime($item->fecha_desde)) }} / {{ date('d-m-Y', strtotime($item->fecha_hasta)) }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <div id="contenedor-info" style="display:none">

            <div class="row">
                <div class="col-md-12">
                    <div class="card card-outline card-primary">
                        <div class="card-body">
                            <p><b>Proveedor:</b> <span id="c-proveedor"></span></p>
                            <p><b>Proceso:</b> <span id="c-proceso"></span></p>
                            <p><b>Orden de compra:</b> <span id="c-orden"></span></p>
                            <p><b>Vigencia:</b> <span id="c-vigencia"></span> <span id="c-estado"></span></p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-3"><div class="info-box"><div class="info-box-content">
                            <span class="info-box-text">Contratado (gal)</span>
                            <span class="info-box-number" id="t-contratado"></span></div></div></div>
                <div class="col-md-3"><div class="info-box"><div class="info-box-content">
                            <span class="info-box-text">Consumido (gal)</span>
                            <span class="info-box-number" id="t-consumido"></span></div></div></div>
                <div class="col-md-3"><div class="info-box"><div class="info-box-content">
                            <span class="info-box-text">Restante (gal)</span>
                            <span class="info-box-number" id="t-restante"></span></div></div></div>
                <div class="col-md-3"><div class="info-box"><div class="info-box-content">
                            <span class="info-box-text">Dinero gastado</span>
                            <span class="info-box-number" id="t-gastado"></span></div></div></div>
            </div>

            <div class="row">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-body table-responsive p-0">
                            <table class="table table-bordered table-hover">
                                <thead>
                                <tr>
                                    <th>Distrito</th>
                                    <th>Combustible</th>
                                    <th>Código</th>
                                    <th>Contratado</th>
                                    <th>Consumido</th>
                                    <th>Restante</th>
                                    <th>% Uso</th>
                                    <th>Dinero gastado</th>
                                </tr>
                                </thead>
                                <tbody id="tabla-detalle"></tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</section>

@extends('backend.menus.footerjs')
@section('archivos-js')

    <script src="{{ asset('js/toastr.min.js') }}" type="text/javascript"></script>
    <script src="{{ asset('js/axios.min.js') }}" type="text/javascript"></script>
    <script src="{{ asset('js/sweetalert2.all.min.js') }}"></script>
    <script src="{{ asset('js/alertaPersonalizada.js') }}"></script>

    <script>

        function formato(n, decimales){
            return Number(n).toLocaleString('en-US', {
                minimumFractionDigits: decimales,
                maximumFractionDigits: decimales
            });
        }

        function cargarContrato(){
            var id = document.getElementById('select-contrato').value;

            if(id === ''){
                document.getElementById('contenedor-info').style.display = 'none';
                return;
            }

            openLoading();
            var formData = new FormData();
            formData.append('id', id);

            axios.post(url+'/dashboard/info', formData)
                .then((response) => {
                    closeLoading();

                    if(response.data.success !== 1){
                        toastr.error('Error al cargar');
                        return;
                    }

                    var c = response.data.contrato;
                    var t = response.data.totales;

                    document.getElementById('c-proveedor').textContent = c.proveedor ?? '';
                    document.getElementById('c-proceso').textContent   = (c.proceso_ref ?? '') + ' - ' + (c.nombre_proceso ?? '');
                    document.getElementById('c-orden').textContent     = c.orden_compra ?? '';
                    document.getElementById('c-vigencia').textContent  = c.fecha_desde + ' al ' + c.fecha_hasta;
                    document.getElementById('c-estado').innerHTML = c.vigente
                        ? '<span class="badge badge-success">Vigente</span>'
                        : '<span class="badge badge-danger">Vencido</span>';

                    document.getElementById('t-contratado').textContent = formato(t.contratado, 3);
                    document.getElementById('t-consumido').textContent  = formato(t.consumido, 3);
                    document.getElementById('t-restante').textContent   = formato(t.restante, 3);
                    document.getElementById('t-gastado').textContent    = '$' + formato(t.gastado, 2);

                    var filas = '';
                    response.data.detalle.forEach(function(f){
                        var color = f.porcentaje >= 90 ? 'badge-danger'
                            : (f.porcentaje >= 70 ? 'badge-warning' : 'badge-success');

                        filas += '<tr>' +
                            '<td>' + f.distrito + '</td>' +
                            '<td>' + f.combustible + '</td>' +
                            '<td>' + (f.codigo ?? '') + '</td>' +
                            '<td>' + formato(f.cantidad, 3) + ' ' + f.unidad + '</td>' +
                            '<td>' + formato(f.consumido, 3) + '</td>' +
                            '<td>' + formato(f.restante, 3) + '</td>' +
                            '<td><span class="badge ' + color + '">' + f.porcentaje + '%</span></td>' +
                            '<td>$' + formato(f.gastado, 2) + '</td>' +
                            '</tr>';
                    });

                    document.getElementById('tabla-detalle').innerHTML = filas;
                    document.getElementById('contenedor-info').style.display = 'block';
                })
                .catch((error) => {
                    closeLoading();
                    toastr.error('Error al cargar');
                });
        }

    </script>

@stop
