<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureTenantActive
{
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check() && Auth::user()->tenant_id !== null) {
            $tenant = Auth::user()->tenant;

            if (! $tenant || $tenant->status !== 'active') {
                Auth::logout();
                return redirect('/login')->withErrors([
                    'email' => 'Your account is suspended or inactive. Please contact support.',
                ]);
            }
        }

        return $next($request);
    }
}
