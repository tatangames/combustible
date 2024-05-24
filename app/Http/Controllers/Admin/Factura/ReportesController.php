<?php

namespace App\Http\Controllers\Admin\Factura;

use App\Http\Controllers\Controller;
use App\Models\Anteriores;
use App\Models\Configuracion;
use App\Models\Equipo;
use App\Models\Extras;
use App\Models\Factura;
use App\Models\TipoCombustible;
use Carbon\Carbon;
use Illuminate\Cookie\CookieValuePrefix;
use Illuminate\Http\Request;

class ReportesController extends Controller
{
    public function __construct(){
        $this->middleware('auth');
    }

    public function vistaReporteFechas(){

        return view('backend.admin.reporte.vistareportefecha');
    }

    public function indexCambioNombre(){

        $infoExtra = Extras::where('id', 1)->first();
        return view('backend.admin.extras.vistaextra', compact('infoExtra'));
    }


    public function reporteFacturaFecha($desde, $hasta){

        $start = Carbon::parse($desde)->startOfDay();
        $end = Carbon::parse($hasta)->endOfDay();

        $desdeFormat = date("d-m-Y", strtotime($desde));
        $hastaFormat = date("d-m-Y", strtotime($hasta));


        $arrayFactura = Factura::whereBetween('fecha', array($start, $end))
                                ->orderBy('fecha', 'DESC')
                                ->get();

        foreach ($arrayFactura as $dato){
            $dato->fechaFormat = date("d-m-Y", strtotime($dato->fecha));

            $multi = $dato->cantidad * $dato->unitario;
            $dato->multi = number_format((float)$multi, 2, '.', ',');
        }

        $infoExtra = Extras::where('id', 1)->first();


        //$mpdf = new \Mpdf\Mpdf(['tempDir' => sys_get_temp_dir(), 'format' => 'LETTER']);
        $mpdf = new \Mpdf\Mpdf(['format' => 'LETTER', ]);
        $mpdf->SetTitle('Combustible');

        // mostrar errores
        $mpdf->showImageErrors = false;

        $stylesheet = file_get_contents('css/cssreporte.css');

        $mpdf->WriteHTML($stylesheet,1);


        $logoalcaldia = 'images/logo.png';

        $tabla = "<div class='content'>
            <img id='logo' src='$logoalcaldia'>
            <p id='titulo'>REPORTE DE COMBUSTIBLE <br>
            GASOLINERA SERVICENTRO METAPAN <br>
                  De: $desdeFormat hasta: $hastaFormat <br>
                    DE LINEA 0101 <br>
                 </p>

            </div>";

        $tabla .= "<div style='margin-top: 45px'></div>";


        $tabla .= "<table id='tablaFor' style='width: 72%'>
                <tbody>
                <tr style='background-color: #e1e1e1;'>
                    <th style='text-align: center; font-size:13px; width: 12%; font-weight: bold'>Fecha</th>
                    <th style='text-align: center; font-size:13px; width: 12%; font-weight: bold'>Equipo</th>
                    <th style='text-align: center; font-size:13px; width: 8%; font-weight: bold'>Placa</th>
                    <th style='text-align: center; font-size:13px; width: 12%; font-weight: bold'>Factura</th>
                    <th style='text-align: center; font-size:13px; width: 20%; font-weight: bold'>Prod.</th>
                    <th style='text-align: center; font-size:13px; width: 12%; font-weight: bold'>Galones</th>
                    <th style='text-align: center; font-size:13px; width: 12%; font-weight: bold'>KM</th>
                    <th style='text-align: center; font-size:13px; width: 12%; font-weight: bold'>Precio U.</th>
                    <th style='text-align: center; font-size:13px; width: 12%; font-weight: bold'>Valor</th>
                </tr>";

        foreach ($arrayFactura as $data){

            $tabla .= "<tr>
                <td style='font-size:13px; text-align: center; font-weight: bold'>$data->fechaFormat</td>
                <td style='font-size:13px; text-align: center; font-weight: bold'>$data->equipo</td>
                <td style='font-size:13px; text-align: center; font-weight: bold'>$data->placa</td>
                <td style='font-size:13px; text-align: center; font-weight: bold'>$data->idfactura</td>
                <td style='font-size:13px; text-align: center; font-weight: bold'>$data->producto</td>
                <td style='font-size:13px; text-align: center; font-weight: bold'>$data->cantidad</td>
                <td style='font-size:13px; text-align: center; font-weight: bold'>$data->km</td>
                <td style='font-size:13px; text-align: center; font-weight: bold'>$data->unitario</td>
                <td style='font-size:13px; text-align: center; font-weight: bold'>$$data->multi</td>

            </tr>";
        }



        $tabla .= "</tbody></table>";

        // ************* FOOTER ***************

        $footer = "<table width='100%' id='tablaForTranspa'>
            <tbody>";

        $footer .= "</tbody></table>";

        $footer .= "<table width='100%' id='tablaForTranspa' style='margin-top: 35px'>
            <tbody>";

        $footer .= "<tr>
                    <td width='25%' style='font-weight: normal; font-size: 14px'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; ______________________________ <br> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;$infoExtra->nombre1 <br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;$infoExtra->nombre2</td>
                    <td width='25%' style='font-weight: normal; font-size: 14px'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; _________________________________________<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; &nbsp;&nbsp;&nbsp;&nbsp; &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;$infoExtra->nombre3 <br> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; $infoExtra->nombre4

                    </td>
                    </tr>";

        $footer .= "<tr>
                    <td colspan=2 style='font-weight: bold; text-align: center; font-size: 12px'> Página {PAGENO}/{nb}</td>
                    </tr>";

        $footer .= "</tbody></table>";


        $mpdf->SetHTMLFooter($footer);

        $mpdf->SetAutoPageBreak(true, 45);


        $mpdf->WriteHTML($tabla, 2);


        $mpdf->Output();

    }



    public function reporteEquipo($desde, $hasta, $idequipo){





        $lista = Factura::where('id_equipo', $idequipo)
        ->whereBetween('fecha', array($desde, $hasta))
            ->orderBy('fecha', 'ASC')->get();

        $fecha1 = Carbon::parse($desde)->format('d-m-Y');
        $fecha2 = Carbon::parse($hasta)->format('d-m-Y');



        foreach ($lista as $ll){

            $ll->fecha = date("d-m-Y", strtotime($ll->fecha));

            $multi = $ll->cantidad * $ll->unitario;

            $totalmulti = $totalmulti + $multi;
            $totalgalones = $totalgalones + $ll->cantidad;

            $ll->valorunitario = number_format((float)$ll->unitario, 2, '.', ',');

            $ll->multiplicado = number_format((float)$multi, 2, '.', ',');

            $tipocom = TipoCombustible::where('id', $ll->id_tipocombustible)->first();
            $ll->unaletra = substr($tipocom->nombre, 0, 1);
        }

        $totalgalones = number_format((float)$totalgalones, 3, '.', ',');
        $totalmulti = '$' . number_format((float)$totalmulti, 2, '.', ',');


        $infoConfig = Configuracion::where('id', 1)->first();


        //$mpdf = new \Mpdf\Mpdf(['tempDir' => sys_get_temp_dir(), 'format' => 'LETTER']);
        $mpdf = new \Mpdf\Mpdf(['format' => 'LETTER', ]);
        $mpdf->SetTitle('Combustible');

        // mostrar errores
        $mpdf->showImageErrors = false;

        $stylesheet = file_get_contents('css/cssreporte.css');

        $mpdf->WriteHTML($stylesheet,1);


        $logoalcaldia = 'images/logo2.png';

        $tabla = "<div class='content'>
            <img id='logo' src='$logoalcaldia'>
            <p id='titulo'>ALCALDÍA MUNICIPAL DE METAPÁN <br>
            REPORTE DE COMBUSTIBLE <br>
              Gasolinera Metapán<br>
              Equipo: $equipo<br>
              Placa: $placa<br>
                 </p>
                 <p style='font-size: 16px; margin-left: 165px; font-weight: bold; margin-bottom: 20px !important;'>
                De: $fecha1 Hasta: $fecha2 <br>
             </p>
            </div>";

        $tabla .= "<div style='margin-top: 45px'></div>";


        $tabla .= "<table id='tablaFor' style='width: 72%'>
                <tbody>
                <tr style='background-color: #e1e1e1;'>
                    <th style='text-align: center; font-size:13px; width: 12%; font-weight: bold'>Fecha</th>
                    <th style='text-align: center; font-size:13px; width: 12%; font-weight: bold'>Factura</th>
                    <th style='text-align: center; font-size:13px; width: 8%; font-weight: bold'>Pro.</th>
                    <th style='text-align: center; font-size:13px; width: 12%; font-weight: bold'>Galones</th>
                    <th style='text-align: center; font-size:13px; width: 20%; font-weight: bold'>Precio U.</th>
                    <th style='text-align: center; font-size:13px; width: 12%; font-weight: bold'>Valor</th>
                </tr>";

        foreach ($lista as $data){

            $tabla .= "<tr>
                <td style='font-size:13px; text-align: center; font-weight: bold'>$data->fecha</td>
                <td style='font-size:13px; text-align: center; font-weight: bold'>$data->factura</td>
                <td style='font-size:13px; text-align: center; font-weight: bold'>$data->unaletra</td>
                <td style='font-size:13px; text-align: center; font-weight: bold'>$data->cantidad</td>
                <td style='font-size:13px; text-align: center; font-weight: bold'>$$data->valorunitario</td>
                <td style='font-size:13px; text-align: center; font-weight: bold'>$$data->multiplicado</td>
            </tr>";
        }

        $tabla .= "<tr>
                <td colspan='3' style='font-size:15px;  text-align: center; font-weight: bold'>Total</td>
                <td style='font-size:13px; text-align: center; font-weight: bold'>$totalgalones</td>
                <td style='font-size:13px; text-align: center; font-weight: bold'></td>
                <td style='font-size:13px; text-align: center; font-weight: bold'>$totalmulti</td>
            </tr>";

        $tabla .= "</tbody></table>";

        // ************* FOOTER ***************

        $footer = "<table width='100%' id='tablaForTranspa'>
            <tbody>";

        $footer .= "</tbody></table>";

        $footer .= "<table width='100%' id='tablaForTranspa' style='margin-top: 35px'>
            <tbody>";

        $footer .= "<tr>
                    <td width='25%' style='font-weight: normal; font-size: 14px'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; ______________________________ <br> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;$infoConfig->nombre1 <br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;$infoConfig->cargo1</td>
                    <td width='25%' style='font-weight: normal; font-size: 14px'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; _________________________________________<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; &nbsp;&nbsp;&nbsp;&nbsp; &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;$infoConfig->nombre2 <br> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; $infoConfig->cargo2

                    </td>
                    </tr>";

        $footer .= "<tr>
                    <td colspan=2 style='font-weight: bold; text-align: center; font-size: 12px'> Página {PAGENO}/{nb}</td>
                    </tr>";

        $footer .= "</tbody></table>";


        $mpdf->SetHTMLFooter($footer);

        $mpdf->SetAutoPageBreak(true, 45);

        $mpdf->WriteHTML($tabla, 2);


        $mpdf->Output();
    }






    public function actualizarBloqueNombre(Request $request){

        Extras::where('id', 1)
            ->update([
                'nombre1' => $request->nombre1,
                'nombre2' => $request->nombre2,
                'nombre3' => $request->nombre3,
                'nombre4' => $request->nombre4,
            ]);

        return ['success' => 1];
    }



}
