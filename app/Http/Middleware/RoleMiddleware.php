<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = Auth::user();
        if (!$user) {
            abort(403, 'You do not have permission to access this page.');
        }

        $allowed = array_map('strtolower', $roles);
        $role = strtolower((string) ($user->role ?? ''));

        if (!in_array($role, $allowed, true)) {
            abort(403, 'You do not have permission to access this page.');
        }

        return $next($request);
    }
}

