<div x-data="{ open: false }" @open-edit-modal.window="open = true">
    <x-admin-modal id="edit-customer-modal" title="Edit Customer" size="lg">
        <form method="POST" action="{{ route('admin.customers.update', request()->route('customer')) }}">
            @csrf
            @method('PUT')
            
            <x-admin-form-input name="name" label="Customer Name" required value="{{ old('name', $customer->name ?? '') }}" />
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <x-admin-form-input name="email" label="Email" type="email" value="{{ old('email', $customer->email ?? '') }}" />
                <x-admin-form-input name="phone" label="Phone" type="tel" value="{{ old('phone', $customer->phone ?? '') }}" />
            </div>
            
            <x-admin-form-input name="address" label="Address" type="textarea" value="{{ old('address', $customer->address ?? '') }}" />
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <x-admin-form-input name="status" label="Status" type="select" :options="['active' => 'Active', 'inactive' => 'Inactive']" value="{{ old('status', $customer->status ?? 'active') }}" />
                <x-admin-form-input name="notes" label="Notes" type="textarea" value="{{ old('notes', $customer->notes ?? '') }}" />
            </div>
            
            <div class="flex items-center justify-end space-x-3 mt-6">
                <button type="button" @click="open = false" class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200">
                    Cancel
                </button>
                <x-admin-button type="submit" variant="primary" icon="fa-solid fa-save">
                    Update Customer
                </x-admin-button>
            </div>
        </form>
    </x-admin-modal>
</div>
