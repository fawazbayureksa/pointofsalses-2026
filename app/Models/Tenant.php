<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Tenant extends Model
{
    use HasFactory, LogsActivity, SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'email',
        'phone',
        'contact_name',
        'business_type',
        'logo',
        'address',
        'plan',
        'status',
        'trial_ends_at',
        'subscription_skipped',
        'subscription_status',
        'subscription_ends_at',
        'is_subscription_exempt',
        'settings',
    ];

    protected $casts = [
        'trial_ends_at' => 'datetime',
        'subscription_ends_at' => 'datetime',
        'subscription_skipped' => 'boolean',
        'is_subscription_exempt' => 'boolean',
        'settings' => 'array',
    ];

    public static function availablePlans(): array
    {
        return [
            'basic' => [
                'key' => 'basic',
                'name' => 'Starter',
                'tagline' => 'Perfect for a single store getting started',
                'monthly_price' => 0,
                'annual_price' => 0,
                'price_label' => 'Free',
                'billing_period' => 'Forever',
                'badge' => null,
                'max_outlets' => 1,
                'max_users' => 3,
                'max_products' => 100,
                'features' => [
                    'pos_terminal' => true,
                    'basic_inventory' => true,
                    'customer_crm' => true,
                    'cashier_shifts' => true,
                    'multi_outlet' => false,
                    'stock_transfers' => false,
                    'supplier_management' => true,
                    'finance_reports' => false,
                    'advanced_analytics' => false,
                    'loyalty_program' => false,
                    'audit_logs' => false,
                    'priority_support' => false,
                    'custom_integration' => false,
                ],
                'highlights' => [
                    '1 Outlet Location',
                    'Up to 3 Staff Accounts',
                    'Up to 100 Active Products',
                    'Standard POS & Cashier Shift',
                    'Basic Stock Tracking',
                    'Optional Supplier Directory & PO',
                    'Standard Receipt Printing',
                ],
            ],
            'professional' => [
                'key' => 'professional',
                'name' => 'Professional',
                'tagline' => 'Full POS & ERP power for growing businesses',
                'monthly_price' => 299000,
                'annual_price' => 2870000, // Rp 239K/mo (20% off)
                'price_label' => 'Rp 299K',
                'billing_period' => 'per month',
                'badge' => 'Most Popular',
                'max_outlets' => 5,
                'max_users' => null, // Unlimited
                'max_products' => null, // Unlimited
                'features' => [
                    'pos_terminal' => true,
                    'basic_inventory' => true,
                    'customer_crm' => true,
                    'cashier_shifts' => true,
                    'multi_outlet' => true,
                    'stock_transfers' => true,
                    'supplier_management' => true,
                    'finance_reports' => true,
                    'advanced_analytics' => true,
                    'loyalty_program' => true,
                    'audit_logs' => true,
                    'priority_support' => true,
                    'custom_integration' => false,
                ],
                'highlights' => [
                    'Up to 5 Outlet Locations',
                    'Unlimited Staff & Cashier Accounts',
                    'Unlimited Products & Categories',
                    'Multi-Outlet Stock Transfers & Movements',
                    'Full Supplier, PO, Goods Receiving & Payables',
                    'Customer Loyalty & Membership Points',
                    'Full Finance, Sales & Tax Reports',
                    'Complete Audit & Activity Logs',
                    'Priority WhatsApp & Email Support',
                ],
            ],
            'enterprise' => [
                'key' => 'enterprise',
                'name' => 'Enterprise',
                'tagline' => 'Unlimited scale for franchises & multi-branch chains',
                'monthly_price' => 799000,
                'annual_price' => 7670000, // Rp 639K/mo (20% off)
                'price_label' => 'Rp 799K',
                'billing_period' => 'per month',
                'badge' => 'Enterprise Scale',
                'max_outlets' => null, // Unlimited
                'max_users' => null, // Unlimited
                'max_products' => null, // Unlimited
                'features' => [
                    'pos_terminal' => true,
                    'basic_inventory' => true,
                    'customer_crm' => true,
                    'cashier_shifts' => true,
                    'multi_outlet' => true,
                    'stock_transfers' => true,
                    'supplier_management' => true,
                    'finance_reports' => true,
                    'advanced_analytics' => true,
                    'loyalty_program' => true,
                    'audit_logs' => true,
                    'priority_support' => true,
                    'custom_integration' => true,
                ],
                'highlights' => [
                    'Unlimited Outlet Locations',
                    'Unlimited Staff & Role Permissions',
                    'Unlimited Products & High-Volume POS',
                    'All Professional ERP, Procurement & Finance Modules',
                    'Dedicated Account Manager & SLA',
                    'Custom API, ERP & Accounting Integration',
                    'Custom Onboarding & Staff Training',
                ],
            ],
        ];
    }

    public static function featureLabels(): array
    {
        return [
            'pos_terminal' => ['label' => 'POS Terminal & Cashier Register', 'icon' => 'fa-cash-register'],
            'basic_inventory' => ['label' => 'Product Catalog & Stock Management', 'icon' => 'fa-boxes-stacked'],
            'customer_crm' => ['label' => 'Customer Database & Order History', 'icon' => 'fa-users'],
            'cashier_shifts' => ['label' => 'Cashier Shift & Cash Drawer Tracking', 'icon' => 'fa-clock'],
            'multi_outlet' => ['label' => 'Multi-Outlet Branch Management', 'icon' => 'fa-store'],
            'stock_transfers' => ['label' => 'Inter-Outlet Stock Transfers & Ledger', 'icon' => 'fa-arrow-right-arrow-left'],
            'supplier_management' => ['label' => 'Supplier, PO, Goods Receiving & Payables (Optional)', 'icon' => 'fa-truck-field'],
            'finance_reports' => ['label' => 'Finance, Tax & Payment Reports', 'icon' => 'fa-file-invoice-dollar'],
            'advanced_analytics' => ['label' => 'Advanced Sales & Inventory Analytics', 'icon' => 'fa-chart-line'],
            'loyalty_program' => ['label' => 'Customer Membership & Loyalty Tier', 'icon' => 'fa-id-card'],
            'audit_logs' => ['label' => 'Security Audit & Activity Logs', 'icon' => 'fa-shield-halved'],
            'priority_support' => ['label' => '24/7 Priority Merchant Support', 'icon' => 'fa-headset'],
            'custom_integration' => ['label' => 'Custom API & Webhook Integrations', 'icon' => 'fa-plug'],
        ];
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->setDescriptionForEvent(fn (string $e) => "Tenant {$this->name} was {$e}");
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function outlets(): HasMany
    {
        return $this->hasMany(Outlet::class);
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function customers(): HasMany
    {
        return $this->hasMany(Customer::class);
    }

    public function suppliers(): HasMany
    {
        return $this->hasMany(Supplier::class);
    }

    public function purchaseOrders(): HasMany
    {
        return $this->hasMany(PurchaseOrder::class);
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function isSuspended(): bool
    {
        return $this->status === 'suspended';
    }

    public function isOnTrial(): bool
    {
        return $this->subscription_status === 'trial'
            && $this->trial_ends_at !== null
            && $this->trial_ends_at->isFuture();
    }

    public function trialDaysLeft(): int
    {
        if (! $this->isOnTrial()) {
            return 0;
        }

        return (int) max(0, ceil(now()->floatDiffInDays($this->trial_ends_at, false)));
    }

    public function trialProgressPercent(): int
    {
        if (! $this->isOnTrial()) {
            return 100;
        }

        $totalDays = 14;
        $daysLeft = $this->trialDaysLeft();
        $usedDays = max(0, $totalDays - $daysLeft);

        return (int) min(100, max(5, round(($usedDays / $totalDays) * 100)));
    }

    public function isSubscribed(): bool
    {
        return $this->subscription_status === 'active'
            && ($this->subscription_ends_at === null || $this->subscription_ends_at->isFuture());
    }

    public function subscriptionDaysLeft(): ?int
    {
        if (! $this->subscription_ends_at) {
            return null;
        }

        if ($this->subscription_ends_at->isPast()) {
            return 0;
        }

        return (int) max(0, ceil(now()->floatDiffInDays($this->subscription_ends_at, false)));
    }

    public function isExempt(): bool
    {
        return (bool) $this->is_subscription_exempt || (bool) $this->subscription_skipped;
    }

    public function hasActiveAccess(): bool
    {
        if ($this->isExempt() || $this->isOnTrial() || $this->isSubscribed()) {
            return true;
        }

        // Starter (basic) plan is free forever unless explicitly cancelled/expired by admin
        return $this->plan === 'basic'
            && ! in_array($this->subscription_status, ['expired', 'cancelled'], true);
    }

    public function canAccessSystem(): bool
    {
        return $this->hasActiveAccess();
    }

    public function effectivePlanKey(): string
    {
        if ($this->isOnTrial() && ($this->plan === 'basic' || empty($this->plan))) {
            return 'professional';
        }

        return in_array($this->plan, ['basic', 'professional', 'enterprise'], true)
            ? $this->plan
            : 'basic';
    }

    public function planDetails(): array
    {
        $plans = self::availablePlans();

        return $plans[$this->effectivePlanKey()] ?? $plans['basic'];
    }

    public function maxOutlets(): ?int
    {
        if ($this->isExempt()) {
            return null;
        }

        return $this->planDetails()['max_outlets'];
    }

    public function maxUsers(): ?int
    {
        if ($this->isExempt()) {
            return null;
        }

        return $this->planDetails()['max_users'];
    }

    public function maxProducts(): ?int
    {
        if ($this->isExempt()) {
            return null;
        }

        return $this->planDetails()['max_products'];
    }

    public function canCreateOutlet(): bool
    {
        $max = $this->maxOutlets();

        return $max === null || $this->outlets()->count() < $max;
    }

    public function canCreateUser(): bool
    {
        $max = $this->maxUsers();

        return $max === null || $this->users()->count() < $max;
    }

    public function hasFeature(string $feature): bool
    {
        if ($this->isExempt()) {
            return true;
        }

        return (bool) ($this->planDetails()['features'][$feature] ?? false);
    }

    public function billingHistory(): array
    {
        return array_values($this->getSetting('billing_history', []));
    }

    public function recordBillingEvent(array $entry): array
    {
        $history = $this->billingHistory();
        $record = array_merge([
            'id' => 'INV-'.now()->format('Ym').'-'.strtoupper(substr(md5(uniqid((string) mt_rand(), true)), 0, 6)),
            'date' => now()->toIso8601String(),
            'plan' => $this->plan,
            'plan_name' => ucfirst($this->plan ?? 'basic'),
            'billing_cycle' => 'monthly',
            'amount' => 0,
            'payment_method' => 'Self-Service',
            'status' => 'paid',
            'description' => 'Subscription update',
            'period_start' => now()->format('d M Y'),
            'period_end' => $this->subscription_ends_at ? $this->subscription_ends_at->format('d M Y') : 'Forever',
        ], $entry);

        array_unshift($history, $record);
        $this->setSetting('billing_history', array_slice($history, 0, 50));

        return $record;
    }

    public function getSetting(string $key, mixed $default = null): mixed
    {
        return data_get($this->settings, $key, $default);
    }

    public function setSetting(string $key, mixed $value): void
    {
        $settings = $this->settings ?? [];
        data_set($settings, $key, $value);
        $this->update(['settings' => $settings]);
    }

    /**
     * Super Admin override for Supplier Management:
     * 'auto' (follow tenant setting or business_type default), 'enabled' (force on), 'disabled' (force off)
     */
    public function supplierManagementOverride(): string
    {
        $override = $this->getSetting('supplier_management_override', 'auto');

        return in_array($override, ['auto', 'enabled', 'disabled'], true) ? $override : 'auto';
    }

    /**
     * Default Supplier Management state based on business_type when no explicit setting is saved.
     * Service-only businesses (barbershop, salon, laundry, clinic, carwash, service, consulting)
     * default to false; retail, grocery, minimarket, supermarket, pharmacy, restaurant, cafe, wholesale default to true.
     */
    public function defaultSupplierManagementByBusinessType(): bool
    {
        $type = strtolower(trim((string) $this->business_type));

        $serviceTypes = [
            'service',
            'jasa',
            'barbershop',
            'salon',
            'laundry',
            'carwash',
            'cuci mobil',
            'clinic',
            'klinik',
            'consulting',
            'freelance',
            'digital',
        ];

        foreach ($serviceTypes as $serviceKeyword) {
            if ($type !== '' && str_contains($type, $serviceKeyword)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Check whether Supplier Management is enabled for this tenant.
     */
    public function isSupplierManagementEnabled(): bool
    {
        $override = $this->supplierManagementOverride();

        if ($override === 'enabled') {
            return true;
        }

        if ($override === 'disabled') {
            return false;
        }

        // Check tenant's own Setting record (without global scope so it works for any tenant instance)
        $tenantSetting = Setting::withoutGlobalScopes()
            ->where('tenant_id', $this->id)
            ->where('key', 'supplier_management_enabled')
            ->first();

        if ($tenantSetting !== null) {
            return filter_var($tenantSetting->value, FILTER_VALIDATE_BOOLEAN);
        }

        // Check JSON settings fallback
        $jsonPref = $this->getSetting('supplier_management_enabled');
        if ($jsonPref !== null) {
            return filter_var($jsonPref, FILTER_VALIDATE_BOOLEAN);
        }

        return $this->defaultSupplierManagementByBusinessType();
    }
}
