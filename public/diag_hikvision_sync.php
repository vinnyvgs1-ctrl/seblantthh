<?php
/**
 * Diagnóstico directo de conexión ISAPI con el terminal Hikvision de SEBLAN
 */
error_reporting(E_ALL);
ini_set('display_errors', 1);

require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

header('Content-Type: application/json');

$dispositivos = \App\Models\ThDispositivo::all();

if ($dispositivos->isEmpty()) {
    echo json_encode([
        'status' => 'ERROR',
        'message' => 'No hay dispositivos en la tabla th_dispositivos'
    ], JSON_PRETTY_PRINT);
    exit;
}

$resultados = [];

foreach ($dispositivos as $disp) {
    $item = [
        'id' => $disp->id,
        'nombre' => $disp->nombre,
        'ip_wan' => $disp->ip,
        'puerto_wan' => $disp->puerto,
        'ip_lan' => $disp->ip_lan ?: $disp->ip_local,
        'usuario' => $disp->usuario ?: $disp->usuario_api,
        'tiene_password' => !empty($disp->password),
        'pruebas' => []
    ];

    $hosts = array_filter([
        'LAN' => $disp->ip_lan ?: $disp->ip_local,
        'WAN' => $disp->ip,
    ]);

    $user = $disp->usuario ?: ($disp->usuario_api ?: 'admin');
    $pass = $disp->password ?: '';

    foreach ($hosts as $tipo => $host) {
        $puerto = $disp->puerto ?: 80;
        $urlStatus = "http://{$host}:{$puerto}/ISAPI/System/status";
        $urlEvents = "http://{$host}:{$puerto}/ISAPI/AccessControl/AcsEvent?format=json";

        // Test 1: Conectividad Socket
        $errno = 0;
        $errstr = '';
        $t0 = microtime(true);
        $fp = @fsockopen($host, $puerto, $errno, $errstr, 2.5);
        $tConn = round((microtime(true) - $t0) * 1000, 2);

        if (!$fp) {
            $item['pruebas'][$tipo] = [
                'socket' => "FALLO: $errstr ($errno) tras {$tConn}ms",
                'isapi' => 'Omitido por fallo de socket'
            ];
            continue;
        }
        fclose($fp);

        // Test 2: Petición ISAPI de eventos
        try {
            $payload = [
                "AcsEventSearchDescription" => [
                    "searchID" => "1",
                    "searchResultPosition" => 0,
                    "maxResults" => 30,
                    "AcsEventCond" => [
                        "searchSymbol" => "",
                        "timeReverseOrder" => true,
                    ]
                ]
            ];

            $res = \Illuminate\Support\Facades\Http::withDigestAuth($user, $pass)
                ->timeout(5)
                ->post($urlEvents, $payload);

            $body = $res->json();
            $numEventos = $body['AcsEvent']['totalMatches'] ?? ($body['AcsEvent']['numOfMatches'] ?? count($body['AcsEvent']['InfoList'] ?? []));

            $item['pruebas'][$tipo] = [
                'socket' => "OK ({$tConn}ms)",
                'http_status' => $res->status(),
                'eventos_encontrados' => $numEventos,
                'ejemplo_eventos' => array_slice($body['AcsEvent']['InfoList'] ?? [], 0, 3)
            ];

        } catch (\Throwable $e) {
            $item['pruebas'][$tipo] = [
                'socket' => "OK ({$tConn}ms)",
                'error_isapi' => $e->getMessage()
            ];
        }
    }

    $resultados[] = $item;
}

echo json_encode($resultados, JSON_PRETTY_PRINT);
