<div x-data="{ open: false }" @open-create-modal.window="open = true">
    <x-admin-modal id="create-tenant-modal" title="Create New Tenant" size="lg">
        <form method="POST" action="{{ route('admin.tenants.store') }}">
            @csrf

            <x-admin-form-input name="name" label="Tenant Name" required placeholder="Enter tenant name" />

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <x-admin-form-input name="slug" label="Slug" required placeholder="my-tenant" />
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

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <x-admin-form-input name="trial_ends_at" label="Trial Ends At" type="datetime-local" />
                <div class="flex flex-col justify-center">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Subscription Skip</label>
                    <label class="inline-flex items-center gap-2 cursor-pointer">
                        <input type="hidden" name="subscription_skipped" value="0">
                        <input type="checkbox" name="subscription_skipped" value="1"
                               class="w-4 h-4 text-blue-600 border-gray-300 rounded">
                        <span class="text-sm text-gray-600">Skip subscription check for this tenant</span>
                    </label>
                </div>
            </div>

            <hr class="my-4 border-gray-200">
            <p class="text-sm font-semibold text-gray-700 mb-3">Admin Account</p>

            <x-admin-form-input name="admin_name" label="Admin Name" required placeholder="Enter admin name" />

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <x-admin-form-input name="admin_email" label="Admin Email" type="email" required
                    placeholder="admin@tenant.com" />
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <x-admin-form-input name="admin_password" label="Admin Password" type="password" required
                    placeholder="Min. 8 characters" />
                <x-admin-form-input name="admin_password_confirmation" label="Confirm Password" type="password" required
                    placeholder="Repeat password" />
            </div>

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
