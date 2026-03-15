@extends('layouts.admin')

@section('title', 'Stock Management')
@section('page-title', 'Stock Management')

@section('content')
    <div class="space-y-6">
        @if (session('success'))
            <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                <p class="text-sm font-medium text-green-800">{{ session('success') }}</p>
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
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Low Stock Threshold
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($products as $product)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 text-sm font-medium text-gray-900">{{ $product->name }}</td>
                                <td class="px-6 py-4 text-sm text-gray-600">{{ $product->sku ?? '-' }}</td>
                                <td class="px-6 py-4 text-sm text-gray-600">{{ $product->outlet->name ?? '-' }}</td>
                                <td
                                    class="px-6 py-4 text-sm font-semibold {{ $product->stock <= $product->low_stock_threshold ? 'text-red-600' : 'text-gray-900' }}">
                                    {{ $product->stock }} {{ $product->unit }}
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-600">{{ $product->low_stock_threshold }}
                                    {{ $product->unit }}</td>
                                <td class="px-6 py-4">
                                    @if ($product->stock <= $product->low_stock_threshold)
                                        <span
                                            class="px-2 py-1 text-xs font-semibold bg-red-100 text-red-800 rounded-full">Low
                                            Stock</span>
                                    @else
                                        <span
                                            class="px-2 py-1 text-xs font-semibold bg-green-100 text-green-800 rounded-full">In
                                            Stock</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-12 text-center text-gray-500">No products with stock
                                    tracking enabled.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-4">{{ $products->links() }}</div>
        </x-admin-card>
    </div>
@endsection
