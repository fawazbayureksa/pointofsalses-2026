<div x-data="{ open: false }" @open-edit-modal.window="open = true">
    <x-admin-modal id="edit-tenant-modal" title="Edit Tenant" size="lg">
        <form method="POST" action="{{ route('admin.tenants.update', request()->route('tenant')) }}">
            @csrf
            @method('PUT')
            
            <x-admin-form-input name="name" label="Tenant Name" required value="{{ old('name', $tenant->name ?? '') }}" />
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <x-admin-form-input name="domain" label="Domain" required value="{{ old('domain', $tenant->domain ?? '') }}" />
                <x-admin-form-input name="email" label="Email" type="email" required value="{{ old('email', $tenant->email ?? '') }}" />
            </div>
            
            <x-admin-form-input name="database" label="Database Name" required value="{{ old('database', $tenant->database ?? '') }}" />
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <x-admin-form-input name="phone" label="Phone" type="tel" value="{{ old('phone', $tenant->phone ?? '') }}" />
                <x-admin-form-input name="status" label="Status" type="select" :options="['active' => 'Active', 'inactive' => 'Inactive', 'suspended' => 'Suspended']" value="{{ old('status', $tenant->status ?? 'active') }}" />
            </div>
            
            <x-admin-form-input name="address" label="Address" type="textarea" value="{{ old('address', $tenant->address ?? '') }}" />
            
            <div class="flex items-center justify-end space-x-3 mt-6">
                <button type="button" @click="open = false" class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200">
                    Cancel
                </button>
                <x-admin-button type="submit" variant="primary" icon="fa-solid fa-save">
                    Update Tenant
                </x-admin-button>
            </div>
        </form>
    </x-admin-modal>
</div>
