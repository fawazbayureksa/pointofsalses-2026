<div x-cloak x-data="{ open: false, record: {} }"
    @open-view-modal.window="open = true; record = $event.detail">
    <x-admin-modal id="view-user-modal" title="User & Subscription Details" size="lg">
        <div class="space-y-6">
            {{-- Header Card --}}
            <div class="flex items-center justify-between bg-slate-50 border border-slate-200 rounded-xl p-4">
                <div class="flex items-center space-x-4">
                    <div class="w-12 h-12 rounded-full bg-indigo-600 text-white flex items-center justify-center text-lg font-bold">
                        <span x-text="record.name ? record.name.charAt(0).toUpperCase() : 'U'"></span>
                    </div>
                    <div>
                        <h4 class="text-base font-bold text-gray-900" x-text="record.name || '-'"></h4>
                        <p class="text-sm text-gray-500" x-text="record.email || '-'"></p>
                    </div>
                </div>
                <div class="flex flex-col items-end gap-1">
                    <span x-show="record.is_active == 1"
                        class="px-2.5 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                        <i class="fa-solid fa-circle-check mr-1"></i> Active Account
                    </span>
                    <span x-show="record.is_active != 1"
                        class="px-2.5 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">
                        <i class="fa-solid fa-circle-xmark mr-1"></i> Inactive Account
                    </span>
                    <span class="text-xs text-gray-400" x-text="'ID: #' + (record.id || '')"></span>
                </div>
            </div>

            {{-- User Info Grid --}}
            <div>
                <h5 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-3">Account Information</h5>
                <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4 bg-white border border-gray-200 rounded-xl p-4">
                    <div>
                        <dt class="text-xs text-gray-500">Phone Number</dt>
                        <dd class="mt-0.5 text-sm font-medium text-gray-900" x-text="record.phone || '-'"></dd>
                    </div>
                    <div>
                        <dt class="text-xs text-gray-500">Assigned Role</dt>
                        <dd class="mt-0.5 text-sm font-medium text-gray-900" x-text="record.role_label || '-'"></dd>
                    </div>
                    <div>
                        <dt class="text-xs text-gray-500">Assigned Outlet</dt>
                        <dd class="mt-0.5 text-sm font-medium text-gray-900" x-text="record.outlet_name || '-'"></dd>
                    </div>
                    <div>
                        <dt class="text-xs text-gray-500">Cashier PIN</dt>
                        <dd class="mt-0.5 text-sm font-medium">
                            <span x-show="record.has_pin" class="text-emerald-600"><i class="fa-solid fa-lock mr-1"></i> Configured</span>
                            <span x-show="!record.has_pin" class="text-gray-400">Not set</span>
                        </dd>
                    </div>
                    <div>
                        <dt class="text-xs text-gray-500">Registered At</dt>
                        <dd class="mt-0.5 text-sm font-medium text-gray-900" x-text="record.created_at_formatted || '-'"></dd>
                    </div>
                </dl>
            </div>

            {{-- Subscription & Trial Info --}}
            <div x-show="!record.is_super_admin">
                <h5 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-3">
                    Trial & Subscription Information
                </h5>
                <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4 bg-indigo-50/50 border border-indigo-100 rounded-xl p-4">
                    <div>
                        <dt class="text-xs text-gray-500">Business / Tenant</dt>
                        <dd class="mt-0.5 text-sm font-semibold text-gray-900" x-text="record.business_name || '-'"></dd>
                    </div>
                    <div>
                        <dt class="text-xs text-gray-500">Current Plan</dt>
                        <dd class="mt-0.5">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-indigo-100 text-indigo-800 capitalize"
                                x-text="record.plan || 'professional'"></span>
                        </dd>
                    </div>
                    <div>
                        <dt class="text-xs text-gray-500">Subscription / Trial Status</dt>
                        <dd class="mt-0.5">
                            <template x-if="record.is_subscription_exempt">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-amber-100 text-amber-800">
                                    <i class="fa-solid fa-shield-heart mr-1"></i> Exempt (Free Access)
                                </span>
                            </template>
                            <template x-if="!record.is_subscription_exempt && record.subscription_status === 'trial'">
                                <span>
                                    <span x-show="record.is_on_trial"
                                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-blue-100 text-blue-800">
                                        <i class="fa-solid fa-gift mr-1"></i> Free Trial (<span x-text="record.trial_days_left"></span>d left)
                                    </span>
                                    <span x-show="!record.is_on_trial"
                                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-red-100 text-red-800">
                                        <i class="fa-solid fa-hourglass-end mr-1"></i> Trial Expired
                                    </span>
                                </span>
                            </template>
                            <template x-if="!record.is_subscription_exempt && record.subscription_status === 'active'">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800">
                                    <i class="fa-solid fa-check-circle mr-1"></i> Subscribed (Paid)
                                </span>
                            </template>
                            <template x-if="!record.is_subscription_exempt && (record.subscription_status === 'expired' || record.subscription_status === 'cancelled')">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-red-100 text-red-800 capitalize"
                                    x-text="record.subscription_status"></span>
                            </template>
                        </dd>
                    </div>
                    <div>
                        <dt class="text-xs text-gray-500">System Access</dt>
                        <dd class="mt-0.5">
                            <span x-show="record.has_active_access" class="text-xs font-semibold text-emerald-700">
                                <i class="fa-solid fa-unlock mr-1"></i> Can Access System
                            </span>
                            <span x-show="!record.has_active_access" class="text-xs font-semibold text-red-600">
                                <i class="fa-solid fa-lock mr-1"></i> Blocked (Redirected to Expired Page)
                            </span>
                        </dd>
                    </div>
                    <div>
                        <dt class="text-xs text-gray-500">Trial Expired Date</dt>
                        <dd class="mt-0.5 text-sm font-semibold text-gray-900" x-text="record.trial_ends_at_formatted || '-'"></dd>
                    </div>
                    <div>
                        <dt class="text-xs text-gray-500">Subscription Expired Date</dt>
                        <dd class="mt-0.5 text-sm font-semibold text-gray-900" x-text="record.subscription_ends_at_formatted || '-'"></dd>
                    </div>
                </dl>
            </div>

            <div class="flex items-center justify-end space-x-3 pt-2">
                <button type="button" @click="open = false"
                    class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200">
                    Close
                </button>
                @can('edit users')
                    <button type="button"
                        @click="open = false; $dispatch('open-edit-modal', { ...record, focusSection: 'subscription' })"
                        class="px-4 py-2 text-sm font-medium text-white bg-indigo-600 rounded-lg hover:bg-indigo-700">
                        <i class="fa-solid fa-calendar-check mr-1.5"></i> Edit User & Expired Date
                    </button>
                @endcan
            </div>
        </div>
    </x-admin-modal>
</div>
