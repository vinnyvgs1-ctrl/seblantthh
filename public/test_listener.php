<?php
/**
 * Test Listener en Vivo para Terminales Hikvision
 * Muestra en tiempo real cualquier paquete que el biométrico envíe al servidor
 */
error_reporting(E_ALL);
ini_set('display_errors', 1);

$logFile = __DIR__ . '/hikvision_live_captures.json';

// Si es una petición POST/PUT del biométrico o de prueba
if ($_SERVER['REQUEST_METHOD'] === 'POST' || $_SERVER['REQUEST_METHOD'] === 'PUT') {
    $raw = file_get_contents('php://input');
    $entry = [
        'timestamp' => date('Y-m-d H:i:s'),
        'ip_remota' => $_SERVER['REMOTE_ADDR'] ?? 'DESCONOCIDA',
        'content_type' => $_SERVER['CONTENT_TYPE'] ?? 'no_definido',
        'headers' => getallheaders(),
        'post' => $_POST,
        'files' => array_keys($_FILES),
        'raw_body_len' => strlen($raw),
        'raw_body_preview' => substr($raw, 0, 1500)
    ];

    $existentes = file_exists($logFile) ? json_decode(file_get_contents($logFile), true) : [];
    if (!is_array($existentes)) $existentes = [];
    array_unshift($existentes, $entry);
    $existentes = array_slice($existentes, 0, 20); // Guardar los últimos 20
    file_put_contents($logFile, json_encode($existentes, JSON_PRETTY_PRINT));

    header('Content-Type: application/json');
    echo json_encode(['status' => 'success', 'message' => 'Captura registrada con exito en SEBLAN']);
    exit;
}

// Si se abre desde el navegador por GET, muestra la consola visual en vivo
$capturas = file_exists($logFile) ? json_decode(file_get_contents($logFile), true) : [];
if (!is_array($capturas)) $capturas = [];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Inspector en Vivo • Webhook Hikvision SEBLAN</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <meta http-equiv="refresh" content="5">
</head>
<body class="bg-slate-900 text-slate-100 min-h-screen p-6 font-sans">
    <div class="max-w-5xl mx-auto space-y-6">
        <div class="flex items-center justify-between bg-slate-800 p-5 rounded-2xl border border-slate-700">
            <div>
                <h1 class="text-xl font-bold flex items-center gap-2">
                    <span class="w-3 h-3 rounded-full bg-emerald-500 animate-ping"></span>
                    Inspector de Tráfico Hikvision (En Vivo)
                </h1>
                <p class="text-xs text-slate-400 mt-1">
                    Esta pantalla se refresca automáticamente cada 5 segundos esperando que el terminal timbre.
                </p>
            </div>
            <div class="text-right text-xs">
                <span class="px-3 py-1.5 rounded-full bg-slate-700 font-mono text-emerald-400">
                    Total capturados: <?= count($capturas) ?>
                </span>
            </div>
        </div>

        <div class="space-y-4">
            <?php if (empty($capturas)): ?>
                <div class="p-12 text-center bg-slate-800/50 rounded-2xl border border-dashed border-slate-700 text-slate-400">
                    <i class="fas fa-radar text-4xl mb-3 text-slate-600 block animate-pulse"></i>
                    A la espera de la primera timbrada...<br>
                    <span class="text-xs text-slate-500">Párate frente al biométrico de SEBLAN o pon tu huella para ver el paquete HTTP llegar aquí.</span>
                </div>
            <?php else: ?>
                <?php foreach ($capturas as $i => $c): ?>
                    <div class="p-4 bg-slate-800 rounded-2xl border border-slate-700 space-y-2">
                        <div class="flex items-center justify-between text-xs">
                            <span class="font-bold text-emerald-400 font-mono">
                                <i class="fas fa-check-circle mr-1"></i> <?= $c['timestamp'] ?>
                            </span>
                            <span class="bg-slate-700 px-2 py-0.5 rounded font-mono text-slate-300">
                                IP Origen: <?= $c['ip_remota'] ?>
                            </span>
                        </div>
                        <div class="text-xs font-mono text-slate-300 bg-slate-900 p-3 rounded-xl overflow-x-auto max-h-48">
                            <pre><?= htmlspecialchars($c['raw_body_preview']) ?></pre>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
