<?php

namespace App\Http\Controllers\Admin\Factura;

use App\Http\Controllers\Controller;
use App\Models\Equipo;
use App\Models\Factura;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use PDF;

class EditarFacturaController extends Controller
{
    public function __construct(){
        $this->middleware('auth');
    }

    public function index(){
        return view('backend.admin.factura.editar.index');
    }

    public function tablaFactura(){
        $lista = Factura::orderBy('factura', 'DESC')
            ->where('visible', 1)
            ->get();

        foreach ($lista as $ll){

            $dato = Equipo::where('id', $ll->id_equipo)->first();

            $ll->equipo = $dato->tipo;

            $ll->fecha = date("d-m-Y", strtotime($ll->fecha));
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

            Factura::where('id', $request->id)->update([
                'visible' => 0
            ]);

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

            return ['success' => 1, 'factura' => $lista, 'equipo' => $equipo];
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
                'producto' => $request->producto,
                'cantidad' => $request->galones,
                'unitario' => $request->precio
            ]);

            return ['success' => 1];
        }else{
            return ['success' => 2];
        }
    }




}
