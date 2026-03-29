<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Outlet;
use App\Models\Payment;
use App\Models\Product;
use App\Models\StockMovement;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function index()
    {
        $now = Carbon::now();

        $totalRevenueThisMonth = (float) Order::where('status', 'completed')
            ->whereMonth('created_at', $now->month)
            ->whereYear('created_at', $now->year)
            ->sum('total_amount');

        $totalOrdersThisMonth = Order::where('status', 'completed')
            ->whereMonth('created_at', $now->month)
            ->whereYear('created_at', $now->year)
            ->count();

        $totalCustomers = Customer::count();

        $totalProducts = Product::where('is_active', true)->count();

        // Revenue trend last 6 months
        $revenueTrend = collect();
        for ($i = 5; $i >= 0; $i--) {
            $month  = $now->copy()->subMonths($i);
            $total  = (float) Order::where('status', 'completed')
                ->whereMonth('created_at', $month->month)
                ->whereYear('created_at', $month->year)
                ->sum('total_amount');
            $revenueTrend->push(['label' => $month->format('M Y'), 'total' => $total]);
        }

        return view('admin.reports.index', compact(
            'totalRevenueThisMonth',
            'totalOrdersThisMonth',
            'totalCustomers',
            'totalProducts',
            'revenueTrend',
        ));
    }

    public function sales(Request $request)
    {
        $dateFrom = $request->filled('date_from')
            ? Carbon::parse($request->date_from)->startOfDay()
            : Carbon::now()->startOfMonth();

        $dateTo = $request->filled('date_to')
            ? Carbon::parse($request->date_to)->endOfDay()
            : Carbon::now()->endOfDay();

        $outletId = $request->outlet_id;

        $baseQuery = Order::where('status', 'completed')
            ->whereBetween('created_at', [$dateFrom, $dateTo]);

        if ($outletId) {
            $baseQuery->where('outlet_id', $outletId);
        }

        // Summary stats
        $totalRevenue    = (float) (clone $baseQuery)->sum('total_amount');
        $totalOrders     = (clone $baseQuery)->count();
        $totalTax        = (float) (clone $baseQuery)->sum('tax_amount');
        $totalDiscount   = (float) (clone $baseQuery)->sum('discount_amount');
        $avgOrderValue   = $totalOrders > 0 ? round($totalRevenue / $totalOrders, 2) : 0;

        // Revenue by day
        $revenueByDay = (clone $baseQuery)
            ->selectRaw('DATE(created_at) as date, COUNT(*) as orders, SUM(total_amount) as revenue')
            ->groupByRaw('DATE(created_at)')
            ->orderBy('date')
            ->get();

        // Revenue by outlet
        $revenueByOutlet = (clone $baseQuery)
            ->selectRaw('outlet_id, SUM(total_amount) as revenue, COUNT(*) as orders')
            ->with('outlet:id,name')
            ->groupBy('outlet_id')
            ->orderByDesc('revenue')
            ->get();

        // Payment method breakdown
        $paymentMethods = Payment::where('status', 'completed')
            ->whereBetween('created_at', [$dateFrom, $dateTo])
            ->when($outletId, fn($q) => $q->whereHas('order', fn($oq) => $oq->where('outlet_id', $outletId)))
            ->selectRaw('payment_method, COUNT(*) as count, SUM(amount) as total')
            ->groupBy('payment_method')
            ->orderByDesc('total')
            ->get();

        // Top selling products in period
        $topProducts = OrderItem::selectRaw('product_id, product_name, SUM(quantity) as qty_sold, SUM(subtotal) as revenue')
            ->whereHas('order', function ($q) use ($dateFrom, $dateTo, $outletId) {
                $q->where('status', 'completed')->whereBetween('created_at', [$dateFrom, $dateTo]);
                if ($outletId) {
                    $q->where('outlet_id', $outletId);
                }
            })
            ->groupBy('product_id', 'product_name')
            ->orderByDesc('revenue')
            ->limit(10)
            ->get();

        $outlets = Outlet::orderBy('name')->get();

        return view('admin.reports.sales', compact(
            'dateFrom',
            'dateTo',
            'outletId',
            'outlets',
            'totalRevenue',
            'totalOrders',
            'totalTax',
            'totalDiscount',
            'avgOrderValue',
            'revenueByDay',
            'revenueByOutlet',
            'paymentMethods',
            'topProducts',
        ));
    }

    public function products(Request $request)
    {
        $dateFrom = $request->filled('date_from')
            ? Carbon::parse($request->date_from)->startOfDay()
            : Carbon::now()->startOfMonth();

        $dateTo = $request->filled('date_to')
            ? Carbon::parse($request->date_to)->endOfDay()
            : Carbon::now()->endOfDay();

        $categoryId = $request->category_id;

        // Top products by revenue
        $topByRevenue = OrderItem::selectRaw('product_id, product_name, SUM(quantity) as qty_sold, SUM(subtotal) as revenue, SUM(subtotal - (COALESCE(cost_price, 0) * quantity)) as profit')
            ->whereHas('order', function ($q) use ($dateFrom, $dateTo) {
                $q->where('status', 'completed')->whereBetween('created_at', [$dateFrom, $dateTo]);
            })
            ->when($categoryId, function ($q) use ($categoryId) {
                $q->whereHas('product', fn($pq) => $pq->where('category_id', $categoryId));
            })
            ->groupBy('product_id', 'product_name')
            ->orderByDesc('revenue')
            ->limit(15)
            ->get();

        // Top products by quantity
        $topByQty = OrderItem::selectRaw('product_id, product_name, SUM(quantity) as qty_sold, SUM(subtotal) as revenue')
            ->whereHas('order', function ($q) use ($dateFrom, $dateTo) {
                $q->where('status', 'completed')->whereBetween('created_at', [$dateFrom, $dateTo]);
            })
            ->when($categoryId, function ($q) use ($categoryId) {
                $q->whereHas('product', fn($pq) => $pq->where('category_id', $categoryId));
            })
            ->groupBy('product_id', 'product_name')
            ->orderByDesc('qty_sold')
            ->limit(15)
            ->get();

        // Revenue by category
        $revenueByCategory = OrderItem::selectRaw('products.category_id, categories.name as category_name, SUM(order_items.subtotal) as revenue, SUM(order_items.quantity) as qty_sold')
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->join('categories', 'products.category_id', '=', 'categories.id')
            ->whereHas('order', function ($q) use ($dateFrom, $dateTo) {
                $q->where('status', 'completed')->whereBetween('created_at', [$dateFrom, $dateTo]);
            })
            ->groupBy('products.category_id', 'categories.name')
            ->orderByDesc('revenue')
            ->get();

        // Slow movers (products with very few sales this period)
        $slowMovers = Product::where('is_active', true)
            ->withCount(['orderItems as orders_count' => function ($q) use ($dateFrom, $dateTo) {
                $q->whereHas('order', fn($oq) => $oq->where('status', 'completed')->whereBetween('created_at', [$dateFrom, $dateTo]));
            }])
            ->having('orders_count', '=', 0)
            ->orderBy('name')
            ->limit(10)
            ->get();

        $categories = \App\Models\Category::orderBy('name')->get();

        return view('admin.reports.products', compact(
            'dateFrom',
            'dateTo',
            'categoryId',
            'categories',
            'topByRevenue',
            'topByQty',
            'revenueByCategory',
            'slowMovers',
        ));
    }

    public function customers(Request $request)
    {
        $dateFrom = $request->filled('date_from')
            ? Carbon::parse($request->date_from)->startOfDay()
            : Carbon::now()->startOfMonth();

        $dateTo = $request->filled('date_to')
            ? Carbon::parse($request->date_to)->endOfDay()
            : Carbon::now()->endOfDay();

        // Top customers by spending
        $topCustomers = Order::where('status', 'completed')
            ->whereBetween('created_at', [$dateFrom, $dateTo])
            ->whereNotNull('customer_id')
            ->selectRaw('customer_id, COUNT(*) as order_count, SUM(total_amount) as total_spent')
            ->with('customer:id,name,email,phone')
            ->groupBy('customer_id')
            ->orderByDesc('total_spent')
            ->limit(15)
            ->get();

        // New customers over time (by month)
        $newCustomersByMonth = Customer::selectRaw('DATE_FORMAT(created_at, "%Y-%m") as month, COUNT(*) as count')
            ->whereBetween('created_at', [$dateFrom, $dateTo])
            ->groupByRaw('DATE_FORMAT(created_at, "%Y-%m")')
            ->orderBy('month')
            ->get();

        // Customer order frequency
        $totalActiveCustomers = Customer::where('is_active', true)->count();
        $customersWithOrders  = Order::where('status', 'completed')
            ->whereBetween('created_at', [$dateFrom, $dateTo])
            ->whereNotNull('customer_id')
            ->distinct()
            ->count('customer_id');

        $walkInOrders = Order::where('status', 'completed')
            ->whereBetween('created_at', [$dateFrom, $dateTo])
            ->whereNull('customer_id')
            ->count();

        $registeredOrders = Order::where('status', 'completed')
            ->whereBetween('created_at', [$dateFrom, $dateTo])
            ->whereNotNull('customer_id')
            ->count();

        // Avg spend per visit
        $avgSpendPerVisit = $registeredOrders > 0
            ? (float) Order::where('status', 'completed')
                ->whereBetween('created_at', [$dateFrom, $dateTo])
                ->whereNotNull('customer_id')
                ->avg('total_amount')
            : 0;

        // New customers in period
        $newCustomers = Customer::whereBetween('created_at', [$dateFrom, $dateTo])->count();

        return view('admin.reports.customers', compact(
            'dateFrom',
            'dateTo',
            'topCustomers',
            'newCustomersByMonth',
            'totalActiveCustomers',
            'customersWithOrders',
            'walkInOrders',
            'registeredOrders',
            'avgSpendPerVisit',
            'newCustomers',
        ));
    }

    public function inventory(Request $request)
    {
        $outletId = $request->outlet_id;

        // Stock overview
        $lowStockItems = Product::where('track_stock', true)
            ->where('is_active', true)
            ->with('outlets')
            ->get()
            ->flatMap(function ($product) use ($outletId) {
                return $product->outlets
                    ->when($outletId, fn($c) => $c->where('id', $outletId))
                    ->filter(
                        fn($outlet) =>
                        $outlet->pivot->low_stock_threshold > 0 &&
                            $outlet->pivot->stock <= $outlet->pivot->low_stock_threshold
                    )
                    ->map(fn($outlet) => (object) [
                        'product_id'   => $product->id,
                        'product_name' => $product->name,
                        'sku'          => $product->sku,
                        'outlet_name'  => $outlet->name,
                        'stock'        => $outlet->pivot->stock,
                        'threshold'    => $outlet->pivot->low_stock_threshold,
                        'status'       => $outlet->pivot->stock == 0 ? 'out_of_stock' : 'low_stock',
                    ]);
            });

        $outOfStockCount = $lowStockItems->where('status', 'out_of_stock')->count();
        $lowStockCount   = $lowStockItems->where('status', 'low_stock')->count();

        // Stock by outlet
        $stockByOutlet = Outlet::with(['products' => function ($q) {
            $q->where('track_stock', true)->where('is_active', true);
        }])->when($outletId, fn($q) => $q->where('id', $outletId))->get()
            ->map(function ($outlet) {
                $totalStock = $outlet->products->sum(fn($p) => $p->pivot->stock);
                $productCount = $outlet->products->count();
                return (object) [
                    'outlet_name'   => $outlet->name,
                    'total_stock'   => $totalStock,
                    'product_count' => $productCount,
                ];
            });

        // Recent stock movements (last 30 days)
        $recentMovements = StockMovement::with(['product:id,name,sku', 'outlet:id,name'])
            ->when($outletId, fn($q) => $q->where('outlet_id', $outletId))
            ->where('created_at', '>=', Carbon::now()->subDays(30))
            ->latest()
            ->limit(20)
            ->get();

        // Movements by type
        $movementsByType = StockMovement::selectRaw('type, SUM(ABS(quantity_change)) as total_qty, COUNT(*) as count')
            ->when($outletId, fn($q) => $q->where('outlet_id', $outletId))
            ->where('created_at', '>=', Carbon::now()->subDays(30))
            ->groupBy('type')
            ->get();

        // Total stock value
        $totalStockValue = Product::where('is_active', true)
            ->where('track_stock', true)
            ->with(['outlets' => function ($q) use ($outletId) {
                if ($outletId) {
                    $q->where('outlets.id', $outletId);
                }
            }])
            ->get()
            ->sum(function ($product) {
                $stock = $product->outlets->sum(fn($o) => $o->pivot->stock);
                return $stock * (float) $product->cost_price;
            });

        $outlets = Outlet::orderBy('name')->get();

        return view('admin.reports.inventory', compact(
            'outletId',
            'outlets',
            'lowStockItems',
            'outOfStockCount',
            'lowStockCount',
            'stockByOutlet',
            'recentMovements',
            'movementsByType',
            'totalStockValue',
        ));
    }
}
