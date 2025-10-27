<?php

namespace App\Http\Controllers\Admin\FacturaV2;

use App\Exports\ReporteEquipoExcel;
use App\Exports\ReporteFacturaExcel;
use App\Http\Controllers\Controller;
use App\Models\Contratos;
use App\Models\ContratosDetalle;
use App\Models\Distritos;
use App\Models\Equipo;
use App\Models\Extras;
use App\Models\Factura;
use App\Models\Facturacion;
use App\Models\TipoFondos;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\DB;

class ReporteV2Controller extends Controller
{
    public function __construct(){
        $this->middleware('auth');
    }


    public function vistaReporteFechas(){

        $arrayEquipos = Equipo::orderBy('nombre', 'ASC')->get();
        $arrayDistrito = Distritos::orderBy('nombre', 'ASC')->get();
        $arrayFondos = TipoFondos::orderBy('nombre', 'ASC')->get();

        return view('backend.admin.configuracion.reporte.equipo.vistareporteequipo',
            compact('arrayEquipos', 'arrayDistrito', 'arrayFondos'));
    }
    public function vistaReporteConsolidado(){

        $arrayEquipos = Equipo::orderBy('nombre', 'ASC')->get();
        $arrayDistrito = Distritos::orderBy('nombre', 'ASC')->get();
        $arrayFondos = TipoFondos::orderBy('nombre', 'ASC')->get();

        return view('backend.admin.configuracion.reporte.equipo.vistareporteequipoconsolidado',
            compact('arrayEquipos', 'arrayDistrito', 'arrayFondos'));
    }

    public function vistaReporteFactura(){
        $arrayDistrito = Distritos::orderBy('nombre', 'ASC')->get();
        $arrayFondos = TipoFondos::orderBy('nombre', 'ASC')->get();

        return view('backend.admin.configuracion.reporte.factura.vistareportefactura',
        compact('arrayDistrito', 'arrayFondos'));
    }


    public function reporteEquipoFechaPDF($desde, $hasta, $idequipo, $iddistrito, $idfondo){

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

        $nombreDistrito = "TODOS";
        if($infoDistrito = Distritos::where('id', $iddistrito)->first()){
            $nombreDistrito = $infoDistrito->nombre;
        }

        $nombreFondo = "TODOS";
        if($infoFondo = TipoFondos::where('id', $idfondo)->first()){
            $nombreFondo = $infoFondo->nombre;
        }

        $boolEquipoTodos = true; // defecto buscar por algun equipo
        $boolDistritoTodos = true; // defecto buscar por algun distrito
        $boolFondosTodos = true; // defecto buscar por algun fondos

        if($idequipo == '0'){
            $boolEquipoTodos = false; // defecto seran todos los equipos
        }

        if($iddistrito == '0'){
            $boolDistritoTodos = false; // defecto seran todos los distrito
        }

        if($idfondo == '0'){
            $boolFondosTodos = false; // defecto seran todos los fondos
        }


        $arrayFactura = Facturacion::whereBetween('fecha', [$start, $end])
            ->when($boolEquipoTodos, function($query) use ($idequipo) {
                return $query->where('id_equipo', $idequipo);
            })
            ->when($boolDistritoTodos, function($query) use ($iddistrito) {
                return $query->where('id_distrito', $iddistrito);
            })
            ->when($boolFondosTodos, function($query) use ($idfondo) {
                return $query->where('id_fondos', $idfondo);
            })

            ->orderBy('fecha', 'ASC')
            ->get();



        foreach ($arrayFactura as $dato){
            $dato->fechaFormat = date("d-m-Y", strtotime($dato->fecha));

            $multi = $dato->cantidad * $dato->unitario;

            $formateado = number_format((float) $multi , 2, '.', ',');
            $newnum = (float) str_replace([',', ' '], '', $formateado);

            $totalLinea += $newnum;

            $totalGalonesMixtos += $dato->cantidad;
            $producto = '';

            if($dato->id_tipocombustible == 2){ // REGULAR
                $totalRegular += $newnum;
                $totalGalonRegular += $dato->cantidad;
                $producto = "R";
            }
            else if($dato->id_tipocombustible == 1){ // DIESEL
                $totalDiesel += $newnum;
                $totalGalonDiesel += $dato->cantidad;
                $producto = "D";
            }
            else if($dato->id_tipocombustible == 3){ // ESPECIAL
                $totalEspecial += $newnum;
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

        $totalLinea = number_format((float)$totalLinea, 2, '.', ',');

        $infoExtra = Extras::where('id', 1)->first();

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

        $tabla = "
            <table style='width: 100%;'>
                <tr>
                    <td style='text-align: center;'>
                        <p id='titulo' style='margin: 0;'>REPORTE DE COMBUSTIBLE <br>
                        Gasolinera PUMA Metapán <br>
                        Distrito de: $nombreDistrito <br>
                        Tipo Fondo: $nombreFondo <br>
                        De: $desdeFormat hasta: $hastaFormat <br>
                        </p>
                    </td>
                    <td style='width: 66px; text-align: right;'>
                        <img id='logo' src='$logoalcaldia' style='width: 66px; height: 73px;' />
                    </td>
                </tr>
            </table>";

        $tabla .= "<div style='margin-top: 45px'></div>";


        $tabla .= "<table id='tablaFor' style='width: 72%'>
                <tbody>
                <tr style='background-color: #e1e1e1;'>
                    <th style='text-align: center; font-size:10px; width: 12%; font-weight: bold'>Fecha</th>
                    <th style='text-align: center; font-size:10px; width: 14%; font-weight: bold'>Equipo</th>
                    <th style='text-align: center; font-size:10px; width: 8%; font-weight: bold'>Placa</th>
                    <th style='text-align: center; font-size:10px; width: 12%; font-weight: bold'>Factura</th>
                    <th style='text-align: center; font-size:10px; width: 12%; font-weight: bold'>Prod.</th>
                    <th style='text-align: center; font-size:10px; width: 13%; font-weight: bold'>Descripción</th>
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
                <td style='font-size:10px; text-align: center; font-weight: bold'>$$totalLinea</td>
            </tr>";// Revertí

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

    //Agregue para reporte consolidado de equipos
    public function reporteEquipoConsolidado($desde, $hasta, $idequipo, $iddistrito, $idfondo){

        $start = Carbon::parse($desde)->startOfDay();
        $end = Carbon::parse($hasta)->endOfDay();

        $desdeFormat = date("d-m-Y", strtotime($desde));
        $hastaFormat = date("d-m-Y", strtotime($hasta));

        $nombreDistrito = "TODOS";
        if($infoDistrito = Distritos::where('id', $iddistrito)->first()){
            $nombreDistrito = $infoDistrito->nombre;
        }

        $nombreFondo = "TODOS";
        if($infoFondo = TipoFondos::where('id', $idfondo)->first()){
            $nombreFondo = $infoFondo->nombre;
        }

        $boolEquipoTodos = true; // defecto buscar por algun equipo
        $boolDistritoTodos = true; // defecto buscar por algun distrito
        $boolFondosTodos = true; // defecto buscar por algun fondos

        if($idequipo == '0'){
            $boolEquipoTodos = false; // defecto seran todos los equipos
        }

        if($iddistrito == '0'){
            $boolDistritoTodos = false; // defecto seran todos los distrito
        }

        if($idfondo == '0'){
            $boolFondosTodos = false; // defecto seran todos los fondos
        }

         $arrayFactura = Facturacion::whereBetween('fecha', [$start, $end])
            ->select('id_equipo',
                DB::raw('SUM(cantidad) as total_galones'),
                DB::raw('SUM(ROUND(cantidad, 2) * unitario)  as total_dolares')
            )
            ->when($boolEquipoTodos, function($query) use ($idequipo) {
                return $query->where('id_equipo', $idequipo);
            })
            ->when($boolDistritoTodos, function($query) use ($iddistrito) {
                return $query->where('id_distrito', $iddistrito);
            })
            ->when($boolFondosTodos, function($query) use ($idfondo) {
                return $query->where('id_fondos', $idfondo);
            })
            ->groupBy('id_equipo')
            ->orderBy('id_equipo', 'ASC')
            ->get();

        foreach ($arrayFactura as $dato){
            $dato->fechaFormat = date("d-m-Y", strtotime($dato->fecha));

            $multi = $dato->cantidad * $dato->unitario;

            $producto = '';

            if($dato->id_tipocombustible == 2){ // REGULAR
                $producto = "R";
            }
            else if($dato->id_tipocombustible == 1){ // DIESEL
                $producto = "D";
            }
            else if($dato->id_tipocombustible == 3){ // ESPECIAL
                $producto = "E";
            }

            $dato->producto = $producto;
            $infoEquipo = Equipo::where('id', $dato->id_equipo)->first();

            $dato->equipo = $infoEquipo->nombre;

            $dato->multi = number_format((float)$multi, 2, '.', ',');
        }

        $infoExtra = Extras::where('id', 1)->first();

        if($infoExtra->reporte == 1){
            $mpdf = new \Mpdf\Mpdf(['tempDir' => sys_get_temp_dir(), 'format' => 'LETTER']);
        }else{
            $mpdf = new \Mpdf\Mpdf(['format' => 'LETTER', ]);
        }

        $mpdf->SetTitle('Combustible');
        $mpdf->showImageErrors = false;

        $stylesheet = file_get_contents('css/cssreporte.css');

        $mpdf->WriteHTML($stylesheet,1);


        $logoalcaldia = 'images/logo.png';

        $tabla = "
            <table style='width: 100%;'>
                <tr>
                    <td style='text-align: center;'>
                        <p id='titulo' style='margin: 0;'>REPORTE DE COMBUSTIBLE <br>
                        Gasolinera PUMA Metapán <br>
                        Distrito de: $nombreDistrito <br>
                        Tipo Fondo: $nombreFondo <br>
                        De: $desdeFormat hasta: $hastaFormat <br>
                        </p>
                    </td>
                    <td style='width: 66px; text-align: right;'>
                        <img id='logo' src='$logoalcaldia' style='width: 66px; height: 73px;' />
                    </td>
                </tr>
            </table>";

        $tabla .= "<div style='margin-top: 45px'></div>";


        $tabla .= "<table id='tablaFor' style='width: 72%'>
                <tbody>
                <tr style='background-color: #e1e1e1;'>
                    <th style='text-align: center; font-size:10px; width: 14%; font-weight: bold'>Equipo</th>
                    <th style='text-align: center; font-size:10px; width: 12%; font-weight: bold'>Galones</th>
                    <th style='text-align: center; font-size:10px; width: 12%; font-weight: bold'>Valor</th>
                </tr>";

        foreach ($arrayFactura as $data){

            $totalDolares = number_format((float)$data->total_dolares, 2, '.', ',');

            $tabla .= "<tr>
                <td style='font-size:10px; text-align: center; font-weight: bold'>$data->equipo</td>
                <td style='font-size:10px; text-align: center; font-weight: bold'>$data->total_galones</td>
                <td style='font-size:10px; text-align: center; font-weight: bold'>$$totalDolares</td>

            </tr>";
        }


        $tabla .= "</tbody></table>";


        //***********************************************

        // ************* FOOTER ***************

        $footer = "<table width='100%' id='tablaForTranspa'><tbody>";

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



    public function reporteFacturaPDF($numfactura, $iddistrito, $idfondo){

        $totalRegular = 0;
        $totalDiesel = 0;
        $totalEspecial = 0;
        $totalGalonRegular = 0;
        $totalGalonDiesel = 0;
        $totalGalonEspecial = 0;
        $totalDineroMixto = 0;
        $totalGalonajeColumna = 0;

        $boolDistritoTodos = true; // defecto buscar por algun distrito
        $boolFondosTodos = true; // defecto buscar por algun fondos

        $textoDistrito = "Todos los Distritos";

        if($iddistrito == '0'){
            $boolDistritoTodos = false; // defecto seran todos los distrito
        }else{
            if($infoDistrito = Distritos::where('id', $iddistrito)->first()){
                $textoDistrito = "Distrito de: " . $infoDistrito->nombre;
            }
        }

        $textoFondos = "Todos los Fondos";
        if($idfondo == '0'){
            $boolFondosTodos = false; // defecto seran todos los fondos
        }else{
            if($infoFondo = TipoFondos::where('id', $idfondo)->first()){
                $textoFondos = "Tipo Fondo: " . $infoFondo->nombre;
            }
        }


        $arrayFactura = Facturacion::where('numero_factura', $numfactura)
            ->when($boolDistritoTodos, function($query) use ($iddistrito) {
                return $query->where('id_distrito', $iddistrito);
            })
            ->when($boolFondosTodos, function($query) use ($idfondo) {
                return $query->where('id_fondos', $idfondo);
            })
            ->orderBy('fecha', 'ASC')
            ->get();


        foreach ($arrayFactura as $dato){
            $dato->fechaFormat = date("d-m-Y", strtotime($dato->fecha));

            //$multi = round($dato->cantidad, 2) * $dato->unitario;
            $multi = $dato->cantidad * $dato->unitario;

            $formateado = number_format((float) $multi , 2, '.', ',');
            $newnum = (float) str_replace([',', ' '], '', $formateado);
            //$totalDineroMixto += $multi;
            $totalDineroMixto += $newnum;


            $totalGalonajeColumna += $dato->cantidad;

            $producto = '';

            if($dato->id_tipocombustible == 2){ // REGULAR
                //$totalRegular += $multi;
                $totalRegular += $newnum;
                $totalGalonRegular += $dato->cantidad;
                $producto = "R";
            }
            else if($dato->id_tipocombustible == 1){ // DIESEL
                //$totalDiesel += $multi;
                $totalDiesel += $newnum;
                $totalGalonDiesel += $dato->cantidad;
                $producto = "D";
            }
            else if($dato->id_tipocombustible == 3){ // ESPECIAL
                //$totalEspecial += $multi;
                $totalEspecial += $newnum;
                $totalGalonEspecial += $dato->cantidad;
                $producto = "E";
            }

            $nombreDistrito = "";
            if($infoDistrito = Distritos::where('id', $dato->id_distrito)->first()){
                $nombreDistrito = $infoDistrito->nombre;
            }

            $nombreFondo = "";
            if($infoFondo = TipoFondos::where('id', $dato->id_fondos)->first()){
                $nombreFondo = $infoFondo->nombre;
            }

            $dato->nombredistrito = $nombreDistrito;
            $dato->nombrefondos = $nombreFondo;

            $dato->producto = $producto;

            $infoEquipo = Equipo::where('id', $dato->id_equipo)->first();

            $dato->placa = $infoEquipo->placa;
            $dato->equipo = $infoEquipo->nombre;

           $dato->multi = number_format((float)$multi, 2, '.', ',');
        }

        $totalRegular = number_format((float)$totalRegular, 2, '.', ',');
        $totalDiesel = number_format((float)$totalDiesel, 2, '.', ',');
        $totalEspecial = number_format((float)$totalEspecial, 2, '.', ',');
        //$totalGalonRegular = number_format((float)$totalGalonRegular, 2, '.', ',');
        //$totalGalonDiesel = number_format((float)$totalGalonDiesel, 2, '.', ',');
        //$totalGalonEspecial = number_format((float)$totalGalonEspecial, 2, '.', ',');
        $totalDineroMixto = number_format((float)$totalDineroMixto, 2, '.', ',');
        //$totalDineroMixto = round($totalDineroMixto, 2);

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


        $tabla = "
            <table style='width: 100%;'>
                <tr>
                    <td style='text-align: center;'>
                        <p id='titulo' style='margin: 0;'>REPORTE DE COMBUSTIBLE <br>
                        Gasolinera PUMA Metapán <br>
                        $textoDistrito <br>
                        $textoFondos <br>
                        </p>
                    </td>
                    <td style='width: 66px; text-align: right;'>
                        <img id='logo' src='$logoalcaldia' style='width: 66px; height: 73px;' />
                    </td>
                </tr>
            </table>";

        $tabla .= "<div style='margin-top: 45px'></div>";


        $tabla .= "<table id='tablaFor' style='width: 72%'>
                <tbody>
                <tr style='background-color: #e1e1e1;'>
                    <th style='text-align: center; font-size:10px; width: 12%; font-weight: bold'>Fecha</th>
                    <th style='text-align: center; font-size:10px; width: 14%; font-weight: bold'>Equipo</th>
                    <th style='text-align: center; font-size:10px; width: 9%; font-weight: bold'>Placa</th>
                    <th style='text-align: center; font-size:10px; width: 12%; font-weight: bold'>Factura</th>
                    <th style=';text-align: center; font-size:10px; width: 8% !important; font-weight: bold'>Prod.</th>
                     <th style=';text-align: center; font-size:10px; width: 13%; font-weight: bold'>Descripción</th>
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




    //********************* REPORTE DE CONTRATO *************************

    public function vistaReporteContrato()
    {
        $arrayContrato = Contratos::orderBy('proceso_ref', 'ASC')->get();


        $arrayDistrito = Distritos::orderBy('nombre', 'ASC')
            ->whereNotIn('id', [5]) // NO MOSTRAR SANTA ANA NORTE
            ->get();

        return view('backend.admin.configuracion.reporte.contrato.vistareportecontrato', compact(
            'arrayContrato', 'arrayDistrito'));
    }


    public function reporteContratoDistrito($desde, $hasta, $idcontrato, $iddistrito)
    {
        $hayRegistro = false;
        if(ContratosDetalle::where('id_contratos', $idcontrato)->where('id_distrito', $iddistrito)->first()){
            $hayRegistro = true;
        }

        $infoExtra = Extras::where('id', 1)->first();
        if($infoExtra->reporte == 1){
            $mpdf = new \Mpdf\Mpdf(['tempDir' => sys_get_temp_dir(), 'format' => 'LETTER']);
        }else{
            $mpdf = new \Mpdf\Mpdf(['format' => 'LETTER', ]);
        }
        // mostrar errores
        $mpdf->showImageErrors = false;

        $logoalcaldia = 'images/gobiernologo.jpg';
        $logosantaana = 'images/logo.png';
        $mpdf->SetTitle('Contrato');

        $tabla = "
        <table style='width: 100%; border-collapse: collapse;'>
            <tr>
                <!-- Logo izquierdo -->
                <td style='width: 15%; text-align: left;'>
                    <img src='$logosantaana' alt='Santa Ana Norte' style='max-width: 100px; height: auto;'>
                </td>
                <!-- Texto centrado -->
                <td style='width: 60%; text-align: center;'>
                    <h1 style='font-size: 16px; margin: 0; color: #003366; text-transform: uppercase;'>ALCALDÍA MUNICIPAL DE SANTA ANA NORTE</h1>
                </td>
                <!-- Logo derecho -->
                <td style='width: 10%; text-align: right;'>
                    <img src='$logoalcaldia' alt='Gobierno de El Salvador' style='max-width: 60px; height: auto;'>
                </td>
            </tr>
        </table>
        <hr style='border: none; border-top: 2px solid #003366; margin: 0;'>
        ";


        $tabla .= "
                <div style='text-align: center; margin-top: 20px;'>
                    <p style='font-size: 16px; margin: 0; color: #000;'>ACTA DE ENTREGA PARCIAL</p>
                </div>
                <div style='text-align: center; margin-top: 10px;'>
                </div><br>
              ";

        if($hayRegistro){

            $infoContrato = Contratos::where('id', $idcontrato)->first();
            $infoDistrito = Distritos::where('id', $iddistrito)->first();
            setlocale(LC_TIME, 'es_ES.UTF-8'); // Configurar el idioma a español
            Carbon::setLocale('es'); // Para asegurarnos en Laravel

            $fechaInicio = Carbon::createFromFormat('Y-m-d', $desde);
            $fechaFin = Carbon::createFromFormat('Y-m-d', $hasta);

            $textoFecha = strtoupper($fechaInicio->translatedFormat('j F')) . ' AL ' . strtoupper($fechaFin->translatedFormat('j \\D\\E F Y'));


            $tabla .= "
                <div style='text-align: left; margin-top: 0px;'>
                 <p style='font-size: 15px; margin: 0; color: #000;'><strong>METODO DE CONTRATACIÓN:</strong> LICITACIÓN COMPETITIVA DE BIENES</p>
                 <p style='font-size: 15px; margin: 0; color: #000;'><strong>PROCESO REF. N°:</strong> $infoContrato->proceso_ref</p>
                 <br>
                     <div style='text-align: left; margin-top: 10px;'>
                 <p style='font-size: 15px; margin: 0; color: #000;'><strong>NOMBRE DEL PROCESO:</strong> COMPRA DE COMBUSTIBLE PARA
                 LOS DIFERENTES EQUIPOS DE LA MUNICIPALIDAD DE SANTA ANA NORTE.</p>
                 </div>
                 ";


            $tabla .= "
                <div style='text-align: left; margin-top: 25px;'>
                 <p style='font-size: 15px; margin: 0; color: #000;'>REUNIDOS EN LAS INSTALACIONES DEL <strong>DISTRITO DE METAPAN DE LA
                 ALCALDIA MUNICIPAL DE SANTA ANA NORTE DURANTE LA SEMANA DEL</strong> $textoFecha CON EL PROPÓSITO DE HACER ENTREGA
                 FORMAL DE COMBUSTIBLE</p>";

            $tabla .= "
                <div style='text-align: left; margin-top: 25px;'>
                 <p style='font-size: 15px; margin: 0; color: #000;'>DE FORMA PARCIAL SE RECIBE LO SIGUIENTE: DISTRITO $infoDistrito->nombre</p>
                 ";


            //** TABLA */

            // TOMAR LOS REGISTROS EQUIVALENTES A LA FECHA
            $inicioC = Carbon::parse($infoContrato->fecha_desde)->startOfDay(); // fecha inicio contrato
            $finC = Carbon::parse($infoContrato->fecha_hasta)->endOfDay(); // fecha fin contrato

            $fechaDesde = Carbon::parse($desde)->startOfDay(); // fecha inicio de acta
            $fechaHasta = Carbon::parse($hasta)->endOfDay(); // fecha fin de acta

            $registrosTotalContrato = Facturacion::where('id_distrito', $iddistrito)
            ->whereBetween('fecha', [$inicioC, $finC])->get();


            $registroFecha = Facturacion::where('id_distrito', $iddistrito)
                ->whereBetween('fecha', [$inicioC, $finC])
                ->whereBetween('fecha', [$fechaDesde, $fechaHasta]) // Filtra directamente en la BD
                ->get();

            // Registro desde inicio contrato hasta fin de acta
            $registroInicioCHastaFActa = Facturacion::where('id_distrito', $iddistrito)
            ->whereBetween('fecha', [$inicioC, $fechaHasta])
                ->get();

            $sumaDiesel = 0;
            $sumaRegular = 0;
            $sumaEspecial = 0;
            // Sumar galonaje
            foreach ($registroInicioCHastaFActa as $item){
                if($item->id_tipocombustible == 1){ // DIESEL
                    $sumaDiesel += $item->cantidad;
                }
                else if($item->id_tipocombustible == 2){ // REGULAR
                    $sumaRegular += $item->cantidad;
                }
                else if($item->id_tipocombustible == 3){ // ESPECIAL
                    $sumaEspecial += $item->cantidad;
                }
            }

            $totalGalonDiesel = 0;
            $totalDineroDiesel = 0;

            $totalGalonRegular = 0;
            $totalDineroRegular = 0;

            $totalGalonEspecial = 0;
            $totalDineroEspecial = 0;


            foreach ($registroFecha as $item) {

                $multi = $item->cantidad * $item->unitario;
                $formateado = number_format((float) $multi , 2, '.', ',');
                $newnum = (float) str_replace([',', ' '], '', $formateado);


                if($item->id_tipocombustible == 1){ // DIESEL
                    $totalGalonDiesel += $item->cantidad;
                    $totalDineroDiesel += $newnum;
                }
                else if($item->id_tipocombustible == 2){ // REGULAR
                    $totalGalonRegular += $item->cantidad;
                    $totalDineroRegular += $newnum;
                }
                else if($item->id_tipocombustible == 3){ // ESPECIAL
                    $totalGalonEspecial += $item->cantidad;
                    $totalDineroEspecial += $newnum;
                }
            }

            $totalMontoDinero = ($totalDineroDiesel + $totalDineroRegular + $totalDineroEspecial);

            // SACAR RESTANTES

            $totalContratoDiesel = 0;
            $totalContratoRegular = 0;
            $totalContratoEspecial = 0;
            $arrayContratoDetalle = ContratosDetalle::where('id_contratos', $idcontrato)
                ->where('id_distrito', $iddistrito)->get();

            foreach ($arrayContratoDetalle as $item){
                if($item->id_combustible == 1){ // DIESEL
                    $totalContratoDiesel = $item->cantidad;
                }
                else if($item->id_combustible == 2){ // REGULAR
                    $totalContratoRegular = $item->cantidad;
                }
                else if($item->id_combustible == 3){ // ESPECIAL
                    $totalContratoEspecial = $item->cantidad;
                }
            }


            // CONTRATO DIESEL (210,000) - ENTREGADO FECHA DESDE AL HASTA (2706.402)
            $totalRestanteDiesel = $totalContratoDiesel - $sumaDiesel;
            $totalRestanteRegular = $totalContratoRegular - $sumaRegular;
            $totalRestanteEspecial = $totalContratoEspecial - $sumaEspecial;


            // CONVERTIR EN TEXTO
            $totalDineroDiesel = number_format($totalDineroDiesel, 2, '.', ',');
            $totalDineroRegular = number_format($totalDineroRegular, 2, '.', ',');
            $totalDineroEspecial = number_format((float)$totalDineroEspecial, 2, '.', ',');
            $totalMontoDinero = number_format($totalMontoDinero, 2, '.', ',');


            $totalGalonDiesel = number_format($totalGalonDiesel, 3, '.', ',');
            $totalGalonRegular = number_format($totalGalonRegular, 3, '.', ',');
            $totalGalonEspecial = number_format($totalGalonEspecial, 3, '.', ',');


            $totalRestanteDiesel = number_format($totalRestanteDiesel, 3, '.', ',');
            $totalRestanteRegular = number_format($totalRestanteRegular, 3, '.', ',');
            $totalRestanteEspecial = number_format($totalRestanteEspecial, 3, '.', ',');


            $tabla .= "<table width='100%' id='tablaFor' style='margin-top: 20px'>
                <tbody>";

            $tabla .= "<tr>
                    <td width='8%' style='font-weight: normal; font-size: 12px'>GALONES DIESEL</td>
                    <td width='8%' style='font-weight: normal; font-size: 12px'>MONTO DINERO</td>
                    <td width='8%' style='font-weight: normal; font-size: 12px'>GALONES REGULAR</td>
                    <td width='8%' style='font-weight: normal; font-size: 12px'>MONTO DINERO</td>
                    <td width='8%' style='font-weight: normal; font-size: 12px'>GALONES ESPECIAL</td>
                    <td width='8%' style='font-weight: normal; font-size: 12px'>MONTO DINERO</td>
                    <td width='8%' style='font-weight: normal; font-size: 12px'>MONTO TOTAL DINERO</td>
                </tr>";


                $tabla .= "<tr>
                        <td style='font-size: 12px'>$totalGalonDiesel</td>
                        <td style='font-size: 12px'>$$totalDineroDiesel</td>
                        <td style='font-size: 12px'>$totalGalonRegular</td>
                        <td style='font-size: 12px'>$$totalDineroRegular</td>
                        <td style='font-size: 12px'>$totalGalonEspecial</td>
                        <td style='font-size: 12px'>$$totalDineroEspecial</td>
                        <td style='font-size: 12px'>$$totalMontoDinero</td>
                    </tr>";


            $tabla .= "</tbody></table>";


            $tabla .= "
                <div style='text-align: left; margin-top: 10px;'>
                 <p style='font-size: 15px; margin: 0; color: #000;'><strong>RESTANTE GALONES</strong></p>
                 <p style='font-size: 15px; margin: 0; color: #000;'><strong>DIESEL:</strong> $totalRestanteDiesel</p>
                 <p style='font-size: 15px; margin: 0; color: #000;'><strong>REGULAR:</strong> $totalRestanteRegular</p>
                 <p style='font-size: 15px; margin: 0; color: #000;'><strong>ESPECIAL:</strong> $totalRestanteEspecial</p>
                 ";







            $infoExtra = Extras::where('id', 1)->first();

            $tabla .= "
                <div style='text-align: left; margin-top: 25px;'>
                 <p style='font-size: 15px; margin: 0; color: #000;'>CON BASE A LO SOLICITADO; PRESENTE EL SEÑOR: <strong>$infoContrato->proveedor</strong>
                 POR PARTE DEL PROVEEDOR Y <strong>$infoExtra->nombre3</strong> EN CALIDAD DE ADMINISTRADOR DE CONTRATOS.</p>
                 ";

            $tabla .= "
                <div style='text-align: left; margin-top: 25px;'>
                 <p style='font-size: 15px; margin: 0; color: #000;'>CABE MENCIONAR QUE DICHOS BIENES CUMPLEN CON LAS
                 ESPECIFICACIONES PREVIAMENTE DEFINIDAS EN EL CONTRATO.</p>
                 ";

            $tabla .= "
                <div style='text-align: left; margin-top: 10px;'>
                 <p style='font-size: 15px; margin: 0; color: #000;'>Y NO HABIENDO MÁS QUE HACER CONSTAR, FIRMAMOS Y RATIFICAMOS LA PRESENTE ACTA.</p>
                 ";



            $tabla .= "
            <table style='width: 100%; margin-top: 20px; font-family: \"Times New Roman\", Times, serif; font-size: 14px; color: #000;'>
                <!-- Fila para los títulos -->
                <tr>
                    <td style='width: 50%; text-align: left; padding-bottom: 40px;'>
                        <p style='margin: 0; font-weight: normal; font-size: 16px;  margin-left: 15px'>ENTREGA:</p>
                    </td>
                    <td style='width: 50%; text-align: right; padding-bottom: 40px;'>
                        <p style='margin: 0; font-weight: normal; font-size: 16px; margin-right: 30px'>RECIBE:</p>
                    </td>
                </tr>
                <!-- Fila para los contenidos -->
                <tr>
                    <td style='width: 50%; text-align: center; padding: 20px;'>
                        <p style='margin: 10px 0;'>f.____________________________</p>
                        <p style='margin: 10px 0;'></p>
                        <p style='margin: 10px 0;'>PROVEEDOR</p>
                        <p style='margin: 10px 0;'></p>
                    </td>
                    <td style='width: 50%; text-align: center; padding: 20px;'>
                        <p style='margin: 10px 0;'>f.____________________________</p>
                        <p style='margin: 10px 0;'></p>
                        <p style='margin: 10px 0;'>ADMINISTRADOR DE CONTRATO</p>
                        <p style='margin: 10px 0;'></p>
                    </td>
                </tr>
            </table>
            ";
        } // end-hayregistros




        $stylesheet = file_get_contents('css/csscontrato.css');
        $mpdf->WriteHTML($stylesheet,1);

        //$mpdf->setFooter("Página: " . '{PAGENO}' . "/" . '{nb}');
        $mpdf->WriteHTML($tabla,2);
        $mpdf->Output();
    }






    //***** TODOS LOS DISTRITOS ***********************************

    public function reporteContratoDistritoTodos($desde, $hasta, $idcontrato)
    {
        $infoExtra = Extras::where('id', 1)->first();
        if($infoExtra->reporte == 1){
            $mpdf = new \Mpdf\Mpdf(['tempDir' => sys_get_temp_dir(), 'format' => 'LETTER']);
        }else{
            $mpdf = new \Mpdf\Mpdf(['format' => 'LETTER', ]);
        }


        $arrayDistritos = Distritos::orderBy('nombre', 'ASC')->get();
        $pilaIdDistritos = array();

        foreach ($arrayDistritos as $fila) {
            // GUARDAR ID DE CADA ENTRADA DETALLE
            array_push($pilaIdDistritos, $fila->id);
        }


        $nombresDistritos = "";
        foreach ($arrayDistritos as $item) {
            $nombresDistritos .= "DISTRITO " . $item->nombre . ', ';
        }

        // mostrar errores
        $mpdf->showImageErrors = false;

        $logoalcaldia = 'images/gobiernologo.jpg';
        $logosantaana = 'images/logo.png';
        $mpdf->SetTitle('Contrato');




        $tabla = "
        <table style='width: 100%; border-collapse: collapse;'>
            <tr>
                <!-- Logo izquierdo -->
                <td style='width: 15%; text-align: left;'>
                    <img src='$logosantaana' alt='Santa Ana Norte' style='max-width: 100px; height: auto;'>
                </td>
                <!-- Texto centrado -->
                <td style='width: 60%; text-align: center;'>
                    <h1 style='font-size: 16px; margin: 0; color: #003366; text-transform: uppercase;'>ALCALDÍA MUNICIPAL DE SANTA ANA NORTE</h1>
                </td>
                <!-- Logo derecho -->
                <td style='width: 10%; text-align: right;'>
                    <img src='$logoalcaldia' alt='Gobierno de El Salvador' style='max-width: 60px; height: auto;'>
                </td>
            </tr>
        </table>
        <hr style='border: none; border-top: 2px solid #003366; margin: 0;'>
        ";


        $tabla .= "
                <div style='text-align: center; margin-top: 20px;'>
                    <p style='font-size: 16px; margin: 0; color: #000;'>ACTA DE ENTREGA PARCIAL</p>
                </div>
                <div style='text-align: center; margin-top: 10px;'>
                </div><br>
              ";



            $infoContrato = Contratos::where('id', $idcontrato)->first();
            setlocale(LC_TIME, 'es_ES.UTF-8'); // Configurar el idioma a español
            Carbon::setLocale('es'); // Para asegurarnos en Laravel

            $fechaInicio = Carbon::createFromFormat('Y-m-d', $desde);
            $fechaFin = Carbon::createFromFormat('Y-m-d', $hasta);

            $textoFecha = strtoupper($fechaInicio->translatedFormat('j F')) . ' AL ' . strtoupper($fechaFin->translatedFormat('j \\D\\E F Y'));


            $tabla .= "
                <div style='text-align: left; margin-top: 0px;'>
                 <p style='font-size: 15px; margin: 0; color: #000;'><strong>METODO DE CONTRATACIÓN:</strong> LICITACIÓN COMPETITIVA DE BIENES</p>
                 <p style='font-size: 15px; margin: 0; color: #000;'><strong>PROCESO REF. N°:</strong> $infoContrato->proceso_ref</p>
                 <br>
                     <div style='text-align: left; margin-top: 10px;'>
                 <p style='font-size: 15px; margin: 0; color: #000;'><strong>NOMBRE DEL PROCESO:</strong> COMPRA DE COMBUSTIBLE PARA
                 LOS DIFERENTES EQUIPOS DE LA MUNICIPALIDAD DE SANTA ANA NORTE.</p>
                 </div>
                 ";


            $tabla .= "
                <div style='text-align: left; margin-top: 25px;'>
                 <p style='font-size: 15px; margin: 0; color: #000;'>REUNIDOS EN LAS INSTALACIONES DEL <strong>DISTRITO DE METAPAN DE LA
                 ALCALDIA MUNICIPAL DE SANTA ANA NORTE DURANTE LA SEMANA DEL</strong> $textoFecha CON EL PROPÓSITO DE HACER ENTREGA
                 FORMAL DE COMBUSTIBLE</p>";

            $tabla .= "
                <div style='text-align: left; margin-top: 25px;'>
                 <p style='font-size: 15px; margin: 0; color: #000;'>DE FORMA PARCIAL SE RECIBE LO SIGUIENTE: $nombresDistritos</p>
                 ";


            //** TABLA */

            // TOMAR LOS REGISTROS EQUIVALENTES A LA FECHA
            $inicioC = Carbon::parse($infoContrato->fecha_desde)->startOfDay(); // fecha inicio contrato
            $finC = Carbon::parse($infoContrato->fecha_hasta)->endOfDay(); // fecha fin contrato

            $fechaDesde = Carbon::parse($desde)->startOfDay(); // fecha inicio de acta
            $fechaHasta = Carbon::parse($hasta)->endOfDay(); // fecha fin de acta






           $registrosTotalContrato = Facturacion::whereIn('id_distrito', $pilaIdDistritos)
                ->whereBetween('fecha', [$inicioC, $finC])->get();




            $registroFecha = Facturacion::whereIn('id_distrito', $pilaIdDistritos)
                ->whereBetween('fecha', [$inicioC, $finC])
                ->whereBetween('fecha', [$fechaDesde, $fechaHasta]) // Filtra directamente en la BD
                ->get();




            // Registro desde inicio contrato hasta fin de acta
            $registroInicioCHastaFActa = Facturacion::whereIn('id_distrito', $pilaIdDistritos)
                ->whereBetween('fecha', [$inicioC, $fechaHasta])
                ->get();



            $sumaDiesel = 0;
            $sumaRegular = 0;
            $sumaEspecial = 0;
            // Sumar galonaje
            foreach ($registroInicioCHastaFActa as $item){
                if($item->id_tipocombustible == 1){ // DIESEL
                    $sumaDiesel += $item->cantidad;
                }
                else if($item->id_tipocombustible == 2){ // REGULAR
                    $sumaRegular += $item->cantidad;
                }
                else if($item->id_tipocombustible == 3){ // ESPECIAL
                    $sumaEspecial += $item->cantidad;
                }
            }

            $totalGalonDiesel = 0;
            $totalDineroDiesel = 0;

            $totalGalonRegular = 0;
            $totalDineroRegular = 0;

            $totalGalonEspecial = 0;
            $totalDineroEspecial = 0;


            foreach ($registroFecha as $item) {

                $multi = $item->cantidad * $item->unitario;
                $formateado = number_format((float) $multi , 2, '.', ',');
                $newnum = (float) str_replace([',', ' '], '', $formateado);


                if($item->id_tipocombustible == 1){ // DIESEL
                    $totalGalonDiesel += $item->cantidad;
                    $totalDineroDiesel += $newnum;
                }
                else if($item->id_tipocombustible == 2){ // REGULAR
                    $totalGalonRegular += $item->cantidad;
                    $totalDineroRegular += $newnum;
                }
                else if($item->id_tipocombustible == 3){ // ESPECIAL
                    $totalGalonEspecial += $item->cantidad;
                    $totalDineroEspecial += $newnum;
                }
            }



            $totalMontoDinero = ($totalDineroDiesel + $totalDineroRegular + $totalDineroEspecial);



            // SACAR RESTANTES

            $totalContratoDiesel = 0;
            $totalContratoRegular = 0;
            $totalContratoEspecial = 0;
            $arrayContratoDetalle = ContratosDetalle::where('id_contratos', $idcontrato)
                ->whereIn('id_distrito', $pilaIdDistritos)->get();

            foreach ($arrayContratoDetalle as $item){
                if($item->id_combustible == 1){ // DIESEL
                    $totalContratoDiesel += $item->cantidad;
                }
                else if($item->id_combustible == 2){ // REGULAR
                    $totalContratoRegular += $item->cantidad;
                }
                else if($item->id_combustible == 3){ // ESPECIAL
                    $totalContratoEspecial += $item->cantidad;
                }
            }


            // CONTRATO DIESEL (210,000) - ENTREGADO FECHA DESDE AL HASTA (2706.402)
            $totalRestanteDiesel = $totalContratoDiesel - $sumaDiesel;
            $totalRestanteRegular = $totalContratoRegular - $sumaRegular;
            $totalRestanteEspecial = $totalContratoEspecial - $sumaEspecial;


            // CONVERTIR EN TEXTO
            $totalDineroDiesel = number_format($totalDineroDiesel, 2, '.', ',');
            $totalDineroRegular = number_format($totalDineroRegular, 2, '.', ',');
            $totalDineroEspecial = number_format((float)$totalDineroEspecial, 2, '.', ',');
            $totalMontoDinero = number_format($totalMontoDinero, 2, '.', ',');


            $totalGalonDiesel = number_format($totalGalonDiesel, 3, '.', ',');
            $totalGalonRegular = number_format($totalGalonRegular, 3, '.', ',');
            $totalGalonEspecial = number_format($totalGalonEspecial, 3, '.', ',');


            $totalRestanteDiesel = number_format($totalRestanteDiesel, 3, '.', ',');
            $totalRestanteRegular = number_format($totalRestanteRegular, 3, '.', ',');
            $totalRestanteEspecial = number_format($totalRestanteEspecial, 3, '.', ',');


            $tabla .= "<table width='100%' id='tablaFor' style='margin-top: 20px'>
                <tbody>";

            $tabla .= "<tr>
                    <td width='8%' style='font-weight: normal; font-size: 12px'>GALONES DIESEL</td>
                    <td width='8%' style='font-weight: normal; font-size: 12px'>MONTO DINERO</td>
                    <td width='8%' style='font-weight: normal; font-size: 12px'>GALONES REGULAR</td>
                    <td width='8%' style='font-weight: normal; font-size: 12px'>MONTO DINERO</td>
                    <td width='8%' style='font-weight: normal; font-size: 12px'>GALONES ESPECIAL</td>
                    <td width='8%' style='font-weight: normal; font-size: 12px'>MONTO DINERO</td>
                    <td width='8%' style='font-weight: normal; font-size: 12px'>MONTO TOTAL DINERO</td>
                </tr>";


            $tabla .= "<tr>
                        <td style='font-size: 12px'>$totalGalonDiesel</td>
                        <td style='font-size: 12px'>$$totalDineroDiesel</td>
                        <td style='font-size: 12px'>$totalGalonRegular</td>
                        <td style='font-size: 12px'>$$totalDineroRegular</td>
                        <td style='font-size: 12px'>$totalGalonEspecial</td>
                        <td style='font-size: 12px'>$$totalDineroEspecial</td>
                        <td style='font-size: 12px'>$$totalMontoDinero</td>
                    </tr>";


            $tabla .= "</tbody></table>";


            $tabla .= "
                <div style='text-align: left; margin-top: 10px;'>
                 <p style='font-size: 15px; margin: 0; color: #000;'><strong>RESTANTE GALONES</strong></p>
                 <p style='font-size: 15px; margin: 0; color: #000;'><strong>DIESEL:</strong> $totalRestanteDiesel</p>
                 <p style='font-size: 15px; margin: 0; color: #000;'><strong>REGULAR:</strong> $totalRestanteRegular</p>
                 <p style='font-size: 15px; margin: 0; color: #000;'><strong>ESPECIAL:</strong> $totalRestanteEspecial</p>
                 ";









            $tabla .= "
                <div style='text-align: left; margin-top: 25px;'>
                 <p style='font-size: 15px; margin: 0; color: #000;'>CON BASE A LO SOLICITADO; PRESENTE EL SEÑOR: <strong>$infoContrato->proveedor</strong>
                 POR PARTE DEL PROVEEDOR Y <strong>$infoExtra->nombre3</strong> EN CALIDAD DE ADMINISTRADOR DE CONTRATOS.</p>
                 ";

            $tabla .= "
                <div style='text-align: left; margin-top: 25px;'>
                 <p style='font-size: 15px; margin: 0; color: #000;'>CABE MENCIONAR QUE DICHOS BIENES CUMPLEN CON LAS
                 ESPECIFICACIONES PREVIAMENTE DEFINIDAS EN EL CONTRATO.</p>
                 ";

            $tabla .= "
                <div style='text-align: left; margin-top: 10px;'>
                 <p style='font-size: 15px; margin: 0; color: #000;'>Y NO HABIENDO MÁS QUE HACER CONSTAR, FIRMAMOS Y RATIFICAMOS LA PRESENTE ACTA.</p>
                 ";



            $tabla .= "
            <table style='width: 100%; margin-top: 20px; font-family: \"Times New Roman\", Times, serif; font-size: 14px; color: #000;'>
                <!-- Fila para los títulos -->
                <tr>
                    <td style='width: 50%; text-align: left; padding-bottom: 40px;'>
                        <p style='margin: 0; font-weight: normal; font-size: 16px;  margin-left: 15px'>ENTREGA:</p>
                    </td>
                    <td style='width: 50%; text-align: right; padding-bottom: 40px;'>
                        <p style='margin: 0; font-weight: normal; font-size: 16px; margin-right: 30px'>RECIBE:</p>
                    </td>
                </tr>
                <!-- Fila para los contenidos -->
                <tr>
                    <td style='width: 50%; text-align: center; padding: 20px;'>
                        <p style='margin: 10px 0;'>f.____________________________</p>
                        <p style='margin: 10px 0;'></p>
                        <p style='margin: 10px 0;'>PROVEEDOR</p>
                        <p style='margin: 10px 0;'></p>
                    </td>
                    <td style='width: 50%; text-align: center; padding: 20px;'>
                        <p style='margin: 10px 0;'>f.____________________________</p>
                        <p style='margin: 10px 0;'></p>
                        <p style='margin: 10px 0;'>ADMINISTRADOR DE CONTRATO</p>
                        <p style='margin: 10px 0;'></p>
                    </td>
                </tr>
            </table>
            ";





        $stylesheet = file_get_contents('css/csscontrato.css');
        $mpdf->WriteHTML($stylesheet,1);

        //$mpdf->setFooter("Página: " . '{PAGENO}' . "/" . '{nb}');
        $mpdf->WriteHTML($tabla,2);
        $mpdf->Output();
    }




    //************************************************************************************************


    public function reporteContratoDistritoSantaAnaNorte($desde, $hasta, $idcontrato, $iddistrito)
    {
        /** ==== CONFIG MPDF ==== */
        $infoExtra = Extras::find(1); // una sola vez
        $mpdfOpts  = ['format' => 'LETTER'];
        if ($infoExtra && (int)$infoExtra->reporte === 1) {
            $mpdfOpts['tempDir'] = sys_get_temp_dir();
        }
        $mpdf = new \Mpdf\Mpdf($mpdfOpts);
        $mpdf->showImageErrors = false;
        $mpdf->SetTitle('Contrato');

        /** ==== DATOS BASE ==== */
        $infoContrato = Contratos::findOrFail($idcontrato);
        $infoDistrito = Distritos::findOrFail($iddistrito);

        setlocale(LC_TIME, 'es_ES.UTF-8');
        Carbon::setLocale('es');

        $fechaInicioUsr = Carbon::parse($desde)->startOfDay();
        $fechaFinUsr    = Carbon::parse($hasta)->endOfDay();

        $inicioContrato = Carbon::parse($infoContrato->fecha_desde)->startOfDay();
        $finContrato    = Carbon::parse($infoContrato->fecha_hasta)->endOfDay();

        /** Intersección de rangos (contrato ∩ usuario) */
        $fechaInicio = $inicioContrato->greaterThan($fechaInicioUsr) ? $inicioContrato : $fechaInicioUsr;
        $fechaFin    = $finContrato->lessThan($fechaFinUsr) ? $finContrato : $fechaFinUsr;

        /** Texto de fecha “DEL X AL Y” */
        $textoFecha = strtoupper($fechaInicio->translatedFormat('j F')) . ' AL ' .
            strtoupper($fechaFin->translatedFormat('j \\D\\E F Y'));

        /** ==== RUTAS IMÁGENES (locales) ==== */
        $logoGob  = 'file://' . public_path('images/gobiernologo.jpg');
        $logoSAN  = 'file://' . public_path('images/logo.png');

        /** ==== ENCABEZADO HTML ==== */
        $tabla = <<<HTML
<table style="width:100%; border-collapse:collapse;">
  <tr>
    <td style="width:15%;text-align:left;">
      <img src="$logoSAN" alt="Santa Ana Norte" style="max-width:100px;height:auto;">
    </td>
    <td style="width:60%;text-align:center;">
      <h1 style="font-size:16px; margin:0; color:#003366; text-transform:uppercase;">
        ALCALDÍA MUNICIPAL DE SANTA ANA NORTE
      </h1>
    </td>
    <td style="width:10%;text-align:right;">
      <img src="$logoGob" alt="Gobierno de El Salvador" style="max-width:60px;height:auto;">
    </td>
  </tr>
</table>
<hr style="border:none; border-top:2px solid #003366; margin:0;">
<div style="text-align:center; margin-top:20px;">
  <p style="font-size:16px; margin:0; color:#000;">ACTA DE ENTREGA PARCIAL</p>
</div><br>

<div style="text-align:left;">
  <p style="font-size:15px; margin:0; color:#000;">
    <strong>MÉTODO DE CONTRATACIÓN:</strong> LICITACIÓN COMPETITIVA DE BIENES
  </p>
  <p style="font-size:15px; margin:0; color:#000;">
    <strong>PROCESO REF. N°:</strong> {$infoContrato->proceso_ref}
  </p>
</div>
<div style="text-align:left; margin-top:10px;">
  <p style="font-size:15px; margin:0; color:#000;">
    <strong>NOMBRE DEL PROCESO:</strong> COMPRA DE COMBUSTIBLE PARA LOS DIFERENTES EQUIPOS DE LA MUNICIPALIDAD DE SANTA ANA NORTE.
  </p>
</div>

<div style="text-align:left; margin-top:25px;">
  <p style="font-size:15px; margin:0; color:#000;">
    REUNIDOS EN LAS INSTALACIONES DEL <strong>DISTRITO DE METAPÁN</strong> DE LA ALCALDÍA MUNICIPAL DE SANTA ANA NORTE
    DURANTE LA SEMANA DEL <strong>$textoFecha</strong> CON EL PROPÓSITO DE HACER ENTREGA FORMAL DE COMBUSTIBLE.
  </p>
</div>

<div style="text-align:left; margin-top:25px;">
  <p style="font-size:15px; margin:0; color:#000;">
    DE FORMA PARCIAL SE RECIBE LO SIGUIENTE: DISTRITO <strong>{$infoDistrito->nombre}</strong>
  </p>
</div>
HTML;

        /** ==== QUERIES OPTIMIZADAS ==== */
        /* Totales del PERIODO (intersección) */
        $totPeriodo = Facturacion::where('id_distrito', $iddistrito)
            ->whereBetween('fecha', [$fechaInicio, $fechaFin])
            ->selectRaw("
        SUM(CASE WHEN id_tipocombustible = 1 THEN cantidad ELSE 0 END)            AS gal_diesel,
        SUM(CASE WHEN id_tipocombustible = 2 THEN cantidad ELSE 0 END)            AS gal_regular,
        SUM(CASE WHEN id_tipocombustible = 3 THEN cantidad ELSE 0 END)            AS gal_especial,
        SUM(CASE WHEN id_tipocombustible = 1 THEN cantidad * unitario ELSE 0 END) AS dinero_diesel,
        SUM(CASE WHEN id_tipocombustible = 2 THEN cantidad * unitario ELSE 0 END) AS dinero_regular,
        SUM(CASE WHEN id_tipocombustible = 3 THEN cantidad * unitario ELSE 0 END) AS dinero_especial
    ")
            ->first();

        // acumulado GLOBAL (todos los distritos)
        $consumoPeriodo = Facturacion::whereBetween('fecha', [$fechaInicio, $fechaFin]) // usa la intersección ya calculada
        ->selectRaw("
        SUM(CASE WHEN id_tipocombustible = 1 THEN cantidad ELSE 0 END) AS periodo_diesel,
        SUM(CASE WHEN id_tipocombustible = 2 THEN cantidad ELSE 0 END) AS periodo_regular,
        SUM(CASE WHEN id_tipocombustible = 3 THEN cantidad ELSE 0 END) AS periodo_especial
    ")
            ->first();

        /* Totales del contrato por combustible (IDs fijos 11,12,13 en ContratosDetalle) */
        $detalles = ContratosDetalle::where('id_contratos', $idcontrato)
            ->whereIn('id', [11, 12, 13])
            ->pluck('cantidad', 'id');

        $contratoDiesel   = (float)($detalles[11] ?? 0); // 11 = Diesel
        $contratoRegular  = (float)($detalles[12] ?? 0); // 12 = Regular
        $contratoEspecial = (float)($detalles[13] ?? 0); // 13 = Especial

        /** ==== CÁLCULOS Y FORMATEOS ==== */
        $galDiesel   = (float)($totPeriodo->gal_diesel ?? 0); // 10.000
        $galRegular  = (float)($totPeriodo->gal_regular ?? 0);
        $galEspecial = (float)($totPeriodo->gal_especial ?? 0);

        $dinDiesel   = (float)($totPeriodo->dinero_diesel ?? 0);
        $dinRegular  = (float)($totPeriodo->dinero_regular ?? 0);
        $dinEspecial = (float)($totPeriodo->dinero_especial ?? 0);

        $montoTotal  = $dinDiesel + $dinRegular + $dinEspecial;

        // Restantes = contrato - consumo DEL PERÍODO
        $restDiesel   = $contratoDiesel   - (float)($consumoPeriodo->periodo_diesel   ?? 0);
        $restRegular  = $contratoRegular  - (float)($consumoPeriodo->periodo_regular  ?? 0);
        $restEspecial = $contratoEspecial - (float)($consumoPeriodo->periodo_especial ?? 0);

        /* Formateos (miles y 2/3 decimales) */
        $f = fn($n, $d = 2) => number_format((float)$n, $d, '.', ',');
        $galDieselF   = $f($galDiesel,   3);
        $galRegularF  = $f($galRegular,  3);
        $galEspecialF = $f($galEspecial, 3);

        $dinDieselF   = $f($dinDiesel,   2);
        $dinRegularF  = $f($dinRegular,  2);
        $dinEspecialF = $f($dinEspecial, 2);
        $montoTotalF  = $f($montoTotal,  2);

        $restDieselF   = $f($restDiesel,   3);
        $restRegularF  = $f($restRegular,  3);
        $restEspecialF = $f($restEspecial, 3);

        /** ==== TABLA DE TOTALES ==== */
        $tabla .= <<<HTML
<table width="100%" style="margin-top:20px; border-collapse:collapse; border:1px solid #000;">
  <tbody>
    <tr>
      <td style="font-weight:normal; font-size:12px;">GALONES DIESEL</td>
      <td style="font-weight:normal; font-size:12px;">MONTO DINERO</td>
      <td style="font-weight:normal; font-size:12px;">GALONES REGULAR</td>
      <td style="font-weight:normal; font-size:12px;">MONTO DINERO</td>
      <td style="font-weight:normal; font-size:12px;">GALONES ESPECIAL</td>
      <td style="font-weight:normal; font-size:12px;">MONTO DINERO</td>
      <td style="font-weight:normal; font-size:12px;">MONTO TOTAL DINERO</td>
    </tr>
    <tr>
      <td style="border:1px solid #000; padding:4px; font-size:12px;">$galDieselF</td>
      <td style="border:1px solid #000; padding:4px; font-size:12px;">\${$dinDieselF}</td>
      <td style="border:1px solid #000; padding:4px; font-size:12px;">$galRegularF</td>
      <td style="border:1px solid #000; padding:4px; font-size:12px;">\${$dinRegularF}</td>
      <td style="border:1px solid #000; padding:4px; font-size:12px;">$galEspecialF</td>
      <td style="border:1px solid #000; padding:4px; font-size:12px;">\${$dinEspecialF}</td>
      <td style="border:1px solid #000; padding:4px; font-size:12px;">\${$montoTotalF}</td>
    </tr>
  </tbody>
</table>

<div style="text-align:left; margin-top:10px;">
  <p style="font-size:15px; margin:0; color:#000;"><strong>RESTANTE GALONES</strong></p>
  <p style="font-size:15px; margin:0; color:#000;"><strong>DIESEL:</strong> $restDieselF</p>
  <p style="font-size:15px; margin:0; color:#000;"><strong>REGULAR:</strong> $restRegularF</p>
  <p style="font-size:15px; margin:0; color:#000;"><strong>ESPECIAL:</strong> $restEspecialF</p>
</div>
HTML;

        /** ==== PÁRRAFOS FINALES ==== */
        $tabla .= <<<HTML
<div style="text-align:left; margin-top:25px;">
  <p style="font-size:15px; margin:0; color:#000;">
    CON BASE A LO SOLICITADO; PRESENTE EL SEÑOR: <strong>{$infoContrato->proveedor}</strong> POR PARTE DEL PROVEEDOR
    Y <strong>{$infoExtra->nombre3}</strong> EN CALIDAD DE ADMINISTRADOR DE CONTRATOS.
  </p>
</div>

<div style="text-align:left; margin-top:25px;">
  <p style="font-size:15px; margin:0; color:#000;">
    CABE MENCIONAR QUE DICHOS BIENES CUMPLEN CON LAS ESPECIFICACIONES PREVIAMENTE DEFINIDAS EN EL CONTRATO.
  </p>
</div>

<div style="text-align:left; margin-top:10px;">
  <p style="font-size:15px; margin:0; color:#000;">
    Y NO HABIENDO MÁS QUE HACER CONSTAR, FIrmAMOS Y RATIFICAMOS LA PRESENTE ACTA.
  </p>
</div>

<table style="width:100%; margin-top:20px; font-family:'Times New Roman', Times, serif; font-size:14px; color:#000;">
  <tr>
    <td style="width:50%; text-align:left; padding-bottom:40px;">
      <p style="margin:0; font-weight:normal; font-size:16px; margin-left:15px;">ENTREGA:</p>
    </td>
    <td style="width:50%; text-align:right; padding-bottom:40px;">
      <p style="margin:0; font-weight:normal; font-size:16px; margin-right:30px;">RECIBE:</p>
    </td>
  </tr>
  <tr>
    <td style="width:50%; text-align:center; padding:20px;">
      <p style="margin:10px 0;">f.____________________________</p>
      <p style="margin:10px 0;">PROVEEDOR</p>
    </td>
    <td style="width:50%; text-align:center; padding:20px;">
      <p style="margin:10px 0;">f.____________________________</p>
      <p style="margin:10px 0;">ADMINISTRADOR DE CONTRATO</p>
    </td>
  </tr>
</table>
HTML;

        /** ==== RENDER ==== */
        $stylesheet = file_get_contents(public_path('css/csscontrato.css'));
        $mpdf->WriteHTML($stylesheet, 1);
        $mpdf->WriteHTML($tabla, 2);
        $mpdf->Output();
    }




    public function reporteContratoDistritoTodosSantaAnaNorte($desde, $hasta)
    {



        /* ==== MPDF ==== */
        $infoExtra = Extras::find(1);
        $mpdfOpts  = ['format' => 'LETTER'];
        if ($infoExtra && (int)$infoExtra->reporte === 1) {
            $mpdfOpts['tempDir'] = sys_get_temp_dir();
        }
        $mpdf = new \Mpdf\Mpdf($mpdfOpts);
        $mpdf->showImageErrors = false;
        $mpdf->SetTitle('Acta General');

        /* ==== Distritos (S.A. Norte) ==== */
        $arrayDistritos  = Distritos::orderBy('nombre', 'ASC')->get();
        $pilaIdDistritos = $arrayDistritos->pluck('id')->all();

        /* ==== Contrato y Fechas ==== */
        $infoContrato   = Contratos::findOrFail(2); // contrato general

        setlocale(LC_TIME, 'es_ES.UTF-8');
        Carbon::setLocale('es');

        $fechaDesde     = Carbon::parse($desde)->startOfDay();
        $fechaHasta     = Carbon::parse($hasta)->endOfDay();

        $inicioContrato = Carbon::parse($infoContrato->fecha_desde)->startOfDay();
        $finContrato    = Carbon::parse($infoContrato->fecha_hasta)->endOfDay();

        // Intersección contrato ∩ rango solicitado
        $rangoIni = $inicioContrato->greaterThan($fechaDesde) ? $inicioContrato : $fechaDesde;
        $rangoFin = $finContrato->lessThan($fechaHasta) ? $finContrato : $fechaHasta;

        // Texto de fecha
        $textoFecha = strtoupper($rangoIni->translatedFormat('j F')) . ' AL ' .
            strtoupper($rangoFin->translatedFormat('j \\D\\E F Y'));

        /* ==== Imágenes ==== */
        $logoGob = 'file://' . public_path('images/gobiernologo.jpg');
        $logoSAN = 'file://' . public_path('images/logo.png');




        /* ==== HEADER HTML ==== */
        $tabla = <<<HTML
<table style="width:100%; border-collapse:collapse;">
  <tr>
    <td style="width:15%;text-align:left;">
      <img src="$logoSAN" alt="Santa Ana Norte" style="max-width:100px;height:auto;">
    </td>
    <td style="width:60%;text-align:center;">
      <h1 style="font-size:16px; margin:0; color:#003366; text-transform:uppercase;">
        ALCALDÍA MUNICIPAL DE SANTA ANA NORTE
      </h1>
    </td>
    <td style="width:10%;text-align:right;">
      <img src="$logoGob" alt="Gobierno de El Salvador" style="max-width:60px;height:auto;">
    </td>
  </tr>
</table>
<hr style="border:none; border-top:2px solid #003366; margin:0;">
<div style="text-align:center; margin-top:20px;">
  <p style="font-size:16px; margin:0; color:#000;">ACTA DE ENTREGA PARCIAL</p>
</div><br>

<div style="text-align:left;">
  <p style="font-size:15px; margin:0; color:#000;">
    <strong>MÉTODO DE CONTRATACIÓN:</strong> LICITACIÓN COMPETITIVA DE BIENES
  </p>
  <p style="font-size:15px; margin:0; color:#000;">
    <strong>PROCESO REF. N°:</strong> {$infoContrato->proceso_ref}
  </p>
</div>
<div style="text-align:left; margin-top:10px;">
  <p style="font-size:15px; margin:0; color:#000;">
    <strong>NOMBRE DEL PROCESO:</strong> COMPRA DE COMBUSTIBLE PARA LOS DIFERENTES EQUIPOS DE LA MUNICIPALIDAD DE SANTA ANA NORTE.
  </p>
</div>

<div style="text-align:left; margin-top:25px;">
  <p style="font-size:15px; margin:0; color:#000;">
    REUNIDOS EN LAS INSTALACIONES DE LA <strong>ALCALDÍA MUNICIPAL DE SANTA ANA NORTE</strong>
    DURANTE LA SEMANA DEL <strong>$textoFecha</strong> CON EL PROPÓSITO DE HACER ENTREGA FORMAL DE COMBUSTIBLE.
  </p>
</div>

<div style="text-align:left; margin-top:25px;">
  <p style="font-size:15px; margin:0; color:#000;">
    DE FORMA PARCIAL SE RECIBE LO SIGUIENTE: <strong>ALCALDÍA MUNICIPAL DE SANTA ANA NORTE</strong>
  </p>
</div>
HTML;

        /* ==== QUERIES ==== */
        // Totales del PERÍODO (todos los distritos de S.A. Norte, con redondeo por línea y DECIMAL)
        $totPeriodo = Facturacion::whereIn('id_distrito', $pilaIdDistritos)
            ->whereBetween('fecha', [$rangoIni, $rangoFin])
            ->selectRaw("
            /* galones por tipo */
            SUM(CASE WHEN id_tipocombustible = 1 THEN cantidad ELSE 0 END) AS gal_diesel,
            SUM(CASE WHEN id_tipocombustible = 2 THEN cantidad ELSE 0 END) AS gal_regular,
            SUM(CASE WHEN id_tipocombustible = 3 THEN cantidad ELSE 0 END) AS gal_especial,

            /* dinero por tipo (ROUND por línea) */
            CAST(SUM(ROUND(CASE WHEN id_tipocombustible = 1 THEN cantidad * unitario ELSE 0 END, 2)) AS DECIMAL(18,2)) AS dinero_diesel,
            CAST(SUM(ROUND(CASE WHEN id_tipocombustible = 2 THEN cantidad * unitario ELSE 0 END, 2)) AS DECIMAL(18,2)) AS dinero_regular,
            CAST(SUM(ROUND(CASE WHEN id_tipocombustible = 3 THEN cantidad * unitario ELSE 0 END, 2)) AS DECIMAL(18,2)) AS dinero_especial,

            /* total dinero del período (ROUND por línea) */
            CAST(SUM(ROUND(cantidad * unitario, 2)) AS DECIMAL(18,2)) AS dinero_total
        ")
            ->first();

        // Totales del contrato (IDs fijos 11=Diésel, 12=Regular, 13=Especial)
        $detalles = ContratosDetalle::where('id_contratos', 2)
            ->whereIn('id', [11, 12, 13])
            ->pluck('cantidad', 'id');

        $contratoDiesel   = (float)($detalles[11] ?? 0);
        $contratoRegular  = (float)($detalles[12] ?? 0);
        $contratoEspecial = (float)($detalles[13] ?? 0);

        /* ==== CÁLCULOS ==== */
        $galDiesel   = (float)($totPeriodo->gal_diesel ?? 0);
        $galRegular  = (float)($totPeriodo->gal_regular ?? 0);
        $galEspecial = (float)($totPeriodo->gal_especial ?? 0);

        // Dinero (ya es DECIMAL string)
        $dinDiesel   = (string)($totPeriodo->dinero_diesel ?? '0.00');
        $dinRegular  = (string)($totPeriodo->dinero_regular ?? '0.00');
        $dinEspecial = (string)($totPeriodo->dinero_especial ?? '0.00');
        $montoTotal  = (string)($totPeriodo->dinero_total  ?? '0.00');

        // Restantes = contrato - consumo DEL PERÍODO (todos los distritos)
        $restDiesel   = max(0, $contratoDiesel   - $galDiesel);
        $restRegular  = max(0, $contratoRegular  - $galRegular);
        $restEspecial = max(0, $contratoEspecial - $galEspecial);

        /* ==== FORMATEOS ==== */
        $fmt3 = fn($n) => number_format((float)$n, 3, '.', ','); // galones
        $fmt2 = fn($n) => number_format((float)$n, 2, '.', ','); // dinero

        $galDieselF   = $fmt3($galDiesel);
        $galRegularF  = $fmt3($galRegular);
        $galEspecialF = $fmt3($galEspecial);

        $dinDieselF   = $fmt2($dinDiesel);
        $dinRegularF  = $fmt2($dinRegular);
        $dinEspecialF = $fmt2($dinEspecial);
        $montoTotalF  = $fmt2($montoTotal);

        $restDieselF   = $fmt3($restDiesel);
        $restRegularF  = $fmt3($restRegular);
        $restEspecialF = $fmt3($restEspecial);

        /* ==== TABLA RESUMEN ==== */
        $tabla .= <<<HTML
<table width="100%" style="margin-top:20px; border-collapse:collapse; border:1px solid #000;">
  <tbody>
    <tr>
      <td style="border:1px solid #000; padding:4px; font-weight:bold; font-size:12px;">GALONES DIESEL</td>
      <td style="border:1px solid #000; padding:4px; font-weight:bold; font-size:12px;">MONTO DINERO</td>
      <td style="border:1px solid #000; padding:4px; font-weight:bold; font-size:12px;">GALONES REGULAR</td>
      <td style="border:1px solid #000; padding:4px; font-weight:bold; font-size:12px;">MONTO DINERO</td>
      <td style="border:1px solid #000; padding:4px; font-weight:bold; font-size:12px;">GALONES ESPECIAL</td>
      <td style="border:1px solid #000; padding:4px; font-weight:bold; font-size:12px;">MONTO DINERO</td>
      <td style="border:1px solid #000; padding:4px; font-weight:bold; font-size:12px;">MONTO TOTAL DINERO</td>
    </tr>
    <tr>
      <td style="border:1px solid #000; padding:4px; font-size:12px;">{$galDieselF}</td>
      <td style="border:1px solid #000; padding:4px; font-size:12px;">\${$dinDieselF}</td>
      <td style="border:1px solid #000; padding:4px; font-size:12px;">{$galRegularF}</td>
      <td style="border:1px solid #000; padding:4px; font-size:12px;">\${$dinRegularF}</td>
      <td style="border:1px solid #000; padding:4px; font-size:12px;">{$galEspecialF}</td>
      <td style="border:1px solid #000; padding:4px; font-size:12px;">\${$dinEspecialF}</td>
      <td style="border:1px solid #000; padding:4px; font-size:12px;">\${$montoTotalF}</td>
    </tr>
  </tbody>
</table>

<div style="text-align:left; margin-top:10px;">
  <p style="font-size:15px; margin:0; color:#000;"><strong>RESTANTE GALONES</strong></p>
  <p style="font-size:15px; margin:0; color:#000;"><strong>DIESEL:</strong> {$restDieselF}</p>
  <p style="font-size:15px; margin:0; color:#000;"><strong>REGULAR:</strong> {$restRegularF}</p>
  <p style="font-size:15px; margin:0; color:#000;"><strong>ESPECIAL:</strong> {$restEspecialF}</p>
</div>
HTML;

        /* ==== PÁRRAFOS FINALES ==== */
        $tabla .= <<<HTML
<div style="text-align:left; margin-top:25px;">
  <p style="font-size:15px; margin:0; color:#000;">
    CON BASE A LO SOLICITADO; PRESENTE EL SEÑOR: <strong>{$infoContrato->proveedor}</strong> POR PARTE DEL PROVEEDOR
    Y <strong>{$infoExtra->nombre3}</strong> EN CALIDAD DE ADMINISTRADOR DE CONTRATOS.
  </p>
</div>

<div style="text-align:left; margin-top:25px;">
  <p style="font-size:15px; margin:0; color:#000;">
    CABE MENCIONAR QUE DICHOS BIENES CUMPLEN CON LAS ESPECIFICACIONES PREVIAMENTE DEFINIDAS EN EL CONTRATO.
  </p>
</div>

<div style="text-align:left; margin-top:10px;">
  <p style="font-size:15px; margin:0; color:#000;">
    Y NO HABIENDO MÁS QUE HACER CONSTAR, FIRMAMOS Y RATIFICAMOS LA PRESENTE ACTA.
  </p>
</div>

<table style="width:100%; margin-top:20px; font-family:'Times New Roman', Times, serif; font-size:14px; color:#000;">
  <tr>
    <td style="width:50%; text-align:left; padding-bottom:40px;">
      <p style="margin:0; font-weight:normal; font-size:16px; margin-left:15px;">ENTREGA:</p>
    </td>
    <td style="width:50%; text-align:right; padding-bottom:40px;">
      <p style="margin:0; font-weight:normal; font-size:16px; margin-right:30px;">RECIBE:</p>
    </td>
  </tr>
  <tr>
    <td style="width:50%; text-align:center; padding:20px;">
      <p style="margin:10px 0;">f.____________________________</p>
      <p style="margin:10px 0;">PROVEEDOR</p>
    </td>
    <td style="width:50%; text-align:center; padding:20px;">
      <p style="margin:10px 0;">f.____________________________</p>
      <p style="margin:10px 0;">ADMINISTRADOR DE CONTRATO</p>
    </td>
  </tr>
</table>
HTML;

        /* ==== RENDER ==== */
        $stylesheet = file_get_contents(public_path('css/csscontrato.css'));
        $mpdf->WriteHTML($stylesheet, 1);
        $mpdf->WriteHTML($tabla, 2);
        $mpdf->Output();
    }









}
