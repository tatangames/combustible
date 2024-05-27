<?php

namespace App\Http\Controllers\Admin\FacturaV2;

use App\Http\Controllers\Controller;
use App\Models\Equipo;
use App\Models\Facturacion;
use App\Models\TipoCombustible;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class FacturaV2Controller extends Controller
{

    public function __construct(){
        $this->middleware('auth');
    }

    public function indexFactura(){
        $fechaActual = Carbon::now('America/El_Salvador');
        $arrayCombus = TipoCombustible::orderBy('nombre', 'ASC')->get();
        $arrayEquipos = Equipo::orderBy('nombre', 'ASC')->get();

        return view('backend.admin.facturav2.nueva.nuevafacturav2', compact('fechaActual', 'arrayCombus', 'arrayEquipos'));
    }



    public function nuevoFactura(Request $request){

        $regla = array(
            'numfactura' => 'required',
            'fecha' => 'required',
            'producto' => 'required',
            'galones' => 'required',
            'unitario' => 'required'
        );

        // equipo, km

        $validar = Validator::make($request->all(), $regla);

        if ($validar->fails()){ return ['success' => 0];}


        DB::beginTransaction();
        try {

            $registro = new Facturacion();
            $registro->id_equipo = $request->equipo;
            $registro->id_tipocombustible = $request->producto;
            $registro->numero_factura = $request->numfactura;
            $registro->fecha = $request->fecha;
            $registro->cantidad = $request->galones;
            $registro->unitario = $request->unitario;
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




    public function indexFacturacion()
    {
        $arrayEquipos = Equipo::orderBy('nombre', 'ASC')->get();
        return view('backend.admin.facturav2.vistafacturav2', compact('arrayEquipos'));
    }

    public function tablaFacturacionTabla()
    {

        $listado = Facturacion::orderBy('fecha', 'DESC')->get();

        foreach ($listado as $dato){
            $dato->fechaFormat = date("d-m-Y", strtotime($dato->fecha));
            $dato->precioFormat = '$ ' . number_format((float)$dato->unitario, 2, '.', ',');

            $infoEquipo = Equipo::where('id', $dato->id_equipo)->first();
            $dato->nombreEquipo = $infoEquipo->nombre;
            $dato->placaEquipo = $infoEquipo->placa;

            $infoCombustible = TipoCombustible::where('id', $dato->id_tipocombustible)->first();
            $dato->tipoCombustible = $infoCombustible->nombre;


        }

        return view('backend.admin.facturav2.tablafacturav2', compact('listado'));
    }

    public function tablaFacturacionTablaFiltro($filtro){

        if($filtro == '0'){
            // TODOS
            $listado = Facturacion::orderBy('fecha', 'DESC')->get();
        }else{
            $listado = Facturacion::where('id_equipo', $filtro)->orderBy('fecha', 'DESC')->get();
        }


        foreach ($listado as $dato){
            $dato->fechaFormat = date("d-m-Y", strtotime($dato->fecha));
            $dato->precioFormat = '$ ' . number_format((float)$dato->unitario, 2, '.', ',');

            $infoEquipo = Equipo::where('id', $dato->id_equipo)->first();
            $dato->nombreEquipo = $infoEquipo->nombre;
            $dato->placaEquipo = $infoEquipo->placa;

            $infoCombustible = TipoCombustible::where('id', $dato->id_tipocombustible)->first();
            $dato->tipoCombustible = $infoCombustible->nombre;
        }

        return view('backend.admin.facturav2.tablafacturav2', compact('listado'));
    }


    public function informacionFactura(Request $request){

        $regla = array(
            'id' => 'required',
        );

        $validar = Validator::make($request->all(), $regla);

        if ($validar->fails()){ return ['success' => 0];}

        if($info = Facturacion::where('id', $request->id)->first()){

            $arrayPro = TipoCombustible::orderBy('nombre', 'ASC')->get();
            $arrayEquipo = Equipo::orderBy('nombre', 'ASC')->get();


            return ['success' => 1, 'info' => $info, 'arrayproducto' => $arrayPro,
                'arrayequipo' => $arrayEquipo];
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

        // equipo, km

        $validar = Validator::make($request->all(), $regla);

        if ($validar->fails()){ return ['success' => 0];}


        DB::beginTransaction();
        try {

            Facturacion::where('id', $request->id)
                ->update([
                    'id_equipo' => $request->equipo,
                    'id_tipocombustible' => $request->producto,
                    'numero_factura' => $request->numfactura,
                    'fecha' => $request->fecha,
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

        $validar = Validator::make($request->all(), $regla);

        if ($validar->fails()){ return ['success' => 0];}

        if(Facturacion::where('id', $request->id)->first()){

            Facturacion::where('id', $request->id)->delete();

            return ['success' => 1];
        }

        return ['success' => 1];
    }


}
