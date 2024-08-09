<?php

namespace App\Http\Controllers\Admin\FacturaV2;

use App\Exports\ReporteEquipoExcel;
use App\Exports\ReporteFacturaExcel;
use App\Http\Controllers\Controller;
use App\Models\Equipo;
use App\Models\Extras;
use App\Models\Facturacion;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;

class ReporteV2Controller extends Controller
{
    public function __construct(){
        $this->middleware('auth');
    }


    public function vistaReporteFechas(){

        $arrayEquipos = Equipo::orderBy('nombre', 'ASC')->get();
        return view('backend.admin.configuracion.reporte.equipo.vistareporteequipo', compact('arrayEquipos'));
    }

    public function vistaReporteFactura(){
        return view('backend.admin.configuracion.reporte.factura.vistareportefactura');
    }


    public function reporteEquipoFechaPDF($desde, $hasta, $idequipo){

        $start = Carbon::parse($desde)->startOfDay();
        $end = Carbon::parse($hasta)->endOfDay();

        $desdeFormat = date("d-m-Y", strtotime($desde));
        $hastaFormat = date("d-m-Y", strtotime($hasta));


        $totalLinea = 0;
        $totalRegular = 0;
        $totalDiesel = 0;
        $totalEspecial = 0;
        $totalGalonRegular = 0;
        $totalGalonDiesel = 0;
        $totalGalonEspecial = 0;

        $totalGalonesMixtos = 0;


        if($idequipo == '0'){
            // TODOS
            $arrayFactura = Facturacion::whereBetween('fecha', array($start, $end))
                ->orderBy('fecha', 'ASC')
                ->get();
        }else{
            $arrayFactura = Facturacion::whereBetween('fecha', array($start, $end))
                ->where('id_equipo', $idequipo)
                ->orderBy('fecha', 'ASC')
                ->get();
        }


        foreach ($arrayFactura as $dato){
            $dato->fechaFormat = date("d-m-Y", strtotime($dato->fecha));

            $multi = $dato->cantidad * $dato->unitario;
            $totalLinea += $multi;

            $totalGalonesMixtos += $dato->cantidad;

            $producto = '';

            if($dato->id_tipocombustible == 2){ // REGULAR
                $totalRegular += $multi;
                $totalGalonRegular += $dato->cantidad;
                $producto = "R";
            }
            else if($dato->id_tipocombustible == 1){ // DIESEL
                $totalDiesel += $multi;
                $totalGalonDiesel += $dato->cantidad;
                $producto = "D";
            }
            else if($dato->id_tipocombustible == 3){ // ESPECIAL
                $totalEspecial += $multi;
                $totalGalonEspecial += $dato->cantidad;
                $producto = "E";
            }

            $dato->producto = $producto;

            $infoEquipo = Equipo::where('id', $dato->id_equipo)->first();

            $dato->placa = $infoEquipo->placa;
            $dato->equipo = $infoEquipo->nombre;

            $dato->multi = number_format((float)$multi, 2, '.', ',');
        }


        $totalLinea = number_format((float)$totalLinea, 2, '.', ',');
        $totalRegular = number_format((float)$totalRegular, 2, '.', ',');
        $totalDiesel = number_format((float)$totalDiesel, 2, '.', ',');
        $totalEspecial = number_format((float)$totalEspecial, 2, '.', ',');
        $totalGalonRegular = number_format((float)$totalGalonRegular, 2, '.', ',');
        $totalGalonDiesel = number_format((float)$totalGalonDiesel, 2, '.', ',');
        $totalGalonEspecial = number_format((float)$totalGalonEspecial, 2, '.', ',');


        $infoExtra = Extras::where('id', 1)->first();

        // USO LOCAL O SERVIDOR
        if($infoExtra->reporte == 1){
            $mpdf = new \Mpdf\Mpdf(['tempDir' => sys_get_temp_dir(), 'format' => 'LETTER']);
        }else{
            $mpdf = new \Mpdf\Mpdf(['format' => 'LETTER', ]);
        }

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
                 </p>

            </div>";

        $tabla .= "<div style='margin-top: 45px'></div>";


        $tabla .= "<table id='tablaFor' style='width: 72%'>
                <tbody>
                <tr style='background-color: #e1e1e1;'>
                    <th style='text-align: center; font-size:10px; width: 12%; font-weight: bold'>Fecha</th>
                    <th style='text-align: center; font-size:10px; width: 12%; font-weight: bold'>Equipo</th>
                    <th style='text-align: center; font-size:10px; width: 8%; font-weight: bold'>Placa</th>
                    <th style='text-align: center; font-size:10px; width: 12%; font-weight: bold'>Factura</th>
                    <th style='text-align: center; font-size:10px; width: 12%; font-weight: bold'>Prod.</th>
                    <th style='text-align: center; font-size:10px; width: 20%; font-weight: bold'>Descripción</th>
                    <th style='text-align: center; font-size:10px; width: 12%; font-weight: bold'>Galones</th>
                    <th style='text-align: center; font-size:10px; width: 12%; font-weight: bold'>KM</th>
                    <th style='text-align: center; font-size:10px; width: 12%; font-weight: bold'>Precio U.</th>
                    <th style='text-align: center; font-size:10px; width: 12%; font-weight: bold'>Valor</th>
                </tr>";

        foreach ($arrayFactura as $data){

            $tabla .= "<tr>
                <td style='font-size:10px; text-align: center; font-weight: bold'>$data->fechaFormat</td>
                <td style='font-size:10px; text-align: center; font-weight: bold'>$data->equipo</td>
                <td style='font-size:10px; text-align: center; font-weight: bold'>$data->placa</td>
                <td style='font-size:10px; text-align: center; font-weight: bold'>$data->numero_factura</td>
                <td style='font-size:10px; text-align: center; font-weight: bold'>$data->producto</td>
                <td style='font-size:10px; text-align: center; font-weight: bold'>$data->descripcion</td>
                <td style='font-size:10px; text-align: center; font-weight: bold'>$data->cantidad</td>
                <td style='font-size:10px; text-align: center; font-weight: bold'>$data->km</td>
                <td style='font-size:10px; text-align: center; font-weight: bold'>$$data->unitario</td>
                <td style='font-size:10px; text-align: center; font-weight: bold'>$$data->multi</td>

            </tr>";
        }

        $tabla .= "<tr>
                <td colspan='6' style='font-size:11px; text-align: center; font-weight: bold'>TOTAL</td>
                <td style='font-size:11px; text-align: center; font-weight: bold'>$totalGalonesMixtos</td>
                <td style='font-size:11px; text-align: center; font-weight: bold'></td>
                <td style='font-size:11px; text-align: center; font-weight: bold'></td>
                <td style='font-size:11px; text-align: center; font-weight: bold'>$$totalLinea</td>
            </tr>";

        $tabla .= "</tbody></table>";




        //***********************************************



        $tabla .= "<br>";
        $tabla .= "<div style='margin-left: 18px'>";
        $tabla .= "<p style='font-weight: bold; color: #0c525d; font-size: 16px'>TOTAL $$totalLinea</p>";
        $tabla .= "<p style='font-weight: bold; color: #0c525d; font-size: 16px'>TOTAL EN REGULAR: $$totalRegular</p>";
        $tabla .= "<p style='font-weight: bold; color: #0c525d; font-size: 16px'>TOTAL EN DIESEL: $$totalDiesel</p>";
        $tabla .= "<p style='font-weight: bold; color: #0c525d; font-size: 16px'>TOTAL EN ESPECIAL: $$totalEspecial</p>";
        $tabla .= "<p style='font-weight: bold; color: #0c525d; font-size: 16px'>TOTAL Galones en Regular: $totalGalonRegular</p>";
        $tabla .= "<p style='font-weight: bold; color: #0c525d; font-size: 16px'>TOTAL Galones en Diesel: $totalGalonDiesel</p>";
        $tabla .= "<p style='font-weight: bold; color: #0c525d; font-size: 16px'>TOTAL Galones en Especial: $totalGalonEspecial</p>";
        $tabla .= "</div>";








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



    public function reporteFacturaPDF($numfactura){

        $totalRegular = 0;
        $totalDiesel = 0;
        $totalEspecial = 0;
        $totalGalonRegular = 0;
        $totalGalonDiesel = 0;
        $totalGalonEspecial = 0;

        $totalDineroMixto = 0;
        $totalGalonajeColumna = 0;

        $arrayFactura = Facturacion::where('numero_factura', $numfactura)
            ->orderBy('fecha', 'DESC')
            ->get();

        foreach ($arrayFactura as $dato){
            $dato->fechaFormat = date("d-m-Y", strtotime($dato->fecha));

            $multi = $dato->cantidad * $dato->unitario;

            $pasado = number_format((float) $multi , 2, '.', ',');
            $numero = (float) str_replace([',', ' '], '', $pasado);
            $totalDineroMixto += $numero;

            $totalGalonajeColumna += $dato->cantidad;

            $producto = '';

            if($dato->id_tipocombustible == 2){ // REGULAR
                $totalRegular += $multi;
                $totalGalonRegular += $dato->cantidad;
                $producto = "R";
            }
            else if($dato->id_tipocombustible == 1){ // DIESEL
                $totalDiesel += $multi;
                $totalGalonDiesel += $dato->cantidad;
                $producto = "D";
            }
            else if($dato->id_tipocombustible == 3){ // ESPECIAL
                $totalEspecial += $multi;
                $totalGalonEspecial += $dato->cantidad;
                $producto = "E";
            }

            $dato->producto = $producto;

            $infoEquipo = Equipo::where('id', $dato->id_equipo)->first();

            $dato->placa = $infoEquipo->placa;
            $dato->equipo = $infoEquipo->nombre;

            $dato->multi = number_format((float)$multi, 2, '.', ',');
        }


        $totalRegular = number_format((float)$totalRegular, 2, '.', ',');
        $totalDiesel = number_format((float)$totalDiesel, 2, '.', ',');
        $totalEspecial = number_format((float)$totalEspecial, 2, '.', ',');
        $totalGalonRegular = number_format((float)$totalGalonRegular, 2, '.', ',');
        $totalGalonDiesel = number_format((float)$totalGalonDiesel, 2, '.', ',');
        $totalGalonEspecial = number_format((float)$totalGalonEspecial, 2, '.', ',');
        $totalDineroMixto = number_format((float)$totalDineroMixto, 2, '.', ',');


        $infoExtra = Extras::where('id', 1)->first();

        // USO LOCAL O SERVIDOR
        if($infoExtra->reporte == 1){
            $mpdf = new \Mpdf\Mpdf(['tempDir' => sys_get_temp_dir(), 'format' => 'LETTER']);
        }else{
            $mpdf = new \Mpdf\Mpdf(['format' => 'LETTER', ]);
        }

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
                  Factura: $numfactura <br>
                 </p>

            </div>";

        $tabla .= "<div style='margin-top: 45px'></div>";


        $tabla .= "<table id='tablaFor' style='width: 72%'>
                <tbody>
                <tr style='background-color: #e1e1e1;'>
                    <th style='text-align: center; font-size:10px; width: 12%; font-weight: bold'>Fecha</th>
                    <th style='text-align: center; font-size:10px; width: 15%; font-weight: bold'>Equipo</th>
                    <th style='text-align: center; font-size:10px; width: 9%; font-weight: bold'>Placa</th>
                    <th style='text-align: center; font-size:10px; width: 12%; font-weight: bold'>Factura</th>
                    <th style=';text-align: center; font-size:10px; width: 8% !important; font-weight: bold'>Prod.</th>
                     <th style=';text-align: center; font-size:10px; width: 20%; font-weight: bold'>Descripción</th>
                    <th style='text-align: center; font-size:10px; width: 12%; font-weight: bold'>Galones</th>
                    <th style='text-align: center; font-size:10px; width: 12%; font-weight: bold'>KM</th>
                    <th style='text-align: center; font-size:10px; width: 12%; font-weight: bold'>Precio U.</th>
                    <th style='text-align: center; font-size:10px; width: 12%; font-weight: bold'>Valor</th>
                </tr>";

        foreach ($arrayFactura as $data){

            $tabla .= "<tr>
                <td style='font-size:10px; text-align: center; font-weight: bold'>$data->fechaFormat</td>
                <td style='font-size:10px; text-align: center; font-weight: bold'>$data->equipo</td>
                <td style='font-size:10px; text-align: center; font-weight: bold'>$data->placa</td>
                <td style='font-size:10px; text-align: center; font-weight: bold'>$data->numero_factura</td>
                <td style='font-size:10px; text-align: center; font-weight: bold'>$data->producto</td>
                <td style='font-size:10px; text-align: center; font-weight: bold'>$data->descripcion</td>
                <td style='font-size:10px; text-align: center; font-weight: bold'>$data->cantidad</td>
                <td style='font-size:10px; text-align: center; font-weight: bold'>$data->km</td>
                <td style='font-size:10px; text-align: center; font-weight: bold'>$$data->unitario</td>
                <td style='font-size:10px; text-align: center; font-weight: bold'>$$data->multi</td>

            </tr>";
        }

        $tabla .= "<tr>
                <td colspan='6' style='font-size:11px; text-align: center; font-weight: bold'>TOTAL</td>
                 <td style='font-size:10px; text-align: center; font-weight: bold'>$totalGalonajeColumna</td>
                 <td style='font-size:10px; text-align: center; font-weight: bold'></td>
                <td style='font-size:10px; text-align: center; font-weight: bold'></td>
                <td style='font-size:10px; text-align: center; font-weight: bold'>$$totalDineroMixto</td>
            </tr>";

        $tabla .= "</tbody></table>";




        //***********************************************



        $tabla .= "<br>";
        $tabla .= "<div style='margin-left: 18px'>";
        $tabla .= "<p style='font-weight: bold; color: #0c525d; font-size: 16px'>TOTAL $$totalDineroMixto</p>";
        $tabla .= "<p style='font-weight: bold; color: #0c525d; font-size: 16px'>TOTAL EN REGULAR: $$totalRegular</p>";
        $tabla .= "<p style='font-weight: bold; color: #0c525d; font-size: 16px'>TOTAL EN DIESEL: $$totalDiesel</p>";
        $tabla .= "<p style='font-weight: bold; color: #0c525d; font-size: 16px'>TOTAL EN ESPECIAL: $$totalEspecial</p>";
        $tabla .= "<p style='font-weight: bold; color: #0c525d; font-size: 16px'>TOTAL Galones en Regular: $totalGalonRegular</p>";
        $tabla .= "<p style='font-weight: bold; color: #0c525d; font-size: 16px'>TOTAL Galones en Diesel: $totalGalonDiesel</p>";
        $tabla .= "<p style='font-weight: bold; color: #0c525d; font-size: 16px'>TOTAL Galones en Especial: $totalGalonEspecial</p>";
        $tabla .= "</div>";








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



}
