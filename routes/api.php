<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Th\HikvisionWebhookController;
use App\Http\Controllers\Api\Th\TalentoHumanoController;

// Webhook en tiempo real para terminales biométricos Hikvision (público / token en header si aplica)
Route::prefix('th/hikvision')->group(function () {
    Route::post('/event', [HikvisionWebhookController::class, 'receiveEvent']);
    Route::post('/events', [HikvisionWebhookController::class, 'receiveEvent']);
    Route::get('/event', [HikvisionWebhookController::class, 'receiveEvent']);
});

// Rutas de gestión de Talento Humano y Biometría
Route::prefix('th')->group(function () {
    Route::get('dashboard', [TalentoHumanoController::class, 'dashboard']);
    Route::get('empleados', [TalentoHumanoController::class, 'getEmpleados']);
    Route::get('empleados/exportar-plantilla-vms', [TalentoHumanoController::class, 'exportarPlantillaVms']);
    Route::post('empleados', [TalentoHumanoController::class, 'saveEmpleado']);
    Route::get('empleados/candidatos-usuarios', [TalentoHumanoController::class, 'getCandidatosUsuarios']);
    Route::post('empleados/importar-usuarios', [TalentoHumanoController::class, 'importarUsuariosSeleccionados']);
    
    // Horarios
    Route::get('horarios', [TalentoHumanoController::class, 'getHorarios']);
    Route::post('horarios', [TalentoHumanoController::class, 'saveHorario']);
    Route::delete('horarios/{id}', [TalentoHumanoController::class, 'deleteHorario']);
    
    // Planificación
    Route::get('planificacion', [TalentoHumanoController::class, 'getPlanificacion']);
    Route::post('planificacion', [TalentoHumanoController::class, 'savePlanificacion']);
    Route::post('planificacion/masiva', [TalentoHumanoController::class, 'savePlanificacionMasiva']);
    
    // Asistencias y Timbradas
    Route::get('asistencias', [TalentoHumanoController::class, 'getAsistencias']);
    Route::get('timbradas/{id}/foto', [TalentoHumanoController::class, 'getFotoTimbrada']);
    
    // Dispositivos Hikvision ISAPI
    Route::get('dispositivos', [TalentoHumanoController::class, 'getDispositivos']);
    Route::post('dispositivos', [TalentoHumanoController::class, 'saveDispositivo']);
    Route::delete('dispositivos/{id}', [TalentoHumanoController::class, 'deleteDispositivo']);
    Route::post('dispositivos/sync-timbradas/{id?}', [TalentoHumanoController::class, 'syncTimbradas']);
    Route::post('dispositivos/{dispositivoId}/subir-empleado/{empleadoId}', [TalentoHumanoController::class, 'uploadEmpleadoToDevice']);
    Route::post('dispositivos/{dispositivoId}/subir-todos', [TalentoHumanoController::class, 'uploadAllEmpleadosToDevice']);
    Route::post('dispositivos/{dispositivoId}/sync-biometria/{empleadoId}', [TalentoHumanoController::class, 'syncBiometriaFromDevice']);
    
    // Tipos de permiso y Solicitudes
    Route::get('tipos-permiso', [TalentoHumanoController::class, 'getTiposPermiso']);
    Route::post('tipos-permiso', [TalentoHumanoController::class, 'saveTipoPermiso']);
    Route::delete('tipos-permiso/{id}', [TalentoHumanoController::class, 'deleteTipoPermiso']);
    
    Route::get('solicitudes/gestion', [TalentoHumanoController::class, 'getSolicitudesGestion']);
    Route::post('solicitudes/permisos/{id}/resolver', [TalentoHumanoController::class, 'resolverSolicitudPermiso']);
    Route::post('solicitudes/vacaciones/{id}/resolver', [TalentoHumanoController::class, 'resolverSolicitudVacacion']);
    
    // Reportes
    Route::get('timbradas/recientes', [TalentoHumanoController::class, 'reporteTimbradas']);
    Route::get('reportes/timbradas', [TalentoHumanoController::class, 'reporteTimbradas']);
    Route::get('reportes/timbradas/exportar-xlsx', [TalentoHumanoController::class, 'exportarTimbradasXlsx']);
    Route::get('reportes/asistencias', [TalentoHumanoController::class, 'reporteAsistencias']);
    Route::get('reportes/asistencias/exportar-xlsx', [TalentoHumanoController::class, 'exportarAsistenciasXlsx']);
    Route::get('reportes/kardex', [TalentoHumanoController::class, 'reporteKardex']);
    Route::get('reportes/kardex/exportar-xlsx', [TalentoHumanoController::class, 'exportarKardexXlsx']);
});
