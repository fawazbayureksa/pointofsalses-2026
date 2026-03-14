<?php

use App\Http\Controllers\Api\ConfigController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Finance Module – API Routes
|--------------------------------------------------------------------------
*/

Route::prefix('api/finance')->middleware(['auth:sanctum'])->group(function () {
    // Daily sales summary
    Route::get('summary', function (\Illuminate\Http\Request $request) {
        $date    = $request->date ?? today()->toDateString();
        $summary = \App\Models\Order::completed()
            ->whereDate('completed_at', $date)
            ->selectRaw('COUNT(*) as total_orders, SUM(total_amount) as total_revenue, SUM(tax_amount) as total_tax')
            ->first();

        return response()->json([
            'date'    => $date,
            'summary' => $summary,
        ]);
    });

    // Payments list
    Route::get('payments', function (\Illuminate\Http\Request $request) {
        $payments = \App\Models\Payment::with('order')
            ->when($request->date_from, fn($q, $d) => $q->whereDate('paid_at', '>=', $d))
            ->when($request->date_to, fn($q, $d) => $q->whereDate('paid_at', '<=', $d))
            ->when($request->method, fn($q, $m) => $q->where('payment_method', $m))
            ->latest('paid_at')
            ->paginate(20);

        return response()->json($payments);
    });
});
