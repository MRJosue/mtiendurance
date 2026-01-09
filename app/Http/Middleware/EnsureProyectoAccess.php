<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Proyecto;

class EnsureProyectoAccess
{
    public function handle(Request $request, Closure $next)
    {
        /** @var \App\Models\Proyecto|null $proyecto */
        $proyecto = $request->route('proyecto');

        // Por si llega como id en vez de model
        if (!$proyecto instanceof Proyecto) {
            $proyecto = Proyecto::find($proyecto);
        }

        if (!$proyecto) {
            abort(404);
        }

        $request->user()->can('view', $proyecto) ?: abort(403);

        return $next($request);
    }
}