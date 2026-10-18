<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Outlet;
use App\Models\Product;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Spatie\Activitylog\Models\Activity;

class SubscriptionManagementController extends Controller
{
    /**
     * Resolve the target tenant for the current request.
     * Tenant users manage their own tenant; super_admin can inspect/manage any tenant.
     */
    protected function resolveTenant(Request $request): ?Tenant
    {
        $user = Auth::user();

        if ($user->hasRole('super_admin')) {
            if ($request->filled('tenant_id')) {
                return Tenant::find($request->integer('tenant_id'));
            }

            if ($user->tenant_id) {
                return $user->tenant;
            }

            return null;
        }

        return $user->tenant;
    }

    public function index(Request $request)
    {
        $user        = Auth::user();
        $isSuper     = $user->hasRole('super_admin');
        $tenant      = $this->resolveTenant($request);
        $plans       = Tenant::availablePlans();
        $featuresMap = Tenant::featureLabels();

        // Super admin global overview stats & tenant list
        $allTenants = collect();
        $adminStats = null;

        if ($isSuper) {
            $allTenants = Tenant::withCount(['outlets', 'users'])
                ->latest()
                ->get();

            $activePaidCount = $allTenants->filter(fn(Tenant $t) => $t->isSubscribed() && $t->plan !== 'basic')->count();
            $trialCount      = $allTenants->filter(fn(Tenant $t) => $t->isOnTrial())->count();
            $exemptCount     = $allTenants->filter(fn(Tenant $t) => $t->isExempt())->count();
            $expiredCount    = $allTenants->filter(fn(Tenant $t) => ! $t->hasActiveAccess())->count();

            $estimatedMrr = $allTenants->sum(function (Tenant $t) use ($plans) {
                if (! $t->isSubscribed() || $t->isExempt()) {
                    return 0;
                }
                return $plans[$t->plan]['monthly_price'] ?? 0;
            });

            $adminStats = [
                'total_tenants' => $allTenants->count(),
                'active_paid'   => $activePaidCount,
                'on_trial'      => $trialCount,
                'exempt'        => $exemptCount,
                'expired'       => $expiredCount,
                'estimated_mrr' => $estimatedMrr,
            ];
        }

        $usage          = null;
        $currentPlan    = null;
        $billingHistory = [];
        $billingProfile = [];
        $activityLogs   = collect();

        if ($tenant) {
            $currentPlan = $tenant->planDetails();

            $outletsCount  = Outlet::withoutGlobalScopes()->where('tenant_id', $tenant->id)->count();
            $usersCount    = User::where('tenant_id', $tenant->id)->count();
            $productsCount = Product::withoutGlobalScopes()->where('tenant_id', $tenant->id)->count();
            $customersCount = Customer::withoutGlobalScopes()->where('tenant_id', $tenant->id)->count();

            $monthlyOrdersQuery = Order::withoutGlobalScopes()
                ->where('tenant_id', $tenant->id)
                ->where('created_at', '>=', now()->startOfMonth());

            $monthlyOrdersCount = (clone $monthlyOrdersQuery)->count();
            $monthlyRevenue     = (float) (clone $monthlyOrdersQuery)
                ->where('status', 'completed')
                ->sum('total_amount');

            $maxOutlets  = $tenant->maxOutlets();
            $maxUsers    = $tenant->maxUsers();
            $maxProducts = $tenant->maxProducts();

            $usage = [
                'outlets_count'        => $outletsCount,
                'outlets_max'          => $maxOutlets,
                'outlets_percent'      => $maxOutlets ? min(100, (int) round(($outletsCount / $maxOutlets) * 100)) : 25,
                'users_count'          => $usersCount,
                'users_max'            => $maxUsers,
                'users_percent'        => $maxUsers ? min(100, (int) round(($usersCount / $maxUsers) * 100)) : 20,
                'products_count'       => $productsCount,
                'products_max'         => $maxProducts,
                'products_percent'     => $maxProducts ? min(100, (int) round(($productsCount / $maxProducts) * 100)) : 15,
                'customers_count'      => $customersCount,
                'monthly_orders_count' => $monthlyOrdersCount,
                'monthly_revenue'      => $monthlyRevenue,
            ];

            $billingHistory = $tenant->billingHistory();

            if (empty($billingHistory)) {
                $billingHistory = [
                    [
                        'id'             => 'INV-' . $tenant->created_at->format('Ym') . '-TRIAL',
                        'date'           => $tenant->created_at->toIso8601String(),
                        'plan'           => $tenant->plan ?? 'professional',
                        'plan_name'      => ucfirst($tenant->plan ?? 'Professional'),
                        'billing_cycle'  => 'trial',
                        'amount'         => 0,
                        'payment_method' => '14-Day Free Trial',
                        'status'         => 'trial',
                        'description'    => '14-Day Free Trial Provisioned on Registration',
                        'period_start'   => $tenant->created_at->format('d M Y'),
                        'period_end'     => $tenant->trial_ends_at ? $tenant->trial_ends_at->format('d M Y') : 'N/A',
                    ],
                ];
            }

            $billingProfile = [
                'billing_name'           => $tenant->getSetting('billing.billing_name', $tenant->name),
                'billing_email'          => $tenant->getSetting('billing.billing_email', $tenant->email ?? $user->email),
                'billing_phone'          => $tenant->getSetting('billing.billing_phone', $tenant->phone ?? ''),
                'tax_id'                 => $tenant->getSetting('billing.tax_id', ''),
                'billing_address'        => $tenant->getSetting('billing.billing_address', $tenant->address ?? ''),
                'auto_renew'             => (bool) $tenant->getSetting('billing.auto_renew', true),
                'default_payment_method' => $tenant->getSetting('billing.default_payment_method', 'qris'),
            ];

            $activityLogs = Activity::where('subject_type', Tenant::class)
                ->where('subject_id', $tenant->id)
                ->latest()
                ->limit(10)
                ->get();
        }

        return view('admin.subscription.index', compact(
            'tenant',
            'plans',
            'featuresMap',
            'currentPlan',
            'usage',
            'billingHistory',
            'billingProfile',
            'activityLogs',
            'isSuper',
            'allTenants',
            'adminStats'
        ));
    }

    /**
     * Upgrade, renew, or switch a tenant's subscription plan.
     */
    public function changePlan(Request $request)
    {
        $validated = $request->validate([
            'plan'           => ['required', 'in:basic,professional,enterprise'],
            'billing_cycle'  => ['nullable', 'in:monthly,annual'],
            'payment_method' => ['nullable', 'string', 'max:50'],
            'coupon_code'    => ['nullable', 'string', 'max:30'],
            'tenant_id'      => ['nullable', 'integer', 'exists:tenants,id'],
        ]);

        $tenant = $this->resolveTenant($request);

        if (! $tenant) {
            return redirect()->route('admin.subscription.index')
                ->withErrors(['plan' => 'Please select a tenant to manage its subscription.']);
        }

        $plans        = Tenant::availablePlans();
        $selectedPlan = $plans[$validated['plan']];
        $billingCycle = $validated['billing_cycle'] ?? 'monthly';
        $paymentLabels = [
            'qris'            => 'QRIS Instant Payment',
            'virtual_account' => 'Bank Virtual Account (BCA/Mandiri)',
            'credit_card'     => 'Credit / Debit Card',
            'bank_transfer'   => 'Manual Bank Transfer',
        ];
        $paymentMethodLabel = $paymentLabels[$validated['payment_method'] ?? 'qris'] ?? 'Online Payment';

        if ($validated['plan'] === 'basic') {
            $tenant->update([
                'plan'                 => 'basic',
                'subscription_status'  => 'active',
                'trial_ends_at'        => null,
                'subscription_ends_at' => null,
            ]);

            $tenant->recordBillingEvent([
                'plan'           => 'basic',
                'plan_name'      => 'Starter (Free)',
                'billing_cycle'  => 'forever',
                'amount'         => 0,
                'payment_method' => 'Free Tier',
                'status'         => 'active',
                'description'    => 'Switched to Starter (Free Forever) Plan',
                'period_start'   => now()->format('d M Y'),
                'period_end'     => 'Forever',
            ]);

            $redirectParams = Auth::user()->hasRole('super_admin') ? ['tenant_id' => $tenant->id] : [];

            return redirect()->route('admin.subscription.index', $redirectParams)
                ->with('success', "{$tenant->name} is now on the Starter (Free) plan.");
        }

        // Calculate price & duration for paid plans (Professional / Enterprise)
        $baseAmount = $billingCycle === 'annual'
            ? $selectedPlan['annual_price']
            : $selectedPlan['monthly_price'];

        $discount = 0;
        $coupon   = strtoupper(trim((string) ($validated['coupon_code'] ?? '')));
        if ($coupon !== '' && in_array($coupon, ['POSPRO20', 'WELCOME20', 'HEMAT20'], true)) {
            $discount = (int) round($baseAmount * 0.20);
        }

        $finalAmount = max(0, $baseAmount - $discount);

        // Extend from existing future expiry date if already active on paid plan
        $startFrom = ($tenant->subscription_ends_at && $tenant->subscription_ends_at->isFuture())
            ? $tenant->subscription_ends_at->copy()
            : now();

        $endsAt = $billingCycle === 'annual'
            ? $startFrom->copy()->addYear()
            : $startFrom->copy()->addMonth();

        $tenant->update([
            'plan'                 => $validated['plan'],
            'subscription_status'  => 'active',
            'subscription_ends_at' => $endsAt,
        ]);

        $invoice = $tenant->recordBillingEvent([
            'plan'           => $validated['plan'],
            'plan_name'      => $selectedPlan['name'],
            'billing_cycle'  => $billingCycle,
            'amount'         => $finalAmount,
            'discount'       => $discount,
            'coupon_code'    => $coupon ?: null,
            'payment_method' => $paymentMethodLabel,
            'status'         => 'paid',
            'description'    => "{$selectedPlan['name']} Plan (" . ucfirst($billingCycle) . " Subscription)",
            'period_start'   => $startFrom->format('d M Y'),
            'period_end'     => $endsAt->format('d M Y'),
        ]);

        $redirectParams = Auth::user()->hasRole('super_admin') ? ['tenant_id' => $tenant->id] : [];

        return redirect()->route('admin.subscription.index', $redirectParams)
            ->with('success', "Subscription upgraded to {$selectedPlan['name']} (" . ucfirst($billingCycle) . ")! Invoice {$invoice['id']} has been generated. Valid until {$endsAt->format('d M Y')}.");
    }

    /**
     * Update tenant billing profile and preferences.
     */
    public function updateBilling(Request $request)
    {
        $validated = $request->validate([
            'billing_name'           => ['required', 'string', 'max:255'],
            'billing_email'          => ['required', 'email', 'max:255'],
            'billing_phone'          => ['nullable', 'string', 'max:30'],
            'tax_id'                 => ['nullable', 'string', 'max:50'],
            'billing_address'        => ['nullable', 'string', 'max:500'],
            'default_payment_method' => ['nullable', 'in:qris,virtual_account,credit_card,bank_transfer'],
            'auto_renew'             => ['nullable', 'boolean'],
            'tenant_id'              => ['nullable', 'integer', 'exists:tenants,id'],
        ]);

        $tenant = $this->resolveTenant($request);

        if (! $tenant) {
            return redirect()->route('admin.subscription.index')
                ->withErrors(['billing_name' => 'No tenant found to update billing details.']);
        }

        $settings = $tenant->settings ?? [];
        data_set($settings, 'billing', [
            'billing_name'           => $validated['billing_name'],
            'billing_email'          => $validated['billing_email'],
            'billing_phone'          => $validated['billing_phone'] ?? '',
            'tax_id'                 => $validated['tax_id'] ?? '',
            'billing_address'        => $validated['billing_address'] ?? '',
            'default_payment_method' => $validated['default_payment_method'] ?? 'qris',
            'auto_renew'             => $request->boolean('auto_renew'),
        ]);

        $tenant->update([
            'email'    => $validated['billing_email'],
            'phone'    => $validated['billing_phone'] ?: $tenant->phone,
            'address'  => $validated['billing_address'] ?: $tenant->address,
            'settings' => $settings,
        ]);

        $redirectParams = Auth::user()->hasRole('super_admin') ? ['tenant_id' => $tenant->id] : [];

        return redirect()->route('admin.subscription.index', $redirectParams)
            ->with('success', 'Billing profile and payment preferences saved successfully.');
    }

    /**
     * Cancel auto-renewal or downgrade subscription.
     */
    public function cancel(Request $request)
    {
        $tenant = $this->resolveTenant($request);

        if (! $tenant) {
            return redirect()->route('admin.subscription.index')
                ->withErrors(['cancel' => 'No tenant found.']);
        }

        $immediate = $request->boolean('immediate_downgrade');

        if ($immediate) {
            $tenant->update([
                'plan'                 => 'basic',
                'subscription_status'  => 'active',
                'subscription_ends_at' => null,
            ]);

            $tenant->recordBillingEvent([
                'plan'           => 'basic',
                'plan_name'      => 'Starter (Free)',
                'billing_cycle'  => 'forever',
                'amount'         => 0,
                'payment_method' => 'System',
                'status'         => 'downgraded',
                'description'    => 'Paid subscription cancelled & downgraded to Starter (Free)',
                'period_start'   => now()->format('d M Y'),
                'period_end'     => 'Forever',
            ]);

            $message = 'Subscription downgraded to Starter (Free) plan immediately.';
        } else {
            $tenant->setSetting('billing.auto_renew', false);
            $endDate = $tenant->subscription_ends_at ? $tenant->subscription_ends_at->format('d M Y') : 'the end of your billing period';
            $message = "Auto-renewal has been turned off. You will retain full access until {$endDate}.";
        }

        $redirectParams = Auth::user()->hasRole('super_admin') ? ['tenant_id' => $tenant->id] : [];

        return redirect()->route('admin.subscription.index', $redirectParams)
            ->with('success', $message);
    }

    /**
     * Super Admin quick actions on any tenant's subscription.
     */
    public function adminAction(Request $request, Tenant $tenant)
    {
        if (! Auth::user()->hasRole('super_admin')) {
            abort(403);
        }

        $validated = $request->validate([
            'action' => ['required', 'in:extend_trial,activate_paid,toggle_exempt,mark_expired,custom_update'],
            'days'   => ['nullable', 'integer', 'min:1', 'max:365'],
            'months' => ['nullable', 'integer', 'min:1', 'max:60'],
            'plan'   => ['nullable', 'in:basic,professional,enterprise'],
            'subscription_status'    => ['nullable', 'in:trial,active,expired,cancelled'],
            'trial_ends_at'          => ['nullable', 'date'],
            'subscription_ends_at'   => ['nullable', 'date'],
            'is_subscription_exempt' => ['nullable', 'boolean'],
        ]);

        $action  = $validated['action'];
        $message = 'Tenant subscription updated.';

        switch ($action) {
            case 'extend_trial':
                $days   = (int) ($validated['days'] ?? 14);
                $base   = ($tenant->trial_ends_at && $tenant->trial_ends_at->isFuture())
                    ? $tenant->trial_ends_at->copy()
                    : now();
                $endsAt = $base->addDays($days);

                $tenant->update([
                    'subscription_status' => 'trial',
                    'trial_ends_at'       => $endsAt,
                    'plan'                => $tenant->plan === 'basic' ? 'professional' : $tenant->plan,
                ]);

                $tenant->recordBillingEvent([
                    'plan'           => $tenant->plan,
                    'plan_name'      => ucfirst($tenant->plan) . ' (Trial Extended)',
                    'billing_cycle'  => 'trial',
                    'amount'         => 0,
                    'payment_method' => 'Admin Override',
                    'status'         => 'trial',
                    'description'    => "Trial extended by {$days} days by Super Admin",
                    'period_start'   => now()->format('d M Y'),
                    'period_end'     => $endsAt->format('d M Y'),
                ]);

                $message = "Extended trial for {$tenant->name} by {$days} days (until {$endsAt->format('d M Y')}).";
                break;

            case 'activate_paid':
                $months = (int) ($validated['months'] ?? 1);
                $plan   = $validated['plan'] ?? ($tenant->plan === 'basic' ? 'professional' : $tenant->plan);
                $plans  = Tenant::availablePlans();
                $base   = ($tenant->subscription_ends_at && $tenant->subscription_ends_at->isFuture())
                    ? $tenant->subscription_ends_at->copy()
                    : now();
                $endsAt = $base->addMonths($months);

                $tenant->update([
                    'plan'                 => $plan,
                    'subscription_status'  => 'active',
                    'subscription_ends_at' => $endsAt,
                ]);

                $amount = ($plans[$plan]['monthly_price'] ?? 0) * $months;
                $tenant->recordBillingEvent([
                    'plan'           => $plan,
                    'plan_name'      => $plans[$plan]['name'] ?? ucfirst($plan),
                    'billing_cycle'  => $months >= 12 ? 'annual' : "{$months} month(s)",
                    'amount'         => $amount,
                    'payment_method' => 'Admin Provisioned',
                    'status'         => 'paid',
                    'description'    => "Activated {$plan} plan for {$months} month(s) by Super Admin",
                    'period_start'   => now()->format('d M Y'),
                    'period_end'     => $endsAt->format('d M Y'),
                ]);

                $message = "Activated {$plan} plan for {$tenant->name} until {$endsAt->format('d M Y')}.";
                break;

            case 'toggle_exempt':
                $newState = ! $tenant->is_subscription_exempt;
                $tenant->update([
                    'is_subscription_exempt' => $newState,
                    'subscription_skipped'   => $newState,
                ]);
                $message = $newState
                    ? "{$tenant->name} is now exempt from subscription checks (Free Access)."
                    : "Removed subscription exemption for {$tenant->name}.";
                break;

            case 'mark_expired':
                $tenant->update([
                    'subscription_status'    => 'expired',
                    'is_subscription_exempt' => false,
                    'subscription_skipped'   => false,
                ]);
                $message = "Marked {$tenant->name}'s subscription as expired.";
                break;

            case 'custom_update':
                $tenant->update([
                    'plan'                   => $validated['plan'] ?? $tenant->plan,
                    'subscription_status'    => $validated['subscription_status'] ?? $tenant->subscription_status,
                    'trial_ends_at'          => $validated['trial_ends_at'] ?? null,
                    'subscription_ends_at'   => $validated['subscription_ends_at'] ?? null,
                    'is_subscription_exempt' => $request->boolean('is_subscription_exempt'),
                    'subscription_skipped'   => $request->boolean('is_subscription_exempt'),
                ]);
                $message = "Updated subscription settings for {$tenant->name}.";
                break;
        }

        return redirect()->back()->with('success', $message);
    }
}
