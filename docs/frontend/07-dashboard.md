# React Native – Dashboard Feature

Use this prompt after completing `00-setup.md` and `01-authentication.md`.

**API Reference:** [`../api/07-dashboard.md`](../api/07-dashboard.md)

---

## Prompt

```
Implement the Dashboard feature for the POS 2026 React Native (Expo + TypeScript) app.

API Endpoint:
- GET /api/dashboard

Response includes: sales stats, order counts, customer stats, top products,
payment method breakdown, low-stock alerts, and recent orders.

Tasks:

1. Create `src/api/dashboard.ts`:
   ```ts
   import { apiClient } from './client';
   export const getDashboard = () => apiClient.get('/dashboard').then(r => r.data);
   ```

2. Create TypeScript interfaces in `src/types/dashboard.ts`:
   ```ts
   interface DailySales { date: string; label: string; total: number; }
   interface TopProduct { product_id: number; product_name: string; sold: number; revenue: string; }
   interface PaymentStat { payment_method: string; count: number; total: string; }
   interface LowStockProduct { product_id: number; name: string; outlet_name: string; outlet_id: number; stock: number; threshold: number; }
   interface RecentOrder { id: number; order_number: string; status: string; total_amount: number; customer: string | null; outlet: string | null; created_at: string; }
   interface DashboardData {
     sales: { today: number; today_growth: number; monthly: number; monthly_growth: number; avg_order: number; last_7_days: DailySales[] };
     orders: { today: number; completed: number; pending: number };
     outlets: number;
     customers: { total: number; new_this_week: number };
     top_products: TopProduct[];
     payment_method_stats: PaymentStat[];
     low_stock_products: LowStockProduct[];
     recent_orders: RecentOrder[];
   }
   ```

3. Create `src/screens/dashboard/DashboardScreen.tsx`:
   - Use `useQuery` to fetch dashboard data, refetch every 5 minutes
   - Pull-to-refresh to force reload
   - ScrollView layout with the following sections:

   Section 1 – Summary Cards (2-column grid):
   - Today's Sales: formatted currency + growth % badge (green/red arrow)
   - Monthly Revenue: formatted currency + growth % badge
   - Orders Today: count + completed sub-label
   - Avg Order Value: formatted currency

   Section 2 – Sales Chart (last 7 days):
   - Bar chart or line chart using `react-native-chart-kit` or `victory-native`
   - X-axis: day labels (Mon, Tue, ...)
   - Y-axis: currency values (abbreviated, e.g. "1.2M")
   - Show today's bar highlighted

   Section 3 – Recent Orders:
   - Horizontal scrollable list of the 8 most recent orders
   - Each card: order number, customer name (or "Walk-in"), total, status badge
   - Tap to navigate to OrderDetailScreen

   Section 4 – Top Products:
   - Ranked list (1-5) with product name, units sold, revenue
   - Progress bar showing relative revenue

   Section 5 – Payment Methods:
   - Donut/pie chart showing breakdown by method
   - Legend: method name, count, total amount

   Section 6 – Low Stock Alerts:
   - Warning cards for products at/below threshold
   - Shows: product name, outlet, current stock / threshold
   - Red colour when stock = 0, orange when stock > 0 but ≤ threshold
   - Tap to navigate to ProductDetailScreen

4. Create `src/hooks/useDashboard.ts`:
   - `useDashboard()` → useQuery with staleTime 2 min, refetchInterval 5 min

Styling requirements:
- Use React Native Paper Surface/Card components
- Currency formatted with `formatCurrency()` utility
- Growth badges: ↑ green for positive, ↓ red for negative
- Loading skeleton placeholders while data is being fetched (use react-native-skeleton-placeholder or similar)
- Empty state component when there are no orders today
```

---

## Expected Output

- Beautiful dashboard screen with real-time stats
- Sales chart for last 7 days
- Low-stock alerts with navigation to products
- Auto-refresh every 5 minutes + pull-to-refresh
