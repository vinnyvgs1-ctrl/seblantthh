<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return response()->json([
        'module' => 'SEBLAN Talento Humano & Biometria Hikvision',
        'status' => 'ONLINE',
        'version' => '1.0.0'
    ]);
});

// Ruta de Single Sign-On desde Scriptcase (pública, valida HMAC)
Route::get('/auth/sso', [\App\Http\Controllers\Api\AuthController::class, 'ssoLogin']);
Route::get('/auth/logout', [\App\Http\Controllers\Api\AuthController::class, 'logout']);

// Panel visual de integración y configuración con Scriptcase
Route::get('/configuracion', [\App\Http\Controllers\ConfiguracionController::class, 'index']);
Route::post('/configuracion/sso-secret', [\App\Http\Controllers\ConfiguracionController::class, 'updateSecret']);

// Rutas protegidas que exigen haber ingresado desde Scriptcase
Route::middleware(['scriptcase.auth'])->group(function () {
    // Vista Principal de Administración de Talento Humano & Biometría
    Route::get('/tthh', function () {
        return view('admin_tthh');
    });
});
