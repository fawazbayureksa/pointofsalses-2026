<?php

namespace Modules\Reporting\Services;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use Illuminate\Support\Collection;

/**
 * ReportingService centralises all reporting queries.
 *
 * Controllers + console commands should call this service instead of
 * writing raw queries in route closures or controller methods.
 */
class ReportingService
{
    /**
     * Daily sales breakdown between two dates.
     *
     * @return Collection<int, object{date: string, orders: int, revenue: float}>
     */
    public function salesByDate(string $from, string $to): Collection
    {
        return Order::completed()
            ->whereBetween('completed_at', [$from, $to])
            ->selectRaw('DATE(completed_at) as date, COUNT(*) as orders, SUM(total_amount) as revenue, SUM(tax_amount) as tax')
            ->groupBy('date')
            ->orderBy('date')
            ->get();
    }

    /**
     * Top-selling products by revenue.
     *
     * @return Collection<int, object{product_id: int, product_name: string, total_quantity: float, total_revenue: float}>
     */
    public function topProducts(int $limit = 20): Collection
    {
        return OrderItem::join('orders', 'orders.id', '=', 'order_items.order_id')
            ->where('orders.status', 'completed')
            ->selectRaw('product_id, product_name, SUM(quantity) as total_quantity, SUM(subtotal) as total_revenue')
            ->groupBy('product_id', 'product_name')
            ->orderByDesc('total_revenue')
            ->limit($limit)
            ->get();
    }

    /**
     * Revenue total grouped by payment method.
     *
     * @return Collection<int, object{payment_method: string, count: int, total: float}>
     */
    public function revenueByPaymentMethod(): Collection
    {
        return Payment::where('status', 'completed')
            ->selectRaw('payment_method, COUNT(*) as count, SUM(amount) as total')
            ->groupBy('payment_method')
            ->get();
    }

    /**
     * Revenue summary for a given date.
     */
    public function dailySummary(string $date): object
    {
        return Order::completed()
            ->whereDate('completed_at', $date)
            ->selectRaw('COUNT(*) as total_orders, COALESCE(SUM(total_amount), 0) as total_revenue, COALESCE(SUM(tax_amount), 0) as total_tax, COALESCE(SUM(discount_amount), 0) as total_discount')
            ->first();
    }
}
