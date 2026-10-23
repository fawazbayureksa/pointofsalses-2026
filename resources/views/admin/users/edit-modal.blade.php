@php
    $roleOptions = collect($roles ?? [])->mapWithKeys(fn($r) => [$r->name => ucwords(str_replace('_', ' ', $r->name))])->toArray();
    $outletOptions = collect($outlets ?? [])->mapWithKeys(fn($o) => [$o->id => $o->name])->toArray();
@endphp

<div x-cloak x-data="{
        open: false,
        currentId: null,
        record: {},
        formatLocalDatetime(date) {
            const pad = (n) => String(n).padStart(2, '0');
            return date.getFullYear() + '-' +
                pad(date.getMonth() + 1) + '-' +
                pad(date.getDate()) + 'T' +
                pad(date.getHours()) + ':' +
                pad(date.getMinutes());
        },
        setTrialDays(days) {
            const d = new Date();
            d.setDate(d.getDate() + days);
            const val = this.formatLocalDatetime(d);
            this.record.trial_ends_at = val;
            const input = this.$el.querySelector('[name=trial_ends_at]');
            if (input) input.value = val;
            if (days > 0) {
                this.record.subscription_status = 'trial';
                const statusSelect = this.$el.querySelector('[name=subscription_status]');
                if (statusSelect) statusSelect.value = 'trial';
            }
        },
        setSubscriptionMonths(months) {
            const d = new Date();
            d.setMonth(d.getMonth() + months);
            const val = this.formatLocalDatetime(d);
            this.record.subscription_ends_at = val;
            const input = this.$el.querySelector('[name=subscription_ends_at]');
            if (input) input.value = val;
            if (months > 0) {
                this.record.subscription_status = 'active';
                const statusSelect = this.$el.querySelector('[name=subscription_status]');
                if (statusSelect) statusSelect.value = 'active';
            }
        },
        expireNow() {
            const d = new Date(Date.now() - 60000);
            const val = this.formatLocalDatetime(d);
            this.record.trial_ends_at = val;
            const input = this.$el.querySelector('[name=trial_ends_at]');
            if (input) input.value = val;
            this.record.subscription_status = 'expired';
            const statusSelect = this.$el.querySelector('[name=subscription_status]');
            if (statusSelect) statusSelect.value = 'expired';
        },
        clearDate(fieldName) {
            this.record[fieldName] = '';
            const input = this.$el.querySelector(`[name=${fieldName}]`);
            if (input) input.value = '';
        }
    }"
    @open-edit-modal.window="
        open = true;
        currentId = $event.detail.id;
        record = { ...$event.detail };
        $nextTick(() => {
            $el.querySelectorAll('[name]').forEach(el => {
                if (el.type === 'hidden' || el.type === 'file') return;
                const v = record[el.name];
                if (v === undefined) return;
                if (el.type === 'checkbox') {
                    el.checked = !!v;
                } else if (el.tagName === 'SELECT') {
                    el.value = v ?? '';
                } else if (el.tagName === 'TEXTAREA') {
                    el.textContent = v ?? '';
                } else {
                    el.value = v ?? '';
                }
            });
            if (record.focusSection === 'subscription') {
                const subInput = $el.querySelector('[name=trial_ends_at]');
                if (subInput) {
                    subInput.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    subInput.focus();
                }
            }
        })">
    <x-admin-modal id="edit-user-modal" title="Edit User & Subscription / Expired Date" size="lg">
        <form method="POST" :action="'{{ url('/admin/users') }}/' + currentId" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            {{-- User Account Details --}}
            <div class="mb-2">
                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-3">
                    <i class="fa-solid fa-user mr-1"></i> User Information
                </p>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <x-admin-form-input name="name" label="Full Name" required value="" />
                    <x-admin-form-input name="email" label="Email Address" type="email" required value="" />
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <x-admin-form-input name="phone" label="Phone Number" type="tel" value="" placeholder="+62..." />
                    <x-admin-form-input name="password" label="New Password" type="password" placeholder="Leave blank to keep current" />
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <x-admin-form-input name="role" label="Role" type="select" :options="$roleOptions" value="" />
                    <x-admin-form-input name="outlet_id" label="Default Outlet" type="select" :options="$outletOptions" value="" />
                    <x-admin-form-input name="is_active" label="Account Status" type="select" required :options="[1 => 'Active', 0 => 'Inactive']" value="1" />
                </div>
            </div>

            {{-- Trial & Subscription Self-Setup --}}
            <div class="border-t border-gray-200 pt-4 mt-4 bg-indigo-50/40 -mx-4 px-4 py-4 rounded-xl border border-indigo-100">
                <div class="flex items-center justify-between mb-3">
                    <p class="text-xs font-bold text-indigo-900 uppercase tracking-wider">
                        <i class="fa-solid fa-calendar-check mr-1 text-indigo-600"></i>
                        Trial & Subscription Setup (Self-Configurable Expired Date)
                    </p>
                    <span class="text-xs text-indigo-600 font-medium" x-show="record.business_name" x-text="record.business_name"></span>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <x-admin-form-input name="business_name" label="Business / Tenant Name" placeholder="e.g. Kopi Nusantara" value="" />
                    <x-admin-form-input name="plan" label="Subscription Plan" type="select" required
                        :options="['basic' => 'Starter (Free)', 'professional' => 'Professional', 'enterprise' => 'Enterprise']" value="professional" />
                    <x-admin-form-input name="subscription_status" label="Subscription / Trial Status" type="select" required
                        :options="['trial' => 'Free Trial', 'active' => 'Active (Subscribed)', 'expired' => 'Expired', 'cancelled' => 'Cancelled']" value="trial" />
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-1">
                    {{-- Trial Expired Date --}}
                    <div>
                        <label for="edit_trial_ends_at" class="block text-sm font-medium text-gray-700 mb-1">
                            Trial Expired Date <span class="text-xs text-gray-400 font-normal">(Set custom date & time)</span>
                        </label>
                        <input type="datetime-local" name="trial_ends_at" id="edit_trial_ends_at"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent text-sm bg-white">
                        <div class="flex flex-wrap items-center gap-1.5 mt-2">
                            <span class="text-xs text-gray-500 mr-1">Quick set:</span>
                            <button type="button" @click="setTrialDays(7)"
                                class="px-2 py-0.5 text-xs font-medium bg-blue-100 text-blue-700 rounded hover:bg-blue-200 transition">
                                +7 Days
                            </button>
                            <button type="button" @click="setTrialDays(14)"
                                class="px-2 py-0.5 text-xs font-medium bg-indigo-100 text-indigo-700 rounded hover:bg-indigo-200 transition">
                                +14 Days (Default)
                            </button>
                            <button type="button" @click="setTrialDays(30)"
                                class="px-2 py-0.5 text-xs font-medium bg-purple-100 text-purple-700 rounded hover:bg-purple-200 transition">
                                +30 Days
                            </button>
                            <button type="button" @click="expireNow()"
                                class="px-2 py-0.5 text-xs font-medium bg-red-100 text-red-700 rounded hover:bg-red-200 transition">
                                Expire Now
                            </button>
                            <button type="button" @click="clearDate('trial_ends_at')"
                                class="px-2 py-0.5 text-xs font-medium bg-gray-100 text-gray-600 rounded hover:bg-gray-200 transition">
                                Clear
                            </button>
                        </div>
                    </div>

                    {{-- Paid Subscription Expired Date --}}
                    <div>
                        <label for="edit_subscription_ends_at" class="block text-sm font-medium text-gray-700 mb-1">
                            Subscription Expired Date <span class="text-xs text-gray-400 font-normal">(For Active Paid Plan)</span>
                        </label>
                        <input type="datetime-local" name="subscription_ends_at" id="edit_subscription_ends_at"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent text-sm bg-white">
                        <div class="flex flex-wrap items-center gap-1.5 mt-2">
                            <span class="text-xs text-gray-500 mr-1">Quick set:</span>
                            <button type="button" @click="setSubscriptionMonths(1)"
                                class="px-2 py-0.5 text-xs font-medium bg-emerald-100 text-emerald-700 rounded hover:bg-emerald-200 transition">
                                +1 Month
                            </button>
                            <button type="button" @click="setSubscriptionMonths(6)"
                                class="px-2 py-0.5 text-xs font-medium bg-emerald-100 text-emerald-700 rounded hover:bg-emerald-200 transition">
                                +6 Months
                            </button>
                            <button type="button" @click="setSubscriptionMonths(12)"
                                class="px-2 py-0.5 text-xs font-medium bg-emerald-100 text-emerald-700 rounded hover:bg-emerald-200 transition">
                                +1 Year
                            </button>
                            <button type="button" @click="clearDate('subscription_ends_at')"
                                class="px-2 py-0.5 text-xs font-medium bg-gray-100 text-gray-600 rounded hover:bg-gray-200 transition">
                                Clear (Unlimited)
                            </button>
                        </div>
                    </div>
                </div>

                <div class="mt-4 pt-3 border-t border-indigo-100 flex items-center justify-between">
                    <label class="inline-flex items-center gap-2.5 cursor-pointer">
                        <input type="hidden" name="is_subscription_exempt" value="0">
                        <input type="checkbox" name="is_subscription_exempt" id="edit_user_is_subscription_exempt"
                            value="1" class="w-4 h-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500">
                        <span class="text-sm font-medium text-gray-700">
                            Exempt from subscription expiration check
                            <span class="text-xs text-gray-500 font-normal block">When enabled, this user will never be locked out even if trial/subscription date expires.</span>
                        </span>
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
                    Save Changes
                </x-admin-button>
            </div>
        </form>
    </x-admin-modal>
</div>
