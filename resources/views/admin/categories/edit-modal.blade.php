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
    <x-admin-modal id="edit-category-modal" title="Edit Category" size="lg">
        <form method="POST" :action="'{{ url('/admin/categories') }}/' + currentId">
            @csrf
            @method('PUT')

            <x-admin-form-input name="name" label="Category Name" required value="" />

            <x-admin-form-input name="slug" label="Slug" value="" />

            <x-admin-form-input name="description" label="Description" type="textarea" value="" />

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <x-admin-form-input name="parent_id" label="Parent Category" type="select" :options="['' => 'No Parent'] + ($parentCategories ?? collect())->pluck('name', 'id')->toArray()"
                    value="" />
                <x-admin-form-input name="sort_order" label="Sort Order" type="number" value="" />
            </div>

            <x-admin-form-input name="is_active" label="Status" type="select" :options="[1 => 'Active', 0 => 'Inactive']" value="" />

            <x-admin-form-input name="image" label="Category Image" type="file" />

            <div class="flex items-center justify-end space-x-3 mt-6">
                <button type="button" @click="open = false"
                    class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200">
                    Cancel
                </button>
                <x-admin-button type="submit" variant="primary" icon="fa-solid fa-save">
                    Update Category
                </x-admin-button>
            </div>
        </form>
    </x-admin-modal>
</div>
