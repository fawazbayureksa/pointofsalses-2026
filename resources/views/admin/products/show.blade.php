@extends('layouts.admin')

@section('title', $product->name)

@section('page-title', 'Product Details')

@section('content')
    <div class="space-y-6">

        <div>
            <a href="{{ route('admin.products.index') }}"
                class="inline-flex items-center text-sm text-gray-500 hover:text-gray-700">
                <i class="fa-solid fa-arrow-left mr-2"></i>
                Back to Products
            </a>
        </div>

        <x-admin-card :title="$product->name">
            @if ($product->image)
                <div class="mb-6">
                    <img src="{{ asset('storage/' . $product->image) }}" alt="{{ $product->name }}"
                        class="h-48 w-48 object-cover rounded-lg border border-gray-200">
                </div>
            @endif
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="space-y-4">
                    <div>
                        <span class="text-xs font-medium text-gray-500 uppercase tracking-wider">SKU</span>
                        <p class="mt-1 text-sm text-gray-900">{{ $product->sku ?? '—' }}</p>
                    </div>
                    <div>
                        <span class="text-xs font-medium text-gray-500 uppercase tracking-wider">Barcode</span>
                        <p class="mt-1 text-sm text-gray-900">{{ $product->barcode ?? '—' }}</p>
                    </div>
                    <div>
                        <span class="text-xs font-medium text-gray-500 uppercase tracking-wider">Category</span>
                        <p class="mt-1 text-sm text-gray-900">{{ $product->category?->name ?? '—' }}</p>
                    </div>
                    <div>
                        <span class="text-xs font-medium text-gray-500 uppercase tracking-wider">Unit</span>
                        <p class="mt-1 text-sm text-gray-900">{{ $product->unit ?? '—' }}</p>
                    </div>
                </div>
                <div class="space-y-4">
                    <div>
                        <span class="text-xs font-medium text-gray-500 uppercase tracking-wider">Price</span>
                        <p class="mt-1 text-sm text-gray-900">{{ number_format($product->price, 2) }}</p>
                    </div>
                    <div>
                        <span class="text-xs font-medium text-gray-500 uppercase tracking-wider">Cost Price</span>
                        <p class="mt-1 text-sm text-gray-900">
                            {{ $product->cost_price ? number_format($product->cost_price, 2) : '—' }}</p>
                    </div>
                    <div>
                        <span class="text-xs font-medium text-gray-500 uppercase tracking-wider">Status</span>
                        <p class="mt-1">
                            @if ($product->is_active)
                                <span
                                    class="inline-flex px-2 py-0.5 text-xs font-medium rounded-full bg-green-100 text-green-800">Active</span>
                            @else
                                <span
                                    class="inline-flex px-2 py-0.5 text-xs font-medium rounded-full bg-red-100 text-red-800">Inactive</span>
                            @endif
                        </p>
                    </div>
                    <div>
                        <span class="text-xs font-medium text-gray-500 uppercase tracking-wider">Track Stock</span>
                        <p class="mt-1 text-sm text-gray-900">{{ $product->track_stock ? 'Yes' : 'No' }}</p>
                    </div>
                </div>
            </div>

            @if ($product->description)
                <div class="mt-4 pt-4 border-t border-gray-200">
                    <span class="text-xs font-medium text-gray-500 uppercase tracking-wider">Description</span>
                    <p class="mt-1 text-sm text-gray-700">{{ $product->description }}</p>
                </div>
            @endif

            @can('edit products')
                <div class="mt-6 pt-4 border-t border-gray-200 flex justify-end">
                    <button type="button"
                        onclick="window.dispatchEvent(new CustomEvent('open-edit-modal', { detail: {
                            id: {{ $product->id }},
                            name: {{ Js::from($product->name) }},
                            sku: {{ Js::from($product->sku ?? '') }},
                            barcode: {{ Js::from($product->barcode ?? '') }},
                            description: {{ Js::from($product->description ?? '') }},
                            price: {{ Js::from((string) $product->price) }},
                            cost_price: {{ Js::from((string) ($product->cost_price ?? '')) }},
                            unit: {{ Js::from($product->unit ?? '') }},
                            category_id: {{ $product->category_id ?? 'null' }},
                            is_active: {{ $product->is_active ? 1 : 0 }},
                            track_stock: {{ $product->track_stock ? 1 : 0 }}
                        }}))"
                        class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg">
                        <i class="fa-solid fa-edit mr-2"></i> Edit Product
                    </button>
                </div>
            @endcan
        </x-admin-card>

        {{-- Stock by outlet --}}
        <x-admin-card title="Stock by Outlet">
            @if ($product->outlets->isEmpty())
                <p class="text-sm text-gray-500">No stock assigned to any outlet yet.</p>
            @else
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Outlet</th>
                                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Stock</th>
                                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Low Stock Alert</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Status</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach ($product->outlets as $outlet)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-4 py-3 font-medium text-gray-900">{{ $outlet->name }}</td>
                                    <td class="px-4 py-3 text-right">
                                        <span
                                            class="{{ $outlet->pivot->stock <= $outlet->pivot->low_stock_threshold ? 'text-red-600 font-semibold' : 'text-gray-900' }}">
                                            {{ $outlet->pivot->stock }} {{ $product->unit }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-right text-gray-600">
                                        {{ $outlet->pivot->low_stock_threshold }} {{ $product->unit }}
                                    </td>
                                    <td class="px-4 py-3">
                                        @if ($outlet->pivot->stock <= $outlet->pivot->low_stock_threshold)
                                            <span
                                                class="inline-flex px-2 py-0.5 text-xs font-medium rounded-full bg-red-100 text-red-800">Low
                                                Stock</span>
                                        @else
                                            <span
                                                class="inline-flex px-2 py-0.5 text-xs font-medium rounded-full bg-green-100 text-green-800">OK</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </x-admin-card>

    </div>

    @php
        $categories = \App\Models\Category::where('is_active', true)->get();
    @endphp
    @include('admin.products.edit-modal')
@endsection
