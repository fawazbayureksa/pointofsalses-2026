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

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $today     = Carbon::today();
        $yesterday = Carbon::yesterday();

        $totalSalesToday = Order::where('status', 'completed')
            ->whereDate('created_at', $today)
            ->sum('total_amount');

        $totalSalesYesterday = Order::where('status', 'completed')
            ->whereDate('created_at', $yesterday)
            ->sum('total_amount');

        $salesGrowth = $totalSalesYesterday > 0
            ? round((($totalSalesToday - $totalSalesYesterday) / $totalSalesYesterday) * 100, 1)
            : 0;

        $totalOrders     = Order::whereDate('created_at', $today)->count();
        $completedOrders = Order::where('status', 'completed')->whereDate('created_at', $today)->count();

        $activeOutlets = Outlet::where('is_active', true)->count();
        $totalOutlets  = Outlet::count();

        $totalCustomers = Customer::count();
        $newCustomers   = Customer::whereBetween('created_at', [
            Carbon::now()->startOfWeek(), Carbon::now(),
        ])->count();

        $recentOrders = Order::with(['customer', 'outlet'])
            ->latest()
            ->take(5)
            ->get();

        $lowStockProducts = collect();

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
        ));
    }
}
