<?php

namespace App\Http\Controllers\Admin\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Distritos;
use App\Models\TipoFondos;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class DashboarController extends Controller
{

    public function __construct(){
        $this->middleware('auth');
    }

    public function indexDashboard()
    {
        $arrayContratos = DB::table('contratos')
            ->orderBy('fecha_desde', 'DESC')
            ->get();

        return view('backend.admin.dashboard.vistadashboard', compact('arrayContratos'));
    }


    public function infoConsumoContrato(Request $request)
    {
        $regla = array('id' => 'required');
        $validar = Validator::make($request->all(), $regla);

        if ($validar->fails()) { return ['success' => 0]; }

        $contrato = DB::table('contratos')->where('id', $request->id)->first();
        if (!$contrato) { return ['success' => 0]; }

        $detalle = DB::table('contratos_detalle as cd')
            ->join('distritos as d', 'd.id', '=', 'cd.id_distrito')
            ->join('tipocombustible as tc', 'tc.id', '=', 'cd.id_combustible')
            ->join('unidad_medida as um', 'um.id', '=', 'cd.id_unidad')
            ->where('cd.id_contratos', $contrato->id)
            ->select(
                'cd.id_distrito', 'cd.id_combustible', 'cd.codigo', 'cd.cantidad',
                'd.nombre as distrito',
                'tc.nombre as combustible',
                'um.nombre as unidad'
            // , 'cd.precio_unitario'   // <-- si agregás esta columna
            )
            ->get();

        $totalContratado = 0;
        $totalConsumido  = 0;
        $totalGastado    = 0;

        foreach ($detalle as $fila) {

            $consumo = DB::table('facturacion')
                ->where('id_distrito', $fila->id_distrito)
                ->where('id_tipocombustible', $fila->id_combustible)
                ->whereBetween('fecha', [$contrato->fecha_desde, $contrato->fecha_hasta])
                ->selectRaw('COALESCE(SUM(cantidad), 0) as galones, COALESCE(SUM(cantidad * unitario), 0) as dinero')
                ->first();

            $fila->consumido   = round($consumo->galones, 3);
            $fila->restante    = round($fila->cantidad - $consumo->galones, 3);
            $fila->gastado     = round($consumo->dinero, 2);
            $fila->porcentaje  = $fila->cantidad > 0
                ? round(($consumo->galones / $fila->cantidad) * 100, 2)
                : 0;

            // Si agregás precio_unitario al detalle:
            // $fila->dinero_contratado = round($fila->cantidad * $fila->precio_unitario, 2);
            // $fila->dinero_disponible = round($fila->dinero_contratado - $consumo->dinero, 2);

            $totalContratado += $fila->cantidad;
            $totalConsumido  += $consumo->galones;
            $totalGastado    += $consumo->dinero;
        }

        $hoy = Carbon::now('America/El_Salvador')->toDateString();

        return [
            'success' => 1,
            'contrato' => [
                'proveedor'      => $contrato->proveedor,
                'proceso_ref'    => $contrato->proceso_ref,
                'nombre_proceso' => $contrato->nombre_proceso,
                'orden_compra'   => $contrato->orden_compra,
                'fecha_desde'    => date('d-m-Y', strtotime($contrato->fecha_desde)),
                'fecha_hasta'    => date('d-m-Y', strtotime($contrato->fecha_hasta)),
                'vigente'        => ($hoy >= $contrato->fecha_desde && $hoy <= $contrato->fecha_hasta),
            ],
            'detalle' => $detalle,
            'totales' => [
                'contratado' => round($totalContratado, 3),
                'consumido'  => round($totalConsumido, 3),
                'restante'   => round($totalContratado - $totalConsumido, 3),
                'gastado'    => round($totalGastado, 2),
            ],
        ];
    }





}
