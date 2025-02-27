<?php

namespace App\Http\Controllers\Admin\Equipo;

use App\Http\Controllers\Controller;
use App\Models\Contratos;
use App\Models\ContratosDetalle;
use App\Models\Distritos;
use App\Models\TipoCombustible;
use App\Models\UnidadMedida;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class ContratoController extends Controller
{
    public function __construct(){
        $this->middleware('auth');
    }

    public function indexContrato(){
        return view('backend.admin.configuracion.contrato.vistacontrato');
    }

    public function tablaContrato(){
        $listado = Contratos::orderBy('proceso_ref', 'ASC')->get();

        foreach ($listado as $item) {
            $item->fechaDesdeFormat = date("d-m-Y", strtotime($item->fecha_desde));
            $item->fechaHastaFormat = date("d-m-Y", strtotime($item->fecha_hasta));
        }

        return view('backend.admin.configuracion.contrato.tablacontrato', compact('listado'));
    }

    public function nuevoContrato(Request $request)
    {
        $regla = array(
            'fechaDesde' => 'required',
            'fechaHasta' => 'required',
        );

        // proveedor, procesoReferencia, proceso

        $validar = Validator::make($request->all(), $regla);

        if ($validar->fails()){ return ['success' => 0];}

        DB::beginTransaction();
        try {
            $registro = new Contratos();
            $registro->proveedor = $request->proveedor;
            $registro->proceso_ref = $request->procesoReferencia;
            $registro->nombre_proceso = $request->proceso;
            $registro->fecha_desde = $request->fechaDesde;
            $registro->fecha_hasta = $request->fechaHasta;
            $registro->save();

            DB::commit();
            return ['success' => 1];

        }catch(\Throwable $e){
            Log::info('error: ' . $e);
            DB::rollback();
            return ['success' => 99];
        }
    }

    public function informacionContrato(Request $request)
    {
        $regla = array(
            'id' => 'required',
        );

        $validar = Validator::make($request->all(), $regla);

        if ($validar->fails()){ return ['success' => 0];}

        if($lista = Contratos::where('id', $request->id)->first()){

            return ['success' => 1, 'info' => $lista];
        }else{
            return ['success' => 2];
        }
    }

    public function editarContrato(Request $request)
    {
        $regla = array(
            'id' => 'required',
            'fechaDesde' => 'required',
            'fechaHasta' => 'required',
        );

        // proveedor, procesoReferencia, proceso

        $validar = Validator::make($request->all(), $regla);

        if ($validar->fails()){ return ['success' => 0];}

        if(Contratos::where('id', $request->id)->first()){

            Contratos::where('id', $request->id)->update([
                'proveedor' => $request->proveedor,
                'proceso_ref' => $request->procesoReferencia,
                'nombre_proceso' => $request->proceso,
                'fecha_desde' => $request->fechaDesde,
                'fecha_hasta' => $request->fechaHasta,
            ]);

            return ['success' => 1];
        }else{
            return ['success' => 2];
        }
    }





    //********** DETALLE ************************

    public function indexContratoDetalle($id)
    {
        $arrayDistritos = Distritos::orderBy('nombre', 'ASC')->get();
        $arrayCombustible = TipoCombustible::orderBy('nombre', 'ASC')->get();
        $arrayUnidad = UnidadMedida::orderBy('nombre', 'ASC')->get();
        return view('backend.admin.configuracion.contrato.detalle.vistacontratodetalle', compact('id',
        'arrayDistritos', 'arrayCombustible', 'arrayUnidad'));
    }


    public function tablaContratoDetalle($id)
    {
        $listado = ContratosDetalle::where('id_contratos', $id)->get();

        foreach ($listado as $item){

            $infoDistrito = Distritos::where('id', $item->id_distrito)->first();
            $item->nombreDistrito = $infoDistrito->nombre;

            $infoCombustible = TipoCombustible::where('id', $item->id_combustible)->first();
            $item->nombreCombustible = $infoCombustible->nombre;

            $infoUnidad = UnidadMedida::where('id', $item->id_unidad)->first();
            $item->nombreUnidad = $infoUnidad->nombre;
        }

        return view('backend.admin.configuracion.contrato.detalle.tablacontratodetalle', compact('listado'));
    }


    public function nuevoContratoDetalle(Request $request)
    {
        $regla = array(
            'id' => 'required',
            'distrito' => 'required',
            'combustible' => 'required',
            'unidad' => 'required',
            'codigo' => 'required',
        );

        $validar = Validator::make($request->all(), $regla);

        if ($validar->fails()){ return ['success' => 0];}

        DB::beginTransaction();
        try {

            // NO REPETIR DISTRITO CON COMBUSTIBLE DEL MISMO CONTRATO
            if(ContratosDetalle::where('id_contratos', $request->id)
            ->where('id_distrito', $request->distrito)
            ->where('id_combustible', $request->combustible)
            ->first()){
                return ['success' => 1];
            }

            $registro = new ContratosDetalle();
            $registro->id_contratos = $request->id;
            $registro->id_distrito = $request->distrito;
            $registro->id_combustible = $request->combustible;
            $registro->id_unidad = $request->unidad;
            $registro->codigo = $request->codigo;
            $registro->save();

            DB::commit();
            return ['success' => 2];

        }catch(\Throwable $e){
            Log::info('error: ' . $e);
            DB::rollback();
            return ['success' => 99];
        }
    }









}
