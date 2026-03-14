<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Reject requests to tenant endpoints when the tenant's status is not 'active'.
 * Place this middleware after tenancy initialization (e.g. after InitializeTenancyByDomain).
 */
class EnsureTenantActive
{
    public function handle(Request $request, Closure $next): Response
    {
        $tenant = tenant();

        if (! $tenant || $tenant->status !== 'active') {
            return response()->json([
                'message' => 'This account is suspended or inactive. Please contact support.',
            ], 403);
        }

        return $next($request);
    }
}
