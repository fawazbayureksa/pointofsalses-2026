@extends('layouts.admin')

@section('title', 'Stock Management')
@section('page-title', 'Stock Management')

@section('content')
    <div x-data="{ adjustOpen: false, assignOpen: false, adjustProduct: {}, assignProduct: {} }" class="space-y-6">

        @if (session('success'))
            <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                <p class="text-sm font-medium text-green-800">{{ session('success') }}</p>
            </div>
        @endif
        @if ($errors->any())
            <div class="bg-red-50 border border-red-200 rounded-lg p-4">
                <ul class="list-disc list-inside text-sm text-red-700">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <x-admin-card title="Current Stock Levels">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Product</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">SKU</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Outlet</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Stock</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Low Stock Alert</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Action</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($products as $product)
                            @forelse($product->outlets as $outlet)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 text-sm font-medium text-gray-900">{{ $product->name }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-600">{{ $product->sku ?? '-' }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-600">{{ $outlet->name }}</td>
                                    <td
                                        class="px-6 py-4 text-sm font-semibold {{ $outlet->pivot->stock <= $outlet->pivot->low_stock_threshold ? 'text-red-600' : 'text-gray-900' }}">
                                        {{ $outlet->pivot->stock }} {{ $product->unit }}
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-600">{{ $outlet->pivot->low_stock_threshold }}
                                        {{ $product->unit }}</td>
                                    <td class="px-6 py-4">
                                        @if ($outlet->pivot->stock <= $outlet->pivot->low_stock_threshold)
                                            <span
                                                class="px-2 py-1 text-xs font-semibold bg-red-100 text-red-800 rounded-full">Low
                                                Stock</span>
                                        @else
                                            <span
                                                class="px-2 py-1 text-xs font-semibold bg-green-100 text-green-800 rounded-full">In
                                                Stock</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <button
                                            @click="adjustOpen = true; adjustProduct = {
                                            id: {{ $product->id }},
                                            name: {{ Js::from($product->name) }},
                                            outlet_id: {{ $outlet->id }},
                                            outlet_name: {{ Js::from($outlet->name) }},
                                            stock: {{ $outlet->pivot->stock }},
                                            unit: {{ Js::from($product->unit ?? 'pcs') }},
                                            url: '{{ route('admin.products.adjust-stock', $product->id) }}'
                                        }"
                                            class="text-xs px-3 py-1.5 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                                            <i class="fa-solid fa-sliders mr-1"></i>Adjust
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 text-sm font-medium text-gray-900">{{ $product->name }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-600">{{ $product->sku ?? '-' }}</td>
                                    <td colspan="4" class="px-6 py-4 text-sm text-gray-400 italic">No outlets assigned
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <button
                                            @click="assignOpen = true; assignProduct = {
                                            id: {{ $product->id }},
                                            name: {{ Js::from($product->name) }},
                                            unit: {{ Js::from($product->unit ?? 'pcs') }},
                                            url: '{{ route('admin.products.adjust-stock', $product->id) }}'
                                        }"
                                            class="text-xs px-3 py-1.5 bg-gray-600 text-white rounded-lg hover:bg-gray-700">
                                            <i class="fa-solid fa-link mr-1"></i>Assign Outlet
                                        </button>
                                    </td>
                                </tr>
                            @endforelse
                        @empty
                            <tr>
                                <td colspan="7" class="px-6 py-12 text-center text-gray-500">No products with stock
                                    tracking enabled.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-4">{{ $products->links() }}</div>
        </x-admin-card>

        {{-- Adjust Stock Modal --}}
        <div x-show="adjustOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">
            <div class="bg-white rounded-xl shadow-xl w-full max-w-md" @click.outside="adjustOpen = false">
                <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
                    <h3 class="text-base font-semibold text-gray-900">Adjust Stock</h3>
                    <button @click="adjustOpen = false" class="text-gray-400 hover:text-gray-600"><i
                            class="fa-solid fa-times"></i></button>
                </div>
                <form method="POST" :action="adjustProduct.url">
                    @csrf
                    <div class="px-6 py-5 space-y-4">
                        <div class="bg-gray-50 rounded-lg p-3 text-sm">
                            <p class="font-medium text-gray-900" x-text="adjustProduct.name"></p>
                            <p class="text-gray-500" x-text="'Outlet: ' + adjustProduct.outlet_name"></p>
                            <p class="text-gray-500">Current stock: <span class="font-semibold text-gray-900"
                                    x-text="adjustProduct.stock + ' ' + adjustProduct.unit"></span></p>
                        </div>
                        <input type="hidden" name="outlet_id" :value="adjustProduct.outlet_id">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Adjustment Quantity <span class="text-gray-400 font-normal">(use negative to reduce)</span>
                            </label>
                            <div class="flex items-center border border-gray-300 rounded-lg overflow-hidden">
                                <span class="px-3 py-2 bg-gray-50 text-gray-500 border-r border-gray-300 text-sm"
                                    x-text="adjustProduct.unit"></span>
                                <input type="number" name="quantity" step="0.001" placeholder="e.g. 10 or -5" required
                                    class="flex-1 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                            </div>
                            <p class="mt-1 text-xs text-gray-400">Positive = add stock &nbsp;|&nbsp; Negative = reduce stock
                            </p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Reason <span
                                    class="text-gray-400 font-normal">(optional)</span></label>
                            <input type="text" name="reason" placeholder="e.g. Restock, Damage, Audit correction"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                    </div>
                    <div class="px-6 py-4 border-t border-gray-200 flex items-center justify-end gap-3">
                        <button type="button" @click="adjustOpen = false"
                            class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200">Cancel</button>
                        <button type="submit"
                            class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700">
                            <i class="fa-solid fa-check mr-1"></i>Apply Adjustment
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Assign to Outlet Modal --}}
        <div x-show="assignOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">
            <div class="bg-white rounded-xl shadow-xl w-full max-w-md" @click.outside="assignOpen = false">
                <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
                    <h3 class="text-base font-semibold text-gray-900">Assign to Outlet & Set Stock</h3>
                    <button @click="assignOpen = false" class="text-gray-400 hover:text-gray-600"><i
                            class="fa-solid fa-times"></i></button>
                </div>
                <form method="POST" :action="assignProduct.url">
                    @csrf
                    <div class="px-6 py-5 space-y-4">
                        <p class="text-sm font-medium text-gray-900" x-text="assignProduct.name"></p>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Outlet *</label>
                            <select name="outlet_id" required
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="">Select Outlet</option>
                                @foreach (\App\Models\Outlet::where('is_active', true)->get() as $o)
                                    <option value="{{ $o->id }}">{{ $o->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Initial Stock</label>
                            <div class="flex items-center border border-gray-300 rounded-lg overflow-hidden">
                                <span class="px-3 py-2 bg-gray-50 text-gray-500 border-r border-gray-300 text-sm"
                                    x-text="assignProduct.unit"></span>
                                <input type="number" name="quantity" step="0.001" min="0" placeholder="0"
                                    required
                                    class="flex-1 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                            </div>
                        </div>
                    </div>
                    <div class="px-6 py-4 border-t border-gray-200 flex items-center justify-end gap-3">
                        <button type="button" @click="assignOpen = false"
                            class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200">Cancel</button>
                        <button type="submit"
                            class="px-4 py-2 text-sm font-medium text-white bg-green-600 rounded-lg hover:bg-green-700">
                            <i class="fa-solid fa-link mr-1"></i>Assign & Set Stock
                        </button>
                    </div>
                </form>
            </div>
        </div>

    </div>
@endsection
