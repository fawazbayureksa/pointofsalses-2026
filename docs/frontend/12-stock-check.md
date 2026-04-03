# React Native – Stock Check (Read-Only)

Use this prompt after completing `00-setup.md`, `01-authentication.md`,
`02-products.md`, and `06-outlets.md`.

**API Reference:** [`../api/07-products.md`](../api/07-products.md)
(uses existing product endpoints — no new backend changes needed)

---

## Prompt

```
Implement a Stock Check (read-only) screen for the POS 2026 React Native
(Expo + TypeScript) app.

Cashiers can browse and search product stock levels for their outlet.
No editing or inventory adjustment is performed here.

API endpoints used:
- GET /api/products?outlet_id={id}&search={q}&per_page=30&page={n}

Dependencies to install:
- expo-camera

Tasks:

1. Extend `src/api/products.ts` (if not already present):
   - `getProducts(params: { outlet_id?: number; search?: string; page?: number; per_page?: number }): Promise<PaginatedResponse<Product>>`

2. Create `src/hooks/useStockCheck.ts`:
   - `useStockCheck(outletId: number, search: string)`
     – uses `useInfiniteQuery` from `@tanstack/react-query`
     – `queryKey: ['stock-check', outletId, search]`
     – `getNextPageParam`: if `next_page_url` is not null, return next page number
     – `staleTime: 2 * 60 * 1000` (2 minutes; use offline cache after)

3. Create `src/utils/stockLevel.ts`:
   ```ts
   type StockLevel = 'good' | 'low' | 'out';

   // Thresholds (adjust as needed)
   const LOW_THRESHOLD = 5;

   function getStockLevel(stock: number | null): StockLevel {
     if (stock === null) return 'good';    // no tracking → always green
     if (stock <= 0) return 'out';
     if (stock <= LOW_THRESHOLD) return 'low';
     return 'good';
   }

   const STOCK_COLORS: Record<StockLevel, string> = {
     good: '#16A34A', // green-600
     low:  '#D97706', // amber-600
     out:  '#DC2626', // red-600
   };

   const STOCK_BG: Record<StockLevel, string> = {
     good: '#DCFCE7', // green-100
     low:  '#FEF3C7', // amber-100
     out:  '#FEE2E2', // red-100
   };
   ```

4. Create `src/screens/stock/StockCheckScreen.tsx`:
   Layout:

   a. Top bar:
      - Search input (auto-focus) — debounced 350 ms → updates query
      - Barcode scan button (camera icon) → toggles scan mode

   b. Scan mode overlay (when active):
      - Full-screen `expo-camera` preview with `onBarcodeScanned`
      - On scan: set search = scanned barcode value, close camera
      - Close button (X) top-right

   c. FlatList of products:
      - Calls `useStockCheck(outletId, debouncedSearch)`
      - `onEndReached` → `fetchNextPage`
      - If `~` prefix should appear for offline cache: add it to the product name
        display if the data was served from the offline cache rather than the network.
        (Use react-query `dataUpdatedAt` + `isStale` flag to decide.)
      - Each row:

        ```
        [Product Image 48px] | Product Name          | [STOCK BADGE]
                               Category · SKU/barcode |
        ```

      - Stock badge: pill shape, background STOCK_BG[level], text STOCK_COLORS[level]
        - `good`: "{stock} in stock"
        - `low`:  "Low: {stock}"
        - `out`:  "Out of stock" (stock = 0) or "N/A" (null / not tracked)

   d. Empty state:
      - If search has text but results empty: "No products found for '{search}'"
      - If no products at all: "No products for this outlet"

   e. Loading state:
      - Skeleton rows (3–5) while first page loads

5. Offline cache note:
   - Product data from `useStockCheck` is automatically cached by `@tanstack/query`.
   - When offline (`useNetworkStatus()` → `isConnected === false`):
     - Show `<OfflineBanner />` (from `09-offline-mode.md`)
     - Serve from cache; add note "(cached)" below the outlet name in the header

6. Navigation:
   - Accessible from the main tab bar as "Stock" tab, or from the Profile tab
     under "Tools → Stock Check"
   - Tab icon: `package` or `archive` icon (FontAwesome / MaterialIcons)

Styling requirements:
- Search bar: white, 8px radius, light border, `#F9FAFB` background on focus
- Row separator: thin `#F3F4F6` line
- Stock badge: 6px border-radius, 5 px horizontal padding, `fontSize: 12`, `fontWeight: '600'`
- Product image: 48×48, 6px radius, grey placeholder if null
- Header: outlet name sub-title below screen title
```

---

## Expected Output

- Fast, searchable product stock browser
- Traffic-light color badges for stock levels (green / amber / red)
- Barcode scan for quick product lookup
- Graceful offline mode using cached data
