@extends('layouts.admin')

@section('title', 'Orders')

@section('page-title', 'Orders Management')

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

        <x-admin-card title="All Orders">
            <form method="GET" action="{{ route('admin.orders.index') }}" class="mb-4">
                <div class="flex items-center justify-between flex-wrap gap-3">
                    <div class="flex items-center space-x-3 flex-wrap gap-y-2">
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Search orders..."
                            class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent w-64">

                        <select name="status"
                            class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            <option value="">All Status</option>
                            <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending
                            </option>
                            <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Completed
                            </option>
                            <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Cancelled
                            </option>
                            <option value="refunded" {{ request('status') === 'refunded' ? 'selected' : '' }}>Refunded
                            </option>
                        </select>

                        <select name="outlet_id"
                            class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            <option value="">All Outlets</option>
                            @foreach ($outlets ?? [] as $outlet)
                                <option value="{{ $outlet->id }}"
                                    {{ request('outlet_id') == $outlet->id ? 'selected' : '' }}>
                                    {{ $outlet->name }}
                                </option>
                            @endforeach
                        </select>

                        <input type="date" name="date_from" value="{{ request('date_from') }}"
                            class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">

                        <input type="date" name="date_to" value="{{ request('date_to') }}"
                            class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">

                        <x-admin-button type="submit" variant="primary" icon="fa-solid fa-filter">
                            Filter
                        </x-admin-button>

                        <a href="{{ route('admin.orders.index') }}" class="text-sm text-gray-600 hover:text-gray-800">
                            Clear Filters
                        </a>
                    </div>

                    @can('create orders')
                        <x-admin-button variant="primary" icon="fa-solid fa-plus" x-data
                            @click="$dispatch('open-create-modal')">
                            New Order
                        </x-admin-button>
                    @endcan
                </div>
            </form>

            @php
                $tableHeaders = [
                    [
                        'label' => 'Order ID',
                        'slot' => function ($order) {
                            return '<span class="font-mono text-sm">' .
                                e($order->order_number ?? '#' . $order->id) .
                                '</span>';
                        },
                    ],
                    [
                        'label' => 'Customer',
                        'slot' => function ($order) {
                            return optional($order->customer)->name ??
                                '<span class="text-gray-400 italic">Guest</span>';
                        },
                    ],
                    [
                        'label' => 'Outlet',
                        'slot' => function ($order) {
                            return optional($order->outlet)->name ?? '-';
                        },
                    ],
                    ['label' => 'Items', 'key' => 'items_count'],
                    [
                        'label' => 'Total',
                        'slot' => function ($order) {
                            return 'Rp ' . number_format($order->total_amount ?? 0, 0, ',', '.');
                        },
                    ],
                    ['label' => 'Payment', 'key' => 'payment_method'],
                    [
                        'label' => 'Status',
                        'slot' => function ($order) {
                            $colors = [
                                'pending' => 'bg-yellow-100 text-yellow-800',
                                'processing' => 'bg-blue-100 text-blue-800',
                                'completed' => 'bg-green-100 text-green-800',
                                'cancelled' => 'bg-red-100 text-red-800',
                                'refunded' => 'bg-gray-100 text-gray-800',
                            ];
                            $status = $order->status ?? 'pending';
                            $color = $colors[$status] ?? 'bg-gray-100 text-gray-800';
                            return '<span class="px-2 py-1 text-xs font-medium rounded-full ' .
                                $color .
                                '">' .
                                ucfirst($status) .
                                '</span>';
                        },
                    ],
                    [
                        'label' => 'Created At',
                        'slot' => function ($order) {
                            return $order->created_at ? $order->created_at->format('d M Y, H:i') : '-';
                        },
                    ],
                ];

                $tableActions = function ($order) {
                    $html = '<div class="flex items-center justify-end space-x-2">';
                    $html .=
                        '<button onclick="' .
                        "event.stopPropagation(); document.dispatchEvent(new CustomEvent('open-show-modal',{detail:{id:{$order->id}},bubbles:true}))" .
                        '" class="text-blue-600 hover:text-blue-900" title="View"><i class="fa-solid fa-eye"></i></button>';
                    $html .= '</div>';
                    return $html;
                };
            @endphp

            <x-admin-table :headers="$tableHeaders" :rows="$orders ?? []" :actions="$tableActions" />

            @if (isset($orders) && method_exists($orders, 'links'))
                <div class="mt-4">
                    {{ $orders->links() }}
                </div>
            @endif
        </x-admin-card>
    </div>

    @include('admin.orders.create-modal')
    @include('admin.orders.show-modal')
@endsection
