# WINCH — Order Assignment System

A real-time order-to-driver assignment system built with **Laravel 12** and **Vue.js**, following a strict **Domain-Driven Design (DDD)** layout.

## Tech Stack & Why

| **Backend** | Laravel 12 | Latest stable version of laravel
| **PHP** | 8.2.12 
| **Database** | MySQL 8 (target) / MariaDB 10.4 (local dev)
| **Frontend** | Vue.js (Composition API) 


## Requirements

- PHP 8.2+
- Composer 2.x
- Node.js 18+
- MySQL 8 (or MariaDB 10.4+)

## Setup

```bash
git clone https://github.com/omarMohammedbenzo/winch.git
cd winch

composer install
cp .env.example .env
php artisan key:generate

php artisan migrate --seed

npm install
npm run dev

php artisan serve
```

### Stage 1 — Scaffold
Laravel 12 (12.60.2) on PHP 8.2.12. Git initialized and pushed.

### Stage 2 — DDD structure
Enforced the mandated `src/` layout with two bounded contexts and one presentation cpanel:

```
src/
├── Domain/
│   ├── Orders/    (Actions, Contracts, Services, DataTransferObjects,
│   └── Drivers/    Enums, Exceptions, Models/{Entities,Scopes,Abilities,
│                   Traits}, Providers, Observers, Traits)
└── Presentation/
    └── Admin/     (Controllers, Requests, Providers, Resources, Routes, Views)
```

### Stage 3 — Database schema
Migrations for `drivers` and `orders`.

- **Coordinates as `DECIMAL(10,7)`** (source of truth) instead of a spatial `POINT`. Nearest-driver search runs a **bounding-box pre-filter** on indexed `latitude`/`longitude`, then an exact `ST_Distance_Sphere()` on the survivors — fast, portable, and works identically on MySQL 8 and MariaDB 10.4 without a spatial extension. (At larger scale: a `POINT` + `SPATIAL INDEX`, or PostGIS.) check https://postgis.net/docs/manual-1.4/ST_Distance_Sphere.html

- `drivers.current_order_id` — denormalized pointer to the active order, so "driver has no active order" is an O(1).

### Stage 4 — Domain core (models, enums, DTOs, contracts)
- **Enums** carry the rules: `OrderStatus` (`canBeAssigned()`, `occupiesDriver()`, `active()`), `DriverStatus` (`canAcceptOrder()`).

- **Models** (`Order`, `Driver`) live under `Domain/{Context}/Models/Entities`, with cast enums and **explicit local query scopes** — `Driver::assignable()`, `Driver::withinBoundingBox()`, `Order::active()`. Local scopes (not global scopes) so rows are never hidden implicitly.

- **`DriverFinder` contract** is the Drivers domain's only public gateway: primitives in, a `NearestDriver` DTO out. The Orders domain depends on this interface, never on the `Driver` model — enforcing the domain boundary.

### Stage 5 — Nearest-driver finder
`NearestAvailableDriverFinder` implements `DriverFinder`, bound to the interface in `DriversServiceProvider`.

- Two-step geo search: indexed **bounding-box** pre-filter → `ST_Distance_Sphere()` ranking → nearest first.
- Only `assignable()` drivers (available + no active order) are considered; busy or already-assigned drivers are excluded even if physically closer.

### Stage 6 — Assignment with concurrency safety
`AssignOrder` (implements the `OrderAssigner` contract) is the heart of the system.

- **One transaction, pessimistic locks.** It `lockForUpdate()`s the order row first (serialising competing assigns of the same order), then claims the driver under a row lock. `SELECT ... FOR UPDATE` is the answer to the brief's "real concurrency" hint — two simultaneous requests can't double-book a driver or an order.
- **Cross-domain only via contracts.** The Orders action never touches the `Driver` model. It calls `DriverFinder` (read) and `DriverClaimer` (atomic claim) — all driver mutation stays inside the Drivers domain.
- **Find→claim race handling.** If a nearby candidate is grabbed by a competing request mid-flight, the claim fails, the finder re-runs (now skipping that busy driver), and the next nearest is tried — bounded to 3 attempts.
- **Explicit failure modes:** missing order → `OrderNotFound` (404); not pending → `OrderAlreadyAssigned` (409); nobody claimable → `NoAvailableDriver` (422).
- **`OrderAssigned` event** is dispatched on success — a seam for notifications / real-time pushes, with no listener required yet.

### Stage 7 — HTTP API (presentation layer)
Thin controllers in `Presentation/Admin`, no business logic:

- `POST /api/orders/{order}/assign` → `OrderAssignmentController` calls the `OrderAssigner` contract, returns `AssignmentResource`.
- `GET /api/drivers/{driver}/orders` → `DriverOrderController`: route-model binding (404 on unknown driver), `ListDriverOrdersRequest` validates an optional `status` filter + bounded `per_page`, the `ListDriverOrders` read action paginates, `OrderResource` shapes the output.
- **Bilingual responses (ar/en).** `SetLocaleFromHeader` middleware reads `Accept-Language`; resources and exception messages localize off `app()->getLocale()`. Each error returns a stable `code` + a localized `message`.
- **HTTP status mapping lives in the presentation layer** (`bootstrap/app.php`), not the domain — domain exceptions don't know about HTTP.

### Stage 8 — Factories & seeders
`php artisan migrate --seed` produces a realistic dispatch snapshot: 24 drivers around central Cairo (15 available, 5 busy, 4 offline) and 30 pending orders.

### Stage 9 — Vue frontend
A Vue 3 (Composition API) SPA served inside Laravel via Vite — a single Blade page mounts `ActiveOrders.vue`.

- The component lists orders with a **filter** (`active` default · `all` · or any single status) + pagination, and gives each pending one an **Assign** button. On assign it shows per-row loading, then either the matched driver + distance, or the localized API error (e.g. "no available driver").
- The assigned row updates in place (no full reload). Axios sends `Accept: application/json`; `Accept-Language` would drive ar/en.

- **Driver list** on open (`GET /api/drivers`): search by name or phone (`Driver::search()` scope), a **driver-status filter** (available / busy / offline), per-page selector, pagination, and a colored availability dot (🟢 available · 🟡 busy · 🔴 offline).
- **Clicking a driver opens a separate detail screen** showing `GET /api/drivers/{id}/orders` with an **order-status filter** (pending / assigned / …) and pagination — the second required endpoint, with a UI.


## API summary

| Method | Endpoint | Purpose |
|--------|----------|---------|
| `POST` | `/api/orders/{order}/assign` | Assign an order to the nearest available driver |
| `GET`  | `/api/orders` | Orders for the dispatcher screen — `?filter=active\|all\|<status>` + pagination |
| `GET`  | `/api/drivers` | Search drivers by name/phone (paginated) |
| `GET`  | `/api/drivers/{driver}/orders` | A driver's orders — status filter + pagination |

All responses are JSON; send `Accept-Language: ar` or `en` for localized labels and error messages. Errors carry a stable `code` plus a localized `message`.

## Intentionally not done (and why)

Conscious incompleteness, per the brief:

- **Automated tests** — the assignment logic and its concurrency/failure paths were verified manually (tinker + real HTTP). A `tests/` suite (assignment happy path, 404/409/422, find→claim race) is the next step; it needs a MySQL test DB because the geo search uses `ST_Distance_Sphere` (not available in sqlite).
- **Real-time updates** — the dispatcher screen refreshes on demand; `OrderAssigned` is already the seam for WebSockets (Laravel Reverb) later.
- **Driver rejection / re-queue, rate limiting, observability** — out of scope for this exercise; the event-driven design leaves room for them.
