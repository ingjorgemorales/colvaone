<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckPermission
{
    public function handle(Request $request, Closure $next, string ...$permissions): Response
    {
        if (!$request->user() || !$request->user()->hasAnyPermission($permissions)) {
            abort(403, 'No tienes permiso para acceder a esta seccion.');
        }

        return $next($request);
    }
}
