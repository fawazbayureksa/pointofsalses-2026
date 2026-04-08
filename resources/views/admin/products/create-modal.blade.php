<div x-data="{ open: false, barcodeValue: '', photoPreview: null }" @open-create-modal.window="open = true"
    @barcode-scanned.window="if ($event.detail.target === 'create') { barcodeValue = $event.detail.code }"
    @product-photo-captured.window="
        if ($event.detail.target === 'create') {
            photoPreview = $event.detail.dataUrl;
            $nextTick(() => {
                const inp = $el.querySelector('input[name=image]');
                if (inp && $event.detail.blob) {
                    const dt = new DataTransfer();
                    dt.items.add(new File([$event.detail.blob], 'product-photo.jpg', { type: 'image/jpeg' }));
                    inp.files = dt.files;
                }
            });
        }
    ">
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

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <x-admin-form-input name="unit" label="Unit" placeholder="pcs, kg, etc." />
                <x-admin-form-input name="is_active" label="Status" type="select" :options="[1 => 'Active', 0 => 'Inactive']" />
                <x-admin-form-input name="track_stock" label="Track Stock" type="select" :options="[1 => 'Yes', 0 => 'No']"
                    value="1" />
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
                {{-- Image upload + camera capture --}}
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Image</label>
                    <div class="flex flex-col gap-2">
                        {{-- Preview --}}
                        <div x-show="photoPreview"
                            class="relative rounded-xl overflow-hidden bg-gray-100 aspect-video w-full">
                            <img :src="photoPreview" class="w-full h-full object-cover" alt="Preview">
                            <button type="button"
                                @click="photoPreview = null; $el.closest('.mb-4').querySelector('input[type=file]').value = ''"
                                class="absolute top-2 right-2 p-1.5 bg-white/80 hover:bg-white rounded-full text-gray-600 hover:text-red-500 shadow transition-colors"
                                title="Remove photo">
                                <i class="fa-solid fa-xmark text-sm"></i>
                            </button>
                        </div>
                        {{-- File input --}}
                        <input type="file" name="image" accept="image/*"
                            @change="photoPreview = $event.target.files[0] ? URL.createObjectURL($event.target.files[0]) : null"
                            class="block w-full text-sm text-gray-600 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 transition-colors">
                        {{-- Camera button --}}
                        <button type="button"
                            @click="window.dispatchEvent(new CustomEvent('open-photo-capture', { detail: { target: 'create' } }))"
                            class="flex items-center justify-center gap-2 w-full py-2 border-2 border-dashed border-blue-300 hover:border-blue-500 text-blue-600 hover:text-blue-700 rounded-xl text-sm font-medium transition-colors hover:bg-blue-50">
                            <i class="fa-solid fa-camera"></i>
                            Take Photo with Camera
                        </button>
                    </div>
                </div>
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
