# React Native – Products Feature

Use this prompt after completing `00-setup.md` and `01-authentication.md`.

**API Reference:** [`../api/02-products.md`](../api/02-products.md)

---

## Prompt

```
Implement the Products feature for the POS 2026 React Native (Expo + TypeScript) app.

All endpoints require `Authorization: Bearer <token>` (Sanctum).

API Endpoints:
- GET    /api/products             → paginated list with search/filter (only active products)
- GET    /api/products/{id}        → single product
- POST   /api/products             → create product
- PUT    /api/products/{id}        → update product
- DELETE /api/products/{id}        → soft-delete product (204 No Content)

Query Parameters (GET /api/products):
- search: string        — matches name, SKU, or barcode
- category_id: integer  — filter by category ID
- outlet_id: integer    — filter to products assigned to that outlet; also populates `stock` field per item
- per_page: integer     — default 20
- page: integer

Notes:
- `stock` in the list response is only populated when `outlet_id` is provided; otherwise null.
- The index endpoint only returns `is_active = true` products (use the admin web panel to manage inactive products).

Tasks:

1. Create `src/api/products.ts` with functions:
   - `getProducts(params?: ProductQueryParams): Promise<PaginatedResponse<Product>>`
   - `getProduct(id: number): Promise<Product>`
   - `createProduct(data: CreateProductData): Promise<Product>`
   - `updateProduct(id: number, data: UpdateProductData): Promise<Product>`
   - `deleteProduct(id: number): Promise<void>`

2. Create TypeScript interfaces in `src/types/product.ts`:
   ```ts
   interface Product {
     id: number;
     name: string;
     sku: string | null;
     barcode: string | null;
     description: string | null;
     category_id: number | null;
     category: string | null;      // category name (resolved on server)
     price: string;
     cost_price: string | null;
     unit: string | null;
     is_active: boolean;
     track_stock: boolean;
     image: string | null;         // full URL or null
     stock: number | null;         // only populated when outlet_id is passed to list endpoint
   }
   interface ProductQueryParams {
     search?: string;
     category_id?: number;
     outlet_id?: number;
     per_page?: number;
     page?: number;
   }
   // POST /api/products – accepted fields
   interface CreateProductData {
     name: string;
     sku?: string;                  // must be unique
     barcode?: string;
     description?: string;
     category?: string;             // category name string (not ID)
     price: number;
     cost_price?: number;
     stock?: number;                // initial stock quantity
     low_stock_threshold?: number;
     unit?: string;
     outlet_id?: number;            // outlet to assign initial stock to
     track_stock?: boolean;         // default true
   }
   // PUT /api/products/{id} – accepted fields (all optional / partial)
   interface UpdateProductData {
     name?: string;
     price?: number;
     cost_price?: number;
     stock?: number;
     low_stock_threshold?: number;
     category?: string;             // category name string
     is_active?: boolean;
   }
   ```

3. Create `src/screens/products/ProductListScreen.tsx`:
   - Searchable FlatList using `useInfiniteQuery` (load more on scroll)
   - Each row shows: product image (or placeholder icon), name, SKU, price, stock badge
   - Pull-to-refresh support
   - FAB (floating action button) to create a new product
   - Swipe-to-delete with confirmation dialog
   - Filter bar: category dropdown (uses categories API)

4. Create `src/screens/products/ProductDetailScreen.tsx`:
   - Show all product fields including `track_stock`, `unit`, `cost_price`, `description`
   - "Edit" button opens `ProductFormScreen` with pre-filled data

5. Create `src/screens/products/ProductFormScreen.tsx`:
   - **Create fields:** name (required), SKU, barcode, description, price (required),
     cost_price, category (free-text or picker), unit, stock (initial), low_stock_threshold,
     outlet_id (for initial stock), track_stock toggle, is_active toggle
   - **Update fields:** name, price, cost_price, stock, low_stock_threshold, category, is_active
     (SKU, barcode, track_stock, unit are not accepted by the update endpoint)
   - On save: call `createProduct` or `updateProduct`, then pop back to list
   - Show validation errors inline below each field (from API 422 response)
   - Barcode scanner button using `expo-barcode-scanner` that fills the barcode field

6. Create `src/hooks/useProducts.ts`:
   - `useProductList(params)` → useInfiniteQuery
   - `useProduct(id)` → useQuery
   - `useCreateProduct()` → useMutation with cache invalidation
   - `useUpdateProduct()` → useMutation with cache invalidation
   - `useDeleteProduct()` → useMutation with cache invalidation

Styling requirements:
- Price formatted using `formatCurrency()` utility
- Stock badge: green if in stock, red if zero/low, grey if null (no outlet filter applied)
- Use React Native Paper's Card, TextInput, Button, FAB components
```

---

## Expected Output

- Products list with infinite scroll and search
- Create / edit / delete product flows
- Barcode scanner integration for product lookup
