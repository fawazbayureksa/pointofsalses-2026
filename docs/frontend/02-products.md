# React Native – Products Feature

Use this prompt after completing `00-setup.md` and `01-authentication.md`.

**API Reference:** [`../api/02-products.md`](../api/02-products.md)

---

## Prompt

```
Implement the Products feature for the POS 2026 React Native (Expo + TypeScript) app.

API Endpoints:
- GET    /api/products             → paginated list with search/filter
- GET    /api/products/{id}        → single product
- POST   /api/products             → create product
- PUT    /api/products/{id}        → update product
- DELETE /api/products/{id}        → soft-delete product

Query Parameters:
- search: string (name, SKU, barcode)
- category: string
- outlet_id: integer
- per_page: integer (default 20)

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
     price: string;
     cost_price: string | null;
     unit: string | null;
     is_active: boolean;
     track_stock: boolean;
     image: string | null;
   }
   interface ProductQueryParams { search?: string; category?: string; outlet_id?: number; per_page?: number; page?: number; }
   interface CreateProductData { name: string; sku?: string; price: number; cost_price?: number; track_stock?: boolean; outlet_id?: number; }
   interface UpdateProductData extends Partial<CreateProductData> { is_active?: boolean; }
   ```

3. Create `src/screens/products/ProductListScreen.tsx`:
   - Searchable FlatList using `useInfiniteQuery` (load more on scroll)
   - Each row shows: product image (or placeholder icon), name, SKU, price, stock badge
   - Pull-to-refresh support
   - FAB (floating action button) to create a new product
   - Swipe-to-delete with confirmation dialog
   - Filter bar: category dropdown (uses categories API with ?all=true)

4. Create `src/screens/products/ProductDetailScreen.tsx`:
   - Show all product fields
   - "Edit" button opens `ProductFormScreen` with pre-filled data

5. Create `src/screens/products/ProductFormScreen.tsx`:
   - Form fields: name (required), SKU, barcode, price (required), cost_price, category picker, unit, track_stock toggle, is_active toggle
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
- Stock badge: green if in stock, red if zero/low
- Use React Native Paper's Card, TextInput, Button, FAB components
```

---

## Expected Output

- Products list with infinite scroll and search
- Create / edit / delete product flows
- Barcode scanner integration for product lookup
