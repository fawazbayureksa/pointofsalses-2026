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
        sss
        <x-admin-card title="All Orders">
            <div class="mb-4 flex items-center justify-between">
                <div class="flex items-center space-x-4">
                    <input type="text" placeholder="Search orders..."
                        class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent w-64">
                    <select
                        class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <option value="">All Status</option>
                        <option value="pending">Pending</option>
                        <option value="completed">Completed</option>
                        <option value="cancelled">Cancelled</option>
                        <option value="refunded">Refunded</option>
                    </select>
                    <select
                        class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <option value="">All Outlets</option>
                        @foreach ($outlets ?? [] as $outlet)
                            <option value="{{ $outlet->id }}">{{ $outlet->name }}</option>
                        @endforeach
                    </select>
                    <input type="date"
                        class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </div>
                @can('create orders')
                    <x-admin-button variant="primary" icon="fa-solid fa-plus" x-data @click="$dispatch('open-create-modal')">
                        New Order
                    </x-admin-button>
                @endcan
            </div>

            <x-admin-table :headers="[
                ['label' => 'Order ID', 'key' => 'id'],
                [
                    'label' => 'Customer',
                    'slot' => function ($order) {
                        return optional($order->customer)->name ?? 'Guest';
                    },
                ],
                [
                    'label' => 'Outlet',
                    'slot' => function ($order) {
                        return optional($order->outlet)->name ?? '-';
                    },
                ],
                ['label' => 'Items', 'key' => 'items_count'],
                ['label' => 'Total', 'key' => 'total_amount'],
                ['label' => 'Payment', 'key' => 'payment_method'],
                ['label' => 'Status', 'key' => 'status'],
                ['label' => 'Created At', 'key' => 'created_at'],
            ]" :rows="$orders ?? []" :actions="function ($order) {
                return view('admin.orders.actions', ['order' => $order])->render();
            }" />
        </x-admin-card>
    </div>

    @include('admin.orders.create-modal')
    {{-- @include('admin.orders.show-modal') --}}
@endsection
