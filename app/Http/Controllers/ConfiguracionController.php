<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ConfiguracionController extends Controller
{
    /**
     * Muestra el panel interactivo de Configuración e Integración con Scriptcase
     */
    public function index()
    {
        $ssoSecret = env('SCRIPTCASE_SSO_SECRET', 'SEBLAN_TTHH_SECRET_KEY_2026');
        $dbName = env('DB_DATABASE', 'seblan_db');
        $dbHost = env('DB_HOST', '127.0.0.1');
        $dbPort = env('DB_PORT', '5432');
        $appUrl = env('APP_URL', 'http://localhost/seblan_biometricos/public');
        
        $webhookUrl = rtrim($appUrl, '/') . '/api/th/hikvision/event';
        $ssoUrl = rtrim($appUrl, '/') . '/auth/sso';

        // Estado de conexión a la BD
        $dbStatus = 'OFFLINE';
        $totalUsuarios = 0;
        $totalEmpresas = 0;
        $totalDispositivos = 0;

        try {
            DB::connection()->getPdo();
            $dbStatus = 'CONECTADO';
            $totalUsuarios = DB::table('sec_users')->count();
            $totalEmpresas = DB::table('th_empresas')->count();
            $totalDispositivos = DB::table('th_dispositivos')->count();
        } catch (\Exception $e) {
            $dbStatus = 'ERROR: ' . $e->getMessage();
        }

        return view('configuracion', compact(
            'ssoSecret',
            'dbName',
            'dbHost',
            'dbPort',
            'appUrl',
            'webhookUrl',
            'ssoUrl',
            'dbStatus',
            'totalUsuarios',
            'totalEmpresas',
            'totalDispositivos'
        ));
    }

    /**
     * Actualiza la clave secreta SSO dinámicamente en el archivo .env
     */
    public function updateSecret(Request $request)
    {
        $newSecret = trim($request->input('sso_secret'));
        if (empty($newSecret)) {
            return redirect()->back()->with('error', 'La clave secreta no puede estar vacía');
        }

        $envFile = base_path('.env');
        if (file_exists($envFile)) {
            $content = file_get_contents($envFile);
            if (strpos($content, 'SCRIPTCASE_SSO_SECRET=') !== false) {
                $content = preg_replace('/^SCRIPTCASE_SSO_SECRET=.*$/m', 'SCRIPTCASE_SSO_SECRET=' . $newSecret, $content);
            } else {
                $content .= "\nSCRIPTCASE_SSO_SECRET=" . $newSecret . "\n";
            }
            file_put_contents($envFile, $content);
        }

        return redirect()->back()->with('success', '¡Clave secreta SSO actualizada exitosamente!');
    }
}
