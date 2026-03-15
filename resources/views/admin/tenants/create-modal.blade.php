<div x-data="{ open: false }" @open-create-modal.window="open = true">
    <x-admin-modal id="create-tenant-modal" title="Create New Tenant" size="lg">
        <form method="POST" action="{{ route('admin.tenants.store') }}">
            @csrf

            <x-admin-form-input name="name" label="Tenant Name" required placeholder="Enter tenant name" />

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <x-admin-form-input name="domain" label="Domain" required placeholder="example.com" />
                <x-admin-form-input name="email" label="Email" type="email" required
                    placeholder="admin@example.com" />
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
                    Create Tenant
                </x-admin-button>
            </div>
        </form>
    </x-admin-modal>
</div>
