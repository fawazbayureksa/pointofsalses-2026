@extends('layouts.admin')

@section('title', 'Advanced Reports')

@section('content')
<div class="space-y-6">

    {{-- Page Header --}}
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Advanced Reports</h1>
            <p class="mt-1 text-sm text-gray-500">Gain deep insights into your business performance</p>
        </div>
    </div>

    {{-- Summary KPI Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
        <div class="bg-gradient-to-br from-emerald-500 to-emerald-600 rounded-xl p-5 text-white shadow">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-emerald-100">Revenue This Month</p>
                    <p class="text-2xl font-bold mt-1">{{ number_format($totalRevenueThisMonth, 0) }}</p>
                </div>
                <div class="w-12 h-12 bg-white/20 rounded-full flex items-center justify-center">
                    <i class="fa-solid fa-money-bill-trend-up text-xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl p-5 text-white shadow">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-blue-100">Orders This Month</p>
                    <p class="text-2xl font-bold mt-1">{{ number_format($totalOrdersThisMonth) }}</p>
                </div>
                <div class="w-12 h-12 bg-white/20 rounded-full flex items-center justify-center">
                    <i class="fa-solid fa-shopping-bag text-xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-gradient-to-br from-violet-500 to-violet-600 rounded-xl p-5 text-white shadow">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-violet-100">Total Customers</p>
                    <p class="text-2xl font-bold mt-1">{{ number_format($totalCustomers) }}</p>
                </div>
                <div class="w-12 h-12 bg-white/20 rounded-full flex items-center justify-center">
                    <i class="fa-solid fa-users text-xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-gradient-to-br from-amber-500 to-amber-600 rounded-xl p-5 text-white shadow">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-amber-100">Active Products</p>
                    <p class="text-2xl font-bold mt-1">{{ number_format($totalProducts) }}</p>
                </div>
                <div class="w-12 h-12 bg-white/20 rounded-full flex items-center justify-center">
                    <i class="fa-solid fa-box-open text-xl"></i>
                </div>
            </div>
        </div>
    </div>

    {{-- Revenue Trend Chart --}}
    <div class="bg-white rounded-xl shadow p-6">
        <h2 class="text-base font-semibold text-gray-900 mb-4">Revenue Trend — Last 6 Months</h2>
        <canvas id="revenueTrendChart" height="90"></canvas>
    </div>

    {{-- Report Module Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">

        <a href="{{ route('admin.reports.sales') }}"
            class="group bg-white border border-gray-100 rounded-xl shadow hover:shadow-md hover:-translate-y-1 transition-all duration-200 p-6 flex flex-col items-center text-center">
            <div class="w-14 h-14 bg-emerald-100 text-emerald-600 rounded-full flex items-center justify-center mb-4 group-hover:bg-emerald-600 group-hover:text-white transition-colors">
                <i class="fa-solid fa-chart-line text-2xl"></i>
            </div>
            <h3 class="font-semibold text-gray-900">Sales Report</h3>
            <p class="text-sm text-gray-500 mt-1">Revenue, orders, and payment method analysis</p>
        </a>

        <a href="{{ route('admin.reports.products') }}"
            class="group bg-white border border-gray-100 rounded-xl shadow hover:shadow-md hover:-translate-y-1 transition-all duration-200 p-6 flex flex-col items-center text-center">
            <div class="w-14 h-14 bg-blue-100 text-blue-600 rounded-full flex items-center justify-center mb-4 group-hover:bg-blue-600 group-hover:text-white transition-colors">
                <i class="fa-solid fa-box text-2xl"></i>
            </div>
            <h3 class="font-semibold text-gray-900">Product Performance</h3>
            <p class="text-sm text-gray-500 mt-1">Top sellers, slow movers, and category breakdown</p>
        </a>

        <a href="{{ route('admin.reports.customers') }}"
            class="group bg-white border border-gray-100 rounded-xl shadow hover:shadow-md hover:-translate-y-1 transition-all duration-200 p-6 flex flex-col items-center text-center">
            <div class="w-14 h-14 bg-violet-100 text-violet-600 rounded-full flex items-center justify-center mb-4 group-hover:bg-violet-600 group-hover:text-white transition-colors">
                <i class="fa-solid fa-users text-2xl"></i>
            </div>
            <h3 class="font-semibold text-gray-900">Customer Analysis</h3>
            <p class="text-sm text-gray-500 mt-1">Top customers, retention, and loyalty insights</p>
        </a>

        <a href="{{ route('admin.reports.inventory') }}"
            class="group bg-white border border-gray-100 rounded-xl shadow hover:shadow-md hover:-translate-y-1 transition-all duration-200 p-6 flex flex-col items-center text-center">
            <div class="w-14 h-14 bg-amber-100 text-amber-600 rounded-full flex items-center justify-center mb-4 group-hover:bg-amber-600 group-hover:text-white transition-colors">
                <i class="fa-solid fa-warehouse text-2xl"></i>
            </div>
            <h3 class="font-semibold text-gray-900">Inventory Report</h3>
            <p class="text-sm text-gray-500 mt-1">Stock levels, low stock alerts, and movements</p>
        </a>

    </div>

</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
    const revenueTrendLabels = @json($revenueTrend->pluck('label'));
    const revenueTrendData   = @json($revenueTrend->pluck('total'));

    new Chart(document.getElementById('revenueTrendChart'), {
        type: 'bar',
        data: {
            labels: revenueTrendLabels,
            datasets: [{
                label: 'Revenue',
                data: revenueTrendData,
                backgroundColor: 'rgba(16, 185, 129, 0.7)',
                borderColor: 'rgb(16, 185, 129)',
                borderWidth: 2,
                borderRadius: 6,
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
</script>
@endpush
