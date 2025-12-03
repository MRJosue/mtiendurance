<?php

namespace App\Http\Middleware;


use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;


class ExpulsarInactivos
{
    public function handle(Request $request, Closure $next)
    {
        if (Auth::check() && Auth::user()->ind_activo == 0) {
            Auth::logout();

            return redirect()->route('login')
                ->withErrors(['email' => 'Tu cuenta está inactiva.']);
        }

        return $next($request);
    }
}