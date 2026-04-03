# Shift Summary – API Notes

The shift summary screen aggregates today's sales for the logged-in cashier's
outlet.  No new backend endpoints are required — all data comes from existing
endpoints.

---

## GET /api/orders

Fetch completed orders for the current outlet and date to build the shift
summary.

### Request
```
GET /api/orders?outlet_id={id}&date_from={today}&date_to={today}&status=completed&per_page=200
```

| Parameter   | Value                        |
|-------------|------------------------------|
| `outlet_id` | `outletStore.currentOutlet.id` |
| `date_from` | `YYYY-MM-DD` (today)         |
| `date_to`   | `YYYY-MM-DD` (today)         |
| `status`    | `completed`                  |
| `per_page`  | `200` (enough for one shift) |

### Response `200`
```json
{
  "current_page": 1,
  "data": [
    {
      "id": 1,
      "order_number": "TEN-20260330-0001",
      "status": "completed",
      "payment_status": "paid",
      "total_amount": "95000.00",
      "created_at": "2026-03-30T08:10:00.000000Z",
      "cashier": { "id": 5, "name": "Andi" },
      "customer": null,
      "outlet": { "id": 1, "name": "Main Store" }
    }
  ],
  "total": 18
}
```

---

## GET /api/orders (pending count)

Fetch any orders still pending (not yet paid) so the cashier knows if there are
open tabs.

```
GET /api/orders?outlet_id={id}&status=pending&date_from={today}&date_to={today}
```

---

## GET /api/dashboard

Optional: use the dashboard endpoint for pre-aggregated payment method breakdown
when per-order detail is not needed.

```
GET /api/dashboard
```

### Relevant fields in response
```json
{
  "total_sales_today": 1250000,
  "total_orders_today": 18,
  "avg_order_value": 69444,
  "payment_methods": [
    { "method": "cash",     "count": 10, "total": 750000 },
    { "method": "qris",     "count":  6, "total": 400000 },
    { "method": "transfer", "count":  2, "total": 100000 }
  ]
}
```

---

## Client-Side Aggregation

The mobile app computes these values from the orders response:

| Metric                  | Calculation |
|-------------------------|-------------|
| Total sales             | `sum(total_amount)` |
| Transaction count       | `data.length` |
| Average order value     | `total_sales / count` |
| Cash collected          | `sum(total_amount) where payment_method === 'cash'` |
| Card / QRIS / Transfer  | Grouped by `payments[0].payment_method` |

> **Note:** Individual payment method per order is available via
> `GET /api/orders/{id}` (includes `payments[]`).  For an overview only the
> summary totals are computed client-side from the order list.
