# React Native – Authentication Feature

Use this prompt after completing `00-setup.md`.

**API Reference:** [`../api/01-authentication.md`](../api/01-authentication.md)

---

## Prompt

```
Implement the Authentication feature for the POS 2026 React Native (Expo + TypeScript) app.

API Endpoints:
- POST   /api/auth/login       → returns { token, user }
- POST   /api/auth/logout      → revokes token
- GET    /api/auth/me          → returns { id, name, email, roles, permissions }
- PUT    /api/auth/profile     → update name/email/phone
- PUT    /api/auth/password    → change password { current_password, password, password_confirmation }

Tasks:

1. Create `src/api/auth.js` with functions:
   - `login(email: string, password: string): Promise<{ token: string; user: User }>`
   - `logout(): Promise<void>`
   - `getMe(): Promise<User>`
   - `updateProfile(data: Partial<ProfileData>): Promise<User>`
   - `changePassword(data: ChangePasswordData): Promise<void>`

2. Create TypeScript interfaces in `src/types/auth.js`:
   ```ts
   interface User {
     id: number;
     name: string;
     email: string;
     phone?: string;
     roles: string[];
     permissions: string[];
   }
   interface ProfileData { name: string; email: string; phone: string; }
   interface ChangePasswordData { current_password: string; password: string; password_confirmation: string; }
   ```

3. Create `src/screens/auth/LoginScreen.jsx`:
   - Email and password text inputs
   - "Login" button that calls `login()`, saves token via `authStore.setAuth()`, then navigates to AppTabs
   - Show inline validation error when API returns 422 (display errors.email message)
   - Show loading spinner on button while request is in progress
   - Handle network errors gracefully with a toast/snackbar

4. Create `src/screens/profile/ProfileScreen.jsx`:
   - Display current user info (name, email, phone, role badge)
   - "Edit Profile" button opens a modal or navigates to an edit form
   - "Change Password" button opens a modal with current/new/confirm password fields
   - "Logout" button calls `logout()`, clears authStore, navigates to LoginScreen
   - Use React Query `useQuery` to call `getMe()` on mount

5. Create `src/hooks/useAuth.js` hook:
   - Returns { user, token, login, logout, isLoading }
   - Wraps the authStore and API calls

6. Wire the screens into the navigation:
   - `LoginScreen` in `AuthStack`
   - `ProfileScreen` as a tab or accessible from Settings tab

Styling requirements:
- Use React Native Paper components (TextInput, Button, Card, Avatar)
- Support both light and dark themes via Paper's theme provider
- Show role as a colored chip (e.g., cashier = blue, admin = red)
```

---

## Expected Output

- Working Login screen with real API call and token persistence
- Profile screen showing authenticated user data
- Logout clears token and returns to Login
- Password change with validation feedback
