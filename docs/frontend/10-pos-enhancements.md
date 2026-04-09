# React Native – POS Enhancements

Use this prompt after completing `00-setup.md`, `01-authentication.md`,
`05-orders.md`, `04-customers.md`, and `06-outlets.md`.

**API Reference:** [`../api/05-orders.md`](../api/05-orders.md),
[`../api/04-customers.md`](../api/04-customers.md)

---

## Prompt

```
Enhance the POS Checkout screen (from 05-orders.md) for the POS 2026 React
Native (Expo + TypeScript) app.  Focus on speed, real-world cashier UX, and
zero-friction checkout.

Dependencies to install:
- expo-haptics
- expo-notifications
- @gorhom/bottom-sheet

Tasks:

1. Quick-Amount Buttons (PaymentSheet enhancement)
   - Below the amount input, show a row of tappable quick-amount chips.
   - Default denominations (IDR): 10.000 / 20.000 / 50.000 / 100.000 / 200.000 / 500.000
   - Tapping a chip sets `amountTendered = chip value` and triggers `Haptics.selectionAsync()`.
   - If `total > 100.000`, auto-scroll to show chips ≥ total.
   - Chip styling: rounded pill, border, selected = filled blue.

2. Hold-and-Resume Cart (Draft Orders)
   Cashiers sometimes need to pause a cart (customer fetches cash, phone call).

   Create `src/store/draftStore.ts` (Zustand + AsyncStorage persist):
   - State: `drafts: Draft[]` where `Draft = { id: string; label: string; cart: CartItem[]; customerId: number | null; savedAt: string }`
   - Actions:
     - `saveDraft(cart, customerId, label?)`: save current cart as draft
     - `restoreDraft(id)`: load draft into `cartStore`, remove from drafts
     - `deleteDraft(id)`: discard without restoring
   - Max 5 drafts (oldest removed when limit exceeded).

   UI changes to PosScreen:
   - Add a "Hold" icon button (pause icon) in the cart header → calls `saveDraft` then `clearCart`.
   - Add a "Drafts" badge button in the top navigation bar showing draft count.
   - Tapping "Drafts" opens `DraftListSheet` (bottom sheet):
     - Shows each draft: label or "Draft {n}", item count, timestamp.
     - Tap to restore → merges into current cart (or replaces if cart is empty).
     - Swipe-to-delete a single draft.

3. Pinned / Favourite Products Row
   - Above the product grid, show a horizontal scroll row of up to 8 pinned products.
   - Long-press any product card to toggle pinned status.
   - Pinned state stored in `AsyncStorage` key `pinned_products_{outletId}`.
   - Show a gold star ★ overlay on pinned product cards in the grid.
   - Row is hidden if no products are pinned.

4. Receipt Sharing (ReceiptScreen enhancement)
   After a successful payment, the receipt screen gains:

   a. WhatsApp Share:
      - "Share via WhatsApp" button.
      - Builds a text receipt string:
        ```
        [Store Name] – Receipt
        Order: {order_number}
        Date: {formatted date}
        ──────────────────
        {item_name} x{qty}   Rp {subtotal}
        ──────────────────
        Total:   Rp {total}
        Payment: {method}
        Change:  Rp {change}
        ──────────────────
        {receipt_footer from config}
        ```
      - Opens: `Share.share({ message: receiptText })` (cross-platform).

   b. Thermal Print (expo-print):
      - "Print Receipt" button.
      - Generates HTML receipt (58mm layout, store logo if available).
      - Calls `Print.printAsync({ html })`.
      - Button hidden if `expo-print` is not available on device.

   c. Customer Loyalty Notification:
      - If the order had a customer with `is_member === true`, show a green
        toast at the bottom: "✓ +{pts} loyalty points added to {name}".
      - `pts = Math.floor(order.total_amount / 1000)` (1 pt per Rp 1,000).

5. Barcode Scanner Integration (PosScreen enhancement)
   - "Scan" FAB button fixed at bottom-right of product grid panel.
   - Opens `expo-camera` in barcode scan mode (full-screen overlay).
   - On scan: look up product by `barcode` field in local product cache.
   - If found: `addToCart(product)` + `Haptics.notificationAsync(SUCCESS)` + auto-close scanner.
   - If not found: show inline "Product not found" toast, keep scanner open.
   - If `track_stock` and `stock <= 0`: show "Out of stock" toast, do not add.

6. Customer Inline Search (PosScreen enhancement)
   - Replace the bare customer display with a compact search bar in the cart panel.
   - Typing 2+ characters triggers debounced `GET /api/customers?all=true&search=`:
     - Show results in a small dropdown list (max 5 rows).
     - Each row: name, phone, tier badge, loyalty points.
   - Selecting a customer:
     - Shows member card: avatar initial circle (colour by tier), name, tier, current points.
     - Shows `+{pts to earn}` preview below the cart total.
   - "New Customer" shortcut at bottom of dropdown → opens `QuickCreateCustomerSheet`
     (bottom sheet with name + phone fields only; submits `POST /api/customers`).

7. UX Polish
   - All add-to-cart actions trigger `Haptics.impactAsync(LIGHT)`.
   - Cart item row swipe-left to delete (react-native-gesture-handler `Swipeable`).
   - Number inputs (`quantity`, `discount`, `amount`) always open numeric keypad:
     `keyboardType="numeric"` and auto-select all text on focus.
   - "Process Order" button is always visible (sticky bottom) — never hidden by
     keyboard (use `KeyboardAvoidingView` with `behavior="padding"`).
   - Cart item count badge on the cart tab (phone layout only):
     red circle badge with number, hidden when cart is empty.

Styling requirements:
- Quick-amount chips: `height: 38`, `borderRadius: 19`, `paddingHorizontal: 14`
- Selected chip: `backgroundColor: '#2563EB'`, `borderColor: '#2563EB'`, white text
- Unselected chip: `backgroundColor: '#fff'`, `borderColor: '#E5E7EB'`, dark text
- Draft count badge: red circle, positioned top-right of the Drafts button
- Member card: gradient background (blue-50 → indigo-50), 8px border-radius
- Tier colours: regular=#9CA3AF, silver=#64748B, gold=#D97706, platinum=#7C3AED
```

---

## Expected Output

- Quick-amount chips in payment sheet for zero-typing cash checkout
- Hold/resume drafts for multi-customer situations
- Pinned products row for fastest-selling items
- WhatsApp and thermal receipt sharing
- Barcode scanner directly in POS grid
- Inline customer search with loyalty preview
- Swipe-to-delete cart items and haptic feedback throughout
