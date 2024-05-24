<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Frontend\Login\LoginController;
use App\Http\Controllers\Controles\ControlController;
use App\Http\Controllers\Admin\Perfil\PerfilController;
use App\Http\Controllers\Admin\Roles\PermisoController;
use App\Http\Controllers\Admin\Roles\RolesController;
use App\Http\Controllers\Admin\Factura\FacturaController;
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
    Route::get('/admin/permisos/index', [PermisoController::class,'index'])->name('admin.permisos.index');
    Route::get('/admin/permisos/tabla', [PermisoController::class,'tablaUsuarios']);
    Route::post('/admin/permisos/nuevo-usuario', [PermisoController::class, 'nuevoUsuario']);
    Route::post('/admin/permisos/info-usuario', [PermisoController::class, 'infoUsuario']);
    Route::post('/admin/permisos/editar-usuario', [PermisoController::class, 'editarUsuario']);
    Route::post('/admin/permisos/nuevo-rol', [PermisoController::class, 'nuevoRol']);
    Route::post('/admin/permisos/extra-nuevo', [PermisoController::class, 'nuevoPermisoExtra']);
    Route::post('/admin/permisos/extra-borrar', [PermisoController::class, 'borrarPermisoGlobal']);

    // --- PERFIL ---
    Route::get('/admin/editar-perfil/index', [PerfilController::class,'indexEditarPerfil'])->name('admin.perfil');
    Route::post('/admin/editar-perfil/actualizar', [PerfilController::class, 'editarUsuario']);



    // FACTURA
    Route::get('/admin/factura/index', [FacturaController::class,'indexFactura'])->name('admin.factura.index');
    Route::get('/admin/factura/tabla', [FacturaController::class, 'tablaFactura']);
    Route::get('/admin/factura/tabla/tipo/{valor}', [FacturaController::class, 'tablaFacturaTodos']);
    Route::post('/admin/factura/informacion', [FacturaController::class, 'informacionFactura']);
    Route::post('/admin/factura/actualizar', [FacturaController::class, 'actualizarFactura']);
    Route::post('/admin/factura/borrar', [FacturaController::class, 'borrarFactura']);


    // REGISTRO NUEVA FACTURA
    Route::get('/admin/nuevafactura/index', [FacturaController::class,'indexNuevaFactura'])->name('admin.nuevafactura.index');
    Route::post('/admin/nuevafactura/nuevo', [FacturaController::class,'nuevaFactura']);

    // CAMBIO DE NOMBRE PARA REPORTES
    Route::get('/admin/cambio/nombres/index', [ReportesController::class,'indexCambioNombre'])->name('admin.nombres.index');
    Route::post('/admin/cambio/nombres/actualizar', [ReportesController::class,'actualizarBloqueNombre']);


    // REPORTE - POR FECHAS
    Route::get('/admin/reportev1/fechas/index', [ReportesController::class,'vistaReporteFechas'])->name('admin.reporte.fechas.index');
    Route::get('/admin/reportev1/generar/fecha/{desde}/{hasta}', [ReportesController::class,'reporteFacturaFecha']);

    // REPORTE - POR EQUIPOS
    Route::get('/admin/reportev1/equipos/index', [ReportesController::class,'vistaReporteEquipos'])->name('admin.reporte.equipos.index');
    Route::get('/admin/reportev1/generar/equipos/{desde}/{hasta}/{texto}', [ReportesController::class,'reporteFacturaEquipos']);

