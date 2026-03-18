<div x-data="{ open: false, currentId: null, record: {} }"
    @open-edit-modal.window="
        open = true;
        currentId = $event.detail.id;
        record = $event.detail;
        $nextTick(() => {
            $el.querySelectorAll('[name]').forEach(el => {
                const v = record[el.name];
                if (v === undefined) return;
                if (el.tagName === 'SELECT') el.value = v ?? '';
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

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <x-admin-form-input name="phone" label="Phone" type="tel" placeholder="+1234567890" />
                <x-admin-form-input name="business_type" label="Business Type" placeholder="e.g. Restaurant" />
            </div>

            <x-admin-form-input name="address" label="Address" type="textarea" placeholder="Enter full address" />

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
