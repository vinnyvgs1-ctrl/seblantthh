<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$recentWebhooks = DB::table('uc_iot_webhooks')
    ->where('created_at', '>=', now()->subHours(12))
    ->orderBy('id', 'asc')
    ->get();

$l5s = [];
$unlocks = [];

foreach ($recentWebhooks as $w) {
    if ($w->cmd === 'L5') { // Asuming L5 is unlock command
        $l5s[] = $w;
    }
    // Check if state is unlocked. Usually D0 or S0 contains status.
    // If it's a dock, maybe it has a different format. 
    // The user says "el estado de un anclaje cambió a desbloqueado/liberado"
    if (stripos($w->raw_payload, 'unlock') !== false || stripos($w->raw_payload, '"lock":0') !== false || stripos($w->raw_payload, '"status":0') !== false || stripos($w->raw_payload, 'liberado') !== false) {
        $unlocks[] = $w;
    }
}

echo count($l5s) . " comandos L5 enviados en las ultimas 12 horas.\n";
foreach ($l5s as $l) {
    echo "L5 - IMEI: {$l->imei} at {$l->created_at} Payload: {$l->raw_payload}\n";
}

echo "\n" . count($unlocks) . " reportes de estado 'desbloqueado'.\n";
foreach ($unlocks as $u) {
    echo "Reporte - IMEI: {$u->imei} at {$u->created_at} Cmd: {$u->cmd} Payload: " . substr($u->raw_payload, 0, 150) . "\n";
}
