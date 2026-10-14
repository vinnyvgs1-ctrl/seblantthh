<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

header('Content-Type: application/json');

$resultados = [];

// 1. Probar ThDispositivo::withCount('timbradas')
try {
    $r1 = \App\Models\ThDispositivo::withCount('timbradas')->get();
    $resultados['withCount_timbradas'] = 'OK (Conteo: ' . count($r1) . ')';
} catch (\Throwable $e) {
    $resultados['withCount_timbradas'] = 'ERROR: ' . $e->getMessage();
}

// 2. Probar ThTimbrada directamente
try {
    $r2 = \App\Models\ThTimbrada::count();
    $resultados['ThTimbrada_count'] = 'OK (' . $r2 . ' registros)';
} catch (\Throwable $e) {
    $resultados['ThTimbrada_count'] = 'ERROR: ' . $e->getMessage();
}

// 3. Probar llamar al controlador directamente tal como lo hace la ruta
try {
    $ctrl = new \App\Http\Controllers\Api\Th\TalentoHumanoController();
    $resCtrl = $ctrl->getDispositivos();
    $resultados['controller_getDispositivos'] = 'OK';
} catch (\Throwable $e) {
    $resultados['controller_getDispositivos'] = 'ERROR: ' . $e->getMessage() . ' en ' . $e->getFile() . ':' . $e->getLine();
}

echo json_encode($resultados, JSON_PRETTY_PRINT);
