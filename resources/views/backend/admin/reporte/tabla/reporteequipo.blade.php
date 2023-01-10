<html>
<head>
    <title>Alcaldía Metapán | Panel</title>
    <style>
        body{
            font-family: Arial;
        }
        @page {
            margin: 145px 25px;
            /* margin-bottom: 10%;*/
        }
        header { position: fixed;
            left: 0px;
            top: -173px;
            right: 0px;
            height: 0px;
            text-align: center;
            font-size: 12px;
        }
        header h1{
            margin: 10px 0;
        }
        header h2{
            margin: 0 0 10px 0;
        }
        footer {
            position: fixed;
            left: 0px;
            bottom: -10px;
            right: 0px;
            height: 10px;
            /* border-bottom: 2px solid #ddd;*/
        }

        footer table {
            width: 100%;
        }
        footer p {
            text-align: right;
        }
        footer .izq {
            margin-top: 20px; !important;
            margin-left: 20px;
            text-align: left;
        }

        footer .derecha {
            margin-top: 20px; !important;
            margin-right: 30px;
            text-align: right;
        }

        .content {
            padding: 20px;
            margin-left: auto;
            margin-right: auto;

        }

        .content img {
            margin-right: 15px;
            float: right;
        }

        .content h3{
            font-size: 20px;

        }
        .content p{
            margin-left: 15px;
            display: block;
            margin: 2px 0 0 0;
        }

        hr{
            page-break-after: always;
            border: none;
            margin: 0;
            padding: 0;
        }

        #tabla {
            border-collapse: collapse;
            width: 100%;
            margin-left: 20px;
            margin-right: 20px;
            margin-top: 35px;
            text-align: center;
        }

        #tabla td{
            border: 1px solid #ddd;
            padding: 8px;
            text-align: center;
            font-size: 15px;
        }

        #tabla th {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: center;
        }

        #tabla th {
            padding-top: 12px;
            padding-bottom: 12px;
            background-color: #f2f2f2;
            color: #1E1E1E;
            text-align: center;
            font-size: 16px;
        }


    </style>
<body>
<header style="margin-top: 25px; margin-bottom: 25px">
    <div class="row">

        <div class="content">
            <img src="{{ asset('images/logo.png') }}" style="float: right" alt="" height="88px" width="72px">
            <p style="text-align: center; font-weight: bold; font-size: 16px">REPORTE DE COMBUSTIBLE <br> Gasolinera METAPAN <br> EQUIPO: {{ $equipo }} <br> PLACA: {{ $placa }}</p>
            <p style="text-align: center; font-weight: bold; font-size: 16px; margin-right: 67px">de {{ $fecha1 }} hasta: {{ $fecha2 }}</p>
            <p style="text-align: center; font-weight: bold; font-size: 16px; margin-right: 67px; margin-bottom: 30px">DE LINEA 0101</p>
        </div>

    </div>

</header>

<footer>
    <table>
        <tr>
            <td>
                <p class="izq" style="font-size: 14px">
                    _________________________<br>
                    Blanca Mariela Argueta Mayorga <br>
                    Encargada de Combustible Interina
                    <br>
                </p>
            </td>
            <td>
                <p class="page">

                </p>
            </td>
            <td>
                <p class="derecha" style="font-size: 14px">
                    ______________________________<br>
                    Lic. Darwin Francisco Sandoval Nolasco <br>
                    Administrador de Contrato &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                    <br>
                </p>
            </td>

        </tr>
    </table>
</footer>

<div id="content">

    <table id="tabla" style="width: 80%; margin: 0 auto; margin-top: 10px; margin-bottom: 35px" >
        <thead>
        <tr>
            <th style="text-align: center; font-size:15px; width: 12%">Fecha</th>
            <th style="text-align: center; font-size:15px; width: 12%">Factura</th>
            <th style="text-align: center; font-size:15px; width: 8%">Prod.</th>
            <th style="text-align: center; font-size:15px; width: 14%">Galones</th>
            <th style="text-align: center; font-size:15px; width: 13%">Precio U.</th>
            <th style="text-align: center; font-size:15px; width: 14%">Valor</th>
        </tr>
        </thead>

        @foreach($lista as $item)
            <tr>
                <td style="font-size:14px; text-align: left">{{ $item->fecha }}</td>
                <td style="font-size:14px; text-align: left">{{ $item->factura }}</td>
                <td style="font-size:14px; text-align: left">{{ $item->producto }}</td>
                <td style="font-size:14px; text-align: left">{{ $item->cantidad }}</td>
                <td style="font-size:14px; text-align: left">${{ $item->unitario }}</td>
                <td style="font-size:14px; text-align: left">${{ $item->valor }}</td>
            </tr>

        @endforeach

        <tr>
            <td style="font-size: 14px; text-align: center;" colspan="3">TOTAL</td>
            <td style="font-size:14px; text-align: left">{{ $totalgalones }}</td>
            <td style="font-size:14px; text-align: left"></td>
            <td style="font-size:14px; text-align: left">${{ $totalmulti }}</td>
        </tr>



    </table>

</div>

<script type="text/php">
    if (isset($pdf)) {
        $x = 258;
        $y = 720;
        $text = "Página {PAGE_NUM} de {PAGE_COUNT}";
        $font = null;
        $size = 10;
        $color = array(0,0,0);
        $word_space = 0.0;  //  default
        $char_space = 0.0;  //  default
        $angle = 0.0;   //  default
        $pdf->page_text($x, $y, $text, $font, $size, $color, $word_space, $char_space, $angle);
    }
</script>

</body>
</html>
