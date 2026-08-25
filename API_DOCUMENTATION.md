# Restaurant QR Ordering API — Documentation

Base URL: `http://localhost:8000/api/v1` (adjust host/port to your environment)

## Overview

This API powers a single-restaurant QR code ordering system: customers scan a per-table QR code, browse the digital menu, place an order, kitchen staff track and update order status, and cashiers collect cash payment. It also exposes an admin back office (settings, tables, categories, products, staff, dashboard, activity log) for the Vue.js admin panel.

## Response Envelope

Every response follows the same shape.

**Success**
```json
{
  "success": true,
  "message": "Success",
  "data": {}
}
```

**Paginated success** (list endpoints) additionally include a `meta` key with pagination info:
```json
{
  "success": true,
  "message": "Success",
  "data": [ ... ],
  "meta": {
    "current_page": 1,
    "per_page": 15,
    "total": 42,
    "last_page": 3
  }
}
```

**Error**
```json
{
  "success": false,
  "message": "Validation Error",
  "errors": {
    "email": ["The email field is required."]
  }
}
```

| Status | Meaning |
|---|---|
| 200 | OK |
| 201 | Created |
| 401 | Unauthenticated (missing/invalid token, or invalid login credentials) |
| 403 | Forbidden (authenticated, but wrong role) |
| 404 | Resource not found / invalid QR token / invalid order number |
| 422 | Validation error or business-rule violation (e.g. invalid status transition, unavailable product) |
| 429 | Too many requests (rate limit exceeded — see [Rate Limiting](#rate-limiting)) |
| 500 | Server error |

## Authentication

Staff (Admin, Kitchen, Cashier) authenticate via **Laravel Sanctum** personal access tokens. Customers never authenticate — they are identified purely by the table's QR token.

1. `POST /auth/login` → returns a bearer token.
2. Send `Authorization: Bearer {token}` on every subsequent staff request.
3. `POST /auth/logout` revokes the current token.

## Roles & Permissions Matrix

| Endpoint group | Admin | Kitchen | Cashier | Guest |
|---|:---:|:---:|:---:|:---:|
| Menu / Cart / Place Order / Track Order / Receipt | – | – | – | ✅ (public) |
| `GET /orders`, `GET /orders/{id}` | ✅ | ✅ | ✅ | – |
| `PATCH /orders/{id}/status`, `GET /kitchen/dashboard` | ✅ | ✅ | – | – |
| `GET /cashier/payments`, `PATCH /cashier/payments/{order}/pay` | ✅ | – | ✅ | – |
| `/admin/*` (settings, tables, categories, products, users, dashboard, activity-logs) | ✅ | – | – | – |

## Order Status State Machine

```
pending → confirmed → preparing → ready → completed
```

From any of the four active states (`pending`, `confirmed`, `preparing`, `ready`), an order may also transition directly to `cancelled` (a `cancelled_reason` is required). `completed` and `cancelled` are terminal — no further transitions are allowed.

Any transition not listed above returns `422` with message `Cannot transition order from 'X' to 'Y'.`

## Payment Status

`pending` → `paid` (cash only). A `Payment` row is created automatically the moment an order is placed (status `pending`, amount = order total) and flipped to `paid` by the cashier.

## Rate Limiting

Three named limiters guard abuse-prone endpoints (configurable via `.env`, see `config/ratelimit.php`):

| Limiter | Applies to | Default limit | Keyed by |
|---|---|---|---|
| `api` | Every route by default | 60/min | authenticated user ID, else IP |
| `login` | `POST /auth/login` | 5/min per email+IP, and 20/min per IP | email + IP, and IP alone |
| `orders` | `POST /orders` | 10/min | IP |

`login` and `orders` routes are excluded from the general `api` limiter (they have their own, tighter limits instead). Exceeding a limit returns `429` with the standard error envelope.

## Seeded Accounts

| Role | Email | Password |
|---|---|---|
| Admin | admin@restaurant.test | password |
| Kitchen | kitchen@restaurant.test | password |
| Cashier | cashier@restaurant.test | password |

## Telegram Notifications

Set `TELEGRAM_BOT_TOKEN` and `TELEGRAM_CHAT_ID` in `.env` to enable. When both are present, notifications are sent for: new order, order cancelled, order ready, payment completed. If unset, notifications are silently skipped (no error).

---

## Endpoints

### Auth

**POST `/auth/login`** — public, rate limited (`login`)
```json
{ "email": "admin@restaurant.test", "password": "password" }
```
→ `data: { user: {...}, token: "1|xxxx" }`

**POST `/auth/logout`** — auth required

**GET `/auth/me`** — auth required → current user

### Public Customer Journey

**GET `/menu/{qr_token}`** — resolves the table from its QR token and returns restaurant info, table info, and active categories with their available products. Returns 404 for an invalid or inactive QR token.

**POST `/cart/validate`** — recomputes cart pricing server-side (never trust client prices). Also used internally by order placement.
```json
{
  "qr_token": "f8j29dj38dk92",
  "items": [
    { "product_id": 1, "quantity": 2, "notes": "no onions" }
  ]
}
```
→ `data: { items: [...], subtotal, tax_amount, service_charge_amount, total_amount }`

Each item: `product_id` required (must exist), `quantity` required integer 1–99, `notes` optional (max 255 chars).

**POST `/orders`** — places an order. Rate limited (`orders`).
```json
{
  "qr_token": "f8j29dj38dk92",
  "customer_name": "John Doe",
  "customer_phone": "0123456789",
  "notes": "No spicy please",
  "items": [
    { "product_id": 1, "quantity": 2 },
    { "product_id": 10, "quantity": 1 }
  ]
}
```
`customer_name` required, `customer_phone`/`notes` optional. → `201`, full `OrderResource` including generated `order_number` (format `ORD-YYYYMMDD-XXXXXX`).

**GET `/orders/track/{order_number}`** — public order status check, no PII returned (no customer name/phone).

**GET `/orders/{order_number}/receipt`** — public printable receipt: restaurant info (name, address, logo, currency) plus order items, totals, and payment status. No PII beyond customer name.

### Orders (staff)

**GET `/orders?status=&table_id=&date=`** — Admin, Kitchen, Cashier — paginated, filterable
**GET `/orders/{id}`** — Admin, Kitchen, Cashier
**PATCH `/orders/{id}/status`** — Admin, Kitchen
```json
{ "status": "cancelled", "cancelled_reason": "Customer left" }
```
`status` must be one of `confirmed`, `preparing`, `ready`, `completed`, `cancelled` (see [state machine](#order-status-state-machine)); `cancelled_reason` required only when cancelling.

### Kitchen

**GET `/kitchen/dashboard`** — Admin, Kitchen → `{ pending, confirmed, preparing, ready, completed_today }`, each an array of orders.

### Cashier

**GET `/cashier/payments?status=pending`** — Admin, Cashier
**PATCH `/cashier/payments/{order_id}/pay`** — Admin, Cashier → marks the order's payment as paid.

### Admin — Dashboard & Activity

**GET `/admin/dashboard`** → `{ today_orders, today_revenue, pending_orders, preparing_orders, completed_orders, popular_menu, recent_orders }`

**GET `/admin/activity-logs`** — paginated audit trail of staff actions.

### Admin — Restaurant Settings

**GET `/admin/settings`**
**PUT `/admin/settings`** (multipart if uploading `logo`)
```json
{
  "name": "The QR Bistro",
  "currency": "USD",
  "tax_percentage": 10,
  "service_charge_percentage": 5,
  "opening_time": "09:00",
  "closing_time": "22:00"
}
```
All fields optional on update (`sometimes`); `currency` must be a 3-letter code when present.

### Admin — Tables

Standard REST resource at `/admin/tables` (`index`, `store`, `show`, `update`, `destroy`), plus:

**POST `/admin/tables/{table}/regenerate-qr`** — issues a new QR token/image for a table (invalidates the old one).

`store` body:
```json
{ "table_number": "T09", "capacity": 4 }
```
`table_number` must be unique; `capacity` 1–50; `status` optional, one of `available`, `occupied`, `reserved`, `inactive`.

### Admin — Categories

Standard REST resource at `/admin/categories`. `store`/`update` accept multipart with optional `image` (max 2MB), plus `is_active` and `sort_order`.

### Admin — Products

Standard REST resource at `/admin/products`. `index` supports `?category_id=&available=1&search=`.
```json
{ "category_id": 1, "name": "Beef Noodle Soup", "price": 6.5, "preparation_time": 15 }
```
`category_id` must exist; `price` numeric ≥ 0; `preparation_time` optional, 1–180 minutes; `image` optional multipart, max 2MB.

### Admin — Staff Users

Standard REST resource at `/admin/users`.
```json
{ "name": "New Cashier", "email": "cashier2@restaurant.test", "password": "password123", "role": "cashier" }
```
`email` must be unique; `password` min 8 chars; `role` one of `admin`, `kitchen`, `cashier`.
