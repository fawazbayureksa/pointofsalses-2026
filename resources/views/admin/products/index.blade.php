@extends('layouts.admin')

@section('title', 'Products')

@section('page-title', 'Products Management')

@section('content')
    <div class="space-y-6">
        @if ($errors->any())
            <div class="bg-red-50 border border-red-200 rounded-lg p-4">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <i class="fa-solid fa-circle-exclamation text-red-400"></i>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-sm font-medium text-red-800">Errors</h3>
                        <div class="mt-2 text-sm text-red-700">
                            <ul class="list-disc list-inside">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        @if (session('success'))
            <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <i class="fa-solid fa-circle-check text-green-400"></i>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-green-800">{{ session('success') }}</p>
                    </div>
                </div>
            </div>
        @endif

        <x-admin-card title="All Products">
            @php
                $tableHeaders = [
                    ['label' => 'ID', 'key' => 'id'],
                    ['label' => 'Name', 'key' => 'name'],
                    ['label' => 'SKU', 'key' => 'sku'],
                    [
                        'label' => 'Category',
                        'slot' => function ($product) {
                            return optional($product->category)->name ?? '-';
                        },
                    ],
                    ['label' => 'Price', 'key' => 'price'],
                    [
                        'label' => 'Stock',
                        'slot' => function ($product) {
                            $total = $product->outlets->sum(fn($o) => $o->pivot->stock);
                            return $total . ' ' . ($product->unit ?? '');
                        },
                    ],
                    [
                        'label' => 'Status',
                        'slot' => function ($product) {
                            return $product->is_active
                                ? '<span class="px-2 py-1 text-xs font-semibold bg-green-100 text-green-800 rounded-full">Active</span>'
                                : '<span class="px-2 py-1 text-xs font-semibold bg-red-100 text-red-800 rounded-full">Inactive</span>';
                        },
                    ],
                    ['label' => 'Created At', 'key' => 'created_at'],
                ];
            @endphp
            <div class="mb-4 flex items-center justify-between">
                <div class="flex items-center space-x-4">
                    <input type="text" placeholder="Search products..."
                        class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent w-64">
                    <select
                        class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <option value="">All Categories</option>
                        @foreach ($categories ?? [] as $category)
                            <option value="{{ $category->id }}">{{ $category->name }}</option>
                        @endforeach
                    </select>
                    <select
                        class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <option value="">All Status</option>
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                </div>
                @can('create products')
                    <x-admin-button variant="primary" icon="fa-solid fa-plus">
                        <button x-data @click="$dispatch('open-create-modal')">Add New Product</button>
                    </x-admin-button>
                @endcan
            </div>

            <x-admin-table :headers="$tableHeaders" :rows="$products ?? []" :actions="fn($product) => view('admin.products.actions', ['product' => $product])->render()" />
        </x-admin-card>
    </div>

    @include('admin.products.create-modal')
    @include('admin.products.edit-modal')
@endsection
