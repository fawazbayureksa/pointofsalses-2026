# Authentication API

All endpoints under `/api/auth`.

---

## POST /api/auth/register

Register a new Tenant, admin user, and default outlet for mobile apps (14-day free trial).

**Public** – No `Authorization` header required.

### Request Body
```json
{
  "name": "Budi Santoso",
  "business_name": "Kopi Nusantara",
  "email": "budi@kopinusantara.id",
  "password": "password123",
  "password_confirmation": "password123",
  "phone": "081234567890"
}
```

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| `name` | string (max:255) | Yes | Full name of the user |
| `business_name` | string (max:255) | No | Business/store name (defaults to `{name}'s Business`) |
| `email` | string (email, max:255) | Yes | Work email (unique in system) |
| `password` | string (min:8) | Yes | Account password |
| `password_confirmation` | string | Yes | Password confirmation (must match `password`) |
| `phone` | string (max:20) | No | Contact phone number |

### Response `201`
```json
{
  "message": "Tenant registered successfully.",
  "token": "1|abcdefghijk...",
  "user": {
    "id": 1,
    "name": "Budi Santoso",
    "email": "budi@kopinusantara.id",
    "phone": "081234567890",
    "roles": ["tenant_admin"],
    "has_pin": false
  },
  "tenant": {
    "id": 1,
    "name": "Kopi Nusantara",
    "slug": "kopi-nusantara",
    "email": "budi@kopinusantara.id",
    "plan": "professional",
    "status": "active",
    "subscription_status": "trial",
    "trial_ends_at": "2026-11-04T01:53:05.000000Z"
  },
  "outlet": {
    "id": 1,
    "name": "Main Outlet",
    "code": "MAIN",
    "is_active": true
  }
}
```

### Error `422`
```json
{
  "message": "The email has already been taken.",
  "errors": {
    "email": ["The email has already been taken."],
    "password": ["The password field confirmation does not match."]
  }
}
```

---

## POST /api/auth/login

Authenticate a user and receive a Sanctum token.

**Public** – No `Authorization` header required.

### Request Body
```json
{
  "email": "cashier@example.com",
  "password": "secret123"
}
```

### Response `200`
```json
{
  "token": "1|abcdefghijk...",
  "user": {
    "id": 1,
    "name": "John Cashier",
    "email": "cashier@example.com",
    "roles": ["cashier"]
  }
}
```

### Error `422`
```json
{
  "message": "The given data was invalid.",
  "errors": {
    "email": ["The provided credentials are incorrect."]
  }
}
```

---

## POST /api/auth/logout

Revoke the current access token.

**Requires auth.**

### Response `200`
```json
{
  "message": "Logged out successfully."
}
```

---

## GET /api/auth/me

Get the currently authenticated user with roles and permissions.

**Requires auth.**

### Response `200`
```json
{
  "id": 1,
  "name": "John Cashier",
  "email": "cashier@example.com",
  "roles": ["cashier"],
  "permissions": ["create_orders", "view_products"]
}
```

---

## PUT /api/auth/profile

Update the authenticated user's name, email, or phone.

**Requires auth.**

### Request Body (all fields optional)
```json
{
  "name": "John Updated",
  "email": "new@example.com",
  "phone": "+6281234567890"
}
```

### Response `200`
```json
{
  "id": 1,
  "name": "John Updated",
  "email": "new@example.com",
  "phone": "+6281234567890"
}
```

---

## PUT /api/auth/password

Change the authenticated user's password.

**Requires auth.**

### Request Body
```json
{
  "current_password": "oldpassword",
  "password": "newpassword",
  "password_confirmation": "newpassword"
}
```

### Response `200`
```json
{
  "message": "Password changed successfully."
}
```

### Error `422`
```json
{
  "message": "The given data was invalid.",
  "errors": {
    "current_password": ["The password is incorrect."]
  }
}
```

---

## Notes

- Tokens are **Bearer tokens** – store them securely using `expo-secure-store` or `@react-native-async-storage/async-storage` with encryption.
- After login, store `token` and `user` in your app state (Redux / Zustand).
- On `401 Unauthenticated` response, redirect the user to the Login screen and clear the stored token.
