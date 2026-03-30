# Offline Queue – API Notes

The mobile app queues orders locally when the device is offline and syncs them
when connectivity is restored.  No new backend endpoints are required.  This
document describes which existing endpoints are used, the expected payloads, and
error-handling rules for the sync worker.

---

## Sync Strategy

1. Device goes offline → orders are saved to `AsyncStorage` in a queue.
2. Device reconnects → the sync worker calls the standard order creation flow:
   - `POST /api/orders` → creates the order (returns `pending` order).
   - `POST /api/orders/{id}/pay` → processes payment immediately.
3. If either call fails the item stays in the queue and is retried on the next
   reconnect.

---

## POST /api/orders (used during sync)

Create a pending order.

### Request Body
```json
{
  "outlet_id": 1,
  "customer_id": 3,
  "notes": "Queued offline order",
  "items": [
    { "product_id": 4, "quantity": 2, "discount_amount": 0 },
    { "product_id": 7, "quantity": 1, "discount_amount": 5000 }
  ]
}
```

### Response `201`
```json
{
  "id": 42,
  "order_number": "TEN-20260330-0042",
  "status": "pending",
  "payment_status": "unpaid",
  "total_amount": "95000.00"
}
```

### Error Cases During Sync
| HTTP | Condition | Mobile Action |
|------|-----------|---------------|
| `422` | Insufficient stock | Mark queue item as `failed`, notify cashier |
| `422` | Product not found | Mark queue item as `failed`, notify cashier |
| `401` | Token expired | Re-authenticate silently then retry |
| `5xx` | Server error | Keep in queue, retry on next sync |

---

## POST /api/orders/{id}/pay (used during sync)

Process payment for the queued order after it is created.

### Request Body
```json
{
  "payment_method": "cash",
  "amount": 100000,
  "reference_number": null
}
```

### Response `200`
```json
{
  "id": 11,
  "payment_method": "cash",
  "amount": "100000.00",
  "change_amount": "5000.00",
  "status": "completed",
  "paid_at": "2026-03-30T10:15:00.000000Z"
}
```

---

## Offline Product Cache

The product list is cached locally so the cashier can add items while offline.
Fetch and cache on every app foreground:

```
GET /api/products?outlet_id={currentOutletId}&per_page=200
```

Cache key: `products_outlet_{id}` — stale after 30 minutes.

Stock numbers displayed while offline show a `~` prefix to indicate they may be
stale (e.g., `~12 pcs`).

---

## Conflict Rules

- **Duplicate prevention**: each offline order is stamped with a
  `client_reference_id` (UUID) stored in `notes` as
  `[ref:<uuid>]` before submission.  The sync worker checks whether an order
  with that reference already exists before retrying:
  ```
  GET /api/orders?search=<uuid>
  ```
  If found, skip creation and proceed to payment (or mark as already paid).

- **Stock conflicts**: if sync fails with `422` insufficient stock the item is
  moved to a `failed` queue.  The cashier is notified via a local notification
  and must review it manually.
