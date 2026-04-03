@extends('layouts.admin')

@section('title', 'Sales Report')

@section('content')
<div class="space-y-6">

    {{-- Header --}}
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <div class="flex items-center gap-2 text-sm text-gray-500 mb-1">
                <a href="{{ route('admin.reports.index') }}" class="hover:text-gray-700">Reports</a>
                <i class="fa-solid fa-chevron-right text-xs"></i>
                <span class="text-gray-900 font-medium">Sales Report</span>
            </div>
            <h1 class="text-2xl font-bold text-gray-900">Sales Report</h1>
        </div>
    </div>

    {{-- Filters --}}
    <div class="bg-white rounded-xl shadow p-5">
        <form method="GET" action="{{ route('admin.reports.sales') }}" class="flex flex-wrap items-end gap-4">
            <div class="flex flex-col gap-1">
                <label class="text-xs font-medium text-gray-600">Date From</label>
                <input type="date" name="date_from" value="{{ $dateFrom->format('Y-m-d') }}"
                    class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-emerald-500 focus:border-transparent">
            </div>
            <div class="flex flex-col gap-1">
                <label class="text-xs font-medium text-gray-600">Date To</label>
                <input type="date" name="date_to" value="{{ $dateTo->format('Y-m-d') }}"
                    class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-emerald-500 focus:border-transparent">
            </div>
            <div class="flex flex-col gap-1">
                <label class="text-xs font-medium text-gray-600">Outlet</label>
                <select name="outlet_id"
                    class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-emerald-500 focus:border-transparent">
                    <option value="">All Outlets</option>
                    @foreach($outlets as $outlet)
                        <option value="{{ $outlet->id }}" {{ $outletId == $outlet->id ? 'selected' : '' }}>
                            {{ $outlet->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <button type="submit"
                class="px-4 py-2 bg-emerald-600 text-white text-sm font-medium rounded-lg hover:bg-emerald-700 transition-colors flex items-center gap-2">
                <i class="fa-solid fa-filter"></i> Apply Filters
            </button>
            <a href="{{ route('admin.reports.sales') }}"
                class="px-4 py-2 text-sm text-gray-600 border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                Reset
            </a>
        </form>
    </div>

    {{-- KPI Summary --}}
    <div class="grid grid-cols-2 lg:grid-cols-5 gap-4">
        <div class="bg-white rounded-xl shadow p-4">
            <p class="text-xs text-gray-500 font-medium uppercase tracking-wider">Total Revenue</p>
            <p class="text-2xl font-bold text-gray-900 mt-1">{{ number_format($totalRevenue, 0) }}</p>
        </div>
        <div class="bg-white rounded-xl shadow p-4">
            <p class="text-xs text-gray-500 font-medium uppercase tracking-wider">Total Orders</p>
            <p class="text-2xl font-bold text-gray-900 mt-1">{{ number_format($totalOrders) }}</p>
        </div>
        <div class="bg-white rounded-xl shadow p-4">
            <p class="text-xs text-gray-500 font-medium uppercase tracking-wider">Avg Order Value</p>
            <p class="text-2xl font-bold text-gray-900 mt-1">{{ number_format($avgOrderValue, 0) }}</p>
        </div>
        <div class="bg-white rounded-xl shadow p-4">
            <p class="text-xs text-gray-500 font-medium uppercase tracking-wider">Total Tax</p>
            <p class="text-2xl font-bold text-gray-900 mt-1">{{ number_format($totalTax, 0) }}</p>
        </div>
        <div class="bg-white rounded-xl shadow p-4">
            <p class="text-xs text-gray-500 font-medium uppercase tracking-wider">Total Discount</p>
            <p class="text-2xl font-bold text-gray-900 mt-1">{{ number_format($totalDiscount, 0) }}</p>
        </div>
    </div>

    {{-- Charts Row --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- Daily Revenue Chart --}}
        <div class="lg:col-span-2 bg-white rounded-xl shadow p-5">
            <h2 class="text-sm font-semibold text-gray-900 mb-4">Daily Revenue</h2>
            @if($revenueByDay->isEmpty())
                <div class="flex flex-col items-center justify-center h-48 text-gray-400">
                    <i class="fa-solid fa-chart-area text-4xl mb-2"></i>
                    <p class="text-sm">No data for this period</p>
                </div>
            @else
                <canvas id="dailyRevenueChart" height="100"></canvas>
            @endif
        </div>

        {{-- Payment Methods --}}
        <div class="bg-white rounded-xl shadow p-5">
            <h2 class="text-sm font-semibold text-gray-900 mb-4">Payment Methods</h2>
            @if($paymentMethods->isEmpty())
                <div class="flex flex-col items-center justify-center h-48 text-gray-400">
                    <i class="fa-solid fa-credit-card text-4xl mb-2"></i>
                    <p class="text-sm">No payments in this period</p>
                </div>
            @else
                <canvas id="paymentMethodChart" height="180"></canvas>
                <div class="mt-4 space-y-2">
                    @foreach($paymentMethods as $method)
                        <div class="flex items-center justify-between text-sm">
                            <span class="text-gray-700 capitalize">{{ str_replace('_', ' ', $method->payment_method) }}</span>
                            <span class="font-semibold text-gray-900">{{ number_format($method->total, 0) }}</span>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    {{-- Revenue by Outlet --}}
    @if($revenueByOutlet->count() > 1)
    <div class="bg-white rounded-xl shadow p-5">
        <h2 class="text-sm font-semibold text-gray-900 mb-4">Revenue by Outlet</h2>
        <div class="space-y-3">
            @php $maxOutletRevenue = $revenueByOutlet->max('revenue'); @endphp
            @foreach($revenueByOutlet as $row)
                <div class="flex items-center gap-4">
                    <div class="w-32 text-sm text-gray-700 truncate">{{ $row->outlet->name ?? 'Unknown' }}</div>
                    <div class="flex-1 bg-gray-100 rounded-full h-3">
                        <div class="bg-emerald-500 h-3 rounded-full"
                            style="width: {{ $maxOutletRevenue > 0 ? round(($row->revenue / $maxOutletRevenue) * 100) : 0 }}%">
                        </div>
                    </div>
                    <div class="text-sm font-semibold text-gray-900 w-28 text-right">{{ number_format($row->revenue, 0) }}</div>
                    <div class="text-xs text-gray-500 w-20 text-right">{{ number_format($row->orders) }} orders</div>
                </div>
            @endforeach
        </div>
    </div>
    @endif

    {{-- Top 10 Products --}}
    <div class="bg-white rounded-xl shadow overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-100">
            <h2 class="text-sm font-semibold text-gray-900">Top 10 Products by Revenue</h2>
        </div>
        @if($topProducts->isEmpty())
            <div class="flex flex-col items-center justify-center h-32 text-gray-400">
                <p class="text-sm">No product sales in this period</p>
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
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @foreach($topProducts as $i => $product)
                            <tr class="hover:bg-gray-50">
                                <td class="px-5 py-3 text-sm text-gray-500">{{ $i + 1 }}</td>
                                <td class="px-5 py-3 text-sm font-medium text-gray-900">{{ $product->product_name }}</td>
                                <td class="px-5 py-3 text-sm text-right text-gray-700">{{ number_format($product->qty_sold, 0) }}</td>
                                <td class="px-5 py-3 text-sm text-right font-semibold text-gray-900">{{ number_format($product->revenue, 0) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    {{-- Daily Breakdown Table --}}
    @if($revenueByDay->isNotEmpty())
    <div class="bg-white rounded-xl shadow overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-100">
            <h2 class="text-sm font-semibold text-gray-900">Daily Breakdown</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-100">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Date</th>
                        <th class="px-5 py-3 text-right text-xs font-semibold text-gray-500 uppercase">Orders</th>
                        <th class="px-5 py-3 text-right text-xs font-semibold text-gray-500 uppercase">Revenue</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @foreach($revenueByDay as $day)
                        <tr class="hover:bg-gray-50">
                            <td class="px-5 py-3 text-sm text-gray-700">{{ \Carbon\Carbon::parse($day->date)->format('D, d M Y') }}</td>
                            <td class="px-5 py-3 text-sm text-right text-gray-700">{{ number_format($day->orders) }}</td>
                            <td class="px-5 py-3 text-sm text-right font-semibold text-gray-900">{{ number_format($day->revenue, 0) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
@if($revenueByDay->isNotEmpty())
    new Chart(document.getElementById('dailyRevenueChart'), {
        type: 'line',
        data: {
            labels: @json($revenueByDay->pluck('date')->map(fn($d) => \Carbon\Carbon::parse($d)->format('d M'))),
            datasets: [{
                label: 'Revenue',
                data: @json($revenueByDay->pluck('revenue')),
                backgroundColor: 'rgba(16, 185, 129, 0.1)',
                borderColor: 'rgb(16, 185, 129)',
                borderWidth: 2,
                tension: 0.4,
                fill: true,
                pointBackgroundColor: 'rgb(16, 185, 129)',
                pointRadius: 4,
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label: ctx => ' ' + Number(ctx.raw).toLocaleString()
                    }
                }
            },
            scales: {
                y: { beginAtZero: true, grid: { color: 'rgba(0,0,0,0.05)' } },
                x: { grid: { display: false } }
            }
        }
    });
@endif

@if($paymentMethods->isNotEmpty())
    new Chart(document.getElementById('paymentMethodChart'), {
        type: 'doughnut',
        data: {
            labels: @json($paymentMethods->pluck('payment_method')->map(fn($m) => ucwords(str_replace('_', ' ', $m)))),
            datasets: [{
                data: @json($paymentMethods->pluck('total')),
                backgroundColor: [
                    'rgba(16, 185, 129, 0.8)',
                    'rgba(59, 130, 246, 0.8)',
                    'rgba(139, 92, 246, 0.8)',
                    'rgba(245, 158, 11, 0.8)',
                    'rgba(239, 68, 68, 0.8)',
                ],
                borderWidth: 2,
                borderColor: '#fff',
            }]
        },
        options: {
            responsive: true,
            cutout: '65%',
            plugins: {
                legend: { position: 'bottom', labels: { padding: 12, font: { size: 11 } } },
                tooltip: {
                    callbacks: {
                        label: ctx => ' ' + Number(ctx.raw).toLocaleString()
                    }
                }
            }
        }
    });
@endif
</script>
@endpush
