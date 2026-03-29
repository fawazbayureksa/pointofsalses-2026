# React Native – Customers Feature

Use this prompt after completing `00-setup.md` and `01-authentication.md`.

**API Reference:** [`../api/04-customers.md`](../api/04-customers.md)

---

## Prompt

```
Implement the Customers feature for the POS 2026 React Native (Expo + TypeScript) app.

API Endpoints:
- GET    /api/customers             → paginated list with search
- GET    /api/customers/{id}        → single customer with order history
- POST   /api/customers             → create customer
- PUT    /api/customers/{id}        → update customer
- DELETE /api/customers/{id}        → soft-delete customer

Special query params:
- ?all=true&search=<query>  → unbounded search results for quick lookup during checkout

Tasks:

1. Create `src/api/customers.js` with functions:
   - `getCustomers(params?: CustomerQueryParams): Promise<PaginatedResponse<Customer> | Customer[]>`
   - `getCustomer(id: number): Promise<CustomerDetail>`
   - `createCustomer(data: CreateCustomerData): Promise<Customer>`
   - `updateCustomer(id: number, data: UpdateCustomerData): Promise<Customer>`
   - `deleteCustomer(id: number): Promise<void>`

2. Create TypeScript interfaces in `src/types/customer.js`:
   ```ts
   interface Customer {
     id: number;
     name: string;
     email: string | null;
     phone: string | null;
     address: string | null;
     loyalty_points: number;
     is_active: boolean;
   }
   interface CustomerDetail extends Customer {
     orders: Array<{ id: number; order_number: string; status: string; total_amount: string }>;
   }
   interface CreateCustomerData { name: string; email?: string; phone?: string; address?: string; }
   type UpdateCustomerData = Partial<CreateCustomerData>;
   ```

3. Create `src/screens/customers/CustomerListScreen.jsx`:
   - Searchable FlatList using `useInfiniteQuery`
   - Each row: avatar (initials), name, phone, loyalty points badge
   - Pull-to-refresh
   - FAB to create a new customer
   - Tap a row to open CustomerDetailScreen

4. Create `src/screens/customers/CustomerDetailScreen.jsx`:
   - Show customer info and order history
   - "Edit" button opens the form

5. Create `src/screens/customers/CustomerFormScreen.jsx`:
   - Fields: name (required), email, phone, address
   - Inline validation errors from API

6. Create `src/components/CustomerSearchModal.jsx`:
   - Reusable modal/bottom sheet component used during checkout
   - Searchable text input with debounce (calls API with `?all=true&search=...`)
   - Returns selected customer to parent
   - "Create New" shortcut opens CustomerFormScreen inline (optional)

7. Create `src/hooks/useCustomers.js`:
   - `useCustomerList(params)` → useInfiniteQuery
   - `useCustomer(id)` → useQuery
   - `useCreateCustomer()` → useMutation
   - `useUpdateCustomer()` → useMutation
   - `useDeleteCustomer()` → useMutation

Styling requirements:
- Loyalty points shown as a gold star badge
- Phone number formatted for display
- Avatar generated from first 2 initials of customer name
```

---

## Expected Output

- Customers list with infinite scroll and search
- Customer detail with order history
- Create/edit/delete customer flows
- Reusable `CustomerSearchModal` for use in POS checkout
