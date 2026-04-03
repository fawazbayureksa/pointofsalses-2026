<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SubscriptionController extends Controller
{
    /** Show the available subscription plans. */
    public function plans()
    {
        $tenant = Auth::user()?->tenant;

        return view('subscription.plans', compact('tenant'));
    }

    /**
     * Handle a plan selection from the subscription page.
     * Currently only the "basic" (free) plan can be self-selected.
     * Paid plans are handled via the contact-sales / email flow.
     */
    public function select(Request $request)
    {
        $request->validate([
            'plan' => ['required', 'in:basic'],
        ]);

        $user   = Auth::user();
        $tenant = $user?->tenant;

        if (! $tenant) {
            return redirect()->route('subscription.plans')
                ->with('warning', 'No tenant associated with your account.');
        }

        $tenant->update([
            'plan'          => 'basic',
            'trial_ends_at' => null,
        ]);

        return redirect('/admin/dashboard')
            ->with('success', 'You are now on the free Starter plan.');
    }
}
