<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Auto-lock middleware for API sessions.
 *
 * Checks the last activity timestamp stored in the token's metadata.
 * If the user has been inactive beyond the configured timeout, the
 * token is revoked and a 401 response is returned, forcing re-auth.
 */
class AutoLockInactivity
{
    /**
     * Inactivity timeout in minutes (default: 15 minutes).
     */
    private const DEFAULT_TIMEOUT_MINUTES = 15;

    public function handle(Request $request, Closure $next): Response
    {
        $user  = $request->user();
        $token = $user?->currentAccessToken();

        if (! $token) {
            return $next($request);
        }

        $timeout    = (int) config('pos.inactivity_timeout', self::DEFAULT_TIMEOUT_MINUTES);
        $lastUsedAt = $token->last_used_at ?? $token->created_at;

        if ($lastUsedAt && $lastUsedAt->diffInMinutes(now()) > $timeout) {
            $token->delete();

            return response()->json([
                'message' => 'Session expired due to inactivity. Please log in again.',
                'locked'  => true,
            ], 401);
        }

        return $next($request);
    }
}
