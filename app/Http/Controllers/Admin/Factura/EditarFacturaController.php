<?php

namespace App\Http\Controllers\Admin\Factura;

use App\Http\Controllers\Controller;
use App\Models\Configuracion;
use App\Models\Equipo;
use App\Models\Factura;
use App\Models\TipoCombustible;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use PDF;

class EditarFacturaController extends Controller{


    public function __construct(){
        $this->middleware('auth');
    }

    public function index(){



        return view('backend.admin.factura.editar.vistaeditarfactura');
    }

    public function tablaFactura(){
        $lista = Factura::orderBy('factura', 'DESC')->get();

        foreach ($lista as $ll){

            $dato = Equipo::where('id', $ll->id_equipo)->first();
            $ll->equipo = $dato->tipo;
            $ll->fecha = date("d-m-Y", strtotime($ll->fecha));

            $infoCom = TipoCombustible::where('id', $ll->id_tipocombustible)->first();
            $ll->tipocombustible = $infoCom->nombre;

            $ll->unitario = '$' . number_format((float)$ll->unitario, 2, '.', ',');
        }

        return view('backend.admin.factura.editar.tabla.tablaeditar', compact('lista'));
    }

    public function borrarFactura(Request $request){

        $regla = array(
            'id' => 'required',
        );

        $validar = Validator::make($request->all(), $regla);

        if ($validar->fails()){ return ['success' => 0];}

        if(Factura::where('id', $request->id)->first()){

            Factura::where('id', $request->id)->delete();

            return ['success' => 1];
        }else{
            return ['success' => 2];
        }
    }

    public function infoFactura(Request $request){

        $regla = array(
            'id' => 'required',
        );

        $validar = Validator::make($request->all(), $regla);

        if ($validar->fails()){ return ['success' => 0];}

        if($lista = Factura::where('id', $request->id)->first()){

            $equipo = Equipo::orderBy('tipo', 'ASC')->get();
            $combustible = TipoCombustible::orderBy('id', 'ASC')->get();

            return ['success' => 1, 'factura' => $lista, 'equipo' => $equipo,
                'combustible' => $combustible];
        }else{
            return ['success' => 2];
        }
    }

    public function editarFactura(Request $request){

        $regla = array(
            'id' => 'required',
        );

        $validar = Validator::make($request->all(), $regla);

        if ($validar->fails()){ return ['success' => 0];}

        if(Factura::where('id', $request->id)->first()){

            Factura::where('id', $request->id)->update([
                'id_equipo' => $request->equipo,
                'factura' => $request->factura,
                'fecha' => $request->fecha,
                'id_tipocombustible' => $request->combustible,
                'cantidad' => $request->galones,
                'unitario' => $request->precio
            ]);

            return ['success' => 1];
        }else{
            return ['success' => 2];
        }
    }

    // configuración de nombres
    public function indexConfiguracion(){

        $lista = Configuracion::where('id', 1)->first();
        return view('backend.admin.configuracion.vistaconfiguracion', compact('lista'));
    }

    public function editarNombres(Request $request){

        Configuracion::where('id', 1)->update([
            'nombre1' => $request->nombre1,
            'cargo1' => $request->cargo1,
            'nombre2' => $request->nombre2,
            'cargo2' => $request->cargo2,
        ]);

        return ['success' => 1];
    }


}
