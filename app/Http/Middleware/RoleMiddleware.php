<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Spatie\Permission\Exceptions\UnauthorizedException;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, $roleOrPermission)
    {
        if (!$request->user()->can($roleOrPermission)) {
            throw UnauthorizedException::forPermissions([$roleOrPermission]);
        }

        return $next($request);
    }
}
