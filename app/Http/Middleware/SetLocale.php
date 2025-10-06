<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\App;


class SetLocale
{
    protected array $allowed = ['es','en'];

    protected function pick(?string $val): ?string {
        return in_array($val, $this->allowed, true) ? $val : null;
    }

    public function handle($request, Closure $next)
    {
        $userLocale = auth()->check() ? (auth()->user()->locale ?? null) : null;

        $locale = $this->pick($request->query('lang'))
            ?? $this->pick(session('locale'))
            ?? $this->pick($userLocale)
            ?? $this->pick($request->cookie('locale'))
            ?? config('app.locale', 'es');

        App::setLocale($locale);

        return $next($request);
    }
}