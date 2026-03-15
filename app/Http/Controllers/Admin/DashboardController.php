<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Outlet;
use App\Models\Product;
use App\Models\Tenant;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Spatie\Activitylog\Models\Activity;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $totalSalesToday     = 0;
        $totalSalesYesterday = 0;
        $totalOrders         = 0;
        $completedOrders     = 0;
        $activeOutlets       = 0;
        $totalOutlets        = 0;
        $totalCustomers      = 0;
        $newCustomers        = 0;
        $recentOrders        = collect();
        $lowStockProducts    = collect();

        $tenants = Tenant::all();

        tenancy()->runForMultiple($tenants, function () use (
            &$totalSalesToday,
            &$totalSalesYesterday,
            &$totalOrders,
            &$completedOrders,
            &$activeOutlets,
            &$totalOutlets,
            &$totalCustomers,
            &$newCustomers,
            &$recentOrders,
            &$lowStockProducts
        ) {
            $totalSalesToday += Order::where('status', 'completed')
                ->whereDate('created_at', Carbon::today())
                ->sum('total_amount');

            $totalSalesYesterday += Order::where('status', 'completed')
                ->whereDate('created_at', Carbon::yesterday())
                ->sum('total_amount');

            $totalOrders     += Order::whereDate('created_at', Carbon::today())->count();
            $completedOrders += Order::where('status', 'completed')->whereDate('created_at', Carbon::today())->count();

            $activeOutlets  += Outlet::where('is_active', true)->count();
            $totalOutlets   += Outlet::count();

            $totalCustomers += Customer::count();
            $newCustomers   += Customer::whereBetween('created_at', [Carbon::now()->startOfWeek(), Carbon::now()])->count();

            $recentOrders = $recentOrders->merge(
                Order::with(['customer', 'outlet'])->latest()->take(5)->get()
            );

            $lowStockProducts = $lowStockProducts->merge(
                Product::where('track_stock', true)
                    ->whereColumn('stock', '<=', 'low_stock_threshold')
                    ->where('is_active', true)
                    ->take(5)
                    ->get()
            );
        });

        $salesGrowth = $totalSalesYesterday > 0
            ? round((($totalSalesToday - $totalSalesYesterday) / $totalSalesYesterday) * 100, 1)
            : 0;

        $recentOrders     = $recentOrders->sortByDesc('created_at')->take(5)->values();
        $lowStockProducts = $lowStockProducts->take(5)->values();

        // Activity logs live in the central DB
        // $recentActivities = Activity::with('causer')->latest()->take(10)->get();
        $recentActivities = [];
        return view('admin.dashboard.index', compact(
            'totalSalesToday',
            'salesGrowth',
            'totalOrders',
            'completedOrders',
            'activeOutlets',
            'totalOutlets',
            'totalCustomers',
            'newCustomers',
            'recentOrders',
            'lowStockProducts',
            // 'recentActivities'
        ));
    }
}
