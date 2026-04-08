# React Native – Outlets Feature

Use this prompt after completing `00-setup.md` and `01-authentication.md`.

**API Reference:** [`../api/06-outlets.md`](../api/06-outlets.md)

---

## Prompt

```
Implement the Outlets feature for the POS 2026 React Native (Expo + TypeScript) app.

Outlets are read-only from mobile. The active outlet determines which stock is shown
and is required when creating orders.

API Endpoints:
- GET    /api/outlets       → list active outlets
- GET    /api/outlets/{id}  → single outlet with assigned users

Tasks:

1. Create `src/api/outlets.js` with functions:
   - `getOutlets(params?: { search?: string; all?: boolean }): Promise<PaginatedResponse<Outlet> | Outlet[]>`
   - `getOutlet(id: number): Promise<OutletDetail>`

2. Create TypeScript interfaces in `src/types/outlet.js`:
   ```ts
   interface Outlet {
     id: number;
     name: string;
     code: string;
     phone: string | null;
     email: string | null;
     address: string | null;
     city: string | null;
     is_active: boolean;
   }
   interface OutletUser {
     id: number; name: string; email: string;
     pivot: { is_default: boolean };
   }
   interface OutletDetail extends Outlet {
     users: OutletUser[];
   }
   ```

3. Create `src/store/outletStore.js` (Zustand):
   - State: `currentOutlet: Outlet | null`, `outlets: Outlet[]`
   - Actions: `setCurrentOutlet(outlet)`, `setOutlets(outlets)`
   - Persist `currentOutlet` to AsyncStorage

4. Create `src/screens/outlets/OutletSelectorScreen.jsx`:
   - Shown at startup (after login) if no outlet is selected yet
   - FlatList of available outlets
   - Tap to select → saves to outletStore, then navigates to AppTabs
   - Show currently active outlet with a checkmark

5. Create `src/components/OutletSwitcher.jsx`:
   - A compact header component showing the current outlet name
   - Tapping it opens a bottom sheet with the outlet list to switch

6. App startup flow:
   - After login → call `GET /api/outlets?all=true`
   - If only 1 outlet: auto-select it
   - If multiple outlets: show `OutletSelectorScreen`
   - Store the selected outlet in `outletStore`
   - Pass `currentOutlet.id` as `outlet_id` when creating orders

7. Create `src/hooks/useOutlets.js`:
   - `useAllOutlets()` → useQuery with `?all=true`, staleTime 30 min
   - `useOutlet(id)` → useQuery
```

---

## Expected Output

- Outlet selector shown after login for multi-outlet setups
- Current outlet persisted across app restarts
- Outlet switcher header component for quick switching
- `outletStore.currentOutlet.id` ready to use in order creation
