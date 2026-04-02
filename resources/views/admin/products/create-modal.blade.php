<div x-data="{ open: false, barcodeValue: '' }" @open-create-modal.window="open = true"
    @barcode-scanned.window="if ($event.detail.target === 'create') { barcodeValue = $event.detail.code }">
    <x-admin-modal id="create-product-modal" title="Create New Product" size="lg">
        <form method="POST" action="{{ route('admin.products.store') }}" enctype="multipart/form-data">
            @csrf

            <x-admin-form-input name="name" label="Product Name" required placeholder="Enter product name" />

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <x-admin-form-input name="sku" label="SKU" required placeholder="PROD-001" />
                <x-admin-form-input name="category_id" label="Category" type="select" :options="['' => 'Select Category'] + ($categories ?? collect())->pluck('name', 'id')->toArray()" />
            </div>

            {{-- Barcode field with scan button --}}
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Barcode</label>
                <div class="flex items-center gap-2">
                    <div class="relative flex-1">
                        <i class="fa-solid fa-barcode absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                        <input type="text" name="barcode" x-model="barcodeValue" placeholder="Scan or type barcode…"
                            class="w-full pl-9 pr-4 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    </div>
                    <button type="button"
                        @click="window.dispatchEvent(new CustomEvent('open-barcode-scanner', { detail: { target: 'create' } }))"
                        title="Scan with camera"
                        class="flex items-center gap-1.5 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-sm font-medium transition-colors shrink-0">
                        <i class="fa-solid fa-camera"></i>
                        <span>Scan</span>
                    </button>
                </div>
                <p class="text-xs text-gray-400 mt-1">Use a USB barcode scanner or click Scan to use your camera.</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
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
