@extends('layouts.admin')

@section('title', 'Customer Analysis Report')

@section('content')
<div class="space-y-6">

    {{-- Header --}}
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <div class="flex items-center gap-2 text-sm text-gray-500 mb-1">
                <a href="{{ route('admin.reports.index') }}" class="hover:text-gray-700">Reports</a>
                <i class="fa-solid fa-chevron-right text-xs"></i>
                <span class="text-gray-900 font-medium">Customer Analysis</span>
            </div>
            <h1 class="text-2xl font-bold text-gray-900">Customer Analysis Report</h1>
        </div>
    </div>

    {{-- Filters --}}
    <div class="bg-white rounded-xl shadow p-5">
        <form method="GET" action="{{ route('admin.reports.customers') }}" class="flex flex-wrap items-end gap-4">
            <div class="flex flex-col gap-1">
                <label class="text-xs font-medium text-gray-600">Date From</label>
                <input type="date" name="date_from" value="{{ $dateFrom->format('Y-m-d') }}"
                    class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-violet-500 focus:border-transparent">
            </div>
            <div class="flex flex-col gap-1">
                <label class="text-xs font-medium text-gray-600">Date To</label>
                <input type="date" name="date_to" value="{{ $dateTo->format('Y-m-d') }}"
                    class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-violet-500 focus:border-transparent">
            </div>
            <button type="submit"
                class="px-4 py-2 bg-violet-600 text-white text-sm font-medium rounded-lg hover:bg-violet-700 transition-colors flex items-center gap-2">
                <i class="fa-solid fa-filter"></i> Apply Filters
            </button>
            <a href="{{ route('admin.reports.customers') }}"
                class="px-4 py-2 text-sm text-gray-600 border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                Reset
            </a>
        </form>
    </div>

    {{-- KPI Cards --}}
    <div class="grid grid-cols-2 lg:grid-cols-5 gap-4">
        <div class="bg-white rounded-xl shadow p-4">
            <p class="text-xs text-gray-500 font-medium uppercase tracking-wider">Total Active Customers</p>
            <p class="text-2xl font-bold text-gray-900 mt-1">{{ number_format($totalActiveCustomers) }}</p>
        </div>
        <div class="bg-white rounded-xl shadow p-4">
            <p class="text-xs text-gray-500 font-medium uppercase tracking-wider">New Customers (Period)</p>
            <p class="text-2xl font-bold text-violet-600 mt-1">{{ number_format($newCustomers) }}</p>
        </div>
        <div class="bg-white rounded-xl shadow p-4">
            <p class="text-xs text-gray-500 font-medium uppercase tracking-wider">Customers Who Purchased</p>
            <p class="text-2xl font-bold text-gray-900 mt-1">{{ number_format($customersWithOrders) }}</p>
        </div>
        <div class="bg-white rounded-xl shadow p-4">
            <p class="text-xs text-gray-500 font-medium uppercase tracking-wider">Walk-in Orders</p>
            <p class="text-2xl font-bold text-gray-900 mt-1">{{ number_format($walkInOrders) }}</p>
        </div>
        <div class="bg-white rounded-xl shadow p-4">
            <p class="text-xs text-gray-500 font-medium uppercase tracking-wider">Avg Spend per Visit</p>
            <p class="text-2xl font-bold text-gray-900 mt-1">{{ number_format($avgSpendPerVisit, 0) }}</p>
        </div>
    </div>

    {{-- Charts Row --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- New Customers Over Time --}}
        <div class="lg:col-span-2 bg-white rounded-xl shadow p-5">
            <h2 class="text-sm font-semibold text-gray-900 mb-4">New Customer Registrations</h2>
            @if($newCustomersByMonth->isEmpty())
                <div class="flex flex-col items-center justify-center h-48 text-gray-400">
                    <i class="fa-solid fa-user-plus text-4xl mb-2"></i>
                    <p class="text-sm">No new customers in this period</p>
                </div>
            @else
                <canvas id="newCustomersChart" height="100"></canvas>
            @endif
        </div>

        {{-- Walk-in vs Registered --}}
        <div class="bg-white rounded-xl shadow p-5">
            <h2 class="text-sm font-semibold text-gray-900 mb-4">Orders: Walk-in vs Registered</h2>
            @if(($walkInOrders + $registeredOrders) === 0)
                <div class="flex flex-col items-center justify-center h-48 text-gray-400">
                    <i class="fa-solid fa-users text-4xl mb-2"></i>
                    <p class="text-sm">No orders in this period</p>
                </div>
            @else
                <canvas id="orderTypeChart" height="200"></canvas>
                <div class="mt-4 space-y-2">
                    <div class="flex items-center justify-between text-sm">
                        <span class="flex items-center gap-2">
                            <span class="w-3 h-3 rounded-full bg-violet-500 inline-block"></span>
                            Registered Customers
                        </span>
                        <span class="font-semibold">{{ number_format($registeredOrders) }}</span>
                    </div>
                    <div class="flex items-center justify-between text-sm">
                        <span class="flex items-center gap-2">
                            <span class="w-3 h-3 rounded-full bg-gray-400 inline-block"></span>
                            Walk-in
                        </span>
                        <span class="font-semibold">{{ number_format($walkInOrders) }}</span>
                    </div>
                </div>
            @endif
        </div>
    </div>

    {{-- Top Customers Table --}}
    <div class="bg-white rounded-xl shadow overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
            <h2 class="text-sm font-semibold text-gray-900">Top Customers by Spending</h2>
            <span class="text-xs text-gray-500">{{ $dateFrom->format('d M Y') }} – {{ $dateTo->format('d M Y') }}</span>
        </div>
        @if($topCustomers->isEmpty())
            <div class="flex flex-col items-center justify-center h-32 text-gray-400">
                <p class="text-sm">No customer orders in this period</p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-100">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase">#</th>
                            <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Customer</th>
                            <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Contact</th>
                            <th class="px-5 py-3 text-right text-xs font-semibold text-gray-500 uppercase">Orders</th>
                            <th class="px-5 py-3 text-right text-xs font-semibold text-gray-500 uppercase">Total Spent</th>
                            <th class="px-5 py-3 text-right text-xs font-semibold text-gray-500 uppercase">Avg per Visit</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @foreach($topCustomers as $i => $row)
                            @php $avg = $row->order_count > 0 ? $row->total_spent / $row->order_count : 0; @endphp
                            <tr class="hover:bg-gray-50">
                                <td class="px-5 py-3 text-sm text-gray-500">{{ $i + 1 }}</td>
                                <td class="px-5 py-3">
                                    <p class="text-sm font-medium text-gray-900">{{ $row->customer->name ?? '–' }}</p>
                                    @if($i < 3)
                                        <span class="inline-flex items-center gap-1 text-xs font-medium px-2 py-0.5 rounded-full
                                            {{ $i === 0 ? 'bg-amber-100 text-amber-700' : ($i === 1 ? 'bg-gray-100 text-gray-600' : 'bg-orange-100 text-orange-700') }}">
                                            <i class="fa-solid fa-trophy text-xs"></i>
                                            {{ $i === 0 ? 'Top Customer' : ($i === 1 ? '2nd Place' : '3rd Place') }}
                                        </span>
                                    @endif
                                </td>
                                <td class="px-5 py-3 text-sm text-gray-500">{{ $row->customer->phone ?? $row->customer->email ?? '–' }}</td>
                                <td class="px-5 py-3 text-sm text-right text-gray-700">{{ number_format($row->order_count) }}</td>
                                <td class="px-5 py-3 text-sm text-right font-semibold text-gray-900">{{ number_format($row->total_spent, 0) }}</td>
                                <td class="px-5 py-3 text-sm text-right text-gray-700">{{ number_format($avg, 0) }}</td>
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
@if($newCustomersByMonth->isNotEmpty())
    new Chart(document.getElementById('newCustomersChart'), {
        type: 'bar',
        data: {
            labels: @json($newCustomersByMonth->pluck('month')),
            datasets: [{
                label: 'New Customers',
                data: @json($newCustomersByMonth->pluck('count')),
                backgroundColor: 'rgba(139, 92, 246, 0.7)',
                borderColor: 'rgb(139, 92, 246)',
                borderWidth: 2,
                borderRadius: 6,
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { display: false },
                tooltip: { callbacks: { label: ctx => ' ' + ctx.raw + ' customers' } }
            },
            scales: {
                y: { beginAtZero: true, ticks: { stepSize: 1 }, grid: { color: 'rgba(0,0,0,0.05)' } },
                x: { grid: { display: false } }
            }
        }
    });
@endif

@if(($walkInOrders + $registeredOrders) > 0)
    new Chart(document.getElementById('orderTypeChart'), {
        type: 'doughnut',
        data: {
            labels: ['Registered Customers', 'Walk-in'],
            datasets: [{
                data: [{{ $registeredOrders }}, {{ $walkInOrders }}],
                backgroundColor: ['rgba(139, 92, 246, 0.8)', 'rgba(156, 163, 175, 0.8)'],
                borderWidth: 2,
                borderColor: '#fff',
            }]
        },
        options: {
            responsive: true,
            cutout: '65%',
            plugins: {
                legend: { display: false },
                tooltip: { callbacks: { label: ctx => ' ' + ctx.raw + ' orders' } }
            }
        }
    });
@endif
</script>
@endpush
