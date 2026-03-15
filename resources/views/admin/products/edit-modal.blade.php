<div x-data="{ open: false }" @open-edit-modal.window="open = true">
    <x-admin-modal id="edit-product-modal" title="Edit Product" size="lg">
        <form method="POST" action="{{ route('admin.products.update', request()->route('product')) }}" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            
            <x-admin-form-input name="name" label="Product Name" required value="{{ old('name', $product->name ?? '') }}" />
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <x-admin-form-input name="sku" label="SKU" required value="{{ old('sku', $product->sku ?? '') }}" />
                <x-admin-form-input name="category_id" label="Category" type="select" :options="['' => 'Select Category']" value="{{ old('category_id', $product->category_id ?? '') }}" />
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <xadmin-form-input name="price" label="Price" type="number" step="0.01" required value="{{ old('price', $product->price ?? '') }}" />
                <x-admin-form-input name="cost" label="Cost" type="number" step="0.01" value="{{ old('cost', $product->cost ?? '') }}" />
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <x-admin-form-input name="stock" label="Stock" type="number" required value="{{ old('stock', $product->stock ?? '') }}" />
                <x-admin-form-input name="min_stock" label="Min Stock" type="number" value="{{ old('min_stock', $product->min_stock ?? '') }}" />
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <x-admin-form-input name="status" label="Status" type="select" :options="['active' => 'Active', 'inactive' => 'Inactive']" value="{{ old('status', $product->status ?? 'active') }}" />
                <x-admin-form-input name="image" label="Image" type="file" />
            </div>
            
            <x-admin-form-input name="description" label="Description" type="textarea" value="{{ old('description', $product->description ?? '') }}" />
            
            <div class="flex items-center justify-end space-x-3 mt-6">
                <button type="button" @click="open = false" class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200">
                    Cancel
                </button>
                <x-admin-button type="submit" variant="primary" icon="fa-solid fa-save">
                    Update Product
                </x-admin-button>
            </div>
        </form>
    </x-admin-modal>
</div>
