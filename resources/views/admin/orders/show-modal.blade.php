<div x-data="{
    open: false,
    loading: false,
    order: null,
    openModal(id) {
        this.open = true;
        this.loading = true;
        this.order = null;
        fetch(`/admin/orders/${id}`, { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } })
            .then(r => r.json())
            .then(data => { this.order = data; })
            .finally(() => { this.loading = false; });
    },
    fmt(num) {
        return 'Rp ' + Number(num ?? 0).toLocaleString('id-ID');
    },
    statusClass(s) {
        const map = { completed: 'bg-green-100 text-green-800', pending: 'bg-yellow-100 text-yellow-800', cancelled: 'bg-red-100 text-red-800' };
        return map[s] ?? 'bg-gray-100 text-gray-800';
    }
}" @open-show-modal.window="openModal($event.detail.id)">
    <x-admin-modal id="show-order-modal" title="Order Details" size="xl">
        {{-- Loading --}}
        <div x-show="loading" class="flex items-center justify-center py-12">
            <i class="fa-solid fa-spinner fa-spin text-2xl text-gray-400"></i>
        </div>

        {{-- Content --}}
        <div x-show="!loading && order" class="space-y-6">
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <p class="text-sm font-medium text-gray-500">Order ID</p>
                    <p class="text-lg font-semibold" x-text="order ? '#' + (order.order_number ?? order.id) : ''"></p>
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-500">Status</p>
                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full"
                        :class="order ? statusClass(order.status) : ''"
                        x-text="order ? (order.status.charAt(0).toUpperCase() + order.status.slice(1)) : ''"></span>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <p class="text-sm font-medium text-gray-500">Customer</p>
                    <p class="text-lg font-semibold" x-text="order?.customer?.name ?? 'Guest'"></p>
                    <p class="text-sm text-gray-600" x-text="order?.customer?.email ?? ''"></p>
                    <p class="text-sm text-gray-600" x-text="order?.customer?.phone ?? ''"></p>
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-500">Outlet</p>
                    <p class="text-lg font-semibold" x-text="order?.outlet?.name ?? '-'"></p>
                    <p class="text-sm text-gray-600" x-text="order?.outlet?.address ?? ''"></p>
                </div>
            </div>

            <div>
                <h4 class="text-lg font-semibold mb-3">Order Items</h4>
                <div class="bg-gray-50 rounded-lg p-4">
                    <table class="w-full">
                        <thead>
                            <tr class="text-left text-sm text-gray-500">
                                <th class="pb-2">Product</th>
                                <th class="pb-2">Price</th>
                                <th class="pb-2">Qty</th>
                                <th class="pb-2">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody class="text-sm">
                            <template x-if="order" x-for="item in order.items" :key="item.product_name">
                                <tr class="border-t border-gray-200">
                                    <td class="py-2" x-text="item.product_name"></td>
                                    <td class="py-2" x-text="fmt(item.unit_price)"></td>
                                    <td class="py-2" x-text="item.quantity"></td>
                                    <td class="py-2" x-text="fmt(item.subtotal)"></td>
                                </tr>
                            </template>
                        </tbody>
                        <tfoot class="border-t border-gray-300">
                            <tr>
                                <td colspan="3" class="pt-2 text-right font-semibold">Total:</td>
                                <td class="pt-2 font-bold" x-text="order ? fmt(order.total_amount) : ''"></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <p class="text-sm font-medium text-gray-500">Payment Method</p>
                    <p class="text-lg font-semibold"
                        x-text="order?.payment_method ? (order.payment_method.charAt(0).toUpperCase() + order.payment_method.slice(1)) : '-'">
                    </p>
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-500">Created At</p>
                    <p class="text-lg font-semibold" x-text="order?.created_at ?? ''"></p>
                </div>
            </div>

            <div x-show="order?.notes">
                <p class="text-sm font-medium text-gray-500">Notes</p>
                <p class="text-gray-900" x-text="order?.notes"></p>
            </div>
        </div>

        <div class="flex items-center justify-end space-x-3 mt-6 pt-6 border-t border-gray-200">
            <button type="button" @click="open = false"
                class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200">
                Close
            </button>
            <a :href="order ? `/admin/orders/${order.id}/print` : '#'"
                class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700"
                :class="{ 'opacity-50 pointer-events-none': !order }">
                <i class="fa-solid fa-print mr-2"></i>Print
            </a>
        </div>
    </x-admin-modal>
</div>
