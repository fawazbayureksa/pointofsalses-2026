<div x-data="{ open: false }" @open-edit-modal.window="open = true">
    <x-admin-modal id="edit-outlet-modal" title="Edit Outlet" size="lg">
        <form method="POST" action="{{ route('admin.outlets.update', request()->route('outlet')) }}">
            @csrf
            @method('PUT')
            
            <x-admin-form-input name="name" label="Outlet Name" required value="{{ old('name', $outlet->name ?? '') }}" />
            
            <x-admin-form-input name="address" label="Address" type="textarea" required value="{{ old('address', $outlet->address ?? '') }}" />
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <x-admin-form-input name="phone" label="Phone" type="tel" value="{{ old('phone', $outlet->phone ?? '') }}" />
                <x-admin-form-input name="email" label="Email" type="email" value="{{ old('email', $outlet->email ?? '') }}" />
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <x-admin-form-input name="status" label="Status" type="select" :options="['active' => 'Active', 'inactive' => 'Inactive']" value="{{ old('status', $outlet->status ?? 'active') }}" />
                <x-admin-form-input name="manager_id" label="Manager" type="select" :options="['' => 'Select Manager']" value="{{ old('manager_id', $outlet->manager_id ?? '') }}" />
            </div>
            
            <x-admin-form-input name="opening_hours" label="Opening Hours" value="{{ old('opening_hours', $outlet->opening_hours ?? '') }}" />
            
            <div class="flex items-center justify-end space-x-3 mt-6">
                <button type="button" @click="open = false" class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200">
                    Cancel
                </button>
                <x-admin-button type="submit" variant="primary" icon="fa-solid fa-save">
                    Update Outlet
                </x-admin-button>
            </div>
        </form>
    </x-admin-modal>
</div>
