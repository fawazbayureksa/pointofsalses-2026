<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Reporting Module – API Routes
|--------------------------------------------------------------------------
*/

Route::prefix('api/reports')->middleware(['auth:sanctum'])->group(function () {

    // Sales report by date range
    Route::get('sales', function (\Illuminate\Http\Request $request) {
        $request->validate([
            'from' => 'required|date',
            'to'   => 'required|date|after_or_equal:from',
        ]);

        $data = \App\Models\Order::completed()
            ->whereBetween('completed_at', [$request->from, $request->to])
            ->selectRaw('DATE(completed_at) as date, COUNT(*) as orders, SUM(total_amount) as revenue')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        return response()->json($data);
    });

    // Top-selling products
    Route::get('top-products', function (\Illuminate\Http\Request $request) {
        $data = \App\Models\OrderItem::join('orders', 'orders.id', '=', 'order_items.order_id')
            ->where('orders.status', 'completed')
            ->selectRaw('product_id, product_name, SUM(quantity) as total_quantity, SUM(subtotal) as total_revenue')
            ->groupBy('product_id', 'product_name')
            ->orderByDesc('total_revenue')
            ->limit(20)
            ->get();

        return response()->json($data);
    });

    // Revenue by payment method
    Route::get('payment-methods', function () {
        $data = \App\Models\Payment::where('status', 'completed')
            ->selectRaw('payment_method, COUNT(*) as count, SUM(amount) as total')
            ->groupBy('payment_method')
            ->get();

        return response()->json($data);
    });
});
