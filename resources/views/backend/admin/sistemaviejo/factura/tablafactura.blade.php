<section class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <table id="tabla" class="table table-bordered table-striped">
                            <thead>
                            <tr>
                                <th>Fecha</th>
                                <th>Factura</th>
                                <th>Equipo</th>
                                <th>Producto</th>
                                <th>Precio</th>
                                <th>Cantidad</th>

                                <th>Opciones</th>
                            </tr>
                            </thead>
                            <tbody>

                            @foreach($listado as $dato)
                                <tr>
                                    <td>{{ $dato->fechaFormat }}</td>
                                    <td>{{ $dato->idfactura }}</td>
                                    <td>{{ $dato->equipo }}</td>
                                    <td>{{ $dato->producto }}</td>
                                    <td>{{ $dato->precioFormat }}</td>
                                    <td>{{ $dato->cantidad }}</td>
                                    <td>

                                        <button type="button" class="btn btn-success btn-xs" onclick="informacion({{ $dato->idauto }})">
                                            <i class="fas fa-eye" title="Editar"></i>&nbsp; Editar
                                        </button>

                                        <button type="button" style="margin-left: 5px" class="btn btn-danger btn-xs" onclick="modalBorrar({{ $dato->idauto }})">
                                            <i class="fas fa-trash" title="Borrar"></i>&nbsp; Borrar
                                        </button>

                                    </td>

                                </tr>
                            @endforeach

                            <script>
                                setTimeout(function () {
                                    closeLoading();
                                }, 1000);
                            </script>

                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>


<script>
    $(document).ready(function() {

        // Añadir la función de ordenación personalizada
        jQuery.extend(jQuery.fn.dataTableExt.oSort, {
            "date-dmy-pre": function(a) {
                var ukDatea = a.split('-');

                // Convierte la fecha al formato YYYYMMDD para que pueda ser comparada fácilmente
                return (ukDatea[2] + ukDatea[1] + ukDatea[0]) * 1;
            },

            "date-dmy-asc": function(a, b) {
                return a - b;
            },

            "date-dmy-desc": function(a, b) {
                return b - a;
            }
        });

        $("#tabla").DataTable({
            "columnDefs": [
                { "type": "date-dmy", "targets": 0 } // Cambia el índice según la columna de fecha
            ],
            "order": [[0, "desc"]],
            "paging": true,
            "lengthChange": true,
            "searching": true,
            "ordering": true,
            "info": true,
            "autoWidth": false,
            "pagingType": "full_numbers",
            "lengthMenu": [[10, 25, 50, 100, 150, -1], [10, 25, 50, 100, 150, "Todo"]],
            "language": {

                "sProcessing": "Procesando...",
                "sLengthMenu": "Mostrar _MENU_ registros",
                "sZeroRecords": "No se encontraron resultados",
                "sEmptyTable": "Ningún dato disponible en esta tabla",
                "sInfo": "Mostrando registros del _START_ al _END_ de un total de _TOTAL_ registros",
                "sInfoEmpty": "Mostrando registros del 0 al 0 de un total de 0 registros",
                "sInfoFiltered": "(filtrado de un total de _MAX_ registros)",
                "sInfoPostFix": "",
                "sSearch": "Buscar:",
                "sUrl": "",
                "sInfoThousands": ",",
                "sLoadingRecords": "Cargando...",
                "oPaginate": {
                    "sFirst": "Primero",
                    "sLast": "Último",
                    "sNext": "Siguiente",
                    "sPrevious": "Anterior"
                },
                "oAria": {
                    "sSortAscending": ": Activar para ordenar la columna de manera ascendente",
                    "sSortDescending": ": Activar para ordenar la columna de manera descendente"
                }

            },
            "responsive": true, "lengthChange": true, "autoWidth": false,
        });
    });


</script>
