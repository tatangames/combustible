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
        $validar = Validator::make($request->all(), ['id' => 'required']);
        if ($validar->fails()) { return ['success' => 0]; }

        $contrato = DB::table('contratos')->where('id', $request->id)->first();
        if (!$contrato) { return ['success' => 0]; }

        /* ==== Fechas: intersección contrato ∩ rango solicitado ==== */
        $inicioContrato = Carbon::parse($contrato->fecha_desde)->startOfDay();
        $finContrato    = Carbon::parse($contrato->fecha_hasta)->endOfDay();

        $fechaDesde = $request->desde ? Carbon::parse($request->desde)->startOfDay() : $inicioContrato;
        $fechaHasta = $request->hasta ? Carbon::parse($request->hasta)->endOfDay()   : $finContrato;

        $rangoIni = $inicioContrato->greaterThan($fechaDesde) ? $inicioContrato : $fechaDesde;
        $rangoFin = $finContrato->lessThan($fechaHasta) ? $finContrato : $fechaHasta;

        /* ==== Contratado por combustible (todos los distritos del contrato) ==== */
        $detalle = DB::table('contratos_detalle as cd')
            ->join('tipocombustible as tc', 'tc.id', '=', 'cd.id_combustible')
            ->join('unidad_medida as um', 'um.id', '=', 'cd.id_unidad')
            ->where('cd.id_contratos', $contrato->id)
            ->groupBy('cd.id_combustible', 'tc.nombre', 'um.nombre')
            ->selectRaw('cd.id_combustible, tc.nombre as combustible, um.nombre as unidad, SUM(cd.cantidad) as contratado')
            ->orderBy('cd.id_combustible')
            ->get();

        /* ==== Consumo del PERÍODO por combustible ==== */
        $periodo = DB::table('facturacion')
            ->whereNotNull('id_distrito')            // igual que el acta: solo facturas con distrito
            ->whereBetween('fecha', [$rangoIni, $rangoFin])
            ->groupBy('id_tipocombustible')
            ->selectRaw('id_tipocombustible,
                     SUM(cantidad) as galones,
                     CAST(SUM(ROUND(cantidad * unitario, 2)) AS DECIMAL(18,2)) as dinero')
            ->get()->keyBy('id_tipocombustible');

        /* ==== Consumo ACUMULADO (inicio contrato -> fin del rango) ==== */
        $acumulado = DB::table('facturacion')
            ->whereNotNull('id_distrito')
            ->whereBetween('fecha', [$inicioContrato, $rangoFin])
            ->groupBy('id_tipocombustible')
            ->selectRaw('id_tipocombustible,
                     SUM(cantidad) as galones,
                     CAST(SUM(ROUND(cantidad * unitario, 2)) AS DECIMAL(18,2)) as dinero')
            ->get()->keyBy('id_tipocombustible');

        $tot = ['contratado' => 0, 'periodo' => 0, 'dinero_periodo' => 0,
            'acumulado' => 0, 'dinero_acumulado' => 0, 'restante' => 0];

        foreach ($detalle as $f) {
            $p = $periodo[$f->id_combustible]   ?? null;
            $a = $acumulado[$f->id_combustible] ?? null;

            $f->contratado       = (float) $f->contratado;
            $f->consumo_periodo  = (float) ($p->galones ?? 0);
            $f->dinero_periodo   = (float) ($p->dinero  ?? 0);
            $f->consumo_acum     = (float) ($a->galones ?? 0);
            $f->dinero_acum      = (float) ($a->dinero  ?? 0);
            $f->restante         = $f->contratado - $f->consumo_acum;   // puede ser negativo (se pasó)
            $f->porcentaje       = $f->contratado > 0
                ? round(($f->consumo_acum / $f->contratado) * 100, 2) : 0;

            $tot['contratado']       += $f->contratado;
            $tot['periodo']          += $f->consumo_periodo;
            $tot['dinero_periodo']   += $f->dinero_periodo;
            $tot['acumulado']        += $f->consumo_acum;
            $tot['dinero_acumulado'] += $f->dinero_acum;
            $tot['restante']         += $f->restante;
        }

        $hoy = Carbon::now('America/El_Salvador')->toDateString();

        return [
            'success'  => 1,
            'contrato' => [
                'proveedor'      => $contrato->proveedor,
                'proceso_ref'    => $contrato->proceso_ref,
                'nombre_proceso' => $contrato->nombre_proceso,
                'orden_compra'   => $contrato->orden_compra,
                'fecha_desde'    => $inicioContrato->format('d-m-Y'),
                'fecha_hasta'    => $finContrato->format('d-m-Y'),
                'rango_ini'      => $rangoIni->format('d-m-Y'),
                'rango_fin'      => $rangoFin->format('d-m-Y'),
                'vigente'        => ($hoy >= $contrato->fecha_desde && $hoy <= $contrato->fecha_hasta),
            ],
            'detalle' => $detalle,
            'totales' => $tot,
        ];
    }





}
