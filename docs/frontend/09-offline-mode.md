# React Native – Offline Mode

Use this prompt after completing `00-setup.md`, `01-authentication.md`,
`05-orders.md`, and `06-outlets.md`.

**API Reference:** [`../api/09-offline-queue.md`](../api/09-offline-queue.md)

---

## Prompt

```
Implement Offline Mode for the POS 2026 React Native (Expo + TypeScript) app.

The app must remain usable when there is no internet connection.  Cashiers must
be able to complete sales offline; orders are synced to the server automatically
when connectivity is restored.

Dependencies to install:
- @react-native-community/netinfo
- expo-task-manager
- expo-background-fetch
- uuid  (+ @types/uuid)

Tasks:

1. Create `src/utils/offlineStorage.ts`:
   - Thin wrapper around AsyncStorage for typed reads/writes.
   - `saveQueue(queue: PendingOrder[]): Promise<void>`
   - `loadQueue(): Promise<PendingOrder[]>`
   - `saveProductCache(outletId: number, products: Product[]): Promise<void>`
   - `loadProductCache(outletId: number): Promise<{ products: Product[]; cachedAt: string } | null>`

2. Create `src/types/offline.ts`:
   ```ts
   type OfflineOrderStatus = 'queued' | 'syncing' | 'synced' | 'failed';

   interface PendingOrder {
     clientRef: string;          // uuid – used for dedup
     outletId: number;
     customerId: number | null;
     notes: string;
     items: Array<{
       product_id: number;
       quantity: number;
       discount_amount: number;
     }>;
     payment: {
       payment_method: 'cash' | 'card' | 'qris' | 'transfer';
       amount: number;
       reference_number?: string;
     };
     createdAt: string;          // ISO timestamp from device
     status: OfflineOrderStatus;
     errorMessage?: string;
   }
   ```

3. Create `src/store/offlineQueueStore.ts` (Zustand + AsyncStorage persist):
   State:
   - `queue: PendingOrder[]`
   - `isSyncing: boolean`
   - `lastSyncAt: string | null`

   Actions:
   - `enqueue(order: PendingOrder): void` – add to queue, persist to AsyncStorage
   - `markSyncing(clientRef: string): void`
   - `markSynced(clientRef: string): void`     – remove from queue
   - `markFailed(clientRef: string, error: string): void`
   - `retryFailed(): void`                      – reset failed → queued
   - `clearSynced(): void`

   Selectors:
   - `pendingCount`: queue items with status 'queued'
   - `failedCount`: queue items with status 'failed'

4. Create `src/hooks/useNetworkSync.ts`:
   - Subscribes to `NetInfo.addEventListener`
   - When `isConnected` transitions false → true, calls `syncQueue()`
   - `syncQueue()` processes the queue sequentially:
     a. For each item with status 'queued', call `markSyncing`
     b. Check dedup: `GET /api/orders?search={clientRef}` – if found, mark synced
     c. Call `POST /api/orders` with items + notes `[ref:{clientRef}] {notes}`
     d. Call `POST /api/orders/{id}/pay` with payment data
     e. On success: `markSynced`
     f. On 422 (stock/product error): `markFailed` with error message
     g. On 401: refresh token, retry once
     h. On 5xx or network error: keep as 'queued', stop processing (retry next reconnect)
   - Export: `useSyncStatus(): { pendingCount, failedCount, isSyncing, lastSyncAt }`

5. Create `src/components/OfflineBanner.tsx`:
   - Fixed banner at top of screen when offline:
     `"Offline — {n} order(s) queued for sync"`
   - Yellow background when offline with queued orders.
   - Red background when there are failed orders – tap to open `OfflineQueueScreen`.
   - Hidden when online and queue is empty.

6. Create `src/screens/pos/OfflineQueueScreen.tsx`:
   - FlatList of all queued/failed orders.
   - Each row: time created, item count, total, status badge.
   - Failed orders show the error message and a "Retry" button.
   - Header button "Retry All Failed".
   - Pull-to-refresh triggers `syncQueue()` if online.

7. Modify `src/screens/pos/PaymentSheet.tsx` (from 05-orders.md):
   - Import `useNetInfo` from `@react-native-community/netinfo`.
   - If `isConnected === false`:
     - Instead of calling the API, call `offlineQueueStore.enqueue(order)`.
     - Navigate to a lightweight `OfflineReceiptScreen` (shows "Order saved for
       sync" with order details).
   - If `isConnected === true`: proceed with normal API flow.

8. Modify `src/screens/pos/PosScreen.tsx` (from 05-orders.md):
   - When offline, show product stock from local cache with `~` prefix.
   - Cache is refreshed on every app foreground via `AppState` listener:
     `GET /api/products?outlet_id={id}&per_page=200`
   - Cache is stored per outlet with timestamp; stale after 30 minutes.

9. Register background sync task in `App.tsx`:
   ```ts
   import * as BackgroundFetch from 'expo-background-fetch';
   import * as TaskManager from 'expo-task-manager';

   const SYNC_TASK = 'OFFLINE_SYNC';

   TaskManager.defineTask(SYNC_TASK, async () => {
     const netInfo = await NetInfo.fetch();
     if (netInfo.isConnected) {
       await syncQueue();
     }
     return BackgroundFetch.BackgroundFetchResult.NewData;
   });

   // Register in App.tsx after login:
   await BackgroundFetch.registerTaskAsync(SYNC_TASK, {
     minimumInterval: 60,      // seconds
     stopOnTerminate: false,
     startOnBoot: true,
   });
   ```

Styling requirements:
- Offline banner: full-width, 40px height, centered text, `zIndex: 100`
- Status badges: queued = blue, syncing = orange (animated), synced = green, failed = red
- Failed row background: `rgba(239, 68, 68, 0.05)` (light red tint)
- Stale stock indicator: grey italic `~12 pcs`
```

---

## Expected Output

- App fully functional without internet
- Completed sales queued and auto-synced on reconnect
- Offline banner shows sync status at all times
- Failed orders visible and retryable by cashier
- Product catalogue cached per outlet for offline browsing
