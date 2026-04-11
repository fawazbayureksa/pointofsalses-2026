# Point of Sales 2026 – Complete API Reference

> **Version:** 1.0  
> **Last updated:** 2026-04-11  
> **Format:** REST / JSON  
> **Auth:** Laravel Sanctum (Bearer Token)

---

## Table of Contents

1. [Overview](#1-overview)
2. [Authentication](#2-authentication)
3. [Standard Response Format](#3-standard-response-format)
4. [Error Handling](#4-error-handling)
5. [Rate Limiting](#5-rate-limiting)
6. [Security Notes](#6-security-notes)
7. [Endpoints](#7-endpoints)
   - [7.1 Auth](#71-auth)
   - [7.2 Dashboard](#72-dashboard)
   - [7.3 Products](#73-products)
   - [7.4 Categories](#74-categories)
   - [7.5 Customers](#75-customers)
   - [7.6 Outlets](#76-outlets)
   - [7.7 Orders](#77-orders)
   - [7.8 Payments](#78-payments)
   - [7.9 Cashier Shifts](#79-cashier-shifts)
   - [7.10 Supervisor Authorization](#710-supervisor-authorization)
   - [7.11 Reports](#711-reports)
   - [7.12 Stock](#712-stock)
   - [7.13 Configuration](#713-configuration)
8. [Integration Guide](#8-integration-guide)

---

## 1. Overview

**Point of Sales 2026** is a multi-tenant, cloud-based Point-of-Sale backend built with **Laravel 11** and **Laravel Sanctum**. It serves mobile POS applications and external integrations with a fully RESTful API.

### Purpose

- Power the React Native cashier application.
- Provide external clients (accounting, inventory, analytics platforms) with programmatic access to sales, product, and shift data.
- Support offline-first mobile workflows through predictable response shapes and idempotent operations.

### Base URL

```
https://<your-tenant-subdomain>.yourdomain.com/api
```

> Replace `<your-tenant-subdomain>` with the slug assigned to your organization. All routes are tenant-scoped.

---

## 2. Authentication

The API uses **Laravel Sanctum Bearer Tokens**. Two authentication flows are available depending on the client type.

### 2.1 Standard Login (email + password)

Intended for admin and back-office users.

```
POST /api/auth/login
```

### 2.2 PIN Login (cashier fast access)

Intended for cashier devices where speed matters.

```
POST /api/auth/login-pin
```

### 2.3 Required Headers

Every **authenticated** request must include:

```http
Authorization: Bearer <token>
Accept: application/json
Content-Type: application/json
```

Public endpoints (`POST /api/auth/login`, `POST /api/auth/login-pin`, `POST /api/auth/switch-cashier`) do **not** require the `Authorization` header.

### 2.4 Token Lifespan

Tokens do not expire automatically but are invalidated on `POST /api/auth/logout`. Implement automatic logout on `401` responses in your client.

---

## 3. Standard Response Format

### Success

Single resource:

```json
{
  "id": 1,
  "name": "Example Resource",
  "created_at": "2026-04-11T10:00:00.000000Z"
}
```

Paginated list:

```json
{
  "current_page": 1,
  "data": [ { "id": 1, "..." }, "..." ],
  "per_page": 20,
  "total": 100,
  "last_page": 5,
  "next_page_url": "https://example.com/api/resource?page=2",
  "prev_page_url": null
}
```

### Error

```json
{
  "message": "The given data was invalid.",
  "errors": {
    "field_name": ["Error message describing the problem."]
  }
}
```

---

## 4. Error Handling

| HTTP Status | Meaning                          | When It Occurs                                              |
|-------------|----------------------------------|-------------------------------------------------------------|
| `200`       | OK                               | Successful GET, PUT, PATCH, POST (non-create)               |
| `201`       | Created                          | Successful POST that creates a resource                     |
| `204`       | No Content                       | Successful DELETE                                           |
| `401`       | Unauthenticated                  | Missing or invalid Bearer token; session locked             |
| `403`       | Forbidden                        | Token valid but user lacks required permission              |
| `404`       | Not Found                        | Resource does not exist or belongs to another tenant        |
| `422`       | Unprocessable Entity             | Request body failed validation                              |
| `429`       | Too Many Requests                | Rate limit exceeded                                         |
| `500`       | Internal Server Error            | Unexpected server-side failure                              |

### Example Error Responses

**401 Unauthenticated**

```json
{ "message": "Unauthenticated." }
```

**403 Forbidden**

```json
{ "message": "This action is unauthorized." }
```

**422 Validation Error**

```json
{
  "message": "The given data was invalid.",
  "errors": {
    "email": ["The email field is required."],
    "pin":   ["The pin must be 6 digits."]
  }
}
```

**404 Not Found**

```json
{ "message": "No query results for model [App\\Models\\Product] 99." }
```

---

## 5. Rate Limiting

The API enforces the following default limits:

| Scope           | Limit         |
|-----------------|---------------|
| Global per IP   | 60 req / min  |
| Auth endpoints  | 10 req / min  |

When the limit is exceeded the API returns `HTTP 429` with:

```json
{
  "message": "Too Many Requests."
}
```

The response includes standard `Retry-After` and `X-RateLimit-*` headers to help clients back off gracefully.

> **Note for future scalability:** Rate limits are configurable per tenant and can be raised for verified integrations. Contact your platform administrator to request higher quotas.

---

## 6. Security Notes

- **Store tokens securely.** On mobile use `expo-secure-store` (React Native / Expo) or the Android `EncryptedSharedPreferences`. Never store tokens in `localStorage` on web.
- **Use HTTPS exclusively.** All traffic must go over TLS 1.2+. HTTP requests will be redirected or rejected.
- **Validate tenant scope.** Resources are silently scoped to the authenticated user's tenant. You will never receive data from a different tenant.
- **Rotate tokens after privilege escalation.** If a user's role changes, invalidate existing tokens and re-authenticate.
- **PIN security.** PINs are stored as bcrypt hashes server-side. Never log or transmit PINs outside of the designated login endpoints.
- **Supervisor actions.** Refunds, voids, and discount overrides require a supervisor token obtained from `POST /api/supervisor/authorize`. These tokens are short-lived and action-scoped.

---

## 7. Endpoints

---

### 7.1 Auth

#### `POST /api/auth/login`

Authenticate with email and password. Returns a Sanctum Bearer token.

**Public – no auth required.**

**Request Body**

| Field      | Type   | Required | Description           |
|------------|--------|----------|-----------------------|
| `email`    | string | ✅        | User's email address  |
| `password` | string | ✅        | User's password       |

```json
{
  "email": "cashier@example.com",
  "password": "secret123"
}
```

**Response `200`**

```json
{
  "token": "1|abcdefghijklmnopqrstuvwxyz",
  "user": {
    "id": 1,
    "name": "John Cashier",
    "email": "cashier@example.com",
    "roles": ["cashier"],
    "has_pin": true
  }
}
```

---

#### `POST /api/auth/login-pin`

Authenticate a cashier using email + 6-digit PIN. Optimised for shared POS devices.

**Public – no auth required.**

**Request Body**

| Field   | Type   | Required | Description               |
|---------|--------|----------|---------------------------|
| `email` | string | ✅        | Cashier's email address   |
| `pin`   | string | ✅        | 6-digit numeric PIN       |

```json
{
  "email": "cashier@example.com",
  "pin": "123456"
}
```

**Response `200`**

```json
{
  "token": "2|abcdefghijklmnopqrstuvwxyz",
  "user": {
    "id": 2,
    "name": "Jane Cashier",
    "roles": ["cashier"]
  }
}
```

---

#### `POST /api/auth/switch-cashier`

Switch the active cashier session on a shared device using a PIN. Does not require logout first.

**Public – no auth required.**

**Request Body**

| Field     | Type    | Required | Description            |
|-----------|---------|----------|------------------------|
| `user_id` | integer | ✅        | ID of the new cashier  |
| `pin`     | string  | ✅        | 6-digit PIN            |

```json
{
  "user_id": 3,
  "pin": "654321"
}
```

**Response `200`**

```json
{
  "token": "3|newtoken...",
  "user": { "id": 3, "name": "Bob Cashier" }
}
```

---

#### `POST /api/auth/logout`

Revoke the current access token.

**Requires auth.**

**Response `200`**

```json
{ "message": "Logged out successfully." }
```

---

#### `GET /api/auth/me`

Get the currently authenticated user with roles and permissions.

**Requires auth.**

**Response `200`**

```json
{
  "id": 1,
  "name": "John Cashier",
  "email": "cashier@example.com",
  "roles": ["cashier"],
  "permissions": ["create_orders", "view_products", "view_reports"],
  "has_pin": true
}
```

---

#### `PUT /api/auth/profile`

Update the authenticated user's profile. All fields are optional.

**Requires auth.**

**Request Body**

| Field   | Type   | Required | Description              |
|---------|--------|----------|--------------------------|
| `name`  | string | ❌        | Display name             |
| `email` | string | ❌        | Must be unique           |
| `phone` | string | ❌        | Contact number           |

```json
{
  "name": "John Updated",
  "phone": "+6281234567890"
}
```

**Response `200`**

```json
{
  "id": 1,
  "name": "John Updated",
  "email": "cashier@example.com",
  "phone": "+6281234567890"
}
```

---

#### `PUT /api/auth/password`

Change the authenticated user's password.

**Requires auth.**

**Request Body**

| Field                   | Type   | Required | Description              |
|-------------------------|--------|----------|--------------------------|
| `current_password`      | string | ✅        | Current password         |
| `password`              | string | ✅        | New password (min 8)     |
| `password_confirmation` | string | ✅        | Must match `password`    |

```json
{
  "current_password": "oldpassword",
  "password": "newpassword123",
  "password_confirmation": "newpassword123"
}
```

**Response `200`**

```json
{ "message": "Password changed successfully." }
```

---

#### `POST /api/auth/set-pin`

Set or update the authenticated user's 6-digit PIN.

**Requires auth.**

**Request Body**

| Field              | Type   | Required | Description           |
|--------------------|--------|----------|-----------------------|
| `pin`              | string | ✅        | 6-digit numeric PIN   |
| `pin_confirmation` | string | ✅        | Must match `pin`      |

```json
{
  "pin": "123456",
  "pin_confirmation": "123456"
}
```

**Response `200`**

```json
{ "message": "PIN set successfully." }
```

---

#### `GET /api/auth/cashiers`

List active cashiers available for PIN-based login on shared devices.

**Requires auth.**

**Response `200`**

```json
{
  "cashiers": [
    { "id": 1, "name": "John Cashier", "avatar": null },
    { "id": 2, "name": "Jane Cashier", "avatar": "https://cdn.example.com/avatars/2.jpg" }
  ]
}
```

---

### 7.2 Dashboard

#### `GET /api/dashboard`

Aggregated statistics for today, this month, and the last 7 days.

**Requires auth.**

**Response `200`**

```json
{
  "sales": {
    "today": 1250000,
    "today_growth": 12.5,
    "monthly": 32000000,
    "monthly_growth": 8.3,
    "avg_order": 85000,
    "last_7_days": [
      { "date": "2026-04-05", "label": "Sun, 05 Apr", "total": 980000 }
    ]
  },
  "orders": {
    "today": 15,
    "completed": 13,
    "pending": 2
  },
  "outlets": 3,
  "customers": {
    "total": 420,
    "new_this_week": 12
  },
  "top_products": [
    { "product_id": 5, "product_name": "Americano", "sold": 42, "revenue": 1680000 }
  ],
  "payment_method_stats": [
    { "payment_method": "cash", "count": 8, "total": 640000 },
    { "payment_method": "qris", "count": 5, "total": 410000 }
  ],
  "low_stock_products": [
    { "product_id": 12, "name": "Milk 1L", "outlet_name": "Main Branch", "stock": 2, "threshold": 5 }
  ],
  "recent_orders": [
    {
      "id": 101,
      "order_number": "ORD-2026-0101",
      "status": "completed",
      "total_amount": 95000,
      "customer": "Alice",
      "outlet": "Main Branch",
      "created_at": "2026-04-11T09:30:00.000000Z"
    }
  ]
}
```

---

### 7.3 Products

#### `GET /api/products`

List products with optional filters and pagination.

**Requires auth.**

**Query Parameters**

| Parameter   | Type    | Description                              |
|-------------|---------|------------------------------------------|
| `search`    | string  | Filter by name or SKU                    |
| `category_id` | int   | Filter by category                       |
| `outlet_id` | int     | Filter by outlet stock availability      |
| `is_active` | boolean | Filter active/inactive products          |
| `per_page`  | int     | Results per page (default: 20)           |
| `page`      | int     | Page number                              |

**Response `200`** *(paginated)*

```json
{
  "current_page": 1,
  "data": [
    {
      "id": 1,
      "name": "Americano",
      "sku": "BEV-001",
      "price": 40000,
      "category": { "id": 2, "name": "Beverages" },
      "is_active": true,
      "track_stock": true,
      "stock": 50
    }
  ],
  "per_page": 20,
  "total": 85
}
```

---

#### `POST /api/products`

Create a new product.

**Requires auth.**

**Request Body**

| Field         | Type    | Required | Description                   |
|---------------|---------|----------|-------------------------------|
| `name`        | string  | ✅        | Product name                  |
| `sku`         | string  | ❌        | Unique stock-keeping unit     |
| `barcode`     | string  | ❌        | Barcode (EAN/UPC)             |
| `price`       | numeric | ✅        | Selling price                 |
| `cost_price`  | numeric | ❌        | Purchase/cost price           |
| `category_id` | int     | ❌        | Category ID                   |
| `track_stock` | boolean | ❌        | Enable stock tracking         |
| `is_active`   | boolean | ❌        | Default: `true`               |
| `description` | string  | ❌        | Product description           |

```json
{
  "name": "Cappuccino",
  "sku": "BEV-002",
  "price": 45000,
  "cost_price": 12000,
  "category_id": 2,
  "track_stock": true
}
```

**Response `201`**

```json
{
  "id": 2,
  "name": "Cappuccino",
  "sku": "BEV-002",
  "price": 45000,
  "is_active": true
}
```

---

#### `GET /api/products/{id}`

Retrieve a single product by ID.

**Requires auth.**

**Response `200`** — full product object including category and outlet stock.

---

#### `PUT /api/products/{id}`

Update an existing product. Accepts the same fields as `POST /api/products`.

**Requires auth. Response `200`.**

---

#### `DELETE /api/products/{id}`

Soft-delete (deactivate) a product.

**Requires auth. Response `204`.**

---

#### `GET /api/products/barcode/{barcode}`

Look up a product by its barcode. Useful for scanner-based checkout.

**Requires auth.**

```
GET /api/products/barcode/8991234567890
```

**Response `200`** — single product object.

**Response `404`** if barcode is not found.

---

#### `POST /api/products/{id}/image`

Upload a product image. Send as `multipart/form-data`.

**Requires auth.**

**Form Field:** `image` (file, max 2 MB, JPEG/PNG/WebP)

**Response `200`**

```json
{ "image_url": "https://cdn.example.com/products/2/image.jpg" }
```

---

### 7.4 Categories

#### `GET /api/categories`

List all product categories.

**Requires auth. Response `200`** — array of categories.

```json
[
  { "id": 1, "name": "Food", "description": null },
  { "id": 2, "name": "Beverages", "description": "Hot & cold drinks" }
]
```

---

#### `POST /api/categories`

Create a new category.

**Requires auth.**

```json
{ "name": "Snacks", "description": "Light bites" }
```

**Response `201`** — created category object.

---

#### `GET /api/categories/{id}` · `PUT /api/categories/{id}` · `DELETE /api/categories/{id}`

Standard CRUD operations. `DELETE` returns `204`.

---

### 7.5 Customers

#### `GET /api/customers`

List customers. Supports `search` query parameter.

**Requires auth. Response `200`** *(paginated).*

```json
{
  "data": [
    {
      "id": 1,
      "name": "Alice Smith",
      "email": "alice@example.com",
      "phone": "+6281111111111",
      "loyalty_points": 250
    }
  ],
  "total": 420
}
```

---

#### `POST /api/customers`

Register a new customer.

**Requires auth.**

| Field    | Type   | Required |
|----------|--------|----------|
| `name`   | string | ✅        |
| `email`  | string | ❌        |
| `phone`  | string | ❌        |
| `address`| string | ❌        |

```json
{ "name": "Bob Jones", "phone": "+6282222222222" }
```

**Response `201`** — created customer object.

---

#### `GET /api/customers/{id}` · `PUT /api/customers/{id}` · `DELETE /api/customers/{id}`

Standard CRUD. `DELETE` returns `204`.

---

### 7.6 Outlets

#### `GET /api/outlets`

List all active outlets for the current tenant.

**Requires auth. Response `200`.**

```json
[
  { "id": 1, "name": "Main Branch", "address": "Jl. Merdeka No. 1", "is_active": true },
  { "id": 2, "name": "Mall Kiosk",  "address": "Mall ABC Lt. 2",    "is_active": true }
]
```

---

#### `GET /api/outlets/{id}`

Get details for a specific outlet.

**Requires auth. Response `200`** — single outlet object.

---

### 7.7 Orders

#### `GET /api/orders`

List orders with filters.

**Requires auth.**

**Query Parameters**

| Parameter    | Type   | Description                                  |
|--------------|--------|----------------------------------------------|
| `status`     | string | `pending`, `completed`, `cancelled`          |
| `outlet_id`  | int    | Filter by outlet                             |
| `date_from`  | date   | `YYYY-MM-DD` start date                      |
| `date_to`    | date   | `YYYY-MM-DD` end date                        |
| `search`     | string | Order number or notes                        |
| `per_page`   | int    | Default: 20                                  |

**Response `200`** *(paginated)* — array of order summaries.

---

#### `POST /api/orders`

Create a new order (POS checkout).

**Requires auth.**

**Request Body**

| Field                      | Type    | Required | Description                              |
|----------------------------|---------|----------|------------------------------------------|
| `outlet_id`                | int     | ✅        | Outlet where the order is placed         |
| `customer_id`              | int     | ❌        | Optional linked customer                 |
| `notes`                    | string  | ❌        | Order notes                              |
| `discount_amount`          | numeric | ❌        | Order-level discount                     |
| `discount_type`            | string  | ❌        | `fixed` or `percentage`                  |
| `loyalty_points_redeemed`  | int     | ❌        | Points to redeem (integer)               |
| `items`                    | array   | ✅        | At least one item required               |
| `items[].product_id`       | int     | ✅        | Product ID                               |
| `items[].quantity`         | numeric | ✅        | Quantity (supports decimals, min 0.001)  |
| `items[].discount_amount`  | numeric | ❌        | Per-item discount                        |

```json
{
  "outlet_id": 1,
  "customer_id": 5,
  "items": [
    { "product_id": 1, "quantity": 2 },
    { "product_id": 3, "quantity": 1, "discount_amount": 5000 }
  ],
  "discount_type": "fixed",
  "discount_amount": 10000
}
```

**Response `201`** — full order object with computed totals.

---

#### `GET /api/orders/{id}`

Get a single order with items, payments, cashier, customer, and outlet.

**Requires auth. Response `200`.**

```json
{
  "id": 101,
  "order_number": "ORD-2026-0101",
  "status": "completed",
  "payment_status": "paid",
  "subtotal": 125000,
  "discount_amount": 10000,
  "tax_amount": 11500,
  "total_amount": 126500,
  "cashier": { "id": 1, "name": "John Cashier" },
  "customer": { "id": 5, "name": "Alice" },
  "outlet": { "id": 1, "name": "Main Branch" },
  "items": [
    {
      "id": 1,
      "product_id": 1,
      "product_name": "Americano",
      "quantity": 2,
      "unit_price": 40000,
      "subtotal": 80000
    }
  ],
  "payments": [
    {
      "id": 1,
      "payment_method": "cash",
      "amount": 126500,
      "status": "completed"
    }
  ]
}
```

---

#### `POST /api/orders/{id}/pay`

Record a payment for a pending order.

**Requires auth.**

| Field              | Type    | Required | Description                                   |
|--------------------|---------|----------|-----------------------------------------------|
| `payment_method`   | string  | ✅        | `cash`, `card`, `qris`, `transfer`            |
| `amount`           | numeric | ✅        | Amount tendered                               |
| `reference_number` | string  | ❌        | Card/transfer reference                       |

```json
{
  "payment_method": "qris",
  "amount": 126500,
  "reference_number": "QRIS-ABC123"
}
```

**Response `200`** — payment object.

---

#### `POST /api/orders/{id}/cancel`

Cancel a pending order.

**Requires auth.**

```json
{ "reason": "Customer changed mind" }
```

**Response `200`** — updated order object.

---

#### `POST /api/orders/{id}/refund`

Refund a completed and paid order. Optionally supply a `supervisor_id` obtained from `POST /api/supervisor/authorize`.

**Requires auth.**

| Field           | Type   | Required | Description                                     |
|-----------------|--------|----------|-------------------------------------------------|
| `reason`        | string | ❌        | Reason for refund (max 500 chars)               |
| `supervisor_id` | int    | ❌        | ID of authorising supervisor                   |

```json
{
  "reason": "Wrong item delivered",
  "supervisor_id": 10
}
```

**Response `200`**

```json
{
  "message": "Order refunded successfully.",
  "payment": { "id": 1, "status": "refunded", "..." }
}
```

---

#### `PATCH /api/orders/{id}/discount`

Apply or update an order-level discount on a **pending** order.

**Requires auth.**

| Field             | Type    | Required | Description                         |
|-------------------|---------|----------|-------------------------------------|
| `discount_amount` | numeric | ✅        | Discount value                      |
| `discount_type`   | string  | ❌        | `fixed` (default) or `percentage`   |
| `supervisor_id`   | int     | ❌        | ID of authorising supervisor        |

```json
{
  "discount_amount": 15000,
  "discount_type": "fixed",
  "supervisor_id": 10
}
```

**Response `200`** — updated order object.

---

### 7.8 Payments

#### `GET /api/payments`

List payment records (paginated).

**Requires auth. Response `200`** *(paginated).*

---

#### `GET /api/payments/{id}`

Get a single payment with its related order.

**Requires auth. Response `200`.**

```json
{
  "id": 1,
  "order_id": 101,
  "payment_method": "cash",
  "amount": 126500,
  "status": "completed",
  "reference_number": null,
  "created_at": "2026-04-11T09:35:00.000000Z"
}
```

---

### 7.9 Cashier Shifts

#### `GET /api/shifts`

List all shifts for the current tenant (paginated).

**Requires auth. Response `200`** *(paginated).*

---

#### `GET /api/shifts/current`

Get the authenticated cashier's currently active (open) shift.

**Requires auth.**

**Response `200`** — shift object or `null` if no active shift.

```json
{
  "id": 5,
  "cashier": "John Cashier",
  "outlet": "Main Branch",
  "started_at": "2026-04-11T08:00:00.000000Z",
  "ended_at": null,
  "starting_cash": 500000,
  "ending_cash": null
}
```

---

#### `POST /api/shifts/start`

Open a new cashier shift.

**Requires auth.**

| Field           | Type    | Required | Description                         |
|-----------------|---------|----------|-------------------------------------|
| `outlet_id`     | int     | ✅        | Outlet for the shift                |
| `starting_cash` | numeric | ✅        | Opening float (cash in drawer)      |
| `notes`         | string  | ❌        | Optional shift notes                |

```json
{
  "outlet_id": 1,
  "starting_cash": 500000
}
```

**Response `201`** — created shift object.

---

#### `POST /api/shifts/end`

Close the current active shift.

**Requires auth.**

| Field         | Type    | Required | Description                       |
|---------------|---------|----------|-----------------------------------|
| `ending_cash` | numeric | ✅        | Counted cash at end of shift      |
| `notes`       | string  | ❌        | Optional closing notes            |

```json
{
  "ending_cash": 875000,
  "notes": "Smooth shift, no issues."
}
```

**Response `200`** — closed shift object.

---

### 7.10 Supervisor Authorization

#### `POST /api/supervisor/authorize`

Validate a supervisor's PIN and grant authorization for a restricted action. Returns the supervisor's user ID on success, which must then be passed to the relevant endpoint (refund, cancel, discount).

**Requires auth.**

| Field       | Type   | Required | Description                                            |
|-------------|--------|----------|--------------------------------------------------------|
| `action`    | string | ✅        | `refund`, `void`, or `discount_override`               |
| `pin`       | string | ✅        | Supervisor's 6-digit PIN                               |

```json
{
  "action": "refund",
  "pin": "999888"
}
```

**Response `200`**

```json
{
  "authorized": true,
  "supervisor_id": 10,
  "supervisor_name": "Manager Jane"
}
```

**Response `403`** if PIN is incorrect or supervisor lacks the required permission.

---

### 7.11 Reports

#### `GET /api/reports/sales-by-cashier`

Sales totals grouped by cashier for a given date range.

**Requires auth.**

**Query Parameters**

| Parameter   | Type | Description              |
|-------------|------|--------------------------|
| `date_from` | date | Start date (YYYY-MM-DD)  |
| `date_to`   | date | End date (YYYY-MM-DD)    |
| `outlet_id` | int  | Filter by outlet         |

**Response `200`**

```json
{
  "report": [
    {
      "cashier_id": 1,
      "cashier_name": "John Cashier",
      "total_transactions": 42,
      "total_sales": 3570000,
      "average_transaction": 85000,
      "total_discounts": 150000,
      "total_tax": 320000
    }
  ]
}
```

---

#### `GET /api/reports/shift-summary`

Paginated shift summary with sales figures per shift.

**Requires auth.**

**Query Parameters**

| Parameter   | Type | Description              |
|-------------|------|--------------------------|
| `date_from` | date | Start date               |
| `date_to`   | date | End date                 |
| `user_id`   | int  | Filter by cashier        |
| `outlet_id` | int  | Filter by outlet         |
| `per_page`  | int  | Default: 20              |

**Response `200`** *(paginated)* — each item includes shift metadata and computed sales figures.

---

### 7.12 Stock

#### `GET /api/stock/movements`

List stock movement history (paginated). Filter by product, outlet, or date range.

**Requires auth. Response `200`** *(paginated)* — movement records.

---

#### `POST /api/stock/adjust`

Manually adjust stock for a product at a specific outlet (e.g. stocktake, wastage).

**Requires auth.**

| Field        | Type    | Required | Description                                          |
|--------------|---------|----------|------------------------------------------------------|
| `product_id` | int     | ✅        | Product to adjust                                    |
| `outlet_id`  | int     | ✅        | Target outlet                                        |
| `quantity`   | numeric | ✅        | Positive (add) or negative (remove)                  |
| `reason`     | string  | ❌        | Reason for adjustment (max 255 chars)                |

```json
{
  "product_id": 12,
  "outlet_id": 1,
  "quantity": -3,
  "reason": "Damaged goods"
}
```

**Response `200`** — updated stock record.

---

### 7.13 Configuration

#### `GET /api/config`

Retrieve all tenant-level configuration keys and their values.

**Requires auth. Response `200`.**

```json
{
  "tax_rate": 0.11,
  "currency": "IDR",
  "receipt_footer": "Thank you for your purchase!",
  "loyalty_points_rate": 0.01
}
```

---

#### `GET /api/config/{key}`

Retrieve a single configuration value by key.

**Requires auth.**

```
GET /api/config/tax_rate
```

**Response `200`**

```json
{ "key": "tax_rate", "value": 0.11 }
```

---

#### `PUT /api/config/{key}`

Update a configuration value.

**Requires auth.**

| Field   | Type   | Required | Description                                |
|---------|--------|----------|--------------------------------------------|
| `value` | any    | ✅        | New value                                  |
| `type`  | string | ❌        | `string`, `integer`, `boolean`, or `json`  |

```json
{ "value": 0.12, "type": "string" }
```

**Response `200`**

```json
{ "key": "tax_rate", "value": 0.12 }
```

---

## 8. Integration Guide

### Step 1 – Install an HTTP client

```bash
# npm
npm install axios

# or use native fetch (no install required)
```

### Step 2 – Configure your API client

```javascript
// src/api/client.js
import axios from 'axios';

const apiClient = axios.create({
  baseURL: 'https://<your-tenant>.yourdomain.com/api',
  headers: {
    'Accept': 'application/json',
    'Content-Type': 'application/json',
  },
});

// Attach token from secure storage before each request
apiClient.interceptors.request.use(async (config) => {
  const token = await SecureStore.getItemAsync('auth_token'); // expo-secure-store
  if (token) {
    config.headers.Authorization = `Bearer ${token}`;
  }
  return config;
});

// Handle 401 globally – redirect to login
apiClient.interceptors.response.use(
  (response) => response,
  async (error) => {
    if (error.response?.status === 401) {
      await SecureStore.deleteItemAsync('auth_token');
      // Navigate to login screen
    }
    return Promise.reject(error);
  }
);

export default apiClient;
```

### Step 3 – Authenticate

```javascript
// Using axios
async function login(email, password) {
  const { data } = await apiClient.post('/auth/login', { email, password });
  await SecureStore.setItemAsync('auth_token', data.token);
  return data.user;
}

// Using native fetch
async function loginWithFetch(email, password) {
  const res = await fetch('https://<your-tenant>.yourdomain.com/api/auth/login', {
    method: 'POST',
    headers: { 'Accept': 'application/json', 'Content-Type': 'application/json' },
    body: JSON.stringify({ email, password }),
  });
  if (!res.ok) throw new Error('Login failed');
  const data = await res.json();
  return data;
}
```

### Step 4 – Make authenticated requests

```javascript
// Fetch product list
async function getProducts(page = 1) {
  const { data } = await apiClient.get('/products', { params: { page, per_page: 20 } });
  return data; // paginated
}

// Create an order
async function createOrder(payload) {
  const { data } = await apiClient.post('/orders', payload);
  return data;
}

// Process payment
async function payOrder(orderId, paymentMethod, amount) {
  const { data } = await apiClient.post(`/orders/${orderId}/pay`, {
    payment_method: paymentMethod,
    amount,
  });
  return data;
}
```

### Step 5 – Handle errors consistently

```javascript
async function safeRequest(fn) {
  try {
    return await fn();
  } catch (error) {
    const status  = error.response?.status;
    const message = error.response?.data?.message ?? 'An unexpected error occurred.';
    const errors  = error.response?.data?.errors ?? {};

    if (status === 422) {
      // Surface validation messages to the user
      const fieldErrors = Object.values(errors).flat();
      throw new Error(fieldErrors.join('\n'));
    }

    throw new Error(message);
  }
}

// Usage
const products = await safeRequest(() => getProducts());
```

---

> **Scalability note:** For high-volume integrations, consider implementing request queuing, exponential back-off on `429` responses, and local caching of infrequently changing data (products, categories, outlets) with a TTL of 5–15 minutes.
