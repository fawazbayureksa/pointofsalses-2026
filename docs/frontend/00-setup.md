# React Native – Project Setup & API Client

Use this prompt to scaffold the React Native project and set up the shared API client.

---

## Prompt

```
Create a new React Native (Expo) project for a Point of Sales mobile app called "POS 2026" with the following setup:

Tech Stack:
- Expo SDK (latest)
- TypeScript
- React Navigation v6 (Stack + Bottom Tabs)
- Zustand for global state management
- Axios for HTTP requests
- expo-secure-store for secure token storage
- React Native Paper (Material Design component library)
- React Query (TanStack Query) for server state

Folder Structure:
src/
  api/          # Axios instance and API functions per feature
  components/   # Reusable UI components
  navigation/   # Stack and tab navigators
  screens/      # One folder per feature
  store/        # Zustand stores
  types/        # TypeScript interfaces matching API responses
  utils/        # Helpers (currency formatter, date formatter)

Tasks:
1. Create the Expo project with javascript template
2. Install all dependencies listed above
3. Create `src/api/client.js`:
   - Axios instance with `baseURL` from `EXPO_PUBLIC_API_URL` env variable
   - Request interceptor that reads the Bearer token from expo-secure-store and injects it into `Authorization` header
   - Response interceptor that:
     - On 401 → clears the stored token and navigates to the Login screen
     - On any error → returns a standardised error object { message, errors }
4. Create `src/utils/currency.js` with a `formatCurrency(amount, currency = 'IDR')` helper
5. Create `src/utils/date.js` with a `formatDate(isoString, format = 'dd/MM/yyyy HH:mm')` helper using date-fns
6. Create `src/store/authStore.js` using Zustand:
   - State: `token`, `user` (id, name, email, roles, permissions)
   - Actions: `setAuth(token, user)`, `clearAuth()`
   - Persist token to expo-secure-store on setAuth and remove on clearAuth
7. Create `src/navigation/RootNavigator.jsx`:
   - If no token → show `AuthStack` (Login screen)
   - If token → show `AppTabs` (Dashboard, POS, Orders, Settings)
8. Create a basic placeholder screen for each tab

Environment variable setup:
- Create `.env` with `EXPO_PUBLIC_API_URL=http://localhost:8000/api`
- Create `.env.production` with the production API URL placeholder
```

---

## Expected Output

After running this prompt, you should have:
- Working Expo project that compiles with `npx expo start`
- Authenticated navigation flow (Login → App tabs)
- Shared Axios client that automatically attaches the Bearer token
- Currency and date formatters ready to use across the app
