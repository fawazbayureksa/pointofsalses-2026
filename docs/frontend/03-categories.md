# React Native – Categories Feature

Use this prompt after completing `00-setup.md` and `01-authentication.md`.

**API Reference:** [`../api/03-categories.md`](../api/03-categories.md)

---

## Prompt

```
Implement the Categories feature for the POS 2026 React Native (Expo + TypeScript) app.

API Endpoints:
- GET    /api/categories             → paginated list with search
- GET    /api/categories/{id}        → single category with parent + children
- POST   /api/categories             → create category
- PUT    /api/categories/{id}        → update category
- DELETE /api/categories/{id}        → soft-delete category

Special query params:
- ?all=true         → returns all categories (no pagination) for use in dropdowns
- ?roots_only=true  → returns only top-level categories

Tasks:

1. Create `src/api/categories.js` with functions:
   - `getCategories(params?: CategoryQueryParams): Promise<PaginatedResponse<Category> | Category[]>`
   - `getCategory(id: number): Promise<Category>`
   - `createCategory(data: CreateCategoryData): Promise<Category>`
   - `updateCategory(id: number, data: UpdateCategoryData): Promise<Category>`
   - `deleteCategory(id: number): Promise<void>`

2. Create TypeScript interfaces in `src/types/category.js`:
   ```ts
   interface Category {
     id: number;
     name: string;
     slug: string;
     parent_id: number | null;
     sort_order: number;
     is_active: boolean;
     products_count?: number;
     parent?: Category | null;
     children?: Category[];
   }
   interface CreateCategoryData { name: string; slug?: string; parent_id?: number | null; sort_order?: number; is_active?: boolean; }
   type UpdateCategoryData = Partial<CreateCategoryData>;
   ```

3. Create `src/screens/categories/CategoryListScreen.jsx`:
   - FlatList showing category name, product count, parent name (if any)
   - Searchable with debounce
   - FAB to create a new category
   - Swipe-to-delete with confirmation

4. Create `src/screens/categories/CategoryFormScreen.jsx`:
   - Fields: name (required), parent category picker (uses ?all=true&roots_only=true), sort_order, is_active toggle
   - On save: create or update, then pop back

5. Create `src/components/CategoryPicker.jsx`:
   - Reusable picker component used in ProductFormScreen
   - Fetches all categories using `?all=true`
   - Renders as a bottom sheet or modal with a searchable list
   - Returns selected category id and name to parent

6. Create `src/hooks/useCategories.js`:
   - `useCategoryList(params)` → useQuery with staleTime 5 min
   - `useAllCategories()` → useQuery with `?all=true`, staleTime 10 min (used for pickers)
   - `useCreateCategory()` → useMutation
   - `useUpdateCategory()` → useMutation
   - `useDeleteCategory()` → useMutation

Styling requirements:
- Indent child categories visually when listing
- Parent category shown as a muted sub-label
```

---

## Expected Output

- Categories list with search and CRUD
- Reusable `CategoryPicker` component for use in product forms
