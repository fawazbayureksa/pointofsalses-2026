
<div x-data="{ open: false }" @open-show-modal.window="open = true">
    <x-admin-modal id="show-order-modal" title="Order Details" size="xl">
        <div class="space-y-6">
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <p class="text-sm font-medium text-gray-500">Order ID</p>
                    <p class="text-lg font-semibold">#{{ $order->id ?? '' }}</p>
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-500">Status</p>
                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full
                        @if(($order->status ?? '') === 'completed') bg-green-100 text-green-800
                        @elseif(($order->status ?? '') === 'pending') bg-yellow-100 text-yellow-800
                        @elseif(($order->status ?? '') === 'cancelled') bg-red-100 text-red-800
                        @else bg-gray-100 text-gray-800 @endif">
                        {{ ucfirst($order->status ?? '') }}
                    </span>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <p class="text-sm font-medium text-gray-500">Customer</p>
                    <p class="text-lg font-semibold">{{ $order->customer->name ?? 'Guest' }}</p>
                    <p class="text-sm text-gray-600">{{ $order->customer->email ?? '' }}</p>
                    <p class="text-sm text-gray-600">{{ $order->customer->phone ?? '' }}</p>
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-500">Outlet</p>
                    <p class="text-lg font-semibold">{{ $order->outlet->name ?? '-' }}</p>
                    <p class="text-sm text-gray-600">{{ $order->outlet->address ?? '' }}</p>
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
                            @foreach($order->items ?? [] as $item)
                                <tr class="border-t border-gray-200">
                                    <td class="py-2">{{ $item->product->name ?? '-' }}</td>
                                    <td class="py-2">${{ number_format($item->price ?? 0, 2) }}</td>
                                    <td class="py-2">{{ $item->quantity ?? 0 }}</td>
                                    <td class="py-2">${{ number_format(($item->price ?? 0) * ($item->quantity ?? 0), 2) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="border-t border-gray-300">
                            <tr>
                                <td colspan="3" class="pt-2 text-right font-semibold">Total:</td>
                                <td class="pt-2 font-bold">${{ number_format($order->total_amount ?? 0, 2) }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <p class="text-sm font-medium text-gray-500">Payment Method</p>
                    <p class="text-lg font-semibold">{{ ucfirst($order->payment_method ?? '') }}</p>
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-500">Created At</p>
                    <p class="text-lg font-semibold">{{ $order->created_at?->format('M d, Y H:i') ?? '' }}</p>
                </div>
            </div>

            @if($order->notes ?? null)
                <div>
                    <p class="text-sm font-medium text-gray-500">Notes</p>
                    <p class="text-gray-900">{{ $order->notes }}</p>
                </div>
            @endif
        </div>

        <div class="flex items-center justify-end space-x-3 mt-6 pt-6 border-t border-gray-200">
            <button type="button" @click="open = false" class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200">
                Close
            </button>
            <a href="{{ route('admin.orders.print', $order->id ?? '') }}" class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700">
                <i class="fa-solid fa-print mr-2"></i>Print
            </a>
        </div>
    </x-admin-modal>
</div>
