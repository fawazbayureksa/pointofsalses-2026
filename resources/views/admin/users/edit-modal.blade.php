<div x-data="{ open: false }" @open-edit-modal.window="open = true">
    <x-admin-modal id="edit-user-modal" title="Edit User" size="lg">
        <form method="POST" action="{{ route('admin.users.update', request()->route('user')) }}">
            @csrf
            @method('PUT')
            
            <x-admin-form-input name="name" label="Full Name" required value="{{ old('name', $user->name ?? '') }}" />
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <x-admin-form-input name="email" label="Email" type="email" required value="{{ old('email', $user->email ?? '') }}" />
                <x-admin-form-input name="phone" label="Phone" type="tel" value="{{ old('phone', $user->phone ?? '') }}" />
            </div>
            
            <x-admin-form-input name="password" label="Password" type="password" placeholder="Leave blank to keep current" />
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <x-admin-form-input name="role" label="Role" type="select" :options="['' => 'Select Role']" value="{{ old('role', $user->roles->first()?->name ?? '') }}" />
                <x-admin-form-input name="outlet_id" label="Outlet" type="select" :options="['' => 'Select Outlet']" value="{{ old('outlet_id', $user->outlet_id ?? '') }}" />
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <x-admin-form-input name="status" label="Status" type="select" :options="['active' => 'Active', 'inactive' => 'Inactive']" value="{{ old('status', $user->status ?? 'active') }}" />
                <x-admin-form-input name="avatar" label="Avatar" type="file" />
            </div>
            
            <x-admin-form-input name="address" label="Address" type="textarea" value="{{ old('address', $user->address ?? '') }}" />
            
            <div class="flex items-center justify-end space-x-3 mt-6">
                <button type="button" @click="open = false" class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200">
                    Cancel
                </button>
                <x-admin-button type="submit" variant="primary" icon="fa-solid fa-save">
                    Update User
                </x-admin-button>
            </div>
        </form>
    </x-admin-modal>
</div>
