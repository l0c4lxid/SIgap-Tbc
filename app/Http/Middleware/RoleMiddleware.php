<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, $role)
    {
        $user = $request->user();
        if (!$user) {
            return redirect()->route('login');
        }

        // Spatie provides hasRole
        if (!$user->hasRole($role)) {
            abort(Response::HTTP_FORBIDDEN, "Unauthorized.");
        }

        return $next($request);
    }
}
