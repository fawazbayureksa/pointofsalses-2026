@extends('layouts.admin')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard')

@section('content')
    <div class="space-y-6">

        @if (session('success'))
            <div class="bg-green-50 border border-green-200 rounded-lg p-4 flex items-center space-x-3">
                <i class="fa-solid fa-circle-check text-green-400"></i>
                <p class="text-sm font-medium text-green-800">{{ session('success') }}</p>
            </div>
        @endif

        {{-- ── ROW 1: Primary KPI cards ──────────────────────────────────────── --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">

            {{-- Today's Sales --}}
            <div class="bg-gradient-to-br from-emerald-500 to-emerald-600 rounded-xl p-5 text-white shadow-sm">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-emerald-100 text-xs font-medium uppercase tracking-wide">Sales Today</p>
                        <p class="text-2xl font-bold mt-1">Rp {{ number_format($totalSalesToday, 0, ',', '.') }}</p>
                        <p class="text-emerald-100 text-xs mt-2 flex items-center">
                            @if ($salesGrowth >= 0)
                                <i class="fa-solid fa-arrow-trend-up mr-1"></i>+{{ $salesGrowth }}% vs yesterday
                            @else
                                <i class="fa-solid fa-arrow-trend-down mr-1"></i>{{ $salesGrowth }}% vs yesterday
                            @endif
                        </p>
                    </div>
                    <div class="bg-emerald-400 bg-opacity-30 rounded-lg p-2.5">
                        <i class="fa-solid fa-chart-line text-xl"></i>
                    </div>
                </div>
            </div>

            {{-- Monthly Revenue --}}
            <div class="bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl p-5 text-white shadow-sm">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-blue-100 text-xs font-medium uppercase tracking-wide">Revenue This Month</p>
                        <p class="text-2xl font-bold mt-1">Rp {{ number_format($monthlySales, 0, ',', '.') }}</p>
                        <p class="text-blue-100 text-xs mt-2 flex items-center">
                            @if ($monthlyGrowth >= 0)
                                <i class="fa-solid fa-arrow-trend-up mr-1"></i>+{{ $monthlyGrowth }}% vs last month
                            @else
                                <i class="fa-solid fa-arrow-trend-down mr-1"></i>{{ $monthlyGrowth }}% vs last month
                            @endif
                        </p>
                    </div>
                    <div class="bg-blue-400 bg-opacity-30 rounded-lg p-2.5">
                        <i class="fa-solid fa-money-bill-wave text-xl"></i>
                    </div>
                </div>
            </div>

            {{-- Orders Today --}}
            <div class="bg-gradient-to-br from-violet-500 to-violet-600 rounded-xl p-5 text-white shadow-sm">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-violet-100 text-xs font-medium uppercase tracking-wide">Orders Today</p>
                        <p class="text-2xl font-bold mt-1">{{ $totalOrders }}</p>
                        <p class="text-violet-100 text-xs mt-2">
                            <i class="fa-solid fa-check mr-1"></i>{{ $completedOrders }} completed
                        </p>
                    </div>
                    <div class="bg-violet-400 bg-opacity-30 rounded-lg p-2.5">
                        <i class="fa-solid fa-shopping-bag text-xl"></i>
                    </div>
                </div>
            </div>

            {{-- Pending Orders --}}
            <div class="bg-gradient-to-br from-amber-500 to-amber-600 rounded-xl p-5 text-white shadow-sm">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-amber-100 text-xs font-medium uppercase tracking-wide">Pending Orders</p>
                        <p class="text-2xl font-bold mt-1">{{ $pendingOrders }}</p>
                        <p class="text-amber-100 text-xs mt-2">
                            <i class="fa-solid fa-clock mr-1"></i>Awaiting processing
                        </p>
                    </div>
                    <div class="bg-amber-400 bg-opacity-30 rounded-lg p-2.5">
                        <i class="fa-solid fa-hourglass-half text-xl"></i>
                    </div>
                </div>
            </div>
        </div>

        {{-- ── ROW 2: Secondary info cards ───────────────────────────────────── --}}
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">
            <div class="bg-white rounded-xl border border-gray-200 p-5 shadow-sm flex items-center space-x-4">
                <div class="bg-orange-100 rounded-lg p-3">
                    <i class="fa-solid fa-users text-orange-500 text-lg"></i>
                </div>
                <div>
                    <p class="text-xs text-gray-500 uppercase tracking-wide">Customers</p>
                    <p class="text-xl font-bold text-gray-900">{{ number_format($totalCustomers) }}</p>
                    <p class="text-xs text-green-600 mt-0.5">+{{ $newCustomers }} this week</p>
                </div>
            </div>
            <div class="bg-white rounded-xl border border-gray-200 p-5 shadow-sm flex items-center space-x-4">
                <div class="bg-purple-100 rounded-lg p-3">
                    <i class="fa-solid fa-store text-purple-500 text-lg"></i>
                </div>
                <div>
                    <p class="text-xs text-gray-500 uppercase tracking-wide">Active Outlets</p>
                    <p class="text-xl font-bold text-gray-900">{{ $activeOutlets }}</p>
                    <p class="text-xs text-gray-500 mt-0.5">{{ $totalOutlets }} total</p>
                </div>
            </div>
            <div class="bg-white rounded-xl border border-gray-200 p-5 shadow-sm flex items-center space-x-4">
                <div class="bg-teal-100 rounded-lg p-3">
                    <i class="fa-solid fa-receipt text-teal-500 text-lg"></i>
                </div>
                <div>
                    <p class="text-xs text-gray-500 uppercase tracking-wide">Avg. Order Today</p>
                    <p class="text-xl font-bold text-gray-900">Rp {{ number_format($avgOrderValue, 0, ',', '.') }}</p>
                    <p class="text-xs text-gray-500 mt-0.5">Completed orders only</p>
                </div>
            </div>
        </div>

        {{-- ── ROW 3: Sales chart + Today's status breakdown ──────────────────── --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">

            {{-- 7-day sales bar chart --}}
            <div class="lg:col-span-2 bg-white rounded-xl border border-gray-200 p-5 shadow-sm">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h3 class="text-sm font-semibold text-gray-900">Sales — Last 7 Days</h3>
                        <p class="text-xs text-gray-500 mt-0.5">Completed orders only</p>
                    </div>
                    <span class="text-xs text-gray-400">Rp</span>
                </div>
                <div class="relative h-56">
                    <canvas id="salesChart"></canvas>
                </div>
            </div>

            {{-- Today's orders by status --}}
            <div class="bg-white rounded-xl border border-gray-200 p-5 shadow-sm">
                <h3 class="text-sm font-semibold text-gray-900 mb-4">Today's Orders by Status</h3>
                @php
                    $statusConfig = [
                        'completed' => [
                            'color' => 'bg-green-500',
                            'text' => 'text-green-700',
                            'bg' => 'bg-green-50',
                            'label' => 'Completed',
                        ],
                        'pending' => [
                            'color' => 'bg-yellow-500',
                            'text' => 'text-yellow-700',
                            'bg' => 'bg-yellow-50',
                            'label' => 'Pending',
                        ],
                        'processing' => [
                            'color' => 'bg-blue-500',
                            'text' => 'text-blue-700',
                            'bg' => 'bg-blue-50',
                            'label' => 'Processing',
                        ],
                        'cancelled' => [
                            'color' => 'bg-red-500',
                            'text' => 'text-red-700',
                            'bg' => 'bg-red-50',
                            'label' => 'Cancelled',
                        ],
                    ];
                    $statuses = ['completed', 'pending', 'processing', 'cancelled'];
                @endphp
                @if ($totalOrders > 0)
                    {{-- Progress bar --}}
                    <div class="flex rounded-full overflow-hidden h-3 mb-4">
                        @foreach ($statuses as $status)
                            @php $count = $ordersByStatus[$status] ?? 0; @endphp
                            @if ($count > 0)
                                <div class="{{ $statusConfig[$status]['color'] }} transition-all"
                                    style="width: {{ round(($count / $totalOrders) * 100) }}%"
                                    title="{{ $statusConfig[$status]['label'] }}: {{ $count }}"></div>
                            @endif
                        @endforeach
                    </div>
                    <div class="space-y-2">
                        @foreach ($statuses as $status)
                            @php $count = $ordersByStatus[$status] ?? 0; @endphp
                            <div
                                class="flex items-center justify-between px-3 py-2 {{ $statusConfig[$status]['bg'] }} rounded-lg">
                                <div class="flex items-center space-x-2">
                                    <span class="w-2 h-2 rounded-full {{ $statusConfig[$status]['color'] }}"></span>
                                    <span
                                        class="text-xs font-medium {{ $statusConfig[$status]['text'] }}">{{ $statusConfig[$status]['label'] }}</span>
                                </div>
                                <span
                                    class="text-sm font-bold {{ $statusConfig[$status]['text'] }}">{{ $count }}</span>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="flex flex-col items-center justify-center h-40 text-gray-400">
                        <i class="fa-solid fa-inbox text-3xl mb-2"></i>
                        <p class="text-sm">No orders today</p>
                    </div>
                @endif

                {{-- Quick link to POS --}}
                <a href="{{ route('admin.pos.index') }}"
                    class="mt-4 flex items-center justify-center w-full px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition">
                    <i class="fa-solid fa-cash-register mr-2"></i>Open Cashier
                </a>
            </div>
        </div>

        {{-- ── ROW 4: Top Products + Payment Methods ───────────────────────────── --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">

            {{-- Top 5 Products this month --}}
            <div class="bg-white rounded-xl border border-gray-200 p-5 shadow-sm">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-sm font-semibold text-gray-900">Top Products <span
                            class="font-normal text-gray-400 text-xs">— this month</span></h3>
                    <a href="{{ route('admin.products.index') }}" class="text-xs text-blue-600 hover:underline">View
                        all</a>
                </div>
                @if ($topProducts->isNotEmpty())
                    <div class="space-y-3">
                        @foreach ($topProducts as $i => $product)
                            @php $maxSold = $topProducts->max('sold'); @endphp
                            <div>
                                <div class="flex items-center justify-between mb-1">
                                    <div class="flex items-center space-x-2">
                                        <span
                                            class="w-5 h-5 rounded-full text-xs flex items-center justify-center font-bold
                                        {{ $i === 0 ? 'bg-yellow-100 text-yellow-700' : 'bg-gray-100 text-gray-500' }}">{{ $i + 1 }}</span>
                                        <span
                                            class="text-sm text-gray-800 font-medium truncate max-w-[180px]">{{ $product->product_name }}</span>
                                    </div>
                                    <div class="text-right shrink-0">
                                        <span
                                            class="text-xs font-semibold text-gray-900">{{ number_format($product->sold, 0) }}
                                            sold</span>
                                        <span class="text-xs text-gray-400 ml-2">Rp
                                            {{ number_format($product->revenue, 0, ',', '.') }}</span>
                                    </div>
                                </div>
                                <div class="w-full bg-gray-100 rounded-full h-1.5">
                                    <div class="bg-blue-500 h-1.5 rounded-full"
                                        style="width: {{ $maxSold > 0 ? round(($product->sold / $maxSold) * 100) : 0 }}%">
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-center text-gray-400 py-8 text-sm">No sales data this month</p>
                @endif
            </div>

            {{-- Payment Methods this month --}}
            <div class="bg-white rounded-xl border border-gray-200 p-5 shadow-sm">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-sm font-semibold text-gray-900">Payment Methods <span
                            class="font-normal text-gray-400 text-xs">— this month</span></h3>
                    <a href="{{ route('admin.orders.payments') }}" class="text-xs text-blue-600 hover:underline">View
                        all</a>
                </div>
                @if ($paymentMethodStats->isNotEmpty())
                    @php $maxPayment = $paymentMethodStats->max('total'); @endphp
                    <div class="space-y-3">
                        @php
                            $methodColors = [
                                'cash' => 'bg-green-500',
                                'card' => 'bg-blue-500',
                                'transfer' => 'bg-purple-500',
                                'qris' => 'bg-orange-500',
                            ];
                            $methodIcons = [
                                'cash' => 'fa-money-bill',
                                'card' => 'fa-credit-card',
                                'transfer' => 'fa-building-columns',
                                'qris' => 'fa-qrcode',
                            ];
                        @endphp
                        @foreach ($paymentMethodStats as $method)
                            @php
                                $key = strtolower($method->payment_method);
                                $color = $methodColors[$key] ?? 'bg-gray-400';
                                $icon = $methodIcons[$key] ?? 'fa-circle-dot';
                            @endphp
                            <div>
                                <div class="flex items-center justify-between mb-1">
                                    <div class="flex items-center space-x-2">
                                        <div
                                            class="w-6 h-6 {{ $color }} bg-opacity-20 rounded flex items-center justify-center">
                                            <i
                                                class="fa-solid {{ $icon }} text-xs {{ str_replace('bg-', 'text-', $color) }}"></i>
                                        </div>
                                        <span
                                            class="text-sm text-gray-700 capitalize">{{ $method->payment_method }}</span>
                                    </div>
                                    <div class="text-right">
                                        <span class="text-xs font-semibold text-gray-900">{{ $method->count }}×</span>
                                        <span class="text-xs text-gray-400 ml-2">Rp
                                            {{ number_format($method->total, 0, ',', '.') }}</span>
                                    </div>
                                </div>
                                <div class="w-full bg-gray-100 rounded-full h-1.5">
                                    <div class="{{ $color }} h-1.5 rounded-full"
                                        style="width: {{ $maxPayment > 0 ? round(($method->total / $maxPayment) * 100) : 0 }}%">
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-center text-gray-400 py-8 text-sm">No payment data this month</p>
                @endif
            </div>
        </div>

        {{-- ── ROW 5: Low stock ────────────────────────────────────────────────── --}}
        <div class="bg-white rounded-xl border border-gray-200 p-5 shadow-sm">
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center space-x-2">
                    <h3 class="text-sm font-semibold text-gray-900">Low Stock Alert</h3>
                    @if ($lowStockProducts->isNotEmpty())
                        <span
                            class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                            {{ $lowStockProducts->count() }}
                        </span>
                    @endif
                </div>
                <a href="{{ route('admin.inventory.stock') }}" class="text-xs text-blue-600 hover:underline">Manage
                    stock</a>
            </div>
            @if ($lowStockProducts->isNotEmpty())
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                    @foreach ($lowStockProducts as $item)
                        <div class="flex items-center justify-between p-3 bg-red-50 border border-red-100 rounded-lg">
                            <div class="min-w-0">
                                <p class="text-sm font-medium text-gray-900 truncate">{{ $item->name }}</p>
                                <p class="text-xs text-gray-500">{{ $item->outlet_name }}</p>
                            </div>
                            <div class="ml-3 shrink-0 text-right">
                                <p class="text-sm font-bold text-red-600">{{ number_format($item->stock, 0) }}</p>
                                <p class="text-xs text-gray-400">/ {{ number_format($item->threshold, 0) }} min</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="flex flex-col items-center justify-center py-10 text-gray-400">
                    <i class="fa-solid fa-circle-check text-3xl text-green-400 mb-2"></i>
                    <p class="text-sm">All stock levels are healthy</p>
                </div>
            @endif
        </div>

        {{-- ── ROW 6: Recent Orders ────────────────────────────────────────────── --}}
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm">
            <div class="flex items-center justify-between px-5 pt-5 pb-3 border-b border-gray-100">
                <h3 class="text-sm font-semibold text-gray-900">Recent Orders</h3>
                <a href="{{ route('admin.orders.index') }}" class="text-xs text-blue-600 hover:underline">View all</a>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-gray-50 text-xs text-gray-500 uppercase">
                        <tr>
                            <th class="px-5 py-3 text-left font-medium">Order</th>
                            <th class="px-5 py-3 text-left font-medium">Customer</th>
                            <th class="px-5 py-3 text-left font-medium">Outlet</th>
                            <th class="px-5 py-3 text-right font-medium">Amount</th>
                            <th class="px-5 py-3 text-left font-medium">Status</th>
                            <th class="px-5 py-3 text-left font-medium">Time</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @forelse($recentOrders as $order)
                            <tr class="hover:bg-gray-50">
                                <td class="px-5 py-3 font-medium text-blue-600">#{{ $order->order_number ?? $order->id }}
                                </td>
                                <td class="px-5 py-3 text-gray-700">{{ $order->customer?->name ?? 'Guest' }}</td>
                                <td class="px-5 py-3 text-gray-500">{{ $order->outlet?->name ?? '-' }}</td>
                                <td class="px-5 py-3 text-right font-medium text-gray-900">Rp
                                    {{ number_format($order->total_amount, 0, ',', '.') }}</td>
                                <td class="px-5 py-3">
                                    <span
                                        class="inline-flex px-2 py-0.5 rounded-full text-xs font-semibold
                                    @if ($order->status === 'completed') bg-green-100 text-green-800
                                    @elseif($order->status === 'pending') bg-yellow-100 text-yellow-800
                                    @elseif($order->status === 'cancelled') bg-red-100 text-red-800
                                    @else bg-gray-100 text-gray-700 @endif">
                                        {{ ucfirst($order->status) }}
                                    </span>
                                </td>
                                <td class="px-5 py-3 text-gray-400 text-xs">{{ $order->created_at->diffForHumans() }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-5 py-10 text-center text-gray-400">No orders yet</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <script>
        (function() {
            const labels = @json($salesLast7Days->pluck('label'));
            const data = @json($salesLast7Days->pluck('total'));

            const ctx = document.getElementById('salesChart');
            if (!ctx) return;

            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels,
                    datasets: [{
                        label: 'Sales (Rp)',
                        data,
                        backgroundColor: 'rgba(59, 130, 246, 0.15)',
                        borderColor: 'rgba(59, 130, 246, 0.8)',
                        borderWidth: 2,
                        borderRadius: 6,
                        borderSkipped: false,
                        tension: 0.4,
                    }],
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            callbacks: {
                                label: ctx => 'Rp ' + Number(ctx.parsed.y).toLocaleString('id-ID'),
                            },
                        },
                    },
                    scales: {
                        x: {
                            grid: {
                                display: false
                            },
                            ticks: {
                                font: {
                                    size: 11
                                }
                            }
                        },
                        y: {
                            beginAtZero: true,
                            grid: {
                                color: 'rgba(0,0,0,0.04)'
                            },
                            ticks: {
                                font: {
                                    size: 11
                                },
                                callback: v => 'Rp ' + Number(v).toLocaleString('id-ID'),
                            },
                        },
                    },
                },
            });
        })();
    </script>
@endpush
