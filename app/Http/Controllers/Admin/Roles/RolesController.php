<?php

namespace App\Http\Controllers\Admin\Roles;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolesController extends Controller
{
    public function __construct(){
        $this->middleware('auth');
    }

    public function index(){
        return view('backend.admin.roles.index');
    }

    public function tablaRoles(){
        $roles = Role::all()->pluck('name', 'id');
        return view('backend.admin.roles.tabla.tablaroles', compact('roles'));
    }

    public function vistaPermisos($id){

        // obtener todos los permisos que existen
        $permisos = Permission::all()->pluck('name', 'id');

        return view('backend.admin.roles.roles-permisos.index', compact('id', 'permisos'));
    }

    public function tablaRolesPermisos($id){
        // se recibe el id del Rol, para buscar los permisos agregados a este Rol.

        // lista de permisos asignados al Rol
        $permisos = Role::findById($id)->permissions()->pluck('name', 'id');

        // lista de todos los permisos que existen
        //$permisos = Permission::pluck('name', 'id');

        return view('backend.admin.roles.roles-permisos.tabla.tabla-rolepermisos', compact('permisos'));
    }

    public function borrarPermiso(Request $request){

        // buscamos el Permiso por su ID
        $permission = Permission::findById($request->idpermiso);

        // buscamos el Rol a cual le quitaremos el permiso
        $role = Role::findById($request->idrol);

        // quitamos el permiso al Rol
        $role->revokePermissionTo($permission);

        return ['success' => 1];
    }

    public function agregarPermiso(Request $request){

        // buscamos el Rol a cual le queremos agregar el permiso
        $role = Role::findById($request->idrol);

        // buscamos el permiso el cual queremos agregar
        $permission = Permission::findById($request->idpermiso);

        // asignamos el permiso al Rol
        $role->givePermissionTo($permission);

        return ['success' => 1];
    }

    public function listaTodosPermisos(){
        return view('backend.admin.roles.indexlista-permisos');
    }

    public function tablaTodosPermisos(){

        $permisos = Permission::all();
        return view('backend.admin.roles.tabla.tablaindexlista-permiso', compact('permisos'));
    }

    public function borrarRolGlobal(Request $request){

        // buscar el rol por id
        $role = Role::findById($request->idrol);

        // elimina el rol y todos los permisos asociados
        $role->delete();

        return ['success' => 1];
    }
}
