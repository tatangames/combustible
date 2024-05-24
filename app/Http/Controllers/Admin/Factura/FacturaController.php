<?php

namespace App\Http\Controllers\Admin\Factura;

use App\Http\Controllers\Controller;
use App\Models\Factura;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class FacturaController extends Controller
{

    public function indexFactura(){
        return view('backend.admin.factura.vistafactura');
    }


    public function tablaFactura(){

        // Fecha actual
        $fechaActual = Carbon::now();

        // Fecha de tres meses atrás
        $fechaTresMesesAtras = (clone $fechaActual)->subMonths(3);

        $listado = Factura::whereBetween('fecha', [$fechaTresMesesAtras, $fechaActual])
            ->orderBy('idfactura', 'DESC')
            ->get();

        foreach ($listado as $dato){
            $dato->fechaFormat = date("d-m-Y", strtotime($dato->fecha));
            $dato->precioFormat = '$ ' . number_format((float)$dato->unitario, 2, '.', ',');
        }

        return view('backend.admin.factura.tablafactura', compact('listado'));
    }


    public function tablaFacturaTodos($tipo){

        if($tipo == 1){
            // todos los registros

            $listado = Factura::orderBy('idfactura', 'DESC')->get();

            foreach ($listado as $dato){
                $dato->fechaFormat = date("d-m-Y", strtotime($dato->fecha));
                $dato->precioFormat = '$ ' . number_format((float)$dato->unitario, 2, '.', ',');
            }

            return view('backend.admin.factura.tablafactura', compact('listado'));
        }else{
            $fechaActual = Carbon::now();

            // Fecha de tres meses atrás
            $fechaTresMesesAtras = (clone $fechaActual)->subMonths(3);

            $listado = Factura::whereBetween('fecha', [$fechaTresMesesAtras, $fechaActual])
                ->orderBy('idfactura', 'DESC')
                ->get();

            foreach ($listado as $dato){
                $dato->fechaFormat = date("d-m-Y", strtotime($dato->fecha));
                $dato->precioFormat = '$ ' . number_format((float)$dato->unitario, 2, '.', ',');
            }

            return view('backend.admin.factura.tablafactura', compact('listado'));
        }
    }


    public function informacionFactura(Request $request){

        $regla = array(
            'id' => 'required',
        );

        $validar = Validator::make($request->all(), $regla);

        if ($validar->fails()){ return ['success' => 0];}

        if($info = Factura::where('idauto', $request->id)->first()){

            return ['success' => 1, 'info' => $info];
        }else{
            return ['success' => 2];
        }
    }

    public function actualizarFactura(Request $request){

        $regla = array(
            'id' => 'required',
            'numfactura' => 'required',
            'fecha' => 'required',
            'producto' => 'required',
            'equipo' => 'required',
            'galones' => 'required',
            'unitario' => 'required'
        );

        // equipo, placa, km

        $validar = Validator::make($request->all(), $regla);

        if ($validar->fails()){ return ['success' => 0];}


        DB::beginTransaction();
        try {

            Factura::where('idauto', $request->id)
                ->update([
                    'idfactura' => $request->numfactura,
                    'equipo' => $request->equipo,
                    'placa' => $request->placa,
                    'fecha' => $request->fecha,
                    'producto' => $request->producto,
                    'cantidad' => $request->galones,
                    'unitario' => $request->unitario,
                    'km' => $request->km,
                ]);

            DB::commit();
            return ['success' => 1];

        }catch(\Throwable $e){
            Log::info('error: ' . $e);
            DB::rollback();
            return ['success' => 99];
        }
    }

    public function borrarFactura(Request  $request){

        $regla = array(
            'id' => 'required',
        );

        // equipo, placa, km

        $validar = Validator::make($request->all(), $regla);

        if ($validar->fails()){ return ['success' => 0];}

        if(Factura::where('idauto', $request->id)->first()){

            Factura::where('idauto', $request->id)->delete();

            return ['success' => 1];
        }

        return ['success' => 1];
    }



    //********************************************************************************************









    public function indexNuevaFactura(){
        $fechaActual = Carbon::now('America/El_Salvador');

        return view('backend.admin.factura.nueva.nuevafactura', compact('fechaActual'));
    }


    public function nuevaFactura(Request $request){

        $regla = array(
            'numfactura' => 'required',
            'fecha' => 'required',
            'producto' => 'required',
            'linea' => 'required',
            'galones' => 'required',
            'unitario' => 'required'
        );

        // equipo, placa, km

        $validar = Validator::make($request->all(), $regla);

        if ($validar->fails()){ return ['success' => 0];}


        DB::beginTransaction();
        try {

            $registro = new Factura();
            $registro->idfactura = $request->numfactura;
            $registro->linea = $request->linea;
            $registro->equipo = $request->equipo;
            $registro->placa = $request->placa;
            $registro->fecha = $request->fecha;
            $registro->producto = $request->producto;
            $registro->cantidad = $request->galones;
            $registro->unitario = $request->unitario;
            $registro->respaldo = null;
            $registro->km = $request->km;
            $registro->save();

            DB::commit();
            return ['success' => 1];

        }catch(\Throwable $e){
            Log::info('error: ' . $e);
            DB::rollback();
            return ['success' => 99];
        }
    }






}
