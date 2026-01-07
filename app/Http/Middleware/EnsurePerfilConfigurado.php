<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsurePerfilConfigurado
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        // Si no hay usuario autenticado, normal
        if (!$user) {
            return $next($request);
        }

        // Rutas que NO deben redirigir (para evitar loop)
        if ($request->routeIs('perfil.inicial*') || $request->routeIs('logout')) {
            return $next($request);
        }

        // Si perfil NO está configurado -> mandarlo al formulario inicial
        if (!$user->flag_perfil_configurado) {
            return redirect()->route('perfil.inicial');
        }

        return $next($request);
    }
}
