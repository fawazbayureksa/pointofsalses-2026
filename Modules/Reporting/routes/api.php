<?php

use Modules\Reporting\Services\ReportingService;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Reporting Module – API Routes
|--------------------------------------------------------------------------
*/

Route::prefix('api/reports')->middleware(['auth:sanctum', 'permission:view_reports'])->group(function () {

    // Sales report by date range
    Route::get('sales', function (\Illuminate\Http\Request $request) {
        $request->validate([
            'from' => 'required|date',
            'to'   => 'required|date|after_or_equal:from',
        ]);

        return response()->json(
            app(ReportingService::class)->salesByDate($request->from, $request->to)
        );
    });

    // Top-selling products
    Route::get('top-products', function (\Illuminate\Http\Request $request) {
        $limit = $request->integer('limit', 20);

        return response()->json(
            app(ReportingService::class)->topProducts($limit)
        );
    });

    // Revenue by payment method
    Route::get('payment-methods', function () {
        return response()->json(
            app(ReportingService::class)->revenueByPaymentMethod()
        );
    });

    // Daily summary
    Route::get('daily-summary', function (\Illuminate\Http\Request $request) {
        $date = $request->date ?? today()->toDateString();

        return response()->json([
            'date'    => $date,
            'summary' => app(ReportingService::class)->dailySummary($date),
        ]);
    });
});
