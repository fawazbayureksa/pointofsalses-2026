@extends('layouts.admin')

@section('title', $outlet->name)

@section('page-title', 'Outlet Details')

@section('content')
    <div class="space-y-6">

        {{-- Back button --}}
        <div>
            <a href="{{ route('admin.outlets.index') }}"
                class="inline-flex items-center text-sm text-gray-500 hover:text-gray-700">
                <i class="fa-solid fa-arrow-left mr-2"></i>
                Back to Outlets
            </a>
        </div>

        {{-- Outlet Info --}}
        <x-admin-card :title="$outlet->name">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="space-y-4">
                    <div>
                        <span class="text-xs font-medium text-gray-500 uppercase tracking-wider">Code</span>
                        <p class="mt-1 text-sm text-gray-900">{{ $outlet->code ?? '—' }}</p>
                    </div>
                    <div>
                        <span class="text-xs font-medium text-gray-500 uppercase tracking-wider">Phone</span>
                        <p class="mt-1 text-sm text-gray-900">{{ $outlet->phone ?? '—' }}</p>
                    </div>
                    <div>
                        <span class="text-xs font-medium text-gray-500 uppercase tracking-wider">Email</span>
                        <p class="mt-1 text-sm text-gray-900">{{ $outlet->email ?? '—' }}</p>
                    </div>
                </div>
                <div class="space-y-4">
                    <div>
                        <span class="text-xs font-medium text-gray-500 uppercase tracking-wider">Address</span>
                        <p class="mt-1 text-sm text-gray-900">{{ $outlet->address ?? '—' }}</p>
                    </div>
                    <div>
                        <span class="text-xs font-medium text-gray-500 uppercase tracking-wider">City</span>
                        <p class="mt-1 text-sm text-gray-900">{{ $outlet->city ?? '—' }}</p>
                    </div>
                    <div>
                        <span class="text-xs font-medium text-gray-500 uppercase tracking-wider">Status</span>
                        <p class="mt-1">
                            @if ($outlet->is_active)
                                <span
                                    class="inline-flex px-2 py-0.5 text-xs font-medium rounded-full bg-green-100 text-green-800">Active</span>
                            @else
                                <span
                                    class="inline-flex px-2 py-0.5 text-xs font-medium rounded-full bg-red-100 text-red-800">Inactive</span>
                            @endif
                        </p>
                    </div>
                </div>
            </div>

            @can('edit outlets')
                <div class="mt-6 pt-4 border-t border-gray-200 flex justify-end">
                    <button type="button"
                        onclick="window.dispatchEvent(new CustomEvent('open-edit-modal', { detail: {
                            id: {{ $outlet->id }},
                            name: {{ Js::from($outlet->name) }},
                            code: {{ Js::from($outlet->code) }},
                            phone: {{ Js::from($outlet->phone) }},
                            email: {{ Js::from($outlet->email) }},
                            address: {{ Js::from($outlet->address) }},
                            city: {{ Js::from($outlet->city) }},
                            is_active: {{ $outlet->is_active ? 1 : 0 }}
                        }}))"
                        class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg">
                        <i class="fa-solid fa-edit mr-2"></i> Edit Outlet
                    </button>
                </div>
            @endcan
        </x-admin-card>

        {{-- Assigned Users --}}
        <x-admin-card title="Assigned Staff">
            @if ($outlet->users->isEmpty())
                <p class="text-sm text-gray-500">No staff assigned to this outlet.</p>
            @else
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Name</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Email</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Role</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Default</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach ($outlet->users as $user)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-4 py-3 font-medium text-gray-900">{{ $user->name }}</td>
                                    <td class="px-4 py-3 text-gray-600">{{ $user->email }}</td>
                                    <td class="px-4 py-3">{{ $user->roles->pluck('name')->join(', ') ?: '—' }}</td>
                                    <td class="px-4 py-3">
                                        @if ($user->pivot->is_default)
                                            <span
                                                class="inline-flex px-2 py-0.5 text-xs font-medium rounded-full bg-blue-100 text-blue-800">Default</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </x-admin-card>

        {{-- Products at this outlet --}}
        <x-admin-card title="Products">
            @if ($outlet->products->isEmpty())
                <p class="text-sm text-gray-500">No products assigned to this outlet.</p>
            @else
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Product</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    SKU</th>
                                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Stock</th>
                                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Low Stock Alert</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach ($outlet->products as $product)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-4 py-3 font-medium text-gray-900">{{ $product->name }}</td>
                                    <td class="px-4 py-3 text-gray-600">{{ $product->sku ?? '—' }}</td>
                                    <td class="px-4 py-3 text-right">
                                        <span
                                            class="{{ $product->pivot->stock <= $product->pivot->low_stock_threshold ? 'text-red-600 font-semibold' : 'text-gray-900' }}">
                                            {{ $product->pivot->stock }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-right text-gray-600">
                                        {{ $product->pivot->low_stock_threshold }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </x-admin-card>

    </div>

    @include('admin.outlets.edit-modal')
@endsection
