<div x-data="{ open: false }" @open-edit-modal.window="open = true">
    <x-admin-modal id="edit-category-modal" title="Edit Category" size="lg">
        <form method="POST" action="{{ route('admin.categories.update', request()->route('category')) }}">
            @csrf
            @method('PUT')
            
            <x-admin-form-input name="name" label="Category Name" required value="{{ old('name', $category->name ?? '') }}" />
            
            <x-admin-form-input name="slug" label="Slug" value="{{ old('slug', $category->slug ?? '') }}" />
            
            <x-admin-form-input name="description" label="Description" type="textarea" value="{{ old('description', $category->description ?? '') }}" />
            
            <x-admin-form-input name="parent_id" label="Parent Category" type="select" :options="['' => 'No Parent']" value="{{ old('parent_id', $category->parent_id ?? '') }}" />
            
            <x-admin-form-input name="status" label="Status" type="select" :options="['active' => 'Active', 'inactive' => 'Inactive']" value="{{ old('status', $category->status ?? 'active') }}" />
            
            <x-admin-form-input name="image" label="Category Image" type="file" />
            
            <div class="flex items-center justify-end space-x-3 mt-6">
                <button type="button" @click="open = false" class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200">
                    Cancel
                </button>
                <x-admin-button type="submit" variant="primary" icon="fa-solid fa-save">
                    Update Category
                </x-admin-button>
            </div>
        </form>
    </x-admin-modal>
</div>
