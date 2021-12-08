<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Frontend\Login\LoginController;
use App\Http\Controllers\Controles\ControlController;
use App\Http\Controllers\Admin\Perfil\PerfilController;
use App\Http\Controllers\Admin\Roles\PermisosController;
use App\Http\Controllers\Admin\Roles\RolesController;
use App\Http\Controllers\Admin\Equipo\EquipoController;
use App\Http\Controllers\Admin\Factura\RegistrarFacturaController;
use App\Http\Controllers\Admin\Factura\EditarFacturaController;
use App\Http\Controllers\Admin\Factura\ReportesController;

Route::get('/', [LoginController::class,'index'])->name('login');

Route::post('/login', [LoginController::class, 'login']);
Route::post('/logout', [LoginController::class, 'logout'])->name('admin.logout');

// --- CONTROL WEB ---
Route::get('/panel', [ControlController::class,'indexRedireccionamiento'])->name('admin.panel');

    // --- ROLES ---
    Route::get('/admin/roles/index', [RolesController::class,'index'])->name('admin.roles.index');
    Route::get('/admin/roles/tabla', [RolesController::class,'tablaRoles']);
    Route::get('/admin/roles/lista/permisos/{id}', [RolesController::class,'vistaPermisos']);
    Route::get('/admin/roles/permisos/tabla/{id}', [RolesController::class,'tablaRolesPermisos']);
    Route::post('/admin/roles/permiso/borrar', [RolesController::class, 'borrarPermiso']);
    Route::post('/admin/roles/permiso/agregar', [RolesController::class, 'agregarPermiso']);
    Route::get('/admin/roles/permisos/lista', [RolesController::class,'listaTodosPermisos']);
    Route::get('/admin/roles/permisos-todos/tabla', [RolesController::class,'tablaTodosPermisos']);
    Route::post('/admin/roles/borrar-global', [RolesController::class, 'borrarRolGlobal']);

    // --- PERMISOS ---
    Route::get('/admin/permisos/index', [PermisosController::class,'index'])->name('admin.permisos.index');
    Route::get('/admin/permisos/tabla', [PermisosController::class,'tablaUsuarios']);
    Route::post('/admin/permisos/nuevo-usuario', [PermisosController::class, 'nuevoUsuario']);
    Route::post('/admin/permisos/info-usuario', [PermisosController::class, 'infoUsuario']);
    Route::post('/admin/permisos/editar-usuario', [PermisosController::class, 'editarUsuario']);
    Route::post('/admin/permisos/nuevo-rol', [PermisosController::class, 'nuevoRol']);
    Route::post('/admin/permisos/extra-nuevo', [PermisosController::class, 'nuevoPermisoExtra']);
    Route::post('/admin/permisos/extra-borrar', [PermisosController::class, 'borrarPermisoGlobal']);

    // --- PERFIL ---
    Route::get('/admin/editar-perfil/index', [PerfilController::class,'indexEditarPerfil'])->name('admin.perfil');
    Route::post('/admin/editar-perfil/actualizar', [PerfilController::class, 'editarUsuario']);

    // --- VEHICULO ---
    Route::get('/admin/equipo/index', [EquipoController::class,'index'])->name('admin.nuevo.equipo.index');
    Route::get('/admin/equipo/tabla', [EquipoController::class,'tablaEquipo']);
    Route::post('/admin/equipo/nuevo', [EquipoController::class, 'nuevoEquipo']);
    Route::post('/admin/equipo/informacion', [EquipoController::class, 'informacionEquipo']);
    Route::post('/admin/equipo/editar', [EquipoController::class, 'editarEquipo']);

    // --- REGISTRAR FACTURA ---
    Route::get('/admin/factura/index', [RegistrarFacturaController::class,'index'])->name('admin.registrar.factura.index');
    Route::post('/admin/factura/nuevo', [RegistrarFacturaController::class, 'nuevaFactura']);

    // --- EDITAR FACTURA ---
    Route::get('/admin/factura/editar/index', [EditarFacturaController::class,'index'])->name('admin.factura.editar.index');
    Route::get('/admin/factura/editar/tabla', [EditarFacturaController::class,'tablaFactura']);
    Route::post('/admin/factura/borrar', [EditarFacturaController::class, 'borrarFactura']);
    Route::post('/admin/factura/informacion', [EditarFacturaController::class, 'infoFactura']);
    Route::post('/admin/factura/editar', [EditarFacturaController::class, 'editarFactura']);

    // --- REPORTE ---
    Route::get('/admin/factura/reporte/index', [ReportesController::class,'index'])->name('admin.factura.reporte.index');
    Route::get('/admin/factura/reporte-equipo/{fecha1}/{fecha2}/{equipo}', [ReportesController::class,'reporteEquipo']);

    // --- REPORTE ANTERIORES ---
    Route::get('/admin/factura/reporte-anterior/index', [ReportesController::class,'indexAnterior'])->name('admin.factura.reporte.anterior.index');
    Route::get('/admin/factura/reporte-anterior/{fecha1}/{fecha2}/{equipo}', [ReportesController::class,'reporteFacturaAnterior']);


    // --- SIN PERMISOS VISTA 403 ---
    Route::get('sin-permisos', [ControlController::class,'indexSinPermiso'])->name('no.permisos.index');


