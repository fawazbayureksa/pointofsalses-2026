<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureSubscriptionActive
{
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check()) {
            $user = Auth::user();

            // Super-admins (no tenant) are always allowed through
            if ($user->tenant_id === null) {
                return $next($request);
            }

            $tenant = $user->tenant;

            if (! $tenant) {
                Auth::logout();
                return redirect('/login')->withErrors([
                    'email' => 'No business account found. Please contact support.',
                ]);
            }

            if (! $tenant->hasActiveAccess()) {
                return redirect()->route('subscription.expired');
            }
        }

        return $next($request);
    }
}
