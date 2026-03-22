<div x-data="{ open: false }" @open-create-modal.window="open = true">
    <x-admin-modal id="create-order-modal" title="Create New Order" size="xl">
        <form method="POST" action="{{ route('admin.orders.store') }}">
            @csrf
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="mb-4">
                    <label for="customer_id" class="block text-sm font-medium text-gray-700 mb-1">Customer</label>
                    <select name="customer_id" id="customer_id"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <option value="">-- Walk-in / Guest --</option>
                        @foreach ($customers ?? [] as $customer)
                            <option value="{{ $customer->id }}">{{ $customer->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-4">
                    <label for="outlet_id" class="block text-sm font-medium text-gray-700 mb-1">Outlet <span class="text-red-500">*</span></label>
                    <select name="outlet_id" id="outlet_id" required
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <option value="">Select Outlet</option>
                        @foreach ($outlets ?? [] as $outlet)
                            <option value="{{ $outlet->id }}">{{ $outlet->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Order Items</label>
                <div id="order-items" class="space-y-2">
                    <div class="flex items-center space-x-2 order-item">
                        <select name="items[0][product_id]" class="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            <option value="">Select Product</option>
                            @foreach($products ?? [] as $product)
                                <option value="{{ $product->id }}">{{ $product->name }} - ${{ number_format($product->price, 2) }}</option>
                            @endforeach
                        </select>
                        <input type="number" name="items[0][quantity]" placeholder="Qty" min="1" class="w-20 px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <button type="button" onclick="this.parentElement.remove()" class="text-red-600 hover:text-red-900">
                            <i class="fa-solid fa-trash"></i>
                        </button>
                    </div>
                </div>
                <button type="button" onclick="addOrderItem()" class="mt-2 text-sm text-blue-600 hover:text-blue-700">
                    <i class="fa-solid fa-plus mr-1"></i> Add Item
                </button>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <x-admin-form-input name="payment_method" label="Payment Method" type="select" :options="['cash' => 'Cash', 'card' => 'Card', 'transfer' => 'Bank Transfer', 'e-wallet' => 'E-Wallet']" />
                <x-admin-form-input name="status" label="Status" type="select" :options="['pending' => 'Pending', 'completed' => 'Completed', 'cancelled' => 'Cancelled']" />
            </div>
            
            <x-admin-form-input name="notes" label="Notes" type="textarea" placeholder="Order notes" />
            
            <div class="flex items-center justify-end space-x-3 mt-6">
                <button type="button" @click="open = false" class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200">
                    Cancel
                </button>
                <x-admin-button type="submit" variant="primary" icon="fa-solid fa-save">
                    Create Order
                </x-admin-button>
            </div>
        </form>
    </x-admin-modal>
</div>

@php
    $productsForJs = ($products ?? collect())->map(function ($p) {
        return ['id' => $p->id, 'name' => $p->name, 'price' => (float) $p->price];
    })->values()->all();
@endphp
<script>
const orderProducts = @json($productsForJs);

function buildProductOptions(selectedId) {
    let html = '<option value="">Select Product</option>';
    orderProducts.forEach(function(p) {
        const price = parseFloat(p.price).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        const selected = (selectedId && selectedId == p.id) ? ' selected' : '';
        html += `<option value="${p.id}"${selected}>${p.name} - Rp ${price}</option>`;
    });
    return html;
}

function addOrderItem() {
    const container = document.getElementById('order-items');
    const itemCount = container.querySelectorAll('.order-item').length;
    const newItem = document.createElement('div');
    newItem.className = 'flex items-center space-x-2 order-item';
    newItem.innerHTML = `
        <select name="items[${itemCount}][product_id]" class="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
            ${buildProductOptions(null)}
        </select>
        <input type="number" name="items[${itemCount}][quantity]" placeholder="Qty" min="1" value="1"
            class="w-20 px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
        <button type="button" onclick="this.parentElement.remove()" class="text-red-600 hover:text-red-900">
            <i class="fa-solid fa-trash"></i>
        </button>
    `;
    container.appendChild(newItem);
}
</script>
