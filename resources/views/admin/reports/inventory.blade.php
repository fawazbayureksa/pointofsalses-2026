@extends('layouts.admin')

@section('title', 'Inventory Report')

@section('content')
<div class="space-y-6">

    {{-- Header --}}
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <div class="flex items-center gap-2 text-sm text-gray-500 mb-1">
                <a href="{{ route('admin.reports.index') }}" class="hover:text-gray-700">Reports</a>
                <i class="fa-solid fa-chevron-right text-xs"></i>
                <span class="text-gray-900 font-medium">Inventory Report</span>
            </div>
            <h1 class="text-2xl font-bold text-gray-900">Inventory Report</h1>
        </div>
    </div>

    {{-- Filters --}}
    <div class="bg-white rounded-xl shadow p-5">
        <form method="GET" action="{{ route('admin.reports.inventory') }}" class="flex flex-wrap items-end gap-4">
            <div class="flex flex-col gap-1">
                <label class="text-xs font-medium text-gray-600">Outlet</label>
                <select name="outlet_id"
                    class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-amber-500 focus:border-transparent">
                    <option value="">All Outlets</option>
                    @foreach($outlets as $outlet)
                        <option value="{{ $outlet->id }}" {{ $outletId == $outlet->id ? 'selected' : '' }}>{{ $outlet->name }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit"
                class="px-4 py-2 bg-amber-500 text-white text-sm font-medium rounded-lg hover:bg-amber-600 transition-colors flex items-center gap-2">
                <i class="fa-solid fa-filter"></i> Apply Filters
            </button>
            <a href="{{ route('admin.reports.inventory') }}"
                class="px-4 py-2 text-sm text-gray-600 border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                Reset
            </a>
        </form>
    </div>

    {{-- KPI Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="bg-white rounded-xl shadow p-4 flex items-center gap-4">
            <div class="w-12 h-12 bg-red-100 text-red-600 rounded-full flex items-center justify-center flex-shrink-0">
                <i class="fa-solid fa-circle-xmark text-xl"></i>
            </div>
            <div>
                <p class="text-xs text-gray-500 font-medium uppercase tracking-wider">Out of Stock</p>
                <p class="text-2xl font-bold text-red-600 mt-0.5">{{ number_format($outOfStockCount) }}</p>
            </div>
        </div>
        <div class="bg-white rounded-xl shadow p-4 flex items-center gap-4">
            <div class="w-12 h-12 bg-amber-100 text-amber-600 rounded-full flex items-center justify-center flex-shrink-0">
                <i class="fa-solid fa-triangle-exclamation text-xl"></i>
            </div>
            <div>
                <p class="text-xs text-gray-500 font-medium uppercase tracking-wider">Low Stock Items</p>
                <p class="text-2xl font-bold text-amber-600 mt-0.5">{{ number_format($lowStockCount) }}</p>
            </div>
        </div>
        <div class="bg-white rounded-xl shadow p-4 flex items-center gap-4">
            <div class="w-12 h-12 bg-emerald-100 text-emerald-600 rounded-full flex items-center justify-center flex-shrink-0">
                <i class="fa-solid fa-coins text-xl"></i>
            </div>
            <div>
                <p class="text-xs text-gray-500 font-medium uppercase tracking-wider">Estimated Stock Value</p>
                <p class="text-2xl font-bold text-gray-900 mt-0.5">{{ number_format($totalStockValue, 0) }}</p>
            </div>
        </div>
    </div>

    {{-- Charts Row --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        {{-- Stock by Outlet --}}
        <div class="bg-white rounded-xl shadow p-5">
            <h2 class="text-sm font-semibold text-gray-900 mb-4">Stock Distribution by Outlet</h2>
            @if($stockByOutlet->isEmpty())
                <div class="flex flex-col items-center justify-center h-48 text-gray-400">
                    <i class="fa-solid fa-store text-4xl mb-2"></i>
                    <p class="text-sm">No outlets configured</p>
                </div>
            @else
                <canvas id="stockByOutletChart" height="180"></canvas>
            @endif
        </div>

        {{-- Movement Types --}}
        <div class="bg-white rounded-xl shadow p-5">
            <h2 class="text-sm font-semibold text-gray-900 mb-4">Stock Movements by Type (Last 30 Days)</h2>
            @if($movementsByType->isEmpty())
                <div class="flex flex-col items-center justify-center h-48 text-gray-400">
                    <i class="fa-solid fa-arrow-right-arrow-left text-4xl mb-2"></i>
                    <p class="text-sm">No movements in the last 30 days</p>
                </div>
            @else
                <canvas id="movementTypeChart" height="180"></canvas>
                <div class="mt-4 space-y-2">
                    @foreach($movementsByType as $mov)
                        <div class="flex items-center justify-between text-sm">
                            <span class="text-gray-700 capitalize">{{ str_replace('_', ' ', $mov->type) }}</span>
                            <span class="font-semibold text-gray-900">{{ number_format($mov->total_qty, 0) }} units</span>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    {{-- Low Stock Alerts --}}
    @if($lowStockItems->isNotEmpty())
    <div class="bg-white rounded-xl shadow overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-100 flex items-center gap-2">
            <i class="fa-solid fa-triangle-exclamation text-amber-500"></i>
            <h2 class="text-sm font-semibold text-gray-900">Stock Alerts</h2>
            <span class="ml-auto px-2 py-0.5 bg-amber-100 text-amber-700 text-xs font-semibold rounded-full">
                {{ $lowStockItems->count() }} items
            </span>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-100">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Product</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase">SKU</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Outlet</th>
                        <th class="px-5 py-3 text-center text-xs font-semibold text-gray-500 uppercase">Current Stock</th>
                        <th class="px-5 py-3 text-center text-xs font-semibold text-gray-500 uppercase">Threshold</th>
                        <th class="px-5 py-3 text-center text-xs font-semibold text-gray-500 uppercase">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @foreach($lowStockItems as $item)
                        <tr class="hover:bg-gray-50">
                            <td class="px-5 py-3 text-sm font-medium text-gray-900">{{ $item->product_name }}</td>
                            <td class="px-5 py-3 text-sm text-gray-500">{{ $item->sku ?? '–' }}</td>
                            <td class="px-5 py-3 text-sm text-gray-700">{{ $item->outlet_name }}</td>
                            <td class="px-5 py-3 text-sm text-center font-bold
                                {{ $item->status === 'out_of_stock' ? 'text-red-600' : 'text-amber-600' }}">
                                {{ number_format($item->stock, 0) }}
                            </td>
                            <td class="px-5 py-3 text-sm text-center text-gray-500">{{ number_format($item->threshold, 0) }}</td>
                            <td class="px-5 py-3 text-center">
                                @if($item->status === 'out_of_stock')
                                    <span class="px-2 py-1 bg-red-100 text-red-700 text-xs font-semibold rounded-full">Out of Stock</span>
                                @else
                                    <span class="px-2 py-1 bg-amber-100 text-amber-700 text-xs font-semibold rounded-full">Low Stock</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @else
    <div class="bg-white rounded-xl shadow p-6 flex flex-col items-center text-center text-gray-400">
        <i class="fa-solid fa-circle-check text-4xl text-emerald-400 mb-2"></i>
        <p class="font-medium text-gray-700">All stock levels are healthy</p>
        <p class="text-sm mt-1">No low stock or out-of-stock items detected.</p>
    </div>
    @endif

    {{-- Recent Stock Movements --}}
    <div class="bg-white rounded-xl shadow overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-100">
            <h2 class="text-sm font-semibold text-gray-900">Recent Stock Movements (Last 30 Days)</h2>
        </div>
        @if($recentMovements->isEmpty())
            <div class="flex flex-col items-center justify-center h-32 text-gray-400">
                <p class="text-sm">No recent movements</p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-100">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Date</th>
                            <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Product</th>
                            <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Outlet</th>
                            <th class="px-5 py-3 text-center text-xs font-semibold text-gray-500 uppercase">Type</th>
                            <th class="px-5 py-3 text-right text-xs font-semibold text-gray-500 uppercase">Quantity</th>
                            <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Reason</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @foreach($recentMovements as $mov)
                            <tr class="hover:bg-gray-50">
                                <td class="px-5 py-3 text-sm text-gray-500 whitespace-nowrap">{{ $mov->created_at->format('d M Y H:i') }}</td>
                                <td class="px-5 py-3 text-sm font-medium text-gray-900">{{ $mov->product->name ?? '–' }}</td>
                                <td class="px-5 py-3 text-sm text-gray-700">{{ $mov->outlet->name ?? '–' }}</td>
                                <td class="px-5 py-3 text-center">
                                    @php
                                        $typeColors = [
                                            'sale'      => 'bg-blue-100 text-blue-700',
                                            'purchase'  => 'bg-emerald-100 text-emerald-700',
                                            'adjustment'=> 'bg-amber-100 text-amber-700',
                                            'transfer'  => 'bg-violet-100 text-violet-700',
                                            'return'    => 'bg-gray-100 text-gray-700',
                                        ];
                                        $typeColor = $typeColors[$mov->type] ?? 'bg-gray-100 text-gray-600';
                                    @endphp
                                    <span class="px-2 py-0.5 text-xs font-semibold rounded-full {{ $typeColor }} capitalize">
                                        {{ str_replace('_', ' ', $mov->type) }}
                                    </span>
                                </td>
                                <td class="px-5 py-3 text-sm text-right font-bold
                                    {{ $mov->quantity_change >= 0 ? 'text-emerald-600' : 'text-red-600' }}">
                                    {{ $mov->quantity_change >= 0 ? '+' : '' }}{{ number_format($mov->quantity_change, 0) }}
                                </td>
                                <td class="px-5 py-3 text-sm text-gray-500">{{ $mov->reason ?? '–' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
@if($stockByOutlet->isNotEmpty())
    new Chart(document.getElementById('stockByOutletChart'), {
        type: 'bar',
        data: {
            labels: @json($stockByOutlet->pluck('outlet_name')),
            datasets: [{
                label: 'Total Stock Units',
                data: @json($stockByOutlet->pluck('total_stock')),
                backgroundColor: 'rgba(245, 158, 11, 0.75)',
                borderColor: 'rgb(245, 158, 11)',
                borderWidth: 2,
                borderRadius: 6,
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { display: false },
                tooltip: { callbacks: { label: ctx => ' ' + Number(ctx.raw).toLocaleString() + ' units' } }
            },
            scales: {
                y: { beginAtZero: true, grid: { color: 'rgba(0,0,0,0.05)' } },
                x: { grid: { display: false } }
            }
        }
    });
@endif

@if($movementsByType->isNotEmpty())
    new Chart(document.getElementById('movementTypeChart'), {
        type: 'doughnut',
        data: {
            labels: @json($movementsByType->pluck('type')->map(fn($t) => ucwords(str_replace('_', ' ', $t)))),
            datasets: [{
                data: @json($movementsByType->pluck('total_qty')),
                backgroundColor: [
                    'rgba(59, 130, 246, 0.8)',
                    'rgba(16, 185, 129, 0.8)',
                    'rgba(245, 158, 11, 0.8)',
                    'rgba(139, 92, 246, 0.8)',
                    'rgba(156, 163, 175, 0.8)',
                ],
                borderWidth: 2,
                borderColor: '#fff',
            }]
        },
        options: {
            responsive: true,
            cutout: '60%',
            plugins: {
                legend: { position: 'bottom', labels: { padding: 10, font: { size: 11 } } },
                tooltip: { callbacks: { label: ctx => ' ' + Number(ctx.raw).toLocaleString() + ' units' } }
            }
        }
    });
@endif
</script>
@endpush
