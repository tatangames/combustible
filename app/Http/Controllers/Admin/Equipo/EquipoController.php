<?php

namespace App\Http\Controllers\Admin\Equipo;

use App\Http\Controllers\Controller;
use App\Models\Equipo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class EquipoController extends Controller
{
    public function __construct(){
        $this->middleware('auth');
    }

    public function indexEquipo(){
        return view('backend.admin.configuracion.equipo.vistaequipo');
    }

    public function tablaEquipo(){

        $listado = Equipo::orderBy('nombre', 'ASC')->get();

        return view('backend.admin.configuracion.equipo.tablaequipo', compact('listado'));
    }

    public function nuevoEquipo(Request $request){

        $regla = array(
            'nombre' => 'required'
        );

        $validar = Validator::make($request->all(), $regla);

        if ($validar->fails()){ return ['success' => 0];}

        DB::beginTransaction();
        try {

            $registro = new Equipo();
            $registro->nombre = $request->nombre;
            $registro->placa = $request->placa;
            $registro->save();

            DB::commit();
            return ['success' => 1];

        }catch(\Throwable $e){
            Log::info('error: ' . $e);
            DB::rollback();
            return ['success' => 99];
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

            return ['success' => 1, 'info' => $lista];
        }else{
            return ['success' => 2];
        }
    }

    // editar
    public function actualizarEquipo(Request $request){

        $regla = array(
            'id' => 'required',
            'nombre' => 'required',
        );

        $validar = Validator::make($request->all(), $regla);

        if ($validar->fails()){ return ['success' => 0];}

        if(Equipo::where('id', $request->id)->first()){

            Equipo::where('id', $request->id)->update([
                'nombre' => $request->nombre,
                'placa' => $request->placa
            ]);

            return ['success' => 1];
        }else{
            return ['success' => 2];
        }
    }

}
