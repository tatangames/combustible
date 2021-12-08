<?php

namespace App\Http\Controllers\Admin\Factura;

use App\Http\Controllers\Controller;
use App\Models\Anteriores;
use App\Models\Equipo;
use App\Models\Factura;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ReportesController extends Controller
{
    public function __construct(){
        $this->middleware('auth');
    }

    public function index(){
        $equipo = Equipo::orderBy('tipo', 'ASC')->get();
        return view('backend.admin.reporte.index', compact('equipo'));
    }

    public function reporteEquipo($desde, $hasta, $idequipo){

        $lista = Factura::where('id_equipo', $idequipo)
            ->where('visible', 1)
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

            $ll->valor = number_format((float)$multi, 2, '.', ',');
        }

        $totalgalones = number_format((float)$totalgalones, 3, '.', ',');
        $totalmulti = number_format((float)$totalmulti, 2, '.', ',');

        $view =  \View::make('backend.admin.reporte.tabla.reporteequipo', compact(['lista', 'totalgalones',
            'totalmulti', 'equipo', 'placa', 'fecha1', 'fecha2']))->render();
        $pdf = \App::make('dompdf.wrapper');
        $pdf->getDomPDF()->set_option("enable_php", true);
        $pdf->loadHTML($view)->setPaper('carta', 'portrait');

        return $pdf->stream();
    }

    public function indexAnterior(){
        return view('backend.admin.reporte.anteriores.index');
    }

    public function reporteFacturaAnterior($desde, $hasta, $equipo){

        $lista = Anteriores::where('equipo', $equipo)
            ->whereBetween('fecha', array($desde, $hasta))
            ->orderBy('fecha', 'ASC')->get();

        $fecha1 = Carbon::parse($desde)->format('d-m-Y');
        $fecha2 = Carbon::parse($hasta)->format('d-m-Y');

        $totalgalones = 0;
        $totalmulti = 0;

        foreach ($lista as $ll){

            $ll->fecha = date("d-m-Y", strtotime($ll->fecha));

            $multi = $ll->cantidad * $ll->unitario;

            $totalmulti = $totalmulti + $multi;
            $totalgalones = $totalgalones + $ll->cantidad;

            $ll->valor = number_format((float)$multi, 2, '.', ',');
        }

        $totalgalones = number_format((float)$totalgalones, 3, '.', ',');
        $totalmulti = number_format((float)$totalmulti, 2, '.', ',');

        $view =  \View::make('backend.admin.reporte.anteriores.tabla.reporteequipo', compact(['lista', 'totalgalones',
            'totalmulti', 'equipo', 'fecha1', 'fecha2']))->render();
        $pdf = \App::make('dompdf.wrapper');
        $pdf->getDomPDF()->set_option("enable_php", true);
        $pdf->loadHTML($view)->setPaper('carta', 'portrait');

        return $pdf->stream();
    }

}
