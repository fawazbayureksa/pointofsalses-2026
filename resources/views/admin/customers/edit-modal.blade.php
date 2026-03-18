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
    <x-admin-modal id="edit-customer-modal" title="Edit Customer" size="lg">
        <form method="POST" :action="'{{ url('/admin/customers') }}/' + currentId">
            @csrf
            @method('PUT')
            
            <x-admin-form-input name="name" label="Customer Name" required value="" />

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <x-admin-form-input name="email" label="Email" type="email" value="" />
                <x-admin-form-input name="phone" label="Phone" type="tel" value="" />
            </div>

            <x-admin-form-input name="address" label="Address" type="textarea" value="" />

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <x-admin-form-input name="gender" label="Gender" type="select" :options="['' => 'Select', 'male' => 'Male', 'female' => 'Female']" value="" />
                <x-admin-form-input name="status" label="Status" type="select" :options="['active' => 'Active', 'inactive' => 'Inactive']" value="" />
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
