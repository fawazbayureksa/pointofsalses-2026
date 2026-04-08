<div x-data="{ open: false, currentId: null, record: {} }"
    @open-edit-modal.window="
        open = true;
        currentId = $event.detail.id;
        record = $event.detail;
        $nextTick(() => {
            $el.querySelectorAll('[name]').forEach(el => {
                const v = record[el.name];
                if (v === undefined) return;
                if (el.type === 'checkbox') { el.checked = !!v; }
                else if (el.tagName === 'SELECT') el.value = v ?? '';
                else if (el.tagName === 'TEXTAREA') el.textContent = v ?? '';
                else el.value = v ?? '';
            });
        })">
    <x-admin-modal id="edit-tenant-modal" title="Edit Tenant" size="lg">
        <form method="POST" :action="'{{ url('/admin/tenants') }}/' + currentId">
            @csrf
            @method('PUT')

            <x-admin-form-input name="name" label="Tenant Name" required placeholder="Enter tenant name" />

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <x-admin-form-input name="slug" label="Slug" required placeholder="my-tenant" />
                <x-admin-form-input name="email" label="Email" type="email" placeholder="admin@example.com" />
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <x-admin-form-input name="plan" label="Plan" type="select" :options="['basic' => 'Basic', 'professional' => 'Professional', 'enterprise' => 'Enterprise']" />
                <x-admin-form-input name="status" label="Status" type="select" :options="['active' => 'Active', 'inactive' => 'Inactive', 'suspended' => 'Suspended']" />
            </div>

            {{-- Subscription --}}
            <div class="border-t border-gray-200 pt-4 mt-2">
                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-3">Subscription</p>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <x-admin-form-input name="subscription_status" label="Subscription Status" type="select"
                        :options="['trial' => 'Trial', 'active' => 'Active (Paid)', 'expired' => 'Expired', 'cancelled' => 'Cancelled']" />
                    <x-admin-form-input name="trial_ends_at" label="Trial Ends At" type="datetime-local" />
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <x-admin-form-input name="subscription_ends_at" label="Subscription Ends At" type="datetime-local" />
                    <div class="flex items-center gap-3 pt-6">
                        <input type="checkbox" name="is_subscription_exempt" id="edit_is_subscription_exempt"
                               value="1" class="w-4 h-4 text-indigo-600 rounded">
                        <label for="edit_is_subscription_exempt" class="text-sm font-medium text-gray-700">
                            Exempt from subscription check
                            <span class="text-xs text-gray-400 block font-normal">For testing or free accounts</span>
                        </label>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <x-admin-form-input name="phone" label="Phone" type="tel" placeholder="+1234567890" />
                <x-admin-form-input name="business_type" label="Business Type" placeholder="e.g. Restaurant" />
            </div>

            <x-admin-form-input name="address" label="Address" type="textarea" placeholder="Enter full address" />

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <x-admin-form-input name="trial_ends_at" label="Trial Ends At" type="datetime-local" />
                <div class="flex flex-col justify-center">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Subscription Skip</label>
                    <label class="inline-flex items-center gap-2 cursor-pointer">
                        <input type="hidden" name="subscription_skipped" value="0">
                        <input type="checkbox" name="subscription_skipped" value="1"
                               x-init="$el.checked = record.subscription_skipped == 1 || record.subscription_skipped === true"
                               class="w-4 h-4 text-blue-600 border-gray-300 rounded">
                        <span class="text-sm text-gray-600">Skip subscription check for this tenant</span>
                    </label>
                </div>
            </div>

            <div class="flex items-center justify-end space-x-3 mt-6">
                <button type="button" @click="open = false"
                    class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200">
                    Cancel
                </button>
                <x-admin-button type="submit" variant="primary" icon="fa-solid fa-save">
                    Update Tenant
                </x-admin-button>
            </div>
        </form>
    </x-admin-modal>
</div>
