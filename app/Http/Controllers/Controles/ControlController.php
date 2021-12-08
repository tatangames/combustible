<?php

namespace App\Http\Controllers\Controles;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ControlController extends Controller
{
    public function __construct(){
        $this->middleware('auth');
    }

    public function indexRedireccionamiento(){

        $user = Auth::user();

        // $permiso = $user->getAllPermissions()->pluck('name');

        // rol 1: Encargado-Administrador
        if($user->hasPermissionTo('vista.roles.index')){
            $ruta = 'admin.roles.index';
        }

        // rol 2: Encargado-Factura
        else if($user->hasPermissionTo('vista.factura.index')){
            $ruta = 'admin.registrar.factura.index';
        }

        // rol 3: Encargado-Reporte
        else if($user->hasPermissionTo('vista.reporte.index')){
            $ruta = 'admin.factura.reporte.index';
        }

        else{
            // no tiene ningun permiso de vista, redirigir a pantalla sin permisos
            $ruta = 'no.permisos.index';
        }

        return view('backend.index', compact( 'ruta', 'user'));
    }

    public function indexSinPermiso(){
        return view('errors.403');
    }

}
