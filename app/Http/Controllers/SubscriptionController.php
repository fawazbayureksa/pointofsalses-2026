<?php

namespace App\Http\Controllers;

use App\Models\Tenant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SubscriptionController extends Controller
{
    /** Show the available subscription plans. */
    public function plans()
    {
        $tenant = Auth::user()?->tenant;
        $plans = Tenant::availablePlans();

        return view('subscription.plans', compact('tenant', 'plans'));
    }

    /**
     * Handle a plan selection / upgrade from the subscription page.
     * Supports Starter (basic), Professional, and Enterprise plans with monthly/annual billing.
     */
    public function select(Request $request)
    {
        $validated = $request->validate([
            'plan' => ['required', 'in:basic,professional,enterprise'],
            'billing_cycle' => ['nullable', 'in:monthly,annual'],
            'payment_method' => ['nullable', 'string', 'max:50'],
        ]);

        $user = Auth::user();
        $tenant = $user?->tenant;

        if (! $tenant) {
            return redirect()->route('subscription.plans')
                ->with('warning', 'No tenant associated with your account.');
        }

        $plans = Tenant::availablePlans();
        $selectedPlan = $plans[$validated['plan']];
        $billingCycle = $validated['billing_cycle'] ?? 'monthly';

        if ($validated['plan'] === 'basic') {
            $tenant->update([
                'plan' => 'basic',
                'subscription_status' => 'active',
                'trial_ends_at' => null,
                'subscription_ends_at' => null,
            ]);

            $tenant->recordBillingEvent([
                'plan' => 'basic',
                'plan_name' => 'Starter (Free)',
                'billing_cycle' => 'forever',
                'amount' => 0,
                'payment_method' => 'Free Tier',
                'status' => 'active',
                'description' => 'Selected Starter (Free Forever) Plan',
                'period_start' => now()->format('d M Y'),
                'period_end' => 'Forever',
            ]);

            return redirect()->route('admin.subscription.index')
                ->with('success', 'You are now on the free Starter plan.');
        }

        $amount = $billingCycle === 'annual'
            ? $selectedPlan['annual_price']
            : $selectedPlan['monthly_price'];

        $startFrom = ($tenant->subscription_ends_at && $tenant->subscription_ends_at->isFuture())
            ? $tenant->subscription_ends_at->copy()
            : now();

        $endsAt = $billingCycle === 'annual'
            ? $startFrom->copy()->addYear()
            : $startFrom->copy()->addMonth();

        $tenant->update([
            'plan' => $validated['plan'],
            'subscription_status' => 'active',
            'subscription_ends_at' => $endsAt,
        ]);

        $invoice = $tenant->recordBillingEvent([
            'plan' => $validated['plan'],
            'plan_name' => $selectedPlan['name'],
            'billing_cycle' => $billingCycle,
            'amount' => $amount,
            'payment_method' => strtoupper($validated['payment_method'] ?? 'QRIS'),
            'status' => 'paid',
            'description' => "{$selectedPlan['name']} Plan (".ucfirst($billingCycle).')',
            'period_start' => $startFrom->format('d M Y'),
            'period_end' => $endsAt->format('d M Y'),
        ]);

        return redirect()->route('admin.subscription.index')
            ->with('success', "Welcome to {$selectedPlan['name']}! Invoice {$invoice['id']} confirmed. Your subscription is active until {$endsAt->format('d M Y')}.");
    }
}
