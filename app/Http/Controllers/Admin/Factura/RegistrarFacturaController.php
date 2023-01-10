<?php

namespace App\Http\Controllers\Admin\Factura;

use App\Http\Controllers\Controller;
use App\Models\Equipo;
use App\Models\Factura;
use App\Models\TipoCombustible;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;


class RegistrarFacturaController extends Controller
{
    public function __construct(){
        $this->middleware('auth');
    }

    public function index(){

        $equipo = Equipo::orderBy('tipo')->get();
        $arraycombustible = TipoCombustible::orderBy('id', 'ASC')->get();

        return view('backend.admin.factura.crear.vistacrearfactura', compact('equipo', 'arraycombustible'));
    }

    public function nuevaFactura(Request $request){

        $regla = array(
            'factura' => 'required',
            'equipo' => 'required',
            'combustible' => 'required', // tipo combustible
            'fecha' => 'required',
            'galones' => 'required',
            'precio' => 'required'
        );

        $validar = Validator::make($request->all(), $regla);

        if ($validar->fails()){ return ['success' => 0];}

        $dato = new Factura();
        $dato->id_equipo = $request->equipo;
        $dato->factura = $request->factura;
        $dato->fecha = $request->fecha;
        $dato->id_tipocombustible = $request->combustible;
        $dato->cantidad = $request->galones;
        $dato->unitario = $request->precio;

        if($dato->save()){
            return ['success' => 1];
        }else{
            return ['success' => 2];
        }

    }
}
