# React Native – POS Checkout & Orders Feature

Use this prompt after completing `00-setup.md`, `01-authentication.md`, `02-products.md`, `04-customers.md`, and `06-outlets.md`.

**API Reference:** [`../api/05-orders.md`](../api/05-orders.md)

---

## Prompt

```
Implement the POS Checkout and Orders feature for the POS 2026 React Native (Expo + TypeScript) app.

API Endpoints:
- GET    /api/orders                 → list orders with filters
- GET    /api/orders/{id}            → order detail
- POST   /api/orders                 → create order (returns pending order)
- POST   /api/orders/{id}/pay        → process payment
- POST   /api/orders/{id}/cancel     → cancel order

Tasks:

1. Create `src/api/orders.ts` with functions:
   - `getOrders(params?: OrderQueryParams): Promise<PaginatedResponse<Order>>`
   - `getOrder(id: number): Promise<OrderDetail>`
   - `createOrder(data: CreateOrderData): Promise<Order>`
   - `payOrder(id: number, data: PayOrderData): Promise<Payment>`
   - `cancelOrder(id: number, reason?: string): Promise<Order>`

2. Create TypeScript interfaces in `src/types/order.ts`:
   ```ts
   interface OrderItem {
     id: number; product_id: number; product_name: string; product_sku: string;
     unit_price: string; quantity: number; discount_amount: string; subtotal: string;
   }
   interface Payment {
     id: number; payment_method: string; amount: string; change_amount: string;
     status: string; reference_number: string | null; paid_at: string;
   }
   interface Order {
     id: number; order_number: string; status: 'pending' | 'completed' | 'cancelled';
     payment_status: 'unpaid' | 'paid'; subtotal: string; tax_amount: string;
     discount_amount: string; total_amount: string; notes: string | null;
     cashier: { id: number; name: string } | null;
     customer: { id: number; name: string } | null;
     outlet: { id: number; name: string } | null;
     created_at: string;
   }
   interface OrderDetail extends Order { items: OrderItem[]; payments: Payment[]; }
   interface CreateOrderData {
     outlet_id: number; customer_id?: number | null; notes?: string;
     items: Array<{ product_id: number; quantity: number; discount_amount?: number }>;
   }
   interface PayOrderData { payment_method: 'cash' | 'card' | 'qris' | 'transfer'; amount: number; reference_number?: string; }
   ```

3. Create `src/store/cartStore.ts` (Zustand):
   State:
   - `items`: CartItem[] (product_id, name, price, quantity, discount_amount)
   - `outletId`: number | null
   - `customerId`: number | null
   - `notes`: string
   Actions:
   - `addItem(product)`: add or increment quantity
   - `removeItem(productId)`: remove item
   - `updateQuantity(productId, qty)`: update quantity
   - `updateDiscount(productId, discount)`: apply per-item discount
   - `setCustomer(id)`: set customer
   - `setOutlet(id)`: set outlet
   - `setNotes(notes)`: set notes
   - `clearCart()`: reset state
   Computed (derived):
   - `subtotal`: sum of (price * qty - discount) for all items
   - `itemCount`: total quantity

4. Create `src/screens/pos/PosScreen.tsx` (main POS screen):
   - Split view:
     - LEFT / TOP: Product grid with search and category filter tabs
       - Grid of product cards (image, name, price)
       - Tap to add to cart
       - Barcode scanner button
     - RIGHT / BOTTOM: Cart panel
       - List of cart items with quantity +/- buttons and delete
       - Customer selector (opens CustomerSearchModal)
       - Notes input
       - Subtotal, tax, total display
       - "Place Order" button → calls `createOrder`, then opens PaymentSheet
   - On tablet/large screen: side-by-side layout; on phone: tab/swipe between product grid and cart

5. Create `src/screens/pos/PaymentSheet.tsx` (bottom sheet):
   - Shows order total
   - Payment method selector: Cash, Card, QRIS, Transfer (chip buttons)
   - Amount input (for cash: auto-filled with total, shows change calculation)
   - Reference number input (for card/qris/transfer)
   - "Confirm Payment" button → calls `payOrder`
   - On success: shows receipt preview, then clears cart

6. Create `src/screens/pos/ReceiptScreen.tsx`:
   - Shows order details, items, payment method, change
   - "Print Receipt" button (integration with react-native-print or expo-print)
   - "New Sale" button → clears cart and returns to PosScreen
   - "View Order" button → navigates to OrderDetailScreen

7. Create `src/screens/orders/OrderListScreen.tsx`:
   - Searchable list with status filter tabs (All, Pending, Completed, Cancelled)
   - Date range picker for filtering
   - Each row: order number, customer name, total, status badge, time
   - Pull-to-refresh + infinite scroll
   - Tap to view OrderDetailScreen

8. Create `src/screens/orders/OrderDetailScreen.tsx`:
   - All order details, items, payment info
   - If `status === 'pending'`: show "Pay Now" button (opens PaymentSheet) and "Cancel" button
   - Cancel requires a reason text input

9. Create `src/hooks/useOrders.ts`:
   - `useOrderList(params)` → useInfiniteQuery
   - `useOrder(id)` → useQuery
   - `useCreateOrder()` → useMutation
   - `usePayOrder()` → useMutation with invalidation of order detail + list
   - `useCancelOrder()` → useMutation

Styling requirements:
- Payment method buttons as icon + label chips
- Status badges: pending = orange, completed = green, cancelled = grey
- Cash change shown prominently in green when cash > total
- Cart item count badge on the cart tab/button
```

---

## Expected Output

- Fully functional POS checkout screen
- Cart management with quantities and discounts
- Payment processing (cash, card, QRIS, transfer)
- Receipt preview and print
- Order history with status filtering
- Order detail with inline pay/cancel actions
