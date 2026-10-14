<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckScriptcaseAuth
{
    /**
     * Valida que el usuario tenga sesión activa validada vía SSO de Scriptcase
     */
    public function handle(Request $request, Closure $next)
    {
        // 1. Si ya tiene sesión activa en Laravel o sesión de Scriptcase
        if (Auth::check() || $request->session()->has('scriptcase_user')) {
            return $next($request);
        }

        // 2. Si no tiene sesión, responder según el tipo de petición
        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json([
                'error' => 'No autorizado. Debe iniciar sesión desde el CRM de SEBLAN (Scriptcase).'
            ], 401);
        }

        return response()->view('errors.no_autorizado', [], 403);
    }
}
