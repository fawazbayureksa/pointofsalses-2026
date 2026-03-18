<div x-data="{ open: false }" @open-create-modal.window="open = true">
    <x-admin-modal id="create-user-modal" title="Create New User" size="lg">
        <form method="POST" action="{{ route('admin.users.store') }}">
            @csrf
            
            <x-admin-form-input name="name" label="Full Name" required placeholder="Enter full name" />
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <x-admin-form-input name="email" label="Email" type="email" required placeholder="user@example.com" />
                <x-admin-form-input name="phone" label="Phone" type="tel" placeholder="+1234567890" />
            </div>
            
            <x-admin-form-input name="password" label="Password" type="password" required placeholder="Enter password" />
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <x-admin-form-input name="role" label="Role" type="select" :options="['' => 'Select Role']" />
                <x-admin-form-input name="outlet_id" label="Outlet" type="select" :options="['' => 'Select Outlet']" />
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <x-admin-form-input name="status" label="Status" type="select" :options="['active' => 'Active', 'inactive' => 'Inactive']" />
                <x-admin-form-input name="avatar" label="Avatar" type="file" />
            </div>
            
            <x-admin-form-input name="address" label="Address" type="textarea" placeholder="Enter address" />
            
            <div class="flex items-center justify-end space-x-3 mt-6">
                <button type="button" @click="open = false" class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200">
                    Cancel
                </button>
                <x-admin-button type="submit" variant="primary" icon="fa-solid fa-save">
                    Create User
                </x-admin-button>
            </div>
        </form>
    </x-admin-modal>
</div>
