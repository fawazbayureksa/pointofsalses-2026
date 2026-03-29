<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckSubscription
{
    /**
     * Allow the request through when:
     *  - the user is a super-admin (no tenant)
     *  - the tenant has subscription_skipped = true (manual override)
     *  - the tenant is on the free "basic" plan
     *  - the tenant is still within their trial period
     *
     * Otherwise redirect to the subscription / plans page.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! Auth::check()) {
            return $next($request);
        }

        $user = Auth::user();

        // Super-admins (no tenant) are never subject to subscription checks.
        if ($user->tenant_id === null) {
            return $next($request);
        }

        $tenant = $user->tenant;

        if (! $tenant || ! $tenant->isActive()) {
            // Handled by EnsureTenantActive – just pass through here.
            return $next($request);
        }

        if ($tenant->canAccessSystem()) {
            return $next($request);
        }

        // Trial has expired and plan requires a paid subscription.
        return redirect()->route('subscription.plans')
            ->with('warning', 'Your free trial has ended. Please choose a plan to continue.');
    }
}
