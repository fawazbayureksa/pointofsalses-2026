<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Outlet;
use App\Models\Payment;
use App\Models\Product;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $today     = Carbon::today();
        $yesterday = Carbon::yesterday();
        $now       = Carbon::now();

        // ── Today's sales ─────────────────────────────────────────────────────
        $totalSalesToday     = (float) Order::where('status', 'completed')->whereDate('created_at', $today)->sum('total_amount');
        $totalSalesYesterday = (float) Order::where('status', 'completed')->whereDate('created_at', $yesterday)->sum('total_amount');
        $salesGrowth = $totalSalesYesterday > 0
            ? round((($totalSalesToday - $totalSalesYesterday) / $totalSalesYesterday) * 100, 1)
            : ($totalSalesToday > 0 ? 100 : 0);

        // ── Monthly revenue ───────────────────────────────────────────────────
        $monthlySales = (float) Order::where('status', 'completed')
            ->whereMonth('created_at', $now->month)
            ->whereYear('created_at', $now->year)
            ->sum('total_amount');

        $lastMonthlySales = (float) Order::where('status', 'completed')
            ->whereMonth('created_at', $now->copy()->subMonth()->month)
            ->whereYear('created_at', $now->copy()->subMonth()->year)
            ->sum('total_amount');

        $monthlyGrowth = $lastMonthlySales > 0
            ? round((($monthlySales - $lastMonthlySales) / $lastMonthlySales) * 100, 1)
            : ($monthlySales > 0 ? 100 : 0);

        // ── Orders ────────────────────────────────────────────────────────────
        $totalOrders     = Order::whereDate('created_at', $today)->count();
        $completedOrders = Order::where('status', 'completed')->whereDate('created_at', $today)->count();
        $pendingOrders   = Order::where('status', 'pending')->count();

        $ordersByStatus = Order::whereDate('created_at', $today)
            ->selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        // ── Avg order value today ─────────────────────────────────────────────
        $avgOrderValue = (float) (Order::where('status', 'completed')->whereDate('created_at', $today)->avg('total_amount') ?? 0);

        // ── Outlets & customers ───────────────────────────────────────────────
        $activeOutlets  = Outlet::where('is_active', true)->count();
        $totalOutlets   = Outlet::count();
        $totalCustomers = Customer::count();
        $newCustomers   = Customer::whereBetween('created_at', [$now->copy()->startOfWeek(), $now])->count();

        // ── Sales last 7 days (for chart) ─────────────────────────────────────
        $salesLast7Days = collect();
        for ($i = 6; $i >= 0; $i--) {
            $date  = $today->copy()->subDays($i);
            $total = (float) Order::where('status', 'completed')->whereDate('created_at', $date)->sum('total_amount');
            $salesLast7Days->push(['label' => $date->format('D, d M'), 'total' => $total]);
        }

        // ── Top 5 products this month ─────────────────────────────────────────
        $topProducts = OrderItem::selectRaw('product_id, product_name, SUM(quantity) as sold, SUM(subtotal) as revenue')
            ->whereHas('order', fn($q) => $q->where('status', 'completed')
                ->whereMonth('created_at', $now->month)
                ->whereYear('created_at', $now->year))
            ->groupBy('product_id', 'product_name')
            ->orderByDesc('sold')
            ->limit(5)
            ->get();

        // ── Payment methods this month ────────────────────────────────────────
        $paymentMethodStats = Payment::selectRaw('payment_method, COUNT(*) as count, SUM(amount) as total')
            ->where('status', 'completed')
            ->whereMonth('created_at', $now->month)
            ->whereYear('created_at', $now->year)
            ->groupBy('payment_method')
            ->get();

        // ── Low stock products ────────────────────────────────────────────────
        $lowStockProducts = Product::where('track_stock', true)
            ->where('is_active', true)
            ->with('outlets')
            ->get()
            ->flatMap(function ($product) {
                return $product->outlets
                    ->filter(
                        fn($outlet) =>
                        $outlet->pivot->low_stock_threshold > 0 &&
                            $outlet->pivot->stock <= $outlet->pivot->low_stock_threshold
                    )
                    ->map(fn($outlet) => (object) [
                        'name'        => $product->name,
                        'outlet_name' => $outlet->name,
                        'stock'       => $outlet->pivot->stock,
                        'threshold'   => $outlet->pivot->low_stock_threshold,
                        'product_id'  => $product->id,
                    ]);
            })->take(6);

        // ── Recent orders ─────────────────────────────────────────────────────
        $recentOrders = Order::with(['customer', 'outlet'])->latest()->take(8)->get();

        return view('admin.dashboard.index', compact(
            'totalSalesToday',
            'salesGrowth',
            'monthlySales',
            'monthlyGrowth',
            'lastMonthlySales',
            'totalOrders',
            'completedOrders',
            'pendingOrders',
            'ordersByStatus',
            'avgOrderValue',
            'activeOutlets',
            'totalOutlets',
            'totalCustomers',
            'newCustomers',
            'salesLast7Days',
            'topProducts',
            'paymentMethodStats',
            'lowStockProducts',
            'recentOrders',
        ));
    }
}
