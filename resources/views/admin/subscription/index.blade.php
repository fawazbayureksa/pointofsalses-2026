@extends('layouts.admin')

@section('title', 'Subscription & Billing')

@section('page-title', 'Subscription & Billing Management')

@section('content')
<div x-data="{
    billingCycle: 'monthly',
    checkoutOpen: false,
    selectedPlan: 'professional',
    selectedPlanName: 'Professional',
    selectedMonthlyPrice: 299000,
    selectedAnnualPrice: 2870000,
    paymentMethod: '{{ $billingProfile['default_payment_method'] ?? 'qris' }}',
    couponCode: '',
    invoiceModalOpen: false,
    activeInvoice: null,
    cancelModalOpen: false,
    adminEditOpen: false,
    adminTargetTenant: null,
    searchTenant: '',
    filterStatus: '',

    openCheckout(planKey, planName, monthlyPrice, annualPrice) {
        this.selectedPlan = planKey;
        this.selectedPlanName = planName;
        this.selectedMonthlyPrice = monthlyPrice;
        this.selectedAnnualPrice = annualPrice;
        this.checkoutOpen = true;
    },

    basePrice() {
        return this.billingCycle === 'annual' ? this.selectedAnnualPrice : this.selectedMonthlyPrice;
    },

    discountAmount() {
        const code = (this.couponCode || '').trim().toUpperCase();
        if (['POSPRO20', 'WELCOME20', 'HEMAT20'].includes(code)) {
            return Math.round(this.basePrice() * 0.20);
        }
        return 0;
    },

    finalPrice() {
        return Math.max(0, this.basePrice() - this.discountAmount());
    },

    formatRp(amount) {
        if (!amount || amount <= 0) return 'Free (Rp 0)';
        return 'Rp ' + Number(amount).toLocaleString('id-ID');
    },

    openInvoice(inv) {
        this.activeInvoice = inv;
        this.invoiceModalOpen = true;
    },

    openAdminEdit(t) {
        this.adminTargetTenant = t;
        this.adminEditOpen = true;
    }
}" class="space-y-8">

    {{-- Flash Alerts --}}
    @if ($errors->any())
        <div class="bg-red-50 border border-red-200 rounded-xl p-4">
            <div class="flex items-start">
                <i class="fa-solid fa-circle-exclamation text-red-500 mt-0.5 mr-3"></i>
                <div>
                    <h3 class="text-sm font-semibold text-red-800">Please check the following errors:</h3>
                    <ul class="mt-1 text-sm text-red-700 list-disc list-inside space-y-0.5">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    @endif

    @if (session('success'))
        <div class="bg-emerald-50 border border-emerald-200 rounded-xl p-4 flex items-center justify-between">
            <div class="flex items-center">
                <div class="w-8 h-8 rounded-lg bg-emerald-100 text-emerald-600 flex items-center justify-center mr-3">
                    <i class="fa-solid fa-circle-check"></i>
                </div>
                <p class="text-sm font-medium text-emerald-800">{{ session('success') }}</p>
            </div>
        </div>
    @endif

    @if (session('warning'))
        <div class="bg-amber-50 border border-amber-200 rounded-xl p-4 flex items-center">
            <i class="fa-solid fa-triangle-exclamation text-amber-500 mr-3"></i>
            <p class="text-sm font-medium text-amber-800">{{ session('warning') }}</p>
        </div>
    @endif

    {{-- =====================================================================
         SUPER ADMIN BAR & GLOBAL TENANT SUBSCRIPTIONS OVERVIEW
    ===================================================================== --}}
    @if ($isSuper)
        <div class="bg-slate-900 text-white rounded-2xl p-6 shadow-lg border border-slate-800">
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                <div>
                    <div class="flex items-center gap-2">
                        <span class="px-2.5 py-0.5 text-xs font-bold uppercase tracking-wider bg-amber-500/20 text-amber-300 border border-amber-500/30 rounded-full">
                            <i class="fa-solid fa-crown mr-1"></i> Super Admin Console
                        </span>
                        <h2 class="text-lg font-bold">Tenant Subscriptions &amp; SaaS Billing Control</h2>
                    </div>
                    <p class="text-xs text-slate-400 mt-1">
                        Monitor recurring revenue, extend free trials, provision paid plans, or inspect any tenant's subscription portal.
                    </p>
                </div>

                <form method="GET" action="{{ route('admin.subscription.index') }}" class="flex items-center gap-2">
                    <select name="tenant_id" onchange="this.form.submit()"
                        class="bg-slate-800 border border-slate-700 text-white text-sm rounded-xl px-4 py-2.5 focus:ring-2 focus:ring-indigo-500">
                        <option value="">— Global Overview (All Tenants) —</option>
                        @foreach ($allTenants as $t)
                            <option value="{{ $t->id }}" @selected($tenant && $tenant->id === $t->id)>
                                {{ $t->name }} ({{ ucfirst($t->plan ?? 'basic') }} • {{ ucfirst($t->subscription_status ?? 'trial') }})
                            </option>
                        @endforeach
                    </select>
                    @if ($tenant)
                        <a href="{{ route('admin.subscription.index') }}"
                            class="px-3 py-2.5 bg-slate-800 hover:bg-slate-700 border border-slate-700 rounded-xl text-xs font-semibold text-slate-300 whitespace-nowrap">
                            Reset Filter
                        </a>
                    @endif
                </form>
            </div>

            @if ($adminStats)
                <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4 mt-6">
                    <div class="bg-slate-800/90 border border-slate-700/70 rounded-xl p-4">
                        <p class="text-xs text-slate-400">Total Tenants</p>
                        <p class="text-2xl font-extrabold text-white mt-1">{{ $adminStats['total_tenants'] }}</p>
                    </div>
                    <div class="bg-slate-800/90 border border-emerald-500/30 rounded-xl p-4">
                        <p class="text-xs text-emerald-400">Active Paid</p>
                        <p class="text-2xl font-extrabold text-emerald-300 mt-1">{{ $adminStats['active_paid'] }}</p>
                    </div>
                    <div class="bg-slate-800/90 border border-indigo-500/30 rounded-xl p-4">
                        <p class="text-xs text-indigo-300">Active 14d Trial</p>
                        <p class="text-2xl font-extrabold text-indigo-200 mt-1">{{ $adminStats['on_trial'] }}</p>
                    </div>
                    <div class="bg-slate-800/90 border border-amber-500/30 rounded-xl p-4">
                        <p class="text-xs text-amber-300">VIP / Exempt</p>
                        <p class="text-2xl font-extrabold text-amber-200 mt-1">{{ $adminStats['exempt'] }}</p>
                    </div>
                    <div class="bg-slate-800/90 border border-red-500/30 rounded-xl p-4">
                        <p class="text-xs text-red-400">Expired / Inactive</p>
                        <p class="text-2xl font-extrabold text-red-300 mt-1">{{ $adminStats['expired'] }}</p>
                    </div>
                    <div class="bg-gradient-to-br from-indigo-600 to-violet-700 rounded-xl p-4">
                        <p class="text-xs text-indigo-100">Estimated MRR</p>
                        <p class="text-xl font-extrabold text-white mt-1">
                            Rp {{ number_format($adminStats['estimated_mrr'], 0, ',', '.') }}
                        </p>
                    </div>
                </div>
            @endif
        </div>

        {{-- Super Admin All Tenants Subscription Table (shown when no single tenant is selected) --}}
        @if (! $tenant)
            <x-admin-card title="All Tenant Subscriptions">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-5">
                    <div class="flex items-center gap-3">
                        <div class="relative">
                            <i class="fa-solid fa-magnifying-glass text-gray-400 absolute left-3.5 top-3 text-sm"></i>
                            <input type="text" x-model="searchTenant" placeholder="Search business name or email..."
                                class="pl-9 pr-4 py-2 border border-gray-300 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 w-72">
                        </div>
                        <select x-model="filterStatus"
                            class="px-3 py-2 border border-gray-300 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500">
                            <option value="">All Subscription Status</option>
                            <option value="trial">On Trial</option>
                            <option value="active">Active (Paid / Free)</option>
                            <option value="expired">Expired</option>
                            <option value="cancelled">Cancelled</option>
                        </select>
                    </div>
                    <a href="{{ route('admin.tenants.index') }}"
                        class="inline-flex items-center text-sm font-medium text-indigo-600 hover:text-indigo-800">
                        <i class="fa-solid fa-building mr-1.5"></i> Go to Tenants CRUD
                    </a>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Tenant</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Plan</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Subscription Status</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Trial / Expiry Date</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Usage (Outlets / Users)</th>
                                <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase">Quick Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 bg-white">
                            @forelse ($allTenants as $item)
                                <tr x-show="
                                    (!searchTenant || '{{ strtolower(addslashes($item->name . ' ' . $item->email . ' ' . $item->slug)) }}'.includes(searchTenant.toLowerCase())) &&
                                    (!filterStatus || '{{ $item->subscription_status }}' === filterStatus)
                                " class="hover:bg-gray-50">
                                    <td class="px-4 py-3.5">
                                        <div class="font-semibold text-gray-900">{{ $item->name }}</div>
                                        <div class="text-xs text-gray-500">{{ $item->email ?? $item->slug }}</div>
                                    </td>
                                    <td class="px-4 py-3.5">
                                        @if ($item->plan === 'enterprise')
                                            <span class="px-2.5 py-1 text-xs font-bold bg-purple-100 text-purple-800 rounded-full">Enterprise</span>
                                        @elseif ($item->plan === 'professional')
                                            <span class="px-2.5 py-1 text-xs font-bold bg-indigo-100 text-indigo-800 rounded-full">Professional</span>
                                        @else
                                            <span class="px-2.5 py-1 text-xs font-semibold bg-gray-100 text-gray-700 rounded-full">Starter (Free)</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3.5">
                                        @if ($item->isExempt())
                                            <span class="px-2.5 py-1 text-xs font-semibold bg-amber-100 text-amber-800 rounded-full">
                                                <i class="fa-solid fa-crown mr-1"></i> VIP Exempt
                                            </span>
                                        @elseif ($item->isOnTrial())
                                            <span class="px-2.5 py-1 text-xs font-semibold bg-blue-100 text-blue-800 rounded-full">
                                                Trial ({{ $item->trialDaysLeft() }}d left)
                                            </span>
                                        @elseif ($item->isSubscribed())
                                            <span class="px-2.5 py-1 text-xs font-semibold bg-emerald-100 text-emerald-800 rounded-full">
                                                Active
                                            </span>
                                        @else
                                            <span class="px-2.5 py-1 text-xs font-semibold bg-red-100 text-red-800 rounded-full">
                                                Expired
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3.5 text-xs text-gray-600">
                                        @if ($item->subscription_status === 'trial' && $item->trial_ends_at)
                                            <div>Trial ends: <strong>{{ $item->trial_ends_at->format('d M Y') }}</strong></div>
                                        @elseif ($item->subscription_ends_at)
                                            <div>Renews/Ends: <strong>{{ $item->subscription_ends_at->format('d M Y') }}</strong></div>
                                        @else
                                            <span class="text-gray-400">No expiry (Forever)</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3.5 text-xs text-gray-700">
                                        <span class="inline-flex items-center gap-1 mr-3">
                                            <i class="fa-solid fa-store text-gray-400"></i>
                                            {{ $item->outlets_count }}/{{ $item->maxOutlets() ?? '∞' }}
                                        </span>
                                        <span class="inline-flex items-center gap-1">
                                            <i class="fa-solid fa-users text-gray-400"></i>
                                            {{ $item->users_count }}/{{ $item->maxUsers() ?? '∞' }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3.5 text-right whitespace-nowrap">
                                        <div class="inline-flex items-center gap-1.5">
                                            <a href="{{ route('admin.subscription.index', ['tenant_id' => $item->id]) }}"
                                                class="px-2.5 py-1.5 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 rounded-lg text-xs font-semibold transition"
                                                title="Open Tenant Subscription Portal">
                                                <i class="fa-solid fa-Sliders mr-1"></i> Portal
                                            </a>

                                            <form method="POST" action="{{ route('admin.subscription.admin-action', $item->id) }}" class="inline">
                                                @csrf
                                                <input type="hidden" name="action" value="extend_trial">
                                                <input type="hidden" name="days" value="14">
                                                <button type="submit"
                                                    class="px-2.5 py-1.5 bg-blue-50 hover:bg-blue-100 text-blue-700 rounded-lg text-xs font-semibold transition"
                                                    title="Extend Free Trial by +14 Days">
                                                    +14d Trial
                                                </button>
                                            </form>

                                            <form method="POST" action="{{ route('admin.subscription.admin-action', $item->id) }}" class="inline">
                                                @csrf
                                                <input type="hidden" name="action" value="activate_paid">
                                                <input type="hidden" name="plan" value="professional">
                                                <input type="hidden" name="months" value="1">
                                                <button type="submit"
                                                    class="px-2.5 py-1.5 bg-emerald-50 hover:bg-emerald-100 text-emerald-700 rounded-lg text-xs font-semibold transition"
                                                    title="Activate Professional Plan for 1 Month">
                                                    +1M Pro
                                                </button>
                                            </form>

                                            <form method="POST" action="{{ route('admin.subscription.admin-action', $item->id) }}" class="inline">
                                                @csrf
                                                <input type="hidden" name="action" value="toggle_exempt">
                                                <button type="submit"
                                                    class="px-2.5 py-1.5 {{ $item->isExempt() ? 'bg-amber-100 text-amber-800' : 'bg-gray-100 text-gray-600' }} hover:bg-amber-200 rounded-lg text-xs font-semibold transition"
                                                    title="Toggle Subscription Exempt">
                                                    VIP
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-4 py-8 text-center text-gray-500">No tenants found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </x-admin-card>
        @endif
    @endif

    {{-- =====================================================================
         TENANT SUBSCRIPTION & BILLING PORTAL
    ===================================================================== --}}
    @if ($tenant && $currentPlan)
        {{-- 1. Hero Subscription Banner --}}
        <div class="relative rounded-3xl overflow-hidden bg-gradient-to-br from-slate-900 via-indigo-950 to-slate-900 text-white p-8 shadow-xl border border-indigo-500/20">
            <div class="absolute -top-24 -right-24 w-96 h-96 bg-indigo-500/15 rounded-full blur-3xl pointer-events-none"></div>
            <div class="absolute -bottom-24 -left-24 w-80 h-80 bg-emerald-500/10 rounded-full blur-3xl pointer-events-none"></div>

            <div class="relative z-10 grid grid-cols-1 lg:grid-cols-12 gap-8 items-center">
                <div class="lg:col-span-7 space-y-4">
                    <div class="flex flex-wrap items-center gap-2.5">
                        <span class="px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider bg-indigo-500/20 text-indigo-300 border border-indigo-400/30">
                            <i class="fa-solid fa-crown text-amber-400 mr-1.5"></i> {{ $currentPlan['name'] }} Plan
                        </span>

                        @if ($tenant->isExempt())
                            <span class="px-3 py-1 rounded-full text-xs font-semibold bg-amber-500/20 text-amber-300 border border-amber-400/30">
                                <i class="fa-solid fa-star mr-1"></i> VIP Exempt (Unlimited Access)
                            </span>
                        @elseif ($tenant->isOnTrial())
                            <span class="px-3 py-1 rounded-full text-xs font-semibold bg-emerald-500/20 text-emerald-300 border border-emerald-400/30">
                                <i class="fa-solid fa-gift mr-1"></i> 14-Day Free Trial Active
                            </span>
                        @elseif ($tenant->isSubscribed())
                            <span class="px-3 py-1 rounded-full text-xs font-semibold bg-emerald-500/20 text-emerald-300 border border-emerald-400/30">
                                <i class="fa-solid fa-circle-check mr-1"></i> Active Subscription
                            </span>
                        @elseif ($tenant->plan === 'basic')
                            <span class="px-3 py-1 rounded-full text-xs font-semibold bg-slate-700 text-slate-300">
                                Free Forever Tier
                            </span>
                        @else
                            <span class="px-3 py-1 rounded-full text-xs font-semibold bg-red-500/20 text-red-300 border border-red-400/30">
                                <i class="fa-solid fa-triangle-exclamation mr-1"></i> Subscription Expired
                            </span>
                        @endif

                        @if ($billingProfile['auto_renew'] ?? false)
                            <span class="text-xs text-slate-400 flex items-center gap-1">
                                <i class="fa-solid fa-rotate text-emerald-400"></i> Auto-renew enabled
                            </span>
                        @endif
                    </div>

                    <div>
                        <h1 class="text-2xl sm:text-3xl font-black tracking-tight text-white">
                            {{ $tenant->name }}
                        </h1>
                        <p class="text-slate-300 text-sm mt-1">
                            {{ $currentPlan['tagline'] }}
                        </p>
                    </div>

                    {{-- Trial or Active Period Progress Bar --}}
                    @if ($tenant->isOnTrial())
                        @php
                            $daysLeft = $tenant->trialDaysLeft();
                            $progress = $tenant->trialProgressPercent();
                        @endphp
                        <div class="bg-white/5 border border-white/10 rounded-2xl p-4 max-w-xl">
                            <div class="flex items-center justify-between text-xs mb-2">
                                <span class="font-semibold text-indigo-200">
                                    <i class="fa-solid fa-hourglass-half mr-1 text-amber-400"></i>
                                    Free Trial Progress ({{ $daysLeft }} day{{ $daysLeft === 1 ? '' : 's' }} remaining)
                                </span>
                                <span class="text-slate-400">
                                    Ends {{ $tenant->trial_ends_at?->format('d M Y, H:i') }}
                                </span>
                            </div>
                            <div class="w-full h-2.5 bg-slate-800 rounded-full overflow-hidden">
                                <div class="h-full bg-gradient-to-r from-indigo-500 via-violet-500 to-emerald-400 rounded-full transition-all"
                                    style="width: {{ $progress }}%"></div>
                            </div>
                            <p class="text-xs text-slate-400 mt-2">
                                Upgrade anytime to keep your multi-outlet, inventory, and financial report modules without interruption.
                            </p>
                        </div>
                    @elseif ($tenant->subscription_ends_at)
                        <div class="bg-white/5 border border-white/10 rounded-2xl p-4 max-w-xl flex items-center justify-between">
                            <div>
                                <p class="text-xs text-slate-400 uppercase tracking-wider">Current Billing Period Ends</p>
                                <p class="text-base font-bold text-white mt-0.5">
                                    {{ $tenant->subscription_ends_at->format('d F Y') }}
                                    @if ($tenant->subscriptionDaysLeft() !== null)
                                        <span class="text-xs font-normal text-emerald-400 ml-1">
                                            ({{ $tenant->subscriptionDaysLeft() }} days remaining)
                                        </span>
                                    @endif
                                </p>
                            </div>
                            <button type="button"
                                @click="openCheckout('{{ $tenant->plan === 'basic' ? 'professional' : $tenant->plan }}', '{{ $currentPlan['name'] }}', {{ $currentPlan['monthly_price'] ?: 299000 }}, {{ $currentPlan['annual_price'] ?: 2870000 }})"
                                class="px-4 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-xs font-bold text-white transition">
                                Extend / Renew
                            </button>
                        </div>
                    @endif
                </div>

                {{-- Right Price & Quick CTA Box --}}
                <div class="lg:col-span-5 flex flex-col sm:flex-row lg:flex-col justify-between gap-4 bg-white/5 border border-white/10 rounded-2xl p-6 backdrop-blur-md">
                    <div class="flex items-baseline justify-between">
                        <div>
                            <p class="text-xs uppercase tracking-wider text-slate-400 font-semibold">Current Plan Rate</p>
                            <div class="flex items-baseline gap-1 mt-1">
                                <span class="text-3xl font-black text-white">{{ $currentPlan['price_label'] }}</span>
                                <span class="text-xs text-slate-400">{{ $currentPlan['billing_period'] }}</span>
                            </div>
                        </div>
                        <div class="text-right">
                            <span class="text-xs text-slate-400 block">Account ID</span>
                            <span class="text-xs font-mono text-indigo-300">{{ strtoupper($tenant->slug) }}</span>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-2">
                        <button type="button"
                            @click="openCheckout('professional', 'Professional', 299000, 2870000)"
                            class="px-4 py-3 rounded-xl bg-gradient-to-r from-indigo-500 to-violet-600 hover:from-indigo-600 hover:to-violet-700 text-white font-bold text-sm shadow-lg shadow-indigo-500/25 flex items-center justify-center gap-2 transition">
                            <i class="fa-solid fa-rocket"></i>
                            {{ $tenant->plan === 'basic' || $tenant->isOnTrial() ? 'Upgrade Plan' : 'Renew / Change Plan' }}
                        </button>
                        <a href="#billing"
                            class="px-4 py-3 rounded-xl bg-white/10 hover:bg-white/15 border border-white/10 text-slate-200 font-semibold text-sm flex items-center justify-center gap-2 transition">
                            <i class="fa-solid fa-file-invoice-dollar"></i> Billing &amp; Invoices
                        </a>
                    </div>
                </div>
            </div>
        </div>

        {{-- 2. Real-Time Plan Quotas & Resource Usage --}}
        <div>
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h3 class="text-lg font-bold text-gray-900">Resource Usage &amp; Plan Limits</h3>
                    <p class="text-xs text-gray-500">Real-time consumption across your outlets, staff accounts, and POS catalog.</p>
                </div>
                <span class="text-xs text-gray-500">
                    Plan Tier: <strong class="text-indigo-600 capitalize">{{ $currentPlan['name'] }}</strong>
                </span>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-5">
                {{-- Outlets Quota --}}
                <div class="bg-white rounded-2xl border border-gray-200 p-5 shadow-sm flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between mb-3">
                            <span class="text-xs font-semibold uppercase tracking-wider text-gray-500">Active Outlets</span>
                            <div class="w-9 h-9 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center">
                                <i class="fa-solid fa-store"></i>
                            </div>
                        </div>
                        <div class="flex items-baseline gap-1.5">
                            <span class="text-2xl font-black text-gray-900">{{ $usage['outlets_count'] }}</span>
                            <span class="text-sm text-gray-500">
                                / {{ $usage['outlets_max'] ?? 'Unlimited' }}
                            </span>
                        </div>
                        <div class="w-full h-2 bg-gray-100 rounded-full overflow-hidden mt-3">
                            <div class="h-full rounded-full {{ ($usage['outlets_max'] && $usage['outlets_count'] >= $usage['outlets_max']) ? 'bg-amber-500' : 'bg-indigo-600' }}"
                                style="width: {{ $usage['outlets_percent'] }}%"></div>
                        </div>
                    </div>
                    <div class="mt-4 pt-3 border-t border-gray-100 flex items-center justify-between text-xs">
                        @if ($usage['outlets_max'] && $usage['outlets_count'] >= $usage['outlets_max'])
                            <span class="text-amber-600 font-semibold">Limit reached — Upgrade for more</span>
                        @else
                            <span class="text-emerald-600 font-medium">Quota available</span>
                        @endif
                        <a href="{{ route('admin.outlets.index') }}" class="text-indigo-600 hover:underline font-medium">Manage →</a>
                    </div>
                </div>

                {{-- Staff / Users Quota --}}
                <div class="bg-white rounded-2xl border border-gray-200 p-5 shadow-sm flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between mb-3">
                            <span class="text-xs font-semibold uppercase tracking-wider text-gray-500">Staff &amp; Cashiers</span>
                            <div class="w-9 h-9 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center">
                                <i class="fa-solid fa-user-shield"></i>
                            </div>
                        </div>
                        <div class="flex items-baseline gap-1.5">
                            <span class="text-2xl font-black text-gray-900">{{ $usage['users_count'] }}</span>
                            <span class="text-sm text-gray-500">
                                / {{ $usage['users_max'] ?? 'Unlimited' }}
                            </span>
                        </div>
                        <div class="w-full h-2 bg-gray-100 rounded-full overflow-hidden mt-3">
                            <div class="h-full rounded-full {{ ($usage['users_max'] && $usage['users_count'] >= $usage['users_max']) ? 'bg-amber-500' : 'bg-emerald-500' }}"
                                style="width: {{ $usage['users_percent'] }}%"></div>
                        </div>
                    </div>
                    <div class="mt-4 pt-3 border-t border-gray-100 flex items-center justify-between text-xs">
                        @if ($usage['users_max'] && $usage['users_count'] >= $usage['users_max'])
                            <span class="text-amber-600 font-semibold">Limit reached — Upgrade for unlimited</span>
                        @else
                            <span class="text-emerald-600 font-medium">Active team accounts</span>
                        @endif
                        <a href="{{ route('admin.users.index') }}" class="text-indigo-600 hover:underline font-medium">Users →</a>
                    </div>
                </div>

                {{-- Products Quota --}}
                <div class="bg-white rounded-2xl border border-gray-200 p-5 shadow-sm flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between mb-3">
                            <span class="text-xs font-semibold uppercase tracking-wider text-gray-500">Products Catalog</span>
                            <div class="w-9 h-9 rounded-xl bg-violet-50 text-violet-600 flex items-center justify-center">
                                <i class="fa-solid fa-boxes-stacked"></i>
                            </div>
                        </div>
                        <div class="flex items-baseline gap-1.5">
                            <span class="text-2xl font-black text-gray-900">{{ number_format($usage['products_count']) }}</span>
                            <span class="text-sm text-gray-500">
                                / {{ $usage['products_max'] ?? 'Unlimited' }}
                            </span>
                        </div>
                        <div class="w-full h-2 bg-gray-100 rounded-full overflow-hidden mt-3">
                            <div class="h-full rounded-full bg-violet-600" style="width: {{ $usage['products_percent'] }}%"></div>
                        </div>
                    </div>
                    <div class="mt-4 pt-3 border-t border-gray-100 flex items-center justify-between text-xs">
                        <span class="text-gray-500">{{ $usage['customers_count'] }} registered customers</span>
                        <a href="{{ route('admin.products.index') }}" class="text-indigo-600 hover:underline font-medium">Catalog →</a>
                    </div>
                </div>

                {{-- Monthly POS Volume --}}
                <div class="bg-white rounded-2xl border border-gray-200 p-5 shadow-sm flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between mb-3">
                            <span class="text-xs font-semibold uppercase tracking-wider text-gray-500">Orders This Month</span>
                            <div class="w-9 h-9 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center">
                                <i class="fa-solid fa-cash-register"></i>
                            </div>
                        </div>
                        <div class="flex items-baseline gap-1.5">
                            <span class="text-2xl font-black text-gray-900">{{ number_format($usage['monthly_orders_count']) }}</span>
                            <span class="text-xs text-emerald-600 font-semibold">Unlimited Tx</span>
                        </div>
                        <p class="text-xs text-gray-500 mt-3">
                            Monthly GMV: <strong class="text-gray-800">Rp {{ number_format($usage['monthly_revenue'], 0, ',', '.') }}</strong>
                        </p>
                    </div>
                    <div class="mt-4 pt-3 border-t border-gray-100 flex items-center justify-between text-xs">
                        <span class="text-gray-500">Zero transaction commission</span>
                        <a href="{{ route('admin.orders.index') }}" class="text-indigo-600 hover:underline font-medium">Orders →</a>
                    </div>
                </div>
            </div>
        </div>

        {{-- 3. Interactive Plans & Pricing Section (#plans) --}}
        <div id="plans" class="scroll-mt-6 bg-slate-950 text-white rounded-3xl p-8 lg:p-10 shadow-xl border border-slate-800">
            <div class="flex flex-col md:flex-row md:items-end justify-between mb-10 gap-6">
                <div>
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-indigo-500/15 text-indigo-300 border border-indigo-500/30 mb-3">
                        <i class="fa-solid fa-bolt text-amber-400"></i> Flexible POS SaaS Plans
                    </span>
                    <h2 class="text-2xl lg:text-3xl font-black tracking-tight">Choose the Right Plan for Your Business</h2>
                    <p class="text-slate-400 text-sm mt-1">
                        Upgrade, switch billing cycles, or scale outlets anytime. Instant activation with zero downtime.
                    </p>
                </div>

                {{-- Monthly / Annual Toggle --}}
                <div class="inline-flex items-center bg-slate-900 border border-slate-800 p-1.5 rounded-2xl self-start">
                    <button type="button" @click="billingCycle = 'monthly'"
                        :class="billingCycle === 'monthly' ? 'bg-indigo-600 text-white shadow' : 'text-slate-400 hover:text-white'"
                        class="px-4 py-2 rounded-xl text-xs font-bold transition">
                        Monthly Billing
                    </button>
                    <button type="button" @click="billingCycle = 'annual'"
                        :class="billingCycle === 'annual' ? 'bg-indigo-600 text-white shadow' : 'text-slate-400 hover:text-white'"
                        class="px-4 py-2 rounded-xl text-xs font-bold transition flex items-center gap-1.5">
                        <span>Annual Billing</span>
                        <span class="px-2 py-0.5 text-[10px] rounded-full bg-emerald-500/20 text-emerald-300 border border-emerald-500/30">
                            Save 20%
                        </span>
                    </button>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 items-stretch">
                {{-- Starter (Basic) Card --}}
                @php $basicPlan = $plans['basic']; @endphp
                <div class="bg-slate-900/90 border {{ $tenant->plan === 'basic' && ! $tenant->isOnTrial() ? 'border-emerald-500/60 ring-1 ring-emerald-500/40' : 'border-slate-800' }} rounded-2xl p-7 flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-bold uppercase tracking-widest text-slate-400">{{ $basicPlan['name'] }}</span>
                            @if ($tenant->plan === 'basic' && ! $tenant->isOnTrial())
                                <span class="px-2.5 py-0.5 text-xs font-bold bg-emerald-500/20 text-emerald-300 rounded-full">Current Plan</span>
                            @endif
                        </div>
                        <div class="mt-4 flex items-baseline gap-1">
                            <span class="text-4xl font-black text-white">Free</span>
                            <span class="text-xs text-slate-400">Forever</span>
                        </div>
                        <p class="text-xs text-slate-400 mt-2">{{ $basicPlan['tagline'] }}</p>

                        <hr class="my-6 border-slate-800">

                        <ul class="space-y-3 text-sm text-slate-300">
                            @foreach ($basicPlan['highlights'] as $item)
                                <li class="flex items-center gap-2.5">
                                    <i class="fa-solid fa-check text-emerald-400 w-4"></i>
                                    <span>{{ $item }}</span>
                                </li>
                            @endforeach
                            <li class="flex items-center gap-2.5 text-slate-600">
                                <i class="fa-solid fa-xmark w-4"></i>
                                <span>Multi-Outlet &amp; Stock Transfers</span>
                            </li>
                            <li class="flex items-center gap-2.5 text-slate-600">
                                <i class="fa-solid fa-xmark w-4"></i>
                                <span>Finance &amp; Tax Reports</span>
                            </li>
                        </ul>
                    </div>

                    <div class="mt-8">
                        @if ($tenant->plan === 'basic' && ! $tenant->isOnTrial())
                            <button type="button" disabled
                                class="w-full py-3 rounded-xl bg-slate-800 text-slate-400 text-sm font-bold cursor-not-allowed">
                                Active Free Plan
                            </button>
                        @else
                            <form method="POST" action="{{ route('admin.subscription.plan.change') }}"
                                onsubmit="return confirm('Switch to the Starter (Free) plan? Multi-outlet and advanced report features will be limited to Starter quotas.');">
                                @csrf
                                @if ($isSuper && $tenant)
                                    <input type="hidden" name="tenant_id" value="{{ $tenant->id }}">
                                @endif
                                <input type="hidden" name="plan" value="basic">
                                <button type="submit"
                                    class="w-full py-3 rounded-xl border border-slate-700 hover:bg-slate-800 text-slate-300 text-sm font-semibold transition">
                                    Switch to Starter (Free)
                                </button>
                            </form>
                        @endif
                    </div>
                </div>

                {{-- Professional Card --}}
                @php $proPlan = $plans['professional']; @endphp
                <div class="bg-gradient-to-b from-indigo-600/95 via-indigo-700/95 to-purple-800/95 border-2 border-indigo-400 rounded-2xl p-7 flex flex-col justify-between relative shadow-2xl shadow-indigo-950/60">
                    <div class="absolute -top-3.5 left-1/2 -translate-x-1/2">
                        <span class="bg-amber-400 text-slate-950 text-xs font-black uppercase tracking-wider px-4 py-1 rounded-full shadow">
                            Most Popular
                        </span>
                    </div>

                    <div>
                        <div class="flex items-center justify-between mt-1">
                            <span class="text-xs font-bold uppercase tracking-widest text-indigo-200">{{ $proPlan['name'] }}</span>
                            @if ($tenant->plan === 'professional')
                                <span class="px-2.5 py-0.5 text-xs font-bold bg-white/20 text-white rounded-full">
                                    {{ $tenant->isOnTrial() ? 'Current Trial' : 'Current Plan' }}
                                </span>
                            @endif
                        </div>

                        <div class="mt-4">
                            <div x-show="billingCycle === 'monthly'" class="flex items-baseline gap-1">
                                <span class="text-4xl font-black text-white">Rp 299K</span>
                                <span class="text-xs text-indigo-200">/ month</span>
                            </div>
                            <div x-show="billingCycle === 'annual'" x-cloak class="flex items-baseline gap-1">
                                <span class="text-4xl font-black text-white">Rp 239K</span>
                                <span class="text-xs text-indigo-200">/ month (Rp 2.87M / yr)</span>
                            </div>
                        </div>
                        <p class="text-xs text-indigo-100 mt-2">{{ $proPlan['tagline'] }}</p>

                        <hr class="my-6 border-indigo-400/30">

                        <ul class="space-y-3 text-sm text-white">
                            @foreach ($proPlan['highlights'] as $item)
                                <li class="flex items-center gap-2.5">
                                    <i class="fa-solid fa-check text-amber-300 w-4"></i>
                                    <span>{{ $item }}</span>
                                </li>
                            @endforeach
                        </ul>
                    </div>

                    <div class="mt-8">
                        <button type="button"
                            @click="openCheckout('professional', 'Professional', {{ $proPlan['monthly_price'] }}, {{ $proPlan['annual_price'] }})"
                            class="w-full py-3.5 rounded-xl bg-white hover:bg-indigo-50 text-indigo-700 font-black text-sm shadow-lg transition flex items-center justify-center gap-2">
                            <i class="fa-solid fa-bolt"></i>
                            @if ($tenant->plan === 'professional' && $tenant->isSubscribed())
                                Renew / Extend Professional
                            @else
                                Upgrade to Professional
                            @endif
                        </button>
                    </div>
                </div>

                {{-- Enterprise Card --}}
                @php $entPlan = $plans['enterprise']; @endphp
                <div class="bg-slate-900/90 border {{ $tenant->plan === 'enterprise' ? 'border-amber-400/60 ring-1 ring-amber-400/40' : 'border-slate-800' }} rounded-2xl p-7 flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-bold uppercase tracking-widest text-amber-400">{{ $entPlan['name'] }}</span>
                            @if ($tenant->plan === 'enterprise')
                                <span class="px-2.5 py-0.5 text-xs font-bold bg-amber-500/20 text-amber-300 rounded-full">Current Plan</span>
                            @endif
                        </div>

                        <div class="mt-4">
                            <div x-show="billingCycle === 'monthly'" class="flex items-baseline gap-1">
                                <span class="text-4xl font-black text-white">Rp 799K</span>
                                <span class="text-xs text-slate-400">/ month</span>
                            </div>
                            <div x-show="billingCycle === 'annual'" x-cloak class="flex items-baseline gap-1">
                                <span class="text-4xl font-black text-white">Rp 639K</span>
                                <span class="text-xs text-slate-400">/ month (Rp 7.67M / yr)</span>
                            </div>
                        </div>
                        <p class="text-xs text-slate-400 mt-2">{{ $entPlan['tagline'] }}</p>

                        <hr class="my-6 border-slate-800">

                        <ul class="space-y-3 text-sm text-slate-300">
                            @foreach ($entPlan['highlights'] as $item)
                                <li class="flex items-center gap-2.5">
                                    <i class="fa-solid fa-check text-emerald-400 w-4"></i>
                                    <span>{{ $item }}</span>
                                </li>
                            @endforeach
                        </ul>
                    </div>

                    <div class="mt-8 space-y-2">
                        <button type="button"
                            @click="openCheckout('enterprise', 'Enterprise', {{ $entPlan['monthly_price'] }}, {{ $entPlan['annual_price'] }})"
                            class="w-full py-3.5 rounded-xl bg-gradient-to-r from-amber-500 to-orange-500 hover:from-amber-600 hover:to-orange-600 text-slate-950 font-black text-sm transition flex items-center justify-center gap-2">
                            <i class="fa-solid fa-crown"></i>
                            @if ($tenant->plan === 'enterprise' && $tenant->isSubscribed())
                                Renew Enterprise Plan
                            @else
                                Activate Enterprise Plan
                            @endif
                        </button>
                    </div>
                </div>
            </div>
        </div>

        {{-- 4. Unlocked POS Capabilities & Feature Matrix --}}
        <x-admin-card title="POS Feature Entitlements ({{ $currentPlan['name'] }} Plan)">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach ($featuresMap as $featureKey => $meta)
                    @php
                        $enabled = $tenant->hasFeature($featureKey);
                    @endphp
                    <div class="flex items-center justify-between p-3.5 rounded-xl border {{ $enabled ? 'bg-emerald-50/40 border-emerald-200/80' : 'bg-gray-50 border-gray-200 opacity-75' }}">
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 rounded-lg flex items-center justify-center {{ $enabled ? 'bg-emerald-100 text-emerald-700' : 'bg-gray-200 text-gray-500' }}">
                                <i class="fa-solid {{ $meta['icon'] }}"></i>
                            </div>
                            <div>
                                <p class="text-sm font-semibold {{ $enabled ? 'text-gray-900' : 'text-gray-500' }}">
                                    {{ $meta['label'] }}
                                </p>
                                <p class="text-xs {{ $enabled ? 'text-emerald-600' : 'text-gray-400' }}">
                                    {{ $enabled ? 'Included in your plan' : 'Requires Professional / Enterprise' }}
                                </p>
                            </div>
                        </div>
                        @if ($enabled)
                            <span class="w-6 h-6 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center text-xs">
                                <i class="fa-solid fa-check"></i>
                            </span>
                        @else
                            <a href="#plans" class="px-2 py-1 text-[11px] font-bold bg-indigo-50 text-indigo-600 rounded-md hover:bg-indigo-100">
                                <i class="fa-solid fa-lock mr-1"></i>PRO
                            </a>
                        @endif
                    </div>
                @endforeach
            </div>
        </x-admin-card>

        {{-- 5. Billing Profile & Invoice History (#billing) --}}
        <div id="billing" class="scroll-mt-6 grid grid-cols-1 lg:grid-cols-12 gap-6">
            {{-- Billing Profile Form --}}
            <div class="lg:col-span-5">
                <x-admin-card title="Billing Profile & Payment Settings">
                    <form method="POST" action="{{ route('admin.subscription.billing.update') }}" class="space-y-4">
                        @csrf
                        @method('PUT')
                        @if ($isSuper && $tenant)
                            <input type="hidden" name="tenant_id" value="{{ $tenant->id }}">
                        @endif

                        <div>
                            <label class="block text-xs font-semibold uppercase text-gray-500 mb-1">Company / Business Billing Name</label>
                            <input type="text" name="billing_name" required
                                value="{{ old('billing_name', $billingProfile['billing_name']) }}"
                                class="w-full px-3.5 py-2.5 border border-gray-300 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500">
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-semibold uppercase text-gray-500 mb-1">Billing Email</label>
                                <input type="email" name="billing_email" required
                                    value="{{ old('billing_email', $billingProfile['billing_email']) }}"
                                    class="w-full px-3.5 py-2.5 border border-gray-300 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold uppercase text-gray-500 mb-1">Phone / WhatsApp</label>
                                <input type="text" name="billing_phone"
                                    value="{{ old('billing_phone', $billingProfile['billing_phone']) }}"
                                    placeholder="+62 812..."
                                    class="w-full px-3.5 py-2.5 border border-gray-300 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500">
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-semibold uppercase text-gray-500 mb-1">Tax ID (NPWP)</label>
                                <input type="text" name="tax_id"
                                    value="{{ old('tax_id', $billingProfile['tax_id']) }}"
                                    placeholder="00.000.000.0-000.000"
                                    class="w-full px-3.5 py-2.5 border border-gray-300 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold uppercase text-gray-500 mb-1">Preferred Payment Method</label>
                                <select name="default_payment_method"
                                    class="w-full px-3.5 py-2.5 border border-gray-300 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500">
                                    <option value="qris" @selected(($billingProfile['default_payment_method'] ?? '') === 'qris')>QRIS Instant</option>
                                    <option value="virtual_account" @selected(($billingProfile['default_payment_method'] ?? '') === 'virtual_account')>Bank Virtual Account (BCA/Mandiri)</option>
                                    <option value="credit_card" @selected(($billingProfile['default_payment_method'] ?? '') === 'credit_card')>Credit / Debit Card</option>
                                    <option value="bank_transfer" @selected(($billingProfile['default_payment_method'] ?? '') === 'bank_transfer')>Manual Bank Transfer</option>
                                </select>
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold uppercase text-gray-500 mb-1">Billing Address</label>
                            <textarea name="billing_address" rows="2"
                                class="w-full px-3.5 py-2.5 border border-gray-300 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500"
                                placeholder="Street address, city, postal code">{{ old('billing_address', $billingProfile['billing_address']) }}</textarea>
                        </div>

                        <div class="pt-1">
                            <label class="inline-flex items-center gap-2.5 cursor-pointer">
                                <input type="checkbox" name="auto_renew" value="1"
                                    @checked(old('auto_renew', $billingProfile['auto_renew']))
                                    class="w-4 h-4 text-indigo-600 rounded border-gray-300 focus:ring-indigo-500">
                                <span class="text-sm text-gray-700 font-medium">Automatically renew subscription before expiry</span>
                            </label>
                        </div>

                        <div class="pt-3 flex items-center justify-between border-t border-gray-100">
                            @if ($tenant->plan !== 'basic')
                                <button type="button" @click="cancelModalOpen = true"
                                    class="text-xs font-semibold text-red-600 hover:text-red-800">
                                    Cancel or Downgrade Plan
                                </button>
                            @else
                                <span></span>
                            @endif

                            <x-admin-button type="submit" variant="primary" icon="fa-solid fa-save">
                                Save Billing Profile
                            </x-admin-button>
                        </div>
                    </form>
                </x-admin-card>
            </div>

            {{-- Invoices & Billing History --}}
            <div class="lg:col-span-7">
                <x-admin-card title="Invoices & Subscription History">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 text-sm">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Invoice</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Plan &amp; Period</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Method</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Amount</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Status</th>
                                    <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase">Receipt</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 bg-white">
                                @foreach ($billingHistory as $inv)
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-4 py-3.5">
                                            <div class="font-mono text-xs font-bold text-indigo-600">{{ $inv['id'] ?? 'INV-001' }}</div>
                                            <div class="text-xs text-gray-500">
                                                {{ \Carbon\Carbon::parse($inv['date'] ?? now())->format('d M Y') }}
                                            </div>
                                        </td>
                                        <td class="px-4 py-3.5">
                                            <div class="font-semibold text-gray-900">{{ $inv['description'] ?? ($inv['plan_name'] ?? 'Subscription') }}</div>
                                            <div class="text-xs text-gray-500">
                                                {{ $inv['period_start'] ?? '-' }} → {{ $inv['period_end'] ?? '-' }}
                                            </div>
                                        </td>
                                        <td class="px-4 py-3.5 text-xs text-gray-600">
                                            {{ $inv['payment_method'] ?? 'Online' }}
                                        </td>
                                        <td class="px-4 py-3.5 font-semibold text-gray-900">
                                            @if (($inv['amount'] ?? 0) > 0)
                                                Rp {{ number_format($inv['amount'], 0, ',', '.') }}
                                            @else
                                                Free
                                            @endif
                                        </td>
                                        <td class="px-4 py-3.5">
                                            @php $st = $inv['status'] ?? 'paid'; @endphp
                                            @if ($st === 'paid' || $st === 'active')
                                                <span class="px-2.5 py-0.5 text-xs font-semibold bg-emerald-100 text-emerald-800 rounded-full capitalize">{{ $st }}</span>
                                            @elseif ($st === 'trial')
                                                <span class="px-2.5 py-0.5 text-xs font-semibold bg-blue-100 text-blue-800 rounded-full">Trial</span>
                                            @else
                                                <span class="px-2.5 py-0.5 text-xs font-semibold bg-gray-100 text-gray-700 rounded-full capitalize">{{ $st }}</span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3.5 text-right">
                                            <button type="button"
                                                @click="openInvoice(@js($inv))"
                                                class="px-2.5 py-1.5 text-xs font-semibold text-indigo-600 hover:bg-indigo-50 rounded-lg transition">
                                                <i class="fa-solid fa-print mr-1"></i> View
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </x-admin-card>
            </div>
        </div>

        {{-- =====================================================================
             CHECKOUT / UPGRADE MODAL
        ===================================================================== --}}
        <div x-show="checkoutOpen" x-cloak
            class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/70 backdrop-blur-sm p-4">
            <div @click.away="checkoutOpen = false"
                class="bg-white rounded-3xl shadow-2xl max-w-lg w-full overflow-hidden border border-gray-200">
                <div class="bg-gradient-to-r from-indigo-600 to-violet-700 px-6 py-5 text-white flex items-center justify-between">
                    <div>
                        <span class="text-xs uppercase tracking-wider text-indigo-200 font-semibold">Instant Plan Activation</span>
                        <h3 class="text-xl font-black mt-0.5" x-text="'Subscribe to ' + selectedPlanName"></h3>
                    </div>
                    <button type="button" @click="checkoutOpen = false" class="text-white/80 hover:text-white">
                        <i class="fa-solid fa-xmark text-lg"></i>
                    </button>
                </div>

                <form method="POST" action="{{ route('admin.subscription.plan.change') }}" class="p-6 space-y-5">
                    @csrf
                    @if ($isSuper && $tenant)
                        <input type="hidden" name="tenant_id" value="{{ $tenant->id }}">
                    @endif
                    <input type="hidden" name="plan" :value="selectedPlan">

                    {{-- Billing Cycle Selection --}}
                    <div>
                        <label class="block text-xs font-bold uppercase text-gray-500 mb-2">1. Select Billing Cycle</label>
                        <div class="grid grid-cols-2 gap-3">
                            <label class="cursor-pointer border rounded-2xl p-3.5 flex flex-col justify-between transition"
                                :class="billingCycle === 'monthly' ? 'border-indigo-600 bg-indigo-50/50 ring-2 ring-indigo-600/20' : 'border-gray-200 hover:border-gray-300'">
                                <div class="flex items-center justify-between">
                                    <span class="text-sm font-bold text-gray-900">Monthly</span>
                                    <input type="radio" name="billing_cycle" value="monthly" x-model="billingCycle" class="text-indigo-600">
                                </div>
                                <p class="text-xs text-gray-500 mt-1" x-text="formatRp(selectedMonthlyPrice) + ' / month'"></p>
                            </label>

                            <label class="cursor-pointer border rounded-2xl p-3.5 flex flex-col justify-between transition relative"
                                :class="billingCycle === 'annual' ? 'border-indigo-600 bg-indigo-50/50 ring-2 ring-indigo-600/20' : 'border-gray-200 hover:border-gray-300'">
                                <span class="absolute -top-2.5 right-3 bg-emerald-500 text-white text-[10px] font-bold px-2 py-0.5 rounded-full">
                                    SAVE 20%
                                </span>
                                <div class="flex items-center justify-between">
                                    <span class="text-sm font-bold text-gray-900">Annual (1 Year)</span>
                                    <input type="radio" name="billing_cycle" value="annual" x-model="billingCycle" class="text-indigo-600">
                                </div>
                                <p class="text-xs text-gray-500 mt-1" x-text="formatRp(selectedAnnualPrice) + ' / year'"></p>
                            </label>
                        </div>
                    </div>

                    {{-- Payment Method Selection --}}
                    <div>
                        <label class="block text-xs font-bold uppercase text-gray-500 mb-2">2. Select Payment Method</label>
                        <div class="grid grid-cols-2 gap-2.5 text-sm">
                            <label class="cursor-pointer border rounded-xl px-3.5 py-2.5 flex items-center gap-2.5"
                                :class="paymentMethod === 'qris' ? 'border-indigo-600 bg-indigo-50/40 font-semibold text-indigo-900' : 'border-gray-200 text-gray-700'">
                                <input type="radio" name="payment_method" value="qris" x-model="paymentMethod" class="text-indigo-600">
                                <i class="fa-solid fa-qrcode text-indigo-600"></i>
                                <span>QRIS Instant</span>
                            </label>
                            <label class="cursor-pointer border rounded-xl px-3.5 py-2.5 flex items-center gap-2.5"
                                :class="paymentMethod === 'virtual_account' ? 'border-indigo-600 bg-indigo-50/40 font-semibold text-indigo-900' : 'border-gray-200 text-gray-700'">
                                <input type="radio" name="payment_method" value="virtual_account" x-model="paymentMethod" class="text-indigo-600">
                                <i class="fa-solid fa-building-columns text-indigo-600"></i>
                                <span>Virtual Account</span>
                            </label>
                            <label class="cursor-pointer border rounded-xl px-3.5 py-2.5 flex items-center gap-2.5"
                                :class="paymentMethod === 'credit_card' ? 'border-indigo-600 bg-indigo-50/40 font-semibold text-indigo-900' : 'border-gray-200 text-gray-700'">
                                <input type="radio" name="payment_method" value="credit_card" x-model="paymentMethod" class="text-indigo-600">
                                <i class="fa-regular fa-credit-card text-indigo-600"></i>
                                <span>Credit / Debit Card</span>
                            </label>
                            <label class="cursor-pointer border rounded-xl px-3.5 py-2.5 flex items-center gap-2.5"
                                :class="paymentMethod === 'bank_transfer' ? 'border-indigo-600 bg-indigo-50/40 font-semibold text-indigo-900' : 'border-gray-200 text-gray-700'">
                                <input type="radio" name="payment_method" value="bank_transfer" x-model="paymentMethod" class="text-indigo-600">
                                <i class="fa-solid fa-money-bill-transfer text-indigo-600"></i>
                                <span>Bank Transfer</span>
                            </label>
                        </div>
                    </div>

                    {{-- Promo Code --}}
                    <div>
                        <label class="block text-xs font-bold uppercase text-gray-500 mb-1">Promo / Voucher Code <span class="text-gray-400 font-normal">(Optional — try POSPRO20)</span></label>
                        <input type="text" name="coupon_code" x-model="couponCode"
                            placeholder="Enter promo code (e.g. POSPRO20)"
                            class="w-full px-3.5 py-2 border border-gray-300 rounded-xl text-sm uppercase focus:ring-2 focus:ring-indigo-500">
                    </div>

                    {{-- Order Summary --}}
                    <div class="bg-gray-50 rounded-2xl p-4 space-y-2 border border-gray-200 text-sm">
                        <div class="flex justify-between text-gray-600">
                            <span x-text="selectedPlanName + ' Plan (' + (billingCycle === 'annual' ? '12 Months' : '1 Month') + ')'"></span>
                            <span class="font-medium text-gray-900" x-text="formatRp(basePrice())"></span>
                        </div>
                        <div x-show="discountAmount() > 0" class="flex justify-between text-emerald-600 font-medium">
                            <span>Promo Discount (20%)</span>
                            <span x-text="'- ' + formatRp(discountAmount())"></span>
                        </div>
                        <div class="border-t border-gray-200 pt-2 flex justify-between text-base font-black text-gray-900">
                            <span>Total Due Today</span>
                            <span class="text-indigo-600" x-text="formatRp(finalPrice())"></span>
                        </div>
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-2">
                        <button type="button" @click="checkoutOpen = false"
                            class="px-4 py-2.5 rounded-xl border border-gray-300 text-gray-700 text-sm font-semibold hover:bg-gray-50">
                            Cancel
                        </button>
                        <button type="submit"
                            class="px-6 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-bold shadow-lg shadow-indigo-600/20 flex items-center gap-2">
                            <i class="fa-solid fa-lock"></i> Confirm &amp; Activate Plan
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- =====================================================================
             PRINTABLE INVOICE MODAL
        ===================================================================== --}}
        <div x-show="invoiceModalOpen" x-cloak
            class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/70 backdrop-blur-sm p-4">
            <div @click.away="invoiceModalOpen = false"
                class="bg-white rounded-3xl shadow-2xl max-w-xl w-full overflow-hidden border border-gray-200">
                <div class="px-6 py-4 bg-slate-900 text-white flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <i class="fa-solid fa-file-invoice-dollar text-emerald-400"></i>
                        <span class="font-bold text-sm" x-text="activeInvoice ? activeInvoice.id : 'Invoice'"></span>
                    </div>
                    <div class="flex items-center gap-3">
                        <button type="button" onclick="window.print()"
                            class="px-3 py-1 rounded-lg bg-white/10 hover:bg-white/20 text-xs font-semibold">
                            <i class="fa-solid fa-print mr-1"></i> Print
                        </button>
                        <button type="button" @click="invoiceModalOpen = false" class="text-white/70 hover:text-white">
                            <i class="fa-solid fa-xmark"></i>
                        </button>
                    </div>
                </div>

                <div class="p-6 space-y-6 text-sm" x-show="activeInvoice">
                    <div class="flex justify-between items-start border-b border-gray-200 pb-4">
                        <div>
                            <h4 class="text-lg font-black text-gray-900">{{ config('app.name', 'POS ERP') }}</h4>
                            <p class="text-xs text-gray-500">Official Subscription Tax Receipt</p>
                        </div>
                        <div class="text-right">
                            <span class="px-2.5 py-1 text-xs font-bold uppercase rounded-full bg-emerald-100 text-emerald-800"
                                x-text="activeInvoice?.status || 'PAID'"></span>
                            <p class="text-xs text-gray-500 mt-1" x-text="activeInvoice?.id"></p>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4 text-xs">
                        <div>
                            <p class="text-gray-400 uppercase font-semibold">Billed To</p>
                            <p class="font-bold text-gray-900 text-sm mt-0.5">{{ $billingProfile['billing_name'] }}</p>
                            <p class="text-gray-600">{{ $billingProfile['billing_email'] }}</p>
                            @if (! empty($billingProfile['tax_id']))
                                <p class="text-gray-600 mt-0.5">NPWP: {{ $billingProfile['tax_id'] }}</p>
                            @endif
                        </div>
                        <div class="text-right">
                            <p class="text-gray-400 uppercase font-semibold">Service Period</p>
                            <p class="font-semibold text-gray-900 mt-0.5"
                                x-text="(activeInvoice?.period_start || '-') + ' — ' + (activeInvoice?.period_end || '-')"></p>
                            <p class="text-gray-500 mt-1" x-text="'Method: ' + (activeInvoice?.payment_method || 'Online')"></p>
                        </div>
                    </div>

                    <div class="bg-gray-50 rounded-2xl p-4 border border-gray-200">
                        <div class="flex justify-between items-center pb-3 border-b border-gray-200">
                            <div>
                                <p class="font-bold text-gray-900" x-text="activeInvoice?.description || 'POS Subscription'"></p>
                                <p class="text-xs text-gray-500 capitalize" x-text="'Cycle: ' + (activeInvoice?.billing_cycle || 'monthly')"></p>
                            </div>
                            <span class="font-bold text-gray-900" x-text="formatRp(activeInvoice?.amount || 0)"></span>
                        </div>
                        <div class="flex justify-between items-center pt-3 text-base font-black text-gray-900">
                            <span>Total Paid</span>
                            <span class="text-indigo-600" x-text="formatRp(activeInvoice?.amount || 0)"></span>
                        </div>
                    </div>

                    <div class="flex justify-end">
                        <button type="button" @click="invoiceModalOpen = false"
                            class="px-5 py-2 rounded-xl bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold text-xs">
                            Close
                        </button>
                    </div>
                </div>
            </div>
        </div>

        {{-- =====================================================================
             CANCEL / DOWNGRADE MODAL
        ===================================================================== --}}
        <div x-show="cancelModalOpen" x-cloak
            class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/70 backdrop-blur-sm p-4">
            <div @click.away="cancelModalOpen = false"
                class="bg-white rounded-3xl shadow-2xl max-w-md w-full p-6 border border-gray-200 space-y-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-red-100 text-red-600 flex items-center justify-center">
                        <i class="fa-solid fa-triangle-exclamation"></i>
                    </div>
                    <div>
                        <h3 class="text-base font-bold text-gray-900">Manage Subscription Cancellation</h3>
                        <p class="text-xs text-gray-500">Choose how you would like to adjust your plan.</p>
                    </div>
                </div>

                <form method="POST" action="{{ route('admin.subscription.cancel') }}" class="space-y-4">
                    @csrf
                    @if ($isSuper && $tenant)
                        <input type="hidden" name="tenant_id" value="{{ $tenant->id }}">
                    @endif

                    <label class="flex items-start gap-3 p-3.5 border border-gray-200 rounded-xl cursor-pointer hover:bg-gray-50">
                        <input type="radio" name="immediate_downgrade" value="0" checked class="mt-1 text-indigo-600">
                        <div class="text-xs">
                            <span class="font-bold text-gray-900 block">Turn off Auto-Renewal Only (Recommended)</span>
                            <span class="text-gray-500">Keep all your current plan features until your current period ends.</span>
                        </div>
                    </label>

                    <label class="flex items-start gap-3 p-3.5 border border-red-200 bg-red-50/30 rounded-xl cursor-pointer hover:bg-red-50/60">
                        <input type="radio" name="immediate_downgrade" value="1" class="mt-1 text-red-600">
                        <div class="text-xs">
                            <span class="font-bold text-red-800 block">Downgrade to Starter (Free) Immediately</span>
                            <span class="text-red-600/80">Switch to the Free Forever plan now (1 Outlet, up to 3 Users).</span>
                        </div>
                    </label>

                    <div class="flex justify-end gap-2 pt-2">
                        <button type="button" @click="cancelModalOpen = false"
                            class="px-4 py-2 rounded-xl border border-gray-300 text-xs font-semibold text-gray-700">
                            Keep My Plan
                        </button>
                        <button type="submit"
                            class="px-4 py-2 rounded-xl bg-red-600 hover:bg-red-700 text-xs font-bold text-white">
                            Confirm Change
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
@endsection
