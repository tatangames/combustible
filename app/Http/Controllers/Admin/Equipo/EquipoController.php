<?php

namespace App\Http\Controllers\Admin\Equipo;

use App\Http\Controllers\Controller;
use App\Models\Equipo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class EquipoController extends Controller
{
    public function __construct(){
        $this->middleware('auth');
    }

    public function index(){
        return view('backend.admin.equipo.index');
    }

    public function tablaEquipo(){
        $lista = Equipo::orderBy('tipo')->get();
        return view('backend.admin.equipo.tabla.tablaequipo', compact('lista'));
    }

    public function nuevoEquipo(Request $request){

        $regla = array(
            'tipo' => 'required'
        );

        $validar = Validator::make($request->all(), $regla);

        if ($validar->fails()){ return ['success' => 0];}


        $dato = new Equipo();
        $dato->tipo = $request->tipo;
        $dato->descripcion = $request->descripcion;
        $dato->placa = $request->placa;

        if($dato->save()){
            return ['success' => 1];
        }else{
            return ['success' => 2];
        }
    }

    // informacion
    public function informacionEquipo(Request $request){
        $regla = array(
            'id' => 'required',
        );

        $validar = Validator::make($request->all(), $regla);

        if ($validar->fails()){ return ['success' => 0];}

        if($lista = Equipo::where('id', $request->id)->first()){

            return ['success' => 1, 'equipo' => $lista];
        }else{
            return ['success' => 2];
        }
    }

    // editar
    public function editarEquipo(Request $request){

        $regla = array(
            'id' => 'required',
            'tipo' => 'required',
        );

        $validar = Validator::make($request->all(), $regla);

        if ($validar->fails()){ return ['success' => 0];}

        if(Equipo::where('id', $request->id)->first()){

            Equipo::where('id', $request->id)->update([
                'tipo' => $request->tipo,
                'descripcion' => $request->descripcion,
                'placa' => $request->placa
            ]);

            return ['success' => 1];
        }else{
            return ['success' => 2];
        }
    }

}
