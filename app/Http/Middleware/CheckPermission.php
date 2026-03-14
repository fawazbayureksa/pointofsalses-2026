<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Gate-based permission check middleware.
 *
 * Usage in routes:
 *   Route::middleware('permission:manage_products')
 *
 * Spatie registers every permission as a Gate automatically.
 * This middleware provides a clean, named alias for route-level guarding.
 */
class CheckPermission
{
    public function handle(Request $request, Closure $next, string ...$permissions): Response
    {
        foreach ($permissions as $permission) {
            if (! $request->user()?->can($permission)) {
                return response()->json([
                    'message' => "Forbidden. Required permission: {$permission}",
                ], 403);
            }
        }

        return $next($request);
    }
}
