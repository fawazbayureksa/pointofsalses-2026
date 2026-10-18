@extends('layouts.admin')

@section('title', 'Users')

@section('page-title', 'Users & Trial Subscriptions Management')

@section('content')
    <div class="space-y-6">
        @if ($errors->any())
            <div class="bg-red-50 border border-red-200 rounded-lg p-4">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <i class="fa-solid fa-circle-exclamation text-red-400"></i>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-sm font-medium text-red-800">Errors</h3>
                        <div class="mt-2 text-sm text-red-700">
                            <ul class="list-disc list-inside">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        @if (session('success'))
            <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <i class="fa-solid fa-circle-check text-green-400"></i>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-green-800">{{ session('success') }}</p>
                    </div>
                </div>
            </div>
        @endif

        {{-- Summary Stat Cards --}}
        @if (isset($stats))
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
                <div class="bg-white border border-gray-200 rounded-xl p-4 shadow-sm flex items-center justify-between">
                    <div>
                        <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Total Users</p>
                        <p class="text-2xl font-bold text-gray-900 mt-1">{{ $stats['total'] ?? 0 }}</p>
                    </div>
                    <div class="w-10 h-10 rounded-lg bg-slate-100 text-slate-600 flex items-center justify-center">
                        <i class="fa-solid fa-users"></i>
                    </div>
                </div>

                <div class="bg-white border border-gray-200 rounded-xl p-4 shadow-sm flex items-center justify-between">
                    <div>
                        <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Active Users</p>
                        <p class="text-2xl font-bold text-emerald-600 mt-1">{{ $stats['active'] ?? 0 }}</p>
                    </div>
                    <div class="w-10 h-10 rounded-lg bg-emerald-100 text-emerald-600 flex items-center justify-center">
                        <i class="fa-solid fa-user-check"></i>
                    </div>
                </div>

                <div class="bg-white border border-gray-200 rounded-xl p-4 shadow-sm flex items-center justify-between">
                    <div>
                        <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">On Free Trial</p>
                        <p class="text-2xl font-bold text-blue-600 mt-1">{{ $stats['on_trial'] ?? 0 }}</p>
                    </div>
                    <div class="w-10 h-10 rounded-lg bg-blue-100 text-blue-600 flex items-center justify-center">
                        <i class="fa-solid fa-gift"></i>
                    </div>
                </div>

                <div class="bg-white border border-gray-200 rounded-xl p-4 shadow-sm flex items-center justify-between">
                    <div>
                        <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Subscribed (Paid)</p>
                        <p class="text-2xl font-bold text-indigo-600 mt-1">{{ $stats['subscribed'] ?? 0 }}</p>
                    </div>
                    <div class="w-10 h-10 rounded-lg bg-indigo-100 text-indigo-600 flex items-center justify-center">
                        <i class="fa-solid fa-crown"></i>
                    </div>
                </div>

                <div class="bg-white border border-gray-200 rounded-xl p-4 shadow-sm flex items-center justify-between">
                    <div>
                        <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Expired Trial / Sub</p>
                        <p class="text-2xl font-bold text-red-600 mt-1">{{ $stats['expired'] ?? 0 }}</p>
                    </div>
                    <div class="w-10 h-10 rounded-lg bg-red-100 text-red-600 flex items-center justify-center">
                        <i class="fa-solid fa-hourglass-end"></i>
                    </div>
                </div>
            </div>
        @endif

        <x-admin-card title="All Users">
            {{-- Filter & Search Bar --}}
            <div class="mb-6 flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                <form method="GET" action="{{ route('admin.users.index') }}" class="flex flex-wrap items-center gap-3 flex-1">
                    <div class="relative">
                        <input type="text" name="search" value="{{ request('search') }}"
                            placeholder="Search name, email, phone, business..."
                            class="pl-9 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent w-72 text-sm">
                        <i class="fa-solid fa-magnifying-glass text-gray-400 absolute left-3 top-2.5 text-sm"></i>
                    </div>

                    <select name="role" onchange="this.form.submit()"
                        class="px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm">
                        <option value="">All Roles</option>
                        @foreach ($roles ?? [] as $role)
                            <option value="{{ $role->name }}" {{ request('role') === $role->name ? 'selected' : '' }}>
                                {{ ucwords(str_replace('_', ' ', $role->name)) }}
                            </option>
                        @endforeach
                    </select>

                    <select name="subscription_status" onchange="this.form.submit()"
                        class="px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm">
                        <option value="">All Trial / Subscription</option>
                        <option value="trial_active" {{ request('subscription_status') === 'trial_active' ? 'selected' : '' }}>
                            Free Trial (Active)
                        </option>
                        <option value="trial_expired" {{ request('subscription_status') === 'trial_expired' ? 'selected' : '' }}>
                            Free Trial (Expired)
                        </option>
                        <option value="active" {{ request('subscription_status') === 'active' ? 'selected' : '' }}>
                            Subscribed (Active)
                        </option>
                        <option value="expired" {{ request('subscription_status') === 'expired' ? 'selected' : '' }}>
                            Expired
                        </option>
                        <option value="cancelled" {{ request('subscription_status') === 'cancelled' ? 'selected' : '' }}>
                            Cancelled
                        </option>
                        <option value="exempt" {{ request('subscription_status') === 'exempt' ? 'selected' : '' }}>
                            Exempt (Free Access)
                        </option>
                    </select>

                    <select name="status" onchange="this.form.submit()"
                        class="px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm">
                        <option value="">All Account Status</option>
                        <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                    </select>

                    <button type="submit"
                        class="px-3.5 py-2 bg-slate-800 text-white text-sm font-medium rounded-lg hover:bg-slate-700 transition">
                        Filter
                    </button>

                    @if (request()->hasAny(['search', 'role', 'status', 'subscription_status']))
                        <a href="{{ route('admin.users.index') }}"
                            class="px-3 py-2 text-sm text-gray-600 bg-gray-100 rounded-lg hover:bg-gray-200 transition">
                            Reset
                        </a>
                    @endif
                </form>

                @can('create users')
                    <x-admin-button variant="primary" icon="fa-solid fa-plus" x-data @click="$dispatch('open-create-modal')">
                        Add New User
                    </x-admin-button>
                @endcan
            </div>

            @php
                $tableHeaders = [
                    [
                        'label' => 'ID',
                        'slot' => fn($user) => '<span class="text-xs font-semibold text-gray-500">#' . $user->id . '</span>',
                    ],
                    [
                        'label' => 'User Info',
                        'slot' => function ($user) {
                            $initial = strtoupper(substr($user->name ?? 'U', 0, 1));
                            $avatarHtml = $user->avatar
                                ? '<img src="' . e(asset('storage/' . $user->avatar)) . '" alt="" class="w-9 h-9 rounded-full object-cover border border-gray-200">'
                                : '<div class="w-9 h-9 rounded-full bg-indigo-100 text-indigo-700 flex items-center justify-center font-bold text-sm">' . e($initial) . '</div>';

                            $phoneHtml = $user->phone
                                ? '<div class="text-xs text-gray-500 mt-0.5"><i class="fa-solid fa-phone text-[10px] mr-1 text-gray-400"></i>' . e($user->phone) . '</div>'
                                : '';

                            $pinBadge = $user->hasPin()
                                ? '<span class="ml-1.5 inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-medium bg-slate-100 text-slate-600" title="Cashier PIN Configured"><i class="fa-solid fa-key mr-1 text-[9px]"></i>PIN</span>'
                                : '';

                            return '<div class="flex items-center space-x-3">'
                                . $avatarHtml
                                . '<div>'
                                . '<div class="text-sm font-semibold text-gray-900 flex items-center">' . e($user->name) . $pinBadge . '</div>'
                                . '<div class="text-xs text-gray-500">' . e($user->email) . '</div>'
                                . $phoneHtml
                                . '</div></div>';
                        },
                    ],
                    [
                        'label' => 'Business & Plan',
                        'slot' => function ($user) {
                            if ($user->isSuperAdmin()) {
                                return '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-slate-800 text-white"><i class="fa-solid fa-shield-halved mr-1.5"></i>System / Super Admin</span>';
                            }

                            $tenant = $user->tenant;
                            if (! $tenant) {
                                return '<span class="text-xs text-gray-400 italic">No Business Linked</span>';
                            }

                            $plan = strtolower($tenant->plan ?? 'professional');
                            $planColors = [
                                'basic'        => 'bg-gray-100 text-gray-700 border-gray-200',
                                'professional' => 'bg-indigo-50 text-indigo-700 border-indigo-200',
                                'enterprise'   => 'bg-purple-50 text-purple-700 border-purple-200',
                            ];
                            $planClass = $planColors[$plan] ?? 'bg-indigo-50 text-indigo-700 border-indigo-200';
                            $planLabel = $plan === 'basic' ? 'Starter (Free)' : ucfirst($plan);

                            return '<div>'
                                . '<div class="text-sm font-medium text-gray-900"><i class="fa-solid fa-building text-gray-400 mr-1.5 text-xs"></i>' . e($tenant->name) . '</div>'
                                . '<div class="mt-1"><span class="inline-flex items-center px-2 py-0.5 rounded border text-[11px] font-semibold ' . $planClass . '">' . e($planLabel) . '</span></div>'
                                . '</div>';
                        },
                    ],
                    [
                        'label' => 'Role & Outlet',
                        'slot' => function ($user) {
                            $roles = $user->roles->pluck('name')->map(function ($role) {
                                $colors = [
                                    'super_admin'  => 'bg-slate-800 text-white',
                                    'tenant_admin' => 'bg-blue-100 text-blue-800',
                                    'manager'      => 'bg-amber-100 text-amber-800',
                                    'cashier'      => 'bg-teal-100 text-teal-800',
                                ];
                                $cls = $colors[$role] ?? 'bg-gray-100 text-gray-800';
                                $label = ucwords(str_replace('_', ' ', $role));
                                return '<span class="inline-flex px-2 py-0.5 rounded-full text-xs font-semibold ' . $cls . '">' . e($label) . '</span>';
                            })->join(' ');

                            $outletName = $user->outlets->pluck('name')->join(', ');
                            $outletHtml = $outletName
                                ? '<div class="text-xs text-gray-500 mt-1"><i class="fa-solid fa-store mr-1 text-gray-400"></i>' . e($outletName) . '</div>'
                                : '<div class="text-xs text-gray-400 mt-1"><i class="fa-solid fa-store mr-1"></i>All / No Outlet</div>';

                            return '<div>' . ($roles ?: '<span class="text-xs text-gray-400">No Role</span>') . $outletHtml . '</div>';
                        },
                    ],
                    [
                        'label' => 'Trial / Subscription',
                        'slot' => function ($user) {
                            if ($user->isSuperAdmin()) {
                                return '<span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-slate-100 text-slate-700"><i class="fa-solid fa-infinity mr-1"></i>Unlimited</span>';
                            }

                            $tenant = $user->tenant;
                            if (! $tenant) {
                                return '<span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-600">Not Configured</span>';
                            }

                            $badges = '';

                            if ($tenant->isExempt()) {
                                $badges .= '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-amber-100 text-amber-800"><i class="fa-solid fa-flag mr-1"></i>Exempt (Free Access)</span>';
                            } elseif ($tenant->subscription_status === 'trial') {
                                if ($tenant->isOnTrial()) {
                                    $daysLeft = $tenant->trialDaysLeft();
                                    $badges .= '<div><span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-blue-100 text-blue-800"><i class="fa-solid fa-gift mr-1"></i>Subscribed: Free Trial</span></div>';
                                    $badges .= '<div class="text-xs font-medium ' . ($daysLeft <= 3 ? 'text-amber-600' : 'text-blue-600') . ' mt-1"><i class="fa-regular fa-clock mr-1"></i>' . $daysLeft . ' day(s) remaining</div>';
                                } else {
                                    $badges .= '<div><span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-red-100 text-red-800"><i class="fa-solid fa-hourglass-end mr-1"></i>Trial Expired</span></div>';
                                    $badges .= '<div class="text-xs text-red-600 mt-1">Needs renewal / upgrade</div>';
                                }
                            } elseif ($tenant->subscription_status === 'active') {
                                if ($tenant->isSubscribed()) {
                                    $badges .= '<div><span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800"><i class="fa-solid fa-circle-check mr-1"></i>Subscribed (Active)</span></div>';
                                    if ($tenant->trial_ends_at) {
                                        $badges .= '<div class="text-[11px] text-gray-500 mt-0.5">Converted from Trial</div>';
                                    }
                                } else {
                                    $badges .= '<div><span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-red-100 text-red-800"><i class="fa-solid fa-circle-exclamation mr-1"></i>Subscription Expired</span></div>';
                                }
                            } elseif ($tenant->subscription_status === 'cancelled') {
                                $badges .= '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-gray-200 text-gray-700"><i class="fa-solid fa-ban mr-1"></i>Cancelled</span>';
                            } else {
                                $badges .= '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-red-100 text-red-800"><i class="fa-solid fa-circle-xmark mr-1"></i>Expired</span>';
                            }

                            return $badges;
                        },
                    ],
                    [
                        'label' => 'Expired Date',
                        'slot' => function ($user) {
                            if ($user->isSuperAdmin()) {
                                return '<span class="text-xs text-gray-400">Never Expires</span>';
                            }

                            $tenant = $user->tenant;
                            $payload = [
                                'id'                     => $user->id,
                                'name'                   => $user->name,
                                'email'                  => $user->email,
                                'phone'                  => $user->phone ?? '',
                                'role'                   => $user->roles->first()?->name ?? '',
                                'outlet_id'              => $user->outlets->first()?->id ?? '',
                                'is_active'              => $user->is_active ? 1 : 0,
                                'business_name'          => $tenant?->name ?? '',
                                'plan'                   => $tenant?->plan ?? 'professional',
                                'subscription_status'    => $tenant?->subscription_status ?? 'trial',
                                'trial_ends_at'          => $tenant?->trial_ends_at ? $tenant->trial_ends_at->format('Y-m-d\TH:i') : '',
                                'subscription_ends_at'   => $tenant?->subscription_ends_at ? $tenant->subscription_ends_at->format('Y-m-d\TH:i') : '',
                                'is_subscription_exempt' => (bool) ($tenant?->is_subscription_exempt ?? false),
                                'focusSection'           => 'subscription',
                            ];
                            $jsonPayload = htmlspecialchars(json_encode($payload), ENT_QUOTES, 'UTF-8');

                            $editBtn = auth()->user()?->can('edit users')
                                ? '<button type="button" x-data @click="$dispatch(\'open-edit-modal\', ' . $jsonPayload . ')" class="mt-1 inline-flex items-center gap-1 px-2 py-0.5 text-[11px] font-medium text-indigo-700 bg-indigo-50 hover:bg-indigo-100 border border-indigo-200 rounded transition"><i class="fa-solid fa-calendar-pen text-[10px]"></i> Set / Edit Date</button>'
                                : '';

                            if (! $tenant) {
                                return '<div><div class="text-xs text-gray-400">Not set</div>' . $editBtn . '</div>';
                            }

                            // Determine primary expiration date to show
                            $isPaidSub = $tenant->subscription_status === 'active' && $tenant->subscription_ends_at;
                            $expiryDate = $isPaidSub ? $tenant->subscription_ends_at : $tenant->trial_ends_at;
                            $dateLabel = $isPaidSub ? 'Sub Ends:' : 'Trial Ends:';

                            if (! $expiryDate) {
                                $unlimitedText = $tenant->subscription_status === 'active'
                                    ? '<span class="text-xs font-medium text-emerald-700">No Expiry (Active)</span>'
                                    : '<span class="text-xs text-gray-400">No Date Set</span>';
                                return '<div><div>' . $unlimitedText . '</div>' . $editBtn . '</div>';
                            }

                            $isPast = $expiryDate->isPast();
                            $dateColor = $isPast ? 'text-red-600 font-semibold' : 'text-gray-900 font-medium';
                            $diffText = $expiryDate->diffForHumans();
                            $diffColor = $isPast ? 'text-red-500' : 'text-gray-500';

                            return '<div>'
                                . '<div class="text-[11px] text-gray-400 uppercase tracking-wide">' . $dateLabel . '</div>'
                                . '<div class="text-sm ' . $dateColor . '">' . e($expiryDate->format('d M Y, H:i')) . '</div>'
                                . '<div class="text-xs ' . $diffColor . '">' . e($diffText) . '</div>'
                                . $editBtn
                                . '</div>';
                        },
                    ],
                    [
                        'label' => 'Status',
                        'slot' => fn($user) => $user->is_active
                            ? '<span class="inline-flex items-center px-2.5 py-0.5 text-xs font-semibold rounded-full bg-green-100 text-green-800"><span class="w-1.5 h-1.5 rounded-full bg-green-600 mr-1.5"></span>Active</span>'
                            : '<span class="inline-flex items-center px-2.5 py-0.5 text-xs font-semibold rounded-full bg-red-100 text-red-800"><span class="w-1.5 h-1.5 rounded-full bg-red-600 mr-1.5"></span>Inactive</span>',
                    ],
                    [
                        'label' => 'Registered At',
                        'slot' => fn($user) => $user->created_at
                            ? '<div class="text-sm text-gray-900">' . e($user->created_at->format('d M Y')) . '</div><div class="text-xs text-gray-500">' . e($user->created_at->format('H:i')) . '</div>'
                            : '-',
                    ],
                ];
            @endphp

            <x-admin-table :headers="$tableHeaders" :rows="$users ?? []" :actions="function ($user) {
                return view('admin.users.actions', ['user' => $user])->render();
            }" />

            @if (isset($users) && method_exists($users, 'links'))
                <div class="mt-4">
                    {{ $users->links() }}
                </div>
            @endif
        </x-admin-card>
    </div>

    @include('admin.users.create-modal')
    @include('admin.users.edit-modal')
    @include('admin.users.view-modal')
@endsection
