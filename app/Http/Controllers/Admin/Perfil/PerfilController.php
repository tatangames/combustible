<?php

namespace App\Http\Controllers\Admin\Perfil;

use App\Http\Controllers\Controller;
use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class PerfilController extends Controller
{
    public function __construct(){
        $this->middleware('auth');
    }

    public function indexEditarPerfil(){
        $usuario = auth()->user();
        return view('backend.admin.perfil.index', compact('usuario'));
    }

    public function editarUsuario(Request $request){

        $regla = array(
            'password' => 'required',
        );

        $validar = Validator::make($request->all(), $regla);

        if ($validar->fails()){return ['success' => 0];}

        $usuario = auth()->user();

        Usuario::where('id', $usuario->id)
            ->update(['password' => bcrypt($request->password)]);

        return ['success' => 1];
    }
}
