@extends('layouts.admin')

@section('title', 'Dashboard')

@section('page-title', 'Dashboard')

@section('content')
<div class="space-y-6">
    @if($errors->any())
        <div class="bg-red-50 border border-red-200 rounded-lg p-4">
            <div class="flex">
                <div class="flex-shrink-0">
                    <i class="fa-solid fa-circle-exclamation text-red-400"></i>
                </div>
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-red-800">Errors</h3>
                    <div class="mt-2 text-sm text-red-700">
                        <ul class="list-disc list-inside">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    @endif

    @if(session('success'))
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

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <x-admin-card class="bg-gradient-to-br from-blue-500 to-blue-600 border-0">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-blue-100 text-sm font-medium">Total Sales Today</p>
                    <p class="text-3xl font-bold text-white mt-2">${{ number_format($totalSalesToday ?? 0, 2) }}</p>
                    <p class="text-blue-100 text-xs mt-2">
                        <i class="fa-solid fa-arrow-trend-up mr-1"></i>
                        {{ $salesGrowth ?? 0 }}% from yesterday
                    </p>
                </div>
                <div class="p-3 bg-blue-400 bg-opacity-30 rounded-lg">
                    <i class="fa-solid fa-dollar-sign text-white text-2xl"></i>
                </div>
            </div>
        </x-admin-card>

        <x-admin-card class="bg-gradient-to-br from-green-500 to-green-600 border-0">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-green-100 text-sm font-medium">Total Orders</p>
                    <p class="text-3xl font-bold text-white mt-2">{{ $totalOrders ?? 0 }}</p>
                    <p class="text-green-100 text-xs mt-2">
                        <i class="fa-solid fa-check mr-1"></i>
                        {{ $completedOrders ?? 0 }} completed
                    </p>
                </div>
                <div class="p-3 bg-green-400 bg-opacity-30 rounded-lg">
                    <i class="fa-solid fa-shopping-cart text-white text-2xl"></i>
                </div>
            </div>
        </x-admin-card>

        <x-admin-card class="bg-gradient-to-br from-purple-500 to-purple-600 border-0">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-purple-100 text-sm font-medium">Active Outlets</p>
                    <p class="text-3xl font-bold text-white mt-2">{{ $activeOutlets ?? 0 }}</p>
                    <p class="text-purple-100 text-xs mt-2">
                        <i class="fa-solid fa-store mr-1"></i>
                        Total: {{ $totalOutlets ?? 0 }}
                    </p>
                </div>
                <div class="p-3 bg-purple-400 bg-opacity-30 rounded-lg">
                    <i class="fa-solid fa-store text-white text-2xl"></i>
                </div>
            </div>
        </x-admin-card>

        <x-admin-card class="bg-gradient-to-br from-orange-500 to-orange-600 border-0">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-orange-100 text-sm font-medium">Total Customers</p>
                    <p class="text-3xl font-bold text-white mt-2">{{ $totalCustomers ?? 0 }}</p>
                    <p class="text-orange-100 text-xs mt-2">
                        <i class="fa-solid fa-user-plus mr-1"></i>
                        {{ $newCustomers ?? 0 }} this week
                    </p>
                </div>
                <div class="p-3 bg-orange-400 bg-opacity-30 rounded-lg">
                    <i class="fa-solid fa-users text-white text-2xl"></i>
                </div>
            </div>
        </x-admin-card>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <x-admin-card title="Recent Orders" collapsible class="lg:col-span-2">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Order ID</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Customer</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Outlet</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @if(isset($recentOrders) && count($recentOrders) > 0)
                            @foreach($recentOrders as $order)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-blue-600">#{{ $order->id }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $order->customer->name ?? 'Guest' }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $order->outlet->name ?? '-' }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">${{ number_format($order->total_amount, 2) }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                                            @if($order->status === 'completed') bg-green-100 text-green-800
                                            @elseif($order->status === 'pending') bg-yellow-100 text-yellow-800
                                            @elseif($order->status === 'cancelled') bg-red-100 text-red-800
                                            @else bg-gray-100 text-gray-800 @endif">
                                            {{ ucfirst($order->status) }}
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        @else
                            <tr>
                                <td colspan="5" class="px-6 py-12 text-center text-gray-500">No recent orders</td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </x-admin-card>

        <x-admin-card title="Top Products" collapsible>
            <div class="space-y-4">
                @if(isset($topProducts) && count($topProducts) > 0)
                    @foreach($topProducts as $index => $product)
                        <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                            <div class="flex items-center">
                                <span class="w-8 h-8 bg-blue-100 text-blue-600 rounded-full flex items-center justify-center text-sm font-bold">
                                    {{ $index + 1 }}
                                </span>
                                <div class="ml-3">
                                    <p class="text-sm font-medium text-gray-900">{{ $product->name }}</p>
                                    <p class="text-xs text-gray-500">{{ $product->category->name ?? 'Uncategorized' }}</p>
                                </div>
                            </div>
                            <div class="text-right">
                                <p class="text-sm font-medium text-gray-900">{{ $product->sold ?? 0 }} sold</p>
                                <p class="text-xs text-gray-500">${{ number_format($product->revenue ?? 0, 2) }}</p>
                            </div>
                        </div>
                    @endforeach
                @else
                    <p class="text-center text-gray-500 py-8">No top products data</p>
                @endif
            </div>
        </x-admin-card>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <x-admin-card title="Low Stock Alert">
            <div class="space-y-3">
                @if(isset($lowStockProducts) && count($lowStockProducts) > 0)
                    @foreach($lowStockProducts as $product)
                        <div class="flex items-center justify-between p-3 border border-red-200 bg-red-50 rounded-lg">
                            <div>
                                <p class="text-sm font-medium text-gray-900">{{ $product->name }}</p>
                                <p class="text-xs text-red-600">Only {{ $product->stock }} left</p>
                            </div>
                            <x-admin-button variant="primary" size="sm" href="{{ route('admin.products.edit', $product->id) }}">
                                Restock
                            </x-admin-button>
                        </div>
                    @endforeach
                @else
                    <p class="text-center text-gray-500 py-8">No low stock items</p>
                @endif
            </div>
        </x-admin-card>

        <x-admin-card title="Recent Activity">
            <div class="space-y-4">
                @if(isset($recentActivities) && count($recentActivities) > 0)
                    @foreach($recentActivities as $activity)
                        <div class="flex items-start space-x-3">
                            <div class="flex-shrink-0">
                                <div class="w-8 h-8 bg-blue-100 text-blue-600 rounded-full flex items-center justify-center">
                                    <i class="fa-solid fa-circle-info text-sm"></i>
                                </div>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm text-gray-900">{{ $activity->description }}</p>
                                <p class="text-xs text-gray-500 mt-1">{{ $activity->causer->name ?? 'System' }} • {{ $activity->created_at->diffForHumans() }}</p>
                            </div>
                        </div>
                    @endforeach
                @else
                    <p class="text-center text-gray-500 py-8">No recent activity</p>
                @endif
            </div>
        </x-admin-card>
    </div>
</div>
@endsection
