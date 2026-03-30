@extends('layouts.admin')

@section('title', 'Product Performance Report')

@section('content')
<div class="space-y-6">

    {{-- Header --}}
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <div class="flex items-center gap-2 text-sm text-gray-500 mb-1">
                <a href="{{ route('admin.reports.index') }}" class="hover:text-gray-700">Reports</a>
                <i class="fa-solid fa-chevron-right text-xs"></i>
                <span class="text-gray-900 font-medium">Product Performance</span>
            </div>
            <h1 class="text-2xl font-bold text-gray-900">Product Performance Report</h1>
        </div>
    </div>

    {{-- Filters --}}
    <div class="bg-white rounded-xl shadow p-5">
        <form method="GET" action="{{ route('admin.reports.products') }}" class="flex flex-wrap items-end gap-4">
            <div class="flex flex-col gap-1">
                <label class="text-xs font-medium text-gray-600">Date From</label>
                <input type="date" name="date_from" value="{{ $dateFrom->format('Y-m-d') }}"
                    class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent">
            </div>
            <div class="flex flex-col gap-1">
                <label class="text-xs font-medium text-gray-600">Date To</label>
                <input type="date" name="date_to" value="{{ $dateTo->format('Y-m-d') }}"
                    class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent">
            </div>
            <div class="flex flex-col gap-1">
                <label class="text-xs font-medium text-gray-600">Category</label>
                <select name="category_id"
                    class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    <option value="">All Categories</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}" {{ $categoryId == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit"
                class="px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition-colors flex items-center gap-2">
                <i class="fa-solid fa-filter"></i> Apply Filters
            </button>
            <a href="{{ route('admin.reports.products') }}"
                class="px-4 py-2 text-sm text-gray-600 border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                Reset
            </a>
        </form>
    </div>

    {{-- Charts Row --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        {{-- Top by Revenue Chart --}}
        <div class="bg-white rounded-xl shadow p-5">
            <h2 class="text-sm font-semibold text-gray-900 mb-4">Top Products by Revenue</h2>
            @if($topByRevenue->isEmpty())
                <div class="flex flex-col items-center justify-center h-48 text-gray-400">
                    <i class="fa-solid fa-box text-4xl mb-2"></i>
                    <p class="text-sm">No sales in this period</p>
                </div>
            @else
                <canvas id="topRevenueChart" height="220"></canvas>
            @endif
        </div>

        {{-- Revenue by Category --}}
        <div class="bg-white rounded-xl shadow p-5">
            <h2 class="text-sm font-semibold text-gray-900 mb-4">Revenue by Category</h2>
            @if($revenueByCategory->isEmpty())
                <div class="flex flex-col items-center justify-center h-48 text-gray-400">
                    <i class="fa-solid fa-tags text-4xl mb-2"></i>
                    <p class="text-sm">No category data for this period</p>
                </div>
            @else
                <canvas id="categoryChart" height="220"></canvas>
            @endif
        </div>
    </div>

    {{-- Top by Revenue Table --}}
    <div class="bg-white rounded-xl shadow overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
            <h2 class="text-sm font-semibold text-gray-900">Top Products by Revenue</h2>
            <span class="text-xs text-gray-500">{{ $dateFrom->format('d M Y') }} – {{ $dateTo->format('d M Y') }}</span>
        </div>
        @if($topByRevenue->isEmpty())
            <div class="flex flex-col items-center justify-center h-32 text-gray-400">
                <p class="text-sm">No data available</p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-100">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase">#</th>
                            <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Product</th>
                            <th class="px-5 py-3 text-right text-xs font-semibold text-gray-500 uppercase">Qty Sold</th>
                            <th class="px-5 py-3 text-right text-xs font-semibold text-gray-500 uppercase">Revenue</th>
                            <th class="px-5 py-3 text-right text-xs font-semibold text-gray-500 uppercase">Est. Profit</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @foreach($topByRevenue as $i => $item)
                            <tr class="hover:bg-gray-50">
                                <td class="px-5 py-3 text-sm text-gray-500">{{ $i + 1 }}</td>
                                <td class="px-5 py-3 text-sm font-medium text-gray-900">{{ $item->product_name }}</td>
                                <td class="px-5 py-3 text-sm text-right text-gray-700">{{ number_format($item->qty_sold, 0) }}</td>
                                <td class="px-5 py-3 text-sm text-right font-semibold text-gray-900">{{ number_format($item->revenue, 0) }}</td>
                                <td class="px-5 py-3 text-sm text-right">
                                    @if((float)$item->profit > 0)
                                        <span class="text-emerald-600 font-semibold">{{ number_format($item->profit, 0) }}</span>
                                    @else
                                        <span class="text-gray-400">–</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    {{-- Top by Quantity Table --}}
    <div class="bg-white rounded-xl shadow overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-100">
            <h2 class="text-sm font-semibold text-gray-900">Top Products by Quantity Sold</h2>
        </div>
        @if($topByQty->isEmpty())
            <div class="flex flex-col items-center justify-center h-32 text-gray-400">
                <p class="text-sm">No data available</p>
            </div>
        @else
            <div class="space-y-3 p-5">
                @php $maxQty = $topByQty->max('qty_sold'); @endphp
                @foreach($topByQty->take(10) as $item)
                    <div class="flex items-center gap-4">
                        <div class="w-40 text-sm text-gray-700 truncate">{{ $item->product_name }}</div>
                        <div class="flex-1 bg-gray-100 rounded-full h-3">
                            <div class="bg-blue-500 h-3 rounded-full"
                                style="width: {{ $maxQty > 0 ? round(($item->qty_sold / $maxQty) * 100) : 0 }}%">
                            </div>
                        </div>
                        <div class="text-sm font-semibold text-gray-900 w-20 text-right">
                            {{ number_format($item->qty_sold, 0) }} units
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    {{-- Slow Movers --}}
    @if($slowMovers->isNotEmpty())
    <div class="bg-white rounded-xl shadow overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-100 flex items-center gap-2">
            <i class="fa-solid fa-triangle-exclamation text-amber-500"></i>
            <h2 class="text-sm font-semibold text-gray-900">Slow Movers — No Sales This Period</h2>
        </div>
        <div class="p-5">
            <p class="text-sm text-gray-500 mb-4">These active products had zero sales in the selected period. Consider promotions or re-evaluation.</p>
            <div class="flex flex-wrap gap-2">
                @foreach($slowMovers as $product)
                    <a href="{{ route('admin.products.show', $product->id) }}"
                        class="inline-flex items-center gap-2 px-3 py-1.5 bg-amber-50 border border-amber-200 text-amber-800 text-sm rounded-lg hover:bg-amber-100 transition-colors">
                        <i class="fa-solid fa-box text-xs"></i>
                        {{ $product->name }}
                    </a>
                @endforeach
            </div>
        </div>
    </div>
    @endif

</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
@if($topByRevenue->isNotEmpty())
    new Chart(document.getElementById('topRevenueChart'), {
        type: 'bar',
        data: {
            labels: @json($topByRevenue->take(8)->pluck('product_name')->map(fn($n) => strlen($n) > 20 ? substr($n, 0, 20).'…' : $n)),
            datasets: [{
                label: 'Revenue',
                data: @json($topByRevenue->take(8)->pluck('revenue')),
                backgroundColor: 'rgba(59, 130, 246, 0.75)',
                borderColor: 'rgb(59, 130, 246)',
                borderWidth: 2,
                borderRadius: 5,
            }]
        },
        options: {
            indexAxis: 'y',
            responsive: true,
            plugins: {
                legend: { display: false },
                tooltip: { callbacks: { label: ctx => ' ' + Number(ctx.raw).toLocaleString() } }
            },
            scales: {
                x: { beginAtZero: true, grid: { color: 'rgba(0,0,0,0.05)' } },
                y: { grid: { display: false } }
            }
        }
    });
@endif

@if($revenueByCategory->isNotEmpty())
    new Chart(document.getElementById('categoryChart'), {
        type: 'doughnut',
        data: {
            labels: @json($revenueByCategory->pluck('category_name')),
            datasets: [{
                data: @json($revenueByCategory->pluck('revenue')),
                backgroundColor: [
                    'rgba(59, 130, 246, 0.8)',
                    'rgba(16, 185, 129, 0.8)',
                    'rgba(139, 92, 246, 0.8)',
                    'rgba(245, 158, 11, 0.8)',
                    'rgba(239, 68, 68, 0.8)',
                    'rgba(20, 184, 166, 0.8)',
                    'rgba(249, 115, 22, 0.8)',
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
                tooltip: { callbacks: { label: ctx => ' ' + Number(ctx.raw).toLocaleString() } }
            }
        }
    });
@endif
</script>
@endpush
