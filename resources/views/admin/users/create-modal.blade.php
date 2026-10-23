@php
    $roleOptions = collect($roles ?? [])->mapWithKeys(fn($r) => [$r->name => ucwords(str_replace('_', ' ', $r->name))])->toArray();
    $outletOptions = collect($outlets ?? [])->mapWithKeys(fn($o) => [$o->id => $o->name])->toArray();
    $tenantOptions = collect($tenants ?? [])->mapWithKeys(fn($t) => [$t->id => $t->name . ' (' . ucfirst($t->plan ?? 'basic') . ')'])->toArray();
    $defaultTrialDate = now()->addDays(14)->format('Y-m-d\TH:i');
@endphp

<div x-cloak x-data="{ open: false }" @open-create-modal.window="open = true">
    <x-admin-modal id="create-user-modal" title="Create New User & Trial Setup" size="lg">
        <form method="POST" action="{{ route('admin.users.store') }}" enctype="multipart/form-data">
            @csrf

            <div class="mb-2">
                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-3">
                    <i class="fa-solid fa-user-plus mr-1"></i> User Account Details
                </p>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <x-admin-form-input name="name" label="Full Name" required placeholder="e.g. Budi Santoso" />
                    <x-admin-form-input name="email" label="Email Address" type="email" required placeholder="user@company.com" />
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <x-admin-form-input name="phone" label="Phone Number" type="tel" placeholder="+62812345678" />
                    <x-admin-form-input name="password" label="Password" type="password" required placeholder="Min. 8 characters" />
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <x-admin-form-input name="role" label="Role" type="select" :options="$roleOptions" value="tenant_admin" />
                    <x-admin-form-input name="outlet_id" label="Default Outlet" type="select" :options="$outletOptions" />
                    <x-admin-form-input name="is_active" label="Account Status" type="select" required :options="[1 => 'Active', 0 => 'Inactive']" value="1" />
                </div>
            </div>

            <div class="border-t border-gray-200 pt-4 mt-4 bg-indigo-50/40 -mx-4 px-4 py-4 rounded-xl border border-indigo-100">
                <p class="text-xs font-bold text-indigo-900 uppercase tracking-wider mb-3">
                    <i class="fa-solid fa-gift mr-1 text-indigo-600"></i>
                    Business & Trial / Expired Date Setup
                </p>

                @role('super_admin')
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <x-admin-form-input name="tenant_id" label="Existing Tenant (Optional)" type="select" :options="$tenantOptions"
                            help="Leave blank to create a new business tenant for this user" />
                        <x-admin-form-input name="business_name" label="New Business Name" placeholder="e.g. Kopi Nusantara" />
                    </div>
                @endrole

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <x-admin-form-input name="plan" label="Subscription Plan" type="select" required
                        :options="['basic' => 'Starter (Free)', 'professional' => 'Professional', 'enterprise' => 'Enterprise']" value="professional" />
                    <x-admin-form-input name="subscription_status" label="Subscription / Trial Status" type="select" required
                        :options="['trial' => 'Free Trial', 'active' => 'Active (Subscribed)', 'expired' => 'Expired', 'cancelled' => 'Cancelled']" value="trial" />
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <x-admin-form-input name="trial_ends_at" label="Trial Expired Date" type="datetime-local" :value="$defaultTrialDate" />
                    <x-admin-form-input name="subscription_ends_at" label="Subscription Expired Date" type="datetime-local" />
                </div>

                <div class="mt-2">
                    <label class="inline-flex items-center gap-2 cursor-pointer">
                        <input type="hidden" name="is_subscription_exempt" value="0">
                        <input type="checkbox" name="is_subscription_exempt" value="1"
                            class="w-4 h-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500">
                        <span class="text-sm text-gray-700">Exempt from subscription expiration check</span>
                    </label>
                </div>
            </div>

            <div class="mt-4">
                <x-admin-form-input name="avatar" label="Avatar (Optional)" type="file" />
            </div>

            <div class="flex items-center justify-end space-x-3 mt-6">
                <button type="button" @click="open = false"
                    class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200">
                    Cancel
                </button>
                <x-admin-button type="submit" variant="primary" icon="fa-solid fa-save">
                    Create User
                </x-admin-button>
            </div>
        </form>
    </x-admin-modal>
</div>
