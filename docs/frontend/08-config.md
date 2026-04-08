# React Native – Settings / Configuration Feature

Use this prompt after completing `00-setup.md` and `01-authentication.md`.

**API Reference:** [`../api/08-config.md`](../api/08-config.md)

---

## Prompt

```
Implement the Settings / Configuration feature for the POS 2026 React Native (Expo + TypeScript) app.

API Endpoints:
- GET    /api/config        → all config key-value pairs
- GET    /api/config/{key}  → single config value
- PUT    /api/config/{key}  → update a config value { value, type? }

Default configuration keys:
- currency        (string)  e.g. "IDR"
- tax_rate        (integer) e.g. 11
- receipt_footer  (string)  e.g. "Thank you for your purchase!"
- timezone        (string)  e.g. "Asia/Jakarta"
- date_format     (string)  e.g. "d/m/Y"
- low_stock_alert (boolean) true/false

Tasks:

1. Create `src/api/config.js`:
   - `getAllConfig(): Promise<Record<string, unknown>>`
   - `getConfig(key: string): Promise<{ key: string; value: unknown }>`
   - `updateConfig(key: string, value: unknown, type?: 'string' | 'integer' | 'boolean' | 'json'): Promise<{ key: string; value: unknown }>`

2. Create TypeScript interfaces in `src/types/config.js`:
   ```ts
   interface AppConfig {
     currency: string;
     tax_rate: number;
     receipt_footer: string;
     timezone: string;
     date_format: string;
     low_stock_alert: boolean;
   }
   ```

3. Create `src/store/configStore.js` (Zustand):
   - State: `config: AppConfig | null`
   - Actions: `setConfig(config)`, `updateKey(key, value)`
   - Load config at app startup after login

4. Create `src/screens/settings/SettingsScreen.jsx`:
   - Grouped settings list:

   Group 1 – Store Configuration (requires appropriate role):
   - Currency: text input (e.g. IDR, USD)
   - Tax Rate (%): numeric input with % suffix
   - Receipt Footer: multi-line text input

   Group 2 – Display:
   - Timezone: picker (common Asia timezones list)
   - Date Format: picker (dd/MM/yyyy, MM/dd/yyyy, yyyy-MM-dd options)
   - Low Stock Alerts: toggle switch

   Group 3 – Account:
   - View Profile (navigates to ProfileScreen)
   - Change Password (navigates to ChangePasswordScreen)
   - App Version (display only, read from expo Constants)

   Group 4 – Danger Zone:
   - Logout button (red, requires confirmation)

   - Each editable field shows a "Save" button or auto-saves on blur
   - Show success toast on save
   - Show error toast on failure

5. Create `src/hooks/useConfig.js`:
   - `useAppConfig()` → useQuery, fetches all config at startup, staleTime 30 min
   - `useUpdateConfig()` → useMutation that updates configStore on success

6. App startup integration:
   After login (in `src/hooks/useAuth.js` or app initialisation):
   - Call `getAllConfig()` and store in `configStore`
   - Apply `config.timezone` to date-fns globally
   - Pass `config.currency` to `formatCurrency()` throughout the app
   - Pass `config.tax_rate` to order calculations

Styling requirements:
- Use React Native Paper List.Section, List.Item, Switch components
- Dangerous actions (logout) use red/error color
- Show current value as sub-label for each setting
```

---

## Expected Output

- Settings screen with all configuration options grouped
- Changes persisted to the server immediately
- Config values used app-wide (currency, tax rate, timezone)
- Logout flow accessible from settings
