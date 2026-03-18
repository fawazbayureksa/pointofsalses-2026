<div x-data="{ open: false }" @open-create-modal.window="open = true">
    <x-admin-modal id="create-outlet-modal" title="Create New Outlet" size="lg">
        <form method="POST" action="{{ route('admin.outlets.store') }}">
            @csrf
            
            <x-admin-form-input name="name" label="Outlet Name" required placeholder="Enter outlet name" />
            
            <x-admin-form-input name="address" label="Address" type="textarea" required placeholder="Enter full address" />
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <x-admin-form-input name="phone" label="Phone" type="tel" placeholder="+1234567890" />
                <x-admin-form-input name="email" label="Email" type="email" placeholder="outlet@example.com" />
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <x-admin-form-input name="status" label="Status" type="select" :options="['active' => 'Active', 'inactive' => 'Inactive']" />
                <x-admin-form-input name="manager_id" label="Manager" type="select" :options="['' => 'Select Manager']" />
            </div>
            
            <x-admin-form-input name="opening_hours" label="Opening Hours" placeholder="Mon-Fri: 9AM-9PM" />
            
            <div class="flex items-center justify-end space-x-3 mt-6">
                <button type="button" @click="open = false" class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200">
                    Cancel
                </button>
                <x-admin-button type="submit" variant="primary" icon="fa-solid fa-save">
                    Create Outlet
                </x-admin-button>
            </div>
        </form>
    </x-admin-modal>
</div>
