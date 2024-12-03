<?php

namespace App\Http\Controllers\Admin\FacturaV2;

use App\Exports\ReporteEquipoExcel;
use App\Exports\ReporteFacturaExcel;
use App\Http\Controllers\Controller;
use App\Models\Distritos;
use App\Models\Equipo;
use App\Models\Extras;
use App\Models\Facturacion;
use App\Models\TipoFondos;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
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

            //$multi = round($dato->cantidad, 2) * $dato->unitario;
            $multi = $dato->cantidad * $dato->unitario;

            $formateado = number_format((float) $multi , 2, '.', ',');
            $newnum = (float) str_replace([',', ' '], '', $formateado);
            //$totalLinea += $multi;
            $totalLinea += $newnum;
          

            $totalGalonesMixtos += $dato->cantidad;
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

            $dato->producto = $producto;
            $infoEquipo = Equipo::where('id', $dato->id_equipo)->first();

            $dato->placa = $infoEquipo->placa;
            $dato->equipo = $infoEquipo->nombre;

            $dato->multi = number_format((float)$multi, 2, '.', ',');
        }
        $totalLinea = round($totalLinea, 2);

         //Para reparar problema de centavo y 4 decimales en factura de gasolinera, se hizo de esta manera para cuadrar el calculo inverso que hace la gasolinera
         $unitario = Facturacion::whereBetween('fecha', [$start, $end])
         ->where('id_tipocombustible', 1)
         ->value('unitario');
            $totalGalonDiesel = $totalDiesel / $unitario;
            $totalGalonDiesel = number_format((float)$totalGalonDiesel, 4, '.', ',');
            //+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
            $unitario = Facturacion::whereBetween('fecha', [$start, $end])
                ->where('id_tipocombustible', 2)
                ->value('unitario');
            $totalGalonRegular = $totalRegular / $unitario;
            $totalGalonRegular = number_format((float)$totalGalonRegular, 4, '.', ',');
            //+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
            $unitario = Facturacion::whereBetween('fecha', [$start, $end])
                ->where('id_tipocombustible', 3)
                ->value('unitario');
            $totalGalonEspecial = $totalEspecial / $unitario;
            $totalGalonEspecial = number_format((float)$totalGalonEspecial, 4, '.', ',');
            //+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
            //Galonaje con aproximación "reparada"
            $nuevototalGalonajeColumna = $totalGalonDiesel + $totalGalonRegular + $totalGalonEspecial;
            $nuevototalGalonajeColumna = number_format((float)$nuevototalGalonajeColumna, 3, '.', ',');

        
        $totalRegular = number_format((float)$totalRegular, 2, '.', ',');
        $totalDiesel = number_format((float)$totalDiesel, 2, '.', ',');
        $totalEspecial = number_format((float)$totalEspecial, 2, '.', ',');
        //$totalGalonRegular = number_format((float)$totalGalonRegular, 2, '.', ',');
        //$totalGalonDiesel = number_format((float)$totalGalonDiesel, 2, '.', ',');
        //$totalGalonEspecial = number_format((float)$totalGalonEspecial, 2, '.', ',');

        //$totalGalonesMixtos = number_format((float)$totalGalonesMixtos, 3, '.', ','); 


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
                <td style='font-size:11px; text-align: center; font-weight: bold'>$nuevototalGalonajeColumna</td>
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
            
            $totalLinea += $multi;
           
            $totalGalonesMixtos += $dato->total_galones;
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

           // $dato->placa = $infoEquipo->placa;
            $dato->equipo = $infoEquipo->nombre;

            $dato->multi = number_format((float)$multi, 2, '.', ',');
        }

        $totalLinea = round($totalLinea, 2);

        //$totalGalonesMixtos = number_format((float)$totalGalonesMixtos, 3, '.', ',');


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

        //$tabla .= "<tr>
        //        <td colspan='1' style='font-size:11px; text-align: center; font-weight: bold'>TOTAL</td>
        //        <td style='font-size:11px; text-align: center; font-weight: bold'>$totalGalonesMixtos</td>
        //        <td style='font-size:11px; text-align: center; font-weight: bold'>$$totalLinea</td>
        //    </tr>";

        $tabla .= "</tbody></table>";


        //***********************************************

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
        //Para reparar problema de centavo y 4 decimales en factura de gasolinera, se hizo de esta manera para cuadrar el calculo inverso que hace la gasolinera
        $unitario1 = Facturacion::where('numero_factura', $numfactura)
            ->where('id_tipocombustible', 1)
            ->value('unitario');

        if($unitario1){$totalGalonDiesel = $totalDiesel / $unitario1;
        }else{
            $totalGalonDiesel = 0.0;
        }
        $totalGalonDiesel = number_format((float)$totalGalonDiesel, 4, '.', ',');
        //+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
        $unitario2 = Facturacion::where('numero_factura', $numfactura)
            ->where('id_tipocombustible', 2)
            ->value('unitario');
        if($unitario2){$totalGalonRegular = $totalRegular / $unitario2;
        }else{
            $totalGalonRegular = 0.0;
        }
        $totalGalonRegular = $totalRegular / $unitario;
        $totalGalonRegular = number_format((float)$totalGalonRegular, 4, '.', ',');
        //+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
        $unitario3 = Facturacion::where('numero_factura', $numfactura)
            ->where('id_tipocombustible', 3)
            ->value('unitario');
        if($unitario3){$totalGalonEspecial = $totalEspecial / $unitario3;
        }else{
            $totalGalonEspecial = 0.0;
        }
        $totalGalonEspecial = $totalEspecial / $unitario;
        $totalGalonEspecial = number_format((float)$totalGalonEspecial, 4, '.', ',');
        //+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
        //Galonaje con aproximación "reparada"
        $nuevototalGalonajeColumna = $totalGalonDiesel + $totalGalonRegular + $totalGalonEspecial;
        $nuevototalGalonajeColumna = number_format((float)$nuevototalGalonajeColumna, 3, '.', ',');

        $totalRegular = number_format((float)$totalRegular, 2, '.', ',');
        $totalDiesel = number_format((float)$totalDiesel, 2, '.', ',');
        $totalEspecial = number_format((float)$totalEspecial, 2, '.', ',');
        //$totalGalonRegular = number_format((float)$totalGalonRegular, 2, '.', ',');
        //$totalGalonDiesel = number_format((float)$totalGalonDiesel, 2, '.', ',');
        //$totalGalonEspecial = number_format((float)$totalGalonEspecial, 2, '.', ',');
        //$totalDineroMixto = number_format((float)$totalDineroMixto, 2, '.', ',');
        $totalDineroMixto = round($totalDineroMixto, 2);

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
                 <td style='font-size:10px; text-align: center; font-weight: bold'>$nuevototalGalonajeColumna</td>
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
