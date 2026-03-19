<div x-data="{ open: false }" @open-create-modal.window="open = true">
    <x-admin-modal id="create-product-modal" title="Create New Product" size="lg">
        <form method="POST" action="{{ route('admin.products.store') }}" enctype="multipart/form-data">
            @csrf

            <x-admin-form-input name="name" label="Product Name" required placeholder="Enter product name" />

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <x-admin-form-input name="sku" label="SKU" required placeholder="PROD-001" />
                <x-admin-form-input name="category_id" label="Category" type="select" :options="['' => 'Select Category'] + ($categories ?? collect())->pluck('name', 'id')->toArray()" />
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <x-admin-form-input name="price" label="Price" type="number" step="0.01" required
                    placeholder="0.00" />
                <x-admin-form-input name="cost_price" label="Cost Price" type="number" step="0.01"
                    placeholder="0.00" />
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <x-admin-form-input name="unit" label="Unit" placeholder="pcs, kg, etc." />
                <x-admin-form-input name="is_active" label="Status" type="select" :options="[1 => 'Active', 0 => 'Inactive']" />
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <x-admin-form-input name="outlet_id" label="Outlet (for initial stock)" type="select"
                    :options="['' => 'Select Outlet'] + ($outlets ?? collect())->pluck('name', 'id')->toArray()" />
                <x-admin-form-input name="stock" label="Initial Stock" type="number" step="0.001"
                    placeholder="0" />
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <x-admin-form-input name="low_stock_threshold" label="Low Stock Alert Threshold" type="number"
                    step="0.001" placeholder="5" />
                <x-admin-form-input name="image" label="Image" type="file" />
            </div>

            <x-admin-form-input name="description" label="Description" type="textarea"
                placeholder="Enter product description" />

            <div class="flex items-center justify-end space-x-3 mt-6">
                <button type="button" @click="open = false"
                    class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200">
                    Cancel
                </button>
                <x-admin-button type="submit" variant="primary" icon="fa-solid fa-save">
                    Create Product
                </x-admin-button>
            </div>
        </form>
    </x-admin-modal>
</div>
