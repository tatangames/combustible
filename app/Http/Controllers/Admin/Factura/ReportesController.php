<?php

namespace App\Http\Controllers\Admin\Factura;

use App\Http\Controllers\Controller;
use App\Models\Anteriores;
use App\Models\Configuracion;
use App\Models\Equipo;
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

    public function index(){
        $equipo = Equipo::orderBy('tipo', 'ASC')->get();
        return view('backend.admin.reporte.vistareportes', compact('equipo'));
    }

    public function reporteEquipo($desde, $hasta, $idequipo){

        $lista = Factura::where('id_equipo', $idequipo)
        ->whereBetween('fecha', array($desde, $hasta))
            ->orderBy('fecha', 'ASC')->get();

        $fecha1 = Carbon::parse($desde)->format('d-m-Y');
        $fecha2 = Carbon::parse($hasta)->format('d-m-Y');

        $dato = Equipo::where('id', $idequipo)->first();
        $equipo = $dato->tipo;
        $placa = $dato->placa;

        $totalgalones = 0;
        $totalmulti = 0;

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

}
