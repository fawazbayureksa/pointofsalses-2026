# React Native – Shift Summary & End-of-Day Report

Use this prompt after completing `00-setup.md`, `01-authentication.md`,
`05-orders.md`, and `06-outlets.md`.

**API Reference:** [`../api/10-shift-summary.md`](../api/10-shift-summary.md)

---

## Prompt

```
Implement the Shift Summary / End-of-Day Report feature for the POS 2026
React Native (Expo + TypeScript) app.

The shift summary is read-only.  All data is derived from existing API endpoints.
No new backend endpoints are required.

API Endpoints used:
- GET /api/orders?outlet_id={id}&date_from={today}&date_to={today}&status=completed&per_page=200
- GET /api/orders?outlet_id={id}&status=pending&date_from={today}&date_to={today}
- GET /api/dashboard   (optional – for pre-aggregated totals)

Dependencies to install:
- expo-print
- expo-sharing

Tasks:

1. Create `src/api/shiftSummary.ts`:
   - `getShiftOrders(outletId: number, date: string): Promise<Order[]>`
     – fetches all completed orders for the outlet on the given date
     (loops pages until `next_page_url` is null).
   - `getPendingOrders(outletId: number): Promise<Order[]>`
     – fetches pending orders for today.

2. Create `src/types/shiftSummary.ts`:
   ```ts
   interface PaymentBreakdown {
     method: 'cash' | 'card' | 'qris' | 'transfer';
     count: number;
     total: number;
   }

   interface ShiftSummary {
     date: string;
     outletName: string;
     cashierName: string;
     totalSales: number;
     transactionCount: number;
     avgOrderValue: number;
     paymentBreakdown: PaymentBreakdown[];
     pendingCount: number;
   }
   ```

3. Create `src/hooks/useShiftSummary.ts`:
   - `useShiftSummary(date?: string)` → useQuery
     - fetches orders, computes `ShiftSummary` client-side:
       - `totalSales = sum(total_amount)`
       - `transactionCount = orders.length`
       - `avgOrderValue = totalSales / transactionCount`
       - `paymentBreakdown`: group orders by `payments[0].payment_method`
         (use `GET /api/orders/{id}` for orders that have payment details, or
         accept that breakdown is best-effort without per-order payment detail).
         Simpler alternative: use only payment_method from `GET /api/dashboard`.
     - `staleTime: 0` (always fresh — cashier opens this at end of shift).

4. Create `src/screens/reports/ShiftSummaryScreen.tsx`:
   Layout (single scroll view):

   a. Header bar:
      - Title: "Shift Summary — {formatted date}"
      - Sub-title: outlet name
      - Right header button: "Export" (opens share sheet)

   b. Summary cards row (horizontal scroll):
      - 💰 Total Sales: `Rp {totalSales}` (large, bold, green)
      - 🧾 Transactions: `{count}`
      - 📊 Avg Order: `Rp {avgOrderValue}`
      - ⏳ Pending: `{pendingCount}` (orange if > 0)

   c. Payment Method Breakdown section:
      - Table rows: method icon + label | count | total amount
      - Methods: Cash 💵 / Card 💳 / QRIS 📱 / Transfer 🏦
      - Show only methods with count > 0.
      - Bottom row (bold): TOTAL | {totalTransactions} | {totalSales}

   d. "Cash to Hand Over" box (only if cash > 0):
      - Highlighted box: "Cash collected: Rp {cashTotal}"
      - Sub-text: "Hand this amount to the manager before closing."

   e. Pending Orders warning (only if pendingCount > 0):
      - Orange warning card:
        "{pendingCount} order(s) still pending payment."
      - "View Pending" button → navigates to OrderListScreen filtered to pending.

   f. Recent Transactions list (last 10):
      - FlatList of today's completed orders.
      - Each row: order number | customer name or "Walk-in" | total | time.
      - Tap → navigates to OrderDetailScreen.

5. Create Z-Report (PDF export) in `src/utils/zReport.ts`:
   - `generateZReportHtml(summary: ShiftSummary, orders: Order[]): string`
   - Outputs a simple HTML string suitable for `expo-print`.
   - Content: store name, date, cashier, summary table, payment breakdown, top 10 orders.
   - `printZReport(summary, orders)`: calls `Print.printAsync({ html })`.
   - `shareZReport(summary, orders)`: generates HTML → PDF via `Print.printToFileAsync`
     → opens share sheet via `Sharing.shareAsync(fileUri)`.

6. Entry Point – access from two places:
   a. Orders tab header: "Today" icon button → `ShiftSummaryScreen` for today.
   b. Profile/account tab: "Shift Summary" row → opens date picker first,
      then navigates to `ShiftSummaryScreen` with the selected date.

7. Create `src/hooks/useShiftSummary.ts`:
   - `useShiftSummary(outletId, date)` → useQuery
   - `useExportReport()` → plain async function (not a mutation), calls
     `printZReport` or `shareZReport`.

Styling requirements:
- Summary cards: white background, 12px border-radius, shadow, min-width 120px
- Total Sales value: `fontSize: 24`, `fontWeight: '700'`, `color: '#16A34A'`
- Payment method row: alternating background `#F9FAFB` / `#fff`
- Cash hand-over box: `backgroundColor: '#EFF6FF'`, blue border, 12px radius
- Pending warning card: `backgroundColor: '#FFF7ED'`, orange border
- Export button: outlined style, document icon
```

---

## Expected Output

- Clean end-of-day summary screen accessible from the Orders tab
- Payment method breakdown with cash hand-over guidance
- Warning for any unpaid pending orders
- Exportable Z-report as PDF via print or share
