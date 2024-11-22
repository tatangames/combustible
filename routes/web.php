<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Frontend\Login\LoginController;
use App\Http\Controllers\Controles\ControlController;
use App\Http\Controllers\Admin\Perfil\PerfilController;
use App\Http\Controllers\Admin\Roles\PermisoController;
use App\Http\Controllers\Admin\Roles\RolesController;
use App\Http\Controllers\Admin\Factura\FacturaController;
use App\Http\Controllers\Admin\Factura\ReportesController;
use App\Http\Controllers\Admin\Equipo\EquipoController;
use App\Http\Controllers\Admin\FacturaV2\FacturaV2Controller;
use App\Http\Controllers\Admin\FacturaV2\ReporteV2Controller;
use App\Http\Controllers\Admin\Fondos\FondosController;


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

    // --- SIN PERMISOS VISTA 403 ---
    Route::get('sin-permisos', [ControlController::class,'indexSinPermiso'])->name('no.permisos.index');

    // --- PERFIL ---
    Route::get('/admin/editar-perfil/index', [PerfilController::class,'indexEditarPerfil'])->name('admin.perfil');
    Route::post('/admin/editar-perfil/actualizar', [PerfilController::class, 'editarUsuario']);





    // EQUIPO
    Route::get('/admin/equipo/index', [EquipoController::class,'indexEquipo'])->name('admin.equipos.index');
    Route::get('/admin/equipo/tabla', [EquipoController::class, 'tablaEquipo']);
    Route::post('/admin/equipo/nuevo', [EquipoController::class, 'nuevoEquipo']);
    Route::post('/admin/equipo/informacion', [EquipoController::class, 'informacionEquipo']);
    Route::post('/admin/equipo/editar', [EquipoController::class, 'actualizarEquipo']);


    // REGISTRAR FACTURACION
    Route::get('/admin/facturav2/index', [FacturaV2Controller::class,'indexFactura'])->name('admin.facturav2.index');
    Route::post('/admin/facturav2/nuevo', [FacturaV2Controller::class, 'nuevoFactura']);


    // TABLA FACTURACION
    Route::get('/admin/facturav2/listado/index', [FacturaV2Controller::class,'indexFacturacion'])->name('admin.facturav2.listado.index');
    Route::get('/admin/facturav2/listado/tabla', [FacturaV2Controller::class, 'tablaFacturacionTabla']);
    Route::get('/admin/facturav2/listado/tabla/{idfiltro}', [FacturaV2Controller::class, 'tablaFacturacionTablaFiltro']);

    Route::post('/admin/facturav2/informacion', [FacturaV2Controller::class, 'informacionFactura']);
    Route::post('/admin/facturav2/actualizar', [FacturaV2Controller::class, 'actualizarFactura']);
    Route::post('/admin/facturav2/borrar', [FacturaV2Controller::class, 'borrarFactura']);


    // REPORTES EQUIPOS
    //Para consolidado
    Route::get('/admin/reportev2/consolidado/index', [ReporteV2Controller::class,'vistaReporteConsolidado'])->name('admin.reporte.equipos.consolidado');
    Route::get('/admin/reportev2/generar/equipos/consolidado/{desde}/{hasta}/{equipo}/{distrito}/{fondo}', [ReporteV2Controller::class,'reporteEquipoConsolidado']);
    
    //Para no consolidado
    Route::get('/admin/reportev2/fechas/index', [ReporteV2Controller::class,'vistaReporteFechas'])->name('admin.reporte.facturacion.equipos');
    Route::get('/admin/reportev2/generar/equipos/{desde}/{hasta}/{equipo}/{distrito}/{fondo}', [ReporteV2Controller::class,'reporteEquipoFechaPDF']);

    // REPORTE FACTURA
    Route::get('/admin/reportev2/factura/index', [ReporteV2Controller::class,'vistaReporteFactura'])->name('admin.reporte.facturacion.factura');
    Route::get('/admin/reportev2/pdf/factura/{numfactura}/{distrito}/{fondo}', [ReporteV2Controller::class,'reporteFacturaPDF']);


    //******************** PARTE DEL SISTEMA ANTERIOR ***************************************


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
    Route::get('/admin/reportev1/generar/fecha/{desde}/{hasta}/{factura}', [ReportesController::class,'reporteFacturaFecha']);

    // REPORTE - POR EQUIPOS
    Route::get('/admin/reportev1/equipos/index', [ReportesController::class,'vistaReporteEquipos'])->name('admin.reporte.equipos.index');
    Route::get('/admin/reportev1/generar/equipos/{desde}/{hasta}/{texto}', [ReportesController::class,'reporteFacturaEquipos']);


    //***************************************************************

    // TIPO DE FONDOS
    Route::get('/admin/fondos/index', [FondosController::class,'indexTipoFondos'])->name('admin.fondos.index');
    Route::get('/admin/fondos/tabla', [FondosController::class, 'tablaTipoFondos']);
    Route::post('/admin/fondos/nuevo', [FondosController::class, 'nuevoTipoFondos']);
    Route::post('/admin/fondos/informacion', [FondosController::class, 'informacionTipoFondos']);
    Route::post('/admin/fondos/editar', [FondosController::class, 'actualizarTipoFondos']);

    // DISTRITO
    Route::get('/admin/distrito/index', [FondosController::class,'indexDistritos'])->name('admin.distritos.index');
    Route::get('/admin/distrito/tabla', [FondosController::class, 'tablaDistritos']);
    Route::post('/admin/distrito/nuevo', [FondosController::class, 'nuevoDistritos']);
    Route::post('/admin/distrito/informacion', [FondosController::class, 'informacionDistritos']);
    Route::post('/admin/distrito/editar', [FondosController::class, 'actualizarDistritos']);


