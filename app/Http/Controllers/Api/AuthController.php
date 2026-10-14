<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    /**
     * Login mediante SSO desde Scriptcase
     * Scriptcase envía: ?user=LOGIN&t=TIMESTAMP&token=HASH_HMAC
     */
    public function ssoLogin(Request $request)
    {
        $userLogin = $request->query('user');
        $time = (int) $request->query('t');
        $token = $request->query('token');

        if (!$userLogin || !$time || !$token) {
            return response()->view('errors.no_autorizado', [
                'mensaje' => 'Parámetros SSO inválidos o incompletos. Debe ingresar exclusivamente a través del menú de SEBLAN CRM.'
            ], 400);
        }

        // Validar vigencia de la firma (máximo 120 segundos)
        if (abs(time() - $time) > 120) {
            return response()->view('errors.no_autorizado', [
                'mensaje' => 'El enlace de acceso ha expirado por seguridad (superó los 120 segundos). Vuelva a hacer clic en el menú del CRM.'
            ], 401);
        }

        // Validar HMAC
        $secretKey = env('SCRIPTCASE_SSO_SECRET', 'SEBLAN_TTHH_SECRET_KEY_2026');
        $expectedToken = hash_hmac('sha256', $userLogin . '|' . $time, $secretKey);

        if (!hash_equals($expectedToken, $token)) {
            return response()->view('errors.no_autorizado', [
                'mensaje' => 'Firma digital de autenticación inválida o clave no sincronizada.'
            ], 403);
        }

        // Buscar el usuario en sec_users de Scriptcase
        $user = User::where('login', $userLogin)->first();
        if (!$user || !$user->isActive()) {
            return response()->view('errors.no_autorizado', [
                'mensaje' => 'Usuario no encontrado o inactivo en la base de datos de Scriptcase.'
            ], 404);
        }

        // Iniciar sesión en Laravel
        Auth::login($user);
        $request->session()->regenerate();
        $request->session()->put('scriptcase_user', [
            'login' => $user->login,
            'name' => $user->name,
            'email' => $user->email,
            'priv_admin' => $user->priv_admin,
            'role' => $user->role,
        ]);

        return redirect()->to('/tthh');
    }

    /**
     * Cierre de sesión
     */
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->to('/tthh');
    }
}
