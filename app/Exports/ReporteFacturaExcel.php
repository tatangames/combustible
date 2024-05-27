<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
class ReporteFacturaExcel implements FromView
{


    public function __construct($listado, $totalLinea, $totalRegular, $totalDiesel,
                                $totalEspecial, $totalGalonRegular, $totalGalonDiesel, $totalGalonEspecial)
    {
        $this->listado = $listado;
        $this->totalLinea = $totalLinea;
        $this->totalRegular = $totalRegular;
        $this->totalDiesel = $totalDiesel;
        $this->totalEspecial = $totalEspecial;
        $this->totalGalonRegular = $totalGalonRegular;
        $this->totalGalonDiesel = $totalGalonDiesel;
        $this->totalGalonEspecial = $totalGalonEspecial;
    }

    public function view(): View{

        return view('backend.admin.configuracion.excel.vistareporteexcel', [
            'listado' => $this->listado,
            'totalLinea' => $this->totalLinea,
            'totalRegular' => $this->totalRegular,
            'totalDiesel' => $this->totalDiesel,
            'totalEspecial' => $this->totalEspecial,
            'totalGalonRegular' => $this->totalGalonRegular,
            'totalGalonDiesel' => $this->totalGalonDiesel,
            'totalGalonEspecial' => $this->totalGalonEspecial,
        ]);
    }
}
