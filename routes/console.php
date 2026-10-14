<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('sir:print-url {sirKey}', function (string $sirKey) {
    $config = DB::table('uc_api_config')
        ->where('sir_name', $sirKey)
        ->orWhere('api_code', $sirKey)
        ->first();

    if (!$config) {
        $this->error("No existe configuración para '$sirKey' en uc_api_config.");
        return 1;
    }

    $sir = app(\App\Services\SirService::class);
    $ref = new ReflectionMethod($sir, 'resolveBaseUrl');
    $ref->setAccessible(true);
    $baseUrl = $ref->invoke($sir, $config);

    $this->line("api_code:   {$config->api_code}");
    $this->line("endpoint:   {$config->endpoint}");
    $this->line("base url:   " . ($baseUrl ?: '(vacío/no configurado)'));
    $this->info("URL final:  " . $baseUrl . $config->endpoint);
})->purpose('Simula la resolución de URL final para una clave de uc_api_config (SIR)');

use Illuminate\Support\Facades\Schedule;

Schedule::command('sir:sync-incremental')->everyMinute()->withoutOverlapping()->runInBackground();

Artisan::command('sir:cleanup-temp', function () {
    $this->info("Limpiando archivos temporales del SIR...");
    $files = glob(storage_path('app/sir_full_*')) ?: [];
    $dirs = glob(storage_path('app/sir_extract_*'), GLOB_ONLYDIR) ?: [];
    
    $now = time();
    $deleted = 0;
    
    foreach ($files as $file) {
        if (is_file($file) && ($now - filemtime($file)) >= 86400) {
            @unlink($file);
            $deleted++;
        }
    }
    
    foreach ($dirs as $dir) {
        if (($now - filemtime($dir)) >= 86400) {
            \Illuminate\Support\Facades\File::deleteDirectory($dir);
            $deleted++;
        }
    }
    $this->info("Se eliminaron $deleted archivos/carpetas.");
})->purpose('Limpia archivos ZIP y carpetas temporales de recargas fallidas o canceladas');

Schedule::command('sir:cleanup-temp')->daily()->withoutOverlapping()->runInBackground();

Schedule::command('geofences:monitor')->everyFiveMinutes()->withoutOverlapping()->runInBackground();

// Auditoría periódica de Hardware OMNI vs BD Local (Anclajes y Bicis) - Solo auditoría pasiva sin alteraciones automáticas
Schedule::command('omni:sync-hardware')->everyMinute()->withoutOverlapping()->runInBackground();
Schedule::command('omni:sync-bikes')->everyMinute()->withoutOverlapping()->runInBackground();
Schedule::command('iot:check-connectivity')->everyFiveMinutes()->withoutOverlapping()->runInBackground();
Schedule::command('vpn:check')->everyMinute()->withoutOverlapping()->runInBackground();

Schedule::command('images:optimize-daily')->dailyAt('22:00')->withoutOverlapping()->runInBackground();

Artisan::command('debug:alex', function () {
    $userId = 6323; // Alex Moreno
    $user = DB::table('uc_users')->where('id', $userId)->first();
    $this->info("User Active: " . $user->user_service_active);
    
    $viajes = DB::table('uc_despachos')
        ->where('user_id', $userId)
        ->where(function($query) {
            $query->whereNull('hora_entrada')
                  ->orWhere('hora_entrada', '')
                  ->orWhere('hora_entrada', '00:00:00');
        })
        ->get();
        
    $this->info("Open trips count: " . $viajes->count());
    foreach ($viajes as $v) {
        $this->info("Trip ID: {$v->id}, Salida: {$v->hora_salida}, Entrada: '{$v->hora_entrada}'");
    }
});
Schedule::command('app:archive-iot-webhooks')->dailyAt('03:00')->withoutOverlapping()->runInBackground();
Schedule::command('app:check-offline-docks')->everyTenMinutes()->runInBackground();
Schedule::command('mantenimiento:generar-alertas')->daily()->withoutOverlapping()->runInBackground();

// La red de seguridad del balanceo: un movimiento abierto cuya bici no esta a bordo. Lo normal lo
// cierra el webhook al ver la bici anclada; esto es para lo que se escape. Ver el comando.
Schedule::command('balanceo:revisar-movimientos-abiertos')->dailyAt('06:30')->withoutOverlapping()->runInBackground();
Schedule::command('app:check-active-trips')->everyMinute()->withoutOverlapping()->runInBackground();
Schedule::command('sanciones:check-expiradas')->everyMinute()->withoutOverlapping()->runInBackground();
// Schedule::command('iot:close-stuck-trips')->hourly()->withoutOverlapping()->runInBackground();
// Schedule::command('iot:auto-close-proximity-trips')->everyFiveMinutes();

// Sincronización en segundo plano para usuarios de emergencia que no obtuvieron su nombre del SIR
Schedule::command('quitobici:sync-names')->everyTenMinutes()->between('6:00', '19:00')->withoutOverlapping()->runInBackground();

// Respaldo diario de base de datos a las 2 AM
Schedule::command('db:backup')->dailyAt('02:00')->withoutOverlapping()->runInBackground();

// Snapshot diario de indicadores operativos del contrato, a las 20:05: tras el cierre del
// servicio (20:00) para que el dia quede completo. La pestana ademas refresca el dia en curso
// al abrirse, asi que este cron es el que consolida el HISTORICO.
// appendOutputTo: con runInBackground() la salida del comando se pierde, y sin ella la unica
// forma de saber si el cron corrio es mirar updated_at de las filas. Un fichero sin fecha, para
// que el historico completo se lea de un vistazo; una linea al dia no justifica rotarlo.
Schedule::command('metrics:daily-snapshot')->dailyAt('20:05')->withoutOverlapping()->runInBackground()
    ->appendOutputTo(storage_path('logs/metrics-snapshot.log'));

// Refresco diario de la vista materializada de Errores de Anclaje a la 01:30 AM (madrugada)
Schedule::command('reportes:refresh-errores-anclaje')->dailyAt('01:30')->withoutOverlapping()->runInBackground();

