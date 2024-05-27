<table>
    <thead>
        <tr>
            <th>Fecha</th>
            <th>Equipo</th>
            <th>Placa</th>
            <th>Factura</th>
            <th>Producto</th>
            <th>Galones</th>
            <th>KM</th>
            <th>Precio Unitario</th>
            <th>Valor</th>
        </tr>
    </thead>

    <tbody>
        @foreach($listado as $dato)
            <tr>
                <td>{{ $dato->fechaFormat }}</td>
                <td>{{ $dato->equipo }}</td>
                <td>{{ $dato->placa }}</td>
                <td>{{ $dato->numero_factura }}</td>
                <td>{{ $dato->producto }}</td>
                <td>{{ $dato->cantidad }}</td>
                <td>{{ $dato->km }}</td>
                <td>{{ $dato->unitario }}</td>
                <td>{{ $dato->multi }}</td>
            </tr>
        @endforeach


        <tr>
            <td colspan="8" style="text-align: center">TOTAL</td>
            <td>$EE</td>
        </tr>

        <tr>
            <td colspan="9" style="text-align: center"></td>
        </tr>

        <tr>
            <td colspan="9" style="text-align: center"></td>
        </tr>


        <tr>
            <td style="text-align: center">TOTAL</td>
            <td style="text-align: center">{{ $totalLinea }}</td>
        </tr>
        <tr>
            <td style="text-align: center">TOTAL EN REGULAR</td>
            <td style="text-align: center">{{ $totalRegular }}</td>
        </tr>
        <tr>
            <td style="text-align: center">TOTAL EN DIESEL</td>
            <td style="text-align: center">{{ $totalDiesel }}</td>
        </tr>
        <tr>
            <td style="text-align: center">TOTAL EN ESPECIAL</td>
            <td style="text-align: center">{{ $totalEspecial }}</td>
        </tr>
        <tr>
            <td style="text-align: center">TOTAL Galones en Regular</td>
            <td style="text-align: center">{{ $totalGalonRegular }}</td>
        </tr>
        <tr>
            <td style="text-align: center">TOTAL Galones en Diesel</td>
            <td style="text-align: center">{{ $totalGalonDiesel }}</td>
        </tr>
        <tr>
            <td style="text-align: center">TOTAL Galones en Especial</td>
            <td style="text-align: center">{{ $totalGalonEspecial }}</td>
        </tr>


    </tbody>

</table>
